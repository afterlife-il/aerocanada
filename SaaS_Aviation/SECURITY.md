# Security

## Current Position

This is a new SaaS application shell. It must not inherit legacy plaintext secrets or direct page-level auth assumptions.

## Requirements

- No hardcoded secrets.
- Local `.env` only for development.
- Production secrets must come from a secrets manager.
- Provider-ready auth abstraction.
- Secure session/JWT strategy.
- MFA/TOTP architecture.
- OAuth/OIDC support for Google, LinkedIn, Microsoft, Apple later.
- RBAC and tenant-aware permissions.
- Audit logs for authentication and business mutations.
- Rate limiting on auth and APIs.
- CSRF strategy for browser mutations.
- CORS allowlist for API.
- Secure cookies in production.
- Password migration plan from Yoyamic legacy users.
- API keys remain server-side only.
- HTTPS/TLS in production.
- Device/session history.

## Auth Architecture

`AuthProvider` is an interface. Implementations:

- `MockAuthProvider` for local scaffolding.
- future `LegacyBridgeAuthProvider`.
- future Clerk/Supabase/Firebase provider.

## Tenant Isolation

Every request must eventually resolve:

- authenticated user
- tenant/company workspace
- role grants
- data access scope

Tenant isolation must be enforced in repositories and APIs.

## Password Migration

Legacy Yoyamic users should be migrated by:

1. mapping employee records to SaaS identities;
2. forcing verified email;
3. rehashing or resetting legacy/plain passwords;
4. enabling MFA for privileged users first;
5. disabling legacy password fallback after migration.
