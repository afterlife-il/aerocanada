# SaaS_Aviation Persistence Foundation

Status: Phase 1 local foundation, not deployed.

## Current State

The deployed SaaS_Aviation staging frontend is still a static Next.js export backed by sample read models. The Express API runtime is not deployed, and there is no deployed SaaS_Aviation production database.

Phase 1 adds local persistence boundaries for Company, Contact, Part Number, and Stock Item so CRUD behavior can be validated without pretending the static sample data is production data.

## Target Architecture

Legacy Yoyamic database -> read-only legacy adapter/importer -> dedicated SaaS_Aviation PostgreSQL database -> SaaS_Aviation API -> SaaS_Aviation frontend.

SaaS_Aviation must never write directly to Yoyamic and must not reuse Yoyamic tables as the permanent SaaS schema.

## Technology Choice

The repo did not contain an ORM or database migration framework. Phase 1 therefore uses the smallest coherent foundation:

- PostgreSQL-compatible SQL migration in `SaaS_Aviation/database/migrations/001_core_persistence.sql`.
- Shared TypeScript repository contracts and validation schemas in `packages/shared/src/persistence.ts`.
- Local in-memory repository implementation in `apps/api/src/persistence/core-memory-repository.ts` for tests and local API wiring.

This avoids adding a second competing database layer before the production database provider is approved. The next persistence sprint should choose the concrete PostgreSQL client/ORM and run the existing SQL migration against a development database.

## Frontend Data Source Boundary

`apps/web/src/lib/data-source-mode.ts` defines two explicit modes:

- `sample-static`: current static export/sample data mode.
- `persistent-api`: future local/runtime API mode requiring `NEXT_PUBLIC_SAAS_API_BASE_URL`.

The static frontend must not silently fall back to fake data when API mode is requested.

## Security

All repository methods require `RequestContext` and filter by `tenantId`. CRUD routes require an authenticated bearer session and the relevant existing read permission. Write-specific RBAC permissions are not yet modeled, so this remains a local foundation rather than production authorization.

No credentials, `.env` files, database deployment, API deployment, Yoyamic writes, Plesk changes, Apache changes, or legacy PHP changes are part of this sprint.
