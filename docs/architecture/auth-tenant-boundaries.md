# Auth and Tenant Module Boundaries

Last updated: 2026-06-30

## Ownership

Auth/Tenant owns:

- `SaaS_Aviation/packages/shared/src/types.ts`
- `SaaS_Aviation/packages/shared/src/contracts.ts`
- `SaaS_Aviation/apps/api/src/auth/**`
- auth/session route definitions in `SaaS_Aviation/apps/api/src/server.ts`
- auth/session schemas in `SaaS_Aviation/apps/api/src/openapi/openapi.ts`
- login/session UI in `SaaS_Aviation/apps/web/src/app/login/page.tsx`
- auth/security docs under `docs/security/`

## Collaboration Contract

Other modules may use `RequestContext`, roles, and permissions, but must not redefine them.

Business modules must not call repository methods without tenant context. If a new repository is added, its methods must accept `RequestContext` unless the data is explicitly public system metadata.

## Protected Surfaces

Protected API routes:

- `GET /v1/companies`
- `GET /v1/parts`
- `GET /v1/stock/internal`
- `GET /v1/stock/external`
- `GET /v1/audit`

Public API routes:

- `GET /health`
- `GET /openapi.json`
- `POST /v1/auth/login`
- `POST /v1/auth/logout`
- `GET /v1/session`

## Static Export Boundary

The currently deployed web app is a static export. It can present auth-aware navigation and login UI, but it cannot enforce real sessions by itself. The Express API and future server runtime are the security boundary.
