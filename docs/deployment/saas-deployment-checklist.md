# SaaS_Aviation Deployment Checklist

Last updated: 2026-06-30

## Target

Deploy only to:

`https://aerocanada-industries.com/SaaS_Aviation/`

Filesystem target:

`/var/www/vhosts/aerocanada-industries.com/httpdocs/SaaS_Aviation/`

Never deploy to:

`/var/www/vhosts/aerocanada-industries.com/httpdocs/yoyamic/`

## Required Checks Before Deploy

Run from `SaaS_Aviation/`:

```bash
npm run lint
npm run typecheck
npm test
npm run build
```

## Smoke Tests

Static route smoke tests:

- `/SaaS_Aviation/`
- `/SaaS_Aviation/login/`
- `/SaaS_Aviation/dashboard/`
- `/SaaS_Aviation/companies/`
- `/SaaS_Aviation/stock/internal/`

Auth/Tenant smoke tests:

- Login API returns a session for a valid seeded admin in non-production foundation mode.
- Login API rejects invalid credentials.
- Protected API reads return `401` without bearer session.
- Protected API reads return only records matching the session tenant.
- Static login page builds under `basePath: /SaaS_Aviation`.

## Documentation Gate

Before deployment, update:

- `APP_RECAP.md`
- `PROJECT_STATE.json`
- relevant `docs/` files

## Deploy Recommendation

Do not deploy if lint, typecheck, tests, build, route smoke tests, or tenant isolation smoke tests fail.
