import test from "node:test";
import assert from "node:assert/strict";
import type { AddressInfo } from "node:net";
import type { Express } from "express";
import type { AuthSession, DocumentCenterReadModel, DocumentOwnerModule, DocumentReadModel, EntityDocumentReadModel, Permission, RequestContext } from "@saas-aviation/shared";
import { SampleDataSource } from "./adapters/sample-data-source.js";
import { InMemoryAuthProvider, requestContextFromSession } from "./auth/auth-provider.js";
import type { AuthProvider } from "./auth/auth-provider.js";
import { createApp } from "./server.js";
import { openApiDocument } from "./openapi/openapi.js";
import { InMemoryCorePersistence } from "./persistence/core-memory-repository.js";
import { dryRunYoyamicCoreImport, type LegacyYoyamicSnapshot } from "./importers/yoyamic-core-importer.js";
import {
  assertYoyamicSelectOnly,
  buildLegacyMappingRecord,
  buildYoyamicBatchPlan,
  buildYoyamicReadQuery,
  summarizeYoyamicReconciliation
} from "./importers/yoyamic-readonly-source.js";
import { getPersistenceConfig } from "./persistence/config.js";
import { createCorePersistenceProvider } from "./persistence/provider.js";
import {
  buildDocumentCenterReadModel,
  buildEntityDocumentReadModel,
  sampleAuditEvents,
  sampleDocumentLinks,
  sampleDocuments,
  sampleDocumentVersions,
  sampleRequestContext,
  sampleTenants,
  sampleUsers
} from "@saas-aviation/shared";

class StaticAuthProvider implements AuthProvider {
  private readonly sessions = new Map<string, AuthSession>();

  constructor(sessions: AuthSession[]) {
    sessions.forEach((session) => this.sessions.set(session.token, session));
  }

  async authenticateWithPassword(): Promise<AuthSession | null> {
    return null;
  }

  async getCurrentSession(authorizationHeader?: string): Promise<AuthSession | null> {
    const token = authorizationHeader?.replace(/^Bearer\s+/i, "");
    return token ? (this.sessions.get(token) ?? null) : null;
  }

  async revokeSession(token: string): Promise<void> {
    this.sessions.delete(token);
  }

  async createLoginAuditEvent(): Promise<void> {
    return;
  }
}

class MalformedDocumentDataSource extends SampleDataSource {
  private readonly malformedSource = {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks.filter((link) => !(link.documentId === "doc-cert-stock-1" && link.relation === "primary")),
    auditEvents: sampleAuditEvents
  };

  override async listDocuments(context: RequestContext): Promise<DocumentCenterReadModel> {
    return buildDocumentCenterReadModel(context, this.malformedSource);
  }

  override async getDocument(context: RequestContext, id: string): Promise<DocumentReadModel | null> {
    return buildDocumentCenterReadModel(context, this.malformedSource).documents.find((document) => document.id === id) ?? null;
  }

  override async listEntityDocuments(
    context: RequestContext,
    ownerModule: DocumentOwnerModule,
    ownerRecordId: string
  ): Promise<EntityDocumentReadModel> {
    return buildEntityDocumentReadModel(context, ownerModule, ownerRecordId, this.malformedSource);
  }
}

function sessionWithPermissions(token: string, permissions: Permission[]): AuthSession {
  const user = sampleUsers[0];
  const tenant = sampleTenants[0];
  assert.ok(user);
  assert.ok(tenant);
  return {
    token,
    user: {
      id: user.id,
      tenantId: user.tenantId,
      email: user.email,
      name: user.name,
      status: user.status,
      roles: user.roles,
      permissions,
      mfaEnabled: user.mfaEnabled,
      authProviders: user.authProviders,
      createdAt: user.createdAt
    },
    tenant,
    expiresAt: new Date(Date.now() + 60_000).toISOString()
  };
}

async function httpGet(app: Express, path: string, token?: string): Promise<{ status: number; body: unknown }> {
  return httpRequest(app, "GET", path, token);
}

async function httpRequest(app: Express, method: string, path: string, token?: string, body?: unknown): Promise<{ status: number; body: unknown }> {
  const server = app.listen(0);
  try {
    await new Promise<void>((resolve) => server.once("listening", resolve));
    const { port } = server.address() as AddressInfo;
    const init: RequestInit = {
      method,
      headers: {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(body ? { "Content-Type": "application/json" } : {})
      }
    };
    if (body) {
      init.body = JSON.stringify(body);
    }
    const response = await fetch(`http://127.0.0.1:${port}${path}`, init);
    return {
      status: response.status,
      body: await response.json()
    };
  } finally {
    await new Promise<void>((resolve, reject) => server.close((error) => (error ? reject(error) : resolve())));
  }
}

async function httpPost(app: Express, path: string, token: string, body: unknown): Promise<{ status: number; body: unknown }> {
  return httpRequest(app, "POST", path, token, body);
}

async function httpPatch(app: Express, path: string, token: string, body: unknown): Promise<{ status: number; body: unknown }> {
  return httpRequest(app, "PATCH", path, token, body);
}

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

test("sample data source exposes tenant-scoped documents and upload validation", async () => {
  const data = new SampleDataSource();
  const documents = await data.listDocuments(sampleRequestContext);
  const stockDocuments = await data.listEntityDocuments(sampleRequestContext, "stock", "stock-1");
  const upload = await data.validateDocumentUpload(sampleRequestContext, {
    ownerModule: "stock",
    ownerRecordId: "stock-1",
    documentType: "Certificate",
    fileName: "8130.pdf",
    mimeType: "application/pdf",
    sizeBytes: 1000,
    visibility: "customer-shareable",
    notes: ""
  });

  assert.equal(documents.tenantId, sampleRequestContext.tenant.tenantId);
  assert.equal(documents.documents.every((document) => document.tenantId === sampleRequestContext.tenant.tenantId), true);
  assert.equal(stockDocuments.documents.length, 2);
  assert.equal(upload.accepted, true);
  assert.equal(upload.intent?.persistence, "metadata-only");
});

test("document read endpoints return HTTP authorization responses", async () => {
  const allowedToken = "allowed-documents";
  const deniedToken = "denied-documents";
  const app = createApp({
    dataSource: new SampleDataSource(),
    auth: new StaticAuthProvider([
      sessionWithPermissions(allowedToken, sampleRequestContext.tenant.permissions),
      sessionWithPermissions(
        deniedToken,
        sampleRequestContext.tenant.permissions.filter((permission) => permission !== "document.read")
      )
    ])
  });

  const documentRoutes = [
    "/v1/documents",
    "/v1/documents/doc-cert-stock-1",
    "/v1/entities/stock/stock-1/documents"
  ];

  for (const route of documentRoutes) {
    assert.equal((await httpGet(app, route)).status, 401, route);
    assert.deepEqual(await httpGet(app, route, deniedToken), {
      status: 403,
      body: { error: "forbidden", permission: "document.read" }
    });
    assert.equal((await httpGet(app, route, allowedToken)).status, 200, route);
  }
});

test("document read endpoints return controlled errors for malformed primary links", async () => {
  const allowedToken = "malformed-documents";
  const app = createApp({
    dataSource: new MalformedDocumentDataSource(),
    auth: new StaticAuthProvider([sessionWithPermissions(allowedToken, sampleRequestContext.tenant.permissions)])
  });

  const documentRoutes = [
    "/v1/documents",
    "/v1/documents/doc-cert-stock-1",
    "/v1/entities/stock/stock-1/documents"
  ];

  for (const route of documentRoutes) {
    assert.deepEqual(await httpGet(app, route, allowedToken), {
      status: 500,
      body: { error: "internal_server_error" }
    });
  }
});

test("persistent CRUD endpoints require authorization and persist local company/contact mutations", async () => {
  const allowedToken = "allowed-core-company";
  const deniedToken = "denied-core-company";
  const app = createApp({
    dataSource: new SampleDataSource(),
    corePersistence: new InMemoryCorePersistence(),
    auth: new StaticAuthProvider([
      sessionWithPermissions(allowedToken, sampleRequestContext.tenant.permissions),
      sessionWithPermissions(
        deniedToken,
        sampleRequestContext.tenant.permissions.filter((permission) => permission !== "company.read")
      )
    ])
  });

  assert.equal((await httpGet(app, "/v1/companies")).status, 401);
  assert.equal((await httpGet(app, "/v1/companies", deniedToken)).status, 403);

  const created = await httpPost(app, "/v1/companies", allowedToken, {
    name: "Aero Persistence Test",
    roles: ["supplier"],
    email: "persist@example.test",
    country: "Canada"
  });
  assert.equal(created.status, 201);
  const createdBody = created.body as { data: { id: string; tenantId: string; createdBy: string } };
  assert.equal(createdBody.data.tenantId, sampleRequestContext.tenant.tenantId);
  assert.equal(createdBody.data.createdBy, sampleRequestContext.tenant.userId);

  const patched = await httpPatch(app, `/v1/companies/${createdBody.data.id}`, allowedToken, { risk: "watch" });
  assert.equal(patched.status, 200);

  const contact = await httpPost(app, `/v1/companies/${createdBody.data.id}/contacts`, allowedToken, {
    firstName: "Casey",
    lastName: "Morgan",
    email: "casey@example.test"
  });
  assert.equal(contact.status, 201);
  assert.equal((await httpGet(app, `/v1/companies/${createdBody.data.id}/contacts`, allowedToken)).status, 200);
});

test("persistent CRUD endpoints support parts and stock with tenant isolation", async () => {
  const allowedToken = "allowed-core-stock";
  const otherTenantToken = "other-core-stock";
  const otherTenantSession = sessionWithPermissions(otherTenantToken, sampleRequestContext.tenant.permissions);
  otherTenantSession.tenant = { ...otherTenantSession.tenant, id: "tenant-other", code: "OTHER", name: "Other Tenant" };
  otherTenantSession.user = { ...otherTenantSession.user, tenantId: "tenant-other" };

  const app = createApp({
    dataSource: new SampleDataSource(),
    corePersistence: new InMemoryCorePersistence(),
    auth: new StaticAuthProvider([sessionWithPermissions(allowedToken, sampleRequestContext.tenant.permissions), otherTenantSession])
  });

  const part = await httpPost(app, "/v1/parts", allowedToken, {
    partNumber: "ABC-123",
    description: "Persistence part",
    manufacturer: "Test Manufacturer",
    aircraft: ["A320"],
    alternates: []
  });
  assert.equal(part.status, 201);
  const partBody = part.body as { data: { id: string; normalizedPartNumber: string } };
  assert.equal(partBody.data.normalizedPartNumber, "ABC123");

  const stock = await httpPost(app, "/v1/stock", allowedToken, {
    partId: partBody.data.id,
    quantity: 0,
    condition: "AR",
    status: "available",
    ownerCompanyId: "company-5263",
    supplierCompanyId: "company-1527",
    tagInfoCompanyId: "company-1527",
    traceabilityCompanyId: "company-4188",
    currency: "USD"
  });
  assert.equal(stock.status, 201);
  const stockBody = stock.body as { data: { id: string; quantity: number; tagInfoCompanyId: string } };
  assert.equal(stockBody.data.quantity, 0);
  assert.equal(stockBody.data.tagInfoCompanyId, "company-1527");

  assert.equal((await httpGet(app, `/v1/stock/${stockBody.data.id}`, allowedToken)).status, 200);
  assert.equal((await httpGet(app, `/v1/stock/${stockBody.data.id}`, otherTenantToken)).status, 404);
});

test("migration validation and importer dry run are repeatable", async () => {
  const persistence = new InMemoryCorePersistence();
  const validation = await persistence.validateMigration();
  assert.equal(validation.ok, true);
  assert.equal(validation.migrations.includes("001_core_persistence.sql"), true);

  const snapshot: LegacyYoyamicSnapshot = {
    companies: [
      { id: 1, name: "Aero One" },
      { id: 2, name: "Aero One" }
    ],
    contacts: [
      { id: 10, companyId: 1, email: "ops@example.test" },
      { id: 11, companyId: 99, email: "ops@example.test" }
    ],
    parts: [
      { id: 20, partNumber: "ABC-123", manufacturer: "OEM" },
      { id: 21, partNumber: "ABC123", manufacturer: "OEM" }
    ],
    stock: [
      { id: 30, partId: 20, ownerCompanyId: 1, supplierCompanyId: 99, quantity: 0, condition: "AR" },
      { id: 31, partId: 99, quantity: -1 }
    ]
  };

  const first = dryRunYoyamicCoreImport(sampleRequestContext.tenant.tenantId, snapshot);
  const second = dryRunYoyamicCoreImport(sampleRequestContext.tenant.tenantId, snapshot);
  assert.deepEqual(first, second);
  assert.equal(first.mode, "dry-run");
  assert.equal(first.duplicate, 3);
  assert.ok(first.anomalies.includes("quantity_zero_stock=30"));
  assert.ok(first.anomalies.includes("unknown_supplier_company=30:99"));
  assert.ok(first.anomalies.includes("orphan_stock=31"));
  assert.ok(first.anomalies.includes("invalid_quantity=31"));

  assert.doesNotThrow(() => assertYoyamicSelectOnly("SELECT * FROM tb_company WHERE CompanyID > 0"));
  assert.throws(() => assertYoyamicSelectOnly("SELECT * FROM tb_company; UPDATE tb_company SET Name = 'bad'"), /one statement/);
  assert.throws(() => assertYoyamicSelectOnly("DELETE FROM tb_company"), /SELECT\/SHOW/);
  assert.throws(() => assertYoyamicSelectOnly("SELECT * FROM tb_company FOR UPDATE"), /rejected/);

  const query = buildYoyamicReadQuery("companies", {
    tenantId: sampleRequestContext.tenant.tenantId,
    limit: 50,
    offset: 100,
    timeoutMs: 5_000,
    since: "2026-01-01T00:00:00.000Z"
  });
  assert.equal(query.entity, "companies");
  assert.match(query.sql, /^SELECT \* FROM tb_company WHERE ModifiedDate >= \? ORDER BY CompanyID LIMIT \? OFFSET \?$/);
  assert.deepEqual(query.params, ["2026-01-01T00:00:00.000Z", 50, 100]);

  const batches = buildYoyamicBatchPlan("stock", { tenantId: sampleRequestContext.tenant.tenantId, limit: 25, timeoutMs: 1_000 }, 3);
  assert.deepEqual(
    batches.map((batch) => batch.offset),
    [0, 25, 50]
  );

  const mapping = buildLegacyMappingRecord({
    tenantId: sampleRequestContext.tenant.tenantId,
    sourceTable: "tb_company",
    sourceId: 1,
    targetEntityType: "company",
    targetEntityId: "company-new",
    sourceRow: snapshot.companies[0],
    importedAt: "2026-07-13T00:00:00.000Z"
  });
  assert.equal(mapping.sourceSystem, "yoyamic");
  assert.equal(mapping.checksum?.length, 64);

  const reconciliation = summarizeYoyamicReconciliation(sampleRequestContext.tenant.tenantId, first);
  assert.equal(reconciliation.sourceRows, 8);
  assert.equal(reconciliation.zeroQuantityRows, 1);
  assert.ok(reconciliation.anomalyCodes.includes("duplicate_company_names"));
});

test("persistence provider does not fall back from postgres to memory", () => {
  assert.throws(() => getPersistenceConfig({ PERSISTENCE_PROVIDER: "postgres" }), /DATABASE_URL is required/);
  const provider = createCorePersistenceProvider(getPersistenceConfig({ PERSISTENCE_PROVIDER: "memory" }));
  assert.equal(provider.mode, "memory");
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
  assert.ok(openApiDocument.components.schemas.DocumentReadModel);
  assert.ok(openApiDocument.components.schemas.DocumentUploadRequest);
  assert.ok(openApiDocument.components.schemas.AuditEvent);
  assert.ok(openApiDocument.components.responses.Forbidden);
  assert.equal(openApiDocument.paths["/v1/session"].get.operationId, "getSession");
  assert.equal(openApiDocument.paths["/v1/auth/login"].post.operationId, "loginWithPassword");
  assert.equal(openApiDocument.paths["/v1/companies"].post.operationId, "createCompany");
  assert.equal(openApiDocument.paths["/v1/companies/{id}"].patch.operationId, "updateCompany");
  assert.equal(openApiDocument.paths["/v1/companies/{companyId}/contacts"].post.operationId, "createCompanyContact");
  assert.equal(openApiDocument.paths["/v1/contacts/{id}"].patch.operationId, "updateContact");
  assert.equal(openApiDocument.paths["/v1/parts"].post.operationId, "createPart");
  assert.equal(openApiDocument.paths["/v1/parts/{id}"].patch.operationId, "updatePart");
  assert.equal(openApiDocument.paths["/v1/stock"].post.operationId, "createStockItem");
  assert.equal(openApiDocument.paths["/v1/stock/{id}"].patch.operationId, "updateStockItem");
  assert.ok(openApiDocument.components.responses.ValidationError);
  assert.ok(openApiDocument.components.responses.Conflict);
  assert.equal(openApiDocument.paths["/v1/audit"].get.operationId, "listAuditEvents");
  assert.equal(openApiDocument.paths["/v1/parts/{id}/360"].get.operationId, "getPart360");
  assert.equal(openApiDocument.paths["/v1/stock/{id}/360"].get.operationId, "getStock360");
  assert.equal(openApiDocument.paths["/v1/company-inventory"].get.operationId, "getCompanyInventory");
  assert.equal(openApiDocument.paths["/v1/documents"].get.operationId, "listDocuments");
  assert.equal(openApiDocument.paths["/v1/documents"].get.responses["403"].$ref, "#/components/responses/Forbidden");
  assert.equal(openApiDocument.paths["/v1/documents/{id}"].get.responses["403"].$ref, "#/components/responses/Forbidden");
  assert.equal(
    openApiDocument.paths["/v1/entities/{ownerModule}/{ownerRecordId}/documents"].get.responses["403"].$ref,
    "#/components/responses/Forbidden"
  );
  assert.equal(openApiDocument.paths["/v1/documents/upload-intent"].post.operationId, "validateDocumentUpload");
  assert.equal(openApiDocument.paths["/v1/entities/{ownerModule}/{ownerRecordId}/documents"].get.operationId, "listEntityDocuments");
});
