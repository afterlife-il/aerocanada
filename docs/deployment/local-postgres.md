# Local PostgreSQL Setup

Status: development/test only. Do not use Yoyamic or a live customer database.

Local runtime status (2026-07-14): verified with Docker Desktop using the Linux/WSL2 engine. PostgreSQL 16 started healthy on the localhost-only Compose binding; migration 001 applied with checksum and idempotent re-apply; the real integration and local API/repository restart tests passed. Nothing was deployed.

## Environment

Use local-only values in an uncommitted `.env` file or shell session:

```bash
PERSISTENCE_PROVIDER=postgres
DATABASE_URL=postgresql://saas_aviation_dev:saas_aviation_dev@127.0.0.1:55432/saas_aviation_dev
TEST_DATABASE_URL=postgresql://saas_aviation_dev:saas_aviation_dev@127.0.0.1:55432/saas_aviation_dev
DATABASE_POOL_MIN=0
DATABASE_POOL_MAX=10
DATABASE_SSL=false
API_PORT=4107
```

`.env.example` contains placeholders only. Do not commit real credentials.

## Local Docker Compose

If Docker is available, start the development database:

```bash
npm run db:dev:up
```

The service is defined in `SaaS_Aviation/docker-compose.dev.yml`, binds only to `127.0.0.1:55432`, uses development-only credentials, and stores data in the named local volume `saas_aviation_postgres_dev`.

Stop it:

```bash
npm run db:dev:down
```

Reset it, deleting the local volume:

```bash
npm run db:dev:reset
```

## Migration Commands

From `SaaS_Aviation/`:

```bash
npm run db:migrate:status
npm run db:migrate:apply
```

Migrations are explicit. They are not applied automatically by API startup, tests, build, or static export.

## Running the API Locally

Use PostgreSQL mode only after the database exists and migrations are applied:

```bash
PERSISTENCE_PROVIDER=postgres npm run dev -w @saas-aviation/api
```

The provider fails fast when PostgreSQL mode is selected without `DATABASE_URL`. It does not fall back to memory.

## Integration Tests

With `TEST_DATABASE_URL` set:

```bash
npm run test:postgres
```

The GitHub Actions workflow includes a dedicated PostgreSQL service job that runs this command. The 2026-07-14 local run completed with 13 tests passed and no skips. On machines without Docker, PostgreSQL binaries, WSL, or a configured database URL, report local runtime verification as blocked rather than passed.

## Frontend Persistent Mode

The static/public frontend remains `sample-static`. For local persistent API testing only:

```bash
NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE=persistent-api
NEXT_PUBLIC_SAAS_API_BASE_URL=http://127.0.0.1:4107
```

Do not deploy this mode to the current public static staging site until persistent auth/session handling, rate limiting, audit, secrets, monitoring, backup/restore, and database operations are approved. Phase 1.1 verified that the old local in-memory session is rejected after API restart and that re-login restores access to persisted Company records.
