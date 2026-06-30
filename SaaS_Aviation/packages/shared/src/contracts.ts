import type { AuditEvent, AuthSession, Company, PartNumber, RequestContext, RfqSummary, StockItem, Tenant, User } from "./types.js";

export interface CompanyRepository {
  listCompanies(context: RequestContext): Promise<Company[]>;
  getCompany(context: RequestContext, id: string): Promise<Company | null>;
}

export interface PartRepository {
  listParts(context: RequestContext): Promise<PartNumber[]>;
  getPart(context: RequestContext, id: string): Promise<PartNumber | null>;
}

export interface StockRepository {
  listInternalStock(context: RequestContext): Promise<StockItem[]>;
  listExternalStock(context: RequestContext): Promise<StockItem[]>;
  getStockItem(context: RequestContext, id: string): Promise<StockItem | null>;
}

export interface RfqRepository {
  listRfqSummaries(context: RequestContext): Promise<RfqSummary[]>;
}

export interface AuditRepository {
  listAuditEvents(context: RequestContext): Promise<AuditEvent[]>;
  recordAuditEvent(event: Omit<AuditEvent, "id" | "occurredAt">): Promise<AuditEvent>;
}

export interface TenantRepository {
  getTenant(id: string): Promise<Tenant | null>;
}

export interface UserRepository {
  getUserByEmail(email: string): Promise<User | null>;
}

export interface AuthRepository {
  authenticateWithPassword(email: string, password: string): Promise<AuthSession | null>;
  getSession(token: string): Promise<AuthSession | null>;
  revokeSession(token: string): Promise<void>;
}

export interface AviationErpDataSource
  extends CompanyRepository,
    PartRepository,
    StockRepository,
    RfqRepository,
    AuditRepository,
    TenantRepository,
    UserRepository {}
