# System State

Last updated: 2026-07-15

- Staging status: isolated `saas-aviation-staging` topology publicly available over verified HTTPS at `aviation.ready2go.aero`
- Product identity: Ready2Go Aviation SaaS platform; initial tenant `aci770`, slug `/AeroCanada`; repository/resource renaming is intentionally deferred
- Local and public staging proof: five healthy containers, migrations 001-005, login, OpenAPI, Company 360, PostgreSQL persistence, and persistent cookie-session continuity after API restart
- Server disk gate: passed; 15,032,721,408 bytes remained after the persistent-auth acceptance
- Authentication status: PostgreSQL users, salted scrypt credentials, tenant-bound sessions, CSRF, lockout and authentication audit are deployed to staging; OTP/TOTP/OAuth not yet implemented

- Current branch: `main`
- Current HEAD: `47fd95c` before this documentation commit
- Origin HEAD: `47fd95c` before this documentation commit
- Current module: TOTP/recovery-code and provider-abstracted phone OTP foundation implemented locally; focused staging rollout pending
- Next module: deploy migration 006 and affected API, validate MFA without enabling it on the owner account, then implement OAuth provider architecture
- Docker status: Docker Desktop 29.6.1, Linux/WSL2 engine running
- PostgreSQL status: local PostgreSQL 16 healthy at `127.0.0.1:55432`; migrations 001-006 applied locally; migrations 001-005 applied to dedicated staging PostgreSQL
- Frontend status: static/sample-backed public frontend unchanged; local `persistent-api` Company workspace implemented; production build passed
- API status: Express Company/Contact/Address/Part/Stock CRUD and the password/session foundation are deployed to isolated staging and PostgreSQL-persistent; MFA and production identity operations remain incomplete
- Deployment status: staging runtime publicly available at `https://aviation.ready2go.aero`; DNS resolves through server/Google/Cloudflare and Let's Encrypt TLS verifies. Public routes, API, OpenAPI, assets, authenticated login, and persistent reads passed. Yoyamic, legacy PHP, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack remain untouched
- Current sprint: Company 360 Production Hardening Phase 1.1: login contract, form normalization, Contact/Address UI, aggregate truthfulness, workflow boundaries, OpenAPI, tests, and documentation
- Known blockers: in-app browser bootstrap failure, production SMS/OAuth credentials, password reset/user administration, production secret management, global rate limiting, persistent Documents, commercial modules, and monitoring
- Last server validation: public login 200; secure cookie session 200; missing CSRF 403; authorized Company create/delete 201/200; same session after API restart 200; logout 200 and post-logout access 401; five containers healthy; Yoyamic 200

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
