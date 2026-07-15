# OAuth and OIDC providers

Status: provider configuration and authorization-request security foundation implemented; no external provider is configured in staging.

## Supported provider definitions

Google, Microsoft, Apple and LinkedIn are represented by explicit authorization/token endpoints and minimum sign-in scopes. The authorization-request helper generates independent high-entropy state, OIDC nonce and PKCE verifier values; PKCE uses S256. Constant-time digest comparison is available for callback state/nonce checks. The safe public status endpoint is `GET /v1/auth/providers`; it returns provider names and configured booleans only, never client IDs or secrets.

Configuration requires a server-only client ID, client secret and exact callback URI for each provider. A provider remains disabled until all three values exist and callback acceptance has been completed. The public login UI currently states `Not configured for this staging environment` for every provider and exposes no active provider action.

## Remaining callback work

External credentials are unavailable. Therefore authorization redirects, token exchange, ID-token signature/issuer/audience validation, provider-subject persistence, tenant membership decisions, duplicate-email review, account linking/disconnect and revocation have not been activated or claimed as tested. No access token is logged or stored by the current foundation.

LinkedIn is limited to official OAuth/OIDC fields and permissions actually granted. No scraping, contact harvesting, unofficial invitation automation or simulated capability is permitted. Contact enrichment and invitations remain manual/user-reviewed unless an official granted API supports them.
