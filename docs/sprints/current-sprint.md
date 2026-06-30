# Current Sprint

Last updated: 2026-06-30

## Sprint Goal

Continue the next-generation AeroCanada Aviation SaaS ERP from the current repository state, with Auth/Tenant foundation and deployment guard in place before business mutations.

## Track A: UI / UX

Define reusable dense ERP workspace components for Company 360, Part 360, and Stock 360.

## Track B: Business Workflow

Preserve `RFQ_ID` as the canonical workflow key. Prepare RFQ, quote, PO, stock lifecycle, repair, exchange, leasing, and loan modules.

## Track C: Security

Auth/Tenant foundation now includes password-session shape, tenant-scoped repository contracts, protected API reads, roles, and permissions. Next security work is persistent sessions, rate limiting, secure cookies, password reset, MFA/TOTP, OAuth/OIDC, and secret management.

## Track D: SaaS Foundation

OpenAPI schemas now cover the current read-route foundation. Next: read-only legacy adapter mapping. Keep `SaaS_Aviation/` isolated from Yoyamic mutations.

## Track E: Performance

Plan query performance review once read-only legacy adapter queries exist.

## Track F: Documentation

Keep `APP_RECAP.md` short, keep `PROJECT_STATE.json` synchronized, and move details into `docs/`.

## Next Highest-Value Task

Replace in-memory auth/session foundation with persistent provider-backed sessions, then create read-only legacy adapter query mapping with tests or fixtures.
