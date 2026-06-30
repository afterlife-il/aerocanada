export type TenantId = string;
export type LegacyId = string | number;

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
