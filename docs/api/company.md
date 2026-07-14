# Company 360 API

Status: local persistent Company foundation hardened; not production-ready and not deployed.

Reads require `company.read`; mutations require `company.manage`.

- `GET/POST /v1/companies` supports list/create; list query parameters are `q`, `status`, `role`, `sort`, `direction`, `page`, and `pageSize`.
- `GET/PATCH/DELETE /v1/companies/:id` supports identity CRUD.
- `GET /v1/companies/:companyId/360` returns persistent Company, Contacts, Addresses, related PostgreSQL Stock and activity. Documents are an explicit empty non-persistent boundary; fixture Documents are never presented as durable Company records. Commercial entries contain future owner, required data, tenant/context checks, and `persistence: none`.
- `GET/POST /v1/companies/:companyId/contacts` and `PATCH/DELETE /v1/contacts/:id` provide Contact CRUD.
- `GET/POST /v1/companies/:companyId/addresses` and `PATCH/DELETE /v1/company-addresses/:id` provide Address CRUD.

The OpenAPI document uses reusable request/response schemas for the hardened login and Company workflows. The local login contract is `{ data: { session } }`; auth and sessions remain in-memory and are lost on API restart. The client never falls back from persistent mode to fixtures.
