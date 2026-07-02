import {
  buildDocumentCenterReadModel,
  buildEntityDocumentReadModel,
  sampleAdminUser,
  sampleAuditEvents,
  sampleDocumentLinks,
  sampleDocuments,
  sampleDocumentVersions,
  sampleRequestContext,
  sampleTenant,
  validateDocumentUploadRequest,
  type DocumentOwnerModule,
  type DocumentUploadRequest,
  type RequestContext
} from "@saas-aviation/shared";

function contextFromCurrentSession(): RequestContext {
  return {
    tenant: {
      ...sampleRequestContext.tenant,
      tenantId: sampleTenant.id,
      tenantCode: sampleTenant.code,
      tenantName: sampleTenant.name,
      userId: sampleAdminUser.id,
      roles: sampleAdminUser.roles,
      permissions: sampleAdminUser.permissions
    }
  };
}

function source() {
  return {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks,
    auditEvents: sampleAuditEvents
  };
}

export function getDocumentCenterReadModel(context: RequestContext = contextFromCurrentSession()) {
  return buildDocumentCenterReadModel(context, source());
}

export function getEntityDocumentReadModel(ownerModule: DocumentOwnerModule, ownerRecordId: string, context: RequestContext = contextFromCurrentSession()) {
  return buildEntityDocumentReadModel(context, ownerModule, ownerRecordId, source());
}

export function validateDocumentUpload(request: DocumentUploadRequest, context: RequestContext = contextFromCurrentSession()) {
  return validateDocumentUploadRequest(context, request);
}
