# Legacy Yoyamic Mapping

Status: live schema metadata and aggregate data-quality audit completed read-only against `aerocanada_yoyamic`; no source mutation performed.

## Verified source identity

The deployed Yoyamic copy under `/var/www/vhosts/aerocanada-industries.com/httpdocs/yoyamic` references the dedicated MariaDB database `aerocanada_yoyamic`. The public `yoyamic.com` vhost currently serves a separate static landing page. The import source is the dedicated database, not the original `aerocanada` database.

All live inspection ran with MariaDB session `tx_read_only=1`. Credentials and raw contact rows were not printed.

## Tables and keys verified live

- Companies: `tb_company.Fld_Company_ID` (5,884 exact rows during audit), `tbl_Company_Details.id_tbl_company_Details` (6,389), `tbl_Company_Type.Fld_Company_Type_ID`.
- Contacts: `tb_company_contact.id_company_contact` (17,502), linked by undeclared `Fld_Company_ID`.
- Parts: `tbl_Parts.Fld_Part_ID` (93,410), with manufacturer stored as a company ID, aircraft stored as `tbl_Aircraft.Fld_AC_ID`, and alternates stored in `alt_pn` text.
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

## Verified data-quality risks

- No declared foreign keys exist on the four core source tables; relationships must be reconciled explicitly.
- 21 normalized Company-name duplicate groups; one blank Company name; 81 orphan Company-detail rows; 325 Companies with multiple detail rows.
- 214 orphan Contacts; 2,564 blank Contact names; 729 invalid non-blank email values; 403 duplicate-email-within-Company groups.
- 233 exact Part Number duplicate groups; 758 normalized Part Number/manufacturer collision groups; 1,397 blank descriptions; 17 manufacturer orphans; 12,090 rows with alternate text.
- Source dates mix `datetime` and `varchar`; invalid/zero date handling must quarantine rather than invent timestamps.
- Some legacy PHP paths insert or update tables directly; importer work must remain separate and read-only against Yoyamic.

## Read-Only Adapter Guardrails

`SaaS_Aviation/apps/api/src/importers/yoyamic-readonly-source.ts` remains the SQL policy boundary. `yoyamic-live-audit.ts` is the only live connector and exposes SELECT-only snapshot reads after enforcing `tx_read_only=1`; it contains no source mutation methods or credentials.

Implemented guardrails:

- SELECT/SHOW-only SQL validation.
- Rejection of multi-statement SQL.
- Rejection of write-capable keywords, unknown procedure calls, locking reads, and file-writing reads.
- Required tenant ID, bounded row limit, bounded offset, and bounded timeout options.
- Canonical source mappings for companies, contacts, parts, and stock.
- Batch query planning for paginated dry-run reads.
- Deterministic legacy mapping records with optional source-row checksums.
- Reconciliation summaries derived from dry-run reports.

The aggregate dry-run report is written only to an explicitly configured restricted server path. Full import is automatically blocked while normalized collisions, rejected rows, or orphan relationships remain unresolved.
