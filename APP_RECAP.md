# AeroCanada Project Recap

Last updated: 2026-07-02

## Vision

Yoyamic remains the legacy staging and migration-reference system. `SaaS_Aviation/` is the new multi-tenant aviation ERP product. See `VISION.md`.

## Current Architecture

- Legacy PHP/MySQL app: `pages/`, `classes/`, and live Yoyamic staging deployment.
- New SaaS app: `SaaS_Aviation/` Next.js web app, Express API shell, shared TypeScript package, adapter boundary, auth/audit abstractions.
- Documentation details live under `docs/`.

## Current Sprint

Stabilize project memory and continue SaaS foundation work without restarting from zero. The API contract is now being formalized before real legacy data integration.

## Active Tracks

- Track A: SaaS architecture and API contracts.
- Track B: read-only legacy adapter planning.
- Track C: aviation ERP UX system.
- Track D: security, tenant isolation, RBAC, audit.
- Track E: documentation and deployment inventory.

## Completed Modules

- SaaS foundation scaffold in `SaaS_Aviation/`.
- Login shell, tenant-aware ERP dashboard, Company 360 shell, Part 360 read-model foundation, Stock 360 read-model foundation, Company Inventory read-model foundation, internal stock list, external stock list.
- Sample-data route handlers and Express read routes, including Documents metadata and upload-intent validation contracts.
- OpenAPI component schemas for current Express read routes.
- Yoyamic stock ownership and tag-info display work, with several staging deployments documented in reports.

## Modules in Progress

- Stock 360 SaaS workspace with read-only tenant-scoped action boundaries.
- Company 360 SaaS workspace with Company Inventory read-model integration.
- Legacy read adapter boundary.
- OpenAPI route contract refinement for future generated clients and validation.
- Permanent project memory.

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

Early foundation. The app builds, has sample read screens, and the static frontend is deployed to staging for visual inspection. Real legacy data integration, auth, RBAC, tenant scope, audit persistence, and server-side deployment design remain.

## Current Deployments

Report-based Yoyamic staging deployments exist for stock ownership and stock list work. `SaaS_Aviation/` static frontend is deployed to staging at `https://aerocanada-industries.com/SaaS_Aviation/`, most recently for the Documents Phase 1 UI on 2026-07-02. See `docs/deployment/legacy-yoyamic-status.md`.

## Current Working Tree

Dirty and uncommitted. Modified legacy PHP files, many untracked reports, untracked `SaaS_Aviation/`, and new project memory/docs files are present.

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

Create read-only legacy adapter mapping for dashboard/ERP workflows, then harden Auth/Tenant persistence before any mutations.
