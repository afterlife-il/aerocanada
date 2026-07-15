import { randomUUID } from "node:crypto";
import { pathToFileURL } from "node:url";
import cors from "cors";
import express, { type ErrorRequestHandler, type RequestHandler } from "express";
import helmet from "helmet";
import { ZodError } from "zod";
import { CoreDomainError } from "@saas-aviation/shared";
import type { AviationErpDataSource, CorePersistence, DocumentOwnerModule } from "@saas-aviation/shared";
import { getLegacyDataSource } from "./adapters/legacy-mysql-adapter.js";
import { AuditService } from "./audit/audit-service.js";
import { createDefaultAuthProvider, type AuthProvider } from "./auth/auth-provider.js";
import { requirePermission, requireSession, sessionCredential } from "./auth/route-guard.js";
import { openApiDocument } from "./openapi/openapi.js";
import { createCorePersistenceProvider } from "./persistence/provider.js";

const port = Number(process.env.API_PORT ?? 4107);
const createHelmetMiddleware = helmet as unknown as () => RequestHandler;
const documentOwnerModules = new Set<DocumentOwnerModule>([
  "company",
  "contact",
  "part",
  "stock",
  "rfq",
  "supplier-quote",
  "customer-quote",
  "purchase-order",
  "sales-order",
  "invoice",
  "repair-exchange-lease"
]);

export interface AppDependencies {
  dataSource?: AviationErpDataSource;
  auth?: AuthProvider;
  corePersistence?: CorePersistence;
}

function asyncHandler(handler: RequestHandler): RequestHandler {
  return (req, res, next) => {
    Promise.resolve(handler(req, res, next)).catch(next);
  };
}

const errorHandler: ErrorRequestHandler = (_error, _req, res, _next) => {
  if (res.headersSent) {
    return;
  }

  res.status(500).json({ error: "internal_server_error", correlationId: res.getHeader("X-Correlation-ID") });
};

function domainErrorStatus(error: CoreDomainError): number {
  switch (error.code) {
    case "not_found":
      return 404;
    case "validation_error":
      return 400;
    case "duplicate_company":
    case "duplicate_contact":
    case "duplicate_part":
      return 409;
    case "unauthorized":
      return 401;
    case "tenant_mismatch":
      return 403;
    case "database_error":
      return 500;
  }
}

function requiredParam(req: express.Request, res: express.Response, name: string): string | null {
  const value = req.params[name];
  if (!value) {
    res.status(400).json({ error: "path_parameter_required", parameter: name });
    return null;
  }
  return value;
}

async function handleCoreResponse<T>(res: express.Response, work: () => Promise<T>, status = 200): Promise<void> {
  try {
    res.status(status).json({ data: await work() });
  } catch (error) {
    if (error instanceof CoreDomainError) {
      res.status(domainErrorStatus(error)).json({ error: error.code, message: error.message, details: error.details });
      return;
    }
    if (error instanceof ZodError) {
      res.status(400).json({ error: "validation_error", issues: error.issues });
      return;
    }
    throw error;
  }
}

export function createApp(dependencies: AppDependencies = {}) {
  const app = express();
  const dataSource = dependencies.dataSource ?? getLegacyDataSource();
  const corePersistence = dependencies.corePersistence ?? createCorePersistenceProvider().repository;
  const auth = dependencies.auth ?? createDefaultAuthProvider();
  const audit = new AuditService(dataSource);

  app.use(createHelmetMiddleware());
  app.use((req, res, next) => {
    const supplied = req.header("X-Correlation-ID");
    const correlationId = supplied && /^[a-zA-Z0-9._:-]{1,100}$/.test(supplied) ? supplied : randomUUID();
    res.setHeader("X-Correlation-ID", correlationId);
    next();
  });
  app.use(cors({ origin: process.env.CORS_ORIGIN?.split(",") ?? ["http://localhost:3007"] }));
  app.use(express.json());
  app.use(asyncHandler(async (req, res, next) => {
    if (["GET", "HEAD", "OPTIONS"].includes(req.method) || req.headers.authorization) { next(); return; }
    const credential = sessionCredential(req);
    if (!credential) { next(); return; }
    const sessionToken = credential.replace(/^Bearer\s+/i, "");
    const csrfToken = req.header("X-CSRF-Token");
    if (!csrfToken || !auth.validateCsrf || !await auth.validateCsrf(sessionToken, csrfToken)) {
      res.status(403).json({ error: "csrf_validation_failed" }); return;
    }
    next();
  }));

  app.get("/health", (_req, res) => {
    res.json({ status: "ok", service: "saas-aviation-api" });
  });

  app.get("/openapi.json", (_req, res) => {
    res.json(openApiDocument);
  });

  app.post(
    "/v1/auth/login",
    asyncHandler(async (req, res) => {
      const { email, password } = req.body as { email?: string; password?: string };

      if (!email || !password) {
        res.status(400).json({ error: "email_and_password_required" });
        return;
      }

      const result = auth.beginPasswordAuthentication ? await auth.beginPasswordAuthentication(email, password) : await auth.authenticateWithPassword(email, password);
      if (!result) {
        res.status(401).json({ error: "invalid_credentials" });
        return;
      }

      if ("mfaRequired" in result) { res.status(202).json({ data: result }); return; }

      const session = result;

      res.cookie("saas_session", session.token, { httpOnly: true, secure: true, sameSite: "strict", path: "/", expires: new Date(session.expiresAt) });
      if (session.csrfToken) res.cookie("saas_csrf", session.csrfToken, { httpOnly: false, secure: true, sameSite: "strict", path: "/", expires: new Date(session.expiresAt) });
      res.json({ data: { session } });
    })
  );

  app.post(
    "/v1/auth/mfa/challenge",
    asyncHandler(async (req, res) => {
      const { challengeId, code } = req.body as { challengeId?: string; code?: string };
      if (!challengeId || !code || !auth.completeMfaChallenge) { res.status(400).json({ error: "mfa_challenge_required" }); return; }
      const session = await auth.completeMfaChallenge(challengeId, code);
      if (!session) { res.status(401).json({ error: "invalid_or_expired_mfa_challenge" }); return; }
      res.cookie("saas_session", session.token, { httpOnly: true, secure: true, sameSite: "strict", path: "/", expires: new Date(session.expiresAt) });
      if (session.csrfToken) res.cookie("saas_csrf", session.csrfToken, { httpOnly: false, secure: true, sameSite: "strict", path: "/", expires: new Date(session.expiresAt) });
      res.json({ data: { session } });
    })
  );

  app.post("/v1/auth/mfa/totp/enroll", asyncHandler(async (req, res) => {
    const session = await auth.getCurrentSession(sessionCredential(req));
    if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
    if (!auth.beginTotpEnrollment) { res.status(501).json({ error: "totp_unavailable" }); return; }
    res.json({ data: await auth.beginTotpEnrollment(session.user.id, session.tenant.id) });
  }));

  app.post("/v1/auth/mfa/totp/confirm", asyncHandler(async (req, res) => {
    const session = await auth.getCurrentSession(sessionCredential(req)); const { code } = req.body as { code?: string };
    if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
    if (!code || !auth.confirmTotpEnrollment) { res.status(400).json({ error: "totp_code_required" }); return; }
    const recoveryCodes = await auth.confirmTotpEnrollment(session.user.id, session.tenant.id, code);
    if (!recoveryCodes) { res.status(401).json({ error: "invalid_totp_code" }); return; }
    res.json({ data: { enabled: true, recoveryCodes } });
  }));

  app.post("/v1/auth/mfa/totp/disable", asyncHandler(async (req, res) => {
    const session = await auth.getCurrentSession(sessionCredential(req)); const { code } = req.body as { code?: string };
    if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
    if (!code || !auth.disableTotp) { res.status(400).json({ error: "totp_code_required" }); return; }
    if (!await auth.disableTotp(session.user.id, session.tenant.id, code)) { res.status(401).json({ error: "invalid_totp_code" }); return; }
    res.json({ data: { enabled: false } });
  }));

  app.post("/v1/auth/phone/enroll/request", asyncHandler(async (req, res) => {
    const session = await auth.getCurrentSession(sessionCredential(req)); const { phone } = req.body as { phone?: string };
    if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
    if (!phone || !auth.requestPhoneEnrollment) { res.status(400).json({ error: "valid_e164_phone_required" }); return; }
    const challenge = await auth.requestPhoneEnrollment(session.user.id, session.tenant.id, phone);
    if (!challenge) { res.status(429).json({ error: "phone_otp_unavailable_or_cooldown_active" }); return; }
    res.status(202).json({ data: challenge });
  }));

  app.post("/v1/auth/phone/enroll/verify", asyncHandler(async (req, res) => {
    const session = await auth.getCurrentSession(sessionCredential(req)); const { challengeId, code } = req.body as { challengeId?: string; code?: string };
    if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
    if (!challengeId || !code || !auth.verifyPhoneEnrollment) { res.status(400).json({ error: "phone_otp_challenge_required" }); return; }
    if (!await auth.verifyPhoneEnrollment(session.user.id, session.tenant.id, challengeId, code)) { res.status(401).json({ error: "invalid_or_expired_phone_otp" }); return; }
    res.json({ data: { verified: true } });
  }));

  app.post(
    "/v1/auth/logout",
    asyncHandler(async (req, res) => {
      const token = sessionCredential(req)?.replace(/^Bearer\s+/i, "");
      if (token) {
        await auth.revokeSession(token);
      }

      res.clearCookie("saas_session", { httpOnly: true, secure: true, sameSite: "strict", path: "/" });
      res.clearCookie("saas_csrf", { httpOnly: false, secure: true, sameSite: "strict", path: "/" });
      res.json({ data: { loggedOut: true } });
    })
  );

  app.post(
    "/v1/auth/revoke-all",
    asyncHandler(async (req, res) => {
      const credential = sessionCredential(req);
      const session = await auth.getCurrentSession(credential);
      if (!session) { res.status(401).json({ error: "unauthorized" }); return; }
      if (!auth.revokeAllSessions) { res.status(501).json({ error: "persistent_session_revocation_unavailable" }); return; }
      await auth.revokeAllSessions(session.user.id, session.tenant.id);
      res.clearCookie("saas_session", { httpOnly: true, secure: true, sameSite: "strict", path: "/" });
      res.clearCookie("saas_csrf", { httpOnly: false, secure: true, sameSite: "strict", path: "/" });
      res.json({ data: { revoked: true } });
    })
  );

  app.get(
    "/v1/session",
    asyncHandler(async (req, res) => {
      const session = await auth.getCurrentSession(sessionCredential(req));
      res.json({ session });
    })
  );

  app.get(
    "/v1/companies",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.read")) return;
      await handleCoreResponse(res, async () => {
        const companies = await corePersistence.listCompanies(context);
        const query = String(req.query.q ?? "").trim().toLowerCase();
        const status = String(req.query.status ?? "all");
        const role = String(req.query.role ?? "all");
        const sort = String(req.query.sort ?? "name");
        const direction = req.query.direction === "desc" ? -1 : 1;
        const page = Math.max(1, Number(req.query.page ?? 1) || 1);
        const pageSize = Math.min(100, Math.max(1, Number(req.query.pageSize ?? 25) || 25));
        const filtered = companies.filter((company) => {
          if (status !== "all" && company.status !== status) return false;
          if (role !== "all" && !company.roles.includes(role as never)) return false;
          if (!query) return true;
          return [company.name, company.legalName, company.code, company.icaoCode, company.iataCode, company.vatNumber, company.email, company.phone, company.website, company.city, company.country, ...company.tags]
            .some((value) => String(value ?? "").toLowerCase().includes(query));
        });
        filtered.sort((left, right) => String(sort === "updatedAt" ? left.updatedAt : sort === "code" ? left.code ?? "" : left.name).localeCompare(String(sort === "updatedAt" ? right.updatedAt : sort === "code" ? right.code ?? "" : right.name), "en-US", { numeric: true }) * direction);
        if (Object.keys(req.query).length === 0) return filtered;
        return { rows: filtered.slice((page - 1) * pageSize, page * pageSize), pagination: { page, pageSize, totalRows: filtered.length, totalPages: Math.max(1, Math.ceil(filtered.length / pageSize)) } };
      });
    })
  );

  app.get(
    "/v1/companies/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, async () => {
        const company = await corePersistence.getCompanyById(context, id);
        if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
        return company;
      });
    })
  );

  app.post(
    "/v1/companies",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.manage")) return;
      await handleCoreResponse(res, () => corePersistence.createCompany(context, req.body), 201);
    })
  );

  app.patch(
    "/v1/companies/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.manage")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updateCompany(context, id, req.body));
    })
  );

  app.delete("/v1/companies/:id", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.manage")) return;
    const id = requiredParam(req, res, "id"); if (!id) return;
    await handleCoreResponse(res, async () => { await corePersistence.deleteCompany(context, id); return { deleted: true }; });
  }));

  app.get("/v1/companies/:companyId/360", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.read")) return;
    const companyId = requiredParam(req, res, "companyId"); if (!companyId) return;
    await handleCoreResponse(res, async () => {
      const company = await corePersistence.getCompanyById(context, companyId);
      if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
      const [contacts, addresses, stock, activity] = await Promise.all([
        corePersistence.listContactsByCompany(context, company.id), corePersistence.listCompanyAddresses(context, company.id),
        corePersistence.listStock(context), corePersistence.listCompanyActivity(context, company.id)
      ]);
      const inventory = stock.filter((item) => [item.ownerCompanyId, item.supplierCompanyId, item.tagInfoCompanyId, item.traceabilityCompanyId].includes(company.id));
      const boundaryMetadata = {
        rfq: { futureOwner: "RFQ module", requiredData: ["companyId", "tenantId", "requested parts"], contextChecks: ["company.read", "tenant match"] },
        "supplier-quote": { futureOwner: "Supplier Quotes module", requiredData: ["companyId", "tenantId", "RFQ_ID"], contextChecks: ["company.read", "tenant match"] },
        "customer-quote": { futureOwner: "Customer Quotes module", requiredData: ["companyId", "tenantId", "RFQ_ID"], contextChecks: ["company.read", "tenant match"] },
        "purchase-order": { futureOwner: "Purchase Orders module", requiredData: ["companyId", "tenantId", "approved supplier quote"], contextChecks: ["company.read", "tenant match"] },
        "sales-order": { futureOwner: "Sales Orders module", requiredData: ["companyId", "tenantId", "approved customer quote"], contextChecks: ["company.read", "tenant match"] }
      } as const;
      return { company, contacts, addresses, inventory, documents: { persistent: false, source: "workflow-boundary", documents: [] }, activity,
        workflowBoundaries: Object.entries(boundaryMetadata).map(([category, metadata]) => ({ category, status: "boundary", companyId: company.id, persistence: "none", ...metadata })) };
    });
  }));

  app.get("/v1/companies/:companyId/addresses", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.read")) return;
    const companyId = requiredParam(req, res, "companyId"); if (!companyId) return;
    await handleCoreResponse(res, () => corePersistence.listCompanyAddresses(context, companyId));
  }));

  app.post("/v1/companies/:companyId/addresses", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.manage")) return;
    const companyId = requiredParam(req, res, "companyId"); if (!companyId) return;
    await handleCoreResponse(res, () => corePersistence.createCompanyAddress(context, companyId, req.body), 201);
  }));

  app.patch("/v1/company-addresses/:id", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.manage")) return;
    const id = requiredParam(req, res, "id"); if (!id) return;
    await handleCoreResponse(res, () => corePersistence.updateCompanyAddress(context, id, req.body));
  }));

  app.delete("/v1/company-addresses/:id", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.manage")) return;
    const id = requiredParam(req, res, "id"); if (!id) return;
    await handleCoreResponse(res, async () => { await corePersistence.deleteCompanyAddress(context, id); return { deleted: true }; });
  }));

  app.get(
    "/v1/companies/:companyId/contacts",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.read")) return;
      const companyId = requiredParam(req, res, "companyId");
      if (!companyId) return;
      await handleCoreResponse(res, () => corePersistence.listContactsByCompany(context, companyId));
    })
  );

  app.post(
    "/v1/companies/:companyId/contacts",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.manage")) return;
      const companyId = requiredParam(req, res, "companyId");
      if (!companyId) return;
      await handleCoreResponse(res, () => corePersistence.createContact(context, companyId, req.body), 201);
    })
  );

  app.patch(
    "/v1/contacts/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.manage")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updateContact(context, id, req.body));
    })
  );

  app.delete("/v1/contacts/:id", asyncHandler(async (req, res) => {
    const context = await requireSession(req, res, auth); if (!context) return;
    if (!requirePermission(context, res, "company.manage")) return;
    const id = requiredParam(req, res, "id"); if (!id) return;
    await handleCoreResponse(res, async () => { await corePersistence.deleteContact(context, id); return { deleted: true }; });
  }));

  app.get(
    "/v1/parts",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "part.read")) return;
      await handleCoreResponse(res, () => corePersistence.listParts(context));
    })
  );

  app.get(
    "/v1/parts/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "part.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, async () => {
        const part = await corePersistence.getPartById(context, id);
        if (!part) throw new CoreDomainError("not_found", "Part was not found in the current tenant.");
        return part;
      });
    })
  );

  app.post(
    "/v1/parts",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "part.read")) return;
      await handleCoreResponse(res, () => corePersistence.createPart(context, req.body), 201);
    })
  );

  app.patch(
    "/v1/parts/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "part.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updatePart(context, id, req.body));
    })
  );

  app.get(
    "/v1/parts/:id/360",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      const partId = req.params.id;
      if (!partId) {
        res.status(400).json({ error: "part_id_required" });
        return;
      }
      const part = await dataSource.getPart360(context, partId);
      if (!part) {
        res.status(404).json({ error: "part_not_found" });
        return;
      }
      res.json({ data: part });
    })
  );

  app.get(
    "/v1/stock/internal",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      res.json({ data: await dataSource.listInternalStock(context) });
    })
  );

  app.get(
    "/v1/stock/external",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      res.json({ data: await dataSource.listExternalStock(context) });
    })
  );

  app.get(
    "/v1/stock/:id/360",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      const stockId = req.params.id;
      if (!stockId) {
        res.status(400).json({ error: "stock_id_required" });
        return;
      }
      const stock = await dataSource.getStock360(context, stockId);
      if (!stock) {
        res.status(404).json({ error: "stock_not_found" });
        return;
      }
      res.json({ data: stock });
    })
  );

  app.get(
    "/v1/company-inventory",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      res.json({ data: await dataSource.getCompanyInventory(context) });
    })
  );

  app.get(
    "/v1/stock",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "stock.read")) return;
      await handleCoreResponse(res, () => corePersistence.listStock(context));
    })
  );

  app.get(
    "/v1/stock/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "stock.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, async () => {
        const stock = await corePersistence.getStockById(context, id);
        if (!stock) throw new CoreDomainError("not_found", "Stock item was not found in the current tenant.");
        return stock;
      });
    })
  );

  app.post(
    "/v1/stock",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "stock.read")) return;
      await handleCoreResponse(res, () => corePersistence.createStockItem(context, req.body), 201);
    })
  );

  app.patch(
    "/v1/stock/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "stock.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updateStockItem(context, id, req.body));
    })
  );

  app.get(
    "/v1/documents",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "document.read")) return;
      res.json({ data: await dataSource.listDocuments(context) });
    })
  );

  app.get(
    "/v1/documents/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "document.read")) return;
      const documentId = req.params.id;
      if (!documentId) {
        res.status(400).json({ error: "document_id_required" });
        return;
      }
      const document = await dataSource.getDocument(context, documentId);
      if (!document) {
        res.status(404).json({ error: "document_not_found" });
        return;
      }
      res.json({ data: document });
    })
  );

  app.post(
    "/v1/documents/upload-intent",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      const result = await dataSource.validateDocumentUpload(context, req.body);
      res.status(result.accepted ? 200 : 400).json({ data: result });
    })
  );

  app.get(
    "/v1/entities/:ownerModule/:ownerRecordId/documents",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "document.read")) return;
      const ownerModule = req.params.ownerModule as DocumentOwnerModule | undefined;
      const ownerRecordId = req.params.ownerRecordId;
      if (!ownerModule || !documentOwnerModules.has(ownerModule)) {
        res.status(400).json({ error: "unsupported_document_owner_module" });
        return;
      }
      if (!ownerRecordId) {
        res.status(400).json({ error: "owner_record_required" });
        return;
      }
      res.json({ data: await dataSource.listEntityDocuments(context, ownerModule, ownerRecordId) });
    })
  );

  app.get(
    "/v1/audit",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      res.json({ data: await audit.list(context) });
    })
  );

  app.use(errorHandler);

  return app;
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  createApp().listen(port, () => {
    console.log(`SaaS Aviation API listening on ${port}`);
  });
}
