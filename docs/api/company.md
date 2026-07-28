# Company 360 API

Status: persistent Company foundation deployed and validated on isolated staging; not production-ready.

Reads require `company.read`; mutations require `company.manage`.

- `GET/POST /v1/companies` supports list/create; list query parameters are `q`, `status`, `role`, `sort`, `direction`, `page`, and `pageSize`.
- `GET/PATCH/DELETE /v1/companies/:id` supports identity CRUD.
- `GET /v1/companies/:companyId/360` returns persistent Company, Contacts, Addresses, related PostgreSQL Stock and activity. Documents are an explicit empty non-persistent boundary; fixture Documents are never presented as durable Company records. Commercial entries contain future owner, required data, tenant/context checks, and `persistence: none`.
- `GET/POST /v1/companies/:companyId/contacts` and `PATCH/DELETE /v1/contacts/:id` provide Contact CRUD.
- `GET/POST /v1/companies/:companyId/addresses` and `PATCH/DELETE /v1/company-addresses/:id` provide Address CRUD.
- `GET/POST /v1/companies/:companyId/notes` and `PATCH/DELETE /v1/company-notes/:id` provide tenant-scoped Company note CRUD. Reads require `company.read`; mutations require `company.manage`; bodies are trimmed, required, and limited to 5,000 characters.

The OpenAPI document uses reusable request/response schemas for the hardened login and Company workflows. The local login contract is `{ data: { session } }`; auth and sessions remain in-memory and are lost on API restart. The client never falls back from persistent mode to fixtures.

The Company 360 aggregate now includes `notes`, ordered pinned-first and then by latest update. Commercial workflows and durable Company Documents remain explicit boundaries.
