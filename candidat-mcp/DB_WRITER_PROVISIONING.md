# MariaDB writer provisioning and validation — 2026-11-24

## Production changes completed

The candidate database principal `aerocanada_mcp_writer@localhost` now exists as a separate local-only account. Its generated credential is stored root-only (`0600`) at `/etc/aerocanada-mcp/aerocanada_mcp_writer.password`; it is not stored in this repository or in the backup. The active MCP service was not configured to use it.

The InnoDB table `aerocanada_yoyamic.mcp_erp_audit` was created from `erp_audit.sql`. It currently contains zero rows. Production changes were limited to this table and the new SQL principal/grants. No commercial rows were inserted, updated, or deleted; the existing `aerocanada_mcp_reader@localhost` grants were confirmed unchanged.

## Effective privilege boundary

The writer has table-level `SELECT` on 32 specific mutation and lookup tables, plus column-level `SELECT` on `tbl_Employee(Employee_ID, Employee_Name, email, statut)`. It has table-level `INSERT` on 19 tables and `UPDATE` on 16 tables, plus column-level `UPDATE` on `tbl_Employee.statut`. There are no schema-level or elevated global privileges. Metadata checks found zero `DELETE`, `CREATE`, `ALTER`, `DROP`, `REFERENCES`, `INDEX`, `TRIGGER`, `EXECUTE`, or `GRANT OPTION` privileges. No `ALL PRIVILEGES` was granted.

The template is `mcp_writer_grants.template.sql`. In particular, the writer can change only an employee's `statut` column; it cannot change employee credentials, email, or other profile fields. The existing reader remains separate and unchanged.

This grant set covers the candidate mutation handlers and their direct lookup/reference reads. It is not yet a reviewed full-ERP read grant set, and the account must not be treated as the runtime credential for all 100 tools.

## Tests executed

- `test_real_schema_writes.sh` cloned **DDL only** for all 426 base tables from `aerocanada_yoyamic` into a temporary MariaDB 10.6.23 instance with networking disabled. No production row data was copied.
- All **44 distinct Yoyamic database mutation operations** passed through the candidate PHP gateway using a temporary account created from the same grant template. The fake Gmail transport also passed the quote-draft tool without contacting Gmail. Repeated setup calls yielded 48 audited requests; persistence checks passed.
- The same isolated writer was explicitly denied `DELETE` and DDL. No live business table was written during production grant validation.
- The production writer credential authenticated successfully. Zero-row `SELECT ... LIMIT 0` probes passed for the 33 table names covered by table or column-level reads and for the scoped employee identity columns. Grant metadata showed no forbidden privilege; audit table engine is InnoDB and audit row count is zero.
- `run_isolated_tests.sh` passed: 17 Python tests; PHP lint and handler groups; MariaDB bootstrap tests for companies, stock, workflows and audit rollback; candidate least-privilege gateway E2E for company, contact, part, RFQ, quotation/PDF and email preview. The E2E denied an unknown subject and did not invoke SEND.
- No real email was sent. Tests ran serially; no test process remains active.

These are isolated and metadata-level checks. They are **not production validation of business writes**: no production mutation was attempted, and the new account is not connected to the MCP process.

## Deployment and identity gate

The production `aerocanada-mcp` and MariaDB services remain active. The production MCP still runs the legacy connector and publishes its existing three read tools. No candidate code was deployed and no service was restarted.

Write activation remains blocked until Yohan's delegated Auth0 `sub` is obtained and verified and mapped explicitly to the matching Ready2Go employee. Do not infer the subject from an email address. Until then the writer password remains inaccessible to the service user and the gateway's production identity binding is not configured.

## Backup and rollback

Verified pre-change backup:

`/var/backups/aerocanada-mcp/pre-writer-provision-20261124T130729Z.tar.gz`

SHA-256: `4f65581b7cb330387837af7c1b69b8279b57bd664f9de5227c7764b90df9d3f7`

It contains a schema-only snapshot (426 table definitions, zero row INSERT/REPLACE statements), sanitized MCP grants metadata, pre-change object state and a rollback note. `gzip -t` and `tar -tzf` succeeded; archive mode is `0600`.

Before the writer is configured or used, rollback is `DROP USER IF EXISTS 'aerocanada_mcp_writer'@'localhost';` and `DROP TABLE IF EXISTS aerocanada_yoyamic.mcp_erp_audit;`. If the writer has been used, stop the candidate process and preserve/export audit rows before considering removal of the audit table. The rollback note is also in the backup archive.
