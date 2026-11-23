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
