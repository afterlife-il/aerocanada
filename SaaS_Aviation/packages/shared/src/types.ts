export type TenantId = string;
export type LegacyId = string | number;

export type AuthProviderKind = "password" | "google" | "linkedin" | "microsoft" | "apple";

export type UserStatus = "active" | "invited" | "disabled";

export type Role = "owner_admin" | "tenant_admin" | "inventory_manager" | "sales_manager" | "read_only";

export type Permission =
  | "tenant.read"
  | "tenant.manage"
  | "user.read"
  | "user.manage"
  | "company.read"
  | "part.read"
  | "stock.read"
  | "document.read"
  | "document.upload"
  | "rfq.read"
  | "audit.read"
  | "auth.manage";

export type EntityStatus =
  | "available"
  | "reserved"
  | "sold"
  | "purchase-order"
  | "work-order"
  | "consignment"
  | "quarantine"
  | "repair"
  | "exchange"
  | "unknown";

export interface Tenant {
  id: TenantId;
  name: string;
  code: string;
  verifiedDomains: string[];
  status: "active" | "suspended";
  primaryCompanyId: string;
}

export interface User {
  id: string;
  tenantId: TenantId;
  email: string;
  name: string;
  status: UserStatus;
  roles: Role[];
  permissions: Permission[];
  mfaEnabled: boolean;
  authProviders: AuthProviderKind[];
  createdAt: string;
}

export interface AuthUserRecord extends User {
  passwordHash?: string;
}

export interface TenantContext {
  tenantId: TenantId;
  tenantCode: string;
  tenantName: string;
  userId: string;
  roles: Role[];
  permissions: Permission[];
}

export interface RequestContext {
  tenant: TenantContext;
}

export interface AuthSession {
  token: string;
  user: User;
  tenant: Tenant;
  expiresAt: string;
}

export interface Company {
  id: string;
  legacyId: LegacyId;
  tenantId: TenantId;
  name: string;
  type: "customer" | "supplier" | "owner" | "repair-vendor" | "mixed";
  country?: string;
  city?: string;
  website?: string;
  primaryEmail?: string;
  tags: string[];
  riskLevel: "normal" | "watch" | "blocked";
  lastActivityAt?: string;
}

export interface Contact {
  id: string;
  legacyId: LegacyId;
  tenantId: TenantId;
  companyId: string;
  name: string;
  title?: string;
  email?: string;
  phone?: string;
  division?: string;
}

export interface PartNumber {
  id: string;
  legacyId: LegacyId;
  tenantId: TenantId;
  pn: string;
  description: string;
  ata?: string;
  ipc?: string;
  aircraft?: string[];
  manufacturer?: string;
  alternates: string[];
  supersededBy?: string;
}

export interface StockItem {
  id: string;
  legacyId: LegacyId;
  tenantId: TenantId;
  source: "internal" | "external";
  pn: string;
  partId: string;
  description: string;
  serialNumber?: string;
  qty: number;
  condition?: string;
  release?: string;
  status: EntityStatus;
  location?: string;
  ownerCompany?: string;
  supplierCompany?: string;
  tagInfoCompany?: string;
  traceabilityCompany?: string;
  price?: number;
  currency?: string;
  entryDate?: string;
  remarks?: string;
}

export interface RfqSummary {
  id: string;
  tenantId: TenantId;
  rfqId: string;
  customerName: string;
  partNumber: string;
  qty: number;
  status: "open" | "quoted" | "accepted" | "closed";
  priority: "normal" | "aog" | "critical";
  createdAt: string;
}

export interface QuoteSummary {
  id: string;
  tenantId: TenantId;
  quoteNumber: string;
  rfqId: string;
  customerName: string;
  partNumber: string;
  status: "draft" | "pending-customer" | "sent" | "accepted" | "expired";
  value: number;
  cost: number;
  currency: string;
  marginPct: number;
  dueAt: string;
}

export interface SupplierQuoteSummary {
  id: string;
  tenantId: TenantId;
  rfqId: string;
  supplierName: string;
  partNumber: string;
  qty: number;
  status: "requested" | "pending" | "received" | "declined";
  dueAt: string;
}

export interface OrderSummary {
  id: string;
  tenantId: TenantId;
  orderNumber: string;
  kind: "purchase" | "sales";
  companyName: string;
  rfqId?: string;
  status: "draft" | "open" | "partially-received" | "ready-to-ship" | "invoicing" | "closed";
  value: number;
  currency: string;
  dueAt: string;
}

export interface ServiceWorkflowSummary {
  id: string;
  tenantId: TenantId;
  kind: "repair" | "exchange" | "lease";
  reference: string;
  companyName: string;
  partNumber: string;
  status: "open" | "vendor-pending" | "customer-pending" | "due-back" | "closed";
  dueAt: string;
}

export interface DocumentAlert {
  id: string;
  tenantId: TenantId;
  documentType: "8130-3" | "EASA Form 1" | "CoC" | "Trace" | "Invoice" | "Packing slip";
  entityType: "stock" | "quote" | "purchase-order" | "sales-order";
  entityId: string;
  status: "missing" | "pending-review" | "expires-soon";
  dueAt: string;
}

export type DocumentOwnerModule =
  | "company"
  | "contact"
  | "part"
  | "stock"
  | "rfq"
  | "supplier-quote"
  | "customer-quote"
  | "purchase-order"
  | "sales-order"
  | "invoice"
  | "repair-exchange-lease";

export type DocumentType =
  | "Certificate"
  | "Trace"
  | "Invoice"
  | "Quote"
  | "PO"
  | "SO"
  | "Packing slip"
  | "Airway bill"
  | "Email attachment"
  | "Contract"
  | "Photo"
  | "Other";

export type DocumentStatus = "active" | "pending-review" | "scan-required" | "quarantined" | "archived";
export type DocumentVisibility = "internal" | "customer-shareable" | "restricted";
export type UploadIntentStatus = "validated" | "rejected";

export interface DocumentRecord {
  id: string;
  tenantId: TenantId;
  ownerModule: DocumentOwnerModule;
  ownerRecordId: string;
  documentType: DocumentType;
  title: string;
  fileName: string;
  mimeType: string;
  sizeBytes: number;
  uploadedBy: string;
  uploadedAt: string;
  version: number;
  visibility: DocumentVisibility;
  status: DocumentStatus;
  notes?: string;
  currentVersionId: string;
  tags: string[];
}

export interface DocumentVersionRecord {
  id: string;
  tenantId: TenantId;
  documentId: string;
  version: number;
  fileName: string;
  mimeType: string;
  sizeBytes: number;
  checksumSha256?: string;
  uploadedBy: string;
  uploadedAt: string;
  scanStatus: "pending" | "clean" | "blocked";
  storageState: "metadata-only" | "quarantine" | "stored";
}

export interface DocumentLinkRecord {
  id: string;
  tenantId: TenantId;
  documentId: string;
  ownerModule: DocumentOwnerModule;
  ownerRecordId: string;
  relation: "primary" | "supporting" | "reference";
  linkedAt: string;
  linkedBy: string;
}

export interface DocumentReadModel extends DocumentRecord {
  currentVersion: DocumentVersionRecord | null;
  versions: DocumentVersionRecord[];
  links: DocumentLinkRecord[];
}

export interface DocumentCenterReadModel {
  tenantId: TenantId;
  tenantCode: string;
  documents: DocumentReadModel[];
  summary: {
    total: number;
    clean: number;
    needsReview: number;
    restricted: number;
    totalSizeBytes: number;
  };
}

export interface EntityDocumentReadModel {
  tenantId: TenantId;
  tenantCode: string;
  entityType: DocumentOwnerModule;
  entityId: string;
  documents: DocumentReadModel[];
}

export interface DocumentUploadRequest {
  ownerModule: DocumentOwnerModule;
  ownerRecordId: string;
  documentType: DocumentType;
  fileName: string;
  mimeType: string;
  sizeBytes: number;
  visibility: DocumentVisibility;
  notes?: string;
}

export interface DocumentUploadIntent {
  status: UploadIntentStatus;
  tenantId: TenantId;
  ownerModule: DocumentOwnerModule;
  ownerRecordId: string;
  documentType: DocumentType;
  fileName: string;
  mimeType: string;
  sizeBytes: number;
  uploadedBy: string;
  uploadedAt: string;
  visibility: DocumentVisibility;
  version: number;
  persistence: "metadata-only";
  securityChecks: string[];
  futureStorageOwner: string;
}

export interface DocumentUploadValidationResult {
  accepted: boolean;
  errors: string[];
  intent: DocumentUploadIntent | null;
}

export interface AccountingAlert {
  id: string;
  tenantId: TenantId;
  title: string;
  companyName: string;
  amount: number;
  currency: string;
  severity: "info" | "warning" | "critical";
  dueAt: string;
}

export interface CompanyInventorySummary {
  companyId: string;
  companyName: string;
  tenantId: TenantId;
  internalUnits: number;
  externalUnits: number;
  stockValue: number;
  currency: string;
  watchItems: number;
}

export interface DashboardAction {
  label: string;
  href: string;
  priority: "primary" | "secondary";
}

export interface DashboardMetric {
  label: string;
  value: string;
  detail: string;
  tone: "neutral" | "good" | "warning" | "critical";
}

export interface MarginKpi {
  label: string;
  value: string;
  detail: string;
  tone: "neutral" | "good" | "warning" | "critical";
}

export interface DashboardData {
  tenantId: TenantId;
  tenantCode: string;
  tenantName: string;
  generatedAt: string;
  metrics: DashboardMetric[];
  marginKpis: MarginKpi[];
  rfqsOpen: RfqSummary[];
  quotesPending: QuoteSummary[];
  supplierQuotesPending: SupplierQuoteSummary[];
  purchaseOrders: OrderSummary[];
  salesOrders: OrderSummary[];
  stockValue: {
    totalValue: number;
    currency: string;
    internalUnits: number;
    externalUnits: number;
    zeroQtyVisible: number;
  };
  companyInventory: CompanyInventorySummary[];
  serviceWorkflows: ServiceWorkflowSummary[];
  documentsPending: DocumentAlert[];
  accountingAlerts: AccountingAlert[];
  recentActivity: AuditEvent[];
  quickActions: DashboardAction[];
}

export interface AuditEvent {
  id: string;
  tenantId: TenantId;
  actor: string;
  action: string;
  entityType: string;
  entityId: string;
  rfqId?: string;
  occurredAt: string;
  summary: string;
}

export interface Kpi {
  label: string;
  value: string;
  trend: string;
  tone: "neutral" | "good" | "warning" | "critical";
}

export interface WorkflowBoundaryAction {
  id: string;
  label: string;
  tenantId: TenantId;
  entityType: "part" | "stock" | "company-inventory";
  entityId: string;
  mode: "boundary";
  persistence: "none";
  requiredData: string[];
  contextChecks: string[];
  futureOwner: string;
  note: string;
}

export interface StockAvailabilitySummary {
  internalUnits: number;
  externalUnits: number;
  internalLines: number;
  externalLines: number;
  availableUnits: number;
  reservedUnits: number;
  zeroQtyRows: number;
  totalValue: number;
  currency: string;
}

export interface MarginSummary {
  quotedValue: number;
  quotedCost: number;
  grossMargin: number;
  marginPct: number;
  currency: string;
}

export interface Part360ReadModel {
  tenantId: TenantId;
  tenantCode: string;
  part: PartNumber;
  stockAvailability: StockAvailabilitySummary;
  internalStock: StockItem[];
  externalStock: StockItem[];
  rfqs: RfqSummary[];
  supplierQuotes: SupplierQuoteSummary[];
  customerQuotes: QuoteSummary[];
  purchaseHistory: OrderSummary[];
  salesHistory: OrderSummary[];
  serviceHistory: ServiceWorkflowSummary[];
  certificates: DocumentAlert[];
  documents: DocumentAlert[];
  traceability: AuditEvent[];
  margin: MarginSummary;
  quickActions: WorkflowBoundaryAction[];
}

export interface Stock360ReadModel {
  tenantId: TenantId;
  tenantCode: string;
  stock: StockItem;
  part: PartNumber | null;
  ownerCompany: Company | null;
  supplierCompany: Company | null;
  tagInfoCompany: Company | null;
  traceabilityCompany: Company | null;
  rfqs: RfqSummary[];
  supplierQuotes: SupplierQuoteSummary[];
  customerQuotes: QuoteSummary[];
  purchaseOrders: OrderSummary[];
  salesOrders: OrderSummary[];
  serviceHistory: ServiceWorkflowSummary[];
  certificates: DocumentAlert[];
  documents: DocumentAlert[];
  lifecycle: AuditEvent[];
  margin: MarginSummary;
  quickActions: WorkflowBoundaryAction[];
}

export interface CompanyInventoryRow {
  tenantId: TenantId;
  companyId: string;
  companyName: string;
  companyType: Company["type"];
  internalUnits: number;
  externalUnits: number;
  zeroQtyRows: number;
  stockValue: number;
  currency: string;
  stockLines: StockItem[];
  documents: DocumentAlert[];
  linkedRfqs: RfqSummary[];
}

export interface CompanyInventoryReadModel {
  tenantId: TenantId;
  tenantCode: string;
  rows: CompanyInventoryRow[];
  totals: {
    internalUnits: number;
    externalUnits: number;
    stockValue: number;
    zeroQtyRows: number;
    currency: string;
  };
  quickActions: WorkflowBoundaryAction[];
}
