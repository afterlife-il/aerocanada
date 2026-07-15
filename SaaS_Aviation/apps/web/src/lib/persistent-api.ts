import { assertPersistentApiMode, getDataSourceConfig, type DataSourceConfig } from "./data-source-mode.js";
import type { AuthSession } from "@saas-aviation/shared";

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
  jobTitle?: string; phone?: string; mobile?: string; preferredLanguage?: string; timezone?: string; notes?: string; status: "active" | "inactive";
}

export interface ApiCompanyAddress { id: string; companyId: string; label: string; addressLine1: string; addressLine2?: string; city?: string; state?: string; postalCode?: string; country: string; isPrimary: boolean; }
export interface ApiCompanyActivity { id: string; category: string; action: string; summary: string; referenceId?: string; occurredAt: string; actorId: string; }
export interface ApiWorkflowBoundary { category: string; status: "boundary"; companyId: string; futureOwner: string; requiredData: string[]; contextChecks: string[]; persistence: "none"; }
export interface ApiCompany360 { company: ApiCompany; contacts: ApiContact[]; addresses: ApiCompanyAddress[]; inventory: ApiStock[]; documents: { persistent: false; source: "workflow-boundary"; documents: [] }; activity: ApiCompanyActivity[]; workflowBoundaries: ApiWorkflowBoundary[]; }
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
    readonly body: unknown,
    readonly correlationId: string
  ) {
    super(message);
    this.name = "PersistentApiError";
  }
}

async function request<T>(path: string, init: RequestInit = {}, config: DataSourceConfig = getDataSourceConfig()): Promise<T> {
  assertPersistentApiMode(config);
  const correlationId = globalThis.crypto?.randomUUID?.() ?? `web-${Date.now()}`;
  const response = await fetch(`${config.apiBaseUrl}${path}`, {
    ...init,
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "X-Correlation-ID": correlationId,
      ...(init.body ? { "Content-Type": "application/json" } : {}),
      ...(!["GET", "HEAD"].includes(init.method ?? "GET") && typeof document !== "undefined" ? { "X-CSRF-Token": document.cookie.split(";").map((item) => item.trim()).find((item) => item.startsWith("saas_csrf="))?.slice("saas_csrf=".length) ?? "" } : {}),
      ...init.headers
    }
  });
  const body = await response.json().catch(() => ({ error: "invalid_api_response" })) as unknown;
  if (!response.ok) {
    const responseCorrelationId = response.headers.get("X-Correlation-ID") ?? correlationId;
    if (response.status === 401 && typeof window !== "undefined") window.localStorage.removeItem("saas_api_token");
    const message = response.status === 401
      ? "Your session is missing or expired. Sign in and try again."
      : `The persistent service could not complete this request. Reference: ${responseCorrelationId}`;
    throw new PersistentApiError(message, response.status, body, responseCorrelationId);
  }
  return (body as ApiEnvelope<T>).data;
}

export const persistentApi = {
  async login(email: string, password: string, config?: DataSourceConfig) {
    const result = await request<{ session: AuthSession }>("/v1/auth/login", { method: "POST", body: JSON.stringify({ email: email.trim(), password }) }, config);
    if (!result.session.token) throw new PersistentApiError("Login response did not contain a session token.", 502, result, "login-response");
    if (typeof window !== "undefined") window.localStorage.removeItem("saas_api_token");
    return result;
  },
  async logout(config?: DataSourceConfig) {
    try { return await request<{ loggedOut: true }>("/v1/auth/logout", { method: "POST" }, config); }
    finally { if (typeof window !== "undefined") window.localStorage.removeItem("saas_api_token"); }
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
