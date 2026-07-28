# Company 360

Last updated: 2026-07-14

## Scope

Company 360 is a hardened local persistent foundation backed by PostgreSQL in explicit `persistent-api` mode. It is not production-ready. The public deployment remains read-only and sample-backed; no API or database was deployed.

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
- Documents workflow boundary; persistent mode does not display fixture Documents as durable Company records.
- Commercial activity panels for RFQ, Supplier Quotes, Customer Quotes, Purchase Orders, and Sales Orders.
- Activity panel with a proper empty state.
- Workflow boundary panel for all mutation/workflow entry points.

## Workflow Boundaries

Company identity, address, and contact mutations persist locally. Unfinished commercial and document-storage modules remain explicit boundaries and are not faked.

- Edit/Delete Company: implemented with stock-reference deletion protection.
- Create/Edit/Delete Contact: implemented.
- Create/Delete Company Address: implemented; API also supports address update and a single primary address.
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

## Verified Local Persistent Scope

- Identity: name, legal name, code, ICAO, IATA, VAT, country, legacy address fields, multiple addresses, website, notes, roles, tags, status, risk, and audit fields.
- Contacts: multiple contacts with position, email, phone, mobile, status, notes, and full CRUD.
- Search: tenant-scoped fast search across identity/codes/VAT/contact fields, status/role filters, sorting, and pagination.
- Inventory: related PostgreSQL stock through independent owner, supplier, tag-info, and traceability relationships.
- Documents: explicit non-persistent boundary only; durable Company document links/storage are not implemented.
- Activity: persisted Company/Contact/Address activity plus clean RFQ, quote, order, stock, and document categories.
- Quick actions: Edit Company, Create Contact, Create RFQ boundary, Upload Document boundary, and Open Inventory.

## Remaining Work and Boundaries

- Real Yoyamic read adapter queries are still pending.
- The public static build remains read-only; persistent mode requires the local API and bearer session.
- Document byte storage, malware scanning, retention, and persisted audit remain future Documents work.
- RFQ, quotes, PO, and SO dedicated workflows remain future modules.
- Authentication uses local sample users and in-memory sessions; sessions are intentionally lost on API restart.
- Persistent users/sessions, production password/identity provider, MFA, rate limiting, secure production session strategy, monitoring, backup/restore, and deployment validation remain blockers.
## Persistence Foundation Phase 2

Company 360 remains deployed as a static/sample read-model screen. Locally, the Express API now has tenant-scoped Company and Contact CRUD foundation routes backed by repository contracts, validation, and selectable memory/PostgreSQL providers:

- `GET/POST /v1/companies`
- `GET/PATCH /v1/companies/:id`
- `GET/POST /v1/companies/:companyId/contacts`
- `PATCH /v1/contacts/:id`

PostgreSQL mode is local/dev only and requires explicit `DATABASE_URL` configuration. The static frontend does not connect to it unless `persistent-api` mode is selected locally.

Validation on 2026-07-14 applied migration 002 and passed the real PostgreSQL suite without skips. On 2026-07-15 the isolated staging runtime passed authenticated Company/Contact/Address CRUD, reconnect persistence/re-login, tenant isolation, backup/restore, and forced-host proxy validation. Browser automation and public validation remain blocked by tooling plus missing DNS/TLS.

## Phase 3 Notes Workflow - 2026-07-28

Company 360 now has a distinct operational-notes workflow in persistent mode. Authorized users can create, edit, pin, unpin, and delete tenant-scoped notes. Each mutation records a Company activity event; validation rejects blank notes and limits bodies to 5,000 characters. This is separate from the single Company profile `notes` field and supports multiple auditable working notes.

The memory/API/UI tests, typecheck, lint, and production build pass. Docker Desktop was restored on 2026-07-28; migration 007 applied with a checksum-ledger entry, its second application was idempotent, and the PostgreSQL suite passed 23/23 with zero skips. The safe isolated `aci770` scenario covers note create/edit/pin, repository/API restart persistence, activity events, unpin/delete, read-only denial, and cross-tenant denial. Playwright/Chromium browser acceptance passed locally through the system-level `agent-browser` fallback, including desktop/mobile rendering, empty/error states, activity, restart persistence, and the CTO evidence view. Public staging acceptance remains pending, so no completion weight was added yet.
