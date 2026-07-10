# Local PostgreSQL Setup

Status: development/test only. Do not use Yoyamic or a live customer database.

## Environment

Use local-only values in an uncommitted `.env` file or shell session:

```bash
PERSISTENCE_PROVIDER=postgres
DATABASE_URL=postgresql://saas_aviation_dev:replace_locally@127.0.0.1:5432/saas_aviation_dev
TEST_DATABASE_URL=postgresql://saas_aviation_test:replace_locally@127.0.0.1:5432/saas_aviation_test
DATABASE_POOL_MIN=0
DATABASE_POOL_MAX=10
DATABASE_SSL=false
API_PORT=4107
```

`.env.example` contains placeholders only. Do not commit real credentials.

## Migration Commands

From `SaaS_Aviation/`:

```bash
npm run migrate:status
npm run migrate:apply
```

Migrations are explicit. They are not applied automatically by API startup, tests, build, or static export.

## Running the API Locally

Use PostgreSQL mode only after the database exists and migrations are applied:

```bash
PERSISTENCE_PROVIDER=postgres npm run dev -w @saas-aviation/api
```

The provider fails fast when PostgreSQL mode is selected without `DATABASE_URL`. It does not fall back to memory.

## Frontend Persistent Mode

The static/public frontend remains `sample-static`. For local persistent API testing only:

```bash
NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE=persistent-api
NEXT_PUBLIC_SAAS_API_BASE_URL=http://127.0.0.1:4107
```

Do not deploy this mode to the current public static staging site until API runtime, auth, audit, and database operations are approved.
