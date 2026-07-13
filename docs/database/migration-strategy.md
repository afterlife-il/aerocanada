# Migration Strategy

Status: explicit local migration runner and hardened read-only import foundation.

## Direction

Yoyamic remains the legacy source of truth until migration is approved. SaaS_Aviation imports into a dedicated SaaS database and never writes back to Yoyamic.

## SQL Migrations

Migrations live in `SaaS_Aviation/database/migrations` and are applied only by explicit commands:

- `npm run migrate:status`
- `npm run migrate:apply`

The runner in `apps/api/src/persistence/migrations.ts` is deterministic and transactional per migration file. It records each migration in `schema_migrations` with a SHA-256 checksum. If a previously applied migration file changes, status/apply reports a checksum mismatch instead of silently continuing.

Migrations are not auto-applied during API startup, tests, build, or frontend export.

## Phase 2 PostgreSQL Provider

The local PostgreSQL provider is selected only when `PERSISTENCE_PROVIDER=postgres` or `DATA_SOURCE_MODE=postgres` is set and `DATABASE_URL` is present. Missing PostgreSQL configuration fails fast. Memory mode remains the default local/test provider.

## Importer Foundation

`apps/api/src/importers/yoyamic-core-importer.ts` accepts an already-read `LegacyYoyamicSnapshot` and produces a dry-run report. It does not contain credentials, does not connect to Yoyamic, and does not write data.

`apps/api/src/importers/yoyamic-readonly-source.ts` defines the future read-only Yoyamic access policy. It exposes no live connection code, no credentials, and no mutation methods. It now provides offline guardrails for:

- SELECT/SHOW-only SQL validation.
- Rejection of multi-statement, write-capable, locking, file-writing, or unknown-procedure SQL.
- Required tenant ID, bounded row limits, bounded offsets, and bounded statement timeout options.
- Canonical read-query plans for company, contact, part, and stock source tables.
- Batch pagination plans for future dry-run reads.
- Deterministic legacy mapping records with optional source-row checksums.
- Reconciliation summaries over dry-run anomaly counts.

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

Provision an isolated development/test PostgreSQL database, set `TEST_DATABASE_URL`, run `npm run migrate:apply`, then run the PostgreSQL integration tests. Do not point this provider at Yoyamic or any live customer database.

After PostgreSQL proof is available, the next migration-adapter step is to add a real MySQL read implementation behind `YoyamicReadonlySource` using read-only credentials, server-side statement timeout, and the existing query plans. Do not run a live import until read-only access, mapping review, anomaly thresholds, and rollback/reconciliation procedure are approved.
