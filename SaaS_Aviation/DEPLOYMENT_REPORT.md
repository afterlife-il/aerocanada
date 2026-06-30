# SaaS_Aviation Staging Deployment Report

Date: 2026-06-30T11:16:25Z

## Target

`https://aerocanada-industries.com/SaaS_Aviation/`

Filesystem target:

`/var/www/vhosts/aerocanada-industries.com/httpdocs/SaaS_Aviation/`

## Scope

Deployed only the built static frontend/app from `SaaS_Aviation/apps/web/out`.

Not touched:

- `/var/www/vhosts/aerocanada-industries.com/httpdocs/yoyamic/`
- legacy PHP files
- database/schema/data
- Yoyamic deployment
- production Yoyamic
- service restarts

## Static Export Changes

Configured the web app for static export under `/SaaS_Aviation/`:

- `output: "export"`
- `basePath: "/SaaS_Aviation"`
- `trailingSlash: true`
- known sample-data dynamic routes via `generateStaticParams()`
- static route-handler markers for sample-data GET handlers

The root `/SaaS_Aviation/` renders the dashboard page directly.

## Pre-Deployment Checks

Passed after static-export configuration:

- `npm run lint`
- `npm run typecheck`
- `npm test`
- `npm run build`

Build output confirmed static export with generated routes including:

- `/dashboard`
- `/companies`
- `/companies/company-5263`
- `/parts`
- `/stock/internal`
- `/stock/internal/stock-1`

## Backup

No prior target folder existed at deployment time, so no backup was created.

## Deployment Actions

Created target folder:

`/var/www/vhosts/aerocanada-industries.com/httpdocs/SaaS_Aviation/`

Copied static export:

`SaaS_Aviation/apps/web/out/.`

Applied web-root ownership and standard permissions:

- owner/group: `aerocanada-industrie_ly0y5wdnawf:psacln`
- directories: `755`
- files: `644`

Folder size: `2.1M`

## URL Verification

Verified with curl:

- `https://aerocanada-industries.com/SaaS_Aviation/` -> `HTTP 200`
- `https://aerocanada-industries.com/SaaS_Aviation/dashboard` -> `HTTP 200`
- `https://aerocanada-industries.com/SaaS_Aviation/companies/company-5263` -> `HTTP 200`
- `https://aerocanada-industries.com/SaaS_Aviation/stock/internal/stock-1` -> `HTTP 200`

## Key Hashes

```text
700bc6d5909f0bfee6d8528f774e06c22237d2bdb1c93be60035426f705b6453  index.html
63422bde9e17678a25f0a44c7443b0d3470107887e463b9745202b5dfafc0895  dashboard/index.html
d7da7c439fcefa4e3f60b1080e3eeb22fac54266d77dba80441b17dff15dc87b  companies/company-5263/index.html
42d551998b9fab424f1c7259ade40f50fce71464d3c45636e6bb1fa74f07adc1  stock/internal/stock-1/index.html
```

## Screenshots

Captured with Playwright under `SaaS_Aviation/screenshots/staging-20260630/`:

- `root.png`
- `dashboard.png`
- `companies.png`
- `company-5263.png`
- `parts.png`
- `stock-internal.png`
- `stock-1.png`

All screenshots are valid `1280 x 720` PNG files.

## Notes

No service restart was performed. The deployed frontend currently uses sample/static data only. Real legacy read integration, auth, RBAC, tenant isolation, and audit persistence remain pending.
