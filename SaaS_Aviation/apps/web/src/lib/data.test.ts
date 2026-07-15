import test from "node:test";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { createCompanySchema, createContactSchema, sampleRequestContext } from "@saas-aviation/shared";
import { currentSession, data, getCompany360ReadModel, getCompanyListReadModel, getStock } from "./data.js";
import { getCtoStatus } from "./cto-status.js";
import { assertPersistentApiMode, getDataSourceConfig, initialRecordsForMode } from "./data-source-mode.js";
import { persistentApi, PersistentApiError } from "./persistent-api.js";
import { getDashboardData } from "./dashboard.js";
import { getDocumentCenterReadModel, getEntityDocumentReadModel, validateDocumentUpload } from "./documents.js";
import { getCompanyInventoryReadModel, getPart360ReadModel, getStock360ReadModel } from "./part-stock.js";
import { normalizeFormData } from "./form-normalization.js";

test("form normalization omits blank optionals, trims values, and preserves validation", () => {
  const data = new FormData();
  data.set("name", "  Normalized Company  "); data.set("email", "   "); data.set("website", "");
  data.set("roles", " customer, supplier "); data.set("tags", " verified, aviation ");
  const normalized = normalizeFormData(data, { arrayFields: ["roles", "tags"] });
  assert.deepEqual(normalized, { name: "Normalized Company", roles: ["customer", "supplier"], tags: ["verified", "aviation"] });
  assert.equal(createCompanySchema.parse(normalized).email, undefined);
  assert.throws(() => createCompanySchema.parse({ ...normalized, email: "invalid" }));
  assert.throws(() => createCompanySchema.parse({ ...normalized, website: "invalid" }));
  assert.throws(() => createCompanySchema.parse({ ...normalized, name: "" }));
  assert.throws(() => createContactSchema.parse({ firstName: "A", lastName: "B", email: "invalid" }));
});

test("web data keeps internal and external stock separated", () => {
  assert.equal(data.internalStock.every((stock) => stock.source === "internal"), true);
  assert.equal(data.externalStock.every((stock) => stock.source === "external"), true);
});

test("web data does not hide Qty 0 stock", () => {
  const stock = getStock("stock-2");
  assert.equal(stock.qty, 0);
});

test("web session is tied to the seeded tenant", () => {
  assert.equal(currentSession.tenant.code, "aci770");
  assert.equal(currentSession.user.tenantId, currentSession.tenant.id);
  assert.equal(data.companies.every((company) => company.tenantId === currentSession.tenant.id), true);
});

test("web data source mode defaults to explicit sample static mode", () => {
  const previousMode = process.env.NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE;
  const previousUrl = process.env.NEXT_PUBLIC_SAAS_API_BASE_URL;
  delete process.env.NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE;
  delete process.env.NEXT_PUBLIC_SAAS_API_BASE_URL;

  try {
    const config = getDataSourceConfig();
    assert.equal(config.mode, "sample-static");
    assert.equal(config.apiBaseUrl, null);
    assert.equal(config.staticExport, true);
    assert.throws(() => assertPersistentApiMode(config), /Persistent API mode is not enabled/);
  } finally {
    if (previousMode) process.env.NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE = previousMode;
    if (previousUrl) process.env.NEXT_PUBLIC_SAAS_API_BASE_URL = previousUrl;
  }
});

test("persistent API client requires explicit API mode and never falls back to sample data", async () => {
  assert.deepEqual(initialRecordsForMode({ mode: "persistent-api", apiBaseUrl: "/api", staticExport: true }, [{ id: "fixture" }]), []);
  assert.deepEqual(initialRecordsForMode({ mode: "sample-static", apiBaseUrl: null, staticExport: true }, [{ id: "fixture" }]), [{ id: "fixture" }]);
  await assert.rejects(
    persistentApi.listCompanies({
      mode: "sample-static",
      apiBaseUrl: null,
      staticExport: true
    }),
    /Persistent API mode is not enabled/
  );
});

test("persistent API client calls configured API and surfaces validation errors", async () => {
  const originalFetch = globalThis.fetch;
  const calls: string[] = [];
  globalThis.fetch = (async (input: string | URL | Request) => {
    calls.push(String(input));
    if (String(input).endsWith("/v1/companies")) {
      return new Response(JSON.stringify({ data: [{ id: "company-db-1", tenantId: "tenant-db", name: "DB Company", roles: ["customer"] }] }), {
        status: 200,
        headers: { "Content-Type": "application/json", "X-Correlation-ID": "api-company-test" }
      });
    }
    return new Response(JSON.stringify({ error: "validation_error" }), {
      status: 400,
      headers: { "Content-Type": "application/json" }
    });
  }) as typeof fetch;

  try {
    const config = { mode: "persistent-api" as const, apiBaseUrl: "http://127.0.0.1:4107", staticExport: true };
    const companies = await persistentApi.listCompanies(config);
    assert.equal(companies[0]?.name, "DB Company");
    assert.equal(calls[0], "http://127.0.0.1:4107/v1/companies");

    await assert.rejects(persistentApi.createPart({}, config), (error) => {
      assert.equal(error instanceof PersistentApiError, true);
      assert.equal((error as PersistentApiError).status, 400);
      return true;
    });
  } finally {
    globalThis.fetch = originalFetch;
  }
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
  const company360 = getCompany360ReadModel("demo-co-1527");

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
  assert.equal(status.buildMetadata.latestLocalCommit, "Harden Company 360 persistent workflows");
  assert.equal(status.buildMetadata.latestOriginMainCommit, "5862d57");
  assert.equal(status.buildMetadata.staticExportMode, "Next.js output export");
  assert.match(status.buildMetadata.buildTimestamp, /^2026-07-14T/);

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
  assert.match(status.checks.lastCheckedAt, /^2026-07-14T/);

  assert.equal(status.security.adminProtectedByBasicAuth, true);
  assert.equal(status.security.ctoRouteHiddenFromPublicSidebar, true);
  assert.equal(status.security.apiRuntimeDeployed, false);
  assert.equal(status.security.dbOrYoyamicTouched, false);
  assert.equal(status.security.notes.some((note) => note.includes("PostgreSQL provider code is local only")), true);
});

test("CTO status activity timeline includes the last ten commits with authors", () => {
  const status = getCtoStatus();

  assert.equal(status.activity.length, 10);
  assert.deepEqual(
    status.activity.map((entry) => entry.commit),
    ["c0d901d", "e473550", "4df9e14", "11baac6", "786aaa7", "8a266ba", "ba1755f", "7e7fe5e", "d803c9d", "bb0ba80"]
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

test("public Part and Stock workspaces use persistent APIs without fixture adapters", () => {
  const partPage = readFileSync(new URL("../app/parts/page.tsx", import.meta.url), "utf8");
  const stockPage = readFileSync(new URL("../app/stock/internal/page.tsx", import.meta.url), "utf8");
  const partWorkspace = readFileSync(new URL("../components/modules/persistent-part-workspace.tsx", import.meta.url), "utf8");
  const stockWorkspace = readFileSync(new URL("../components/modules/persistent-stock-workspace.tsx", import.meta.url), "utf8");
  assert.equal(partPage.includes("@/lib/data"), false); assert.equal(partPage.includes("@/lib/part-stock"), false);
  assert.equal(stockPage.includes("@/lib/data"), false); assert.equal(stockPage.includes("@/lib/part-stock"), false);
  assert.match(partWorkspace, /persistentApi\.listParts/); assert.match(partWorkspace, /persistentApi\.listStock/);
  assert.match(stockWorkspace, /persistentApi\.listStock/); assert.match(stockWorkspace, /Legacy stock migration not yet completed/);
  assert.equal(partWorkspace.includes("getPart360ReadModel"), false); assert.equal(stockWorkspace.includes("getStock360ReadModel"), false);
});

test("public Company workspace never imports or serializes sample Company records", () => {
  const companyPage = readFileSync(new URL("../app/companies/page.tsx", import.meta.url), "utf8");
  const companyDetailPage = readFileSync(new URL("../app/companies/[id]/page.tsx", import.meta.url), "utf8");
  const companyWorkspace = readFileSync(new URL("../components/modules/company-production-workspace.tsx", import.meta.url), "utf8");

  assert.equal(companyPage.includes("@/lib/data"), false);
  assert.equal(companyPage.includes("getCompanyListReadModel"), false);
  assert.match(companyPage, /initialCompanies=\{\[\]\}/);
  assert.match(companyDetailPage, /mode === "persistent-api"\) return \[\{ id: "persistent" \}\]/);
  assert.match(companyWorkspace, /setCompanies\(\[\]\); setSelected\(null\)/);
  for (const fixtureId of ["company-5263", "company-1527", "company-4188"]) {
    assert.equal(companyPage.includes(fixtureId), false);
    assert.equal(companyWorkspace.includes(fixtureId), false);
  }
});
