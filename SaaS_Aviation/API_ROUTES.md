# API Routes

## Web Route Handlers

- `GET /api/health`
- `GET /api/companies`
- `GET /api/parts`
- `GET /api/stock/internal`
- `GET /api/stock/external`
- `GET /api/audit`

## API Service Routes

- `GET /health`
- `GET /openapi.json`
- `POST /v1/auth/login`
- `POST /v1/auth/logout`
- `GET /v1/companies`
- `GET /v1/parts`
- `GET /v1/stock/internal`
- `GET /v1/stock/external`
- `GET /v1/audit`

## OpenAPI Status

`apps/api/src/openapi/openapi.ts` now defines component schemas for:

- `HealthResponse`
- `SessionUser`
- `SessionResponse`
- `Company`
- `PartNumber`
- `StockItem`
- `AuditEvent`
- list response wrappers for companies, parts, stock, and audit events

The current OpenAPI contract covers all existing API service read routes. Mutation routes remain intentionally absent.

Business read routes now require bearer sessions and tenant context.

## Future Mutations

No business mutations are implemented in the foundation. Future mutations require RBAC, tenant scope, validation, audit event, and rollback strategy.
