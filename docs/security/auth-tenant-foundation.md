# Auth and Tenant Foundation

Last updated: 2026-07-15

## Scope

This foundation covers email/password auth shape, tenant context, roles, permissions, session records, route protection, and the first tenant seed for `AEROCANADA INDUSTRIES 770 INC.`.

## Current Implementation

- Shared auth and tenant types live in `SaaS_Aviation/packages/shared/src/types.ts`.
- Repository contracts require `RequestContext` for business reads.
- Production PostgreSQL mode selects `PostgresAuthProvider`; local unit tests retain `InMemoryAuthProvider`.
- Migration 005 persists tenant users, salted scrypt credentials, lockout state, sessions and authentication audit events.
- Browser sessions use HttpOnly/Secure/SameSite cookies and CSRF double-submit validation.
- Express business read routes require a bearer session before returning data.
- Sample data includes one active tenant and one admin user structure.
- Web static export shows auth-aware tenant/session context but is not the security boundary.

## First Tenant Seed

- Tenant code: `ACI770`
- Tenant name: `AEROCANADA INDUSTRIES 770 INC.`
- Primary company: `company-5263`
- First admin: `user-aci-admin`
- Roles: `owner_admin`, `tenant_admin`, `inventory_manager`, `sales_manager`

## Security Rules

- UI visibility is not authorization.
- All business repositories must receive `RequestContext`.
- Future database queries must filter by `context.tenant.tenantId` at query level.
- Password reset, TOTP/recovery codes, phone OTP, rate limiting and production identity lifecycle remain required.
- OAuth/OIDC providers must map identities to an existing tenant before granting access.

## Provider Roadmap

Planned providers:

- Google
- LinkedIn
- Microsoft
- Apple

Future MFA:

- TOTP
- recovery codes
- admin-enforced MFA for privileged roles
