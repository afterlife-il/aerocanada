# Documents

Last updated: 2026-07-02

## Scope

Documents now has a Phase 1 foundation in `SaaS_Aviation/`: tenant-scoped document metadata, version records, entity links, reusable UI panels, API contracts, and upload-intent validation. It covers certificates, trace, invoices, quotes, PO/SO documents, packing slips, airway bills, email attachments, contracts, photos, and other aviation records.

The foundation does not store file bytes yet. Upload support is currently a secure validation/initiation contract that checks tenant context, permission, owner module, MIME type, file extension, file size, filename sanitization, visibility, and notes before returning a metadata-only upload intent.

## Reference

The full target architecture lives in `SaaS_Aviation/DOCUMENTS_ARCHITECTURE.md`. The implemented Phase 1 code follows that document's read-model-first recommendation.

## Implemented Surfaces

- Shared service: `SaaS_Aviation/packages/shared/src/document-service.ts`.
- Shared types: `DocumentRecord`, `DocumentVersionRecord`, `DocumentLinkRecord`, `DocumentReadModel`, `DocumentCenterReadModel`, `EntityDocumentReadModel`, `DocumentUploadRequest`, and `DocumentUploadValidationResult`.
- API contracts:
  - `GET /v1/documents`
  - `GET /v1/documents/{id}`
  - `GET /v1/entities/{ownerModule}/{ownerRecordId}/documents`
  - `POST /v1/documents/upload-intent`
- Web UI:
  - `/documents`
  - reusable `DocumentPanel`
  - reusable `UploadFoundationPanel`
  - Company 360, Part 360, and Stock 360 document panels.

## Ownership Model

`DocumentLinkRecord` is the canonical source of ownership. Each document must have exactly one link with `relation: "primary"`; that primary link supplies `DocumentReadModel.ownerModule`, `DocumentReadModel.ownerRecordId`, and `DocumentReadModel.primaryLink`.

`DocumentRecord` stores document metadata only. It does not duplicate owner module or owner record fields. Additional `supporting` and `reference` links are allowed for cross-module visibility, but they do not replace the primary owner.

## Document Alerts

`DocumentAlert` is an operational alert/read-model layer for existing Dashboard, Part 360, Stock 360, and Company Inventory surfaces. It represents missing, expiring, or review-needed document work derived from document requirements and linked document metadata.

`DocumentAlert` is not a separate document system and does not own files, versions, links, uploads, or document metadata. As the Documents module expands, alert rows should be derived from `DocumentRecord`, `DocumentVersionRecord`, and `DocumentLinkRecord` instead of maintained as unrelated document data.

## Yoyamic Logic To Preserve

- `RFQ_ID` remains the link key for commercial documents tied to RFQ, quote, PO, and SO workflows.
- Owner/Company, Supplier, Tag Info, and Traceability company remain independent relationships.
- ACI-owned stock and external supplier stock keep separate document trails.
- Certificates and trace documents are first-class aviation traceability concepts, not generic attachments.

## Upload Foundation

The upload foundation is intentionally not a local fake file store. The current slice validates upload metadata and returns an upload intent with `persistence: "metadata-only"`. Real byte storage remains a future storage service responsibility and must include object storage, quarantine, malware scanning, checksum, retention, and audited completion before any document becomes downloadable.

## Known Gaps / Next Steps

- No object storage, malware scanning, or persistence decision has been made.
- Uploads are metadata-only validation contracts until storage is approved.
- PDF/template generation, OCR, AI certificate analysis, and full-text search remain future phases.
- Legacy Yoyamic document reads are not mapped yet.
