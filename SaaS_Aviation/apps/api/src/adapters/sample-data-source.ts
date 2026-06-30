import {
  sampleAuditEvents,
  sampleCompanies,
  sampleExternalStock,
  sampleInternalStock,
  sampleParts,
  sampleRfqs
} from "@saas-aviation/shared";
import type { AuditEvent, AviationErpDataSource, Company, PartNumber, RfqSummary, StockItem } from "@saas-aviation/shared";

export class SampleDataSource implements AviationErpDataSource {
  async listCompanies(): Promise<Company[]> {
    return sampleCompanies;
  }

  async getCompany(id: string): Promise<Company | null> {
    return sampleCompanies.find((company) => company.id === id || String(company.legacyId) === id) ?? null;
  }

  async listParts(): Promise<PartNumber[]> {
    return sampleParts;
  }

  async getPart(id: string): Promise<PartNumber | null> {
    return sampleParts.find((part) => part.id === id || part.pn === id || String(part.legacyId) === id) ?? null;
  }

  async listInternalStock(): Promise<StockItem[]> {
    return sampleInternalStock;
  }

  async listExternalStock(): Promise<StockItem[]> {
    return sampleExternalStock;
  }

  async getStockItem(id: string): Promise<StockItem | null> {
    return [...sampleInternalStock, ...sampleExternalStock].find((item) => item.id === id || String(item.legacyId) === id) ?? null;
  }

  async listRfqSummaries(): Promise<RfqSummary[]> {
    return sampleRfqs;
  }

  async listAuditEvents(): Promise<AuditEvent[]> {
    return sampleAuditEvents;
  }

  async recordAuditEvent(event: Omit<AuditEvent, "id" | "occurredAt">): Promise<AuditEvent> {
    return {
      ...event,
      id: `audit-${sampleAuditEvents.length + 1}`,
      occurredAt: new Date().toISOString()
    };
  }
}
