# PostgreSQL Integration Testing

Status: implemented and passed against a local isolated PostgreSQL database on 2026-07-14.

## Test Command

From `SaaS_Aviation/`:

```bash
TEST_DATABASE_URL=postgresql://saas_aviation_dev:saas_aviation_dev@127.0.0.1:55432/saas_aviation_dev npm run test:postgres
```

If `TEST_DATABASE_URL` is not set, the PostgreSQL integration test is skipped with an explicit message. It does not fake a passing database run.

## Coverage

`apps/api/src/postgres-persistence.test.ts` verifies:

- migration apply/status against an isolated PostgreSQL schema
- idempotent migration re-apply
- migration checksum mismatch detection
- Company, Contact, Part, and Stock persistence across close/reopen
- quantity 0 and independent owner/supplier/tag-info/traceability company persistence
- tenant isolation for companies, contacts, parts, and stock
- duplicate and cross-tenant constraint behavior
- local API Company/Contact/Part/Stock persistence across repository restart, including second-tenant denial
- transaction rollback for multi-table part alternate updates
- rollback for invalid cross-tenant stock relationship updates
- Company 360 persistent aggregate with identity, Contacts, Addresses, inventory, explicit empty Documents boundary, and commercial workflow boundaries

Each test run creates a temporary schema in the configured test database and drops it in cleanup.

## Current Environment Note

Docker Desktop with the Linux/WSL2 engine is available. On 2026-07-14, migrations 001/002 were current and `npm run test:postgres` completed with 15 passed, 0 failed, and 0 skipped after Company hardening. The proof was local only: no API or database was deployed, the public frontend remains static/sample-backed, and no Yoyamic or live database was accessed.

## CI

`.github/workflows/saas-aviation-ci.yml` includes a `postgres-integration` job with a PostgreSQL service and `TEST_DATABASE_URL`. That job is the expected executable proof path when local runtime tooling is unavailable. The legacy nested `SaaS_Aviation/.github/workflows/ci.yml` is kept aligned for local reference, but repository CI is driven from the root `.github` workflow path.

## Safety Rules

- Never point `TEST_DATABASE_URL` at Yoyamic.
- Never point `TEST_DATABASE_URL` at a live customer database.
- Keep credentials outside git.
- Use only isolated development/test databases for migration apply and integration tests.
