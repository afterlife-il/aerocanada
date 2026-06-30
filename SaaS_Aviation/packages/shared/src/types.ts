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
