import { assertPersistentApiMode, getDataSourceConfig, type DataSourceConfig } from "./data-source-mode.js";

interface ApiEnvelope<T> {
  data: T;
}

export interface ApiCompany {
  id: string;
  tenantId: string;
  name: string;
  roles: string[];
}

export interface ApiContact {
  id: string;
  tenantId: string;
  companyId: string;
  firstName: string;
  lastName: string;
  email?: string;
}

export interface ApiPart {
  id: string;
  tenantId: string;
  partNumber: string;
  description: string;
}

export interface ApiStock {
  id: string;
  tenantId: string;
  partId: string;
  quantity: number;
  ownerCompanyId?: string;
  supplierCompanyId?: string;
  tagInfoCompanyId?: string;
  traceabilityCompanyId?: string;
}

export class PersistentApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
    readonly body: unknown
  ) {
    super(message);
    this.name = "PersistentApiError";
  }
}

async function request<T>(path: string, init: RequestInit = {}, config: DataSourceConfig = getDataSourceConfig()): Promise<T> {
  assertPersistentApiMode(config);
  const response = await fetch(`${config.apiBaseUrl}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      ...(init.body ? { "Content-Type": "application/json" } : {}),
      ...init.headers
    }
  });
  const body = (await response.json()) as unknown;
  if (!response.ok) {
    throw new PersistentApiError("Persistent API request failed.", response.status, body);
  }
  return (body as ApiEnvelope<T>).data;
}

export const persistentApi = {
  listCompanies(config?: DataSourceConfig) {
    return request<ApiCompany[]>("/v1/companies", {}, config);
  },
  createCompany(input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiCompany>("/v1/companies", { method: "POST", body: JSON.stringify(input) }, config);
  },
  updateCompany(id: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiCompany>(`/v1/companies/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config);
  },
  createContact(companyId: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiContact>(`/v1/companies/${companyId}/contacts`, { method: "POST", body: JSON.stringify(input) }, config);
  },
  updateContact(id: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiContact>(`/v1/contacts/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config);
  },
  listParts(config?: DataSourceConfig) {
    return request<ApiPart[]>("/v1/parts", {}, config);
  },
  createPart(input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiPart>("/v1/parts", { method: "POST", body: JSON.stringify(input) }, config);
  },
  updatePart(id: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiPart>(`/v1/parts/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config);
  },
  listStock(config?: DataSourceConfig) {
    return request<ApiStock[]>("/v1/stock", {}, config);
  },
  createStock(input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiStock>("/v1/stock", { method: "POST", body: JSON.stringify(input) }, config);
  }
};
