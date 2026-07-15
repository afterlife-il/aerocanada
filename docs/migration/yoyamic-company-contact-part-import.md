# Yoyamic Company, Contact and Part import

Status: live read-only dry-run and controlled staging sample completed; full import blocked.

## Scope

Source: `aerocanada_yoyamic`, read-only. Destination: tenant `aci770` in `saas_aviation_staging`. In scope are Company, Company Address, Contact, Part, manufacturer display name, aircraft applicability, alternate Part text, and migration metadata. Stock quantities, RFQ, Quote, Order, Document, invoice, repair, lease, and exchange records are excluded.

## Commands

`npm run import:yoyamic:audit` requires `YOYAMIC_DATABASE_URL` and `YOYAMIC_AUDIT_REPORT_PATH`. The report path must be outside Git and public webroots with mode 600. The command never writes either database. `npm run import:yoyamic:sample` additionally requires `SAMPLE_IMPORT_APPROVED=true`, `DATABASE_URL`, and `YOYAMIC_SAMPLE_REPORT_PATH`; it writes only the dedicated SaaS staging PostgreSQL.

## Verified 2026-07-15 result

- source counts: 5,884 Companies, 6,389 Company details, 17,502 Contacts, 93,410 Parts;
- source MariaDB session: `tx_read_only=1`;
- dry-run report: `/opt/ready2go/saas-aviation/migration-reports/20260715T135622Z-dry-run/aerocanada-import-audit.json`;
- pre-import backup: `/opt/ready2go/saas-aviation/backups/pre-import-20260715T140733Z` with SHA-256 manifest;
- sample batch: 7 Companies, 9 addresses, 13 Contacts, 7 Parts, zero Stock, zero quarantine;
- idempotency batch: zero inserted; 7 Companies, 9 addresses, 13 Contacts, and 7 Parts unchanged;
- tenant isolation: zero importer-created Company rows outside `tenant-aci`;
- public reconciliation: imported Company, Company 360, and Part reads returned 200; Company 360 showed zero stock.

Server-only reports are root-owned mode 600. No source row values, credentials, or PII are committed.

## Full-import stop conditions

- normalized Part/manufacturer collision groups;
- unexplained Company duplicates or count mismatch;
- orphan Contacts or Company details;
- source session not read-only;
- migration checksum failure;
- missing staging backup or unsafe disk gate;
- failed tenant isolation, idempotency, reconciliation, or rollback proof.

No full staging import may proceed while any stop condition is unresolved.

The current dry-run is blocked by: missing Company name, duplicate normalized Company name, orphan Company detail, orphan Contact, duplicate email within a Company, duplicate normalized Part/manufacturer, and orphan manufacturer. Phase 2 must establish approved resolution rules and produce a clean repeat dry-run before full-import approval.
