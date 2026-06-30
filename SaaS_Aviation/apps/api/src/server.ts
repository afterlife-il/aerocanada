import cors from "cors";
import express from "express";
import helmet from "helmet";
import { getLegacyDataSource } from "./adapters/legacy-mysql-adapter.js";
import { AuditService } from "./audit/audit-service.js";
import { MockAuthProvider } from "./auth/auth-provider.js";
import { openApiDocument } from "./openapi/openapi.js";

const app = express();
const port = Number(process.env.API_PORT ?? 4107);
const dataSource = getLegacyDataSource();
const auth = new MockAuthProvider();
const audit = new AuditService(dataSource);

app.use(helmet());
app.use(cors({ origin: process.env.CORS_ORIGIN?.split(",") ?? ["http://localhost:3007"] }));
app.use(express.json());

app.get("/health", (_req, res) => {
  res.json({ status: "ok", service: "saas-aviation-api" });
});

app.get("/openapi.json", (_req, res) => {
  res.json(openApiDocument);
});

app.get("/v1/session", async (req, res) => {
  const user = await auth.getCurrentUser(req.headers.authorization);
  res.json({ user });
});

app.get("/v1/companies", async (_req, res) => {
  res.json({ data: await dataSource.listCompanies() });
});

app.get("/v1/parts", async (_req, res) => {
  res.json({ data: await dataSource.listParts() });
});

app.get("/v1/stock/internal", async (_req, res) => {
  res.json({ data: await dataSource.listInternalStock() });
});

app.get("/v1/stock/external", async (_req, res) => {
  res.json({ data: await dataSource.listExternalStock() });
});

app.get("/v1/audit", async (_req, res) => {
  res.json({ data: await audit.list() });
});

app.listen(port, () => {
  console.log(`SaaS Aviation API listening on ${port}`);
});
