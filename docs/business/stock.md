# Stock 360 and Company Inventory

Last updated: 2026-07-02

## Scope

The first Stock 360 and Company Inventory slice is read-only. It connects tenant, owner/company, part, condition, quantity, location, tag info, certificates, traceability, availability, lifecycle, source, cost, margin, documents, ACI stock, external stock, company inventory, and workflow links.

## Implementation

- Shared read-model builders: `buildStock360ReadModel` and `buildCompanyInventoryReadModel`.
- API contracts:
  - `GET /v1/stock/{id}/360`
  - `GET /v1/company-inventory`
- UI surfaces:
  - `/stock/internal`
  - `/stock/external`
  - `/stock/internal/[id]`
  - `/company-inventory`
  - Company 360 inventory panels under `/companies/[id]`

## Tenant Isolation

Every read-model builder accepts `RequestContext`. The builder filters companies, parts, stock, RFQs, quotes, orders, documents, service workflows, and audit events by tenant before joining records. Missing records from another tenant return `null` for detail read models or empty inventory rows.

## Business Rules Preserved

- Qty `0` remains visible and is counted in exception totals.
- Owner/company, supplier, tag info, and traceability company are independent relationships.
- ACI stock and external stock remain separate sources.
- Company Inventory may show the same stock line under multiple company relationships, but workspace totals count unique stock records.
- RFQ-linked stock context uses `RFQ_ID`, not quote id.
- Stock lifecycle is represented through audit/lifecycle events and remains non-mutating.

## Mutation Boundaries

Boundary panels exist for:

- Add stock
- Upload certificate/document
- Reserve stock
- Move stock
- Create RFQ from stock

Each panel lists required data, tenant/context checks, and the future owning module. These actions do not persist data and do not touch Yoyamic or a live database.

## Known Gaps

- No reservation ledger yet.
- No stock movement ledger yet.
- No document storage or certificate validation pipeline yet.
- No real consignment lifecycle persistence yet.
- No Yoyamic MySQL read adapter query map yet.
## Persistence Foundation Phase 2

Stock 360 and Company Inventory remain deployed as static/sample read-model screens. Locally, the Express API now has tenant-scoped Stock CRUD foundation routes backed by repository contracts, validation, and selectable memory/PostgreSQL providers:

- `GET/POST /v1/stock`
- `GET/PATCH /v1/stock/:id`

The persistent schema keeps owner company, supplier company, tag-info company, and traceability company as independent relationships. Quantity `0` rows remain valid and visible. `locationText` is intentionally temporary until Warehouse/location modeling is approved.

PostgreSQL mode is local/dev only and requires explicit `DATABASE_URL` configuration. The static frontend does not connect to it unless `persistent-api` mode is selected locally.

This does not make deployed stock mutations operational. Add stock, reserve stock, move stock, and lifecycle changes remain workflow boundaries in the static frontend.
