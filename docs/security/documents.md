# Documents Security

Last updated: 2026-07-02

## Current Implementation

The Documents Phase 1 implementation is a metadata and upload-validation foundation. It does not store file bytes, serve downloads, or generate public URLs.

Implemented controls:

- All document reads and upload validation accept `RequestContext`.
- Documents, versions, links, and upload intents carry `tenantId`.
- `document.read` and `document.upload` permissions are modeled.
- Upload validation checks:
  - tenant context
  - `document.upload` permission
  - owner module and owner record presence
  - MIME allowlist
  - extension allowlist
  - maximum size of 20 MB
  - non-empty file size
  - sanitized display filename
- Accepted upload intents return `persistence: "metadata-only"` to avoid unsafe fake storage.

## Allowed File Types In Phase 1

- PDF
- JPEG
- PNG
- WebP
- plain text
- RFC822 email
- DOCX
- XLSX

Executable file types are rejected. File names are metadata only and must never become storage keys without a generated document/version id.

## Required Before Real Byte Storage

Before production upload/download:

- Object storage provider must be approved.
- Uploads must land in quarantine before active storage.
- Malware scanning must complete before files can be viewed or downloaded.
- MIME type must be detected server-side from file content, not trusted from the client header.
- SHA-256 checksum must be computed server-side.
- Download and preview must use short-lived signed URLs.
- Every upload, view, download, version, delete, restore, share, and legal-hold action must be audited.
- Retention and legal-hold behavior must be enforced before purge or archive.
- Tenant filtering must happen at repository and database query level.

## Known Security Gaps

- No object storage integration yet.
- No malware scanner yet.
- No persisted audit log yet.
- No download/preview URL signing yet.
- No OCR or AI analysis pipeline yet.
