#!/usr/bin/env bash
set -euo pipefail

candidate=$(cd "$(dirname "$0")" && pwd)
workspace=$(cd "$candidate/.." && pwd)
sandbox=$(mktemp -d /tmp/aerocanada-mysql-e2e.XXXXXXXX)
socket="$sandbox/mysql.sock"
datadir="$sandbox/data"
app_user=mcp_e2e
app_password=$("$workspace/.venv/bin/python" -c 'import secrets; print(secrets.token_hex(24))')
password_file="$sandbox/db-password"
config_file="$sandbox/erp-config.json"
server_pid=

cleanup() {
    if [[ -n "$server_pid" ]] && kill -0 "$server_pid" 2>/dev/null; then
        mariadb-admin --no-defaults --socket="$socket" --user=root shutdown >/dev/null 2>&1 || kill "$server_pid" 2>/dev/null || true
        wait "$server_pid" 2>/dev/null || true
    fi
    rm -rf -- "$sandbox"
}
trap cleanup EXIT

mariadb-install-db --no-defaults --datadir="$datadir" --auth-root-authentication-method=normal --skip-test-db > "$sandbox/install.log" 2>&1
mariadbd --no-defaults --user=root --datadir="$datadir" --socket="$socket" --pid-file="$sandbox/mariadb.pid" \
    --skip-networking --log-error="$sandbox/mariadb.log" &
server_pid=$!
ready=0
for _ in $(seq 1 100); do
    if mariadb-admin --no-defaults --socket="$socket" --user=root ping >/dev/null 2>&1; then ready=1; break; fi
    if ! kill -0 "$server_pid" 2>/dev/null; then break; fi
    sleep 0.1
done
if [[ "$ready" != 1 ]]; then
    tail -30 "$sandbox/mariadb.log" >&2 || true
    echo "Isolated MariaDB did not start" >&2
    exit 1
fi

sed -e 's/CREATE DATABASE synthetic_workflows;/CREATE DATABASE synthetic_e2e;/' \
    -e 's/USE synthetic_workflows;/USE synthetic_e2e;/' "$candidate/synthetic-workflows.sql" > "$sandbox/schema.sql"
cat >> "$sandbox/schema.sql" <<'SQL'
ALTER TABLE tb_company MODIFY Fld_Company_ID INT NOT NULL AUTO_INCREMENT;
ALTER TABLE tb_company_contact MODIFY id_company_contact INT NOT NULL AUTO_INCREMENT;
ALTER TABLE tbl_Parts MODIFY Fld_Part_ID INT NOT NULL AUTO_INCREMENT;
ALTER TABLE tbl_RFQ DROP INDEX Fld_RFQ_ID,
    MODIFY Fld_RFQ_ID VARCHAR(19) NULL, MODIFY Fld_Date VARCHAR(9) NULL;
ALTER TABLE tbl_RFQ_1 MODIFY Fld_RFQ_ID VARCHAR(30) NULL, MODIFY `date` VARCHAR(15) NOT NULL;
ALTER TABLE tb_company
    ADD internet VARCHAR(250) NOT NULL DEFAULT '', ADD cage_code VARCHAR(32) NOT NULL DEFAULT '',
    ADD aci_contact INT NOT NULL DEFAULT 1, ADD companyrating VARCHAR(32) NOT NULL DEFAULT '',
    ADD logocompany VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE tb_company_contact
    ADD Fld_Contact_Name VARCHAR(160) NOT NULL DEFAULT '', ADD Fld_Contact_Email VARCHAR(254) NULL,
    ADD Fld_Contact_Phone VARCHAR(50) NULL, ADD Fld_Contact_Phone2 VARCHAR(50) NULL,
    ADD Fld_Company_Mobile VARCHAR(50) NULL, ADD Fld_Contact_Title VARCHAR(128) NULL,
    ADD aci_contact INT NULL, ADD entry_date DATE NULL, ADD modified_date DATETIME NULL;
UPDATE tb_company_contact
    SET Fld_Contact_Name = CASE id_company_contact WHEN 1 THEN 'Synthetic Customer Buyer' ELSE 'Synthetic Supplier Buyer' END,
        Fld_Contact_Email = CASE id_company_contact WHEN 1 THEN 'customer@synthetic.invalid' ELSE 'supplier@synthetic.invalid' END;
ALTER TABLE tbl_Parts
    ADD Fld_Part_MFG INT NULL, ADD Fld_AC_ID INT NULL, ADD Fld_Part_List_Price DECIMAL(13,3) NULL,
    ADD Fld_Part_Price_Currency_ID INT NULL, ADD Fld_Remark TEXT NULL, ADD alt_pn VARCHAR(500) NULL,
    ADD ata_chapter VARCHAR(50) NULL, ADD cage_code VARCHAR(50) NULL, ADD moq INT NULL,
    ADD oem_lead_time VARCHAR(100) NULL, ADD core_value DECIMAL(13,3) NULL,
    ADD id_currency_core_value INT NULL, ADD Fld_Add_PN_Date DATETIME NULL,
    ADD aci_contact_entry INT NULL, ADD wanted TINYINT NOT NULL DEFAULT 0,
    ADD status VARCHAR(10) NOT NULL DEFAULT 'Available';
ALTER TABLE tbl_Condition ADD Fld_Condition_Text VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE tbl_Currency ADD Fld_Currency_Text VARCHAR(10) NOT NULL DEFAULT '';
ALTER TABLE tbl_Release ADD Fld_Release_Text VARCHAR(100) NOT NULL DEFAULT '';
CREATE TABLE tbl_Aircraft (Fld_AC_ID INT PRIMARY KEY, Fld_AC_Model VARCHAR(100)) ENGINE=InnoDB;
UPDATE tbl_Condition SET Fld_Condition_Text = 'NEW' WHERE Fld_Condition_ID = 1;
UPDATE tbl_Currency SET Fld_Currency_Text = 'USD' WHERE Fld_Currency_ID = 1;
UPDATE tbl_Release SET Fld_Release_Text = 'Synthetic certificate' WHERE Fld_Release_ID = 1;
ALTER TABLE tbl_RFQ_3 ADD sender_user_id INT NULL;
CREATE TABLE tbl_Employee (
    Employee_ID INT PRIMARY KEY, Employee_Name VARCHAR(120), email VARCHAR(254),
    statut VARCHAR(30), position VARCHAR(80)
) ENGINE=InnoDB;
INSERT INTO tbl_Employee VALUES (77, 'Synthetic Employee', 'employee@synthetic.invalid', 'Sales', 'Sales');
SQL
cat "$candidate/erp_audit.sql" >> "$sandbox/schema.sql"
cat >> "$sandbox/schema.sql" <<SQL
CREATE USER '$app_user'@'localhost' IDENTIFIED BY '$app_password';
GRANT SELECT ON synthetic_e2e.* TO '$app_user'@'localhost';
GRANT INSERT, UPDATE ON synthetic_e2e.tb_company TO '$app_user'@'localhost';
GRANT INSERT, UPDATE ON synthetic_e2e.tb_company_contact TO '$app_user'@'localhost';
GRANT INSERT, UPDATE ON synthetic_e2e.tbl_Parts TO '$app_user'@'localhost';
GRANT INSERT ON synthetic_e2e.tbl_RFQ TO '$app_user'@'localhost';
GRANT INSERT ON synthetic_e2e.tbl_RFQ_1 TO '$app_user'@'localhost';
GRANT INSERT ON synthetic_e2e.tbl_RFQ_2 TO '$app_user'@'localhost';
GRANT INSERT ON synthetic_e2e.tbl_RFQ_3 TO '$app_user'@'localhost';
GRANT INSERT, UPDATE ON synthetic_e2e.mcp_erp_audit TO '$app_user'@'localhost';
FLUSH PRIVILEGES;
SQL
mariadb --no-defaults --socket="$socket" --user=root < "$sandbox/schema.sql"

umask 077
printf '%s' "$app_password" > "$password_file"
"$workspace/.venv/bin/python" - "$config_file" "$password_file" "$socket" "$app_user" <<'PY'
import json,sys
config={
    'issuer':'https://issuer.synthetic.invalid/', 'enableSend':False,
    'database':'synthetic_e2e', 'socket':sys.argv[3], 'user':sys.argv[4],
    'passwordFile':sys.argv[2], 'yoyamicPages':'/opt/aerocanada/pages',
    'subjects':{'auth0|isolated-e2e-subject':{
        'enabled':True,'employeeId':77,'employeeEmail':'employee@synthetic.invalid',
        'operations':['create_company','update_company','get_company','create_contact','update_contact','get_contact',
            'create_part','update_part','get_part','create_rfq','get_yoyamic_rfq','create_supplier_quote',
            'list_supplier_quotes','create_customer_quotes','get_customer_quote','get_customer_quote_pdf',
            'prepare_quote_email_draft']
    }}
}
open(sys.argv[1],'w').write(json.dumps(config))
PY
chmod 600 "$config_file" "$password_file"
AEROCANADA_ERP_CONFIG="$config_file" /usr/bin/php "$candidate/test_erp_mysql_gateway_e2e.php"
