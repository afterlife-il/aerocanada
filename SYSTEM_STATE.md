# System State

Last updated: 2026-07-14

- Current branch: `main`
- Current HEAD: the current `Harden Company 360 persistent workflows` commit (`git rev-parse HEAD` is authoritative)
- Origin HEAD: `5862d57` (`origin/main`)
- Current module: Company 360 local persistent foundation, hardened and validated locally
- Next module: persistent authentication/session architecture and deployment prerequisites; do not start Part 360 from this sprint
- Docker status: Docker Desktop 29.6.1, Linux/WSL2 engine running
- PostgreSQL status: local PostgreSQL 16 healthy at `127.0.0.1:55432`; migrations 001 and 002 applied
- Frontend status: static/sample-backed public frontend unchanged; local `persistent-api` Company workspace implemented; production build passed
- API status: local Express Company 360 API uses aligned login/session envelopes and `company.read`/`company.manage`; auth/session storage is still in-memory and not production-grade; not deployed
- Deployment status: no push or deployment; Yoyamic, legacy PHP, and live databases untouched
- Current sprint: Company 360 Production Hardening Phase 1.1: login contract, form normalization, Contact/Address UI, aggregate truthfulness, workflow boundaries, OpenAPI, tests, and documentation
- Known blockers: persistent users/sessions, production password/identity provider, MFA, rate limiting, secure production session strategy, persistent Documents, commercial modules, monitoring, backup/restore, deployment validation, and visual browser automation
- Last validation: migrations 001/002 current; PostgreSQL suite 15/15 passed with zero skips; local HTTP login/CRUD/API restart persistence passed; standard tests, typecheck, lint, production build, JSON parse, and diff checks passed; browser automation was unavailable

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
