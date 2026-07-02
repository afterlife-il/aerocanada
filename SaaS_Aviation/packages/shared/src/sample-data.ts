import type {
  AccountingAlert,
  AuditEvent,
  AuthUserRecord,
  Company,
  Contact,
  DocumentAlert,
  Kpi,
  OrderSummary,
  PartNumber,
  QuoteSummary,
  RfqSummary,
  ServiceWorkflowSummary,
  StockItem,
  SupplierQuoteSummary,
  Tenant
} from "./types.js";

export const sampleTenant: Tenant = {
  id: "tenant-aci",
  name: "AEROCANADA INDUSTRIES 770 INC.",
  code: "ACI770",
  verifiedDomains: ["aerocanada-industries.com"],
  status: "active",
  primaryCompanyId: "company-5263"
};

export const sampleUsers: AuthUserRecord[] = [
  {
    id: "user-aci-admin",
    tenantId: sampleTenant.id,
    email: "ops@aerocanada-industries.com",
    name: "AeroCanada Admin",
    status: "active",
    roles: ["owner_admin", "tenant_admin", "inventory_manager", "sales_manager"],
    permissions: [
      "tenant.read",
      "tenant.manage",
      "user.read",
      "user.manage",
      "company.read",
      "part.read",
      "stock.read",
      "rfq.read",
      "audit.read",
      "auth.manage"
    ],
    mfaEnabled: false,
    authProviders: ["password"],
    createdAt: "2026-06-30T00:00:00Z",
    passwordHash: "sha256:a7bf62c36e02ac549dbc324498a300142489c9258f826fc7c113858d73690553"
  }
];

export const sampleTenants: Tenant[] = [sampleTenant];
export const sampleAdminUser = sampleUsers[0] as AuthUserRecord;

export const sampleRequestContext = {
  tenant: {
    tenantId: sampleTenant.id,
    tenantCode: sampleTenant.code,
    tenantName: sampleTenant.name,
    userId: sampleAdminUser.id,
    roles: sampleAdminUser.roles,
    permissions: sampleAdminUser.permissions
  }
};

export const sampleCompanies: Company[] = [
  {
    id: "company-5263",
    legacyId: 5263,
    tenantId: sampleTenant.id,
    name: "AeroCanada Industries 770",
    type: "owner",
    country: "France",
    city: "Paris",
    primaryEmail: "ops@aerocanada-industries.com",
    tags: ["ACI770", "Internal owner"],
    riskLevel: "normal",
    lastActivityAt: "2026-06-24"
  },
  {
    id: "company-1527",
    legacyId: 1527,
    tenantId: sampleTenant.id,
    name: "Better Aviation Products",
    type: "supplier",
    country: "United States",
    city: "Miami",
    tags: ["Tag Info", "Supplier"],
    riskLevel: "normal",
    lastActivityAt: "2026-06-20"
  },
  {
    id: "company-4188",
    legacyId: 4188,
    tenantId: sampleTenant.id,
    name: "Regional Airline MRO",
    type: "repair-vendor",
    country: "Germany",
    city: "Hamburg",
    tags: ["Repair", "EASA"],
    riskLevel: "watch",
    lastActivityAt: "2026-06-12"
  }
];

export const sampleContacts: Contact[] = [
  {
    id: "contact-1",
    legacyId: 101,
    tenantId: sampleTenant.id,
    companyId: "company-1527",
    name: "Maria Alvarez",
    title: "Sales Manager",
    email: "maria@example.test",
    division: "Sales"
  },
  {
    id: "contact-2",
    legacyId: 102,
    tenantId: sampleTenant.id,
    companyId: "company-4188",
    name: "Thomas Weber",
    title: "Repair Coordinator",
    email: "thomas@example.test",
    division: "Repair"
  }
];

export const sampleParts: PartNumber[] = [
  {
    id: "part-1",
    legacyId: 1,
    tenantId: sampleTenant.id,
    pn: "03-1802-2001",
    description: "LIGHT",
    ata: "33",
    ipc: "33-40-01",
    aircraft: ["A320", "A321"],
    manufacturer: "Honeywell",
    alternates: ["03-1802-2001-ALT"]
  },
  {
    id: "part-2",
    legacyId: 2,
    tenantId: sampleTenant.id,
    pn: "8260-124",
    description: "CONTROL UNIT",
    ata: "22",
    ipc: "22-10-00",
    aircraft: ["B737"],
    manufacturer: "Collins",
    alternates: ["8260-124R", "8260-124-01"]
  },
  {
    id: "part-3",
    legacyId: 3,
    tenantId: sampleTenant.id,
    pn: "DMC-45-12",
    description: "DUCT MOUNT CLAMP",
    ata: "36",
    aircraft: ["ATR72"],
    manufacturer: "Safran",
    alternates: []
  }
];

export const sampleInternalStock: StockItem[] = [
  {
    id: "stock-1",
    legacyId: 1,
    tenantId: sampleTenant.id,
    source: "internal",
    pn: "03-1802-2001",
    partId: "part-1",
    description: "LIGHT",
    serialNumber: "SNT140034",
    qty: 1,
    condition: "SV",
    release: "FAA 8130-3",
    status: "available",
    location: "Paris (Physical Stk)",
    ownerCompany: "AeroCanada Industries 770",
    supplierCompany: "Better Aviation Products",
    tagInfoCompany: "Better Aviation Products",
    traceabilityCompany: "Better Aviation Products",
    price: 2200,
    currency: "USD",
    entryDate: "2018-05-29",
    remarks: "Legacy imported ACI stock row"
  },
  {
    id: "stock-2",
    legacyId: 2,
    tenantId: sampleTenant.id,
    source: "internal",
    pn: "8260-124",
    partId: "part-2",
    description: "CONTROL UNIT",
    qty: 0,
    condition: "AR",
    release: "EASA Form 1",
    status: "exchange",
    location: "Out Exchange",
    ownerCompany: "AeroCanada Industries 770",
    tagInfoCompany: "Regional Airline MRO",
    entryDate: "2024-11-10",
    remarks: "Qty 0 preserved. Awaiting exchange return."
  }
];

export const sampleExternalStock: StockItem[] = [
  {
    id: "external-48499",
    legacyId: 48499,
    tenantId: sampleTenant.id,
    source: "external",
    pn: "DMC-45-12",
    partId: "part-3",
    description: "DUCT MOUNT CLAMP",
    qty: 12,
    condition: "NE",
    release: "CoC",
    status: "available",
    location: "Supplier stock",
    ownerCompany: "Better Aviation Products",
    supplierCompany: "Better Aviation Products",
    tagInfoCompany: "Better Aviation Products",
    price: 85,
    currency: "USD",
    entryDate: "2026-06-18"
  }
];

export const sampleRfqs: RfqSummary[] = [
  {
    id: "rfq-row-1",
    tenantId: sampleTenant.id,
    rfqId: "RFQ-2026-1044",
    customerName: "Northern Charter",
    partNumber: "03-1802-2001",
    qty: 1,
    status: "quoted",
    priority: "aog",
    createdAt: "2026-06-27"
  },
  {
    id: "rfq-row-2",
    tenantId: sampleTenant.id,
    rfqId: "RFQ-2026-1051",
    customerName: "Regional Airline MRO",
    partNumber: "8260-124",
    qty: 2,
    status: "open",
    priority: "critical",
    createdAt: "2026-06-28"
  },
  {
    id: "rfq-row-3",
    tenantId: sampleTenant.id,
    rfqId: "RFQ-2026-1057",
    customerName: "Atlantic Spares Desk",
    partNumber: "DMC-45-12",
    qty: 12,
    status: "open",
    priority: "normal",
    createdAt: "2026-06-30"
  }
];

export const sampleQuotes: QuoteSummary[] = [
  {
    id: "quote-64639",
    tenantId: sampleTenant.id,
    quoteNumber: "Q-64639",
    rfqId: "RFQ-2026-1044",
    customerName: "Northern Charter",
    partNumber: "03-1802-2001",
    status: "pending-customer",
    value: 3150,
    cost: 2200,
    currency: "USD",
    marginPct: 30.2,
    dueAt: "2026-07-02"
  },
  {
    id: "quote-64642",
    tenantId: sampleTenant.id,
    quoteNumber: "Q-64642",
    rfqId: "RFQ-2026-1051",
    customerName: "Regional Airline MRO",
    partNumber: "8260-124",
    status: "draft",
    value: 18600,
    cost: 13250,
    currency: "USD",
    marginPct: 28.8,
    dueAt: "2026-07-01"
  },
  {
    id: "quote-64645",
    tenantId: sampleTenant.id,
    quoteNumber: "Q-64645",
    rfqId: "RFQ-2026-1057",
    customerName: "Atlantic Spares Desk",
    partNumber: "DMC-45-12",
    status: "sent",
    value: 2140,
    cost: 1020,
    currency: "USD",
    marginPct: 52.3,
    dueAt: "2026-07-03"
  }
];

export const sampleSupplierQuotes: SupplierQuoteSummary[] = [
  {
    id: "sq-7901",
    tenantId: sampleTenant.id,
    rfqId: "RFQ-2026-1051",
    supplierName: "Better Aviation Products",
    partNumber: "8260-124",
    qty: 2,
    status: "pending",
    dueAt: "2026-07-01"
  },
  {
    id: "sq-7902",
    tenantId: sampleTenant.id,
    rfqId: "RFQ-2026-1057",
    supplierName: "Regional Airline MRO",
    partNumber: "DMC-45-12",
    qty: 12,
    status: "requested",
    dueAt: "2026-07-02"
  }
];

export const sampleOrders: OrderSummary[] = [
  {
    id: "po-31008",
    tenantId: sampleTenant.id,
    orderNumber: "PO-31008",
    kind: "purchase",
    companyName: "Better Aviation Products",
    rfqId: "RFQ-2026-1051",
    status: "open",
    value: 13250,
    currency: "USD",
    dueAt: "2026-07-05"
  },
  {
    id: "po-31009",
    tenantId: sampleTenant.id,
    orderNumber: "PO-31009",
    kind: "purchase",
    companyName: "Regional Airline MRO",
    status: "partially-received",
    value: 7400,
    currency: "USD",
    dueAt: "2026-07-08"
  },
  {
    id: "so-42017",
    tenantId: sampleTenant.id,
    orderNumber: "SO-42017",
    kind: "sales",
    companyName: "Northern Charter",
    rfqId: "RFQ-2026-1044",
    status: "ready-to-ship",
    value: 3150,
    currency: "USD",
    dueAt: "2026-07-02"
  },
  {
    id: "so-42018",
    tenantId: sampleTenant.id,
    orderNumber: "SO-42018",
    kind: "sales",
    companyName: "Atlantic Spares Desk",
    rfqId: "RFQ-2026-1057",
    status: "invoicing",
    value: 2140,
    currency: "USD",
    dueAt: "2026-07-04"
  }
];

export const sampleServiceWorkflows: ServiceWorkflowSummary[] = [
  {
    id: "svc-ex-8260",
    tenantId: sampleTenant.id,
    kind: "exchange",
    reference: "EX-8260-124-24",
    companyName: "Regional Airline MRO",
    partNumber: "8260-124",
    status: "due-back",
    dueAt: "2026-07-12"
  },
  {
    id: "svc-repair-light",
    tenantId: sampleTenant.id,
    kind: "repair",
    reference: "REP-03-1802",
    companyName: "Regional Airline MRO",
    partNumber: "03-1802-2001",
    status: "vendor-pending",
    dueAt: "2026-07-18"
  },
  {
    id: "svc-lease-dmc",
    tenantId: sampleTenant.id,
    kind: "lease",
    reference: "LS-DMC-45",
    companyName: "Atlantic Spares Desk",
    partNumber: "DMC-45-12",
    status: "customer-pending",
    dueAt: "2026-07-09"
  }
];

export const sampleDocumentAlerts: DocumentAlert[] = [
  {
    id: "doc-8130-stock-1",
    tenantId: sampleTenant.id,
    documentType: "8130-3",
    entityType: "stock",
    entityId: "stock-1",
    status: "pending-review",
    dueAt: "2026-07-01"
  },
  {
    id: "doc-trace-po-31008",
    tenantId: sampleTenant.id,
    documentType: "Trace",
    entityType: "purchase-order",
    entityId: "PO-31008",
    status: "missing",
    dueAt: "2026-07-03"
  },
  {
    id: "doc-invoice-so-42018",
    tenantId: sampleTenant.id,
    documentType: "Invoice",
    entityType: "sales-order",
    entityId: "SO-42018",
    status: "pending-review",
    dueAt: "2026-07-04"
  }
];

export const sampleAccountingAlerts: AccountingAlert[] = [
  {
    id: "acct-ar-northern",
    tenantId: sampleTenant.id,
    title: "Invoice hold before shipment",
    companyName: "Northern Charter",
    amount: 3150,
    currency: "USD",
    severity: "warning",
    dueAt: "2026-07-02"
  },
  {
    id: "acct-ap-bap",
    tenantId: sampleTenant.id,
    title: "Supplier prepayment approval",
    companyName: "Better Aviation Products",
    amount: 13250,
    currency: "USD",
    severity: "critical",
    dueAt: "2026-07-01"
  }
];

export const sampleAuditEvents: AuditEvent[] = [
  {
    id: "audit-1",
    tenantId: sampleTenant.id,
    actor: "system.mock",
    action: "login.success",
    entityType: "user",
    entityId: "mock-user",
    occurredAt: "2026-06-29T14:00:00Z",
    summary: "Foundation login event for the seeded admin session"
  },
  {
    id: "audit-2",
    tenantId: sampleTenant.id,
    actor: "system.mock",
    action: "stock.view",
    entityType: "stock",
    entityId: "stock-1",
    rfqId: "RFQ-2026-1044",
    occurredAt: "2026-06-29T14:10:00Z",
    summary: "Stock 360 viewed with RFQ context"
  },
  {
    id: "audit-3",
    tenantId: sampleTenant.id,
    actor: "system.mock",
    action: "stock.lifecycle.exchange_due",
    entityType: "stock",
    entityId: "stock-2",
    rfqId: "RFQ-2026-1051",
    occurredAt: "2026-06-30T09:15:00Z",
    summary: "Qty 0 exchange stock remains visible while awaiting return"
  }
];

export const sampleKpis: Kpi[] = [
  { label: "Open RFQs", value: "42", trend: "+8 today", tone: "warning" },
  { label: "Quoted value", value: "$1.28M", trend: "+14% MoM", tone: "good" },
  { label: "AOG queue", value: "6", trend: "2 critical", tone: "critical" },
  { label: "Repair due back", value: "11", trend: "next 14 days", tone: "neutral" },
  { label: "Stock exceptions", value: "9", trend: "owner/tag review", tone: "warning" }
];
