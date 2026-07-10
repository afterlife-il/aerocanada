# PostgreSQL Integration Testing

Status: implemented, gated on a local isolated PostgreSQL database.

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
- repository persistence across close/reopen
- tenant isolation for companies, contacts, parts, and stock
- duplicate and cross-tenant constraint behavior
- local API CRUD persistence across repository restart
- transaction rollback for multi-table part alternate updates
- rollback for invalid cross-tenant stock relationship updates

Each test run creates a temporary schema in the configured test database and drops it in cleanup.

## Current Environment Note

During Phase 2 implementation on 2026-07-10, this machine had no available `postgres`, `psql`, Docker, Podman, WSL distribution, or database URL configured. The implementation, local Docker Compose config, CI service config, and gated tests are present, but actual database execution remains blocked on this machine until a local test database is provisioned.

## CI

`.github/workflows/saas-aviation-ci.yml` includes a `postgres-integration` job with a PostgreSQL service and `TEST_DATABASE_URL`. That job is the expected executable proof path when local runtime tooling is unavailable. The legacy nested `SaaS_Aviation/.github/workflows/ci.yml` is kept aligned for local reference, but repository CI is driven from the root `.github` workflow path.

## Safety Rules

- Never point `TEST_DATABASE_URL` at Yoyamic.
- Never point `TEST_DATABASE_URL` at a live customer database.
- Keep credentials outside git.
- Use only isolated development/test databases for migration apply and integration tests.
