# Documents Data Model Notes

Last updated: 2026-07-02

## Current State

No live database schema was changed. The Phase 1 implementation is sample-data backed and uses tenant-scoped TypeScript read models in `SaaS_Aviation/packages/shared`.

Implemented metadata entities:

- `DocumentRecord`: tenant-owned document metadata, document type, file metadata, uploader, version, visibility, status, notes, and tags.
- `DocumentVersionRecord`: immutable version metadata with file name, MIME type, size, checksum placeholder, scan status, and storage state.
- `DocumentLinkRecord`: polymorphic links to Company, Contact, Part, Stock, RFQ, Supplier Quote, Customer Quote, PO, SO, Invoice, and Repair/Exchange/Lease. This is the canonical ownership table.
- `DocumentCenterReadModel`: tenant document center summary.
- `EntityDocumentReadModel`: documents for one linked entity.
- `DocumentUploadValidationResult`: secure upload-intent validation result.

## Ownership Consistency

Document ownership is not stored twice. `DocumentRecord` does not include `ownerModule` or `ownerRecordId`; those fields are projected onto `DocumentReadModel` from the single `DocumentLinkRecord` with `relation: "primary"`.

Persistence must enforce exactly one primary link per document inside a tenant. `supporting` and `reference` links can attach the same document to related records, but they cannot become competing owners.

## Alert Layer

`DocumentAlert` remains a denormalized operational alert layer for module dashboards and 360 pages. It should be generated from document requirements plus `DocumentRecord`/`DocumentVersionRecord`/`DocumentLinkRecord` state. It must not become a second table of document metadata or upload ownership.

## Future Persistence Requirements

Before real persistence:

- Every document, version, and link row must include `tenant_id`.
- Repository methods must require `RequestContext`.
- A tenant-scoped uniqueness constraint must allow only one primary link per document.
- Read endpoints must require `document.read` after authentication and before returning document metadata.
- File bytes belong in object storage, never in the relational store.
- Object keys must be generated from tenant id, document id, version id, and sanitized filename.
- No document is servable before malware scan status is clean.
- SHA-256 checksums must be computed server-side.
- Version rows must be immutable.
- Document deletes must be soft deletes with retention and legal-hold enforcement.
- Audit events must be persisted for upload, view, download, version, share, delete, restore, and legal-hold changes.

## Migration Notes From Yoyamic

Legacy document tables and directories remain migration references only:

- `tbl_docs_attachment_pn`
- `tbl_docs_attachment_company`
- `docsattachment/`
- `docsattachmentcompany/`

Known legacy gaps:

- No tenant column.
- No versioning.
- No checksum.
- No MIME detection.
- No scan status.
- No uploader or audit trail.
- No soft delete.
- Filename-based storage collision risk.

Migration must be read-only first, followed by bulk copy with checksum, MIME detection, mandatory malware scanning, explicit tenant assignment, reconciliation reporting, and validated cutover.
