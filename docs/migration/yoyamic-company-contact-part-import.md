# Yoyamic Company, Contact and Part import

Status: audit and control foundation implemented; dry-run pending; no destination import performed.

## Scope

Source: `aerocanada_yoyamic`, read-only. Destination: tenant `aci770` in `saas_aviation_staging`. In scope are Company, Company Address, Contact, Part, manufacturer display name, aircraft applicability, alternate Part text, and migration metadata. Stock quantities, RFQ, Quote, Order, Document, invoice, repair, lease, and exchange records are excluded.

## Commands

`npm run import:yoyamic:audit` requires `YOYAMIC_DATABASE_URL` and `YOYAMIC_AUDIT_REPORT_PATH`. The report path must be outside Git and public webroots with mode 600. The command never writes either database.

## Full-import stop conditions

- normalized Part/manufacturer collision groups;
- unexplained Company duplicates or count mismatch;
- orphan Contacts or Company details;
- source session not read-only;
- migration checksum failure;
- missing staging backup or unsafe disk gate;
- failed tenant isolation, idempotency, reconciliation, or rollback proof.

No full staging import may proceed while any stop condition is unresolved.
