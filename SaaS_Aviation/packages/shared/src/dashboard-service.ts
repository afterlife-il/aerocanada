import type {
  AccountingAlert,
  AuditEvent,
  Company,
  CompanyInventorySummary,
  DashboardAction,
  DashboardData,
  DocumentAlert,
  OrderSummary,
  QuoteSummary,
  RequestContext,
  RfqSummary,
  ServiceWorkflowSummary,
  StockItem,
  SupplierQuoteSummary
} from "./types.js";

export interface DashboardSource {
  companies: Company[];
  internalStock: StockItem[];
  externalStock: StockItem[];
  rfqs: RfqSummary[];
  quotes: QuoteSummary[];
  supplierQuotes: SupplierQuoteSummary[];
  orders: OrderSummary[];
  serviceWorkflows: ServiceWorkflowSummary[];
  documents: DocumentAlert[];
  accountingAlerts: AccountingAlert[];
  auditEvents: AuditEvent[];
}

function tenantItems<T extends { tenantId: string }>(context: RequestContext, items: T[]): T[] {
  return items.filter((item) => item.tenantId === context.tenant.tenantId);
}

function formatMoney(value: number, currency = "USD"): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency,
    maximumFractionDigits: 0
  }).format(value);
}

function sumMoney<T extends { value: number }>(items: T[]): number {
  return items.reduce((total, item) => total + item.value, 0);
}

function stockLineValue(item: StockItem): number {
  return (item.price ?? 0) * item.qty;
}

export function buildTenantDashboard(context: RequestContext, source: DashboardSource): DashboardData {
  const companies = tenantItems(context, source.companies);
  const internalStock = tenantItems(context, source.internalStock);
  const externalStock = tenantItems(context, source.externalStock);
  const rfqs = tenantItems(context, source.rfqs);
  const quotes = tenantItems(context, source.quotes);
  const supplierQuotes = tenantItems(context, source.supplierQuotes);
  const orders = tenantItems(context, source.orders);
  const serviceWorkflows = tenantItems(context, source.serviceWorkflows);
  const documents = tenantItems(context, source.documents);
  const accountingAlerts = tenantItems(context, source.accountingAlerts);
  const auditEvents = tenantItems(context, source.auditEvents);

  const rfqsOpen = rfqs.filter((rfq) => rfq.status === "open" || rfq.status === "quoted");
  const quotesPending = quotes.filter((quote) => quote.status === "draft" || quote.status === "pending-customer" || quote.status === "sent");
  const supplierQuotesPending = supplierQuotes.filter((quote) => quote.status === "requested" || quote.status === "pending");
  const purchaseOrders = orders.filter((order) => order.kind === "purchase" && order.status !== "closed");
  const salesOrders = orders.filter((order) => order.kind === "sales" && order.status !== "closed");
  const stock = [...internalStock, ...externalStock];
  const stockTotal = stock.reduce((total, item) => total + stockLineValue(item), 0);
  const stockCurrency = stock.find((item) => item.currency)?.currency ?? "USD";
  const openMargin = quotesPending.reduce(
    (total, quote) => {
      total.value += quote.value;
      total.cost += quote.cost;
      return total;
    },
    { value: 0, cost: 0 }
  );
  const marginPct = openMargin.value > 0 ? ((openMargin.value - openMargin.cost) / openMargin.value) * 100 : 0;
  const highRiskAccounting = accountingAlerts.filter((alert) => alert.severity !== "info");

  const companyInventory: CompanyInventorySummary[] = companies
    .map((company) => {
      const ownedInternal = internalStock.filter((item) => item.ownerCompany === company.name || item.supplierCompany === company.name);
      const ownedExternal = externalStock.filter((item) => item.ownerCompany === company.name || item.supplierCompany === company.name);
      const companyStock = [...ownedInternal, ...ownedExternal];

      return {
        companyId: company.id,
        companyName: company.name,
        tenantId: company.tenantId,
        internalUnits: ownedInternal.reduce((total, item) => total + item.qty, 0),
        externalUnits: ownedExternal.reduce((total, item) => total + item.qty, 0),
        stockValue: companyStock.reduce((total, item) => total + stockLineValue(item), 0),
        currency: companyStock.find((item) => item.currency)?.currency ?? stockCurrency,
        watchItems: companyStock.filter((item) => item.status === "repair" || item.status === "exchange" || item.status === "quarantine" || item.qty === 0).length
      };
    })
    .filter((summary) => summary.internalUnits > 0 || summary.externalUnits > 0 || summary.watchItems > 0);

  const quickActions: DashboardAction[] = [
    { label: "New RFQ", href: "/dashboard#rfq", priority: "primary" },
    { label: "Create quote", href: "/dashboard#quotes", priority: "primary" },
    { label: "Check supplier quotes", href: "/dashboard#supplier-quotes", priority: "secondary" },
    { label: "Open ACI stock", href: "/stock/internal", priority: "secondary" },
    { label: "Company inventory", href: "/companies/demo-co-5263", priority: "secondary" },
    { label: "Document review", href: "/dashboard#documents", priority: "secondary" }
  ];

  return {
    tenantId: context.tenant.tenantId,
    tenantCode: context.tenant.tenantCode,
    tenantName: context.tenant.tenantName,
    generatedAt: "2026-07-01T00:00:00Z",
    metrics: [
      { label: "RFQs open", value: String(rfqsOpen.length), detail: `${rfqsOpen.filter((rfq) => rfq.priority !== "normal").length} AOG / critical`, tone: "warning" },
      { label: "Quotes pending", value: String(quotesPending.length), detail: formatMoney(sumMoney(quotesPending), stockCurrency), tone: "good" },
      { label: "Supplier quotes pending", value: String(supplierQuotesPending.length), detail: "vendor follow-ups", tone: "warning" },
      { label: "Purchase orders", value: String(purchaseOrders.length), detail: formatMoney(sumMoney(purchaseOrders), stockCurrency), tone: "neutral" },
      { label: "Sales orders", value: String(salesOrders.length), detail: formatMoney(sumMoney(salesOrders), stockCurrency), tone: "good" },
      { label: "Stock value", value: formatMoney(stockTotal, stockCurrency), detail: `${internalStock.length} internal / ${externalStock.length} external lines`, tone: "neutral" },
      { label: "Documents pending", value: String(documents.length), detail: `${documents.filter((doc) => doc.status === "missing").length} missing`, tone: documents.length > 0 ? "warning" : "good" },
      { label: "Accounting alerts", value: String(accountingAlerts.length), detail: `${highRiskAccounting.length} need action`, tone: highRiskAccounting.length > 0 ? "critical" : "neutral" }
    ],
    marginKpis: [
      { label: "Open quote margin", value: `${marginPct.toFixed(1)}%`, detail: `${formatMoney(openMargin.value - openMargin.cost, stockCurrency)} gross`, tone: marginPct >= 28 ? "good" : "warning" },
      { label: "Quote pipeline", value: formatMoney(sumMoney(quotesPending), stockCurrency), detail: `${quotesPending.length} active customer quotes`, tone: "neutral" },
      { label: "Order book", value: formatMoney(sumMoney(salesOrders), stockCurrency), detail: `${salesOrders.length} sales orders`, tone: "good" }
    ],
    rfqsOpen,
    quotesPending,
    supplierQuotesPending,
    purchaseOrders,
    salesOrders,
    stockValue: {
      totalValue: stockTotal,
      currency: stockCurrency,
      internalUnits: internalStock.reduce((total, item) => total + item.qty, 0),
      externalUnits: externalStock.reduce((total, item) => total + item.qty, 0),
      zeroQtyVisible: stock.filter((item) => item.qty === 0).length
    },
    companyInventory,
    serviceWorkflows,
    documentsPending: documents,
    accountingAlerts,
    recentActivity: auditEvents.sort((left, right) => right.occurredAt.localeCompare(left.occurredAt)).slice(0, 8),
    quickActions
  };
}
