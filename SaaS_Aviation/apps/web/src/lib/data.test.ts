import test from "node:test";
import assert from "node:assert/strict";
import { sampleRequestContext } from "@saas-aviation/shared";
import { currentSession, data, getStock } from "./data.js";
import { getDashboardData } from "./dashboard.js";
import { getDocumentCenterReadModel, getEntityDocumentReadModel, validateDocumentUpload } from "./documents.js";
import { getCompanyInventoryReadModel, getPart360ReadModel, getStock360ReadModel } from "./part-stock.js";

test("web data keeps internal and external stock separated", () => {
  assert.equal(data.internalStock.every((stock) => stock.source === "internal"), true);
  assert.equal(data.externalStock.every((stock) => stock.source === "external"), true);
});

test("web data does not hide Qty 0 stock", () => {
  const stock = getStock("stock-2");
  assert.equal(stock.qty, 0);
});

test("web session is tied to the seeded tenant", () => {
  assert.equal(currentSession.tenant.code, "ACI770");
  assert.equal(currentSession.user.tenantId, currentSession.tenant.id);
  assert.equal(data.companies.every((company) => company.tenantId === currentSession.tenant.id), true);
});


test("web dashboard data is generated from the current tenant context", () => {
  const dashboard = getDashboardData();

  assert.equal(dashboard.tenantId, currentSession.tenant.id);
  assert.equal(dashboard.metrics.some((metric) => metric.label === "RFQs open"), true);
  assert.equal(dashboard.quotesPending.every((quote) => quote.rfqId.startsWith("RFQ-")), true);
  assert.equal(dashboard.stockValue.zeroQtyVisible, 1);
});

test("web dashboard adapter can return an empty other-tenant dashboard", () => {
  const dashboard = getDashboardData({
    tenant: {
      ...sampleRequestContext.tenant,
      tenantId: "tenant-other",
      tenantCode: "OTHER",
      tenantName: "Other Tenant"
    }
  });

  assert.equal(dashboard.rfqsOpen.length, 0);
  assert.equal(dashboard.documentsPending.length, 0);
  assert.equal(dashboard.stockValue.totalValue, 0);
});

test("web Part 360 adapter is tenant-scoped and exposes boundary actions", () => {
  const part360 = getPart360ReadModel("part-1");

  assert.equal(part360?.tenantId, currentSession.tenant.id);
  assert.equal(part360?.quickActions.every((action) => action.mode === "boundary" && action.persistence === "none"), true);
  assert.equal(part360?.rfqs.every((rfq) => rfq.rfqId.startsWith("RFQ-")), true);
});

test("web Stock 360 adapter preserves Qty 0 and reservation boundary", () => {
  const stock360 = getStock360ReadModel("stock-2");

  assert.equal(stock360?.stock.qty, 0);
  assert.ok(stock360?.quickActions.find((action) => action.id === "reserve-stock"));
  assert.equal(stock360?.quickActions.every((action) => action.requiredData.includes("tenantId")), true);
});

test("web Company Inventory adapter counts unique stock totals", () => {
  const inventory = getCompanyInventoryReadModel();

  assert.equal(inventory.tenantId, currentSession.tenant.id);
  assert.equal(inventory.totals.zeroQtyRows, 1);
  assert.equal(inventory.rows.every((row) => row.tenantId === currentSession.tenant.id), true);
});

test("web documents adapter returns tenant-scoped document center and entity documents", () => {
  const center = getDocumentCenterReadModel();
  const stockDocuments = getEntityDocumentReadModel("stock", "stock-1");

  assert.equal(center.tenantId, currentSession.tenant.id);
  assert.equal(center.documents.every((document) => document.tenantId === currentSession.tenant.id), true);
  assert.equal(stockDocuments.documents.length, 2);
});

test("web documents upload adapter validates unsafe upload metadata", () => {
  const rejected = validateDocumentUpload({
    ownerModule: "stock",
    ownerRecordId: "stock-1",
    documentType: "Certificate",
    fileName: "malware.exe",
    mimeType: "application/x-msdownload",
    sizeBytes: 200,
    visibility: "restricted"
  });

  assert.equal(rejected.accepted, false);
  assert.ok(rejected.errors.includes("mime_type_not_allowed"));
});
