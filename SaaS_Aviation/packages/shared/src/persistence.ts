import { z } from "zod";
import type { EntityStatus, LegacyId, RequestContext, TenantId } from "./types.js";

export type CoreEntityType = "company" | "contact" | "part" | "stock";
export type CompanyRole =
  | "customer"
  | "supplier"
  | "repair-station"
  | "airline"
  | "manufacturer"
  | "broker"
  | "distributor"
  | "government"
  | "military"
  | "stock-owner"
  | "consignment-owner";

export type CoreDomainErrorCode =
  | "not_found"
  | "validation_error"
  | "duplicate_company"
  | "duplicate_contact"
  | "duplicate_part"
  | "unauthorized"
  | "tenant_mismatch"
  | "database_error";

export class CoreDomainError extends Error {
  constructor(
    readonly code: CoreDomainErrorCode,
    message: string,
    readonly details: Record<string, unknown> = {}
  ) {
    super(message);
    this.name = "CoreDomainError";
  }
}

export interface PersistentAuditFields {
  createdAt: string;
  updatedAt: string;
  createdBy: string;
  updatedBy: string;
}

export interface TenantRecord {
  id: TenantId;
  name: string;
  slug: string;
  status: "active" | "suspended";
  createdAt: string;
  updatedAt: string;
}

export interface CompanyRecord extends PersistentAuditFields {
  id: string;
  tenantId: TenantId;
  legacyId?: LegacyId | undefined;
  name: string;
  legalName?: string | undefined;
  code?: string | undefined;
  icaoCode?: string | undefined;
  iataCode?: string | undefined;
  vatNumber?: string | undefined;
  status: "active" | "inactive" | "blocked";
  email?: string | undefined;
  phone?: string | undefined;
  website?: string | undefined;
  addressLine1?: string | undefined;
  addressLine2?: string | undefined;
  city?: string | undefined;
  state?: string | undefined;
  postalCode?: string | undefined;
  country?: string | undefined;
  risk: "normal" | "watch" | "blocked";
  notes?: string | undefined;
  tags: string[];
  roles: CompanyRole[];
}

export interface CompanyAddressRecord extends PersistentAuditFields {
  id: string;
  tenantId: TenantId;
  companyId: string;
  label: string;
  addressLine1: string;
  addressLine2?: string | undefined;
  city?: string | undefined;
  state?: string | undefined;
  postalCode?: string | undefined;
  country: string;
  isPrimary: boolean;
}

export interface CompanyActivityRecord {
  id: string;
  tenantId: TenantId;
  companyId: string;
  category: "company" | "contact" | "rfq" | "supplier-quote" | "customer-quote" | "purchase-order" | "sales-order" | "stock" | "document";
  action: string;
  summary: string;
  referenceId?: string | undefined;
  occurredAt: string;
  actorId: string;
}

export interface ContactRecord extends PersistentAuditFields {
  id: string;
  tenantId: TenantId;
  companyId: string;
  legacyId?: LegacyId | undefined;
  firstName: string;
  lastName: string;
  jobTitle?: string | undefined;
  email?: string | undefined;
  phone?: string | undefined;
  mobile?: string | undefined;
  preferredLanguage?: string | undefined;
  timezone?: string | undefined;
  status: "active" | "inactive";
  notes?: string | undefined;
}

export interface PartRecord extends PersistentAuditFields {
  id: string;
  tenantId: TenantId;
  legacyId?: LegacyId | undefined;
  partNumber: string;
  normalizedPartNumber: string;
  description: string;
  manufacturer?: string | undefined;
  manufacturerCode?: string | undefined;
  ata?: string | undefined;
  ipc?: string | undefined;
  aircraft?: string[] | undefined;
  status: "active" | "inactive";
  alternates: string[];
}

export interface StockRecord extends PersistentAuditFields {
  id: string;
  tenantId: TenantId;
  legacyId?: LegacyId | undefined;
  partId: string;
  serialNumber?: string | undefined;
  quantity: number;
  condition?: string | undefined;
  releaseType?: string | undefined;
  status: EntityStatus;
  locationText?: string | undefined;
  ownerCompanyId?: string | undefined;
  supplierCompanyId?: string | undefined;
  tagInfoCompanyId?: string | undefined;
  traceabilityCompanyId?: string | undefined;
  acquisitionCost?: number | undefined;
  quotedValue?: number | undefined;
  currency?: string | undefined;
}

export interface LegacyMappingRecord {
  sourceSystem: "yoyamic";
  sourceTable: string;
  sourceId: string;
  tenantId: TenantId;
  targetEntityType: CoreEntityType;
  targetEntityId: string;
  importedAt: string;
  sourceUpdatedAt?: string | undefined;
  checksum?: string | undefined;
}

export type CreateCompanyInput = z.input<typeof createCompanySchema>;
export type UpdateCompanyInput = z.input<typeof updateCompanySchema>;
export type CreateContactInput = z.input<typeof createContactSchema>;
export type UpdateContactInput = z.input<typeof updateContactSchema>;
export type CreateCompanyAddressInput = z.input<typeof createCompanyAddressSchema>;
export type UpdateCompanyAddressInput = z.input<typeof updateCompanyAddressSchema>;
export type CreatePartInput = z.input<typeof createPartSchema>;
export type UpdatePartInput = z.input<typeof updatePartSchema>;
export type CreateStockInput = z.input<typeof createStockSchema>;
export type UpdateStockInput = z.input<typeof updateStockSchema>;

const optionalText = z.string().trim().min(1).max(500).optional();
const companyRoleSchema = z.enum([
  "customer",
  "supplier",
  "repair-station",
  "airline",
  "manufacturer",
  "broker",
  "distributor",
  "government",
  "military",
  "stock-owner",
  "consignment-owner"
]);

export const createCompanySchema = z.object({
  legacyId: z.union([z.string(), z.number()]).optional(),
  name: z.string().trim().min(1).max(240),
  legalName: optionalText,
  code: optionalText,
  icaoCode: z.string().trim().toUpperCase().length(4).optional(),
  iataCode: z.string().trim().toUpperCase().length(3).optional(),
  vatNumber: optionalText,
  status: z.enum(["active", "inactive", "blocked"]).default("active"),
  email: z.string().trim().email().optional(),
  phone: optionalText,
  website: z.string().trim().url().optional(),
  addressLine1: optionalText,
  addressLine2: optionalText,
  city: optionalText,
  state: optionalText,
  postalCode: optionalText,
  country: optionalText,
  risk: z.enum(["normal", "watch", "blocked"]).default("normal"),
  notes: optionalText,
  tags: z.array(z.string().trim().min(1).max(80)).default([]),
  roles: z.array(companyRoleSchema).min(1).default(["customer"])
});

export const updateCompanySchema = createCompanySchema.partial().refine((value) => Object.keys(value).length > 0, {
  message: "At least one company field is required."
});

export const createContactSchema = z.object({
  legacyId: z.union([z.string(), z.number()]).optional(),
  firstName: z.string().trim().min(1).max(120),
  lastName: z.string().trim().min(1).max(120),
  jobTitle: optionalText,
  email: z.string().trim().email().optional(),
  phone: optionalText,
  mobile: optionalText,
  preferredLanguage: optionalText,
  timezone: optionalText,
  status: z.enum(["active", "inactive"]).default("active"),
  notes: optionalText
});

export const updateContactSchema = createContactSchema.partial().refine((value) => Object.keys(value).length > 0, {
  message: "At least one contact field is required."
});

export const createCompanyAddressSchema = z.object({
  label: z.string().trim().min(1).max(120),
  addressLine1: z.string().trim().min(1).max(500),
  addressLine2: optionalText,
  city: optionalText,
  state: optionalText,
  postalCode: optionalText,
  country: z.string().trim().min(2).max(120),
  isPrimary: z.boolean().default(false)
});

export const updateCompanyAddressSchema = createCompanyAddressSchema.partial().refine((value) => Object.keys(value).length > 0, {
  message: "At least one address field is required."
});

export const createPartSchema = z.object({
  legacyId: z.union([z.string(), z.number()]).optional(),
  partNumber: z.string().trim().min(1).max(120),
  description: z.string().trim().min(1).max(500),
  manufacturer: optionalText,
  manufacturerCode: optionalText,
  ata: optionalText,
  ipc: optionalText,
  aircraft: z.array(z.string().trim().min(1).max(80)).default([]),
  status: z.enum(["active", "inactive"]).default("active"),
  alternates: z.array(z.string().trim().min(1).max(120)).default([])
});

export const updatePartSchema = createPartSchema.partial().refine((value) => Object.keys(value).length > 0, {
  message: "At least one part field is required."
});

export const createStockSchema = z.object({
  legacyId: z.union([z.string(), z.number()]).optional(),
  partId: z.string().trim().min(1),
  serialNumber: optionalText,
  quantity: z.number().finite().nonnegative(),
  condition: optionalText,
  releaseType: optionalText,
  status: z.enum(["available", "reserved", "sold", "purchase-order", "work-order", "consignment", "quarantine", "repair", "exchange", "unknown"]),
  locationText: optionalText,
  ownerCompanyId: optionalText,
  supplierCompanyId: optionalText,
  tagInfoCompanyId: optionalText,
  traceabilityCompanyId: optionalText,
  acquisitionCost: z.number().finite().nonnegative().optional(),
  quotedValue: z.number().finite().nonnegative().optional(),
  currency: z.string().trim().length(3).optional()
});

export const updateStockSchema = createStockSchema.partial().refine((value) => Object.keys(value).length > 0, {
  message: "At least one stock field is required."
});

export function normalizePartNumber(value: string): string {
  return value.trim().toUpperCase().replace(/[\s-]+/g, "");
}

export interface CoreCompanyRepository {
  listCompanies(context: RequestContext): Promise<CompanyRecord[]>;
  getCompanyById(context: RequestContext, id: string): Promise<CompanyRecord | null>;
  createCompany(context: RequestContext, input: CreateCompanyInput): Promise<CompanyRecord>;
  updateCompany(context: RequestContext, id: string, input: UpdateCompanyInput): Promise<CompanyRecord>;
  deleteCompany(context: RequestContext, id: string): Promise<void>;
  listCompanyAddresses(context: RequestContext, companyId: string): Promise<CompanyAddressRecord[]>;
  createCompanyAddress(context: RequestContext, companyId: string, input: CreateCompanyAddressInput): Promise<CompanyAddressRecord>;
  updateCompanyAddress(context: RequestContext, id: string, input: UpdateCompanyAddressInput): Promise<CompanyAddressRecord>;
  deleteCompanyAddress(context: RequestContext, id: string): Promise<void>;
  listCompanyActivity(context: RequestContext, companyId: string): Promise<CompanyActivityRecord[]>;
}

export interface CoreContactRepository {
  listContactsByCompany(context: RequestContext, companyId: string): Promise<ContactRecord[]>;
  createContact(context: RequestContext, companyId: string, input: CreateContactInput): Promise<ContactRecord>;
  updateContact(context: RequestContext, id: string, input: UpdateContactInput): Promise<ContactRecord>;
  deleteContact(context: RequestContext, id: string): Promise<void>;
}

export interface CorePartRepository {
  listParts(context: RequestContext): Promise<PartRecord[]>;
  getPartById(context: RequestContext, id: string): Promise<PartRecord | null>;
  createPart(context: RequestContext, input: CreatePartInput): Promise<PartRecord>;
  updatePart(context: RequestContext, id: string, input: UpdatePartInput): Promise<PartRecord>;
}

export interface CoreStockRepository {
  listStock(context: RequestContext): Promise<StockRecord[]>;
  getStockById(context: RequestContext, id: string): Promise<StockRecord | null>;
  createStockItem(context: RequestContext, input: CreateStockInput): Promise<StockRecord>;
  updateStockItem(context: RequestContext, id: string, input: UpdateStockInput): Promise<StockRecord>;
}

export interface CorePersistence extends CoreCompanyRepository, CoreContactRepository, CorePartRepository, CoreStockRepository {
  validateMigration(): Promise<{ ok: boolean; migrations: string[]; checks: string[] }>;
}
