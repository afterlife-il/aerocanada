# Migration Strategy

## First Principle

Yoyamic remains the production/staging legacy reference. The SaaS app reads through adapters first.

## Stages

1. Data dictionary and read-only adapters.
2. Side-by-side UI validation against Yoyamic.
3. Read-only production pilot.
4. Module-level dual-write only after approval.
5. Reconciliation reports.
6. Cutover by module.

## Migration Risks

- Legacy records use inconsistent date and text formats.
- Ownership is incomplete in old stock rows.
- Candidate ACI770 stock must not be backfilled silently.
- RFQ, quote, and PO relationships rely on `RFQ_ID`.
- Old movement tables exist but are not currently populated.

## Rollback

Until a module is formally cut over, Yoyamic remains the source of truth.
