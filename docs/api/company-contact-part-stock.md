# Company / Contact / Part / Stock API

Status: local Express API foundation, not deployed.

All routes are under `/v1` and require a bearer session. Existing permissions are reused:

- Company and Contact routes require `company.read`.
- Part routes require `part.read`.
- Stock routes require `stock.read`.

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

## Error Model

Routes return controlled errors for authorization, validation, duplicates, not found, tenant mismatch, and database errors. Tenant mismatch and cross-tenant reads do not expose records from another tenant.
