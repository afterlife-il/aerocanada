# Company / Contact / Part / Stock API

The operational Part and Stock web workspaces consume `/v1/parts`, `/v1/parts/:id`, `/v1/stock`, and `/v1/stock/:id`. They intentionally do not consume the older sample-adapter `/360` routes in persistent staging. Filtering/sorting/pagination are currently deterministic client operations over tenant-scoped persistent result sets; server-side pagination remains the next scale step.

Status: Express API foundation deployed to isolated persistent staging with PostgreSQL; not production-ready.

All routes are under `/v1` and require a bearer session:

- Company/Contact/Address reads require `company.read`; mutations require `company.manage`.
- Part routes require `part.read`.
- Stock routes require `stock.read`.

Company write RBAC and activity persistence are implemented locally. Authentication users/sessions remain in-memory and are not production-grade. Deployment still requires persistent auth, secure session handling, rate limiting, secrets, monitoring, backup/restore, and explicit approval.

## Companies

- `GET /v1/companies`
- `GET /v1/companies/:id`
- `POST /v1/companies`
- `PATCH /v1/companies/:id`
- `DELETE /v1/companies/:id`

## Contacts

- `GET /v1/companies/:companyId/contacts`
- `POST /v1/companies/:companyId/contacts`
- `PATCH /v1/contacts/:id`
- `DELETE /v1/contacts/:id`

See `docs/api/company.md` for the Company 360 aggregate, search, address, and delete contracts.

## Parts

- `GET /v1/parts`
- `GET /v1/parts/:id`
- `POST /v1/parts`
- `PATCH /v1/parts/:id`

## Stock

- `GET /v1/stock`
- `GET /v1/stock/:id`
- `POST /v1/stock`
- `PATCH /v1/stock/:id`

## Persistence Providers

The API uses `createCorePersistenceProvider()`:

- `memory`: default provider for local sample runtime and normal tests.
- `postgres`: local PostgreSQL provider selected by environment, requiring `DATABASE_URL`.

PostgreSQL mode fails if the database URL is missing. It does not fall back to memory.

## Error Model

Routes return controlled errors for authorization, validation, duplicates, not found, tenant mismatch, and database errors. Tenant mismatch and cross-tenant reads do not expose records from another tenant.

## Frontend Boundary

The public/static frontend remains `sample-static`. `persistent-api` is an explicit local mode and must be configured with `NEXT_PUBLIC_SAAS_API_BASE_URL`. The client refuses to run in sample mode and does not silently fall back.
