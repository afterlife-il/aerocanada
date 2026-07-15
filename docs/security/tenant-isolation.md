# Tenant isolation for persistent staging

Every persistent repository call receives tenant context from an authenticated session. PostgreSQL reads and writes include the tenant identifier, and the tenant primary-company foreign key is constrained to a company owned by that same tenant.

The staging administrator password is not committed. Production-mode API startup requires `STAGING_ADMIN_PASSWORD` of at least 16 characters; its SHA-256 value is held only in process memory. Sessions are bearer tokens held in memory and are lost when the API restarts. This limitation is acceptable only for staging and must be replaced before production.

PostgreSQL, Redis, MinIO, and the raw API have no published host ports. The public web proxy is loopback-only and CORS allows only the staging origin. Current tenant isolation is application-enforced; database row-level security, persistent users/sessions, MFA, rate limiting, secure session cookies, password lifecycle, and secret-manager integration remain production blockers.
