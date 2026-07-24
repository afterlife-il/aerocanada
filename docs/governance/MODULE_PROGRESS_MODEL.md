# Module Progress Model

Last updated: 2026-07-24

`SaaS_Aviation/module-status.json` is the canonical structured source for the protected CTO Dashboard. The static
web export consumes it through `apps/web/src/lib/cto-status.ts`; percentages and summary counts are calculated and
are not stored as editable claims.

The default checklist weights are business specification 5%, database schema 10%, persistence repository 10%, API
10%, tenant isolation 10%, UI 10%, list tools 10%, detail workspace 10%, actions 10%, automated tests 5%,
AeroCanada example 5%, and public staging 5%. `passed` earns full weight. `partial` requires an explicit score no
greater than its weight. `failed` and `blocked` earn zero. `not_applicable` is removed from the denominator so its
weight is transparently redistributed.

Green `validated` status is derived only when all applicable criteria pass, the calculated result is 100%, public
staging evidence passes, and a safe `aci770` example exists. A failed criterion derives blocked/red status. A
blocker derives blocked/amber status. Current data is labeled `build-time`, `last-recorded`, or `runtime-live`;
static export metadata must never be presented as live.

The initial 2026-07-24 reconciliation deliberately records incomplete catalog modules at 0% and records known
implemented modules conservatively. It does not infer evidence from pages, endpoints, fixtures, HTTP status alone,
or old prose percentages.
