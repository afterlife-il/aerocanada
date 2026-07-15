# System State

Last updated: 2026-07-15

- Staging status: isolated `saas-aviation-staging` topology publicly available over verified HTTPS at `aviation.ready2go.aero`
- Product identity: Ready2Go Aviation SaaS platform; initial tenant `aci770`, slug `/AeroCanada`; repository/resource renaming is intentionally deferred
- Local and public staging proof: five healthy containers, migrations 001-005, login, OpenAPI, Company 360, PostgreSQL persistence, and persistent cookie-session continuity after API restart
- Server disk gate: passed for the focused web rebuild at 15,546,359,808 bytes after pruning only regenerable BuildKit, npm and unused pnpm-store cache
- Authentication status: PostgreSQL password/session auth plus encrypted TOTP, recovery-code and provider-abstracted phone-enrollment foundations are deployed; owner MFA is disabled, production SMS and OAuth are not configured

- Current branch: `main`
- Current HEAD: `47fd95c` before this documentation commit
- Origin HEAD: `47fd95c` before this documentation commit
- Current module: persistent Part and Stock workspaces deployed without fixture `/360` adapters
- Next module: implement audited archive/restore lifecycle and complete Company remaining fields
- Docker status: Docker Desktop 29.6.1, Linux/WSL2 engine running
- PostgreSQL status: local PostgreSQL 16 healthy at `127.0.0.1:55432`; migrations 001-006 applied locally and to dedicated staging PostgreSQL
- Frontend status: static/sample-backed public frontend unchanged; local `persistent-api` Company workspace implemented; production build passed
- API status: Express Company/Contact/Address/Part/Stock CRUD and the password/session foundation are deployed to isolated staging and PostgreSQL-persistent; MFA and production identity operations remain incomplete
- Deployment status: staging runtime publicly available at `https://aviation.ready2go.aero`; DNS resolves through server/Google/Cloudflare and Let's Encrypt TLS verifies. Public routes, API, OpenAPI, assets, authenticated login, and persistent reads passed. Yoyamic, legacy PHP, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack remain untouched
- Current sprint: Company 360 Production Hardening Phase 1.1: login contract, form normalization, Contact/Address UI, aggregate truthfulness, workflow boundaries, OpenAPI, tests, and documentation
- Known blockers: public deployment and visual acceptance of the locally corrected Company/root-asset export remain pending; production SMS/OAuth credentials; password reset/user administration; production secret management; global rate limiting; persistent Documents; commercial modules; and monitoring
- Last server validation: all enumerated public pages/health/OpenAPI/provider routes returned 200; protected Company/Part/Stock APIs returned 401 unauthenticated; login/logout, secure cookies, Company CRUD cleanup, persistent session and Company/Part/Stock reads through API restart passed; PostgreSQL held 13/8/1 Company/Part/Stock rows; five containers healthy; Yoyamic 200; Connected Mailboxes not started

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
