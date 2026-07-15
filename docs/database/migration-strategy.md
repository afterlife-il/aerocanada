# Migration Strategy

Status: explicit migration runner, live read-only Yoyamic audit, and migration-control schema validated locally; full import not approved.

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

`apps/api/src/importers/yoyamic-live-audit.ts` adds the controlled live audit path. It requires environment-only connection/report configuration, forces MariaDB `tx_read_only=1`, reads only hard-coded SELECT projections, emits aggregate findings without raw contact data, and blocks full import on manual-review or rejected findings.

Migration 004 adds `import_batches`, `imported_records`, and `import_quarantine`, plus tenant-scoped legacy-ID uniqueness for Companies, Contacts, and Parts. It is applied locally and to dedicated persistent staging PostgreSQL.

Migration 005 adds persistent authentication users, scrypt credentials, sessions and authentication audit events. Its checksum is `80ff5c77057b0a98be65d278fa31f899c24811cc92742baba55629d1130946f2`; it was applied to staging after the `pre-auth-20260715T145902Z` backup.

Migration 006 adds tenant-bound encrypted TOTP factors, hashed one-use recovery codes, verified E.164 phone factors, expiring bounded-attempt OTP challenges and the related audit categories. Its local checksum is `5bddbca138bf5b260b39d84e1c8ec12a9b21dfe3b7b7ed92404cd3a1449333c1`. It is locally implemented and PostgreSQL-tested with zero skips; staging deployment remains pending until its focused rollout gate.

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

## Current gate

Run the live aggregate dry-run into a restricted server report, then design a representative sample that excludes collision groups and orphans. Do not run the full import while Part normalization collisions, Company duplicates, or Contact/detail orphans remain unresolved. Before any sample write, apply migration 004 to staging only after a fresh backup and disk/health checks.
