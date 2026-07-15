# Ready2Go SaaS_Aviation deployment journal

No entry contains a credential value. Operator: Codex through the owner-authorized SSH session.

| UTC timestamp | Action | Environment | Result and validation | Rollback point | Naming status | Commit / next action |
|---|---|---|---|---|---|---|
| 2026-07-15T10:52Z | Resource gate and approved cache/log cleanup | Server filesystem | Free space raised from 7.7 GB to 16 GB; protected data untouched | No application state changed | Old Ready2Go names retained for rollback | `01a8019`; build isolated topology |
| 2026-07-15T10:54Z | Pre-deployment backup | Old Docker stack and proxy | Verified `/opt/ready2go/saas-aviation/backups/predeploy-20260715T105420Z` | Complete old-stack archive and dumps | Old `aerocanada-*` names retained | `c667f28`; create clean release |
| 2026-07-15T10:56Z | Linux image build | API and web | OCI labels and image IDs match `c667f284101272b7b987abe91501d4f79dd487dd` | Old stack still running | New `saas-aviation-*` images complete | Satisfy post-build disk gate |
| 2026-07-15T11:18Z | Approved minimal cleanup | Release Git metadata and APT indexes | Exact free space `15,335,415,808` bytes | Release traceable through path, origin and labels | No rename impact | Start isolated stack |
| 2026-07-15T11:29Z | Compose start and health validation | `saas-aviation-staging` | Five healthy containers; only web at `127.0.0.1:8180` | Stop only new project | New resource naming complete; old names deferred | Migrate database |
| 2026-07-15T11:32Z | Migration status/apply/status/re-apply | Dedicated PostgreSQL 16 | 001-003 stable and idempotent; 20 foreign keys | New dedicated volume | Database and role naming complete | Seed tenant |
| 2026-07-15T11:35Z | Seed twice | Tenant and primary Company | Exactly one `aci770`/`AeroCanada` tenant and primary Company/role | Conflict-safe seed | AeroCanada retained as tenant data | Run functional proof |
| 2026-07-15T11:37Z | Loopback CRUD and restart proof | Web/API/PostgreSQL | Login, all persistent CRUD, quantity 0, independent relations, boundaries and restart persistence passed | Old stack remained live | Ready2Go/SaaS_Aviation identity complete | Test isolation |
| 2026-07-15T11:40Z | Tenant isolation proof | API and PostgreSQL | Tenant A got 404 for tenant B Company; cross-tenant relation rejected; tenant B removed | No residual tenant B data | Tenant administration deferred | Configure domain |
| 2026-07-15T11:42Z | Plesk subdomain and proxy | `aviation.ready2go.aero` -> `127.0.0.1:8180` | nginx/Apache valid; forced-host pages, API, auth, PATCH and assets passed | `/opt/ready2go/saas-aviation/backups/proxy-prechange-20260715T114203Z` | Domain complete; DNS/certificate deferred | Add A record and certificate |
| 2026-07-15T11:45Z | Backup and restore rehearsal | New staging | `/opt/ready2go/saas-aviation/backups/staging-20260715T114501Z`; restore counts `3|1|6|1|1|1`; temporary DB removed | Verified dump and metadata | No rename impact | Complete public DNS validation |
| 2026-07-15T11:47Z | Protected-system health check | Legacy and old Ready2Go | AeroCanada/Yoyamic/old Ready2Go HTTP 200; MariaDB alive; Odoo/PostgreSQL active | Old stack remains rollback | Old stack removal/rename deferred | Record deployment; DNS remains blocker |
| 2026-07-15T11:54Z | Final read-only validation | New and protected stacks | Five new containers healthy; forced-host routes 200; 15,299,858,432 bytes free. Historical `aerocanada-auth` is running but its health probe receives HTTP 429; no restart or change performed | Old stack and all backups retained | Old stack remediation deferred | Publish DNS/TLS; investigate old auth probe separately |

## Required external action

Create `A aviation.ready2go.aero 217.182.69.159`, wait for propagation, then issue and assign a Let’s Encrypt certificate. Until then, public browser/HTTPS validation is blocked. Forced-resolution validation passed but is not public DNS success.
