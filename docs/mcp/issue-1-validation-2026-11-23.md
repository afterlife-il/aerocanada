# AeroCanada MCP ERP — validation du candidat de l’issue #1

Le candidat du 23 novembre 2026 ajoute 100 outils au catalogue MCP existant, dont 54 lectures, 45 écritures et un envoi soumis à approbation. Les outils sont désactivés par défaut et les deux inscriptions OAuth existantes sont conservées.

Les essais isolés ont validé 16 tests Python, les 100 handlers PHP sur des données synthétiques, des requêtes et assertions MariaDB hors ligne, le rendu FPDF, l’import RFQ multilignes et la création/reprise de brouillon et d’envoi avec transport Gmail simulé. Aucun courriel réel n’a été envoyé.

Le candidat nécessite une validation du schéma et des droits effectifs, le provisionnement du lien Auth0–employé pour chaque sujet et une exécution depuis les deux clients réels. Aucun déploiement n’a été effectué et l’issue reste ouverte.

Empreinte SHA-256 du fichier `SHA256SUMS` des sources, tests et procédures du candidat local : `1d6ef2a94a4ee285725ad3f01dd60f9d8b4de80076b6f505b2b47e9ddd1419e8`.


## Vérifications en intégration — 23 novembre 2026

- Un ancien processus de test Python endormi depuis plus de quatre heures a été identifié dans le répertoire temporaire du candidat et arrêté seul (SIGTERM). Le service MCP, MariaDB et les autres processus métier n’ont pas été interrompus.
- MariaDB 10.6.23 a accepté, sous l’identité Unix du service, une requête de lecture utilisant le compte applicatif lecteur et une référence synthétique inexistante. Aucun résultat ERP n’a été émis. Le compte n’a que SELECT et USAGE; aucun droit DML n’est accordé.
- La découverte OAuth/Auth0, les métadonnées de ressource, PKCE et la clé de signature ont été validés. Le socket MCP actif répond 401 sans jeton et 200 aux métadonnées protégées.
- Les tests isolés ont été relancés avec succès : 16 tests Python, handlers PHP, MariaDB bootstrap hors ligne synthétique et workflows. Les 29 empreintes du manifeste SHA-256 sont conformes.
- Le manifeste local recense 100 outils candidat, mais le service de production exécute toujours l’ancien connecteur en lecture seule (3 outils). Aucun déploiement, redémarrage, changement de permissions, mutation commerciale ou email réel n’a eu lieu.
- Reste bloquant : aucun mapping vérifié Auth0 subject ↔ employé Yoyamic, aucun rôle MariaDB minimal d’écriture/audit ni base de staging, aucun jeton de boîte Gmail de test. Les appels réels depuis Claude et ChatGPT n’ont pas été testés et les connecteurs existants n’ont pas été modifiés.
- Aucun backup de déploiement n’a été pris puisqu’aucun changement sensible n’a été appliqué; sauvegarde et rollback doivent précéder le déploiement futur.


## Tentative de mise en service en lecture seule — 23 novembre 2026

- Sauvegarde root-only vérifiée : `/var/backups/aerocanada-mcp/pre-readonly-deploy-20261123T160407Z.tar.gz`, 13 entrées, mode 0600, SHA-256 `027a42c9aa194998c0099021013412053ad3688604c7cb9ec66f3afc9727443d`.
- Les trois outils déjà déployés ont réussi depuis le connecteur ChatGPT existant, sur une référence synthétique sans résultat : `search_part`, `get_stock`, `get_part_history`. Aucune nouvelle fonction candidate n’est visible.
- Le candidat comporte 54 outils de lecture et 18 scopes ; 16 scopes ne sont pas annoncés par les métadonnées OAuth du serveur actif. La passerelle attend également un mapping Auth0-subject ↔ employé Yoyamic vérifié, absent. Les permissions du lecteur DB ne couvrent pas des écritures et l’accès identité/audit du candidat n’est pas validé.
- Déploiement annulé avant toute modification, car ces contrôles empêchent d’assurer des lectures autorisées. Aucun redémarrage, changement de permission, mutation ERP ou email. Rollback non déclenché car la production n’a pas changé. La disponibilité depuis Claude et celle des outils candidate dans ChatGPT ne sont pas vérifiées.


## Durcissement CREATE/UPDATE — 23 novembre 2026

Le candidat publie des permissions distinctes READ, CREATE, UPDATE et SEND. La traduction des anciens descripteurs internes `.write` est centralisée dans la passerelle MCP; les outils create/add/import obtiennent `.create`, les outils update/mark/validate/restore/archive obtiennent `.update`. La liste d’activation refuse archive/delete/remove. Les tests de portée confirment qu’un jeton CREATE ne peut pas faire UPDATE et qu’aucun scope générique WRITE ou DELETE n’est publié.

La suite isolée a réussi après ce correctif : 17 tests Python, handlers PHP métier, MariaDB bootstrap synthétique, PDF FPDF local et transport Gmail simulé. 29 empreintes du candidat sont vérifiées. Le serveur actif n’a pas été modifié; l’identité employé Yoyamic, les nouveaux scopes Auth0, les droits de schéma et les tests Claude restent des prérequis de déploiement.


## Passerelle réelle sur MariaDB isolée — 23 novembre 2026

- Ajout d’un test E2E qui démarre une instance MariaDB temporaire sur socket privé avec `--skip-networking`, schéma synthétique, et compte éphémère limité à SELECT et INSERT/UPDATE sur les tables nécessaires. L’instance de production n’est pas utilisée.
- Appels exercés au travers de la passerelle candidate : création, mise à jour et relecture d’une compagnie, d’un contact lié, et d’un Part Number; création puis relecture d’une RFQ avec ligne; enregistrement/relecture de Supplier Quote et Customer Quotation; génération d’un PDF; aperçu d’email non persisté.
- Sujet OAuth synthétique inconnu refusé. Aucun appel SEND, aucun email réel, aucune modification de données de production.
- Le test E2E passe individuellement. La suite complète `run_isolated_tests.sh` passe : 17 tests Python, syntaxe PHP, handlers, MariaDB bootstrap et assertions, inventory/workflow et six étapes E2E de la passerelle.
- Le manifeste SHA-256 local a été recalculé et vérifié après ajout du runner E2E.
- Cela valide uniquement le candidat sur données synthétiques. Déploiement toujours bloqué : compte MariaDB de service en SELECT/USAGE seulement, scopes OAuth candidat non provisionnés sur le serveur actif (44 scopes candidat, 3 annoncés; 42 manquants), absence de mapping Auth0 subject → employé vérifié, configuration runtime candidate absente et aucun accès de session Claude disponible. ChatGPT ne voit que les trois outils de lecture déjà déployés. Aucun changement de production ou rollback n’a été nécessaire.


## Préflight final Auth0 / MariaDB — 23 novembre 2026

- Le manifeste candidat contient 100 outils et 44 scopes distincts. L’endpoint protégé actif annonce `aerocanada.parts.read`, `aerocanada.stock.read` et `aerocanada.history.read`; 42 scopes candidat manquent, dont tous les scopes CREATE/UPDATE/SEND. La matrice Auth0 exhaustive avec description, outils, rôles et statut est publiée dans [AUTH0_SCOPES_TO_CONFIGURE.md](AUTH0_SCOPES_TO_CONFIGURE.md).
- Aucun outil d’administration Auth0 ni credential Management API n’est disponible. Les scopes et les permissions assignées à chaque utilisateur n’ont donc pas été modifiés/vérifiés. Le mapping privé Auth0 subject → employé Ready2Go n’est pas configuré; les sujets inconnus restent refusés par le candidat.
- Le compte MariaDB actif `aerocanada_mcp_reader` est toujours limité à SELECT/USAGE. Aucun nouveau compte ni grant n’a été appliqué : le candidat attend une base unique, mais ses 18 tables d’écriture existent dans `aerocanada` et 19 dans `aerocanada_yoyamic`, sans base candidate définie. L’accès de production n’a pas été élargi.
- MCP et MariaDB sont actifs sur la version précédente. Les 100 outils candidats ne sont pas déployés (0 outil candidat en production). ChatGPT conserve les 3 outils READ existants; aucune session Claude n’est accessible pour un test. Aucun redémarrage, changement de configuration, donnée commerciale ou email.
- La sauvegarde existante a été vérifiée par SHA-256. Les tests isolés/E2E déjà réussis n’ont pas été rerun. Déploiement bloqué par les scopes/assignations Auth0, mapping utilisateur et clarification du schéma/comptes DB.


## Validation et intégration candidate — 24 novembre 2026

- Le snapshot de 32 fichiers candidat est maintenant versionné sous `candidat-mcp/` (commit de snapshot `67c3cbf29568c61d37c4e7395e7a2931002aa73f`). Il inclut le code, les tests, les fixtures synthétiques, la matrice Auth0 et les empreintes SHA-256.
- Vérification runtime : `aerocanada-mcp.service` et MariaDB actifs, mais ExecStart reste `connector:create_app`; runtime candidate absent. L’endpoint MCP annonce toujours les 3 scopes de l’ancien service. Outils candidats réellement déployés : 0/100.
- Le compte Yoyamic correspondant à l’adresse fournie existe avec rôle `SuperAdmin`; aucun `sub` Auth0 signé n’est disponible et aucun binding n’a été créé. L’utilisateur rapporte 45 permissions Auth0 et un rôle dédié, mais la session ne dispose pas d’Auth0 Management pour les confirmer. ChatGPT a exécuté l’ancien `search_part` (référence synthétique inexistante → aucun résultat). Aucune session Claude n’est disponible.
- MariaDB n’a toujours que le compte lecteur en production, sans DML. Le candidate audit table n’existe pas. Le schéma RFQ réel ne correspondait pas au fixture initial (master ID 19 chars/date 9, line ID 30/date 15); le candidat a été corrigé et le nouvel E2E a passé sur un serveur MariaDB isolé reproduisant ces largeurs. Aucune donnée ni permission production modifiée.
- Les tests exécutés après le correctif : PHP lint workflow, 36 handlers PHP workflow, E2E RFQ/schema sur base isolée. Les tests isolés antérieurs n’ont pas été relancés intégralement. La compatibilité du reste des 100 outils avec le schéma réel et les droits minimaux reste non prouvée.
- Aucune sauvegarde fraîche n’a été prise car aucun déploiement n’est autorisé par le préflight; la sauvegarde existante a été vérifiée dans la session précédente. Aucun restart ni rollback nécessaire.


## Schema-only lecture Yoyamic — 24 novembre 2026

Un clone éphémère MariaDB du schéma `aerocanada_yoyamic` a été construit à partir d’un dump DDL sans lignes; aucune donnée commerciale n’a été exportée. 48 handlers READ non Gmail/email ont été appelés, 6 lectures Gmail/email omises faute de boîte de test. Zéro erreur SQL de table/colonne/syntaxe; 16 appels ont eu des issues non SQL faute d’enregistrements synthétiques. L’instance temporaire a été supprimée automatiquement. Ce contrôle n’établit pas la compatibilité métier des 54 lectures ni celle des 44 opérations d’écriture.

Le candidat corrigé pour les longueurs/date RFQ passe son E2E MariaDB isolé. La production reste inchangée; service legacy actif, 3 outils, aucun compte DML candidate, aucun binding Auth0 subject vérifié, 0 outil candidate déployé. Le snapshot code et les suivis sont dans le commit candidat `67c3cbf29568c61d37c4e7395e7a2931002aa73f` et les commits de documentation suivants.


## Vérification ciblée des blocages — 24 novembre 2026

- Aucun processus candidat de test n’était encore actif. Le PID 888 est le processus de mariadb.service en production; aucun service n’a été arrêté.
- Les identifiants déjà fournis au service ont été réutilisés sans afficher le secret : connexion réussie à MariaDB 10.6.23 sous aerocanada_mcp_reader@localhost. Les métadonnées de privilèges montrent 66 SELECT limités par colonnes et aucun INSERT/UPDATE/DELETE. Le compte lecteur reste inchangé; aucun compte writer ni table d’audit candidate n’a été créé.
- Pour valider l’incompatibilité de schéma sans toucher les données, un test a importé uniquement les définitions DDL de dix tables Yoyamic dans une MariaDB temporaire sans réseau. Aucune ligne de production n’a été copiée. CREATE/READ compagnie, contact et create_rfq, format de dates historiques et refus d’un doublon ont réussi. Le test a révélé les colonnes legacy obligatoires manquantes aux insertions société/contact et le besoin de citer l’identifiant réservé delete; le candidat et ses fixtures ont été corrigés.
- La suite isolée complète a ensuite réussi : 17 tests Python, handlers PHP, bootstrap MariaDB, tests d’audit/rollback, PDF et flux gateway synthétique. Gmail était mocké; aucun email réel n’a été envoyé.
- Le service actif est toujours connector:create_app, actif avec ses trois outils de lecture. Les métadonnées OAuth annoncent toujours trois scopes et une initialisation MCP sans jeton retourne 401. create_rfq n’est pas publié ni exécutable depuis ce service.
- L’accès Auth0 Management et une session OAuth vérifiée de Yohan restent indisponibles; aucun sub n’a été déduit de l’adresse email ou inventé. Aucun grant, configuration, DDL, redémarrage, déploiement ou write métier n’a été effectué.


## MariaDB writer and exact-schema validation — 24 November 2026

- The candidate was run against an isolated MariaDB 10.6.23 instance built from **DDL only** for all 426 production Yoyamic base tables; no production rows were copied. The 44 distinct Yoyamic database mutation operations passed through the gateway with the exact writer grant template. A fake Gmail transport covered draft creation. All 48 test calls persisted one audit row each; synthetic persistence checks passed. DELETE and DDL were denied.
- The full isolated suite passed: 17 Python tests, PHP lint and handler suites, MariaDB bootstrap tests for company/contact, stock, workflow, audit/rollback, and the candidate gateway E2E. The E2E refused an unknown OAuth subject and did not invoke SEND. No real email was sent.
- A verified pre-change snapshot is `/var/backups/aerocanada-mcp/pre-writer-provision-20261124T130729Z.tar.gz` (SHA-256 `4f65581b7cb330387837af7c1b69b8279b57bd664f9de5227c7764b90df9d3f7`). It contains 426 schema definitions and zero INSERT/REPLACE statements, plus sanitized grants and rollback notes.
- Production metadata changes are limited to `aerocanada_mcp_writer@localhost` and InnoDB table `aerocanada_yoyamic.mcp_erp_audit`. The writer has 32 table SELECT grants, 19 INSERT, 16 UPDATE, four Employee SELECT columns, and UPDATE on `tbl_Employee.statut`; no DELETE, DDL, ALL, global or schema-level grants. The reader’s 33 column SELECT grants are unchanged. The production audit table has zero rows. A live writer login and zero-row SELECT probes passed; no production commercial DML was run.
- The active MCP service was not reconfigured or restarted; it still exposes three legacy read tools. The writer credential remains root-only and is not connected to the service. Production write activation remains blocked until Yohan’s delegated Auth0 subject is verified and explicitly mapped to a Ready2Go employee.
- The only user action needed to unblock identity verification is: call the existing Auth0 `/userinfo` endpoint with Yohan’s delegated access token and provide the returned `sub` claim only; do not share the bearer token.


## Contrôle du sub Auth0 délégué — 24 novembre 2026

- Vérification en lecture seule de `tbl_Employee` : l’employé Yoyamic 6 correspond à l’adresse professionnelle connue de Yohan; le rôle actuel retourné est `SuperAdmin`.
- Aucun accès de cette session ne permet de récupérer le jeton délégué AeroCanada ni d’appeler le `/userinfo` du tenant Auth0 existant. L’identifiant de profil communiqué par l’utilisateur n’a donc pas été considéré comme preuve de la claim signée `sub` du jeton.
- Aucun jeton n’a été lu, affiché ou enregistré; aucun binding Auth0→employé n’a été écrit. Le candidat reste non déployé et le service actif inchangé.
- Action exacte manquante : dans Cowork, appeler le `/userinfo` Auth0 existant avec le jeton délégué AeroCanada de Yohan et transmettre uniquement le claim `sub` retourné (jamais le bearer token). Après vérification de cette valeur, reprendre le binding explicite et les contrôles de déploiement.


## Temporary delegated-sub verification probe — 24 November 2026

- Added temporary MCP tool `verify_delegated_sub` to the active connector. It uses the existing JWT verifier (RS256 signature, issuer, audience, expiration and signed `sub`), requires only the existing `aerocanada.parts.read` scope, makes no MariaDB call, and returns only the signed subject. No bearer token is logged or persisted; no OAuth client, callback or scope was changed.
- Five isolated synthetic tests passed, including tampered/wrong issuer/audience/expired JWT rejection, no-scope denial, authenticated MCP list/call with a synthetic subject only, no DB access, and unauthenticated HTTP 401. No production token was used.
- Verified root-only backup before deployment: `/var/backups/aerocanada-mcp/pre-temp-subcheck-20261124T140739Z.tar.gz`, SHA-256 `5b1e013159e00d4feee433f83d8c7ead1fd8be822d4ce365d35142c14ec3d905`. The MCP service was restarted to load this single temporary tool; post-restart it is active, still returns 401 without OAuth, and advertises the same three scopes.
- The three existing ChatGPT reads succeeded after restart on a synthetic nonexistent part number. The authenticated list/call has not been run through Yohan’s client session: this runtime only has the original three-tool client snapshot, and the user delegated token is not available to the server operator. The isolated app lists four tools including the temporary one.
- No Auth0-sub-to-employee binding has been created; the 100-tool ERP candidate remains undeployed. Exact next action: refresh/reopen the existing AeroCanada MCP connection and invoke `verify_delegated_sub`; provide only its output for comparison with the supplied Auth0 User ID. After verification the temporary tool must be removed and the service restarted before continuing candidate deployment.


## Identity match and private binding — 24 November 2026

- The user reports that `verify_delegated_sub` returned a value exactly equal to the previously supplied Auth0 User ID. That temporary tool validated the access token server-side before returning its signed `sub`; no bearer token was shared or stored. A read-only MariaDB check confirms Yoyamic employee 6 and current role `SuperAdmin`.
- A private mapping was created in `/etc/aerocanada-mcp/candidate-identity-bindings.json` (root:root 0600), using the existing issuer and employee 6. Its `operations` list is empty. The PHP gateway denied a synthetic `search_companies` request before opening MariaDB. The mapping is not connected to the active runtime and grants no business operations.
- The temporary probe was removed by restoring the verified pre-probe connector backup and restarting the service. Post-removal checks: service active, unauthenticated `/mcp` returns 401, original three OAuth scopes unchanged, and all three existing ChatGPT reads succeed on a synthetic nonexistent part number. Two isolated removal tests pass.
- The candidate manifest still contains 100 tools and 44 scopes (54 READ, 21 CREATE, 24 UPDATE, 1 SEND). The candidate remains undeployed. The active service still uses the reader; the private mapping and writer are not connected to its runtime.
- Remaining blockers: active MCP metadata advertises only 3 scopes (42 of 44 candidate scopes are absent), the complete candidate READ grants have not been reviewed, and the candidate runtime/configuration is not deployed. No commercial data writes or real emails occurred.
