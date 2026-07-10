import test from "node:test";
import assert from "node:assert/strict";
import type { AddressInfo } from "node:net";
import type { Express } from "express";
import pg from "pg";
import type { AuthSession, Permission, RequestContext } from "@saas-aviation/shared";
import { sampleRequestContext, sampleTenants, sampleUsers } from "@saas-aviation/shared";
import type { AuthProvider } from "./auth/auth-provider.js";
import { createApp } from "./server.js";
import { applyMigrations, getMigrationStatus } from "./persistence/migrations.js";
import type { PostgresConfig } from "./persistence/config.js";
import { PostgresCorePersistence } from "./persistence/postgres-core-repository.js";

const { Pool } = pg;
const databaseUrl = process.env.TEST_DATABASE_URL ?? process.env.DATABASE_URL;

function context(suffix: string): RequestContext {
  return {
    tenant: {
      ...sampleRequestContext.tenant,
      tenantId: `tenant-pg-${suffix}`,
      tenantCode: `PG${suffix.toUpperCase()}`,
      tenantName: `Postgres Tenant ${suffix.toUpperCase()}`,
      userId: `user-pg-${suffix}`
    }
  };
}

function schemaName(): string {
  return `saas_test_${Date.now()}_${Math.random().toString(36).slice(2)}`;
}

function urlWithSearchPath(value: string, schema: string): string {
  const url = new URL(value);
  url.searchParams.set("options", `-c search_path=${schema}`);
  return url.toString();
}

async function createIsolatedSchema(baseUrl: string): Promise<{ schema: string; config: PostgresConfig; cleanup(): Promise<void> }> {
  const schema = schemaName();
  const adminPool = new Pool({ connectionString: baseUrl });
  await adminPool.query(`CREATE SCHEMA ${schema}`);
  await adminPool.end();

  return {
    schema,
    config: {
      connectionString: urlWithSearchPath(baseUrl, schema),
      poolMin: 0,
      poolMax: 4,
      ssl: false
    },
    async cleanup() {
      const cleanupPool = new Pool({ connectionString: baseUrl });
      await cleanupPool.query(`DROP SCHEMA IF EXISTS ${schema} CASCADE`);
      await cleanupPool.end();
    }
  };
}

class StaticAuthProvider implements AuthProvider {
  constructor(private readonly sessions: AuthSession[]) {}

  async authenticateWithPassword(): Promise<AuthSession | null> {
    return null;
  }

  async getCurrentSession(authorizationHeader?: string): Promise<AuthSession | null> {
    const token = authorizationHeader?.replace(/^Bearer\s+/i, "");
    return this.sessions.find((session) => session.token === token) ?? null;
  }

  async revokeSession(): Promise<void> {
    return;
  }

  async createLoginAuditEvent(): Promise<void> {
    return;
  }
}

function session(token: string, requestContext: RequestContext, permissions: Permission[] = sampleRequestContext.tenant.permissions): AuthSession {
  const user = sampleUsers[0];
  const tenant = sampleTenants[0];
  assert.ok(user);
  assert.ok(tenant);
  return {
    token,
    user: {
      id: requestContext.tenant.userId,
      tenantId: requestContext.tenant.tenantId,
      email: `${requestContext.tenant.userId}@example.test`,
      name: requestContext.tenant.userId,
      status: user.status,
      roles: user.roles,
      permissions,
      mfaEnabled: user.mfaEnabled,
      authProviders: user.authProviders,
      createdAt: user.createdAt
    },
    tenant: {
      ...tenant,
      id: requestContext.tenant.tenantId,
      code: requestContext.tenant.tenantCode,
      name: requestContext.tenant.tenantName
    },
    expiresAt: new Date(Date.now() + 60_000).toISOString()
  };
}

async function httpRequest(app: Express, method: string, path: string, token: string, body?: unknown): Promise<{ status: number; body: unknown }> {
  const server = app.listen(0, "127.0.0.1");
  try {
    await new Promise<void>((resolve) => server.once("listening", resolve));
    const { port } = server.address() as AddressInfo;
    const init: RequestInit = {
      method,
      headers: {
        Authorization: `Bearer ${token}`,
        ...(body ? { "Content-Type": "application/json" } : {})
      }
    };
    if (body) init.body = JSON.stringify(body);
    const response = await fetch(`http://127.0.0.1:${port}${path}`, init);
    return { status: response.status, body: await response.json() };
  } finally {
    await new Promise<void>((resolve, reject) => server.close((error) => (error ? reject(error) : resolve())));
  }
}

test(
  "PostgreSQL persistence survives restarts and enforces tenant isolation",
  { skip: databaseUrl ? false : "Set TEST_DATABASE_URL or DATABASE_URL to run PostgreSQL integration tests." },
  async () => {
    assert.ok(databaseUrl);
    const isolated = await createIsolatedSchema(databaseUrl);
    const tenantA = context("a");
    const tenantB = context("b");

    try {
      const migrated = await applyMigrations({ provider: "postgres", postgres: isolated.config });
      assert.equal(migrated.find((row) => row.id === "001_core_persistence.sql")?.applied, true);
      assert.equal((await getMigrationStatus({ provider: "postgres", postgres: isolated.config })).every((row) => row.applied), true);

      const repo = new PostgresCorePersistence(isolated.config);
      const companyA = await repo.createCompany(tenantA, { name: "PG Company A", roles: ["supplier"] });
      const companyB = await repo.createCompany(tenantB, { name: "PG Company B", roles: ["customer"] });
      const contactA = await repo.createContact(tenantA, companyA.id, { firstName: "Alex", lastName: "Aero", email: "alex@example.test" });
      const partA = await repo.createPart(tenantA, { partNumber: "PG-123", description: "Postgres part", manufacturer: "OEM", alternates: ["PG-ALT"] });
      const partB = await repo.createPart(tenantB, { partNumber: "PG-456", description: "Other tenant part", manufacturer: "OEM" });
      const stockA = await repo.createStockItem(tenantA, {
        partId: partA.id,
        quantity: 0,
        status: "available",
        ownerCompanyId: companyA.id,
        supplierCompanyId: companyA.id,
        tagInfoCompanyId: companyA.id,
        traceabilityCompanyId: companyA.id,
        currency: "USD"
      });
      await repo.close();

      const restarted = new PostgresCorePersistence(isolated.config);
      assert.equal((await restarted.getCompanyById(tenantA, companyA.id))?.name, "PG Company A");
      assert.equal((await restarted.listContactsByCompany(tenantA, companyA.id))[0]?.id, contactA.id);
      assert.equal((await restarted.getPartById(tenantA, partA.id))?.alternates[0], "PG-ALT");
      assert.equal((await restarted.getStockById(tenantA, stockA.id))?.quantity, 0);

      assert.equal((await restarted.listCompanies(tenantA)).some((company) => company.id === companyB.id), false);
      assert.equal(await restarted.getCompanyById(tenantA, companyB.id), null);
      await assert.rejects(restarted.updateCompany(tenantA, companyB.id, { name: "Blocked" }), /not_found/);
      await assert.rejects(restarted.listContactsByCompany(tenantA, companyB.id), /not_found/);
      assert.equal(await restarted.getPartById(tenantA, partB.id), null);
      await assert.rejects(restarted.createStockItem(tenantA, { partId: partB.id, quantity: 1, status: "available" }), /tenant_mismatch|not_found/);
      await assert.rejects(
        restarted.createStockItem(tenantA, { partId: partA.id, quantity: 1, status: "available", ownerCompanyId: companyB.id }),
        /tenant_mismatch/
      );
      await assert.rejects(restarted.createCompany(tenantA, { name: "PG Company A", roles: ["supplier"] }), /duplicate_company/);

      const tokenA = "pg-api-a";
      const app = createApp({
        corePersistence: restarted,
        auth: new StaticAuthProvider([session(tokenA, tenantA)])
      });
      const apiCompany = await httpRequest(app, "POST", "/v1/companies", tokenA, { name: "API Restart Company", roles: ["customer"] });
      assert.equal(apiCompany.status, 201);
      const apiCompanyId = (apiCompany.body as { data: { id: string } }).data.id;
      const apiPart = await httpRequest(app, "POST", "/v1/parts", tokenA, { partNumber: "API-PG-1", description: "API part" });
      assert.equal(apiPart.status, 201);
      const apiPartId = (apiPart.body as { data: { id: string } }).data.id;
      const apiStock = await httpRequest(app, "POST", "/v1/stock", tokenA, {
        partId: apiPartId,
        quantity: 2,
        status: "available",
        ownerCompanyId: apiCompanyId,
        supplierCompanyId: apiCompanyId,
        tagInfoCompanyId: apiCompanyId,
        traceabilityCompanyId: apiCompanyId
      });
      assert.equal(apiStock.status, 201);
      await restarted.close();

      const apiRestartedRepo = new PostgresCorePersistence(isolated.config);
      const restartedApp = createApp({
        corePersistence: apiRestartedRepo,
        auth: new StaticAuthProvider([session(tokenA, tenantA), session("pg-api-b", tenantB)])
      });
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}`, tokenA)).status, 200);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}`, "pg-api-b")).status, 404);
      await apiRestartedRepo.close();
    } finally {
      await isolated.cleanup();
    }
  }
);
