import test from "node:test";
import assert from "node:assert/strict";
import { buildTenantDashboard } from "./dashboard-service.js";
import { buildCompanyInventoryReadModel, buildPart360ReadModel, buildStock360ReadModel } from "./part-stock-service.js";
import {
  sampleAccountingAlerts,
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

test("Part 360 read model links tenant-scoped stock, RFQs, quotes, documents, and boundary actions", () => {
  const part360 = buildPart360ReadModel(sampleRequestContext, "part-1", {
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
  });

  assert.ok(part360);
  assert.equal(part360?.tenantId, sampleTenant.id);
  assert.equal(part360?.part.pn, "03-1802-2001");
  assert.equal(part360?.stockAvailability.internalUnits, 1);
  assert.equal(part360?.rfqs.every((rfq) => rfq.rfqId.startsWith("RFQ-")), true);
  assert.equal(part360?.customerQuotes.every((quote) => quote.rfqId.startsWith("RFQ-")), true);
  assert.equal(part360?.documents.some((document) => document.documentType === "8130-3"), true);
  assert.equal(part360?.quickActions.every((action) => action.mode === "boundary" && action.persistence === "none"), true);
  assert.ok(part360?.quickActions.find((action) => action.id === "create-rfq")?.requiredData.includes("tenantId"));
});

test("Part 360 read model returns null when part belongs to another tenant", () => {
  const part360 = buildPart360ReadModel(
    {
      tenant: {
        ...sampleRequestContext.tenant,
        tenantId: "tenant-other"
      }
    },
    "part-1",
    {
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
    }
  );

  assert.equal(part360, null);
});

test("Stock 360 read model keeps Qty 0 stock visible and exposes non-persistent action boundaries", () => {
  const stock360 = buildStock360ReadModel(sampleRequestContext, "stock-2", {
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
  });

  assert.ok(stock360);
  assert.equal(stock360?.stock.qty, 0);
  assert.equal(stock360?.stock.tenantId, sampleTenant.id);
  assert.equal(stock360?.lifecycle.some((event) => event.entityId === "stock-2"), true);
  assert.ok(stock360?.quickActions.find((action) => action.id === "reserve-stock")?.futureOwner.includes("Inventory"));
  assert.equal(stock360?.quickActions.every((action) => action.persistence === "none"), true);
});

test("Company Inventory read model summarizes stock by company without leaking other tenants", () => {
  const inventory = buildCompanyInventoryReadModel(sampleRequestContext, {
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
  });

  assert.equal(inventory.tenantId, sampleTenant.id);
  assert.equal(inventory.rows.every((row) => row.tenantId === sampleTenant.id), true);
  assert.equal(inventory.totals.zeroQtyRows, 1);
  assert.ok(inventory.rows.find((row) => row.companyName === "AeroCanada Industries 770")?.stockLines.length);
  assert.ok(inventory.quickActions.find((action) => action.id === "add-stock")?.requiredData.includes("ownerCompanyId"));
});
