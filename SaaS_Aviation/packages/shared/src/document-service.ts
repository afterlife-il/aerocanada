import type {
  AuditEvent,
  DocumentCenterReadModel,
  DocumentLinkRecord,
  DocumentOwnerModule,
  DocumentReadModel,
  DocumentRecord,
  DocumentUploadRequest,
  DocumentUploadValidationResult,
  DocumentVersionRecord,
  EntityDocumentReadModel,
  RequestContext
} from "./types.js";

export interface DocumentSource {
  documents: DocumentRecord[];
  versions: DocumentVersionRecord[];
  links: DocumentLinkRecord[];
  auditEvents: AuditEvent[];
}

const maxUploadBytes = 20 * 1024 * 1024;
const allowedMimeTypes = new Set([
  "application/pdf",
  "image/jpeg",
  "image/png",
  "image/webp",
  "text/plain",
  "message/rfc822",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
]);

const allowedExtensions = new Set(["pdf", "jpg", "jpeg", "png", "webp", "txt", "eml", "docx", "xlsx"]);

function tenantItems<T extends { tenantId: string }>(context: RequestContext, items: T[]): T[] {
  return items.filter((item) => item.tenantId === context.tenant.tenantId);
}

function sanitizeFileName(fileName: string): string {
  const baseName = fileName.replace(/\\/g, "/").split("/").filter(Boolean).pop() ?? "document";
  return baseName
    .normalize("NFKD")
    .replace(/[^\w.\- ]+/g, "")
    .trim()
    .replace(/\s+/g, "-")
    .replace(/-+/g, "-")
    .slice(0, 140) || "document";
}

function extensionOf(fileName: string): string {
  const sanitized = sanitizeFileName(fileName);
  const extension = sanitized.split(".").pop();
  return extension ? extension.toLowerCase() : "";
}

function composeDocuments(context: RequestContext, source: DocumentSource): DocumentReadModel[] {
  const documents = tenantItems(context, source.documents);
  const versions = tenantItems(context, source.versions);
  const links = tenantItems(context, source.links);

  return documents.map((document) => {
    const documentVersions = versions.filter((version) => version.documentId === document.id).sort((left, right) => right.version - left.version);
    const documentLinks = links.filter((link) => link.documentId === document.id);
    const primaryLinks = documentLinks.filter((link) => link.relation === "primary");
    const primaryLink = primaryLinks[0];
    if (primaryLinks.length !== 1 || !primaryLink) {
      throw new Error(`document_primary_link_required:${document.id}`);
    }

    return {
      ...document,
      ownerModule: primaryLink.ownerModule,
      ownerRecordId: primaryLink.ownerRecordId,
      primaryLink,
      currentVersion: documentVersions.find((version) => version.id === document.currentVersionId) ?? documentVersions[0] ?? null,
      versions: documentVersions,
      links: documentLinks
    };
  });
}

export function buildDocumentCenterReadModel(context: RequestContext, source: DocumentSource): DocumentCenterReadModel {
  const documents = composeDocuments(context, source).sort((left, right) => right.uploadedAt.localeCompare(left.uploadedAt));
  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    documents,
    summary: {
      total: documents.length,
      clean: documents.filter((document) => document.currentVersion?.scanStatus === "clean").length,
      needsReview: documents.filter((document) => document.status === "pending-review" || document.status === "scan-required").length,
      restricted: documents.filter((document) => document.visibility === "restricted").length,
      totalSizeBytes: documents.reduce((total, document) => total + document.sizeBytes, 0)
    }
  };
}

export function buildEntityDocumentReadModel(
  context: RequestContext,
  entityType: DocumentOwnerModule,
  entityId: string,
  source: DocumentSource
): EntityDocumentReadModel {
  const documents = composeDocuments(context, source).filter((document) =>
    document.links.some((link) => link.ownerModule === entityType && link.ownerRecordId === entityId)
  );

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    entityType,
    entityId,
    documents
  };
}

export function validateDocumentUploadRequest(context: RequestContext, request: DocumentUploadRequest): DocumentUploadValidationResult {
  const errors: string[] = [];
  const sanitizedFileName = sanitizeFileName(request.fileName);
  const extension = extensionOf(sanitizedFileName);

  if (!context.tenant.permissions.includes("document.upload")) errors.push("permission_denied");
  if (!request.ownerRecordId.trim()) errors.push("owner_record_required");
  if (!request.fileName.trim()) errors.push("file_name_required");
  if (!allowedMimeTypes.has(request.mimeType)) errors.push("mime_type_not_allowed");
  if (!allowedExtensions.has(extension)) errors.push("file_extension_not_allowed");
  if (request.sizeBytes <= 0) errors.push("file_empty");
  if (request.sizeBytes > maxUploadBytes) errors.push("file_too_large");
  if (sanitizedFileName.toLowerCase().endsWith(".exe")) errors.push("file_extension_not_allowed");

  if (errors.length > 0) {
    return {
      accepted: false,
      errors: Array.from(new Set(errors)),
      intent: null
    };
  }

  return {
    accepted: true,
    errors: [],
    intent: {
      status: "validated",
      tenantId: context.tenant.tenantId,
      ownerModule: request.ownerModule,
      ownerRecordId: request.ownerRecordId,
      documentType: request.documentType,
      fileName: sanitizedFileName,
      mimeType: request.mimeType,
      sizeBytes: request.sizeBytes,
      uploadedBy: context.tenant.userId,
      uploadedAt: new Date().toISOString(),
      visibility: request.visibility,
      version: 1,
      persistence: "metadata-only",
      securityChecks: ["tenant-context", "document.upload-permission", "mime-allowlist", "extension-allowlist", "size-limit", "filename-sanitized"],
      futureStorageOwner: "Documents storage service with object storage, malware scanning, checksum, retention, and audit persistence"
    }
  };
}
