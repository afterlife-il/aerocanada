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
  const server = app.listen(0);
  try {
    await new Promise<void>((resolve) => server.once("listening", resolve));
    const { port } = server.address() as AddressInfo;
    const response = await fetch(`http://127.0.0.1:${port}${path}`, {
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    });
    return {
      status: response.status,
      body: await response.json()
    };
  } finally {
    await new Promise<void>((resolve, reject) => server.close((error) => (error ? reject(error) : resolve())));
  }
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
