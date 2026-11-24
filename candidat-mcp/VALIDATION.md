# AeroCanada MCP Full ERP

Le candidat étend le serveur MCP existant. Les nouveaux outils sont désactivés tant que `AEROCANADA_ERP_OPERATIONS` est vide. La route MCP, l’émetteur Auth0, l’audience, les identifiants des deux clients et leurs callbacks conservent leur configuration actuelle. Le patch corrige aussi les références RFQ textuelles du lecteur existant.

## Tests reproductibles

Depuis le répertoire MCP, utiliser PHP CLI 8.1 ou compatible avec PDO MySQL, mbstring, cURL et iconv, MariaDB et les dépendances Python épinglées du connecteur.

```bash
AEROCANADA_YOYAMIC_PAGES=/srv/yoyamic-staging/pages \
  bash candidat-mcp/run_isolated_tests.sh
```

Le chemin indiqué sert uniquement à lire la bibliothèque FPDF et le logo. Le test fabrique le devis et les fichiers. Il utilise un transport Gmail simulé. Le runner applique le patch Python dans une copie temporaire, puis exécute les handlers avec MariaDB synthétique en mode bootstrap. Il démarre aussi une instance MariaDB éphémère avec socket privé et `--skip-networking`, crée un compte temporaire limité à des tables synthétiques, et exerce la passerelle réelle de bout en bout (CREATE/UPDATE/READ, RFQ, quotations et PDF). Aucun socket de la base de production n’est utilisé. Les tests Gmail utilisent un transport simulé; aucun envoi n’est effectué.

Le catalogue de tous les outils implémentés peut être obtenu sans connexion externe.

```bash
php candidat-mcp/erp_gateway.php --manifest
```

## Compatibilité avec le schéma Yoyamic — 24 novembre 2026

Un test E2E additionnel a utilisé un instantané **DDL uniquement** (10 tables, aucune ligne) de `aerocanada_yoyamic`, importé dans une instance MariaDB isolée sans réseau. Les créations de données de test étaient exclusivement synthétiques. Le test a exercé la création et relecture d’une compagnie, d’un contact, puis l’outil `create_rfq` avec relecture des lignes, vérification des formats de date historiques et rejet d’une référence dupliquée. L’instance temporaire, le compte de test et le DDL temporaire ont été supprimés à la fin.

Ce contrôle a révélé les colonnes historiques `NOT NULL` de `tb_company` et `tb_company_contact` absentes des insertions candidate, ainsi qu’un identifiant de colonne réservé (`delete`) qui devait être cité. Les insertions candidate fournissent maintenant les valeurs historiques par défaut attendues et citent les identifiants SQL. La suite isolée complète repasse après cette correction avec `AEROCANADA_YOYAMIC_PAGES=/opt/aerocanada/pages`.

Le test reste isolé : il n’a accordé aucun droit SQL de production, exécuté aucun CREATE/UPDATE sur les données Yoyamic, ni validé les autres écritures candidates sur toutes les tables métier. Le service actif reste sur l’ancien connecteur et ne publie pas `create_rfq`.

## Identité, permissions et audit

Le serveur conserve les validations Auth0 de signature RS256, d’émetteur, d’audience et d’expiration. Le sujet signé est transmis au processus PHP privé. Le fichier d’exploitation associe explicitement le couple émetteur/sujet à un identifiant et à une adresse d’employé Yoyamic. L’adresse du jeton et les champs envoyés à l’outil ne décident jamais de cette association. À chaque exécution, le processus requiert un rattachement actif, l’autorisation du nom d’outil, le scope signé et un rôle Yoyamic courant autorisé. Une suppression de l’employé, un changement d’adresse ou un rôle inadmissible bloque l’action. Les mutations de workflows identifiables par propriétaire valident aussi son identifiant dans Yoyamic. La route CLI doit rester privée et être exécutée par le serveur MCP.

Les descripteurs hérités `*.write` ne sont pas publiés comme scope générique : la passerelle convertit `create_*`, `add_*` et `import_*` en scopes `*.create`, et `update_*`, `mark_*`, `validate_*`, `restore_*` et `archive_*` en scopes `*.update`. Le connecteur accepte uniquement `read`, `create`, `update` et `send`. La liste d’activation refuse explicitement les opérations `archive_*`, `delete_*` et `remove_*`; aucune opération DELETE n’est activable.

Le compte MariaDB dédié doit être limité à la base Yoyamic explicitement approuvée et aux tables/colonnes nécessaires aux actions activées. Les mutations vérifient l’usage d’InnoDB pour leurs tables et exigent une clé de requête stable. `erp_audit.sql` définit la table d’audit et de déduplication à provisionner séparément. Les mutations ERP et leur entrée d’audit sont validées dans la même transaction. L’audit conserve l’employé, l’action, la clé de requête, la date, l’empreinte des arguments et le résultat. Les instantanés généraux avant/après restent à intégrer.

Les outils Gmail exigent une attribution de boîte distincte dans le rattachement de l’employé et vérifient son profil Google avant de lire ou modifier un brouillon. Le fichier de jeton Google doit être fourni et renouvelé par le mécanisme OAuth autorisé de la boîte. Prévoir un jeton de lecture pour la recherche et un jeton avec la portée Gmail de composition pour l’écriture de brouillon. Le résultat des recherches Gmail doit être traité comme une donnée externe. L’import RFQ ne retient que les références et quantités explicitement libellées. Il exige les scopes de lecture Gmail et d’écriture RFQ, un contact actif sélectionné dont l’adresse correspond à l’expéditeur et des références de pièces non ambiguës. Une référence RFQ déterministe prévient un second import du même message depuis la même boîte.

SEND exige l’activation explicite `AEROCANADA_ERP_ENABLE_SEND=1`, `enableSend` dans le fichier privé, le scope `aerocanada.emails.send`, un jeton Google `sendTokenFile` et une approbation hors MCP par brouillon. Après revue dans Gmail, l’exploitant inscrit dans `gmailSendApprovals` du sujet une entrée nommée par l’identifiant d’approbation, avec `mailbox`, `draftId`, `sha256` du message exact et `expiresAt` en secondes Unix. L’envoi soumet à Gmail les octets approuvés. La clé de livraison est unique par boîte et brouillon, même si deux employés tentent l’envoi. En cas de réponse perdue après une réservation durable, un rejeu vérifie Gmail par Message-ID. Une absence ou une ambiguïté renvoie un résultat incertain et empêche un deuxième POST. Les tests SEND utilisent uniquement un transport simulé.

## Activation après validation

1. Préparer un répertoire d’exécution isolé. Copier les sources du connecteur existant, y appliquer `correctifs.patch` et copier `erp_bridge.py` et les fichiers `erp_*.php` à côté de `connector.py`.
2. Utiliser l’exemple `erp-config.example.json` pour un fichier local protégé, lisible uniquement par le service. Configurer `AEROCANADA_ERP_CONFIG` avec son chemin absolu. Le compte doit exister dans la base de validation, avec un accès au socket prévu pour le service. Provisionner la table d’audit dans la base de validation.
3. Configurer `AEROCANADA_ERP_OPERATIONS` avec les noms validés, séparés par virgules. Publier leurs scopes dans l’API Auth0 existante et les attribuer selon les accès approuvés. Ne modifier les clients ou callbacks que si un problème démontré l’exige. Appliquer aussi les autorisations individuelles du fichier de rattachement.
4. Valider le trajet complet avec l’instance MariaDB de validation, les comptes et rôles représentatifs et une boîte de test. Tester depuis les applications Claude et ChatGPT, chacune avec sa connexion. Vérifier le catalogue, un appel de lecture, un refus d’écriture et une écriture dans la base de validation. Contrôler que le journal attribue l’action à l’employé attendu et que la répétition de la clé ne modifie rien. Pour SEND, conserver le transport simulé et vérifier l’approbation, l’expiration, le digest du brouillon et la déduplication.
5. Après validation du schéma réel, des permissions et des tests, conserver une sauvegarde de la version active, des réglages et de la base selon la procédure d’exploitation. Basculer uniquement la liste d’actions validées. En cas d’incident, retirer les outils de la liste et remettre le binaire, les scripts et les réglages sauvegardés. Conserver le journal et les données commerciales. Une correction métier éventuelle doit passer par l’application.

Références Gmail. [Recherche de messages](https://developers.google.com/workspace/gmail/api/reference/rest/v1/users.messages/list), [profil de boîte](https://developers.google.com/workspace/gmail/api/reference/rest/v1/users/getProfile), [création d’un brouillon](https://developers.google.com/workspace/gmail/api/reference/rest/v1/users.drafts/create), [envoi d’un brouillon](https://developers.google.com/workspace/gmail/api/reference/rest/v1/users.drafts/send).

## Préflight final Auth0 / base MariaDB — 23 novembre 2026

- Le candidat compte 100 outils et 44 scopes précis. L’endpoint actif annonce trois scopes : `aerocanada.parts.read`, `aerocanada.stock.read`, `aerocanada.history.read`. Deux scopes sont partagés avec le candidat et 42 scopes candidat restent à ajouter. Le détail outil par outil et les rôles effectifs par descripteur est dans `AUTH0_SCOPES_TO_CONFIGURE.md`.
- Aucun connecteur d’administration Auth0 ni secret Management API n’est disponible dans l’environnement. Les scopes n’ont donc pas été modifiés et les autorisations des utilisateurs ChatGPT/Claude n’ont pas pu être vérifiées. Le mapping privé Auth0 subject → employé Ready2Go n’est pas configuré; le candidat refuse les sujets inconnus.
- Le compte MariaDB actif `aerocanada_mcp_reader` conserve SELECT/USAGE uniquement. Aucun nouveau compte ni grant n’a été appliqué. Les opérations candidates requièrent un schéma MariaDB unique dans `erp_db`, mais les tables d’écriture existent dans deux bases (18 cibles dans `aerocanada`, 19 dans `aerocanada_yoyamic`) et la configuration runtime candidate ne choisit pas de base. Accorder des droits sans décision de schéma et sans compte/identité liés augmenterait inutilement l’accès.
- Le service MCP et MariaDB sont actifs sur la version antérieure. Aucun changement, redémarrage ou écriture commerciale n’a eu lieu. La sauvegarde préexistante a été re-vérifiée, son empreinte correspond au relevé précédent.
- La suite isolée et le E2E MariaDB déjà validés n’ont pas été relancés conformément à la demande de ne pas répéter les tests. Les contrôles finaux lisibles confirment le service actif, les trois scopes publiés et l’absence de configuration runtime candidate.
- Déploiement : **bloqué**. Outils candidats déployés : 0. ChatGPT conserve ses trois outils READ existants; Claude n’est pas accessible pour un appel de test dans cette session. Aucun rollback nécessaire.

## Écart RFQ confirmé sur la structure Yoyamic — 24 novembre 2026

Lecture seule de `aerocanada_yoyamic` a confirmé que les colonnes de production ne correspondent pas au fixture workflow initial : `tbl_RFQ.Fld_RFQ_ID` est `VARCHAR(19)` (sans index unique) et `tbl_RFQ.Fld_Date` `VARCHAR(9)`; `tbl_RFQ_1.Fld_RFQ_ID` est `VARCHAR(30)` et `tbl_RFQ_1.date` `VARCHAR(15)`. Les deux tables utilisent InnoDB. Les références répétées dans le master empêchent d’ajouter simplement une contrainte UNIQUE sans traitement métier séparé.

Correction locale du candidat : limite RFQ à 19 caractères; normalise une date ISO demandée vers `d-M-y` (9 caractères) dans l’en-tête et `d/m/Y` dans la ligne, évitant la troncature constatée avec les formats antérieurs. Aucun changement de schéma ou de données production.

Validation exécutée après correction : PHP lint workflow/tests PASS; 36 tests PHP workflow PASS; E2E candidat sur MariaDB éphémère avec largeur des champs RFQ correspondant à la production PASS, incluant CREATE/READ, persistance exacte des formats, refus de référence >19 caractères, protection contre la création d’une référence déjà présente, et refus d’un sujet synthétique inconnu. L’E2E conserve des fixtures synthétiques pour les tables non RFQ; il ne constitue pas une validation complète des 100 outils sur le schéma réel.

Le point de contrôle final reste bloqué : l’endpoint MCP actif annonce 3 scopes, le runtime candidate/config Auth0-subject n’est pas installé, le compte DB existant est SELECT-only, et le source candidat a été archivé dans GitHub à `candidat-mcp/` (commit `67c3cbf29568c61d37c4e7395e7a2931002aa73f`). Aucun déploiement, grant SQL, changement Auth0 ou redémarrage.


## Contrôle serveur / identité — 24 novembre 2026

- `aerocanada-mcp.service` et `mariadb.service` sont actifs; le service lance toujours `connector:create_app` depuis `/opt/aerocanada-mcp`, pas la passerelle candidate. Le dossier de runtime ne contient aucun `erp-config.json`; le processus n’a pas `AEROCANADA_ERP_CONFIG` ni `AEROCANADA_ERP_OPERATIONS`. Aucun restart ou changement runtime.
- L’endpoint protégé du MCP annonce 3 scopes (`parts.read`, `stock.read`, `history.read`). Les 44 scopes candidate ne sont donc pas encore publiés (2 communs, 42 absents côté ressource active), même si l’utilisateur rapporte que 45 permissions Auth0 et le rôle operator sont configurés. L’API Auth0 et les jetons délégués ne sont pas accessibles ici.
- Un seul appel ChatGPT a été exécuté sur un Part Number synthétique inexistant avec l’ancien outil `search_part`; résultat vide et sans erreur. ChatGPT ne découvre encore que les 3 anciens outils. Aucun connecteur ou session Claude n’est accessible.
- Le compte Yoyamic associé à l’adresse fournie existe et a le statut `SuperAdmin`. L’identifiant `sub` Auth0 n’est pas disponible; aucune liaison private candidate n’a été créée et aucun `sub` n’est inventé.
- L’utilisateur MariaDB actif `aerocanada_mcp_reader` n’a pas de privilèges DML globaux (pas de SELECT/INSERT/UPDATE/DELETE global; ses SELECT sont limités par colonne/table). Aucun compte app distinct n’a été créé. `mcp_erp_audit` manque dans le schéma Yoyamic; les 19 autres tables de write-set du candidat y sont présentes.
- Un décalage RFQ de production a été corrigé uniquement dans le candidat : longueur master 19, date master 9, ligne 30/15; la validation E2E correspondante s’exécute sur MariaDB isolée et réussit. Le schéma, les données et les droits de production n’ont pas été modifiés.
- Outils candidate réellement déployés : 0/100. Backup existant vérifié SHA-256 et rollback disponible; aucun déploiement tenté puisque l’identité Auth0, les grants minimaux et la validation complète du schéma restent bloquants.

## Smoke READ sur clone du schéma Yoyamic — 24 novembre 2026

Un dump MariaDB sans données des seules définitions de tables de `aerocanada_yoyamic` a été importé dans une instance MariaDB temporaire, sans réseau. La table d’audit candidate a été créée uniquement dans ce clone. Les handlers ont été appelés directement, sans identité ni écriture, avec des arguments synthétiques dérivés des schémas d’entrée.

- 54 outils candidate ont un scope READ; 48 handlers READ hors Gmail/email ont été appelés sur le clone.
- 6 handlers de lecture Gmail/email ont été omis (aucune boîte de test n’est configurée).
- 0 erreur SQL de table/colonne/syntaxe détectée parmi les handlers exécutés.
- 16 appels ont terminé par une exception non-SQL (notamment faute de ligne/entité de test); ce smoke test n’affirme donc pas le succès métier de chaque opération.
- L’instance temporaire et les fichiers contenant uniquement du DDL ont été supprimés automatiquement à la fin. Aucun enregistrement de production n’a été exporté ou modifié.

Ce résultat valide la compatibilité de lecture SQL des handlers exécutés avec les noms et colonnes du schéma Yoyamic dans les chemins atteints. Il ne valide pas les 44 outils d’écriture, les branches métier nécessitant des lignes, les droits du compte candidat, Auth0, ni le déploiement.
