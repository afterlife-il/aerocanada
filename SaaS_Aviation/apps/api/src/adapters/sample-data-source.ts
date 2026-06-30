import {
  sampleAuditEvents,
  sampleCompanies,
  sampleExternalStock,
  sampleInternalStock,
  sampleParts,
  sampleRfqs,
  sampleTenants,
  sampleUsers
} from "@saas-aviation/shared";
import type { AuditEvent, AviationErpDataSource, Company, PartNumber, RequestContext, RfqSummary, StockItem, Tenant, User } from "@saas-aviation/shared";

function matchesTenant<T extends { tenantId: string }>(context: RequestContext, item: T): boolean {
  return item.tenantId === context.tenant.tenantId;
}

export class SampleDataSource implements AviationErpDataSource {
  async listCompanies(context: RequestContext): Promise<Company[]> {
    return sampleCompanies.filter((company) => matchesTenant(context, company));
  }

  async getCompany(context: RequestContext, id: string): Promise<Company | null> {
    return sampleCompanies.find((company) => matchesTenant(context, company) && (company.id === id || String(company.legacyId) === id)) ?? null;
  }

  async listParts(context: RequestContext): Promise<PartNumber[]> {
    return sampleParts.filter((part) => matchesTenant(context, part));
  }

  async getPart(context: RequestContext, id: string): Promise<PartNumber | null> {
    return sampleParts.find((part) => matchesTenant(context, part) && (part.id === id || part.pn === id || String(part.legacyId) === id)) ?? null;
  }

  async listInternalStock(context: RequestContext): Promise<StockItem[]> {
    return sampleInternalStock.filter((stock) => matchesTenant(context, stock));
  }

  async listExternalStock(context: RequestContext): Promise<StockItem[]> {
    return sampleExternalStock.filter((stock) => matchesTenant(context, stock));
  }

  async getStockItem(context: RequestContext, id: string): Promise<StockItem | null> {
    return [...sampleInternalStock, ...sampleExternalStock].find((item) => matchesTenant(context, item) && (item.id === id || String(item.legacyId) === id)) ?? null;
  }

  async listRfqSummaries(context: RequestContext): Promise<RfqSummary[]> {
    return sampleRfqs.filter((rfq) => matchesTenant(context, rfq));
  }

  async listAuditEvents(context: RequestContext): Promise<AuditEvent[]> {
    return sampleAuditEvents.filter((event) => matchesTenant(context, event));
  }

  async recordAuditEvent(event: Omit<AuditEvent, "id" | "occurredAt">): Promise<AuditEvent> {
    return {
      ...event,
      id: `audit-${sampleAuditEvents.length + 1}`,
      occurredAt: new Date().toISOString()
    };
  }

  async getTenant(id: string): Promise<Tenant | null> {
    return sampleTenants.find((tenant) => tenant.id === id || tenant.code === id) ?? null;
  }

  async getUserByEmail(email: string): Promise<User | null> {
    const normalizedEmail = email.trim().toLowerCase();
    return sampleUsers.find((user) => user.email.toLowerCase() === normalizedEmail) ?? null;
  }
}
