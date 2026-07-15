import test from "node:test";
import assert from "node:assert/strict";
import { buildTenantDashboard } from "./dashboard-service.js";
import { buildDocumentCenterReadModel, buildEntityDocumentReadModel, validateDocumentUploadRequest } from "./document-service.js";
import { buildCompanyInventoryReadModel, buildPart360ReadModel, buildStock360ReadModel } from "./part-stock-service.js";
import {
  sampleAccountingAlerts,
  sampleAuditEvents,
  sampleCompanies,
  sampleDocumentAlerts,
  sampleDocuments,
  sampleDocumentVersions,
  sampleDocumentLinks,
  sampleExternalStock,
  sampleInternalStock,
  sampleOrders,
  sampleParts,
  sampleQuotes,
  sampleRequestContext,
  sampleRfqs,
  sampleServiceWorkflows,
  sampleSupplierQuotes,
  sampleTenant,
  sampleUsers
} from "./sample-data.js";

test("Qty 0 remains represented as zero", () => {
  const zeroQty = sampleInternalStock.find((item) => item.qty === 0);
  assert.ok(zeroQty);
  assert.equal(zeroQty?.qty, 0);
});

test("RFQ_ID remains present on RFQ summaries", () => {
  assert.ok(sampleRfqs.every((rfq) => rfq.rfqId.length > 0));
});

test("first tenant seed has an admin user and tenant-owned records", () => {
  const admin = sampleUsers.find((user) => user.tenantId === sampleTenant.id && user.roles.includes("owner_admin"));
  assert.ok(admin);
  assert.equal(sampleTenant.name, "AEROCANADA INDUSTRIES 770 INC.");
  assert.equal(sampleInternalStock.every((item) => item.tenantId === sampleTenant.id), true);
  assert.equal(sampleRfqs.every((rfq) => rfq.tenantId === sampleTenant.id), true);
});


test("tenant-aware dashboard is scoped and preserves workflow keys", () => {
  const dashboard = buildTenantDashboard(sampleRequestContext, {
    companies: sampleCompanies,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    accountingAlerts: sampleAccountingAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.equal(dashboard.tenantId, sampleTenant.id);
  assert.equal(dashboard.rfqsOpen.every((rfq) => rfq.tenantId === sampleTenant.id && rfq.rfqId.startsWith("RFQ-")), true);
  assert.equal(dashboard.quotesPending.every((quote) => quote.tenantId === sampleTenant.id && quote.rfqId.startsWith("RFQ-")), true);
  assert.equal(dashboard.stockValue.zeroQtyVisible, 1);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "repair"), true);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "exchange"), true);
  assert.equal(dashboard.serviceWorkflows.some((workflow) => workflow.kind === "lease"), true);
});

test("tenant-aware dashboard excludes other tenant data", () => {
  const dashboard = buildTenantDashboard(
    {
      tenant: {
        ...sampleRequestContext.tenant,
        tenantId: "tenant-other",
        tenantCode: "OTHER",
        tenantName: "Other Tenant"
      }
    },
    {
      companies: sampleCompanies,
      internalStock: sampleInternalStock,
      externalStock: sampleExternalStock,
      rfqs: sampleRfqs,
      quotes: sampleQuotes,
      supplierQuotes: sampleSupplierQuotes,
      orders: sampleOrders,
      serviceWorkflows: sampleServiceWorkflows,
      documents: sampleDocumentAlerts,
      accountingAlerts: sampleAccountingAlerts,
      auditEvents: sampleAuditEvents
    }
  );

  assert.equal(dashboard.rfqsOpen.length, 0);
  assert.equal(dashboard.quotesPending.length, 0);
  assert.equal(dashboard.stockValue.totalValue, 0);
  assert.equal(dashboard.accountingAlerts.length, 0);
});

test("Part 360 read model links tenant-scoped stock, RFQs, quotes, documents, and boundary actions", () => {
  const part360 = buildPart360ReadModel(sampleRequestContext, "part-1", {
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.ok(part360);
  assert.equal(part360?.tenantId, sampleTenant.id);
  assert.equal(part360?.part.pn, "03-1802-2001");
  assert.equal(part360?.stockAvailability.internalUnits, 1);
  assert.equal(part360?.rfqs.every((rfq) => rfq.rfqId.startsWith("RFQ-")), true);
  assert.equal(part360?.customerQuotes.every((quote) => quote.rfqId.startsWith("RFQ-")), true);
  assert.equal(part360?.documents.some((document) => document.documentType === "8130-3"), true);
  assert.equal(part360?.quickActions.every((action) => action.mode === "boundary" && action.persistence === "none"), true);
  assert.ok(part360?.quickActions.find((action) => action.id === "create-rfq")?.requiredData.includes("tenantId"));
});

test("Part 360 header summarizes availability, condition, certification, and last update", () => {
  const part360 = buildPart360ReadModel(sampleRequestContext, "part-1", {
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.equal(part360?.header.availabilityStatus, "in-stock");
  assert.deepEqual(part360?.header.conditionSummary, [{ condition: "SV", qty: 1, lines: 1 }]);
  assert.ok(part360?.header.certificationIndicators.find((indicator) => indicator.documentType === "8130-3" && indicator.status === "pending-review"));
  assert.ok(part360?.header.certificationIndicators.find((indicator) => indicator.documentType === "CoC" && indicator.status === "missing"));
  assert.ok(part360?.header.lastUpdatedAt);
});

test("Part 360 traceability summary links previous owner, origin, repair references, and serials", () => {
  const part360 = buildPart360ReadModel(sampleRequestContext, "part-1", {
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.deepEqual(part360?.traceabilitySummary.previousOwners, ["Better Aviation Products"]);
  assert.deepEqual(part360?.traceabilitySummary.origins, ["Better Aviation Products"]);
  assert.equal(part360?.traceabilitySummary.repairReferences.some((workflow) => workflow.reference === "REP-03-1802"), true);
  assert.equal(part360?.traceabilitySummary.serialTraceability.some((row) => row.serialNumber === "SNT140034"), true);
});

test("Part 360 read model returns null when part belongs to another tenant", () => {
  const part360 = buildPart360ReadModel(
    {
      tenant: {
        ...sampleRequestContext.tenant,
        tenantId: "tenant-other"
      }
    },
    "part-1",
    {
      companies: sampleCompanies,
      parts: sampleParts,
      internalStock: sampleInternalStock,
      externalStock: sampleExternalStock,
      rfqs: sampleRfqs,
      quotes: sampleQuotes,
      supplierQuotes: sampleSupplierQuotes,
      orders: sampleOrders,
      serviceWorkflows: sampleServiceWorkflows,
      documents: sampleDocumentAlerts,
      auditEvents: sampleAuditEvents
    }
  );

  assert.equal(part360, null);
});

test("Stock 360 read model keeps Qty 0 stock visible and exposes non-persistent action boundaries", () => {
  const stock360 = buildStock360ReadModel(sampleRequestContext, "stock-2", {
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.ok(stock360);
  assert.equal(stock360?.stock.qty, 0);
  assert.equal(stock360?.stock.tenantId, sampleTenant.id);
  assert.equal(stock360?.lifecycle.some((event) => event.entityId === "stock-2"), true);
  assert.ok(stock360?.quickActions.find((action) => action.id === "reserve-stock")?.futureOwner.includes("Inventory"));
  assert.equal(stock360?.quickActions.every((action) => action.persistence === "none"), true);
});

test("Company Inventory read model summarizes stock by company without leaking other tenants", () => {
  const inventory = buildCompanyInventoryReadModel(sampleRequestContext, {
    companies: sampleCompanies,
    parts: sampleParts,
    internalStock: sampleInternalStock,
    externalStock: sampleExternalStock,
    rfqs: sampleRfqs,
    quotes: sampleQuotes,
    supplierQuotes: sampleSupplierQuotes,
    orders: sampleOrders,
    serviceWorkflows: sampleServiceWorkflows,
    documents: sampleDocumentAlerts,
    auditEvents: sampleAuditEvents
  });

  assert.equal(inventory.tenantId, sampleTenant.id);
  assert.equal(inventory.rows.every((row) => row.tenantId === sampleTenant.id), true);
  assert.equal(inventory.totals.zeroQtyRows, 1);
  assert.ok(inventory.rows.find((row) => row.companyName === "AeroCanada Industries 770")?.stockLines.length);
  assert.ok(inventory.quickActions.find((action) => action.id === "add-stock")?.requiredData.includes("ownerCompanyId"));
});

test("Documents read model is tenant-scoped and links documents to aviation entities", () => {
  const center = buildDocumentCenterReadModel(sampleRequestContext, {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks,
    auditEvents: sampleAuditEvents
  });

  assert.equal(center.tenantId, sampleTenant.id);
  assert.equal(center.documents.every((document) => document.tenantId === sampleTenant.id), true);
  assert.ok(center.documents.find((document) => document.documentType === "Certificate"));
  assert.ok(center.documents.find((document) => document.links.some((link) => link.ownerModule === "stock")));
  assert.equal(center.documents.every((document) => document.primaryLink.relation === "primary"), true);
  assert.equal(center.documents.find((document) => document.id === "doc-cert-stock-1")?.ownerRecordId, "stock-1");
  assert.equal(center.summary.clean, 5);
  assert.equal(center.summary.needsReview, 2);
});

test("DocumentLink primary relation is the canonical document owner", () => {
  const documents = buildDocumentCenterReadModel(sampleRequestContext, {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks.map((link) =>
      link.documentId === "doc-cert-stock-1" && link.relation === "primary"
        ? { ...link, ownerModule: "company", ownerRecordId: "demo-co-5263" }
        : link
    ),
    auditEvents: sampleAuditEvents
  });

  const document = documents.documents.find((item) => item.id === "doc-cert-stock-1");
  assert.equal(document?.ownerModule, "company");
  assert.equal(document?.ownerRecordId, "demo-co-5263");
});

test("Documents read model rejects documents without exactly one primary link", () => {
  assert.throws(
    () =>
      buildDocumentCenterReadModel(sampleRequestContext, {
        documents: sampleDocuments,
        versions: sampleDocumentVersions,
        links: sampleDocumentLinks.filter((link) => !(link.documentId === "doc-cert-stock-1" && link.relation === "primary")),
        auditEvents: sampleAuditEvents
      }),
    /document_primary_link_required/
  );
});

test("Entity documents read model returns only documents linked to the requested entity", () => {
  const stockDocuments = buildEntityDocumentReadModel(sampleRequestContext, "stock", "stock-1", {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks,
    auditEvents: sampleAuditEvents
  });

  assert.equal(stockDocuments.entityType, "stock");
  assert.equal(stockDocuments.entityId, "stock-1");
  assert.equal(stockDocuments.documents.length, 2);
  assert.equal(stockDocuments.documents.every((document) => document.links.some((link) => link.ownerModule === "stock" && link.ownerRecordId === "stock-1")), true);
});

test("Document upload validation rejects unsafe files and builds a non-persistent upload intent for valid files", () => {
  const accepted = validateDocumentUploadRequest(sampleRequestContext, {
    ownerModule: "stock",
    ownerRecordId: "stock-1",
    documentType: "Certificate",
    fileName: "../8130 light.pdf",
    mimeType: "application/pdf",
    sizeBytes: 512000,
    visibility: "customer-shareable",
    notes: "FAA 8130 for stock-1"
  });

  assert.equal(accepted.accepted, true);
  assert.equal(accepted.intent?.status, "validated");
  assert.equal(accepted.intent?.fileName, "8130-light.pdf");
  assert.equal(accepted.intent?.persistence, "metadata-only");
  assert.equal(accepted.intent?.securityChecks.includes("tenant-context"), true);
  assert.notEqual(accepted.intent?.uploadedAt, "2026-07-02T00:00:00Z");
  assert.doesNotThrow(() => new Date(accepted.intent?.uploadedAt ?? "").toISOString());

  const executable = validateDocumentUploadRequest(sampleRequestContext, {
    ownerModule: "stock",
    ownerRecordId: "stock-1",
    documentType: "Certificate",
    fileName: "danger.exe",
    mimeType: "application/x-msdownload",
    sizeBytes: 12,
    visibility: "internal",
    notes: ""
  });

  assert.equal(executable.accepted, false);
  assert.ok(executable.errors.includes("mime_type_not_allowed"));

  const tooLarge = validateDocumentUploadRequest(sampleRequestContext, {
    ownerModule: "stock",
    ownerRecordId: "stock-1",
    documentType: "Trace",
    fileName: "trace.pdf",
    mimeType: "application/pdf",
    sizeBytes: 30 * 1024 * 1024,
    visibility: "restricted",
    notes: ""
  });

  assert.equal(tooLarge.accepted, false);
  assert.ok(tooLarge.errors.includes("file_too_large"));
});
