# Continuous Definition of Done

Last updated: 2026-07-24

A feature is complete only when code and persistent PostgreSQL behavior exist, the interface works, tenant
isolation is tested, automated tests pass with no required skips, public staging is validated, a concrete safe
AeroCanada example is verified, canonical status recalculates, and documentation plus the deployment journal match
the verified runtime.

Every sprint must update `SaaS_Aviation/module-status.json`. A regression changes its criterion to `failed`,
recalculates the percentage, removes green status, and records the evidence and next action. Checks that were
skipped, blocked, unexecuted, or limited to HTTP must be described exactly that way.
