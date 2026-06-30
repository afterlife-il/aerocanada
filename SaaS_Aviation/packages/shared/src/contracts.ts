import type { AuditEvent, Company, PartNumber, RfqSummary, StockItem } from "./types.js";

export interface CompanyRepository {
  listCompanies(): Promise<Company[]>;
  getCompany(id: string): Promise<Company | null>;
}

export interface PartRepository {
  listParts(): Promise<PartNumber[]>;
  getPart(id: string): Promise<PartNumber | null>;
}

export interface StockRepository {
  listInternalStock(): Promise<StockItem[]>;
  listExternalStock(): Promise<StockItem[]>;
  getStockItem(id: string): Promise<StockItem | null>;
}

export interface RfqRepository {
  listRfqSummaries(): Promise<RfqSummary[]>;
}

export interface AuditRepository {
  listAuditEvents(): Promise<AuditEvent[]>;
  recordAuditEvent(event: Omit<AuditEvent, "id" | "occurredAt">): Promise<AuditEvent>;
}

export interface AviationErpDataSource
  extends CompanyRepository,
    PartRepository,
    StockRepository,
    RfqRepository,
    AuditRepository {}
