import {
  buildTenantDashboard,
  sampleAccountingAlerts,
  sampleAuditEvents,
  sampleCompanies,
  sampleDocumentAlerts,
  sampleExternalStock,
  sampleInternalStock,
  sampleOrders,
  sampleQuotes,
  sampleRequestContext,
  sampleRfqs,
  sampleServiceWorkflows,
  sampleSupplierQuotes
} from "@saas-aviation/shared";
import type { DashboardData, RequestContext } from "@saas-aviation/shared";

export function getDashboardData(context: RequestContext = sampleRequestContext): DashboardData {
  return buildTenantDashboard(context, {
    companies: sampleCompanies,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    accountingAlerts: sampleAccountingAlerts,
    auditEvents: sampleAuditEvents
  });
}
