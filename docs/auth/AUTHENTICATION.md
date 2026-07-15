# Authentication

Status: persistent staging foundation implemented locally; deployment pending.

## Password and user storage

Migration 005 creates tenant-bound users, salted `scrypt-v1` credentials, persistent sessions and authentication audit events. Password text and session tokens are never stored: credentials use a random 32-byte salt and 64-byte scrypt output; sessions store SHA-256 token and CSRF digests. The staging administrator is bootstrapped from server-only environment values only when no matching persistent user exists.

Five consecutive failures lock the account for 15 minutes. Successful login resets the counter and records last login. Password-change timestamps and account state are persisted.

## Browser session

The browser receives `saas_session` as `HttpOnly`, `Secure`, `SameSite=Strict` and a separate readable `saas_csrf` cookie. State-changing cookie-authenticated requests require the matching `X-CSRF-Token`. Bearer authentication remains available for controlled API clients. Logout revokes the current session; `/v1/auth/revoke-all` revokes every current-user session.

## Remaining work

TOTP, recovery codes, phone OTP, password change/reset, administrator user management, OAuth/OIDC and production secret management remain separate gated work. Persistent staging is not production authentication.
