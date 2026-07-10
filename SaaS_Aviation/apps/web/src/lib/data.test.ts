import test from "node:test";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { sampleRequestContext } from "@saas-aviation/shared";
import { currentSession, data, getCompany360ReadModel, getCompanyListReadModel, getStock } from "./data.js";
import { getCtoStatus } from "./cto-status.js";
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

test("web Company list read model searches, filters, sorts, and paginates tenant companies", () => {
  const list = getCompanyListReadModel({
    query: "better",
    type: "supplier",
    status: "active",
    sort: "name",
    direction: "asc",
    page: 1,
    pageSize: 2
  });

  assert.equal(list.tenantId, currentSession.tenant.id);
  assert.equal(list.state, "ready");
  assert.equal(list.rows.length, 1);
  assert.equal(list.rows[0]?.company.name, "Better Aviation Products");
  assert.equal(list.rows[0]?.primaryContact?.email, "maria@example.test");
  assert.deepEqual(list.filters.availableTypes, ["customer", "supplier", "owner", "repair-vendor", "mixed"]);
  assert.equal(list.pagination.totalRows, 1);
});

test("web Company list read model exposes empty state for unmatched searches", () => {
  const list = getCompanyListReadModel({ query: "no-match-company" });

  assert.equal(list.state, "empty");
  assert.equal(list.rows.length, 0);
  assert.match(list.emptyState.detail, /No tenant company matches/);
});

test("web Company 360 read model aggregates contacts inventory documents activity and workflow boundaries", () => {
  const company360 = getCompany360ReadModel("company-1527");

  assert.equal(company360.company.name, "Better Aviation Products");
  assert.equal(company360.contacts.length, 1);
  assert.equal(company360.inventorySummary.externalUnits, 12);
  assert.equal(company360.documents.documents.length, 1);
  assert.ok(company360.boundaryActions.find((action) => action.id === "create-contact"));
  assert.ok(company360.boundaryActions.find((action) => action.id === "add-document"));
  assert.ok(company360.boundaryActions.find((action) => action.id === "create-rfq"));
  assert.equal(company360.commercialActivity.rfqs.state, "empty");
  assert.equal(company360.commercialActivity.purchaseOrders.rows.length, 1);
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

test("CTO status covers every requested module with a next action", () => {
  const status = getCtoStatus();
  const expectedModules = [
    "Core", "Authentication", "Dashboard", "Company 360", "Part 360", "Stock 360", "Company Inventory",
    "Documents", "Warehouse", "RFQ", "Supplier Quotes", "Customer Quotes", "Purchase Orders", "Sales Orders",
    "Repair / Exchange / Lease", "Accounting", "Reports", "Administration", "AI", "API", "Security", "Multi-Tenant"
  ];

  assert.equal(status.modules.length, expectedModules.length);
  assert.deepEqual(status.modules.map((row) => row.module), expectedModules);
  assert.equal(status.modules.every((row) => row.nextAction.length > 0), true);
  assert.equal(status.modules.every((row) => row.progressPct >= 0 && row.progressPct <= 100), true);
  assert.ok(status.blockers.length > 0);
});

test("CTO status exposes static build and deployment metadata", () => {
  const status = getCtoStatus();

  assert.equal(status.buildMetadata.branch, "main");
  assert.equal(status.buildMetadata.latestLocalCommit, "bb0ba80");
  assert.equal(status.buildMetadata.latestOriginMainCommit, "bb0ba80");
  assert.equal(status.buildMetadata.staticExportMode, "Next.js output export");
  assert.match(status.buildMetadata.buildTimestamp, /^2026-07-07T/);

  assert.equal(status.deployment.lastDeployedCommit, "bb0ba80");
  assert.equal(status.deployment.environment, "staging/public static frontend");
  assert.equal(status.deployment.protectedAdminStatus, "/SaaS_Aviation/admin/ protected by Apache Basic Auth");
  assert.match(status.deployment.backupPath, /SaaS_Aviation_backup_20260707_155253$/);
});

test("CTO status exposes check and security metadata", () => {
  const status = getCtoStatus();

  assert.equal(status.checks.testStatus, "passing");
  assert.equal(status.checks.typecheckStatus, "passing");
  assert.equal(status.checks.lintStatus, "passing");
  assert.equal(status.checks.buildStatus, "passing");
  assert.match(status.checks.lastCheckedAt, /^2026-07-07T/);

  assert.equal(status.security.adminProtectedByBasicAuth, true);
  assert.equal(status.security.ctoRouteHiddenFromPublicSidebar, true);
  assert.equal(status.security.apiRuntimeDeployed, false);
  assert.equal(status.security.dbOrYoyamicTouched, false);
});

test("CTO status activity timeline includes the last ten commits with authors", () => {
  const status = getCtoStatus();

  assert.equal(status.activity.length, 10);
  assert.deepEqual(
    status.activity.map((entry) => entry.commit),
    ["bb0ba80", "3c32468", "20bdc67", "088e9d8", "362c4a7", "f14ddd4", "46f6e72", "366e375", "05b04d7", "5372dc2"]
  );
  assert.equal(status.activity.every((entry) => entry.author.length > 0), true);
});

test("CTO dashboard is not exposed from public navigation", () => {
  const sidebarSource = readFileSync(new URL("../components/erp/sidebar.tsx", import.meta.url), "utf8");

  assert.equal(sidebarSource.includes("/admin/cto"), false);
  assert.equal(sidebarSource.includes("CTO Dashboard"), false);
});

test("static CTO admin route ships only safe Basic Auth config", () => {
  const htaccess = readFileSync(new URL("../../public/admin/.htaccess", import.meta.url), "utf8");

  assert.match(htaccess, /AuthType Basic/);
  assert.match(htaccess, /Require user cto/);
  assert.match(htaccess, /AuthUserFile \/var\/www\/vhosts\/aerocanada-industries\.com\/\.htpasswd_saas_cto/);
  assert.equal(htaccess.includes("Yoyamic"), false);
  assert.equal(htaccess.includes("cto:"), false);
});
