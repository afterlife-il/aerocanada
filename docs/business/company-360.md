# Company 360

Last updated: 2026-07-07

## Scope

Company 360 is now the foundation for the future Company module in `SaaS_Aviation/`. It remains read-only and sample-data-backed, but it has moved beyond a shell into a complete tenant-scoped workspace for company identity, contacts, inventory, documents, commercial context, activity, and workflow entry points.

## Implemented Surfaces

- Static web read-model composition: `SaaS_Aviation/apps/web/src/lib/data.ts`.
- Company list UI: `/companies`.
- Company detail UI: `/companies/[id]`.
- Shared type support: `WorkflowBoundaryAction.entityType` now accepts existing document owner modules plus `company-inventory`.

## Company List

The list read model supports:

- Search across company name, legacy code, email, website, location, primary contact email/phone/name, and tags.
- Filters for company type and active/inactive status.
- Sorting by name, type, location, risk, and last activity.
- Pagination metadata.
- Explicit ready, empty, and error states.
- Responsive table rendering with company status, risk, contacts, inventory, document count, and legacy id.

## Company 360 Detail

The detail workspace includes:

- Company overview and KPI cards.
- Company information, tags, status, risk, legacy id, location, email, and last activity.
- Contacts panel with role, email, phone, mobile, and edit entry point.
- Inventory summary with ACI units, external units, stock value, zero-qty rows, and linked stock lines.
- Documents panel backed by the shared Documents read model.
- Commercial activity panels for RFQ, Supplier Quotes, Customer Quotes, Purchase Orders, and Sales Orders.
- Activity panel with a proper empty state.
- Workflow boundary panel for all mutation/workflow entry points.

## Workflow Boundaries

Company 360 exposes entry points only. It does not fake unfinished modules and does not persist mutations.

- Edit Company: future Company mutation workflow.
- Create Contact: future Contact module.
- Edit Contact: future Contact module.
- Add Document: Documents module upload/storage workflow.
- Create RFQ: future RFQ module.
- View Company Inventory: Company Inventory / Inventory module.
- Supplier Quotes, Customer Quotes, Purchase Orders, and Sales Orders panels remain open-workflow boundaries unless sample read-model records already exist.

## Business Rules Preserved

- Tenant context is required before composing read models.
- `RFQ_ID` remains the commercial workflow key.
- Owner/company, supplier, tag info, and traceability company remain separate inventory relationships.
- Qty `0` stock rows remain visible in company inventory.
- No Yoyamic, database, deployment, or mutation workflow was touched.

## Known Gaps

- Real Yoyamic read adapter queries are still pending.
- Contact create/edit workflows are not implemented.
- Company edit mutations are not implemented.
- Document byte storage, malware scanning, retention, and persisted audit remain future Documents work.
- RFQ, quotes, PO, and SO dedicated workflows remain future modules.
## Persistence Foundation Phase 2

Company 360 remains deployed as a static/sample read-model screen. Locally, the Express API now has tenant-scoped Company and Contact CRUD foundation routes backed by repository contracts, validation, and selectable memory/PostgreSQL providers:

- `GET/POST /v1/companies`
- `GET/PATCH /v1/companies/:id`
- `GET/POST /v1/companies/:companyId/contacts`
- `PATCH /v1/contacts/:id`

PostgreSQL mode is local/dev only and requires explicit `DATABASE_URL` configuration. The static frontend does not connect to it unless `persistent-api` mode is selected locally.

This does not make deployed Company 360 mutations operational. Create/edit company and create/edit contact are still workflow-boundary UI actions in the static frontend until the API runtime, dedicated SaaS database, production RBAC, and audit persistence are deployed.
