# AeroCanada MCP — matrice de livraison

Le candidat contient **100 nouveaux outils**. Le mode désactivé conserve le catalogue et les connexions existantes. Les colonnes Claude et ChatGPT concernent les applications réelles. Les essais HTTP isolés ont utilisé deux clients OAuth synthétiques et des jetons RS256 signés localement.

| Module | Outils MCP du candidat | Tests | Claude | ChatGPT | Production |
|---|---|---|---|---|---|
| Compagnies et CAGE | `add_company_aircraft`, `add_company_competitor`, `archive_company`, `create_company`, `create_company_address`, `get_company`, `get_company_payment_terms`, `list_company_addresses`, `list_company_competitors`, `list_company_fleet`, `list_company_forwarders`, `lookup_company_cage_code`, `search_companies`, `update_company`, `update_company_address`, `update_company_aircraft`, `update_company_payment_terms` | PDO simulé et MariaDB hors ligne | En attente | En attente | Non déployé |
| Contacts | `archive_contact`, `create_contact`, `get_contact`, `restore_contact`, `search_contacts`, `update_contact` | PDO simulé et MariaDB hors ligne | En attente | En attente | Non déployé |
| Pièces et stocks | `archive_part`, `create_part`, `create_parts`, `get_part`, `list_wanted_parts`, `search_parts`, `update_part`, `create_external_stock`, `create_internal_stock`, `get_inventory_item`, `list_inventory_lookups`, `list_part_prices`, `search_inventory`, `update_external_stock`, `update_internal_stock` | PDO simulé et MariaDB hors ligne | En attente | En attente | Non déployé |
| RFQ | `add_rfq_line`, `create_rfq`, `create_rfq_from_gmail`, `get_yoyamic_rfq`, `rank_parts`, `search_rfqs`, `update_rfq_line` | PDO simulé et MariaDB hors ligne, lots et rollback | En attente | En attente | Non déployé |
| Cotations clients et fournisseurs | `create_customer_quotes`, `get_customer_quote`, `get_customer_quote_pdf`, `list_customer_quotes`, `update_customer_quote`, `create_supplier_quote`, `list_supplier_quotes`, `update_supplier_quote` | PDO simulé, MariaDB hors ligne et rendu PDF natif | En attente | En attente | Non déployé |
| Gmail et brouillons | `create_quote_gmail_draft`, `get_gmail_message`, `list_gmail_drafts`, `prepare_quote_email_draft`, `preview_gmail_draft_for_approval`, `preview_gmail_rfq`, `search_gmail_messages`, `send_approved_gmail_draft` | Transport Gmail simulé, import de RFQ, PDF, brouillons, approbation et déduplication SEND | En attente | En attente | Non déployé |
| Commandes | `create_order_draft_from_quote`, `get_order_draft`, `get_purchase_order`, `get_sales_order`, `list_order_drafts`, `list_purchase_orders`, `list_sales_orders`, `update_order_draft`, `validate_customer_po` | PDO simulé et MariaDB hors ligne | En attente | En attente | Non déployé |
| Factures | `add_invoice_item`, `create_invoice`, `get_invoice`, `list_invoices`, `mark_invoice_paid`, `mark_invoice_sent`, `update_invoice_item` | PDO simulé et MariaDB hors ligne, totaux et rollback | En attente | En attente | Non déployé |
| Expéditions et transporteurs | `create_shipment`, `get_shipment`, `list_shipments`, `update_shipment_status`, `update_shipment_tracking`, `create_shipper`, `list_shippers`, `update_shipper` | PDO simulé et MariaDB hors ligne pour les expéditions | En attente | En attente | Non déployé |
| Réparations et capacités | `import_supplier_capabilities`, `list_supplier_capabilities` | PDO simulé et MariaDB hors ligne | En attente | En attente | Non déployé |
| Documents | `download_yoyamic_document`, `list_company_documents`, `list_part_documents` | PDO simulé, FPDF et fichiers synthétiques | En attente | En attente | Non déployé |
| Administration, profil et audit | `list_yoyamic_mcp_audit`, `list_yoyamic_users`, `update_yoyamic_user_role`, `get_yoyamic_profile` | PDO simulé, test de refus et audit SQL hors ligne | En attente | En attente | Non déployé |
| Rapports et référentiels | `get_erp_dashboard`, `list_currencies`, `list_part_conditions`, `list_release_types`, `list_rfq_priorities`, `list_rfq_types` | PDO simulé et rapports sur données synthétiques | En attente | En attente | Non déployé |

## Fonctions restant à traiter

- Fusion de compagnies, code fournisseur distinct du CAGE, opérations bancaires et gestion complète des documents. Le modèle du code fournisseur distinct reste à établir.
- Interchangeabilité des pièces, imports complets, réservations, remises en stock et ventes. Les transitions transactionnelles natives correspondantes restent à valider.
- Suppressions et transitions RFQ et devis restantes, règles avancées outright/exchange et core charge, finalisation des ventes et achats et cycles des ordres de réparation.
- Association automatique et historique métier des messages. L’import Gmail couvre les lignes explicitement libellées et le contact sélectionné. L’envoi avec approbation est implémenté sous verrou et vérifié avec transport simulé. Le branchement Gmail autorisé reste à valider.
- Gestion complète des utilisateurs, rapports et paramètres. L’audit central enregistre l’action, le résultat et son attribution. Les comparaisons avant/après restent partielles.

## Blocages vérifiés

- Le fichier privé du compte lecteur MariaDB existant renvoie **EACCES (13)**. La connexion au socket MariaDB et la création d’un socket Unix isolé renvoient **EPERM (1)**. Le mode SQL bootstrap fonctionne sans socket. Le test intégré via PDO, les droits réels du compte dédié et la conformité du schéma de production restent à vérifier.
- Le rattachement OAuth est implémenté et ses refus sont testés. Le provisionnement des sujets réels, des attributions Auth0 et des jetons Gmail par boîte nécessite le contexte de validation autorisé.
- Le journal des processus n’a montré aucun test bloqué au début de l’intervention. Aucun service de production redémarré. Aucune connexion ni écriture SQL de production, aucun message réel envoyé.

[Procédure de validation et retour arrière](VALIDATION.md).
