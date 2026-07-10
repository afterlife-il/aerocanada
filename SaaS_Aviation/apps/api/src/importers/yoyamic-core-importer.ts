import { normalizePartNumber } from "@saas-aviation/shared";

export interface LegacyYoyamicSnapshot {
  companies: Array<{ id: string | number; name?: string | null; type?: string | null; updatedAt?: string | null }>;
  contacts: Array<{ id: string | number; companyId?: string | number | null; email?: string | null; name?: string | null }>;
  parts: Array<{ id: string | number; partNumber?: string | null; manufacturer?: string | null }>;
  stock: Array<{
    id: string | number;
    partId?: string | number | null;
    ownerCompanyId?: string | number | null;
    supplierCompanyId?: string | number | null;
    tagInfoCompanyId?: string | number | null;
    traceabilityCompanyId?: string | number | null;
    quantity?: number | null;
    serialNumber?: string | null;
    condition?: string | null;
  }>;
}

export interface ImportDryRunReport {
  mode: "dry-run";
  tenantId: string;
  inserted: number;
  updated: number;
  skipped: number;
  duplicate: number;
  failed: number;
  anomalies: string[];
  entities: {
    companies: number;
    contacts: number;
    parts: number;
    stock: number;
  };
}

function duplicateCount(values: string[]): number {
  const seen = new Set<string>();
  const duplicates = new Set<string>();
  for (const value of values) {
    if (seen.has(value)) duplicates.add(value);
    seen.add(value);
  }
  return duplicates.size;
}

export function dryRunYoyamicCoreImport(tenantId: string, snapshot: LegacyYoyamicSnapshot): ImportDryRunReport {
  const anomalies: string[] = [];
  const sourceCompanyIds = new Set(snapshot.companies.map((company) => String(company.id)));
  const sourcePartIds = new Set(snapshot.parts.map((part) => String(part.id)));
  const companyNames = snapshot.companies.map((company) => company.name?.trim().toLowerCase()).filter((value): value is string => Boolean(value));
  const contactEmails = snapshot.contacts.map((contact) => contact.email?.trim().toLowerCase()).filter((value): value is string => Boolean(value));
  const partNumbers = snapshot.parts
    .map((part) => (part.partNumber ? `${normalizePartNumber(part.partNumber)}:${part.manufacturer ?? ""}` : null))
    .filter((value): value is string => Boolean(value));

  const duplicateCompanies = duplicateCount(companyNames);
  const duplicateContacts = duplicateCount(contactEmails);
  const duplicateParts = duplicateCount(partNumbers);

  if (duplicateCompanies) anomalies.push(`duplicate_company_names=${duplicateCompanies}`);
  if (duplicateContacts) anomalies.push(`duplicate_contact_emails=${duplicateContacts}`);
  if (duplicateParts) anomalies.push(`duplicate_part_numbers=${duplicateParts}`);

  for (const contact of snapshot.contacts) {
    if (!contact.companyId || !sourceCompanyIds.has(String(contact.companyId))) {
      anomalies.push(`orphan_contact=${contact.id}`);
    }
  }

  for (const stock of snapshot.stock) {
    if (!stock.partId || !sourcePartIds.has(String(stock.partId))) {
      anomalies.push(`orphan_stock=${stock.id}`);
    }
    for (const [field, companyId] of [
      ["owner", stock.ownerCompanyId],
      ["supplier", stock.supplierCompanyId],
      ["tag_info", stock.tagInfoCompanyId],
      ["traceability", stock.traceabilityCompanyId]
    ]) {
      if (companyId && !sourceCompanyIds.has(String(companyId))) {
        anomalies.push(`unknown_${field}_company=${stock.id}:${companyId}`);
      }
    }
    if (stock.quantity === 0) anomalies.push(`quantity_zero_stock=${stock.id}`);
    if (stock.quantity == null || stock.quantity < 0) anomalies.push(`invalid_quantity=${stock.id}`);
    if (!stock.condition) anomalies.push(`missing_condition=${stock.id}`);
  }

  const failed = anomalies.filter((anomaly) => anomaly.startsWith("invalid_")).length;
  const duplicate = duplicateCompanies + duplicateContacts + duplicateParts;
  const skipped = anomalies.filter((anomaly) => anomaly.startsWith("orphan_") || anomaly.startsWith("unknown_")).length;
  const sourceRows = snapshot.companies.length + snapshot.contacts.length + snapshot.parts.length + snapshot.stock.length;

  return {
    mode: "dry-run",
    tenantId,
    inserted: Math.max(0, sourceRows - duplicate - skipped - failed),
    updated: 0,
    skipped,
    duplicate,
    failed,
    anomalies,
    entities: {
      companies: snapshot.companies.length,
      contacts: snapshot.contacts.length,
      parts: snapshot.parts.length,
      stock: snapshot.stock.length
    }
  };
}
