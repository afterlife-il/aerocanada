# SaaS_Aviation Persistence Foundation

Status: Phase 2 local PostgreSQL provider foundation, not deployed.

## Current State

The deployed SaaS_Aviation staging frontend is still a static Next.js export backed by sample read models. The Express API runtime is not deployed, and no public frontend is connected to a development API or database.

Phase 2 keeps `sample-static` as the deployed UI mode and adds an explicit local PostgreSQL persistence provider for Company, Contact, Part Number, and Stock Item development.

## Provider Modes

`apps/api/src/persistence/provider.ts` selects one provider from environment configuration:

- `memory`: default local/test provider, backed by `InMemoryCorePersistence`.
- `postgres`: development PostgreSQL provider, backed by `PostgresCorePersistence`.

PostgreSQL mode requires `DATABASE_URL`. If `PERSISTENCE_PROVIDER=postgres` or `DATA_SOURCE_MODE=postgres` is set without `DATABASE_URL`, API startup fails. There is no silent fallback from PostgreSQL to memory.

## PostgreSQL Implementation

Phase 2 uses the smallest coherent PostgreSQL layer:

- Native `pg` client and connection pool.
- Explicit pool configuration from environment.
- Deterministic SQL migrations under `SaaS_Aviation/database/migrations`.
- Tenant-scoped repository methods using parameterized queries.
- Transactional writes for multi-table company role and part alternate updates.

The provider is for development/local integration only. Production deployment still requires final auth, RBAC, audit persistence, secrets management, backup/restore, monitoring, and operational database decisions.

## Frontend Data Source Boundary

`apps/web/src/lib/data-source-mode.ts` keeps two explicit frontend modes:

- `sample-static`: current static export/sample data mode.
- `persistent-api`: local/runtime API mode requiring `NEXT_PUBLIC_SAAS_API_BASE_URL`.

`apps/web/src/lib/persistent-api.ts` implements a reusable client for persistent mode. It refuses to run outside explicit `persistent-api` mode and does not fall back to sample fixtures.

## Yoyamic Boundary

Yoyamic remains a read-only legacy source for future migration planning. Phase 2 adds only a `YoyamicReadonlySource` policy scaffold with safe read options and no live queries. It contains no write methods and performs no connection to Yoyamic.

SaaS_Aviation must never write directly to Yoyamic and must not reuse Yoyamic tables as the permanent SaaS schema.

## Security

Every repository method requires `RequestContext` and filters by `tenantId`. The PostgreSQL schema reinforces tenant boundaries with `tenant_id` columns, tenant-composite foreign keys, and tenant-scoped uniqueness where business rules require it.

No credentials, `.env` files, database deployment, API deployment, Yoyamic writes, Plesk changes, Apache changes, or legacy PHP changes are part of this sprint.
