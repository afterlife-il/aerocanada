# AeroCanada Project Recap

Last updated: 2026-07-15

## Persistent staging preparation

Ready2Go Aviation SaaS is the platform and AeroCanada is tenant 1 (`aci770`, public slug `AeroCanada`). The separate `saas-aviation-staging` Compose topology is deployed side-by-side with dedicated PostgreSQL 16, Redis, MinIO, API, and web containers. Migrations 001-003, repeat tenant seed, authenticated persistent CRUD, API-restart persistence, tenant isolation, backup/restore rehearsal, public routing, and HTTPS validation passed. `aviation.ready2go.aero` resolves publicly to `217.182.69.159` and has a hostname-valid Let's Encrypt certificate. No legacy application, Yoyamic data, MariaDB, host PostgreSQL 14, Odoo, or old Ready2Go service was modified.

## Vision

Yoyamic remains the legacy staging and migration-reference system. `SaaS_Aviation/` is the new multi-tenant aviation ERP product. See `VISION.md`.

CTO-level status snapshot: `SaaS_Aviation/CTO_STATUS.md`.

## Documentation Definition of Done

Every task includes a documentation review, even when no code changes are needed. At minimum, review this recap,
`PROJECT_STATE.json`, and `SaaS_Aviation/CTO_STATUS.md`, plus all affected documents under `docs/`. Record only
verified results, current module completion, remaining work, blockers, next action, validation, deployment truth,
and the relevant commit. The durable agent instructions are in `AGENTS.md`.

## Current Architecture

- Legacy PHP/MySQL app: `pages/`, `classes/`, and live Yoyamic staging deployment.
- New SaaS app: `SaaS_Aviation/` Next.js web app, Express API shell, shared TypeScript package, adapter boundary, auth/audit abstractions.
- Documentation details live under `docs/`.

## Current Sprint

Operate and validate the isolated persistent staging foundation without treating it as production-ready. Company/Contact/Address/Part/Stock persistence and the PostgreSQL password/session foundation are deployed; MFA, identity administration, Documents and commercial workflows remain boundaries.

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
- Company 360 local persistent foundation with PostgreSQL identity, Company/Contact/Address CRUD, search/filter/sort/pagination, related inventory, persisted activity, aligned local login contract, and explicit non-persistent Documents/commercial boundaries. It is a staging candidate only after remaining auth and operational prerequisites; public mode remains sample-static.
- Warehouse architecture design is documented at `docs/architecture/warehouse.md`; no Warehouse production code is implemented.
- Legacy read adapter boundary.
- OpenAPI route contract refinement for future generated clients and validation.
- Permanent project memory.
- Persistent Data Foundation Phase 2 local code: core Company/Contact/Part/Stock schema, repository contracts, in-memory local implementation, PostgreSQL repository provider, explicit migration runner, CRUD API routes, OpenAPI contract updates, hardened Yoyamic read-only query planning, importer dry-run/reconciliation tests, persistent-api client boundary, and documentation. Not deployed.

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

Public persistent staging foundation. Company, Contact, Address, Part, and Stock CRUD run against the dedicated staging PostgreSQL database through `https://aviation.ready2go.aero`. This is not production-ready: users/sessions are in-memory, Documents/commercial modules are non-persistent boundaries, visual browser automation remains unavailable, and production auth/RBAC, audit persistence, rate limiting, monitoring, and secret-management work remains.

## Current Deployments

Report-based Yoyamic staging deployments exist for stock ownership and stock list work. `SaaS_Aviation/` static frontend is deployed to staging at `https://aerocanada-industries.com/SaaS_Aviation/`, most recently for protected CTO Dashboard static frontend commit `bb0ba80` on 2026-07-07. See `docs/deployment/legacy-yoyamic-status.md`.

## Current Working Tree

`main` is active. The deployed topology/image revision is `c667f284101272b7b987abe91501d4f79dd487dd`; local HEAD and `origin/main` matched that revision before this documentation-only commit. The standing untracked local files are the three `.code-workspace` files.

## Known Technical Debt

- Legacy Yoyamic pages remain tightly coupled and mixed with SQL, HTML, and workflow logic.
- Some staging deployment truth is report-based and should be reverified before any deployment.
- Authenticated Yoyamic visual verification has been blocked by login/session access.
- `APP_RECAP.md` and `PROJECT_STATE.json` were introduced after earlier work, so older status is reconstructed from reports.

## Current Priorities

1. Keep project memory synchronized.
2. Inventory deployed vs working-tree Yoyamic changes before any future deployment.
3. Continue Auth/Tenant persistence design now that local PostgreSQL migration, reconnect, rollback, and tenant-isolation validation has passed.
4. Use the hardened read-only Yoyamic adapter plans for future source sampling only after approved read-only credentials exist.
5. Define auth, RBAC, tenant isolation, and audit strategy before SaaS mutations.

## Changelog

- 2026-07-15: Implemented the local MFA and phone-enrollment foundation. Migration 006 adds encrypted tenant-bound TOTP factors, hashed one-use recovery codes, E.164 phone factors, expiring five-attempt OTP challenges and expanded authentication audit categories. Login supports a short-lived MFA challenge before session issuance. The staging-only phone adapter writes codes to a configured mode-0600 server spool and never returns or logs them publicly. The full suite passed, including PostgreSQL 22/22 with zero skips, typecheck, lint and build. Deployment is pending; no external SMS provider is configured.
- 2026-07-15: Deployed the persistent password/session foundation from `47fd95c` after backup `/opt/ready2go/saas-aviation/backups/pre-auth-20260715T145902Z`. Migration 005 adds tenant users, salted scrypt credentials, failed-attempt lockout, persistent session digests and authentication audit events. Public acceptance proved secure cookie login, CSRF enforcement, disposable persistent CRUD, session continuity across API restart, logout revocation and post-logout 401. PostgreSQL, Redis and MinIO were not recreated; Yoyamic remained 200. Phone OTP, TOTP and OAuth remain explicit subsequent work.
- 2026-07-15: Reproduced and fixed the owner-visible Company API failure. Public logs proved an unauthenticated 401 while pre-rendered fixture Companies remained visible. Persistent Company HTML and state now contain no fixtures, failures clear rows, and sign-in/retry actions plus correlation references are explicit. Public login and PostgreSQL Company reads passed. Only web `98a9076` and API `3994fb2` were replaced; PostgreSQL, Redis, MinIO, proxy, TLS and protected systems were unchanged.
- 2026-07-15: Completed the controlled Yoyamic Company/Contact/Part migration audit and staging sample. The live source was queried only in a MariaDB read-only session; counts remained 5,884 Companies, 6,389 Company details, 17,502 Contacts, and 93,410 Parts. Migration 004 and a restricted pre-import backup were applied to the dedicated staging PostgreSQL only. A deterministic sample imported 7 Companies, 9 addresses, 13 Contacts, and 7 Parts; a repeat run inserted zero rows and reconciled all 36 records as unchanged. No stock was imported. Full import is blocked by seven unresolved data-quality gates, including normalized duplicates and orphans. Public imported reads and temporary Company/Contact/Address CRUD passed; the probe was fully deleted. Runtime images remain `c667f284`; Yoyamic, legacy PHP, original MariaDB data, Odoo, host PostgreSQL 14, and the old Ready2Go stack were not modified.
- 2026-07-15: Revalidated public DNS through both authoritative nameservers, Cloudflare, Google, and the server resolver; confirmed the existing Let's Encrypt assignment, nginx/Apache configuration, all required HTTPS routes, healthy containers, and unchanged `neo.ready2go.aero`. Public acceptance passed login plus Company/Contact/Address create-read-update-delete and Part/Stock create-read-update with PostgreSQL persistence and quantity zero. Part/Stock DELETE API routes are not implemented; their acceptance records were removed by one strictly targeted staging-database transaction and zero residue was verified. No protected system was touched.
- 2026-07-15: Completed public HTTPS activation for Ready2Go Aviation SaaS. Verified the new A record through the server resolver, Google DNS, Cloudflare DNS, and the authoritative path; issued and assigned `Lets Encrypt aviation.ready2go.aero`; passed TLS, nginx, Apache, public page/API/OpenAPI/assets, authenticated login, and persisted Company/Part/Stock reads. `neo.ready2go.aero` remained valid and unchanged. Browser control failed to initialize, so interactive visual acceptance is still recorded as blocked rather than passing.
- 2026-07-15: Deployed the isolated `saas-aviation-staging` runtime side-by-side on Ready2Go from `c667f284`. Applied migrations 001-003 and the idempotent `aci770` seed; proved authenticated Company/Contact/Address/Part/Stock persistence, quantity zero, independent company relationships, API-restart persistence/re-login, tenant isolation, backup and independent restore, resource limits, and forced-host proxy routing. DNS/TLS and browser validation remain blocked. Protected legacy AeroCanada, Yoyamic, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack remained operational and unchanged.
- 2026-07-14: Hardened local Company 360 persistent workflows. Aligned the login response/client token contract, added logout clearing, normalized blank optional form fields, replaced temporary Contact editing, completed Address editing/primary handling, removed fixture Documents from the persistent aggregate, enriched non-persistent commercial boundaries, and strengthened OpenAPI/tests. Real PostgreSQL tests passed 15/15 with zero skips; local HTTP restart persistence, standard tests, typecheck, lint, build, and diff checks passed. In-app browser automation was unavailable. Auth remains local/in-memory; nothing was pushed or deployed and no Yoyamic, legacy PHP, live database, or Documents internals changed.
- 2026-07-14: Completed the local Company 360 production sprint. Added migration 002, complete Company/Contact/Address CRUD, aviation identity fields, tenant-scoped activity, PostgreSQL inventory aggregation, Documents links, commercial boundaries, `company.manage` authorization, persistent login/UI, OpenAPI routes, tests, and synchronized system/module docs. PostgreSQL tests passed 13/13 with zero skips; authenticated CRUD, typecheck, lint, and build passed. Browser automation remained blocked by unavailable/timing-out tooling. Nothing was pushed or deployed; Yoyamic, legacy PHP, and live databases were untouched.
- 2026-07-14: Verified the existing local PostgreSQL path with Docker Desktop/WSL2. Applied migration 001 with a recorded checksum, proved idempotent re-apply, and passed the real PostgreSQL integration suite with reconnect persistence for Company/Contact/Part/Stock, quantity 0, independent stock-company relationships, tenant isolation, rollback, and local API/repository restart coverage. Fixed module-relative migration discovery for npm workspaces and strengthened the runtime test. The public frontend remains static/sample-backed; no API or PostgreSQL deployment, push, Yoyamic access, legacy PHP change, live database access, or credential commit occurred.
- 2026-07-13: Hardened the Yoyamic read-only migration adapter locally. Added SELECT/SHOW-only SQL validation, multi-statement/write/locking/file-write rejection, bounded tenant-aware read options, canonical company/contact/part/stock query plans, batch pagination planning, deterministic legacy mapping checksums, reconciliation summaries, tests, and migration docs. No live Yoyamic query, credential, write, import, database change, legacy PHP change, deployment, or push was performed.
- 2026-07-10: Added Persistent Data Foundation Phase 2 locally. Implemented explicit memory/postgres provider selection, native `pg` PostgreSQL repository for Company/Contact/Part/Stock, deterministic migration status/apply runner with checksums, tenant-composite schema constraints including part alternates, persistent-api frontend client boundary with no sample fallback, and Yoyamic read-only source policy scaffold with no live queries or writes. Added PostgreSQL integration tests gated on `TEST_DATABASE_URL`/`DATABASE_URL`; this machine had no PostgreSQL runtime or database URL, so real DB apply/restart verification remains blocked until a local test DB is provisioned. No deployment, push, Yoyamic change, live DB change, legacy PHP change, or Express API deployment was performed.
- 2026-07-10: Hardened the PostgreSQL persistence provider path with localhost-only Docker Compose dev configuration, root GitHub Actions PostgreSQL integration job, npm `db:*`/`test:postgres` scripts, migration idempotency and checksum mismatch test coverage, transaction rollback test coverage, and updated local Postgres docs. Local DB execution remains blocked on this machine because no postgres/psql/Docker/Podman/WSL/database URL is available.
- 2026-07-10: Added the Warehouse architecture design under `docs/architecture/warehouse.md`. The document defines the future physical warehouse execution layer, append-only movement ledger, location hierarchy, condition/disposition separation, receiving/inspection/put-away/pick/pack/ship/transfer/count/adjustment/audit/quarantine-release workflows, Documents boundaries, and Yoyamic read-only migration stance. Documentation only; no Warehouse production code, schema, API route, UI workflow, deployment, Yoyamic change, or live DB change was made.
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

Resolve the seven Yoyamic full-import blockers with an approved, documented duplicate/orphan disposition process, then repeat dry-run and reconciliation before requesting full import approval. Do not run the full import while any gate remains. Production readiness still requires persistent authentication/session storage, MFA, rate limiting, secure cookies/CSRF, persistent audit, monitoring, and secret management.
