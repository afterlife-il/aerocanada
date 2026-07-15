# Multi-tenant identity

Ready2Go is the platform and SaaS_Aviation is the product. Tenants are data, not product branding. The initial tenant is `AEROCANADA INDUSTRIES 770 INC.` with canonical code `aci770` and public slug `AeroCanada`.

Migration 003 adds a case-insensitive unique tenant code and an optional primary-company relationship constrained to the same tenant. The public route is `/<tenant-slug>`. Repository and API operations require an authenticated tenant context and PostgreSQL queries scope records by `tenant_id`.

The initial static tenant route is generated for `/AeroCanada`, but this does not make AeroCanada the global platform identity. Future tenant routing must resolve slugs from persistent tenant data. Persistent identity administration, tenant provisioning, and durable user/session membership remain Phase 2 work.
