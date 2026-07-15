# Authentication

Status: persistent password/session and MFA foundations deployed and publicly validated at `aviation.ready2go.aero`; not production-complete.

## Password and user storage

Migration 005 creates tenant-bound users, salted `scrypt-v1` credentials, persistent sessions and authentication audit events. Password text and session tokens are never stored: credentials use a random 32-byte salt and 64-byte scrypt output; sessions store SHA-256 token and CSRF digests. The staging administrator is bootstrapped from server-only environment values only when no matching persistent user exists.

Five consecutive failures lock the account for 15 minutes. Successful login resets the counter and records last login. Password-change timestamps and account state are persisted.

## Browser session

The browser receives `saas_session` as `HttpOnly`, `Secure`, `SameSite=Strict` and a separate readable `saas_csrf` cookie. State-changing cookie-authenticated requests require the matching `X-CSRF-Token`. Bearer authentication remains available for controlled API clients. Logout revokes the current session; `/v1/auth/revoke-all` revokes every current-user session.

## Remaining work

Password change/reset, administrator user management, OAuth/OIDC, a production SMS provider and production secret management remain separate gated work. Persistent staging is not production authentication.

## MFA and phone enrollment

Migration 006 adds encrypted TOTP factors, hashed one-use recovery codes, E.164 phone factors and bounded OTP challenges. TOTP secrets use AES-256-GCM under `AUTH_ENCRYPTION_KEY`, never a committed key. Login returns a short-lived challenge instead of a session when TOTP is enabled; a valid current TOTP or unused recovery code is required before secure session cookies are issued. Recovery codes are returned once at enrollment and stored only as SHA-256 digests.

Phone enrollment normalizes E.164 numbers, expires OTPs after ten minutes, limits attempts to five and enforces a one-minute resend cooldown even after successful consumption. The only implemented delivery adapter is `staging-spool`: it writes individual codes to a server-only mode-0600 spool configured by `PHONE_OTP_STAGING_SPOOL`. Codes are never returned by the public API and never written to application logs. Production must configure and test a real SMS adapter before phone authentication can be advertised as operational.

The staging rollout applied migration 006 and recreated only the API. Acceptance used temporary non-enabled factors, verified a phone challenge from the container-private spool, then deleted the factor, challenge and spool record. The owner account remained `mfa_enabled=false`. TOTP/recovery behavior is covered by the zero-skip PostgreSQL suite; owner enrollment remains an explicit user decision.

External identity-provider configuration and its current limitations are documented in `docs/auth/OAUTH_PROVIDERS.md`.

## Staging validation

On 2026-07-15 migration 005 was applied to the dedicated staging PostgreSQL database after a root-only backup. Public acceptance proved password login, cookie-authenticated session lookup, CSRF rejection and acceptance, disposable Company create/delete, session continuity after an API container restart, persisted logout, and unauthorized access after logout. The WAF requires an explicit zero-length body for empty POST requests; the browser client supplies this through `fetch`. No credential value was printed or committed.
