import {
  buildCompanyInventoryReadModel,
  buildEntityDocumentReadModel,
  sampleAuditEvents,
  sampleAccountingAlerts,
  sampleCompanies,
  sampleContacts,
  sampleDocumentAlerts,
  sampleDocumentLinks,
  sampleDocuments,
  sampleDocumentVersions,
  sampleExternalStock,
  sampleAdminUser,
  sampleInternalStock,
  sampleKpis,
  sampleOrders,
  sampleParts,
  sampleQuotes,
  sampleRfqs,
  sampleServiceWorkflows,
  sampleSupplierQuotes,
  sampleTenant,
  sampleUsers,
  type AuditEvent,
  type Company,
  type Contact,
  type DocumentCenterReadModel,
  type EntityDocumentReadModel,
  type OrderSummary,
  type QuoteSummary,
  type RequestContext,
  type RfqSummary,
  type StockItem,
  type SupplierQuoteSummary,
  type WorkflowBoundaryAction
} from "@saas-aviation/shared";

export const currentSession = {
  user: sampleAdminUser,
  tenant: sampleTenant
};

export const data = {
  tenant: sampleTenant,
  users: sampleUsers,
  session: currentSession,
  companies: sampleCompanies,
  contacts: sampleContacts,
  parts: sampleParts,
  internalStock: sampleInternalStock,
  externalStock: sampleExternalStock,
  rfqs: sampleRfqs,
  quotes: sampleQuotes,
  supplierQuotes: sampleSupplierQuotes,
  orders: sampleOrders,
  serviceWorkflows: sampleServiceWorkflows,
  documents: sampleDocumentAlerts,
  accountingAlerts: sampleAccountingAlerts,
  audit: sampleAuditEvents,
  kpis: sampleKpis
};

function firstOrThrow<T>(items: T[], label: string): T {
  const first = items[0];
  if (!first) {
    throw new Error(`Sample data is missing required ${label}`);
  }
  return first;
}

export function getCompany(id: string) {
  return data.companies.find((company) => company.id === id || String(company.legacyId) === id) ?? firstOrThrow(data.companies, "company");
}

export function getPart(id: string) {
  return data.parts.find((part) => part.id === id || part.pn === id || String(part.legacyId) === id) ?? firstOrThrow(data.parts, "part");
}

export function getStock(id: string) {
  return (
    [...data.internalStock, ...data.externalStock].find((stock) => stock.id === id || String(stock.legacyId) === id) ??
    firstOrThrow(data.internalStock, "internal stock")
  );
}

export type CompanyStatusFilter = "all" | "active" | "inactive";
export type CompanySortKey = "name" | "type" | "location" | "risk" | "lastActivity";
export type CompanySortDirection = "asc" | "desc";

export interface CompanyListQuery {
  query?: string;
  type?: Company["type"] | "all";
  status?: CompanyStatusFilter;
  sort?: CompanySortKey;
  direction?: CompanySortDirection;
  page?: number;
  pageSize?: number;
}

export interface CompanyListRow {
  company: Company;
  primaryContact: Contact | null;
  contactCount: number;
  internalUnits: number;
  externalUnits: number;
  stockValue: number;
  documentCount: number;
  status: "active" | "inactive";
}

export interface CompanyListReadModel {
  tenantId: string;
  tenantCode: string;
  state: "loading" | "ready" | "empty" | "error";
  rows: CompanyListRow[];
  allRows: CompanyListRow[];
  error: string | null;
  emptyState: {
    title: string;
    detail: string;
  };
  filters: {
    query: string;
    type: Company["type"] | "all";
    status: CompanyStatusFilter;
    sort: CompanySortKey;
    direction: CompanySortDirection;
    availableTypes: Company["type"][];
  };
  pagination: {
    page: number;
    pageSize: number;
    totalRows: number;
    totalPages: number;
  };
}

interface ActivityPanel<T> {
  label: string;
  state: "ready" | "empty" | "error";
  rows: T[];
  boundaryAction: WorkflowBoundaryAction;
}

export interface Company360ReadModel {
  tenantId: string;
  tenantCode: string;
  company: Company;
  overviewKpis: Array<{ label: string; value: string; trend: string; tone: "neutral" | "good" | "warning" | "critical" }>;
  contacts: Contact[];
  inventorySummary: {
    internalUnits: number;
    externalUnits: number;
    zeroQtyRows: number;
    stockValue: number;
    currency: string;
    stockLines: StockItem[];
  };
  documents: EntityDocumentReadModel;
  documentCenter: DocumentCenterReadModel;
  commercialActivity: {
    rfqs: ActivityPanel<RfqSummary>;
    supplierQuotes: ActivityPanel<SupplierQuoteSummary>;
    customerQuotes: ActivityPanel<QuoteSummary>;
    purchaseOrders: ActivityPanel<OrderSummary>;
    salesOrders: ActivityPanel<OrderSummary>;
  };
  activity: AuditEvent[];
  boundaryActions: WorkflowBoundaryAction[];
}

function contextFromCurrentSession(): RequestContext {
  return {
    tenant: {
      tenantId: sampleTenant.id,
      tenantCode: sampleTenant.code,
      tenantName: sampleTenant.name,
      userId: sampleAdminUser.id,
      roles: sampleAdminUser.roles,
      permissions: sampleAdminUser.permissions
    }
  };
}

function companyStatus(company: Company): "active" | "inactive" {
  return company.riskLevel === "blocked" ? "inactive" : "active";
}

function normalize(value: string | number | undefined): string {
  return String(value ?? "").toLowerCase();
}

function stockLinkedToCompany(stock: StockItem, company: Company): boolean {
  return [stock.ownerCompany, stock.supplierCompany, stock.tagInfoCompany, stock.traceabilityCompany].includes(company.name);
}

function inventorySource() {
  return {
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
  };
}

function documentSource() {
  return {
    documents: sampleDocuments,
    versions: sampleDocumentVersions,
    links: sampleDocumentLinks,
    auditEvents: sampleAuditEvents
  };
}

function boundaryAction(
  context: RequestContext,
  id: string,
  label: string,
  entityType: WorkflowBoundaryAction["entityType"],
  entityId: string,
  requiredData: string[],
  futureOwner: string,
  note: string
): WorkflowBoundaryAction {
  return {
    id,
    label,
    tenantId: context.tenant.tenantId,
    entityType,
    entityId,
    mode: "boundary",
    persistence: "none",
    requiredData,
    contextChecks: ["tenantId", "companyId", "permission"],
    futureOwner,
    note
  };
}

function activityPanel<T>(label: string, rows: T[], boundaryAction: WorkflowBoundaryAction): ActivityPanel<T> {
  return {
    label,
    rows,
    state: rows.length > 0 ? "ready" : "empty",
    boundaryAction
  };
}

export function getCompanyListReadModel(query: CompanyListQuery = {}, context: RequestContext = contextFromCurrentSession()): CompanyListReadModel {
  try {
    const inventory = buildCompanyInventoryReadModel(context, inventorySource());
    const normalizedQuery = normalize(query.query).trim();
    const type = query.type ?? "all";
    const status = query.status ?? "all";
    const sort = query.sort ?? "name";
    const direction = query.direction ?? "asc";
    const pageSize = Math.max(1, query.pageSize ?? 25);
    const page = Math.max(1, query.page ?? 1);

    const allRows = sampleCompanies
      .filter((company) => company.tenantId === context.tenant.tenantId)
      .map<CompanyListRow>((company) => {
        const contacts = sampleContacts.filter((contact) => contact.tenantId === context.tenant.tenantId && contact.companyId === company.id);
        const inventoryRow = inventory.rows.find((row) => row.companyId === company.id);
        const documents = buildEntityDocumentReadModel(context, "company", company.id, documentSource());

        return {
          company,
          primaryContact: contacts[0] ?? null,
          contactCount: contacts.length,
          internalUnits: inventoryRow?.internalUnits ?? 0,
          externalUnits: inventoryRow?.externalUnits ?? 0,
          stockValue: inventoryRow?.stockValue ?? 0,
          documentCount: documents.documents.length,
          status: companyStatus(company)
        };
      });

    const filteredRows = allRows
      .filter((row) => {
        if (type !== "all" && row.company.type !== type) return false;
        if (status !== "all" && row.status !== status) return false;
        if (!normalizedQuery) return true;

        return [
          row.company.name,
          row.company.legacyId,
          row.company.primaryEmail,
          row.company.website,
          row.company.city,
          row.company.country,
          row.primaryContact?.email,
          row.primaryContact?.phone,
          row.primaryContact?.name,
          ...row.company.tags
        ].some((field) => normalize(field).includes(normalizedQuery));
      })
      .sort((left, right) => {
        const values: Record<CompanySortKey, [string | number, string | number]> = {
          name: [left.company.name, right.company.name],
          type: [left.company.type, right.company.type],
          location: [
            [left.company.city, left.company.country].filter(Boolean).join(", "),
            [right.company.city, right.company.country].filter(Boolean).join(", ")
          ],
          risk: [left.company.riskLevel, right.company.riskLevel],
          lastActivity: [left.company.lastActivityAt ?? "", right.company.lastActivityAt ?? ""]
        };
        const [leftValue, rightValue] = values[sort];
        const result = String(leftValue).localeCompare(String(rightValue), "en-US", { numeric: true });
        return direction === "asc" ? result : -result;
      });

    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));

    return {
      tenantId: context.tenant.tenantId,
      tenantCode: context.tenant.tenantCode,
      state: filteredRows.length > 0 ? "ready" : "empty",
      rows: filteredRows.slice((page - 1) * pageSize, page * pageSize),
      allRows,
      error: null,
      emptyState: {
        title: "No companies found",
        detail: normalizedQuery ? "No tenant company matches the current search and filters." : "No tenant companies are available in this read model."
      },
      filters: {
        query: query.query ?? "",
        type,
        status,
        sort,
        direction,
        availableTypes: ["customer", "supplier", "owner", "repair-vendor", "mixed"]
      },
      pagination: {
        page,
        pageSize,
        totalRows: filteredRows.length,
        totalPages
      }
    };
  } catch (error) {
    return {
      tenantId: context.tenant.tenantId,
      tenantCode: context.tenant.tenantCode,
      state: "error",
      rows: [],
      allRows: [],
      error: error instanceof Error ? error.message : "Unable to load Company list.",
      emptyState: {
        title: "Companies unavailable",
        detail: "The Company list read model could not be generated."
      },
      filters: {
        query: query.query ?? "",
        type: query.type ?? "all",
        status: query.status ?? "all",
        sort: query.sort ?? "name",
        direction: query.direction ?? "asc",
        availableTypes: ["customer", "supplier", "owner", "repair-vendor", "mixed"]
      },
      pagination: {
        page: 1,
        pageSize: query.pageSize ?? 25,
        totalRows: 0,
        totalPages: 1
      }
    };
  }
}

export function getCompany360ReadModel(id: string, context: RequestContext = contextFromCurrentSession()): Company360ReadModel {
  const company =
    sampleCompanies.find((item) => item.tenantId === context.tenant.tenantId && (item.id === id || String(item.legacyId) === id)) ??
    firstOrThrow(sampleCompanies.filter((item) => item.tenantId === context.tenant.tenantId), "company");
  const contacts = sampleContacts.filter((contact) => contact.tenantId === context.tenant.tenantId && contact.companyId === company.id);
  const inventory = buildCompanyInventoryReadModel(context, inventorySource());
  const inventoryRow = inventory.rows.find((row) => row.companyId === company.id);
  const stockLines = [...sampleInternalStock, ...sampleExternalStock].filter((stock) => stock.tenantId === context.tenant.tenantId && stockLinkedToCompany(stock, company));
  const documents = buildEntityDocumentReadModel(context, "company", company.id, documentSource());
  const documentCenter: DocumentCenterReadModel = {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    documents: documents.documents,
    summary: {
      total: documents.documents.length,
      clean: documents.documents.filter((document) => document.status === "active").length,
      needsReview: documents.documents.filter((document) => document.status !== "active").length,
      restricted: documents.documents.filter((document) => document.visibility === "restricted").length,
      totalSizeBytes: documents.documents.reduce((total, document) => total + document.sizeBytes, 0)
    }
  };

  const boundaryActions = [
    boundaryAction(context, "edit-company", "Edit Company", "company", company.id, ["tenantId", "companyId", "changedFields"], "Company module", "Entry point only. Company mutation persistence is outside this read-only foundation."),
    boundaryAction(context, "create-contact", "Create Contact", "contact", company.id, ["tenantId", "companyId", "name", "email", "phone", "role"], "Contact module", "Workflow Boundary: contact creation continues in the future Contact module."),
    boundaryAction(context, "edit-contact", "Edit Contact", "contact", company.id, ["tenantId", "companyId", "contactId", "changedFields"], "Contact module", "Workflow Boundary: contact editing continues in the future Contact module."),
    boundaryAction(context, "add-document", "Add Document", "company", company.id, ["tenantId", "companyId", "documentType", "file", "visibility"], "Documents module", "Workflow Boundary: metadata is validated here; byte storage continues in the Documents module."),
    boundaryAction(context, "create-rfq", "Create RFQ", "rfq", company.id, ["tenantId", "companyId", "partNumber", "qty", "priority"], "RFQ module", "Workflow Boundary: RFQ creation continues in the future RFQ module."),
    boundaryAction(context, "view-company-inventory", "View Company Inventory", "company-inventory", company.id, ["tenantId", "companyId"], "Inventory module", "Workflow Boundary: detailed stock operations continue in Company Inventory.")
  ];
  const rfqs = sampleRfqs.filter((rfq) => rfq.tenantId === context.tenant.tenantId && rfq.customerName === company.name);
  const supplierQuotes = sampleSupplierQuotes.filter((quote) => quote.tenantId === context.tenant.tenantId && quote.supplierName === company.name);
  const customerQuotes = sampleQuotes.filter((quote) => quote.tenantId === context.tenant.tenantId && quote.customerName === company.name);
  const purchaseOrders = sampleOrders.filter((order) => order.tenantId === context.tenant.tenantId && order.kind === "purchase" && order.companyName === company.name);
  const salesOrders = sampleOrders.filter((order) => order.tenantId === context.tenant.tenantId && order.kind === "sales" && order.companyName === company.name);

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    company,
    overviewKpis: [
      { label: "Contacts", value: String(contacts.length), trend: contacts.length ? "Primary contact available" : "No contacts", tone: contacts.length ? "good" : "warning" },
      { label: "Inventory units", value: String((inventoryRow?.internalUnits ?? 0) + (inventoryRow?.externalUnits ?? 0)), trend: `${inventoryRow?.zeroQtyRows ?? 0} zero qty rows`, tone: (inventoryRow?.zeroQtyRows ?? 0) > 0 ? "warning" : "neutral" },
      { label: "Stock value", value: `$${(inventoryRow?.stockValue ?? 0).toLocaleString("en-US")}`, trend: inventoryRow?.currency ?? "USD", tone: (inventoryRow?.stockValue ?? 0) > 0 ? "good" : "neutral" },
      { label: "Documents", value: String(documents.documents.length), trend: documents.documents.length ? "Linked records" : "No linked documents", tone: documents.documents.length ? "good" : "neutral" }
    ],
    contacts,
    inventorySummary: {
      internalUnits: inventoryRow?.internalUnits ?? 0,
      externalUnits: inventoryRow?.externalUnits ?? 0,
      zeroQtyRows: inventoryRow?.zeroQtyRows ?? 0,
      stockValue: inventoryRow?.stockValue ?? 0,
      currency: inventoryRow?.currency ?? "USD",
      stockLines
    },
    documents,
    documentCenter,
    commercialActivity: {
      rfqs: activityPanel("RFQ", rfqs, boundaryActions.find((action) => action.id === "create-rfq") as WorkflowBoundaryAction),
      supplierQuotes: activityPanel("Supplier Quotes", supplierQuotes, boundaryAction(context, "open-supplier-quotes", "Open Supplier Quotes", "supplier-quote", company.id, ["tenantId", "companyId"], "Supplier Quotes module", "Workflow Boundary: supplier quote workflow is not implemented in Company 360.")),
      customerQuotes: activityPanel("Customer Quotes", customerQuotes, boundaryAction(context, "open-customer-quotes", "Open Customer Quotes", "customer-quote", company.id, ["tenantId", "companyId"], "Customer Quotes module", "Workflow Boundary: customer quote workflow is not implemented in Company 360.")),
      purchaseOrders: activityPanel("Purchase Orders", purchaseOrders, boundaryAction(context, "open-purchase-orders", "Open Purchase Orders", "purchase-order", company.id, ["tenantId", "companyId"], "Purchase Orders module", "Workflow Boundary: purchase order workflow is not implemented in Company 360.")),
      salesOrders: activityPanel("Sales Orders", salesOrders, boundaryAction(context, "open-sales-orders", "Open Sales Orders", "sales-order", company.id, ["tenantId", "companyId"], "Sales Orders module", "Workflow Boundary: sales order workflow is not implemented in Company 360."))
    },
    activity: sampleAuditEvents.filter((event) => event.tenantId === context.tenant.tenantId && (event.entityId === company.id || event.summary.includes(company.name))),
    boundaryActions
  };
}
