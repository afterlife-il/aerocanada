# Company 360 API

Status: local authenticated API complete; not deployed.

Reads require `company.read`; mutations require `company.manage`.

- `GET/POST /v1/companies` supports list/create; list query parameters are `q`, `status`, `role`, `sort`, `direction`, `page`, and `pageSize`.
- `GET/PATCH/DELETE /v1/companies/:id` supports identity CRUD.
- `GET /v1/companies/:companyId/360` returns Company, Contacts, Addresses, related PostgreSQL Stock, Documents-module links, persisted activity, and commercial workflow boundaries.
- `GET/POST /v1/companies/:companyId/contacts` and `PATCH/DELETE /v1/contacts/:id` provide Contact CRUD.
- `GET/POST /v1/companies/:companyId/addresses` and `PATCH/DELETE /v1/company-addresses/:id` provide Address CRUD.

The OpenAPI document includes these routes. Errors remain controlled for validation, authorization, duplicates, not-found records, tenant mismatch, stock-reference deletion protection, and database failures. The client never falls back from persistent mode to fixtures.
