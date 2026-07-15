import test from "node:test";
import assert from "node:assert/strict";
import { mkdir, readFile, rm, writeFile } from "node:fs/promises";
import type { AddressInfo } from "node:net";
import { tmpdir } from "node:os";
import path from "node:path";
import { fileURLToPath } from "node:url";
import type { Express } from "express";
import pg from "pg";
import { CoreDomainError } from "@saas-aviation/shared";
import type { AuthSession, Permission, RequestContext } from "@saas-aviation/shared";
import { sampleRequestContext, sampleTenants, sampleUsers } from "@saas-aviation/shared";
import type { AuthProvider } from "./auth/auth-provider.js";
import { PostgresAuthProvider } from "./auth/postgres-auth-provider.js";
import { decryptSecret, encryptSecret, normalizeE164, totp, verifyTotp } from "./auth/mfa-crypto.js";
import { createApp } from "./server.js";
import { applyMigrations, getMigrationStatus } from "./persistence/migrations.js";
import type { PostgresConfig } from "./persistence/config.js";
import { PostgresCorePersistence } from "./persistence/postgres-core-repository.js";
import { InMemoryCorePersistence } from "./persistence/core-memory-repository.js";

const { Pool } = pg;
const databaseUrl = process.env.TEST_DATABASE_URL ?? process.env.DATABASE_URL;
const migrationFile = fileURLToPath(new URL("../../../database/migrations/001_core_persistence.sql", import.meta.url));

function hasCode(code: string): (error: unknown) => boolean {
  return (error: unknown): boolean => error instanceof CoreDomainError && error.code === code;
}

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
      const appliedAgain = await applyMigrations({ provider: "postgres", postgres: isolated.config });
      assert.deepEqual(appliedAgain, migrated);

      const mismatchDir = path.join(tmpdir(), `saas-migrations-${Date.now()}-${Math.random().toString(36).slice(2)}`);
      await mkdir(mismatchDir, { recursive: true });
      try {
        const originalSql = await readFile(migrationFile, "utf8");
        await writeFile(path.join(mismatchDir, "001_core_persistence.sql"), `${originalSql}\n-- checksum mismatch probe\n`);
        const previousDir = process.env.MIGRATIONS_DIR;
        process.env.MIGRATIONS_DIR = mismatchDir;
        try {
          await assert.rejects(applyMigrations({ provider: "postgres", postgres: isolated.config }), /checksum mismatch/);
        } finally {
          if (previousDir === undefined) {
            delete process.env.MIGRATIONS_DIR;
          } else {
            process.env.MIGRATIONS_DIR = previousDir;
          }
        }
      } finally {
        await rm(mismatchDir, { recursive: true, force: true });
      }

      const repo = new PostgresCorePersistence(isolated.config);
      const companyA = await repo.createCompany(tenantA, { name: "PG Company A", legalName: "PG Company A Legal", code: "PGA", icaoCode: "PGAA", iataCode: "PGA", vatNumber: "VAT-PG-A", tags: ["verified", "aviation"], country: "Canada", roles: ["supplier"] });
      const supplierA = await repo.createCompany(tenantA, { name: "PG Supplier A", roles: ["supplier"] });
      const tagInfoA = await repo.createCompany(tenantA, { name: "PG Tag Info A", roles: ["repair-station"] });
      const traceabilityA = await repo.createCompany(tenantA, { name: "PG Traceability A", roles: ["stock-owner"] });
      const companyB = await repo.createCompany(tenantB, { name: "PG Company B", roles: ["customer"] });
      const contactA = await repo.createContact(tenantA, companyA.id, { firstName: "Alex", lastName: "Aero", email: "alex@example.test" });
      const addressA = await repo.createCompanyAddress(tenantA, companyA.id, { label: "Head Office", addressLine1: "1 Aviation Way", city: "Montreal", country: "Canada", isPrimary: true });
      const temporaryContact = await repo.createContact(tenantA, companyA.id, { firstName: "Delete", lastName: "Me" });
      await repo.updateContact(tenantA, temporaryContact.id, { jobTitle: "Temporary" });
      await repo.deleteContact(tenantA, temporaryContact.id);
      const partA = await repo.createPart(tenantA, { partNumber: "PG-123", description: "Postgres part", manufacturer: "OEM", alternates: ["PG-ALT"] });
      const partB = await repo.createPart(tenantB, { partNumber: "PG-456", description: "Other tenant part", manufacturer: "OEM" });
      const stockA = await repo.createStockItem(tenantA, {
        partId: partA.id,
        quantity: 0,
        status: "available",
        ownerCompanyId: companyA.id,
        supplierCompanyId: supplierA.id,
        tagInfoCompanyId: tagInfoA.id,
        traceabilityCompanyId: traceabilityA.id,
        currency: "USD"
      });
      await repo.close();

      const restarted = new PostgresCorePersistence(isolated.config);
      assert.equal((await restarted.getCompanyById(tenantA, companyA.id))?.name, "PG Company A");
      assert.equal((await restarted.getCompanyById(tenantA, companyA.id))?.icaoCode, "PGAA");
      assert.deepEqual((await restarted.getCompanyById(tenantA, companyA.id))?.tags, ["verified", "aviation"]);
      assert.equal((await restarted.listContactsByCompany(tenantA, companyA.id))[0]?.id, contactA.id);
      assert.equal((await restarted.listCompanyAddresses(tenantA, companyA.id))[0]?.id, addressA.id);
      assert.equal((await restarted.listCompanyActivity(tenantA, companyA.id)).some((event) => event.action === "address-created"), true);
      await assert.rejects(restarted.listCompanyAddresses(tenantB, companyA.id), hasCode("not_found"));
      await assert.rejects(restarted.listCompanyActivity(tenantB, companyA.id), hasCode("not_found"));
      assert.equal((await restarted.getPartById(tenantA, partA.id))?.alternates[0], "PG-ALT");
      const restartedStock = await restarted.getStockById(tenantA, stockA.id);
      assert.equal(restartedStock?.quantity, 0);
      assert.equal(restartedStock?.ownerCompanyId, companyA.id);
      assert.equal(restartedStock?.supplierCompanyId, supplierA.id);
      assert.equal(restartedStock?.tagInfoCompanyId, tagInfoA.id);
      assert.equal(restartedStock?.traceabilityCompanyId, traceabilityA.id);

      assert.equal((await restarted.listCompanies(tenantA)).some((company) => company.id === companyB.id), false);
      assert.equal(await restarted.getCompanyById(tenantA, companyB.id), null);
      await assert.rejects(restarted.updateCompany(tenantA, companyB.id, { name: "Blocked" }), hasCode("not_found"));
      await assert.rejects(restarted.listContactsByCompany(tenantA, companyB.id), hasCode("not_found"));
      assert.equal(await restarted.getPartById(tenantA, partB.id), null);
      await assert.rejects(restarted.createStockItem(tenantA, { partId: partB.id, quantity: 1, status: "available" }), hasCode("tenant_mismatch"));
      await assert.rejects(
        restarted.createStockItem(tenantA, { partId: partA.id, quantity: 1, status: "available", ownerCompanyId: companyB.id }),
        hasCode("tenant_mismatch")
      );
      await assert.rejects(restarted.createCompany(tenantA, { name: "PG Company A", roles: ["supplier"] }), hasCode("duplicate_company"));

      const originalPart = await restarted.getPartById(tenantA, partA.id);
      assert.ok(originalPart);
      await assert.rejects(
        restarted.updatePart(tenantA, partA.id, { description: "Should rollback", alternates: ["DUP-ALT", "DUP-ALT"] }),
        hasCode("database_error")
      );
      assert.equal((await restarted.getPartById(tenantA, partA.id))?.description, originalPart.description);
      assert.deepEqual((await restarted.getPartById(tenantA, partA.id))?.alternates, originalPart.alternates);

      await assert.rejects(
        restarted.updateStockItem(tenantA, stockA.id, { quantity: 5, ownerCompanyId: companyB.id }),
        hasCode("tenant_mismatch")
      );
      const rolledBackStock = await restarted.getStockById(tenantA, stockA.id);
      assert.equal(rolledBackStock?.quantity, 0);
      assert.equal(rolledBackStock?.ownerCompanyId, companyA.id);

      const tokenA = "pg-api-a";
      const app = createApp({
        corePersistence: restarted,
        auth: new StaticAuthProvider([session(tokenA, tenantA)])
      });
      const apiCompany = await httpRequest(app, "POST", "/v1/companies", tokenA, { name: "API Restart Company", roles: ["customer"] });
      assert.equal(apiCompany.status, 201);
      const apiCompanyId = (apiCompany.body as { data: { id: string } }).data.id;
      const apiContact = await httpRequest(app, "POST", `/v1/companies/${apiCompanyId}/contacts`, tokenA, {
        firstName: "API",
        lastName: "Contact"
      });
      assert.equal(apiContact.status, 201);
      const apiContactId = (apiContact.body as { data: { id: string } }).data.id;
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
      const apiStockId = (apiStock.body as { data: { id: string } }).data.id;
      await restarted.close();

      const apiRestartedRepo = new PostgresCorePersistence(isolated.config);
      const restartedApp = createApp({
        corePersistence: apiRestartedRepo,
        auth: new StaticAuthProvider([session(tokenA, tenantA), session("pg-api-b", tenantB)])
      });
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}`, tokenA)).status, 200);
      const company360 = await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}/360`, tokenA);
      assert.equal(company360.status, 200);
      assert.equal((company360.body as { data: { contacts: Array<{ id: string }>; inventory: Array<{ id: string }>; workflowBoundaries: unknown[] } }).data.contacts[0]?.id, apiContactId);
      assert.equal((company360.body as { data: { contacts: unknown[]; inventory: Array<{ id: string }>; workflowBoundaries: unknown[] } }).data.inventory.some((item) => item.id === apiStockId), true);
      assert.equal((company360.body as { data: { workflowBoundaries: unknown[] } }).data.workflowBoundaries.length, 5);
      assert.deepEqual((company360.body as { data: { documents: unknown } }).data.documents, { persistent: false, source: "workflow-boundary", documents: [] });
      assert.equal((company360.body as { data: { workflowBoundaries: Array<{ persistence: string; futureOwner: string }> } }).data.workflowBoundaries.every((boundary) => boundary.persistence === "none" && boundary.futureOwner.length > 0), true);
      const restartedContacts = await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}/contacts`, tokenA);
      assert.equal(restartedContacts.status, 200);
      assert.equal((restartedContacts.body as { data: Array<{ id: string }> }).data[0]?.id, apiContactId);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/parts/${apiPartId}`, tokenA)).status, 200);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/stock/${apiStockId}`, tokenA)).status, 200);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}`, "pg-api-b")).status, 404);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}/contacts`, "pg-api-b")).status, 404);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/parts/${apiPartId}`, "pg-api-b")).status, 404);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/stock/${apiStockId}`, "pg-api-b")).status, 404);
      assert.equal((await httpRequest(restartedApp, "GET", `/v1/companies/${apiCompanyId}/360`, "pg-api-b")).status, 404);
      const deletable = await apiRestartedRepo.createCompany(tenantA, { name: "Delete Company", roles: ["customer"] });
      await apiRestartedRepo.deleteCompany(tenantA, deletable.id);
      assert.equal(await apiRestartedRepo.getCompanyById(tenantA, deletable.id), null);
      await apiRestartedRepo.close();
    } finally {
      await isolated.cleanup();
    }
  }
);

test(
  "PostgreSQL authentication hashes passwords, persists sessions, validates CSRF and locks repeated failures",
  { skip: databaseUrl ? false : "Set TEST_DATABASE_URL or DATABASE_URL to run PostgreSQL integration tests." },
  async () => {
    assert.ok(databaseUrl);
    const isolated = await createIsolatedSchema(databaseUrl);
    const pool = new Pool({ connectionString: isolated.config.connectionString });
    const env = { ...process.env, STAGING_ADMIN_EMAIL: "persistent-auth@example.test", STAGING_ADMIN_PASSWORD: "Persistent-Test-Password-2026!" };
    try {
      await applyMigrations({ provider: "postgres", postgres: isolated.config });
      await pool.query("INSERT INTO tenants (id,name,slug,status,code) VALUES ('tenant-aci','AeroCanada','AeroCanada','active','aci770')");
      const auth = new PostgresAuthProvider(isolated.config, env);
      const loggedIn = await auth.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD);
      assert.ok(loggedIn?.token);
      assert.ok(loggedIn?.csrfToken);
      const credential = `Bearer ${loggedIn.token}`;
      assert.equal((await auth.getCurrentSession(credential))?.user.email, env.STAGING_ADMIN_EMAIL);
      assert.equal(await auth.validateCsrf(loggedIn.token, loggedIn.csrfToken), true);
      assert.equal(await auth.validateCsrf(loggedIn.token, "wrong-csrf"), false);
      const credentialRow = await pool.query<{ algorithm: string; password_hash: string }>("SELECT algorithm,password_hash FROM auth_credentials");
      assert.equal(credentialRow.rows[0]?.algorithm, "scrypt-v1");
      assert.notEqual(credentialRow.rows[0]?.password_hash, env.STAGING_ADMIN_PASSWORD);
      await auth.close();

      const restarted = new PostgresAuthProvider(isolated.config, env);
      assert.equal((await restarted.getCurrentSession(credential))?.user.email, env.STAGING_ADMIN_EMAIL);
      const authApp = createApp({ auth: restarted, corePersistence: new InMemoryCorePersistence() });
      const authServer = authApp.listen(0, "127.0.0.1");
      await new Promise<void>((resolve) => authServer.once("listening", resolve));
      try {
        const { port } = authServer.address() as AddressInfo;
        const loginResponse = await fetch(`http://127.0.0.1:${port}/v1/auth/login`, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ email: env.STAGING_ADMIN_EMAIL, password: env.STAGING_ADMIN_PASSWORD }) });
        assert.equal(loginResponse.status, 200);
        const setCookie = loginResponse.headers.get("set-cookie") ?? "";
        assert.match(setCookie, /saas_session=.*HttpOnly.*Secure.*SameSite=Strict/i);
        const sessionCookie = setCookie.match(/saas_session=([^;]+)/)?.[1];
        const csrfCookie = setCookie.match(/saas_csrf=([^;]+)/)?.[1];
        assert.ok(sessionCookie && csrfCookie);
        const cookie = `saas_session=${sessionCookie}; saas_csrf=${csrfCookie}`;
        assert.equal((await fetch(`http://127.0.0.1:${port}/v1/companies`, { headers: { Cookie: cookie } })).status, 200);
        assert.equal((await fetch(`http://127.0.0.1:${port}/v1/companies`, { method: "POST", headers: { Cookie: cookie, "Content-Type": "application/json" }, body: JSON.stringify({ name: "CSRF blocked" }) })).status, 403);
        assert.equal((await fetch(`http://127.0.0.1:${port}/v1/companies`, { method: "POST", headers: { Cookie: cookie, "X-CSRF-Token": decodeURIComponent(csrfCookie), "Content-Type": "application/json" }, body: JSON.stringify({ name: "CSRF accepted" }) })).status, 201);
      } finally { await new Promise<void>((resolve, reject) => authServer.close((error) => error ? reject(error) : resolve())); }
      await restarted.revokeSession(loggedIn.token);
      assert.equal(await restarted.getCurrentSession(credential), null);
      const sessionA = await restarted.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD);
      const sessionB = await restarted.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD);
      assert.ok(sessionA && sessionB);
      await restarted.revokeAllSessions(sessionA.user.id, sessionA.tenant.id);
      assert.equal(await restarted.getCurrentSession(`Bearer ${sessionA.token}`), null);
      assert.equal(await restarted.getCurrentSession(`Bearer ${sessionB.token}`), null);
      for (let attempt = 0; attempt < 5; attempt++) assert.equal(await restarted.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, "wrong-password"), null);
      assert.equal(await restarted.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD), null);
      const state = await pool.query<{ failed_attempts: number; locked: boolean }>("SELECT failed_attempts,locked_until>now() locked FROM auth_users");
      assert.equal(state.rows[0]?.failed_attempts, 5);
      assert.equal(state.rows[0]?.locked, true);
      assert.equal(Number((await pool.query("SELECT count(*) FROM auth_audit_events")).rows[0]?.count) >= 7, true);
      await restarted.close();
    } finally { await pool.end(); await isolated.cleanup(); }
  }
);

test(
  "PostgreSQL MFA supports encrypted TOTP, one-use recovery codes and rate-limited staging phone enrollment",
  { skip: databaseUrl ? false : "Set TEST_DATABASE_URL or DATABASE_URL to run PostgreSQL integration tests." },
  async () => {
    assert.ok(databaseUrl); const isolated = await createIsolatedSchema(databaseUrl); const pool = new Pool({ connectionString: isolated.config.connectionString });
    const spool = path.join(tmpdir(), `saas-otp-${Date.now()}.jsonl`);
    const env = { ...process.env, STAGING_ADMIN_EMAIL: "mfa-auth@example.test", STAGING_ADMIN_PASSWORD: "Persistent-MFA-Password-2026!", AUTH_ENCRYPTION_KEY: "test-only-encryption-key-with-at-least-32-characters", PHONE_OTP_PROVIDER: "staging-spool", PHONE_OTP_STAGING_SPOOL: spool };
    try {
      await applyMigrations({ provider: "postgres", postgres: isolated.config });
      await pool.query("INSERT INTO tenants (id,name,slug,status,code) VALUES ('tenant-aci','AeroCanada','AeroCanada','active','aci770')");
      const auth = new PostgresAuthProvider(isolated.config, env); const first = await auth.authenticateWithPassword(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD); assert.ok(first);
      const enrollment = await auth.beginTotpEnrollment(first.user.id, first.tenant.id); assert.equal(verifyTotp(enrollment.secret, totp(enrollment.secret)), true);
      const cipher = encryptSecret(enrollment.secret, env.AUTH_ENCRYPTION_KEY); assert.equal(decryptSecret(cipher, env.AUTH_ENCRYPTION_KEY), enrollment.secret);
      const recovery = await auth.confirmTotpEnrollment(first.user.id, first.tenant.id, totp(enrollment.secret)); assert.equal(recovery?.length, 10);
      const pending = await auth.beginPasswordAuthentication(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD); assert.ok(pending && "mfaRequired" in pending);
      const mfaSession = pending && "mfaRequired" in pending ? await auth.completeMfaChallenge(pending.challengeId, totp(enrollment.secret)) : null; assert.ok(mfaSession?.token);
      const pendingRecovery = await auth.beginPasswordAuthentication(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD); assert.ok(pendingRecovery && "mfaRequired" in pendingRecovery);
      assert.ok(recovery?.[0]); assert.ok(pendingRecovery && "mfaRequired" in pendingRecovery && await auth.completeMfaChallenge(pendingRecovery.challengeId, recovery[0]));
      const pendingReuse = await auth.beginPasswordAuthentication(env.STAGING_ADMIN_EMAIL, env.STAGING_ADMIN_PASSWORD); assert.ok(pendingReuse && "mfaRequired" in pendingReuse);
      assert.equal(pendingReuse && "mfaRequired" in pendingReuse ? await auth.completeMfaChallenge(pendingReuse.challengeId, recovery[0]) : null, null);
      const phone = await auth.requestPhoneEnrollment(first.user.id, first.tenant.id, "+1 (514) 555-0199"); assert.equal(phone?.delivery, "staging-spool"); assert.equal(normalizeE164("+1 (514) 555-0199"), "+15145550199");
      const delivery = JSON.parse((await readFile(spool, "utf8")).trim().split("\n").at(-1)!) as { code: string };
      assert.ok(phone && await auth.verifyPhoneEnrollment(first.user.id, first.tenant.id, phone.challengeId, delivery.code));
      assert.equal(await auth.requestPhoneEnrollment(first.user.id, first.tenant.id, "+15145550199"), null);
      assert.equal(await auth.disableTotp(first.user.id, first.tenant.id, totp(enrollment.secret)), true); await auth.close();
    } finally { await rm(spool, { force: true }); await pool.end(); await isolated.cleanup(); }
  }
);
