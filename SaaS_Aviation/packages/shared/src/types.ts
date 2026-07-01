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
