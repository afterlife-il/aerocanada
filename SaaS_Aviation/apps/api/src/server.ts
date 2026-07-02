import cors from "cors";
import express from "express";
import helmet from "helmet";
import type { DocumentOwnerModule } from "@saas-aviation/shared";
import { getLegacyDataSource } from "./adapters/legacy-mysql-adapter.js";
import { AuditService } from "./audit/audit-service.js";
import { InMemoryAuthProvider } from "./auth/auth-provider.js";
import { requireSession } from "./auth/route-guard.js";
import { openApiDocument } from "./openapi/openapi.js";

const app = express();
const port = Number(process.env.API_PORT ?? 4107);
const dataSource = getLegacyDataSource();
const auth = new InMemoryAuthProvider();
const audit = new AuditService(dataSource);
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

app.use(helmet());
app.use(cors({ origin: process.env.CORS_ORIGIN?.split(",") ?? ["http://localhost:3007"] }));
app.use(express.json());

app.get("/health", (_req, res) => {
  res.json({ status: "ok", service: "saas-aviation-api" });
});

app.get("/openapi.json", (_req, res) => {
  res.json(openApiDocument);
});

app.post("/v1/auth/login", async (req, res) => {
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
});

app.post("/v1/auth/logout", async (req, res) => {
  const token = req.headers.authorization?.replace(/^Bearer\s+/i, "");
  if (token) {
    await auth.revokeSession(token);
  }

  res.status(204).send();
});

app.get("/v1/session", async (req, res) => {
  const session = await auth.getCurrentSession(req.headers.authorization);
  res.json({ session });
});

app.get("/v1/companies", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.listCompanies(context) });
});

app.get("/v1/parts", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.listParts(context) });
});

app.get("/v1/parts/:id/360", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  const part = await dataSource.getPart360(context, req.params.id);
  if (!part) {
    res.status(404).json({ error: "part_not_found" });
    return;
  }
  res.json({ data: part });
});

app.get("/v1/stock/internal", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.listInternalStock(context) });
});

app.get("/v1/stock/external", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.listExternalStock(context) });
});

app.get("/v1/stock/:id/360", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  const stock = await dataSource.getStock360(context, req.params.id);
  if (!stock) {
    res.status(404).json({ error: "stock_not_found" });
    return;
  }
  res.json({ data: stock });
});

app.get("/v1/company-inventory", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.getCompanyInventory(context) });
});

app.get("/v1/documents", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await dataSource.listDocuments(context) });
});

app.get("/v1/documents/:id", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  const document = await dataSource.getDocument(context, req.params.id);
  if (!document) {
    res.status(404).json({ error: "document_not_found" });
    return;
  }
  res.json({ data: document });
});

app.post("/v1/documents/upload-intent", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  const result = await dataSource.validateDocumentUpload(context, req.body);
  res.status(result.accepted ? 200 : 400).json({ data: result });
});

app.get("/v1/entities/:ownerModule/:ownerRecordId/documents", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  if (!documentOwnerModules.has(req.params.ownerModule as DocumentOwnerModule)) {
    res.status(400).json({ error: "unsupported_document_owner_module" });
    return;
  }
  res.json({ data: await dataSource.listEntityDocuments(context, req.params.ownerModule as DocumentOwnerModule, req.params.ownerRecordId) });
});

app.get("/v1/audit", async (req, res) => {
  const context = await requireSession(req, res, auth);
  if (!context) return;
  res.json({ data: await audit.list(context) });
});

app.listen(port, () => {
  console.log(`SaaS Aviation API listening on ${port}`);
});
