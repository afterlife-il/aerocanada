-- Least-privilege database grants for candidate-mcp ERP CREATE/UPDATE paths.
-- Render only with validated database/user identifiers; no DELETE or DDL grants.
-- {{DB}} is the Yoyamic schema. {{USER}} is a localhost-only service principal.

-- SELECT is restricted to business rows the write handlers lock/read and their
-- required reference lookups. The existing reader principal is unchanged.
GRANT SELECT ON `{{DB}}`.`tb_company` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tb_company_contact` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Company_Details` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Competitor` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Fleet` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Parts` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Stock` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Stock_external` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_RFQ` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_RFQ_1` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_RFQ_2` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_RFQ_3` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_PO_Draft` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Invoices` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Invoice_Items` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Shipper` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Shipping` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_capa_list` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`mcp_erp_audit` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Company_Type` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Division` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Region` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Aircraft` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Payment` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Currency` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Condition` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Release` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Status` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Stock_Location` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Warehouse_Location` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_Priority` TO '{{USER}}'@'localhost';
GRANT SELECT ON `{{DB}}`.`tbl_RFQ_Type` TO '{{USER}}'@'localhost';

-- Yoyamic identity resolution reads only identity and current role. The admin
-- role tool can change only statut, never credentials, email, or other fields.
GRANT SELECT (`Employee_ID`, `Employee_Name`, `email`, `statut`) ON `{{DB}}`.`tbl_Employee` TO '{{USER}}'@'localhost';
GRANT UPDATE (`statut`) ON `{{DB}}`.`tbl_Employee` TO '{{USER}}'@'localhost';

-- CREATE/UPDATE are granted only where candidate handlers issue those verbs.
GRANT INSERT, UPDATE ON `{{DB}}`.`tb_company` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tb_company_contact` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Company_Details` TO '{{USER}}'@'localhost';
GRANT INSERT ON `{{DB}}`.`tbl_Competitor` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Fleet` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Parts` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Stock` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Stock_external` TO '{{USER}}'@'localhost';
GRANT INSERT ON `{{DB}}`.`tbl_RFQ` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_RFQ_1` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_RFQ_2` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_RFQ_3` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_PO_Draft` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Invoices` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Invoice_Items` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Shipper` TO '{{USER}}'@'localhost';
GRANT INSERT, UPDATE ON `{{DB}}`.`tbl_Shipping` TO '{{USER}}'@'localhost';
GRANT INSERT ON `{{DB}}`.`tbl_capa_list` TO '{{USER}}'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `{{DB}}`.`mcp_erp_audit` TO '{{USER}}'@'localhost';
