# Staging tenant seed

`npm run db:seed:staging` creates or verifies the minimum staging identity: tenant `tenant-aci`, name `AEROCANADA INDUSTRIES 770 INC.`, code `aci770`, slug `AeroCanada`, and primary company `company-aci770-primary` with the stock-owner role.

The seed is idempotent and refuses to overwrite a conflicting tenant code, slug, name, or primary company. It imports no Yoyamic data and creates no password. Authentication secrets are supplied only through the server environment.

Migration order is `001_core_persistence.sql`, `002_company_360_production.sql`, then `003_tenant_identity.sql`. The migration ledger records SHA-256 checksums and rejects changed applied migrations.
