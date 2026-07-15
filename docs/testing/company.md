# Company 360 Testing

## Persistent staging acceptance scope

Acceptance covers login; Company create/read/update/delete; optional blank fields; Contact and Address create/update/delete; primary-address behavior; reload persistence; API-restart persistence with re-login; tenant isolation; and explicit Documents/commercial workflow boundaries. Local pre-deployment proof on 2026-07-15 completed login, Company create/read, Company 360 aggregation, and API-restart persistence. Full browser CRUD acceptance remains pending public deployment.

Last validated: 2026-07-14, Company 360 Production Hardening Phase 1.1.

Executed evidence:

- Migrations 001/002 status and idempotent apply passed against localhost-only PostgreSQL 16.
- `npm run test:postgres`: 15 passed, 0 failed, 0 skipped.
- Local HTTP login returned `{ data: { session } }` with a non-empty token; invalid login returned 401; logout revoked and cleared the local session path.
- Authenticated Company request succeeded after login. After API restart the old in-memory token returned 401, re-login succeeded, and PostgreSQL Company/Contact/Address records persisted.
- Optional blank fields normalize to omitted values; whitespace trims; invalid non-blank email/URL and required blanks remain rejected.
- Company CRUD, search, filters, sorting, pagination, Stock-reference delete protection, Contact full update/delete, Address create/update/delete/one-primary handling, and tenant isolation passed in automated tests.
- Persistent Company 360 returns PostgreSQL identity, Contacts, Addresses, inventory, and activity; Documents are an explicit empty non-persistent boundary and five commercial categories remain `persistence: none` boundaries.
- `npm run test`, `npm run typecheck`, `npm run lint`, `npm run build`, and `git diff --check` passed. The ordinary test command reports the database test skipped when no URL is inherited; the explicit database command above is the required zero-skip proof.

Visual browser automation was not executed because no in-app browser instance was available. This must not be represented as browser success. UI behavior is covered by normalization tests, route/API tests, typecheck, lint, and production build, but a visual staging review remains required.

Authentication remains local/in-memory and is not production-grade. The public frontend remains sample-static. Nothing was pushed or deployed; Yoyamic, legacy PHP, Documents internals, and live databases were untouched.
