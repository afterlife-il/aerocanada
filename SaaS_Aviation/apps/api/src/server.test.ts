import test from "node:test";
import assert from "node:assert/strict";
import { SampleDataSource } from "./adapters/sample-data-source.js";
import { openApiDocument } from "./openapi/openapi.js";

test("sample data source preserves internal and external stock separation", async () => {
  const data = new SampleDataSource();
  assert.equal((await data.listInternalStock()).every((item) => item.source === "internal"), true);
  assert.equal((await data.listExternalStock()).every((item) => item.source === "external"), true);
});


test("openapi document covers current read routes with component schemas", () => {
  assert.ok(openApiDocument.components.schemas.Company);
  assert.ok(openApiDocument.components.schemas.PartNumber);
  assert.ok(openApiDocument.components.schemas.StockItem);
  assert.ok(openApiDocument.components.schemas.AuditEvent);
  assert.equal(openApiDocument.paths["/v1/session"].get.operationId, "getSession");
  assert.equal(openApiDocument.paths["/v1/audit"].get.operationId, "listAuditEvents");
});
