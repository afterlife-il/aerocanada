# System State

Last updated: 2026-07-15

- Staging status: isolated `saas-aviation-staging` topology deployed side-by-side and healthy on Ready2Go; public DNS/TLS activation remains pending
- Product identity: Ready2Go / SaaS_Aviation; initial tenant `aci770`, slug `/AeroCanada`
- Local staging proof: five healthy containers, migrations 001-003, repeat seed, login, OpenAPI, Company 360, and PostgreSQL persistence after API restart
- Server disk gate: passed after owner-approved minimal cleanup; last verified free space was 15,299,883,008 bytes, protected backups and application data untouched
- Authentication limitation: staging administrator and bearer sessions remain in memory; re-login is required after API restart

- Current branch: `main`
- Current HEAD: `c667f284101272b7b987abe91501d4f79dd487dd` before this documentation commit
- Origin HEAD: `c667f284101272b7b987abe91501d4f79dd487dd` before this documentation commit
- Current module: Company 360 persistent staging foundation, deployed and runtime-validated
- Next module: persistent authentication/session architecture and deployment prerequisites; do not start Part 360 from this sprint
- Docker status: Docker Desktop 29.6.1, Linux/WSL2 engine running
- PostgreSQL status: local PostgreSQL 16 healthy at `127.0.0.1:55432`; migrations 001 and 002 applied
- Frontend status: static/sample-backed public frontend unchanged; local `persistent-api` Company workspace implemented; production build passed
- API status: Express Company/Contact/Address/Part/Stock CRUD is deployed to isolated staging and PostgreSQL-persistent; auth/session storage is still in-memory and not production-grade
- Deployment status: staging runtime deployed at `aviation.ready2go.aero`; DNS is NXDOMAIN and TLS is not issued, so only forced-host validation is complete. Yoyamic, legacy PHP, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack remain untouched and operational
- Current sprint: Company 360 Production Hardening Phase 1.1: login contract, form normalization, Contact/Address UI, aggregate truthfulness, workflow boundaries, OpenAPI, tests, and documentation
- Known blockers: public DNS/TLS, persistent users/sessions, production password/identity provider, MFA, rate limiting, secure production session strategy, persistent Documents, commercial modules, monitoring, and visual browser validation
- Last server validation: five new containers healthy; migrations 001-003 applied and idempotent; repeat seed; login; OpenAPI; persistent CRUD; API-restart persistence/re-login; tenant isolation; backup and independent restore rehearsal; forced-host proxy routes/assets; protected systems healthy. Browser automation was unavailable

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
