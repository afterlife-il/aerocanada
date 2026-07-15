# CTO Status Dashboard - SaaS_Aviation

Last updated: 2026-07-15
Source: `APP_RECAP.md`, `PROJECT_STATE.json`, `git log`, and the 2026-07-07 protected static deploy report.

## Persistent staging preparation

The Ready2Go Aviation SaaS staging topology is deployed side-by-side, healthy, and publicly available over verified HTTPS. It uses five isolated containers, internal-only PostgreSQL/Redis/MinIO/API networking, loopback web publishing, health checks, resource limits, log rotation, migration checksums, and an idempotent tenant seed for `aci770`/`AeroCanada`. Runtime validation proved authenticated CRUD, PostgreSQL persistence, tenant isolation, backup/restore, public DNS, TLS, routing, API/OpenAPI, and persisted reads. Users and sessions remain staging-grade and in memory; Documents storage and commercial workflows remain explicit boundaries.

A protected in-app mirror of this snapshot exists at `/admin/cto` in the static SaaS_Aviation frontend. The frontend
is exported with `output: "export"`, so dashboard data is baked into the static build from
`apps/web/src/lib/cto-status.ts`; it is not queried live. Keep this file, `PROJECT_STATE.json`, `APP_RECAP.md`, and
`apps/web/src/lib/cto-status.ts` in sync by hand.

## Documentation Definition of Done

Every completed task must review and synchronize `APP_RECAP.md`, `PROJECT_STATE.json`, this status file, and all
affected documents under `docs/`. Module updates must state completion, finished and missing work, blockers, next
action, validation actually performed, deployment status, and the relevant commit. Skipped, blocked, failed, or
unexecuted checks must never be reported as passing. See the repository-wide policy in `AGENTS.md`.

## Current Global Status

Pre-MVP persistent staging foundation. The dedicated PostgreSQL database, Express API, Redis, MinIO, and web
runtime are deployed alongside, but isolated from, legacy systems. Company, Contact, Address, Part, and Stock
CRUD are persistent. PostgreSQL users, salted credentials, tenant-bound sessions, lockout, CSRF and authentication
audit are deployed to staging. MFA, production identity administration and broader operational hardening are incomplete.

## Persistent Data Foundation Phase 2

Implemented and deployed to isolated staging:

- PostgreSQL-compatible core schema migration for tenants, companies, company roles, contacts, part numbers, part alternates, stock items, and legacy mappings.
- Shared tenant-scoped repository contracts, validation schemas, and domain errors.
- Local in-memory API repository for CRUD tests and route validation.
- Native `pg` PostgreSQL repository provider selected explicitly by environment.
- Deterministic migration status/apply runner with checksum ledger.
- Express CRUD routes for Company, Contact, Part, and Stock.
- Explicit frontend data-source mode: `sample-static` vs `persistent-api`.
- Persistent API client boundary that refuses to fall back to sample data.
- Controlled Yoyamic importer dry-run and reconciliation foundation.
- Hardened Yoyamic read-only source policy and query planning guardrails; no live query, write method, or credential.

The PostgreSQL suite passed 21/21 with zero skips. On 2026-07-15 the server applied migrations 001-005, seeded tenant `aci770`, and passed authenticated CRUD, quantity 0, independent stock company relationships, tenant isolation, API-restart persistence, backup/restore, public HTTPS, and persistent-auth acceptance. Browser automation did not initialize, so visual browser validation is not claimed.

Yoyamic, legacy AeroCanada, MariaDB, host PostgreSQL 14, Odoo, and the old Ready2Go stack were not modified and remained operational. Plesk received only the new `aviation.ready2go.aero` subdomain and domain-specific reverse-proxy configuration.

## CTO Dashboard Phase 2

This sprint improves only the protected `/admin/cto` dashboard and its static status data. No business modules,
Yoyamic files, database schema, legacy PHP, or Express API runtime were changed or deployed.

The dashboard now includes:

- Build metadata: current branch, latest local commit, latest `origin/main` commit, build timestamp, static export mode.
- Deployment metadata: last deployed commit, deployment date, staging/public static environment, backup path, protected admin status.
- Checks: test, typecheck, lint, build, and last checked timestamp.
- Activity timeline: last 10 commits with hash, date, author, and message.
- Security status: `/admin` protected by Basic Auth, CTO route hidden from public sidebar, no API runtime deployed, no DB/Yoyamic touched.

## Build Metadata

| Field | Value |
|---|---|
| Current branch | `main` |
| Latest local commit | `c667f284` before this documentation commit |
| Latest origin/main commit | `c667f284` before this documentation commit |
| Build timestamp | OCI images created `2026-07-15T10:56:12Z` |
| Static export mode | `Next.js output export` |

This is a static snapshot. The commit that introduces this Phase 2 metadata cannot self-reference its own final hash
without another manual update.

## Deployment Metadata

| Field | Value |
|---|---|
| Last deployed commit | `47fd95c257b5db61e0f2963bf9f5e6f4e264c9f5` |
| Last deployment date | `2026-07-15` |
| Environment | isolated persistent staging; public HTTPS active |
| Public certificate | `Lets Encrypt aviation.ready2go.aero`, valid through 2026-10-13 |
| Backup paths | `/opt/ready2go/saas-aviation/backups/predeploy-20260715T105420Z`, `proxy-prechange-20260715T114203Z`, `staging-20260715T114501Z`, `pre-auth-20260715T145902Z` |
| Runtime path | `/opt/ready2go/saas-aviation/releases/47fd95c257b5db61e0f2963bf9f5e6f4e264c9f5` |

## Checks Snapshot

| Check | Status |
|---|---|
| Tests | passing |
| Typecheck | passing |
| Lint | passing |
| Build | passing |
| Last checked | `2026-07-14T19:00:00+03:00` |

The required PostgreSQL runtime command passed locally on 2026-07-14 with 15 passed, 0 failed, and 0 skipped. Public HTTPS routes, API/OpenAPI/assets, login, Company/Contact/Address create-read-update-delete, and Part/Stock create-read-update with quantity zero passed on 2026-07-15. Part/Stock DELETE API routes do not exist; targeted database cleanup removed the acceptance records and verified zero residue. Visual browser automation was unavailable and was not counted as passing.

## Security Status

- `/SaaS_Aviation/admin/` is protected by Apache Basic Auth on staging.
- The CTO route is hidden from the public sidebar.
- The Express API and dedicated PostgreSQL 16 database are deployed only in the isolated staging stack.
- No Yoyamic, legacy PHP, MariaDB, host PostgreSQL 14, or Odoo changes were made.
- Credentials remain server-side only and are not committed.

## Last 10 Commits

| Commit | Date | Author | Message |
|---|---|---|---|
| `c0d901d` | 2026-07-13 | Afterlife | Harden Yoyamic read-only migration adapter |
| `e473550` | 2026-07-10 | Afterlife | Update project operational status |
| `4df9e14` | 2026-07-10 | Afterlife | Add Warehouse architecture design |
| `11baac6` | 2026-07-10 | Afterlife | Harden PostgreSQL persistence provider |
| `786aaa7` | 2026-07-10 | Afterlife | Implement PostgreSQL persistence provider |
| `8a266ba` | 2026-07-10 | Afterlife | Build persistent data foundation |
| `ba1755f` | 2026-07-09 | Afterlife | Complete Company 360 foundation |
| `7e7fe5e` | 2026-07-08 | Afterlife | Complete Part 360 foundation |
| `d803c9d` | 2026-07-07 | Afterlife | Improve CTO Dashboard metadata |
| `bb0ba80` | 2026-07-07 | Afterlife | Protect CTO Dashboard |

## Module Table

| Module | Status | Progress | Last Commit | Review Status | Deploy Status | Next Action |
|---|---|---|---|---|---|---|
| Core | Operational | 85% | `141fee0` | Not formally reviewed | Static export only | Map to read-only Yoyamic adapter |
| Authentication | Persistent staging foundation | 60% | `47fd95c` | PostgreSQL tests and public restart/logout acceptance passed | Deployed to persistent staging | TOTP/recovery codes, phone OTP, identity administration, rate limiting |
| Dashboard | Foundation | 65% | `05b04d7` | Not reviewed | Not deployed | Map to legacy adapter |
| Company 360 | Persistent staging foundation; not production-ready | 90% | `c667f284` | PostgreSQL 15/15; CRUD/restart/tenant/restore/public HTTPS/login/read passed; browser unavailable | Public persistent staging active | Persistent auth/session and operational prerequisites |
| Part 360 | Workspace + persistent Part create/read/update foundation | 75% | `c667f284` | Public create/read/update passed; DELETE API missing | Persistent staging deployed | Implement authorized DELETE with dependency rules |
| Stock 360 | Persistent Stock create/read/update foundation | 60% | `c667f284` | Public create/read/update and quantity 0 passed; DELETE API missing | Persistent staging deployed | Implement authorized DELETE and audit rules |
| Company Inventory | Foundation | 50% | `366e375` | Not reviewed | Not deployed | Use hardened read-only Yoyamic adapter plans for future mapping |
| Documents | In progress | 40% | `088e9d8` | `46f6e72`, `f14ddd4` reviewed; `088e9d8` pending | Static UI on staging; API runtime not deployed | Review `088e9d8`; hold Phase 2 for persistent audit + API deploy |
| Warehouse | Architecture documented | 10% | local pending commit | Not reviewed | Not deployed | Review `docs/architecture/warehouse.md`; no production code implemented |
| RFQ | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Supplier Quotes | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Customer Quotes | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Purchase Orders | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Sales Orders | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Repair / Exchange / Lease | Planned | 15% | `05b04d7` | - | Not deployed (sample model only) | Design dedicated workspace |
| Accounting | Planned | 10% | `05b04d7` | - | Not deployed (sample model only) | Not yet scoped as a distinct module |
| Reports | Not started | 0% | - | - | - | Not yet scoped |
| Administration | Not started | 0% | - | - | - | Tenant admin / RBAC UI not yet scoped |
| AI | Planned | 5% | - | - | - | No LLM provider selected; tool-layer architecture only |
| API | Persistent staging foundation | 55% | `c667f284` | Health/OpenAPI/auth/CRUD/restart/proxy passed | Isolated staging deployed | Continue auth, audit, and operational hardening before production |
| Security | Persistent auth foundation | 50% | `47fd95c` | CSRF, secure cookies, lockout and auth audit validated | Persistent staging auth deployed; static `/admin` protected | MFA, RBAC administration, rate limiting, secrets manager |
| Multi-Tenant | Foundation | 55% | `c667f284` | Second-tenant API isolation and composite FK rejection passed on staging | Isolated staging deployed | Continue persistent RBAC, RLS and audit design |

## Current Sprint

Company 360 is a deployed persistent staging foundation on PostgreSQL. Phase 1.1 aligns login contracts,
normalizes forms, completes Contact/Address editing, removes fixture Documents from the persistent aggregate, and
keeps commercial modules explicit non-persistent boundaries. Auth/sessions remain in-memory; it is not production-ready.

Persistent authentication is deployed through migration 005: tenant-bound users, salted scrypt credentials, lockout state, session digests and authentication audit events. Browser cookies are HttpOnly/Secure/SameSite and mutations require CSRF validation. Public acceptance proved CSRF rejection/acceptance, disposable CRUD, session continuity through API restart, logout revocation and post-logout denial. OTP, TOTP, OAuth, password reset and user administration remain incomplete.

The public Company failure was traced to an unauthenticated 401 combined with retained pre-rendered fixtures. The deployed correction removes fixture initialization and serialization from persistent mode, clears rows after failure, provides sign-in/retry actions and safe correlation references, and keeps explicit sample mode separate. Public login, PostgreSQL Company reads, unauthorized handling and no-fixture HTML passed. Web is `98a9076`; API is `3994fb2`.

## Yoyamic migration gate — 2026-07-15

Migration 004 and the controlled Company/Contact/Part sample are active only in dedicated staging PostgreSQL. The sample contains 7 Companies, 9 addresses, 13 Contacts, 7 Parts, and no Stock. A second run inserted zero records and reconciled all 36 as unchanged. Public imported reads and temporary Company/Contact/Address CRUD passed. Runtime images remain `c667f284`; no application runtime was rebuilt or redeployed.

Full migration is **blocked**. The dry-run found unresolved missing names, normalized Company and Part/manufacturer collisions, orphan Company details/Contacts/manufacturers, and duplicate emails within Companies. These require approved business disposition rules and a clean repeat dry-run. Yoyamic was read-only and remained operational; legacy PHP, original MariaDB data, Odoo, host PostgreSQL 14, and the old Ready2Go stack were untouched.

## Next Recommended Sprint

Resolve the seven migration data-quality gates through an approved duplicate/orphan review workflow, then repeat
the read-only audit and reconciliation. Full import remains prohibited until every stop condition passes. Continue
persistent Auth/Tenant sessions, secure session handling, MFA, rate limiting, audit, monitoring, and secret
management before production promotion.
