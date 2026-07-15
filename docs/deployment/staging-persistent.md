# Persistent SaaS_Aviation staging

Status: persistent staging runtime deployed side-by-side on 2026-07-15 at commit `c667f284101272b7b987abe91501d4f79dd487dd`. Public DNS and a hostname-valid certificate remain pending.

Ready2Go is the platform, SaaS_Aviation is the product, and AeroCanada is the first tenant. The intended public endpoints are `https://aviation.ready2go.aero/`, tenant route `/AeroCanada`, and same-origin API route `/api/`.

The staging Compose project is `saas-aviation-staging` under `/opt/ready2go/saas-aviation`. It uses containers `saas-aviation-web`, `saas-aviation-api`, `saas-aviation-postgres`, `saas-aviation-redis`, and `saas-aviation-minio`; network `saas_aviation_staging`; and three new dedicated volumes. Only the web listener is published, on `127.0.0.1:8180`. PostgreSQL, Redis, MinIO, and the raw API remain internal.

Secrets belong only in a server-side mode-600 environment file. The Compose definition rejects missing database and MinIO secrets, and production API startup rejects a missing or short staging administrator password. No environment file is included in images or Git.

Validated on the server: Linux image labels, all five health checks, migrations 001-003 and stable checksums, idempotent tenant seed, same-origin health/OpenAPI, login, Company/Contact/Address/Part/Stock CRUD, quantity 0, independent company relationships, tenant isolation, and PostgreSQL persistence after API restart. Authentication users and sessions remain in memory; an API restart requires re-login. Documents and commercial workflows remain explicit non-persistent boundaries. This is persistent staging, not production.

The restricted pre-deployment backup is `/opt/ready2go/saas-aviation/backups/predeploy-20260715T105420Z`. The verified staging backup and restore rehearsal are at `/opt/ready2go/saas-aviation/backups/staging-20260715T114501Z`. The deployed release is `/opt/ready2go/saas-aviation/releases/c667f284101272b7b987abe91501d4f79dd487dd`.

Operations:

```bash
docker compose -f docker-compose.staging.yml ps
docker compose -f docker-compose.staging.yml logs --tail=200 api web
docker compose -f docker-compose.staging.yml restart api
docker compose -f docker-compose.staging.yml exec -T api node apps/api/dist/persistence/migrations.js status
```

Rollback keeps the abandoned seven-container stack and its volumes untouched. Remove the new domain proxy, then stop only the `saas-aviation-staging` project. Preserve the new PostgreSQL volume for diagnosis.
