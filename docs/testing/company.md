# Company 360 Testing

Last validated: 2026-07-14.

Executed evidence:

- Migration 002 status/apply/status passed with checksum recorded.
- `npm run test:postgres`: 13 passed, 0 failed, 0 skipped.
- PostgreSQL reconnect preserved identity, ICAO/IATA/VAT/tags, Contacts, Addresses, activity, Stock, and quantity zero.
- Tenant B could not read Company, Contacts, Addresses, activity, Parts, Stock, or the Company 360 aggregate for Tenant A.
- Authenticated HTTP smoke created, read, updated, and deleted Company/Contact/Address records against local PostgreSQL.
- Company deletion with stock relationships is protected; safe deletion passed.
- Typecheck, lint, and production build passed.

Browser automation did not pass or fail the UI: `agent-browser` was unavailable, Playwright Chromium was not installed, and installed Edge automation timed out twice. This is a tooling blocker and must not be represented as visual browser success.

The public frontend remains sample-static and was not deployed. Persistent UI verification requires local `persistent-api` mode, API port 4107, and a valid bearer login.
