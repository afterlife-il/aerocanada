import {
  buildCompanyInventoryReadModel,
  buildPart360ReadModel,
  buildStock360ReadModel,
  sampleAdminUser,
  sampleAuditEvents,
  sampleCompanies,
  sampleDocumentAlerts,
  sampleExternalStock,
  sampleInternalStock,
  sampleOrders,
  sampleParts,
  sampleQuotes,
  sampleRequestContext,
  sampleRfqs,
  sampleServiceWorkflows,
  sampleSupplierQuotes,
  sampleTenant,
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
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  };
}

export function getPart360ReadModel(id: string, context: RequestContext = contextFromCurrentSession()) {
  return buildPart360ReadModel(context, id, source());
}

export function getStock360ReadModel(id: string, context: RequestContext = contextFromCurrentSession()) {
  return buildStock360ReadModel(context, id, source());
}

export function getCompanyInventoryReadModel(context: RequestContext = contextFromCurrentSession()) {
  return buildCompanyInventoryReadModel(context, source());
}
