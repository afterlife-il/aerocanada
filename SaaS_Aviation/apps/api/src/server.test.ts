import test from "node:test";
import assert from "node:assert/strict";
import { SampleDataSource } from "./adapters/sample-data-source.js";
import { InMemoryAuthProvider, requestContextFromSession } from "./auth/auth-provider.js";
import { openApiDocument } from "./openapi/openapi.js";
import { sampleRequestContext } from "@saas-aviation/shared";

test("sample data source preserves internal and external stock separation", async () => {
  const data = new SampleDataSource();
  assert.equal((await data.listInternalStock(sampleRequestContext)).every((item) => item.source === "internal"), true);
  assert.equal((await data.listExternalStock(sampleRequestContext)).every((item) => item.source === "external"), true);
});

test("sample data source enforces tenant scoping", async () => {
  const data = new SampleDataSource();
  const emptyContext = {
    tenant: {
      ...sampleRequestContext.tenant,
      tenantId: "tenant-other"
    }
  };

  assert.equal((await data.listInternalStock(emptyContext)).length, 0);
  assert.equal((await data.listCompanies(emptyContext)).length, 0);
});

test("sample data source exposes tenant-scoped Part 360, Stock 360, and Company Inventory read models", async () => {
  const data = new SampleDataSource();
  const part360 = await data.getPart360(sampleRequestContext, "part-1");
  const stock360 = await data.getStock360(sampleRequestContext, "stock-2");
  const inventory = await data.getCompanyInventory(sampleRequestContext);

  assert.equal(part360?.tenantId, sampleRequestContext.tenant.tenantId);
  assert.equal(part360?.quickActions.every((action) => action.persistence === "none"), true);
  assert.equal(stock360?.stock.qty, 0);
  assert.equal(stock360?.quickActions.some((action) => action.id === "reserve-stock"), true);
  assert.equal(inventory.totals.zeroQtyRows, 1);
  assert.equal(inventory.rows.every((row) => row.tenantId === sampleRequestContext.tenant.tenantId), true);
});

test("password auth creates a tenant-scoped session", async () => {
  const auth = new InMemoryAuthProvider();
  const session = await auth.authenticateWithPassword("ops@aerocanada-industries.com", "ChangeMe!ACI770!");

  assert.ok(session);
  assert.equal(session?.tenant.code, "ACI770");
  assert.equal(session?.user.roles.includes("owner_admin"), true);
  assert.equal(session?.user.permissions.includes("stock.read"), true);

  const context = requestContextFromSession(session!);
  assert.equal(context.tenant.tenantId, "tenant-aci");
});

test("openapi document covers current read routes with component schemas", () => {
  assert.ok(openApiDocument.components.schemas.Company);
  assert.ok(openApiDocument.components.schemas.PartNumber);
  assert.ok(openApiDocument.components.schemas.StockItem);
  assert.ok(openApiDocument.components.schemas.Part360ReadModel);
  assert.ok(openApiDocument.components.schemas.Stock360ReadModel);
  assert.ok(openApiDocument.components.schemas.CompanyInventoryReadModel);
  assert.ok(openApiDocument.components.schemas.AuditEvent);
  assert.equal(openApiDocument.paths["/v1/session"].get.operationId, "getSession");
  assert.equal(openApiDocument.paths["/v1/auth/login"].post.operationId, "loginWithPassword");
  assert.equal(openApiDocument.paths["/v1/audit"].get.operationId, "listAuditEvents");
  assert.equal(openApiDocument.paths["/v1/parts/{id}/360"].get.operationId, "getPart360");
  assert.equal(openApiDocument.paths["/v1/stock/{id}/360"].get.operationId, "getStock360");
  assert.equal(openApiDocument.paths["/v1/company-inventory"].get.operationId, "getCompanyInventory");
});
