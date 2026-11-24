#!/usr/bin/env bash
set -euo pipefail

source_db="aerocanada_yoyamic"
test_db="synthetic_yoyamic"
tmp_dir="$(mktemp -d /tmp/aerocanada-real-ddl-XXXXXX)"
chmod 700 "$tmp_dir"
server_pid=""
cleanup() {
  if [[ -S "$tmp_dir/mysql.sock" ]]; then
    mariadb-admin --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot shutdown >/dev/null 2>&1 || true
  fi
  if [[ -n "$server_pid" ]]; then
    wait "$server_pid" >/dev/null 2>&1 || true
  fi
  python3 - "$tmp_dir" <<'PY'
from pathlib import Path
import shutil, sys
p = Path(sys.argv[1])
if p.parent == Path('/tmp') and p.name.startswith('aerocanada-real-ddl-'):
    shutil.rmtree(p)
PY
}
trap cleanup EXIT INT TERM

mapfile -t tables < <(mariadb --batch --skip-column-names -e "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='${source_db}' AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")
if ((${#tables[@]} < 100)); then
  echo "Refusing: unexpectedly small source schema (${#tables[@]} tables)" >&2
  exit 2
fi
mariadb-dump --no-data --skip-lock-tables --skip-add-locks --skip-triggers --skip-routines --skip-events --default-character-set=utf8mb4 "$source_db" "${tables[@]}" > "$tmp_dir/schema.sql"
if [[ ! -s "$tmp_dir/schema.sql" ]] || grep -Eiq '^[[:space:]]*INSERT[[:space:]]+INTO' "$tmp_dir/schema.sql"; then
  echo "Refusing: schema-only dump is empty or contains data inserts" >&2
  exit 2
fi

mariadb-install-db --no-defaults --datadir="$tmp_dir/data" --auth-root-authentication-method=normal --skip-test-db --user=root > "$tmp_dir/install.log" 2>&1
mariadbd --no-defaults --user=root --datadir="$tmp_dir/data" --socket="$tmp_dir/mysql.sock" --pid-file="$tmp_dir/mysql.pid" --skip-networking --log-error="$tmp_dir/mysql.err" &
server_pid=$!
ready=0
for _ in $(seq 1 100); do
  if mariadb --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot -e 'SELECT 1' >/dev/null 2>&1; then ready=1; break; fi
  if ! kill -0 "$server_pid" 2>/dev/null; then break; fi
  sleep 0.2
done
if ((ready == 0)); then
  cat "$tmp_dir/mysql.err" >&2 || true
  echo "Temporary MariaDB did not start" >&2
  exit 1
fi
mariadb --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot -e "CREATE DATABASE ${test_db} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mariadb --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot "$test_db" < "$tmp_dir/schema.sql"
echo "PASS cloned ${#tables[@]} production table definitions only; no row data copied"
mariadb --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot "$test_db" < "$(dirname "$0")/erp_audit.sql"
python3 - "$(dirname "$0")/mcp_writer_grants.template.sql" "$tmp_dir/writer-grants.sql" "$tmp_dir/writer.password" "$test_db" synthetic_mcp_writer <<'PY'
from pathlib import Path
import re, secrets, sys
template, sql_path, password_path, database, username = map(Path, sys.argv[1:])
database, username = str(database), str(username)
if not re.fullmatch(r'[A-Za-z0-9_]{1,64}', database) or not re.fullmatch(r'[A-Za-z0-9_]{1,32}', username):
    raise SystemExit('Invalid isolated grant identifiers')
password = secrets.token_hex(32)
password_path.write_text(password)
password_path.chmod(0o600)
sql = "CREATE USER '" + username + "'@'localhost' IDENTIFIED BY '" + password + "';\n"
sql += template.read_text().replace('{{DB}}', database).replace('{{USER}}', username)
sql_path.write_text(sql)
sql_path.chmod(0o600)
PY
mariadb --no-defaults --protocol=socket --socket="$tmp_dir/mysql.sock" -uroot < "$tmp_dir/writer-grants.sql"
AEROCANADA_TEST_SOCKET="$tmp_dir/mysql.sock" AEROCANADA_TEST_DATABASE="$test_db" \
  AEROCANADA_TEST_DB_USER=synthetic_mcp_writer AEROCANADA_TEST_DB_PASSWORD_FILE="$tmp_dir/writer.password" \
  php "$(dirname "$0")/test_real_schema_writes.php"
