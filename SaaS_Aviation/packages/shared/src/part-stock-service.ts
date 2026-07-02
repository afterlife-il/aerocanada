import type {
  AccountingAlert,
  AuditEvent,
  Company,
  CompanyInventoryReadModel,
  CompanyInventoryRow,
  DocumentAlert,
  MarginSummary,
  OrderSummary,
  Part360ReadModel,
  PartNumber,
  QuoteSummary,
  RequestContext,
  RfqSummary,
  ServiceWorkflowSummary,
  Stock360ReadModel,
  StockAvailabilitySummary,
  StockItem,
  SupplierQuoteSummary,
  WorkflowBoundaryAction
} from "./types.js";

export interface PartStockSource {
  companies: Company[];
  parts: PartNumber[];
  internalStock: StockItem[];
  externalStock: StockItem[];
  rfqs: RfqSummary[];
  quotes: QuoteSummary[];
  supplierQuotes: SupplierQuoteSummary[];
  orders: OrderSummary[];
  serviceWorkflows: ServiceWorkflowSummary[];
  documents: DocumentAlert[];
  auditEvents: AuditEvent[];
  accountingAlerts?: AccountingAlert[];
}

function tenantItems<T extends { tenantId: string }>(context: RequestContext, items: T[]): T[] {
  return items.filter((item) => item.tenantId === context.tenant.tenantId);
}

function stockValue(item: StockItem): number {
  return (item.price ?? 0) * item.qty;
}

function marginForQuotes(quotes: QuoteSummary[]): MarginSummary {
  const quotedValue = quotes.reduce((total, quote) => total + quote.value, 0);
  const quotedCost = quotes.reduce((total, quote) => total + quote.cost, 0);
  const grossMargin = quotedValue - quotedCost;
  return {
    quotedValue,
    quotedCost,
    grossMargin,
    marginPct: quotedValue > 0 ? (grossMargin / quotedValue) * 100 : 0,
    currency: quotes.find((quote) => quote.currency)?.currency ?? "USD"
  };
}

function stockAvailability(stock: StockItem[]): StockAvailabilitySummary {
  const internalStock = stock.filter((item) => item.source === "internal");
  const externalStock = stock.filter((item) => item.source === "external");
  const currency = stock.find((item) => item.currency)?.currency ?? "USD";

  return {
    internalUnits: internalStock.reduce((total, item) => total + item.qty, 0),
    externalUnits: externalStock.reduce((total, item) => total + item.qty, 0),
    internalLines: internalStock.length,
    externalLines: externalStock.length,
    availableUnits: stock.filter((item) => item.status === "available").reduce((total, item) => total + item.qty, 0),
    reservedUnits: stock.filter((item) => item.status === "reserved").reduce((total, item) => total + item.qty, 0),
    zeroQtyRows: stock.filter((item) => item.qty === 0).length,
    totalValue: stock.reduce((total, item) => total + stockValue(item), 0),
    currency
  };
}

function actionBoundary(
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
    contextChecks: ["RequestContext.tenant.tenantId", "actor permissions", "entity tenantId", "audit correlation id"],
    futureOwner,
    note
  };
}

function partActions(context: RequestContext, part: PartNumber): WorkflowBoundaryAction[] {
  return [
    actionBoundary(context, "create-rfq", "Create RFQ", "part", part.id, ["tenantId", "partId", "pn", "requestedQty", "customerCompanyId", "priority"], "RFQ module", "Boundary only. RFQ mutation will be owned by the future RFQ workflow service."),
    actionBoundary(context, "create-supplier-quote", "Create supplier quote", "part", part.id, ["tenantId", "partId", "pn", "supplierCompanyId", "qty", "targetCondition"], "Supplier Quote module", "Boundary only. Supplier quote persistence is outside this read-model slice."),
    actionBoundary(context, "create-customer-quote", "Create customer quote", "part", part.id, ["tenantId", "partId", "pn", "customerCompanyId", "rfqId", "sellPrice"], "Customer Quote module", "Boundary only. Customer quote persistence is outside this read-model slice."),
    actionBoundary(context, "add-stock", "Add stock", "part", part.id, ["tenantId", "partId", "ownerCompanyId", "condition", "qty", "location", "source"], "Inventory module", "Boundary only. Stock mutations require persistent auth, audit, and storage approvals."),
    actionBoundary(context, "upload-document", "Upload certificate/document", "part", part.id, ["tenantId", "partId", "documentType", "file", "linkedEntityId"], "Document module", "Boundary only. Uploads require storage, virus scanning, retention, and audit design.")
  ];
}

function stockActions(context: RequestContext, stock: StockItem): WorkflowBoundaryAction[] {
  return [
    actionBoundary(context, "reserve-stock", "Reserve stock", "stock", stock.id, ["tenantId", "stockId", "qty", "rfqId or salesOrderId", "reservationExpiresAt"], "Inventory Reservation module", "Boundary only. Reservation must be auditable and conflict-aware before persistence."),
    actionBoundary(context, "move-stock", "Move stock", "stock", stock.id, ["tenantId", "stockId", "fromLocation", "toLocation", "qty", "reason"], "Inventory Movement module", "Boundary only. Movement persistence requires stock ledger design."),
    actionBoundary(context, "upload-stock-document", "Upload certificate/document", "stock", stock.id, ["tenantId", "stockId", "documentType", "file", "certificateDate", "tagInfoCompanyId"], "Document module", "Boundary only. Uploads require approved storage and malware scanning."),
    actionBoundary(context, "create-rfq", "Create RFQ", "stock", stock.id, ["tenantId", "stockId", "partId", "customerCompanyId", "requestedQty"], "RFQ module", "Boundary only. RFQ persistence is owned by the future RFQ workflow service.")
  ];
}

function inventoryActions(context: RequestContext): WorkflowBoundaryAction[] {
  return [
    actionBoundary(context, "add-stock", "Add stock", "company-inventory", context.tenant.tenantId, ["tenantId", "ownerCompanyId", "partId", "condition", "qty", "location", "source"], "Inventory module", "Boundary only. No stock row is persisted in this slice."),
    actionBoundary(context, "upload-document", "Upload stock document", "company-inventory", context.tenant.tenantId, ["tenantId", "stockId", "documentType", "file", "ownerCompanyId"], "Document module", "Boundary only. Document storage is outside this slice."),
    actionBoundary(context, "move-stock", "Move stock", "company-inventory", context.tenant.tenantId, ["tenantId", "stockId", "fromLocation", "toLocation", "qty", "reason"], "Inventory Movement module", "Boundary only. Movement ledger persistence is outside this slice.")
  ];
}

function documentsForStock(documents: DocumentAlert[], stock: StockItem): DocumentAlert[] {
  return documents.filter((document) => document.entityType === "stock" && (document.entityId === stock.id || document.entityId === String(stock.legacyId)));
}

function stockLinkedToCompany(stock: StockItem[], company: Company): StockItem[] {
  return stock.filter(
    (item) =>
      item.ownerCompany === company.name ||
      item.supplierCompany === company.name ||
      item.tagInfoCompany === company.name ||
      item.traceabilityCompany === company.name
  );
}

export function buildPart360ReadModel(context: RequestContext, partId: string, source: PartStockSource): Part360ReadModel | null {
  const parts = tenantItems(context, source.parts);
  const part = parts.find((item) => item.id === partId || item.pn === partId || String(item.legacyId) === partId);
  if (!part) return null;

  const internalStock = tenantItems(context, source.internalStock).filter((item) => item.partId === part.id || item.pn === part.pn);
  const externalStock = tenantItems(context, source.externalStock).filter((item) => item.partId === part.id || item.pn === part.pn);
  const stock = [...internalStock, ...externalStock];
  const rfqs = tenantItems(context, source.rfqs).filter((rfq) => rfq.partNumber === part.pn);
  const customerQuotes = tenantItems(context, source.quotes).filter((quote) => quote.partNumber === part.pn);
  const supplierQuotes = tenantItems(context, source.supplierQuotes).filter((quote) => quote.partNumber === part.pn);
  const purchaseHistory = tenantItems(context, source.orders).filter((order) => order.kind === "purchase" && (order.rfqId ? rfqs.some((rfq) => rfq.rfqId === order.rfqId) : false));
  const salesHistory = tenantItems(context, source.orders).filter((order) => order.kind === "sales" && (order.rfqId ? rfqs.some((rfq) => rfq.rfqId === order.rfqId) : false));
  const serviceHistory = tenantItems(context, source.serviceWorkflows).filter((workflow) => workflow.partNumber === part.pn);
  const stockIds = new Set(stock.flatMap((item) => [item.id, String(item.legacyId)]));
  const documents = tenantItems(context, source.documents).filter((document) => stockIds.has(document.entityId) || customerQuotes.some((quote) => quote.id === document.entityId));
  const traceability = tenantItems(context, source.auditEvents).filter((event) => stockIds.has(event.entityId) || rfqs.some((rfq) => rfq.rfqId === event.rfqId));

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    part,
    stockAvailability: stockAvailability(stock),
    internalStock,
    externalStock,
    rfqs,
    supplierQuotes,
    customerQuotes,
    purchaseHistory,
    salesHistory,
    serviceHistory,
    certificates: documents.filter((document) => document.documentType === "8130-3" || document.documentType === "EASA Form 1" || document.documentType === "CoC"),
    documents,
    traceability,
    margin: marginForQuotes(customerQuotes),
    quickActions: partActions(context, part)
  };
}

export function buildStock360ReadModel(context: RequestContext, stockId: string, source: PartStockSource): Stock360ReadModel | null {
  const stock = [...tenantItems(context, source.internalStock), ...tenantItems(context, source.externalStock)].find(
    (item) => item.id === stockId || String(item.legacyId) === stockId
  );
  if (!stock) return null;

  const companies = tenantItems(context, source.companies);
  const companyByName = (name?: string) => companies.find((company) => company.name === name) ?? null;
  const part = tenantItems(context, source.parts).find((item) => item.id === stock.partId || item.pn === stock.pn) ?? null;
  const rfqs = tenantItems(context, source.rfqs).filter((rfq) => rfq.partNumber === stock.pn);
  const customerQuotes = tenantItems(context, source.quotes).filter((quote) => quote.partNumber === stock.pn);
  const supplierQuotes = tenantItems(context, source.supplierQuotes).filter((quote) => quote.partNumber === stock.pn);
  const orders = tenantItems(context, source.orders).filter((order) => (order.rfqId ? rfqs.some((rfq) => rfq.rfqId === order.rfqId) : false));
  const serviceHistory = tenantItems(context, source.serviceWorkflows).filter((workflow) => workflow.partNumber === stock.pn);
  const documents = documentsForStock(tenantItems(context, source.documents), stock);
  const lifecycle = tenantItems(context, source.auditEvents).filter((event) => event.entityId === stock.id || event.entityId === String(stock.legacyId));

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    stock,
    part,
    ownerCompany: companyByName(stock.ownerCompany),
    supplierCompany: companyByName(stock.supplierCompany),
    tagInfoCompany: companyByName(stock.tagInfoCompany),
    traceabilityCompany: companyByName(stock.traceabilityCompany),
    rfqs,
    supplierQuotes,
    customerQuotes,
    purchaseOrders: orders.filter((order) => order.kind === "purchase"),
    salesOrders: orders.filter((order) => order.kind === "sales"),
    serviceHistory,
    certificates: documents.filter((document) => document.documentType === "8130-3" || document.documentType === "EASA Form 1" || document.documentType === "CoC"),
    documents,
    lifecycle,
    margin: marginForQuotes(customerQuotes),
    quickActions: stockActions(context, stock)
  };
}

export function buildCompanyInventoryReadModel(context: RequestContext, source: PartStockSource): CompanyInventoryReadModel {
  const companies = tenantItems(context, source.companies);
  const stock = [...tenantItems(context, source.internalStock), ...tenantItems(context, source.externalStock)];
  const documents = tenantItems(context, source.documents);
  const rfqs = tenantItems(context, source.rfqs);
  const rows: CompanyInventoryRow[] = companies
    .map((company) => {
      const stockLines = stockLinkedToCompany(stock, company);
      const partNumbers = new Set(stockLines.map((item) => item.pn));
      const rowDocuments = stockLines.flatMap((item) => documentsForStock(documents, item));
      const linkedRfqs = rfqs.filter((rfq) => partNumbers.has(rfq.partNumber));
      return {
        tenantId: context.tenant.tenantId,
        companyId: company.id,
        companyName: company.name,
        companyType: company.type,
        internalUnits: stockLines.filter((item) => item.source === "internal").reduce((total, item) => total + item.qty, 0),
        externalUnits: stockLines.filter((item) => item.source === "external").reduce((total, item) => total + item.qty, 0),
        zeroQtyRows: stockLines.filter((item) => item.qty === 0).length,
        stockValue: stockLines.reduce((total, item) => total + stockValue(item), 0),
        currency: stockLines.find((item) => item.currency)?.currency ?? "USD",
        stockLines,
        documents: rowDocuments,
        linkedRfqs
      };
    })
    .filter((row) => row.stockLines.length > 0 || row.documents.length > 0 || row.linkedRfqs.length > 0)
    .sort((left, right) => right.stockValue - left.stockValue);

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    rows,
    totals: {
      internalUnits: stock.filter((item) => item.source === "internal").reduce((total, item) => total + item.qty, 0),
      externalUnits: stock.filter((item) => item.source === "external").reduce((total, item) => total + item.qty, 0),
      stockValue: stock.reduce((total, item) => total + stockValue(item), 0),
      zeroQtyRows: stock.filter((item) => item.qty === 0).length,
      currency: rows.find((row) => row.currency)?.currency ?? "USD"
    },
    quickActions: inventoryActions(context)
  };
}
