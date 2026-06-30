# Auth and Tenant Foundation

Last updated: 2026-06-30

## Scope

This foundation covers email/password auth shape, tenant context, roles, permissions, session records, route protection, and the first tenant seed for `AEROCANADA INDUSTRIES 770 INC.`.

## Current Implementation

- Shared auth and tenant types live in `SaaS_Aviation/packages/shared/src/types.ts`.
- Repository contracts require `RequestContext` for business reads.
- API password auth is implemented by `InMemoryAuthProvider`.
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
- Password auth is a foundation only; production needs persisted sessions, password reset, rate limiting, secure cookies, CSRF strategy, and MFA.
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
