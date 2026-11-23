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
