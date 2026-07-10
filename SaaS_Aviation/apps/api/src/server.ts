import { pathToFileURL } from "node:url";
import cors from "cors";
import express, { type ErrorRequestHandler, type RequestHandler } from "express";
import helmet from "helmet";
import { ZodError } from "zod";
import { CoreDomainError } from "@saas-aviation/shared";
import type { AviationErpDataSource, CorePersistence, DocumentOwnerModule } from "@saas-aviation/shared";
import { getLegacyDataSource } from "./adapters/legacy-mysql-adapter.js";
import { AuditService } from "./audit/audit-service.js";
import { InMemoryAuthProvider, type AuthProvider } from "./auth/auth-provider.js";
import { requirePermission, requireSession } from "./auth/route-guard.js";
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

  res.status(500).json({ error: "internal_server_error" });
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
  const auth = dependencies.auth ?? new InMemoryAuthProvider();
  const audit = new AuditService(dataSource);

  app.use(createHelmetMiddleware());
  app.use(cors({ origin: process.env.CORS_ORIGIN?.split(",") ?? ["http://localhost:3007"] }));
  app.use(express.json());

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

      const session = await auth.authenticateWithPassword(email, password);
      if (!session) {
        res.status(401).json({ error: "invalid_credentials" });
        return;
      }

      res.json({ session });
    })
  );

  app.post(
    "/v1/auth/logout",
    asyncHandler(async (req, res) => {
      const token = req.headers.authorization?.replace(/^Bearer\s+/i, "");
      if (token) {
        await auth.revokeSession(token);
      }

      res.status(204).send();
    })
  );

  app.get(
    "/v1/session",
    asyncHandler(async (req, res) => {
      const session = await auth.getCurrentSession(req.headers.authorization);
      res.json({ session });
    })
  );

  app.get(
    "/v1/companies",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.read")) return;
      await handleCoreResponse(res, () => corePersistence.listCompanies(context));
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
      if (!requirePermission(context, res, "company.read")) return;
      await handleCoreResponse(res, () => corePersistence.createCompany(context, req.body), 201);
    })
  );

  app.patch(
    "/v1/companies/:id",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      if (!requirePermission(context, res, "company.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updateCompany(context, id, req.body));
    })
  );

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
      if (!requirePermission(context, res, "company.read")) return;
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
      if (!requirePermission(context, res, "company.read")) return;
      const id = requiredParam(req, res, "id");
      if (!id) return;
      await handleCoreResponse(res, () => corePersistence.updateContact(context, id, req.body));
    })
  );

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
