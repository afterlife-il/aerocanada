# Company 360 Data Model Notes

Last updated: 2026-07-07

## Current State

No live database schema was changed for this slice. Company 360 uses tenant-scoped TypeScript fixtures and web read-model composition in `SaaS_Aviation/apps/web/src/lib/data.ts`.

## Read Model Inputs

- `Company`: tenant-owned company profile, type, location, email, tags, risk, legacy id, and last activity.
- `Contact`: tenant-owned contacts linked by `companyId`.
- `CompanyInventoryReadModel`: derived company stock summary from internal and external stock.
- `EntityDocumentReadModel`: documents linked through the shared Documents metadata/link model.
- `RfqSummary`, `SupplierQuoteSummary`, `QuoteSummary`, and `OrderSummary`: commercial read-model rows linked by company name in the current sample layer.
- `AuditEvent`: read-only tenant activity.
- `WorkflowBoundaryAction`: explicit non-persistent action boundary metadata.

## Future Persistence Requirements

Before real persistence:

- Every company, address, contact, commercial relationship, document link, and activity row must include `tenant_id`.
- Repository methods must require `RequestContext`.
- Queries must filter by `tenant_id` at database level.
- Company aliases, parent/subsidiary/DBA relationships, address records, and contact roles should be modeled as first-class records instead of display-only fields.
- Commercial links should use stable workflow identifiers, especially `RFQ_ID`, not display labels.
- Mutations require persistent auth, RBAC, audit, and tenant isolation before they are enabled.

## Migration Notes From Yoyamic

Yoyamic behavior to preserve:

- `tb_company` and company detail/contact rows must map to tenant-scoped company and contact records.
- `Fld_Company_ID` / legacy company ids remain visible for traceability.
- Company contacts from `tb_company_contact` should preserve role/title, email, phone, mobile, and remarks where available.
- Legacy company documents from `tbl_docs_attachment_company` and `docsattachmentcompany/` should migrate through the shared Documents ownership model.
- Stock owner/company, supplier, tag info company, and traceability company must remain separate relationships.
