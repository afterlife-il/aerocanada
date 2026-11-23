# AeroCanada MCP ERP — validation du candidat de l’issue #1

Le candidat du 23 novembre 2026 ajoute 96 outils au catalogue MCP existant, dont 52 lectures et 44 écritures. Les outils sont désactivés par défaut et les deux inscriptions OAuth existantes sont conservées.

Les tests isolés ont validé 14 tests Python, les 96 handlers PHP sur des données synthétiques, des requêtes et assertions MariaDB hors ligne, le rendu FPDF et la création/reprise d’un brouillon avec un transport Gmail simulé. Aucune base de production, boîte réelle ou connexion client réelle n’a été utilisée. Aucun courriel envoyé.

Le candidat nécessite une validation du schéma et des droits effectifs, le provisionnement du lien Auth0–employé pour chaque sujet et une exécution depuis les deux clients réels. Aucun déploiement n’a été effectué et l’issue reste ouverte.

Empreinte SHA-256 du fichier `SHA256SUMS` des sources, tests et procédures du candidat local : `0eeab3e26a833082061392655442f14f0547750fb4c70387dfe5e82dc39e8d1f`.
