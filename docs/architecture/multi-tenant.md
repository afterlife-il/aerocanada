# Multi-tenant identity

Ready2Go Aviation SaaS is the platform. Tenants are data, not product branding. The initial tenant is `AEROCANADA INDUSTRIES 770 INC.` with canonical code `aci770` and public slug `AeroCanada`; future tenant routes follow `https://aviation.ready2go.aero/<TenantSlug>`. Existing repository and runtime names are retained until a later approved, non-disruptive migration.

Migration 003 adds a case-insensitive unique tenant code and an optional primary-company relationship constrained to the same tenant. The public route is `/<tenant-slug>`. Repository and API operations require an authenticated tenant context and PostgreSQL queries scope records by `tenant_id`.

The initial route `/AeroCanada/` is deployed and forced-host validated, but this does not make AeroCanada the global platform identity. Runtime isolation proved tenant A cannot read a tenant B Company and PostgreSQL rejects a cross-tenant primary-company relation. Dynamic slug resolution, tenant administration and durable user/session membership remain Phase 2 work.
