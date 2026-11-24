#!/usr/bin/env bash
set -euo pipefail

candidate=$(cd "$(dirname "$0")" && pwd)
workspace=$(cd "$candidate/.." && pwd)
python=${AEROCANADA_TEST_PYTHON:-"$workspace/.venv/bin/python"}
sandbox=$(mktemp -d /tmp/aerocanada-full-erp-test.XXXXXXXX)
trap 'rm -rf -- "$sandbox"' EXIT

cp "$workspace"/{business_reads.py,connector.py,preflight.py,verify_oauth.py,requirements.txt} "$sandbox/"
cp "$candidate"/{correctifs.patch,erp_bridge.py,erp_*.php,erp_audit.sql,test_connector.py,test_erp_bridge.py,generate-sql-test.py,synthetic-schema.sql} "$sandbox/"
(
    cd "$sandbox"
    patch -p1 < correctifs.patch
    "$python" -m unittest -q
    "$python" generate-sql-test.py > sql-test.sql
)
for file in "$candidate"/erp_*.php; do
    php -l "$file"
done
for test in gateway companies inventory workflows operations mail_documents; do
    php "$candidate/test_erp_$test.php"
done

run_mariadb() {
    local name=$1
    local input=$2
    mariadb-install-db --no-defaults --datadir="$sandbox/$name" --auth-root-authentication-method=normal --skip-test-db > "$sandbox/$name-init.log" 2>&1
    if ! mariadbd --no-defaults --user="$(id -un)" --datadir="$sandbox/$name" --bootstrap --skip-networking --wsrep-on=OFF         < "$input" > "$sandbox/$name.log" 2>&1; then
        tail -20 "$sandbox/$name.log" >&2
        return 1
    fi
}

{
    cat "$candidate/synthetic-companies.sql"
    php "$candidate/test_erp_companies.php" --emit-sql
} > "$sandbox/companies.sql"
run_mariadb companies "$sandbox/companies.sql"
echo "MariaDB hors ligne : compagnies, contacts et référentiels."

{
    cat "$sandbox/sql-test.sql"
    cat "$candidate/erp_audit.sql"
    cat <<'SQL'
START TRANSACTION;
INSERT INTO tbl_Parts VALUES (2, 'SYNTH-AUDIT', 'Synthetic operation');
INSERT INTO mcp_erp_audit (actor_hash, employee_id, operation, request_id, input_hash, result_json)
VALUES (REPEAT('a', 64), 77, 'test_write', 'synthetic-request-01', REPEAT('b', 64), '{"id":2}');
INSERT INTO assertions SELECT COUNT(*) = 1 FROM mcp_erp_audit;
ROLLBACK;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM mcp_erp_audit;
INSERT INTO assertions SELECT COUNT(*) = 0 FROM tbl_Parts WHERE Fld_Part_ID = 2;
INSERT INTO mcp_erp_audit (actor_hash, employee_id, operation, request_id, input_hash, result_json, delivery_key)
VALUES (REPEAT('a', 64), 77, 'send_draft', 'synthetic-send-01', REPEAT('b', 64), '{}', REPEAT('c', 64));
INSERT IGNORE INTO mcp_erp_audit (actor_hash, employee_id, operation, request_id, input_hash, result_json, delivery_key)
VALUES (REPEAT('d', 64), 88, 'send_draft', 'synthetic-send-02', REPEAT('b', 64), '{}', REPEAT('c', 64));
INSERT INTO assertions SELECT COUNT(*) = 1 FROM mcp_erp_audit;
SQL
} > "$sandbox/audit.sql"
run_mariadb legacy_and_audit "$sandbox/audit.sql"
echo "MariaDB hors ligne : 24 requêtes de lecture et quatre assertions audit/rollback/SEND."
bash "$candidate/test_inventory_mariadb.sh"
bash "$candidate/test_workflows_mariadb.sh"
# Actual candidate gateway integration against an ephemeral MariaDB instance.
# The runner provisions only synthetic tables and a temporary least-privilege user.
bash "$candidate/test_erp_mysql_gateway_e2e.sh"
