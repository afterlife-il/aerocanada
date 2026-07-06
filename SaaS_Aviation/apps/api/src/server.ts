import { pathToFileURL } from "node:url";
import cors from "cors";
import express, { type ErrorRequestHandler, type RequestHandler } from "express";
import helmet from "helmet";
import type { AviationErpDataSource, DocumentOwnerModule } from "@saas-aviation/shared";
import { getLegacyDataSource } from "./adapters/legacy-mysql-adapter.js";
import { AuditService } from "./audit/audit-service.js";
import { InMemoryAuthProvider, type AuthProvider } from "./auth/auth-provider.js";
import { requirePermission, requireSession } from "./auth/route-guard.js";
import { openApiDocument } from "./openapi/openapi.js";

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

export function createApp(dependencies: AppDependencies = {}) {
  const app = express();
  const dataSource = dependencies.dataSource ?? getLegacyDataSource();
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
      res.json({ data: await dataSource.listCompanies(context) });
    })
  );

  app.get(
    "/v1/parts",
    asyncHandler(async (req, res) => {
      const context = await requireSession(req, res, auth);
      if (!context) return;
      res.json({ data: await dataSource.listParts(context) });
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
