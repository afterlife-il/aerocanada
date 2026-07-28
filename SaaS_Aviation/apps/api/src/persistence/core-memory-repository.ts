import { randomUUID } from "node:crypto";
import {
  CoreDomainError,
  createCompanySchema,
  createCompanyAddressSchema,
  createCompanyNoteSchema,
  createContactSchema,
  createPartSchema,
  createStockSchema,
  normalizePartNumber,
  sampleCompanies,
  sampleContacts,
  sampleExternalStock,
  sampleInternalStock,
  sampleParts,
  sampleTenant,
  updateCompanySchema,
  updateCompanyAddressSchema,
  updateCompanyNoteSchema,
  updateContactSchema,
  updatePartSchema,
  updateStockSchema
} from "@saas-aviation/shared";
import type {
  CompanyRecord,
  CompanyActivityRecord,
  CompanyAddressRecord,
  CompanyNoteRecord,
  ContactRecord,
  CorePersistence,
  CreateCompanyInput,
  CreateCompanyAddressInput,
  CreateCompanyNoteInput,
  CreateContactInput,
  CreatePartInput,
  CreateStockInput,
  PartRecord,
  RequestContext,
  StockRecord,
  UpdateCompanyInput,
  UpdateCompanyAddressInput,
  UpdateCompanyNoteInput,
  UpdateContactInput,
  UpdatePartInput,
  UpdateStockInput
} from "@saas-aviation/shared";

interface MemoryStore {
  companies: CompanyRecord[];
  companyAddresses: CompanyAddressRecord[];
  companyActivity: CompanyActivityRecord[];
  companyNotes: CompanyNoteRecord[];
  contacts: ContactRecord[];
  parts: PartRecord[];
  stock: StockRecord[];
}

function now(): string {
  return new Date().toISOString();
}

function auditFields(context: RequestContext) {
  const timestamp = now();
  return {
    createdAt: timestamp,
    updatedAt: timestamp,
    createdBy: context.tenant.userId,
    updatedBy: context.tenant.userId
  };
}

function updateAudit(context: RequestContext) {
  return {
    updatedAt: now(),
    updatedBy: context.tenant.userId
  };
}

function stripUndefined<T extends Record<string, unknown>>(input: T): Partial<T> {
  return Object.fromEntries(Object.entries(input).filter(([, value]) => value !== undefined)) as Partial<T>;
}

function matchesTenant(context: RequestContext, record: { tenantId: string }): boolean {
  return record.tenantId === context.tenant.tenantId;
}

function assertSameTenant<T extends { tenantId: string }>(context: RequestContext, record: T | undefined, code = "not_found"): asserts record is T {
  if (!record) {
    throw new CoreDomainError(code === "not_found" ? "not_found" : "tenant_mismatch", "Record was not found in the current tenant.");
  }
  if (!matchesTenant(context, record)) {
    throw new CoreDomainError("tenant_mismatch", "Record belongs to a different tenant.");
  }
}

function contactNames(name: string): { firstName: string; lastName: string } {
  const [firstName = "Unknown", ...rest] = name.trim().split(/\s+/);
  return { firstName, lastName: rest.join(" ") || "Contact" };
}

function seedStore(): MemoryStore {
  const seedActor = "system.seed";
  return {
    companies: sampleCompanies.map((company) => ({
      id: company.id,
      tenantId: company.tenantId,
      legacyId: company.legacyId,
      name: company.name,
      legalName: company.name,
      code: company.tags.find((tag) => tag === sampleTenant.code),
      status: company.riskLevel === "blocked" ? "blocked" : "active",
      email: company.primaryEmail,
      website: company.website,
      city: company.city,
      country: company.country,
      risk: company.riskLevel,
      notes: "Seeded from static sample read model.",
      tags: company.tags,
      roles:
        company.type === "owner"
          ? ["stock-owner"]
          : company.type === "repair-vendor"
            ? ["repair-station"]
            : company.type === "mixed"
              ? ["customer", "supplier"]
              : [company.type],
      createdAt: "2026-07-01T00:00:00.000Z",
      updatedAt: "2026-07-01T00:00:00.000Z",
      createdBy: seedActor,
      updatedBy: seedActor
    })),
    companyAddresses: [],
    companyActivity: [],
    companyNotes: [],
    contacts: sampleContacts.map((contact) => ({
      id: contact.id,
      tenantId: contact.tenantId,
      companyId: contact.companyId,
      legacyId: contact.legacyId,
      ...contactNames(contact.name),
      jobTitle: contact.title,
      email: contact.email,
      phone: contact.phone,
      status: "active",
      notes: contact.division,
      createdAt: "2026-07-01T00:00:00.000Z",
      updatedAt: "2026-07-01T00:00:00.000Z",
      createdBy: seedActor,
      updatedBy: seedActor
    })),
    parts: sampleParts.map((part) => ({
      id: part.id,
      tenantId: part.tenantId,
      legacyId: part.legacyId,
      partNumber: part.pn,
      normalizedPartNumber: normalizePartNumber(part.pn),
      description: part.description,
      manufacturer: part.manufacturer,
      ata: part.ata,
      ipc: part.ipc,
      aircraft: part.aircraft ?? [],
      status: "active",
      alternates: part.alternates,
      createdAt: "2026-07-01T00:00:00.000Z",
      updatedAt: "2026-07-01T00:00:00.000Z",
      createdBy: seedActor,
      updatedBy: seedActor
    })),
    stock: [...sampleInternalStock, ...sampleExternalStock].map((stock) => ({
      id: stock.id,
      tenantId: stock.tenantId,
      legacyId: stock.legacyId,
      partId: stock.partId,
      serialNumber: stock.serialNumber,
      quantity: stock.qty,
      condition: stock.condition,
      releaseType: stock.release,
      status: stock.status,
      locationText: stock.location,
      ownerCompanyId: sampleCompanies.find((company) => company.name === stock.ownerCompany)?.id,
      supplierCompanyId: sampleCompanies.find((company) => company.name === stock.supplierCompany)?.id,
      tagInfoCompanyId: sampleCompanies.find((company) => company.name === stock.tagInfoCompany)?.id,
      traceabilityCompanyId: sampleCompanies.find((company) => company.name === stock.traceabilityCompany)?.id,
      quotedValue: stock.price,
      currency: stock.currency,
      createdAt: "2026-07-01T00:00:00.000Z",
      updatedAt: "2026-07-01T00:00:00.000Z",
      createdBy: seedActor,
      updatedBy: seedActor
    }))
  };
}

export class InMemoryCorePersistence implements CorePersistence {
  private readonly store: MemoryStore;

  constructor(store: MemoryStore = seedStore()) {
    this.store = {
      companies: [...store.companies],
      companyAddresses: [...store.companyAddresses],
      companyActivity: [...store.companyActivity],
      companyNotes: [...store.companyNotes],
      contacts: [...store.contacts],
      parts: [...store.parts],
      stock: [...store.stock]
    };
  }

  async listCompanies(context: RequestContext): Promise<CompanyRecord[]> {
    return this.store.companies.filter((company) => matchesTenant(context, company));
  }

  async getCompanyById(context: RequestContext, id: string): Promise<CompanyRecord | null> {
    return this.store.companies.find((company) => matchesTenant(context, company) && (company.id === id || String(company.legacyId) === id)) ?? null;
  }

  async createCompany(context: RequestContext, input: CreateCompanyInput): Promise<CompanyRecord> {
    const parsed = createCompanySchema.parse(input);
    const duplicate = this.store.companies.find((company) => matchesTenant(context, company) && company.name.toLowerCase() === parsed.name.toLowerCase());
    if (duplicate) {
      throw new CoreDomainError("duplicate_company", "A company with this name already exists for the tenant.", { name: parsed.name });
    }
    const company: CompanyRecord = {
      ...parsed,
      id: randomUUID(),
      tenantId: context.tenant.tenantId,
      ...auditFields(context)
    };
    this.store.companies.push(company);
    this.recordCompanyActivity(context, company.id, "company", "created", `Company ${company.name} created.`, company.id);
    return company;
  }

  async updateCompany(context: RequestContext, id: string, input: UpdateCompanyInput): Promise<CompanyRecord> {
    const parsed = updateCompanySchema.parse(input);
    const index = this.store.companies.findIndex((company) => company.id === id || String(company.legacyId) === id);
    const existing = this.store.companies[index];
    assertSameTenant(context, existing);
    if (parsed.name) {
      const duplicate = this.store.companies.find(
        (company) => matchesTenant(context, company) && company.id !== existing.id && company.name.toLowerCase() === parsed.name?.toLowerCase()
      );
      if (duplicate) {
        throw new CoreDomainError("duplicate_company", "A company with this name already exists for the tenant.", { name: parsed.name });
      }
    }
    const updated = { ...existing, ...(stripUndefined(parsed) as Partial<CompanyRecord>), ...updateAudit(context) } as CompanyRecord;
    this.store.companies[index] = updated;
    this.recordCompanyActivity(context, updated.id, "company", "updated", `Company ${updated.name} updated.`, updated.id);
    return updated;
  }

  async deleteCompany(context: RequestContext, id: string): Promise<void> {
    const index = this.store.companies.findIndex((company) => matchesTenant(context, company) && (company.id === id || String(company.legacyId) === id));
    const existing = this.store.companies[index];
    if (!existing) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    if (this.store.stock.some((stock) => matchesTenant(context, stock) && [stock.ownerCompanyId, stock.supplierCompanyId, stock.tagInfoCompanyId, stock.traceabilityCompanyId].includes(existing.id))) {
      throw new CoreDomainError("database_error", "Company cannot be deleted while stock relationships exist.");
    }
    this.store.companies.splice(index, 1);
    this.store.contacts = this.store.contacts.filter((contact) => contact.companyId !== existing.id || !matchesTenant(context, contact));
    this.store.companyAddresses = this.store.companyAddresses.filter((address) => address.companyId !== existing.id || !matchesTenant(context, address));
    this.store.companyActivity = this.store.companyActivity.filter((activity) => activity.companyId !== existing.id || !matchesTenant(context, activity));
    this.store.companyNotes = this.store.companyNotes.filter((note) => note.companyId !== existing.id || !matchesTenant(context, note));
  }

  async listCompanyAddresses(context: RequestContext, companyId: string): Promise<CompanyAddressRecord[]> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    return this.store.companyAddresses.filter((address) => matchesTenant(context, address) && address.companyId === company.id);
  }

  async createCompanyAddress(context: RequestContext, companyId: string, input: CreateCompanyAddressInput): Promise<CompanyAddressRecord> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    const parsed = createCompanyAddressSchema.parse(input);
    if (parsed.isPrimary) this.store.companyAddresses = this.store.companyAddresses.map((address) => address.companyId === company.id ? { ...address, isPrimary: false } : address);
    const address = { ...parsed, id: randomUUID(), tenantId: context.tenant.tenantId, companyId: company.id, ...auditFields(context) };
    this.store.companyAddresses.push(address);
    this.recordCompanyActivity(context, company.id, "company", "address-created", `${address.label} address created.`, address.id);
    return address;
  }

  async updateCompanyAddress(context: RequestContext, id: string, input: UpdateCompanyAddressInput): Promise<CompanyAddressRecord> {
    const index = this.store.companyAddresses.findIndex((address) => address.id === id);
    const existing = this.store.companyAddresses[index];
    assertSameTenant(context, existing);
    const parsed = updateCompanyAddressSchema.parse(input);
    if (parsed.isPrimary) this.store.companyAddresses = this.store.companyAddresses.map((address) => address.companyId === existing.companyId ? { ...address, isPrimary: false } : address);
    const updated = { ...existing, ...stripUndefined(parsed), ...updateAudit(context) } as CompanyAddressRecord;
    this.store.companyAddresses[index] = updated;
    this.recordCompanyActivity(context, existing.companyId, "company", "address-updated", `${updated.label} address updated.`, updated.id);
    return updated;
  }

  async deleteCompanyAddress(context: RequestContext, id: string): Promise<void> {
    const index = this.store.companyAddresses.findIndex((address) => address.id === id);
    const existing = this.store.companyAddresses[index];
    assertSameTenant(context, existing);
    this.store.companyAddresses.splice(index, 1);
    this.recordCompanyActivity(context, existing.companyId, "company", "address-deleted", `${existing.label} address deleted.`, existing.id);
  }

  async listCompanyActivity(context: RequestContext, companyId: string): Promise<CompanyActivityRecord[]> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    return this.store.companyActivity.filter((activity) => matchesTenant(context, activity) && activity.companyId === company.id).sort((a, b) => b.occurredAt.localeCompare(a.occurredAt));
  }

  async listCompanyNotes(context: RequestContext, companyId: string): Promise<CompanyNoteRecord[]> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    return this.store.companyNotes.filter((note) => matchesTenant(context, note) && note.companyId === company.id)
      .sort((left, right) => Number(right.pinned) - Number(left.pinned) || right.updatedAt.localeCompare(left.updatedAt));
  }

  async createCompanyNote(context: RequestContext, companyId: string, input: CreateCompanyNoteInput): Promise<CompanyNoteRecord> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    const parsed = createCompanyNoteSchema.parse(input);
    const note = { ...parsed, id: randomUUID(), tenantId: context.tenant.tenantId, companyId: company.id, ...auditFields(context) };
    this.store.companyNotes.push(note);
    this.recordCompanyActivity(context, company.id, "company", "note-created", "Company note created.", note.id);
    return note;
  }

  async updateCompanyNote(context: RequestContext, id: string, input: UpdateCompanyNoteInput): Promise<CompanyNoteRecord> {
    const index = this.store.companyNotes.findIndex((note) => note.id === id);
    const existing = this.store.companyNotes[index];
    assertSameTenant(context, existing);
    const updated = { ...existing, ...stripUndefined(updateCompanyNoteSchema.parse(input)), ...updateAudit(context) } as CompanyNoteRecord;
    this.store.companyNotes[index] = updated;
    this.recordCompanyActivity(context, updated.companyId, "company", "note-updated", "Company note updated.", updated.id);
    return updated;
  }

  async deleteCompanyNote(context: RequestContext, id: string): Promise<void> {
    const index = this.store.companyNotes.findIndex((note) => note.id === id);
    const existing = this.store.companyNotes[index];
    assertSameTenant(context, existing);
    this.store.companyNotes.splice(index, 1);
    this.recordCompanyActivity(context, existing.companyId, "company", "note-deleted", "Company note deleted.", existing.id);
  }

  async listContactsByCompany(context: RequestContext, companyId: string): Promise<ContactRecord[]> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) {
      throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    }
    return this.store.contacts.filter((contact) => matchesTenant(context, contact) && contact.companyId === company.id);
  }

  async createContact(context: RequestContext, companyId: string, input: CreateContactInput): Promise<ContactRecord> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) {
      throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    }
    const parsed = createContactSchema.parse(input);
    if (parsed.email) {
      const duplicate = this.store.contacts.find(
        (contact) => matchesTenant(context, contact) && contact.companyId === company.id && contact.email?.toLowerCase() === parsed.email?.toLowerCase()
      );
      if (duplicate) {
        throw new CoreDomainError("duplicate_contact", "A contact with this email already exists for the company.", { email: parsed.email });
      }
    }
    const contact: ContactRecord = {
      ...parsed,
      id: randomUUID(),
      tenantId: context.tenant.tenantId,
      companyId: company.id,
      ...auditFields(context)
    };
    this.store.contacts.push(contact);
    this.recordCompanyActivity(context, company.id, "contact", "created", `Contact ${contact.firstName} ${contact.lastName} created.`, contact.id);
    return contact;
  }

  async updateContact(context: RequestContext, id: string, input: UpdateContactInput): Promise<ContactRecord> {
    const parsed = updateContactSchema.parse(input);
    const index = this.store.contacts.findIndex((contact) => contact.id === id || String(contact.legacyId) === id);
    const existing = this.store.contacts[index];
    assertSameTenant(context, existing);
    const updated = { ...existing, ...(stripUndefined(parsed) as Partial<ContactRecord>), ...updateAudit(context) } as ContactRecord;
    this.store.contacts[index] = updated;
    this.recordCompanyActivity(context, updated.companyId, "contact", "updated", `Contact ${updated.firstName} ${updated.lastName} updated.`, updated.id);
    return updated;
  }

  async deleteContact(context: RequestContext, id: string): Promise<void> {
    const index = this.store.contacts.findIndex((contact) => contact.id === id || String(contact.legacyId) === id);
    const existing = this.store.contacts[index];
    assertSameTenant(context, existing);
    this.store.contacts.splice(index, 1);
    this.recordCompanyActivity(context, existing.companyId, "contact", "deleted", `Contact ${existing.firstName} ${existing.lastName} deleted.`, existing.id);
  }

  private recordCompanyActivity(context: RequestContext, companyId: string, category: CompanyActivityRecord["category"], action: string, summary: string, referenceId?: string): void {
    this.store.companyActivity.push({ id: randomUUID(), tenantId: context.tenant.tenantId, companyId, category, action, summary, referenceId, occurredAt: now(), actorId: context.tenant.userId });
  }

  async listParts(context: RequestContext): Promise<PartRecord[]> {
    return this.store.parts.filter((part) => matchesTenant(context, part));
  }

  async getPartById(context: RequestContext, id: string): Promise<PartRecord | null> {
    return this.store.parts.find((part) => matchesTenant(context, part) && (part.id === id || part.partNumber === id || String(part.legacyId) === id)) ?? null;
  }

  async createPart(context: RequestContext, input: CreatePartInput): Promise<PartRecord> {
    const parsed = createPartSchema.parse(input);
    const normalizedPartNumber = normalizePartNumber(parsed.partNumber);
    const duplicate = this.store.parts.find(
      (part) =>
        matchesTenant(context, part) &&
        part.normalizedPartNumber === normalizedPartNumber &&
        (part.manufacturerCode ?? part.manufacturer ?? "") === (parsed.manufacturerCode ?? parsed.manufacturer ?? "")
    );
    if (duplicate) {
      throw new CoreDomainError("duplicate_part", "A part with this normalized part number and manufacturer already exists.", {
        partNumber: parsed.partNumber
      });
    }
    const part: PartRecord = {
      ...parsed,
      id: randomUUID(),
      tenantId: context.tenant.tenantId,
      normalizedPartNumber,
      ...auditFields(context)
    };
    this.store.parts.push(part);
    return part;
  }

  async updatePart(context: RequestContext, id: string, input: UpdatePartInput): Promise<PartRecord> {
    const parsed = updatePartSchema.parse(input);
    const index = this.store.parts.findIndex((part) => part.id === id || String(part.legacyId) === id);
    const existing = this.store.parts[index];
    assertSameTenant(context, existing);
    const normalizedPartNumber = parsed.partNumber ? normalizePartNumber(parsed.partNumber) : existing.normalizedPartNumber;
    const updated = { ...existing, ...(stripUndefined(parsed) as Partial<PartRecord>), normalizedPartNumber, ...updateAudit(context) } as PartRecord;
    this.store.parts[index] = updated;
    return updated;
  }

  async listStock(context: RequestContext): Promise<StockRecord[]> {
    return this.store.stock.filter((stock) => matchesTenant(context, stock));
  }

  async getStockById(context: RequestContext, id: string): Promise<StockRecord | null> {
    return this.store.stock.find((stock) => matchesTenant(context, stock) && (stock.id === id || String(stock.legacyId) === id)) ?? null;
  }

  async createStockItem(context: RequestContext, input: CreateStockInput): Promise<StockRecord> {
    const parsed = createStockSchema.parse(input);
    const part = await this.getPartById(context, parsed.partId);
    if (!part) {
      throw new CoreDomainError("not_found", "Part was not found in the current tenant.");
    }
    for (const companyId of [parsed.ownerCompanyId, parsed.supplierCompanyId, parsed.tagInfoCompanyId, parsed.traceabilityCompanyId].filter(Boolean)) {
      if (!(await this.getCompanyById(context, companyId as string))) {
        throw new CoreDomainError("not_found", "Related company was not found in the current tenant.", { companyId });
      }
    }
    const stock: StockRecord = {
      ...parsed,
      id: randomUUID(),
      tenantId: context.tenant.tenantId,
      ...auditFields(context)
    };
    this.store.stock.push(stock);
    return stock;
  }

  async updateStockItem(context: RequestContext, id: string, input: UpdateStockInput): Promise<StockRecord> {
    const parsed = updateStockSchema.parse(input);
    const index = this.store.stock.findIndex((stock) => stock.id === id || String(stock.legacyId) === id);
    const existing = this.store.stock[index];
    assertSameTenant(context, existing);
    const updated = { ...existing, ...(stripUndefined(parsed) as Partial<StockRecord>), ...updateAudit(context) } as StockRecord;
    this.store.stock[index] = updated;
    return updated;
  }

  async validateMigration(): Promise<{ ok: boolean; migrations: string[]; checks: string[] }> {
    return {
      ok: true,
      migrations: ["001_core_persistence.sql"],
      checks: ["tenant foreign keys", "core entity indexes", "legacy mapping uniqueness", "stock relationship independence"]
    };
  }
}
