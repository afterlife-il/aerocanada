import {
  sampleAuditEvents,
  sampleCompanies,
  sampleContacts,
  sampleExternalStock,
  sampleInternalStock,
  sampleKpis,
  sampleParts,
  sampleRfqs,
  sampleTenant
} from "@saas-aviation/shared";

export const data = {
  tenant: sampleTenant,
  companies: sampleCompanies,
  contacts: sampleContacts,
  parts: sampleParts,
  internalStock: sampleInternalStock,
  externalStock: sampleExternalStock,
  rfqs: sampleRfqs,
  audit: sampleAuditEvents,
  kpis: sampleKpis
};

function firstOrThrow<T>(items: T[], label: string): T {
  const first = items[0];
  if (!first) {
    throw new Error(`Sample data is missing required ${label}`);
  }
  return first;
}

export function getCompany(id: string) {
  return data.companies.find((company) => company.id === id || String(company.legacyId) === id) ?? firstOrThrow(data.companies, "company");
}

export function getPart(id: string) {
  return data.parts.find((part) => part.id === id || part.pn === id || String(part.legacyId) === id) ?? firstOrThrow(data.parts, "part");
}

export function getStock(id: string) {
  return (
    [...data.internalStock, ...data.externalStock].find((stock) => stock.id === id || String(stock.legacyId) === id) ??
    firstOrThrow(data.internalStock, "internal stock")
  );
}
