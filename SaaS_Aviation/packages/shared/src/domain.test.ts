import test from "node:test";
import assert from "node:assert/strict";
import { buildTenantDashboard } from "./dashboard-service.js";
import {
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
  sampleSupplierQuotes,
  sampleTenant,
  sampleUsers
} from "./sample-data.js";

test("Qty 0 remains represented as zero", () => {
  const zeroQty = sampleInternalStock.find((item) => item.qty === 0);
  assert.ok(zeroQty);
  assert.equal(zeroQty?.qty, 0);
});

test("RFQ_ID remains present on RFQ summaries", () => {
  assert.ok(sampleRfqs.every((rfq) => rfq.rfqId.length > 0));
});

test("first tenant seed has an admin user and tenant-owned records", () => {
  const admin = sampleUsers.find((user) => user.tenantId === sampleTenant.id && user.roles.includes("owner_admin"));
  assert.ok(admin);
  assert.equal(sampleTenant.name, "AEROCANADA INDUSTRIES 770 INC.");
  assert.equal(sampleInternalStock.every((item) => item.tenantId === sampleTenant.id), true);
  assert.equal(sampleRfqs.every((rfq) => rfq.tenantId === sampleTenant.id), true);
});


test("tenant-aware dashboard is scoped and preserves workflow keys", () => {
  const dashboard = buildTenantDashboard(sampleRequestContext, {
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

  assert.equal(dashboard.tenantId, sampleTenant.id);
  assert.equal(dashboard.rfqsOpen.every((rfq) => rfq.tenantId === sampleTenant.id && rfq.rfqId.startsWith("RFQ-")), true);
  assert.equal(dashboard.quotesPending.every((quote) => quote.tenantId === sampleTenant.id && quote.rfqId.startsWith("RFQ-")), true);
  assert.equal(dashboard.stockValue.zeroQtyVisible, 1);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "repair"), true);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "exchange"), true);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "lease"), true);
});

test("tenant-aware dashboard excludes other tenant data", () => {
  const dashboard = buildTenantDashboard(
    {
      tenant: {
        ...sampleRequestContext.tenant,
        tenantId: "tenant-other",
        tenantCode: "OTHER",
        tenantName: "Other Tenant"
      }
    },
    {
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
    }
  );

  assert.equal(dashboard.rfqsOpen.length, 0);
  assert.equal(dashboard.quotesPending.length, 0);
  assert.equal(dashboard.stockValue.totalValue, 0);
  assert.equal(dashboard.accountingAlerts.length, 0);
});
