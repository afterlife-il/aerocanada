import { assertPersistentApiMode, getDataSourceConfig, type DataSourceConfig } from "./data-source-mode.js";

interface ApiEnvelope<T> {
  data: T;
}

export interface ApiCompany {
  id: string;
  tenantId: string;
  name: string;
  roles: string[];
  legalName?: string; code?: string; icaoCode?: string; iataCode?: string; vatNumber?: string;
  status: "active" | "inactive" | "blocked"; email?: string; phone?: string; website?: string;
  city?: string; country?: string; notes?: string; tags: string[]; updatedAt: string;
}

export interface ApiContact {
  id: string;
  tenantId: string;
  companyId: string;
  firstName: string;
  lastName: string;
  email?: string;
  jobTitle?: string; phone?: string; mobile?: string; status: "active" | "inactive";
}

export interface ApiCompanyAddress { id: string; companyId: string; label: string; addressLine1: string; addressLine2?: string; city?: string; state?: string; postalCode?: string; country: string; isPrimary: boolean; }
export interface ApiCompanyActivity { id: string; category: string; action: string; summary: string; referenceId?: string; occurredAt: string; actorId: string; }
export interface ApiCompany360 { company: ApiCompany; contacts: ApiContact[]; addresses: ApiCompanyAddress[]; inventory: ApiStock[]; documents: { documents: Array<{ id: string; title: string; documentType: string; status: string }> }; activity: ApiCompanyActivity[]; workflowBoundaries: Array<{ category: string; status: "boundary"; companyId: string }>; }
export interface ApiCompanyPage { rows: ApiCompany[]; pagination: { page: number; pageSize: number; totalRows: number; totalPages: number }; }

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
      ...(typeof window !== "undefined" && window.localStorage.getItem("saas_api_token") ? { Authorization: `Bearer ${window.localStorage.getItem("saas_api_token")}` } : {}),
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
  async login(email: string, password: string, config?: DataSourceConfig) {
    const result = await request<{ token: string }>("/v1/auth/login", { method: "POST", body: JSON.stringify({ email, password }) }, config);
    if (typeof window !== "undefined") window.localStorage.setItem("saas_api_token", result.token);
    return result;
  },
  listCompanies(config?: DataSourceConfig) {
    return request<ApiCompany[]>("/v1/companies", {}, config);
  },
  createCompany(input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiCompany>("/v1/companies", { method: "POST", body: JSON.stringify(input) }, config);
  },
  updateCompany(id: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiCompany>(`/v1/companies/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config);
  },
  getCompany360(id: string, config?: DataSourceConfig) { return request<ApiCompany360>(`/v1/companies/${id}/360`, {}, config); },
  searchCompanies(params: URLSearchParams, config?: DataSourceConfig) { return request<ApiCompanyPage>(`/v1/companies?${params.toString()}`, {}, config); },
  deleteCompany(id: string, config?: DataSourceConfig) { return request<{ deleted: true }>(`/v1/companies/${id}`, { method: "DELETE" }, config); },
  createContact(companyId: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiContact>(`/v1/companies/${companyId}/contacts`, { method: "POST", body: JSON.stringify(input) }, config);
  },
  updateContact(id: string, input: Record<string, unknown>, config?: DataSourceConfig) {
    return request<ApiContact>(`/v1/contacts/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config);
  },
  deleteContact(id: string, config?: DataSourceConfig) { return request<{ deleted: true }>(`/v1/contacts/${id}`, { method: "DELETE" }, config); },
  createAddress(companyId: string, input: Record<string, unknown>, config?: DataSourceConfig) { return request<ApiCompanyAddress>(`/v1/companies/${companyId}/addresses`, { method: "POST", body: JSON.stringify(input) }, config); },
  updateAddress(id: string, input: Record<string, unknown>, config?: DataSourceConfig) { return request<ApiCompanyAddress>(`/v1/company-addresses/${id}`, { method: "PATCH", body: JSON.stringify(input) }, config); },
  deleteAddress(id: string, config?: DataSourceConfig) { return request<{ deleted: true }>(`/v1/company-addresses/${id}`, { method: "DELETE" }, config); },
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
