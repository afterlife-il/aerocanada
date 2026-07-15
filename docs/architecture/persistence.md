# Ready2Go Aviation SaaS Persistence Foundation

## Persistent staging topology

The staging provider is explicitly `postgres`; persistent mode never falls back to the memory repository or sample data. PostgreSQL 16 uses the dedicated `saas_aviation_postgres_data` volume and is reachable only on the staging Docker network. Migration 003 adds canonical tenant code and a tenant-safe primary-company relationship. Redis and MinIO have separate volumes, but current durable business CRUD is PostgreSQL-backed; Documents object persistence is not yet implemented.

Status: dedicated PostgreSQL 16 persistent staging provider deployed and verified on 2026-07-15; production deployment is not approved.

## Current State

The Ready2Go Aviation SaaS staging frontend is a static Next.js export served by the isolated web container at `https://aviation.ready2go.aero`, with same-origin `/api/` proxying to the deployed Express API in explicit PostgreSQL mode. Persistent API mode refuses memory/sample fallback. AeroCanada is tenant data under `/AeroCanada`, not the platform identity. Some read-only UI areas still intentionally use sample read models and must not be described as durable business data.

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

The provider is deployed to persistent staging only. Production promotion still requires final auth, RBAC, audit persistence, secrets management, monitoring, and operational approval.

Local Docker Desktop/WSL2 validation passed real PostgreSQL tests for Company/Contact/Part/Stock reconnect persistence, quantity 0, independent stock company relationships, tenant isolation, constraint failures, and transactional rollback. The dedicated staging PostgreSQL 16 database now has migrations 001-003, and public authenticated reads proved earlier Company/Part/Stock records remained persistent after DNS/TLS activation. This does not make sample-backed workflow panels durable or authorize production promotion.

## Frontend Data Source Boundary

`apps/web/src/lib/data-source-mode.ts` keeps two explicit frontend modes:

- `sample-static`: current static export/sample data mode.
- `persistent-api`: local/runtime API mode requiring `NEXT_PUBLIC_SAAS_API_BASE_URL`.

`apps/web/src/lib/persistent-api.ts` implements a reusable client for persistent mode. It refuses to run outside explicit `persistent-api` mode and does not fall back to sample fixtures. Login uses the canonical `{ data: { session } }` envelope; the browser stores the local bearer token and clears it on logout. This is local-only: users and sessions remain in memory and sessions do not survive API restart.

## Yoyamic Boundary

Yoyamic remains a read-only legacy source for future migration planning. Phase 2 adds only a `YoyamicReadonlySource` policy scaffold with safe read options and no live queries. It contains no write methods and performs no connection to Yoyamic.

SaaS_Aviation must never write directly to Yoyamic and must not reuse Yoyamic tables as the permanent SaaS schema.

## Security

Every repository method requires `RequestContext` and filters by `tenantId`. The PostgreSQL schema reinforces tenant boundaries with `tenant_id` columns, tenant-composite foreign keys, and tenant-scoped uniqueness where business rules require it.

No credentials, `.env` files, database deployment, API deployment, Yoyamic writes, Plesk changes, Apache changes, or legacy PHP changes are part of this sprint.
