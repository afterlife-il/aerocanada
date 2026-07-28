# System State

## 2026-07-28 Company 360 final-validation continuation

- Branch/HEAD/origin at validation: `main`, `d6f04726131a8ff771275b4aea070f7b27cd812a`, matching before commit.
- Local Docker Desktop/WSL2 PostgreSQL 16 restored at `127.0.0.1:55432`.
- Migration 007 applied via the repository runner with checksum `25a4c322f4247e9f58d6fdb74808eae191197bdb5938557823773146b5f73aee`; re-apply was idempotent.
- PostgreSQL suite: 23 passed, 0 failed, 0 skipped, including masked isolated `aci770` Company-note lifecycle, restart persistence, validation, activity, permissions, and tenant isolation.
- Browser fallback validated: integrated runtime had no backend, but system Chrome and cached Playwright Chromium were usable through `agent-browser` 0.33.1. Company list/detail, note lifecycle, restart persistence, activity, empty/error states, mobile/desktop layout and CTO dimensions passed locally; no application console/network/hydration errors were observed.
- Browser acceptance found and the sprint fixed an async form-reset defect that prevented post-create refresh.
- Company 360 remains 89% pending public staging. No commit, push, or staging deployment had occurred at this checkpoint. Yoyamic, legacy PHP, MariaDB, Odoo, Backup V1/V2, and production databases are unchanged.
- Next action: commit/push, create rollback backup, deploy migration 007 plus affected API/web, and complete public acceptance. Do not start Part 360.

## 2026-07-24 CTO dashboard reconciliation

- Branch/HEAD/origin at task start: `main`, `5580e5653868667de075dc5d24fa6b96beaffbb9`, matching.
- Current module: evidence-based Monitoring / CTO Dashboard.
- Canonical status source: `SaaS_Aviation/module-status.json`.
- Deployment: unchanged while implementation and gates run; web/API/database/Yoyamic/legacy PHP are unchanged.
- Verified: `npm test` 61 passed/0 failed/3 expected PostgreSQL-gated skips; dedicated PostgreSQL 23/23 with zero
  skips; typecheck, lint, isolated clean-install build, and diff check passed. In-place build generated all pages
  but Dropbox locked the final output directory.
- Pending: commit/push, affected web deploy, public protected route and visual-browser validation.
- Deployment blocker: server SSH authentication is unavailable in this session; no runtime was changed.
- Next action: commit/push, then provide an authorized SSH session to deploy only the affected web/status component.

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
- Known blockers: manual visual, hydration and browser-console acceptance remains pending because integrated browser control did not initialize; production SMS/OAuth credentials; password reset/user administration; production secret management; global rate limiting; persistent Documents; commercial modules; and monitoring
- Last server validation: all enumerated public pages/health/OpenAPI/provider routes returned 200; protected Company/Part/Stock APIs returned 401 unauthenticated; login/logout, secure cookies, Company CRUD cleanup, persistent session and Company/Part/Stock reads through API restart passed; PostgreSQL held 13/8/1 Company/Part/Stock rows; five containers healthy; Yoyamic 200; Connected Mailboxes not started
- Corrected web runtime: commit `2ff9534bfc1e83c41a8755d7b54d90fcbd24881d`, image `sha256:b98a6b81ac7c3a71c39bc54600bde76cd9adae04b452d2017a4257b960eb4e33`; public Company HTML contains zero former fixture IDs/names and root-relative CSS/JS return 200 with correct MIME types

Update this file at the end of every task. Use a symbolic description for the task's own commit because a file cannot contain the final hash of the commit that contains it.
