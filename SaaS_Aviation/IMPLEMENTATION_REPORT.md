# AeroCanada Aviation SaaS ERP - Foundation Implementation Report

Date: 2026-06-29

## Scope Completed

Created a new isolated project in `SaaS_Aviation/`. No legacy Yoyamic PHP files were modified, deployed, or restarted.

Implemented the first runnable foundation for a multi-tenant aviation SaaS ERP:

- Next.js / TypeScript web application
- Express / TypeScript REST API shell
- Shared TypeScript domain package
- Legacy database adapter boundary
- Auth provider abstraction
- Audit log abstraction
- OpenAPI placeholder
- Docker and local dev structure
- GitHub Actions CI placeholder
- Aviation ERP documentation set
- First usable ERP screens with sample-adapter data

## Stack Decision

Selected stack:

- Frontend: Next.js App Router, TypeScript, Tailwind CSS, reusable ERP components
- Backend: Express TypeScript REST API shell for the first foundation
- Shared contracts: internal workspace package
- Data: adapter-first architecture, initially sample data matching legacy Yoyamic business rules
- Future-ready: PostgreSQL migration path, provider-based auth, OpenAPI, audit logs, Docker

Tailwind CSS was pinned to the stable v3 pipeline because the host currently runs Node 18.20.8 and Tailwind v4 native oxide dependencies require Node 20+ on this environment.

## Product / UX Result

Built a dense professional ERP shell, not a marketing site:

- Sidebar navigation
- Top search bar
- Page headers
- KPI summary cards
- Filter bars
- Dense data tables
- Status badges
- Detail panels
- Entity timeline
- Sticky action bar
- Company 360 shell
- Part Number 360 shell
- Stock 360 shell

Current screens:

- `/login`
- `/dashboard`
- `/companies`
- `/companies/company-5263`
- `/parts`
- `/parts/part-1`
- `/stock/internal`
- `/stock/internal/stock-1`
- `/stock/external`

## Business Rules Preserved

The sample adapter and tests explicitly preserve these legacy constraints:

- `RFQ_ID` remains the business workflow key.
- `quote_id` is not used to replace `RFQ_ID`.
- Internal stock and external stock are separated.
- Owner / Company and Tag Info remain independent.
- Qty `0` remains visible as `0`.
- No ownership inference or silent ACI770 backfill exists in the new foundation.
- Legacy data access is adapter-based and read-only by design until approved.

## API Foundation

Implemented local web API handlers:

- `GET /api/health`
- `GET /api/companies`
- `GET /api/parts`
- `GET /api/stock/internal`
- `GET /api/stock/external`
- `GET /api/audit`

Implemented separate API app shell:

- `GET /health`
- `GET /openapi.json`
- `GET /v1/session`
- `GET /v1/companies`
- `GET /v1/parts`
- `GET /v1/stock/internal`
- `GET /v1/stock/external`
- `GET /v1/audit`

## Validation

Commands run from `SaaS_Aviation/`:

- `npm install`: passed with Node engine warning from `eslint-visitor-keys`.
- `npm run lint`: passed.
- `npm run typecheck`: passed.
- `npm test`: passed.
- `npm run build`: passed.
- `npm audit --omit=dev`: reports two moderate advisories through Next's bundled PostCSS dependency. npm suggests `--force`, which would apply a breaking downgrade, so no blind fix was applied.

## Screenshots Captured

Saved under `SaaS_Aviation/screenshots/`:

- `dashboard.png`
- `companies.png`
- `companies_company-5263.png`
- `parts.png`
- `parts_part-1.png`
- `stock_internal.png`
- `stock_internal_stock-1.png`

Visual inspection completed for:

- Dashboard: loaded, dense ERP shell visible, RFQ queue and audit panel rendered.
- Stock 360: loaded, Owner/Company and Tag Info displayed independently, no obvious overlap.

## Known Placeholders

These are intentionally not implemented yet:

- Real legacy MySQL credentials and queries
- Production auth provider
- MFA/TOTP/passkeys
- Tenant isolation enforcement
- File upload and certificate storage
- Virus scanning
- Real OpenAPI route schemas
- Real RFQ / quote / PO mutation workflows
- Real stock lifecycle movement writes
- Notification delivery
- AI features

## Security Notes

Current security posture is foundation-only:

- No hardcoded production secrets were added.
- `.env.example` uses local placeholder values only.
- Auth is a mock bridge behind an interface.
- API uses Helmet and CORS defaults in the API shell.
- Audit service abstraction is present.
- Tenant isolation is documented but not enforced yet.

Dependency watch:

- `npm audit --omit=dev` reports moderate PostCSS advisories via Next. Do not use `npm audit fix --force` without reviewing Next version impact.

## Deployment Status

- No production deployment.
- No legacy Yoyamic deployment.
- No database writes.
- No schema changes.
- No service restart.

## Next Recommended Priorities

1. Replace sample adapter with approved read-only legacy MySQL adapter queries.
2. Add typed OpenAPI schemas for the existing read routes.
3. Add real Company 360 and Stock 360 data aggregation from Yoyamic.
4. Choose auth provider strategy: Clerk, Supabase Auth, Firebase, Auth0, or custom bridge.
5. Define tenant isolation strategy before any write workflows.
6. Add document/certificate storage architecture with malware scanning.
7. Add RFQ_ID-centered RFQ / quote / PO read-only timeline.
