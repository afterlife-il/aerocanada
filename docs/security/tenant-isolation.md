# Tenant isolation for persistent staging

Every persistent repository call receives tenant context from an authenticated session. PostgreSQL reads and writes include the tenant identifier, and the tenant primary-company foreign key is constrained to a company owned by that same tenant.

The staging administrator password is not committed. Production-mode API startup requires `STAGING_ADMIN_PASSWORD` of at least 16 characters; its SHA-256 value is held only in process memory. Sessions are bearer tokens held in memory and are lost when the API restarts. This limitation is acceptable only for staging and must be replaced before production.

The deployed `saas-aviation-staging` topology keeps PostgreSQL, Redis, MinIO, and the raw API off host-published ports. Only the web container is published on `127.0.0.1:8180`, behind the domain-specific Plesk proxy. Forced-host API checks proved tenant A could not read tenant B (404), and the tenant-composite primary-company foreign key rejected a cross-tenant relation. Current tenant isolation is still application- and constraint-enforced; database row-level security, persistent users/sessions, MFA, rate limiting, secure session cookies, password lifecycle, and secret-manager integration remain production blockers.
