# AeroCanada Project Recap

Last updated: 2026-07-10

## Vision

Yoyamic remains the legacy staging and migration-reference system. `SaaS_Aviation/` is the new multi-tenant aviation ERP product. See `VISION.md`.

CTO-level status snapshot: `SaaS_Aviation/CTO_STATUS.md`.

## Current Architecture

- Legacy PHP/MySQL app: `pages/`, `classes/`, and live Yoyamic staging deployment.
- New SaaS app: `SaaS_Aviation/` Next.js web app, Express API shell, shared TypeScript package, adapter boundary, auth/audit abstractions.
- Documentation details live under `docs/`.

## Current Sprint

Build SaaS_Aviation Persistent Data Foundation Phase 2 locally: native PostgreSQL provider, explicit migration runner, tenant-scoped local persistence integration, persistent-api frontend client boundary, and Yoyamic read-only adapter preparation. No deployment.

## Active Tracks

- Track A: SaaS architecture and API contracts.
- Track B: read-only legacy adapter planning.
- Track C: aviation ERP UX system.
- Track D: security, tenant isolation, RBAC, audit.
- Track E: documentation and deployment inventory.

## Completed Modules

- SaaS foundation scaffold in `SaaS_Aviation/`.
- Login shell, tenant-aware ERP dashboard, Company 360 shell, Part 360 business workspace (header, internal/external
  stock, RFQ/supplier/customer quotes, purchase/sales history placeholders, documents & certificates, dedicated
  traceability panel, workflow-boundary quick actions), Stock 360 read-model foundation, Company Inventory
  read-model foundation, internal stock list, external stock list.
- Sample-data route handlers and Express read routes, including Documents metadata and upload-intent validation contracts.
- OpenAPI component schemas for current Express read routes.
- Yoyamic stock ownership and tag-info display work, with several staging deployments documented in reports.
- Internal CTO Dashboard at `/admin/cto` in the SaaS frontend (dev-team only, not part of the customer ERP). The deployed `/SaaS_Aviation/admin/` path is protected by Apache Basic Auth. See `SaaS_Aviation/CTO_STATUS.md`.

## Modules in Progress

- Part 360 workspace completed as a read-only aggregation layer over Stock/RFQ/Quote/Order/Documents/Audit data;
  Purchase History and Sales History remain explicit placeholder panels pending real PO/SO modules.
- Stock 360 SaaS workspace with read-only tenant-scoped action boundaries.
- Company 360 SaaS workspace with list search/filter/sort, contacts, inventory, documents, commercial activity placeholders, and workflow boundaries.
- Legacy read adapter boundary.
- OpenAPI route contract refinement for future generated clients and validation.
- Permanent project memory.
- Persistent Data Foundation Phase 2 local code: core Company/Contact/Part/Stock schema, repository contracts, in-memory local implementation, PostgreSQL repository provider, explicit migration runner, CRUD API routes, OpenAPI contract updates, importer dry-run/reconciliation tests, persistent-api client boundary, and documentation. Not deployed.

## Pending Modules

Detailed RFQ, supplier quote, customer quote, purchase orders, sales orders, repair, exchange, leasing, loan,
inventory lifecycle, AI workflows, tenant administration, RBAC, and observability modules. Documents/certificates
now have a Phase 1 metadata, link, reusable UI, and upload-validation foundation; object storage and scanning are
not implemented yet.

## Business Rules

- `RFQ_ID` remains the business workflow key.
- `quote_id` may be used only for email idempotency.
- Owner / Company and Tag Info are independent.
- Qty `0` must remain visible as `0`.
- Stock lifecycle changes must be explicit and auditable.

## Security Status

Foundation only. No production auth, MFA, tenant isolation enforcement, persisted audit logs, rate limiting, or secret manager integration is complete yet. No hardcoded production secrets were added in the SaaS foundation.

## SaaS Readiness

Early foundation. The app builds, has sample read screens, and the static frontend is deployed to staging for visual inspection with the admin path protected by Basic Auth. Local persistent CRUD foundations now exist for Company, Contact, Part, and Stock with memory and PostgreSQL providers, but the API runtime and database are not deployed. Real legacy data integration, production auth/RBAC, audit persistence, and server-side deployment design remain.

## Current Deployments

Report-based Yoyamic staging deployments exist for stock ownership and stock list work. `SaaS_Aviation/` static frontend is deployed to staging at `https://aerocanada-industries.com/SaaS_Aviation/`, most recently for protected CTO Dashboard static frontend commit `bb0ba80` on 2026-07-07. See `docs/deployment/legacy-yoyamic-status.md`.

## Current Working Tree

Main is the active branch. The standing untracked local workspace files are the three `.code-workspace` files; implementation changes should be committed before deployment.

## Known Technical Debt

- Legacy Yoyamic pages remain tightly coupled and mixed with SQL, HTML, and workflow logic.
- Some staging deployment truth is report-based and should be reverified before any deployment.
- Authenticated Yoyamic visual verification has been blocked by login/session access.
- `APP_RECAP.md` and `PROJECT_STATE.json` were introduced after earlier work, so older status is reconstructed from reports.

## Current Priorities

1. Keep project memory synchronized.
2. Inventory deployed vs working-tree Yoyamic changes before any future deployment.
3. Continue SaaS Phase 2 with read-only legacy adapter planning.
4. Define auth, RBAC, tenant isolation, and audit strategy before SaaS mutations.

## Changelog

- 2026-07-10: Added Persistent Data Foundation Phase 2 locally. Implemented explicit memory/postgres provider selection, native `pg` PostgreSQL repository for Company/Contact/Part/Stock, deterministic migration status/apply runner with checksums, tenant-composite schema constraints including part alternates, persistent-api frontend client boundary with no sample fallback, and Yoyamic read-only source policy scaffold with no live queries or writes. Added PostgreSQL integration tests gated on `TEST_DATABASE_URL`/`DATABASE_URL`; this machine had no PostgreSQL runtime or database URL, so real DB apply/restart verification remains blocked until a local test DB is provisioned. No deployment, push, Yoyamic change, live DB change, legacy PHP change, or Express API deployment was performed.
- 2026-07-10: Added Persistent Data Foundation Phase 1 locally. Created PostgreSQL-compatible core schema migration for tenants, companies, company roles, contacts, part numbers, stock items, and legacy mappings; added shared persistence types, validation schemas, repository interfaces, domain errors, local in-memory repository implementation, Express CRUD endpoints for Company/Contact/Part/Stock, OpenAPI route contracts, explicit web data-source mode, dry-run Yoyamic importer/reconciliation foundation, tests, and persistence/database/API docs. Legacy Yoyamic was inspected only via repository PHP references; no live DB query, DB deployment, API deployment, Yoyamic write, legacy PHP change, or push/deploy was performed.
- 2026-07-07: Completed the Part 360 business workspace as a read-only aggregation layer: a part header (status,
  condition summary, certification indicators, last update), internal/external stock panels with real loading
  (route-level skeleton), empty, and error states, dedicated RFQ/supplier quote/customer quote panels each with a
  workflow-boundary CTA, explicit Purchase History/Sales History placeholder panels, a Documents & Certificates
  panel with an Upload Certificate boundary, and a new dedicated Traceability panel (previous owner, origin,
  serial traceability, repair references, certification chain, event timeline). Added `header` and
  `traceabilitySummary` to the shared `Part360ReadModel` (additive, backward compatible) plus small additive
  `DetailPanel`/`DocumentPanel`/`WorkflowBoundaryPanel`/`EntityTabs`/`StatusBadge` UI extensions reused across the
  page. Updated the OpenAPI `Part360ReadModel` schema and Part 360 business/database docs. No Warehouse, RFQ,
  Supplier Quotes, Customer Quotes, Purchase Orders, Sales Orders, Documents internals, or Company 360 code was
  changed. No deployment performed.
- 2026-07-07: Built an internal CTO Dashboard (`/admin/cto`) in the SaaS_Aviation frontend — global build/deploy
  status, a 22-module status table, blockers, sprint tracking, technical debt, architecture decisions, and a
  commit activity timeline. Dev-team only and not part of the customer ERP; its deployed admin path is now protected by Basic Auth.
  Data is a hand-maintained static snapshot (`apps/web/src/lib/cto-status.ts`), matching `SaaS_Aviation/CTO_STATUS.md`.
- 2026-07-07: Protected the deployed CTO Dashboard under `/SaaS_Aviation/admin/` with Apache Basic Auth, hid the
  public sidebar link, and deployed only the static frontend. No API runtime, DB, Yoyamic, or legacy PHP changes
  were made. Backup: `/var/www/vhosts/aerocanada-industries.com/httpdocs/SaaS_Aviation_backup_20260707_155253`.
- 2026-07-07: Added CTO Dashboard Phase 2 static metadata locally: build metadata, deployment metadata, check
  statuses, security status, and the last 10 commits with author/date/message. Not deployed yet.
- 2026-07-07: Completed the Company 360 foundation locally with a Company list read model, search/filter/sort
  controls, Company 360 KPI/profile/contact/inventory/document/commercial panels, explicit workflow boundaries,
  tests, and Company 360 business/database docs. No Yoyamic, database, or deployment changes were made.
- 2026-07-02: Completed an Aviation Business Architect benchmark of every implemented module (Auth/Tenant,
  Dashboard, Company 360, Part 360, Stock 360, Company Inventory) against IFS Aerospace, Ramco Aviation, Quantum
  Control, AvSight, Traxxall, SAP Aviation, AMOS, Rusada ENVISION, and OASES. Verdict: architecture is strong,
  but the product is pre-MVP functionally — no RFQ/Quote/PO/SO transaction can yet be created, only displayed.
  See `SaaS_Aviation/AVIATION_ERP_BENCHMARK.md`. No code implemented.
- 2026-07-02: Designed the complete Documents ecosystem architecture — entity model, storage/naming strategy,
  malware scanning, versioning, permissions, tenant isolation, preview, OCR, AI document analysis, search/
  indexing, audit trail, retention/archive/soft-delete/legal-hold, API proposal, UI proposal, workflow proposal,
  migration strategy from Yoyamic, risks, and phased recommendations. See `SaaS_Aviation/DOCUMENTS_ARCHITECTURE.md`,
  `docs/business/documents.md`, `docs/database/documents.md`. No code implemented.
- 2026-07-02: Implemented Documents Phase 1 foundation with tenant-scoped document/version/link metadata,
  reusable DocumentPanel and UploadFoundationPanel, Document Center UI, protected API contracts, upload-intent
  validation, tests, and security/business/database docs. No Yoyamic or live DB writes.
- 2026-07-02: Deployed the `SaaS_Aviation` static frontend for Documents Phase 1 to staging. No API runtime,
  Yoyamic, or live database deployment was performed.
- 2026-07-01: Added first tenant-aware ERP dashboard for ACI770 with dashboard service/adapters, workflow fixtures, dense cockpit UI, tests, and business/UI docs. No deployment performed.
- 2026-07-02: Added read-only Part 360, Stock 360, and Company Inventory foundation with tenant-scoped shared read models, API contracts, dense connected UI, workflow boundary panels, tests, and docs. No deployment performed.
- 2026-06-30: Added Auth/Tenant foundation with shared user/tenant/session types, tenant-scoped repository contracts, API password session endpoints, protected business reads, and QA deployment checklist.
- 2026-06-30: Created permanent project recap and machine-readable state.
- 2026-06-30: Added OpenAPI component schemas for current SaaS API read routes.
- 2026-06-30: Deployed `SaaS_Aviation` static frontend to staging for visual inspection.
- 2026-06-29: SaaS foundation reported complete with sample data, screens, route handlers, tests, lint, typecheck, and build.
- 2026-06-24: Yoyamic stock ownership, stock list actions, and change-stock UI work documented.

## Next Sprint

Provision an isolated local PostgreSQL test database, run migration apply/status and PostgreSQL integration tests, then continue Auth/Tenant persistence and read-only legacy adapter mapping before any production mutations.
