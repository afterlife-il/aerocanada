# System State

Last updated: 2026-07-15

- Staging status: isolated `saas-aviation-staging` topology publicly available over verified HTTPS at `aviation.ready2go.aero`
- Product identity: Ready2Go Aviation SaaS platform; initial tenant `aci770`, slug `/AeroCanada`; repository/resource renaming is intentionally deferred
- Local staging proof: five healthy containers, migrations 001-003, repeat seed, login, OpenAPI, Company 360, and PostgreSQL persistence after API restart
- Server disk gate: passed; last verified free space was 15,288,799,232 bytes, protected backups and application data untouched
- Authentication limitation: staging administrator and bearer sessions remain in memory; re-login is required after API restart

- Current branch: `main`
- Current HEAD: `442810768a94216135609529ff0d7192b152b70f` before this documentation commit
- Origin HEAD: `442810768a94216135609529ff0d7192b152b70f` before this documentation commit
- Current module: Company 360 persistent staging foundation, deployed and runtime-validated
- Next module: persistent authentication/session architecture and deployment prerequisites; do not start Part 360 from this sprint
- Docker status: Docker Desktop 29.6.1, Linux/WSL2 engine running
- PostgreSQL status: local PostgreSQL 16 healthy at `127.0.0.1:55432`; migrations 001 and 002 applied
- Frontend status: static/sample-backed public frontend unchanged; local `persistent-api` Company workspace implemented; production build passed
- API status: Express Company/Contact/Address/Part/Stock CRUD is deployed to isolated staging and PostgreSQL-persistent; auth/session storage is still in-memory and not production-grade
- Deployment status: staging runtime publicly available at `https://aviation.ready2go.aero`; DNS resolves through server/Google/Cloudflare and Let's Encrypt TLS verifies. Public routes, API, OpenAPI, assets, authenticated login, and persistent reads passed. Yoyamic, legacy PHP, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack remain untouched
- Current sprint: Company 360 Production Hardening Phase 1.1: login contract, form normalization, Contact/Address UI, aggregate truthfulness, workflow boundaries, OpenAPI, tests, and documentation
- Known blockers: interactive browser validation, persistent users/sessions, production password/identity provider, MFA, rate limiting, secure production session strategy, persistent Documents, commercial modules, and monitoring
- Last server validation: authoritative/Cloudflare/Google/server DNS consistent; Let's Encrypt assigned; nginx/Apache valid; required public routes 200; five new containers healthy; public login; Company/Contact/Address create-read-update-delete; Part/Stock create-read-update with quantity zero; targeted cleanup left zero residue. Part/Stock DELETE API routes remain unimplemented. `neo.ready2go.aero` remained valid and unchanged

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
