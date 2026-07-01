import test from "node:test";
import assert from "node:assert/strict";
import { sampleRequestContext } from "@saas-aviation/shared";
import { currentSession, data, getStock } from "./data.js";
import { getDashboardData } from "./dashboard.js";

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
