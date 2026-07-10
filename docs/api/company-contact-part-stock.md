# Company / Contact / Part / Stock API

Status: local Express API foundation with memory and PostgreSQL providers, not deployed.

All routes are under `/v1` and require a bearer session. Existing permissions are reused:

- Company and Contact routes require `company.read`.
- Part routes require `part.read`.
- Stock routes require `stock.read`.

Write-specific production RBAC is not complete. These routes are local persistence foundations and must not be exposed as production mutation workflows until auth, audit, rate limiting, and module ownership are hardened.

## Companies

- `GET /v1/companies`
- `GET /v1/companies/:id`
- `POST /v1/companies`
- `PATCH /v1/companies/:id`

## Contacts

- `GET /v1/companies/:companyId/contacts`
- `POST /v1/companies/:companyId/contacts`
- `PATCH /v1/contacts/:id`

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
