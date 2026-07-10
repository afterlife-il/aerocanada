# Migration Strategy

Status: controlled dry-run foundation only.

## Direction

Yoyamic remains the legacy source of truth until migration is approved. SaaS_Aviation imports into a dedicated SaaS database and never writes back to Yoyamic.

## Phase 1 Importer Foundation

`apps/api/src/importers/yoyamic-core-importer.ts` accepts an already-read `LegacyYoyamicSnapshot` and produces a dry-run report. It does not contain credentials, does not connect to Yoyamic, and does not write data.

The report includes inserted, updated, skipped, duplicate, failed, and anomaly counts for companies, contacts, parts, and stock.

## Reconciliation Checks

Implemented dry-run checks include:

- duplicate company names
- duplicate contact emails
- duplicate normalized part numbers by manufacturer
- orphan contacts
- orphan stock
- unknown owner/supplier/tag-info/traceability companies
- quantity `0` rows
- invalid quantity
- missing condition

## Next Step

Add a read-only Yoyamic adapter using approved credentials and run the importer against a development/test SaaS database only.
