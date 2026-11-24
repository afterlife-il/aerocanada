#!/usr/bin/env bash
set -euo pipefail

test_dir=$(cd -- "$(dirname -- "$0")" && pwd)
stage=$(mktemp -d /tmp/aerocanada-inventory.XXXXXXXX)
trap 'rm -rf -- "$stage"' EXIT

{
    cat "$test_dir/synthetic-inventory.sql"
    echo 'START TRANSACTION;'
    php "$test_dir/test_erp_inventory.php" --sql
    echo 'COMMIT;'
    cat <<'SQL'
START TRANSACTION;
UPDATE tbl_Stock SET Fld_Qty = 999 WHERE Fld_Stock_ID = 2;
UPDATE tbl_Stock_external SET Fld_Qty = 999 WHERE Fld_Stock_externe_ID = 1;
ROLLBACK;
INSERT INTO checks SELECT COUNT(*) = 4 FROM tbl_Parts;
INSERT INTO checks SELECT COUNT(*) = 1 FROM tbl_Parts WHERE Fld_Part_ID = 2 AND aci_contact_entry = 9 AND Fld_Part_Desc = 'Updated description' AND wanted = 1;
INSERT INTO checks SELECT COUNT(*) = 1 FROM tbl_Parts WHERE Fld_Part_ID = 3 AND status = 'archive';
INSERT INTO checks SELECT COUNT(*) = 1 FROM tbl_Stock WHERE Fld_Stock_ID = 2 AND Fld_Part_ID = 1 AND Fld_Qty = 3 AND Fld_Warehouse_Location_ID = 1;
INSERT INTO checks SELECT COUNT(*) = 1 FROM tbl_Stock_external WHERE Fld_Stock_externe_ID = 1 AND Fld_Part_ID = 1 AND Fld_Company_ID = 1 AND Fld_Tag_Info_ID = 1 AND Fld_Entry_Date = '2026-11-23' AND Fld_Qty = 7;
SQL
} > "$stage/test.sql"

if ! mariadb-install-db --no-defaults --datadir="$stage/db" --auth-root-authentication-method=normal --skip-test-db > "$stage/init.log" 2>&1; then
    cat "$stage/init.log" >&2
    exit 1
fi

if ! mariadbd --no-defaults --user="$(id -un)" --datadir="$stage/db" --bootstrap --skip-networking --wsrep-on=OFF < "$stage/test.sql" > "$stage/sql.log" 2>&1; then
    cat "$stage/sql.log" >&2
    exit 1
fi

echo 'Inventory SQL passed MariaDB bootstrap and five persisted-state assertions'
