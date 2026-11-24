#!/usr/bin/env bash
set -eu
candidate=$(cd "$(dirname "$0")" && pwd)
sandbox=$(mktemp -d /tmp/aerocanada-workflows-validation.XXXXXXXX)
trap 'rm -rf -- "$sandbox"' EXIT
mariadb-install-db --no-defaults --datadir="$sandbox/db" --auth-root-authentication-method=normal --skip-test-db > "$sandbox/init.log" 2>&1
{
    cat "$candidate/synthetic-workflows.sql"
    printf '%s\n' 'START TRANSACTION;'
    php "$candidate/test_erp_workflows.php" --sql
    cat <<'SQL'
INSERT INTO assertions SELECT COUNT(*) = 1 FROM tbl_RFQ WHERE Fld_RFQ_ACI_Employee_Id = 77;
INSERT INTO assertions SELECT COUNT(*) = 3 FROM tbl_RFQ_1 WHERE Employee_ID = 77;
INSERT INTO assertions SELECT Fld_Qty = 4 FROM tbl_RFQ_1 WHERE ID = 1;
INSERT INTO assertions SELECT COUNT(*) = 3 FROM tbl_RFQ_3 WHERE id_tbl_rfq1 IN (1, 2);
INSERT INTO assertions SELECT COUNT(*) = 1 FROM tbl_RFQ_3 WHERE source_type = 'SQ' AND source_id = 1;
INSERT INTO assertions SELECT Fld_Price = 26.50 FROM tbl_RFQ_3 WHERE ID = 1;
INSERT INTO assertions SELECT Fld_Price = 22.50 AND aci_contact = 77 FROM tbl_RFQ_2 WHERE ID = 1;
INSERT INTO assertions SELECT status = 'ACCEPTED_ORDER' AND created_by = 77 FROM tbl_PO_Draft WHERE ID = 1;
INSERT INTO assertions SELECT total = 45.00 AND status = 'paid' AND created_by = 77 FROM tbl_Invoices WHERE ID = 1;
INSERT INTO assertions SELECT total = 45.00 AND quantity = 4 FROM tbl_Invoice_Items WHERE id = 1;
INSERT INTO assertions SELECT status = 'delivered' AND tracking_number = 'SYNTH-TRACK' AND created_by = 77 FROM tbl_Shipping WHERE ID = 1;
INSERT INTO assertions SELECT COUNT(*) = 1 AND MAX(aircraft) = 'A320 FAMILY | A320' FROM tbl_capa_list;
ROLLBACK;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_RFQ;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_RFQ_1;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_RFQ_2;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_RFQ_3;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_PO_Draft;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_Invoices;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_Invoice_Items;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_Shipping;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_capa_list;
SQL
} > "$sandbox/test.sql"
if ! mariadbd --no-defaults --user="$(id -un)" --datadir="$sandbox/db" --bootstrap --skip-networking --wsrep-on=OFF     < "$sandbox/test.sql" > "$sandbox/mariadb.log" 2>&1; then
    tail -20 "$sandbox/mariadb.log" >&2
    exit 1
fi
printf 'MariaDB hors ligne : %s requêtes de parcours, 21 assertions.\n' "$(php "$candidate/test_erp_workflows.php" --sql | grep -c ';$')"
