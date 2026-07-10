# PostgreSQL Integration Testing

Status: implemented, gated on a local isolated PostgreSQL database.

## Test Command

From `SaaS_Aviation/`:

```bash
TEST_DATABASE_URL=postgresql://saas_aviation_test:replace_locally@127.0.0.1:5432/saas_aviation_test npm run test -w @saas-aviation/api
```

If `TEST_DATABASE_URL` is not set, the PostgreSQL integration test is skipped with an explicit message. It does not fake a passing database run.

## Coverage

`apps/api/src/postgres-persistence.test.ts` verifies:

- migration apply/status against an isolated PostgreSQL schema
- repository persistence across close/reopen
- tenant isolation for companies, contacts, parts, and stock
- duplicate and cross-tenant constraint behavior
- local API CRUD persistence across repository restart

Each test run creates a temporary schema in the configured test database and drops it in cleanup.

## Current Environment Note

During Phase 2 implementation on 2026-07-10, this machine had no available `postgres`, `psql`, or Docker PostgreSQL runtime and no `DATABASE_URL`/`TEST_DATABASE_URL` configured. The implementation and gated tests are present, but actual database execution remains blocked until a local test database is provisioned.

## Safety Rules

- Never point `TEST_DATABASE_URL` at Yoyamic.
- Never point `TEST_DATABASE_URL` at a live customer database.
- Keep credentials outside git.
- Use only isolated development/test databases for migration apply and integration tests.
