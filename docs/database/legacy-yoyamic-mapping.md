# Legacy Yoyamic Mapping

Status: PHP-code audit plus offline read-only adapter hardening. No live database query was executed in this sprint.

## Tables Identified From Legacy PHP References

- Companies: `tb_company`, `tbl_Company_Details`, `tbl_Company_Type`.
- Contacts: `tb_company_contact`.
- Parts: `tbl_Parts`.
- Internal stock: `tb_stock_part`.
- External stock: `tbl_Stock_external`.
- Conditions: `tbl_Condition`.
- Release/certification: `tbl_Release`.
- Currency: `tbl_Currency`.
- Aircraft/platform: `tbl_Aircraft`.
- Company document attachments: `tbl_docs_attachment_company`.
- Part document attachments: `tbl_docs_attachment_pn`.

## Relationship Notes

Legacy stock code references separate fields for supplier, owner/company, tag info, and traceability. The SaaS schema preserves these as independent relationships and does not collapse them into one company field.

## Risks To Verify With Read-Only DB Access

- Primary key and foreign key definitions are not verified from information_schema yet.
- Nullability and date/encoding anomalies require read-only database sampling.
- Duplicate company, contact email, and part-number rules require source-count reconciliation.
- Some legacy PHP paths insert or update tables directly; importer work must remain separate and read-only against Yoyamic.

## Read-Only Adapter Guardrails

`SaaS_Aviation/apps/api/src/importers/yoyamic-readonly-source.ts` is the only planned interface for future Yoyamic reads. It remains offline in this repository and does not contain credentials or connection code.

Implemented guardrails:

- SELECT/SHOW-only SQL validation.
- Rejection of multi-statement SQL.
- Rejection of write-capable keywords, unknown procedure calls, locking reads, and file-writing reads.
- Required tenant ID, bounded row limit, bounded offset, and bounded timeout options.
- Canonical source mappings for companies, contacts, parts, and stock.
- Batch query planning for paginated dry-run reads.
- Deterministic legacy mapping records with optional source-row checksums.
- Reconciliation summaries derived from dry-run reports.

Future live usage must still use explicit read-only database credentials, statement timeouts at the driver/server layer, and human-reviewed source-to-target mapping before any import is allowed.
