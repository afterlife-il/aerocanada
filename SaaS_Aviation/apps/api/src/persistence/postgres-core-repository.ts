import { randomUUID } from "node:crypto";
import pg from "pg";
import {
  CoreDomainError,
  createCompanySchema,
  createContactSchema,
  createPartSchema,
  createStockSchema,
  normalizePartNumber,
  updateCompanySchema,
  updateContactSchema,
  updatePartSchema,
  updateStockSchema
} from "@saas-aviation/shared";
import type {
  CompanyRecord,
  CompanyRole,
  ContactRecord,
  CorePersistence,
  CreateCompanyInput,
  CreateContactInput,
  CreatePartInput,
  CreateStockInput,
  EntityStatus,
  PartRecord,
  RequestContext,
  StockRecord,
  UpdateCompanyInput,
  UpdateContactInput,
  UpdatePartInput,
  UpdateStockInput
} from "@saas-aviation/shared";
import type { PostgresConfig } from "./config.js";

const { Pool } = pg;

type Queryable = pg.Pool | pg.PoolClient;

interface CompanyRow {
  id: string;
  tenant_id: string;
  legacy_id: string | null;
  name: string;
  legal_name: string | null;
  code: string | null;
  status: CompanyRecord["status"];
  email: string | null;
  phone: string | null;
  website: string | null;
  address_line_1: string | null;
  address_line_2: string | null;
  city: string | null;
  state: string | null;
  postal_code: string | null;
  country: string | null;
  risk: CompanyRecord["risk"];
  notes: string | null;
  created_at: Date;
  updated_at: Date;
  created_by: string;
  updated_by: string;
  roles: CompanyRole[] | null;
}

interface ContactRow {
  id: string;
  tenant_id: string;
  company_id: string;
  legacy_id: string | null;
  first_name: string;
  last_name: string;
  job_title: string | null;
  email: string | null;
  phone: string | null;
  mobile: string | null;
  preferred_language: string | null;
  timezone: string | null;
  status: ContactRecord["status"];
  notes: string | null;
  created_at: Date;
  updated_at: Date;
  created_by: string;
  updated_by: string;
}

interface PartRow {
  id: string;
  tenant_id: string;
  legacy_id: string | null;
  part_number: string;
  normalized_part_number: string;
  description: string;
  manufacturer: string | null;
  manufacturer_code: string | null;
  ata: string | null;
  ipc: string | null;
  aircraft: string[];
  status: PartRecord["status"];
  alternates: string[] | null;
  created_at: Date;
  updated_at: Date;
  created_by: string;
  updated_by: string;
}

interface StockRow {
  id: string;
  tenant_id: string;
  legacy_id: string | null;
  part_id: string;
  serial_number: string | null;
  quantity: string;
  condition: string | null;
  release_type: string | null;
  status: EntityStatus;
  location_text: string | null;
  owner_company_id: string | null;
  supplier_company_id: string | null;
  tag_info_company_id: string | null;
  traceability_company_id: string | null;
  acquisition_cost: string | null;
  quoted_value: string | null;
  currency: string | null;
  created_at: Date;
  updated_at: Date;
  created_by: string;
  updated_by: string;
}

function optional<T>(value: T | null): T | undefined {
  return value ?? undefined;
}

function legacy(value: string | number | undefined): string | undefined {
  return value === undefined ? undefined : String(value);
}

function stripUndefined<T extends Record<string, unknown>>(input: T): Partial<T> {
  return Object.fromEntries(Object.entries(input).filter(([, value]) => value !== undefined)) as Partial<T>;
}

function mapPgError(error: unknown): CoreDomainError {
  const pgError = error as { code?: string; constraint?: string; detail?: string };
  if (pgError.code === "23505") {
    if (pgError.constraint?.includes("companies")) {
      return new CoreDomainError("duplicate_company", "A company with this name already exists for the tenant.");
    }
    if (pgError.constraint?.includes("parts")) {
      return new CoreDomainError("duplicate_part", "A part with this normalized part number and manufacturer already exists.");
    }
  }
  if (pgError.code === "23503") {
    return new CoreDomainError("tenant_mismatch", "Related record is missing or belongs to a different tenant.");
  }
  return new CoreDomainError("database_error", "PostgreSQL persistence operation failed.");
}

function companyFromRow(row: CompanyRow): CompanyRecord {
  return {
    id: row.id,
    tenantId: row.tenant_id,
    legacyId: optional(row.legacy_id),
    name: row.name,
    legalName: optional(row.legal_name),
    code: optional(row.code),
    status: row.status,
    email: optional(row.email),
    phone: optional(row.phone),
    website: optional(row.website),
    addressLine1: optional(row.address_line_1),
    addressLine2: optional(row.address_line_2),
    city: optional(row.city),
    state: optional(row.state),
    postalCode: optional(row.postal_code),
    country: optional(row.country),
    risk: row.risk,
    notes: optional(row.notes),
    roles: row.roles ?? [],
    createdAt: row.created_at.toISOString(),
    updatedAt: row.updated_at.toISOString(),
    createdBy: row.created_by,
    updatedBy: row.updated_by
  };
}

function contactFromRow(row: ContactRow): ContactRecord {
  return {
    id: row.id,
    tenantId: row.tenant_id,
    companyId: row.company_id,
    legacyId: optional(row.legacy_id),
    firstName: row.first_name,
    lastName: row.last_name,
    jobTitle: optional(row.job_title),
    email: optional(row.email),
    phone: optional(row.phone),
    mobile: optional(row.mobile),
    preferredLanguage: optional(row.preferred_language),
    timezone: optional(row.timezone),
    status: row.status,
    notes: optional(row.notes),
    createdAt: row.created_at.toISOString(),
    updatedAt: row.updated_at.toISOString(),
    createdBy: row.created_by,
    updatedBy: row.updated_by
  };
}

function partFromRow(row: PartRow): PartRecord {
  return {
    id: row.id,
    tenantId: row.tenant_id,
    legacyId: optional(row.legacy_id),
    partNumber: row.part_number,
    normalizedPartNumber: row.normalized_part_number,
    description: row.description,
    manufacturer: optional(row.manufacturer),
    manufacturerCode: optional(row.manufacturer_code),
    ata: optional(row.ata),
    ipc: optional(row.ipc),
    aircraft: row.aircraft,
    status: row.status,
    alternates: row.alternates ?? [],
    createdAt: row.created_at.toISOString(),
    updatedAt: row.updated_at.toISOString(),
    createdBy: row.created_by,
    updatedBy: row.updated_by
  };
}

function stockFromRow(row: StockRow): StockRecord {
  return {
    id: row.id,
    tenantId: row.tenant_id,
    legacyId: optional(row.legacy_id),
    partId: row.part_id,
    serialNumber: optional(row.serial_number),
    quantity: Number(row.quantity),
    condition: optional(row.condition),
    releaseType: optional(row.release_type),
    status: row.status,
    locationText: optional(row.location_text),
    ownerCompanyId: optional(row.owner_company_id),
    supplierCompanyId: optional(row.supplier_company_id),
    tagInfoCompanyId: optional(row.tag_info_company_id),
    traceabilityCompanyId: optional(row.traceability_company_id),
    acquisitionCost: row.acquisition_cost === null ? undefined : Number(row.acquisition_cost),
    quotedValue: row.quoted_value === null ? undefined : Number(row.quoted_value),
    currency: optional(row.currency),
    createdAt: row.created_at.toISOString(),
    updatedAt: row.updated_at.toISOString(),
    createdBy: row.created_by,
    updatedBy: row.updated_by
  };
}

async function withTransaction<T>(pool: pg.Pool, work: (client: pg.PoolClient) => Promise<T>): Promise<T> {
  const client = await pool.connect();
  try {
    await client.query("BEGIN");
    const result = await work(client);
    await client.query("COMMIT");
    return result;
  } catch (error) {
    await client.query("ROLLBACK");
    throw error;
  } finally {
    client.release();
  }
}

export class PostgresCorePersistence implements CorePersistence {
  readonly pool: pg.Pool;

  constructor(config: PostgresConfig | pg.Pool) {
    this.pool =
      config instanceof Pool
        ? config
        : new Pool({
            connectionString: config.connectionString,
            min: config.poolMin,
            max: config.poolMax,
            ssl: config.ssl ? { rejectUnauthorized: true } : undefined
          });
  }

  async close(): Promise<void> {
    await this.pool.end();
  }

  async ensureTenant(context: RequestContext): Promise<void> {
    await this.pool.query(
      `INSERT INTO tenants (id, name, slug, status)
       VALUES ($1, $2, $3, 'active')
       ON CONFLICT (id) DO NOTHING`,
      [context.tenant.tenantId, context.tenant.tenantName, context.tenant.tenantCode.toLowerCase()]
    );
  }

  async listCompanies(context: RequestContext): Promise<CompanyRecord[]> {
    const result = await this.pool.query<CompanyRow>(
      `SELECT c.*, COALESCE(array_agg(cr.role ORDER BY cr.role) FILTER (WHERE cr.role IS NOT NULL), '{}') AS roles
       FROM companies c
       LEFT JOIN company_roles cr ON cr.tenant_id = c.tenant_id AND cr.company_id = c.id
       WHERE c.tenant_id = $1
       GROUP BY c.id
       ORDER BY c.name`,
      [context.tenant.tenantId]
    );
    return result.rows.map(companyFromRow);
  }

  async getCompanyById(context: RequestContext, id: string): Promise<CompanyRecord | null> {
    const result = await this.pool.query<CompanyRow>(
      `SELECT c.*, COALESCE(array_agg(cr.role ORDER BY cr.role) FILTER (WHERE cr.role IS NOT NULL), '{}') AS roles
       FROM companies c
       LEFT JOIN company_roles cr ON cr.tenant_id = c.tenant_id AND cr.company_id = c.id
       WHERE c.tenant_id = $1 AND (c.id = $2 OR c.legacy_id = $2)
       GROUP BY c.id
       LIMIT 1`,
      [context.tenant.tenantId, id]
    );
    return result.rows[0] ? companyFromRow(result.rows[0]) : null;
  }

  async createCompany(context: RequestContext, input: CreateCompanyInput): Promise<CompanyRecord> {
    const parsed = createCompanySchema.parse(input);
    try {
      return await withTransaction(this.pool, async (client) => {
        await this.ensureTenantWithClient(client, context);
        const id = randomUUID();
        await client.query(
          `INSERT INTO companies (
            id, tenant_id, legacy_id, name, legal_name, code, status, email, phone, website,
            address_line_1, address_line_2, city, state, postal_code, country, risk, notes, created_by, updated_by
          ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$19)`,
          [
            id,
            context.tenant.tenantId,
            legacy(parsed.legacyId),
            parsed.name,
            parsed.legalName,
            parsed.code,
            parsed.status,
            parsed.email,
            parsed.phone,
            parsed.website,
            parsed.addressLine1,
            parsed.addressLine2,
            parsed.city,
            parsed.state,
            parsed.postalCode,
            parsed.country,
            parsed.risk,
            parsed.notes,
            context.tenant.userId
          ]
        );
        await this.replaceCompanyRoles(client, context, id, parsed.roles);
        const created = await this.getCompanyByIdUsing(client, context, id);
        if (!created) throw new CoreDomainError("database_error", "Created company could not be reloaded.");
        return created;
      });
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async updateCompany(context: RequestContext, id: string, input: UpdateCompanyInput): Promise<CompanyRecord> {
    const parsed = stripUndefined(updateCompanySchema.parse(input));
    try {
      return await withTransaction(this.pool, async (client) => {
        const existing = await this.getCompanyByIdUsing(client, context, id);
        if (!existing) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
        const merged = { ...existing, ...parsed };
        await client.query(
          `UPDATE companies SET
            legacy_id=$3, name=$4, legal_name=$5, code=$6, status=$7, email=$8, phone=$9, website=$10,
            address_line_1=$11, address_line_2=$12, city=$13, state=$14, postal_code=$15, country=$16,
            risk=$17, notes=$18, updated_at=now(), updated_by=$19
           WHERE tenant_id=$1 AND id=$2`,
          [
            context.tenant.tenantId,
            existing.id,
            legacy(merged.legacyId),
            merged.name,
            merged.legalName,
            merged.code,
            merged.status,
            merged.email,
            merged.phone,
            merged.website,
            merged.addressLine1,
            merged.addressLine2,
            merged.city,
            merged.state,
            merged.postalCode,
            merged.country,
            merged.risk,
            merged.notes,
            context.tenant.userId
          ]
        );
        if (Array.isArray(parsed.roles)) {
          await this.replaceCompanyRoles(client, context, existing.id, parsed.roles);
        }
        const updated = await this.getCompanyByIdUsing(client, context, existing.id);
        if (!updated) throw new CoreDomainError("database_error", "Updated company could not be reloaded.");
        return updated;
      });
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async listContactsByCompany(context: RequestContext, companyId: string): Promise<ContactRecord[]> {
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    const result = await this.pool.query<ContactRow>("SELECT * FROM contacts WHERE tenant_id = $1 AND company_id = $2 ORDER BY last_name, first_name", [
      context.tenant.tenantId,
      company.id
    ]);
    return result.rows.map(contactFromRow);
  }

  async createContact(context: RequestContext, companyId: string, input: CreateContactInput): Promise<ContactRecord> {
    const parsed = createContactSchema.parse(input);
    const company = await this.getCompanyById(context, companyId);
    if (!company) throw new CoreDomainError("not_found", "Company was not found in the current tenant.");
    try {
      const id = randomUUID();
      const result = await this.pool.query<ContactRow>(
        `INSERT INTO contacts (
          id, tenant_id, company_id, legacy_id, first_name, last_name, job_title, email, phone, mobile,
          preferred_language, timezone, status, notes, created_by, updated_by
        ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$15)
        RETURNING *`,
        [
          id,
          context.tenant.tenantId,
          company.id,
          legacy(parsed.legacyId),
          parsed.firstName,
          parsed.lastName,
          parsed.jobTitle,
          parsed.email,
          parsed.phone,
          parsed.mobile,
          parsed.preferredLanguage,
          parsed.timezone,
          parsed.status,
          parsed.notes,
          context.tenant.userId
        ]
      );
      const row = result.rows[0];
      if (!row) throw new CoreDomainError("database_error", "Created contact could not be reloaded.");
      return contactFromRow(row);
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async updateContact(context: RequestContext, id: string, input: UpdateContactInput): Promise<ContactRecord> {
    const parsed = stripUndefined(updateContactSchema.parse(input));
    const existing = await this.getContactById(context, id);
    if (!existing) throw new CoreDomainError("not_found", "Contact was not found in the current tenant.");
    const merged = { ...existing, ...parsed };
    try {
      const result = await this.pool.query<ContactRow>(
        `UPDATE contacts SET
          legacy_id=$3, first_name=$4, last_name=$5, job_title=$6, email=$7, phone=$8, mobile=$9,
          preferred_language=$10, timezone=$11, status=$12, notes=$13, updated_at=now(), updated_by=$14
         WHERE tenant_id=$1 AND id=$2
         RETURNING *`,
        [
          context.tenant.tenantId,
          existing.id,
          legacy(merged.legacyId),
          merged.firstName,
          merged.lastName,
          merged.jobTitle,
          merged.email,
          merged.phone,
          merged.mobile,
          merged.preferredLanguage,
          merged.timezone,
          merged.status,
          merged.notes,
          context.tenant.userId
        ]
      );
      const row = result.rows[0];
      if (!row) throw new CoreDomainError("not_found", "Contact was not found in the current tenant.");
      return contactFromRow(row);
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async listParts(context: RequestContext): Promise<PartRecord[]> {
    const result = await this.pool.query<PartRow>(
      `SELECT p.*, COALESCE(array_agg(pa.alternate_part_number ORDER BY pa.alternate_part_number) FILTER (WHERE pa.alternate_part_number IS NOT NULL), '{}') AS alternates
       FROM part_numbers p
       LEFT JOIN part_alternates pa ON pa.tenant_id = p.tenant_id AND pa.part_id = p.id
       WHERE p.tenant_id = $1
       GROUP BY p.id
       ORDER BY p.part_number`,
      [context.tenant.tenantId]
    );
    return result.rows.map(partFromRow);
  }

  async getPartById(context: RequestContext, id: string): Promise<PartRecord | null> {
    return this.getPartByIdUsing(this.pool, context, id);
  }

  async createPart(context: RequestContext, input: CreatePartInput): Promise<PartRecord> {
    const parsed = createPartSchema.parse(input);
    try {
      return await withTransaction(this.pool, async (client) => {
        await this.ensureTenantWithClient(client, context);
        const id = randomUUID();
        await client.query(
          `INSERT INTO part_numbers (
            id, tenant_id, legacy_id, part_number, normalized_part_number, description, manufacturer,
            manufacturer_code, ata, ipc, aircraft, status, created_by, updated_by
          ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$13)`,
          [
            id,
            context.tenant.tenantId,
            legacy(parsed.legacyId),
            parsed.partNumber,
            normalizePartNumber(parsed.partNumber),
            parsed.description,
            parsed.manufacturer,
            parsed.manufacturerCode,
            parsed.ata,
            parsed.ipc,
            parsed.aircraft,
            parsed.status,
            context.tenant.userId
          ]
        );
        await this.replacePartAlternates(client, context, id, parsed.alternates);
        const created = await this.getPartByIdUsing(client, context, id);
        if (!created) throw new CoreDomainError("database_error", "Created part could not be reloaded.");
        return created;
      });
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async updatePart(context: RequestContext, id: string, input: UpdatePartInput): Promise<PartRecord> {
    const parsed = stripUndefined(updatePartSchema.parse(input));
    try {
      return await withTransaction(this.pool, async (client) => {
        const existing = await this.getPartByIdUsing(client, context, id);
        if (!existing) throw new CoreDomainError("not_found", "Part was not found in the current tenant.");
        const merged = { ...existing, ...parsed };
        const partNumber = merged.partNumber ?? existing.partNumber;
        await client.query(
          `UPDATE part_numbers SET
            legacy_id=$3, part_number=$4, normalized_part_number=$5, description=$6, manufacturer=$7,
            manufacturer_code=$8, ata=$9, ipc=$10, aircraft=$11, status=$12, updated_at=now(), updated_by=$13
           WHERE tenant_id=$1 AND id=$2`,
          [
            context.tenant.tenantId,
            existing.id,
            legacy(merged.legacyId),
            partNumber,
            normalizePartNumber(partNumber),
            merged.description,
            merged.manufacturer,
            merged.manufacturerCode,
            merged.ata,
            merged.ipc,
            merged.aircraft,
            merged.status,
            context.tenant.userId
          ]
        );
        if (Array.isArray(parsed.alternates)) {
          await this.replacePartAlternates(client, context, existing.id, parsed.alternates);
        }
        const updated = await this.getPartByIdUsing(client, context, existing.id);
        if (!updated) throw new CoreDomainError("database_error", "Updated part could not be reloaded.");
        return updated;
      });
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async listStock(context: RequestContext): Promise<StockRecord[]> {
    const result = await this.pool.query<StockRow>("SELECT * FROM stock_items WHERE tenant_id = $1 ORDER BY created_at, id", [context.tenant.tenantId]);
    return result.rows.map(stockFromRow);
  }

  async getStockById(context: RequestContext, id: string): Promise<StockRecord | null> {
    const result = await this.pool.query<StockRow>("SELECT * FROM stock_items WHERE tenant_id = $1 AND (id = $2 OR legacy_id = $2) LIMIT 1", [
      context.tenant.tenantId,
      id
    ]);
    return result.rows[0] ? stockFromRow(result.rows[0]) : null;
  }

  async createStockItem(context: RequestContext, input: CreateStockInput): Promise<StockRecord> {
    const parsed = createStockSchema.parse(input);
    try {
      const id = randomUUID();
      const result = await this.pool.query<StockRow>(
        `INSERT INTO stock_items (
          id, tenant_id, legacy_id, part_id, serial_number, quantity, condition, release_type, status,
          location_text, owner_company_id, supplier_company_id, tag_info_company_id, traceability_company_id,
          acquisition_cost, quoted_value, currency, created_by, updated_by
        ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$18)
        RETURNING *`,
        [
          id,
          context.tenant.tenantId,
          legacy(parsed.legacyId),
          parsed.partId,
          parsed.serialNumber,
          parsed.quantity,
          parsed.condition,
          parsed.releaseType,
          parsed.status,
          parsed.locationText,
          parsed.ownerCompanyId,
          parsed.supplierCompanyId,
          parsed.tagInfoCompanyId,
          parsed.traceabilityCompanyId,
          parsed.acquisitionCost,
          parsed.quotedValue,
          parsed.currency,
          context.tenant.userId
        ]
      );
      const row = result.rows[0];
      if (!row) throw new CoreDomainError("database_error", "Created stock item could not be reloaded.");
      return stockFromRow(row);
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async updateStockItem(context: RequestContext, id: string, input: UpdateStockInput): Promise<StockRecord> {
    const parsed = stripUndefined(updateStockSchema.parse(input));
    const existing = await this.getStockById(context, id);
    if (!existing) throw new CoreDomainError("not_found", "Stock item was not found in the current tenant.");
    const merged = { ...existing, ...parsed };
    try {
      const result = await this.pool.query<StockRow>(
        `UPDATE stock_items SET
          legacy_id=$3, part_id=$4, serial_number=$5, quantity=$6, condition=$7, release_type=$8, status=$9,
          location_text=$10, owner_company_id=$11, supplier_company_id=$12, tag_info_company_id=$13,
          traceability_company_id=$14, acquisition_cost=$15, quoted_value=$16, currency=$17,
          updated_at=now(), updated_by=$18
         WHERE tenant_id=$1 AND id=$2
         RETURNING *`,
        [
          context.tenant.tenantId,
          existing.id,
          legacy(merged.legacyId),
          merged.partId,
          merged.serialNumber,
          merged.quantity,
          merged.condition,
          merged.releaseType,
          merged.status,
          merged.locationText,
          merged.ownerCompanyId,
          merged.supplierCompanyId,
          merged.tagInfoCompanyId,
          merged.traceabilityCompanyId,
          merged.acquisitionCost,
          merged.quotedValue,
          merged.currency,
          context.tenant.userId
        ]
      );
      const row = result.rows[0];
      if (!row) throw new CoreDomainError("not_found", "Stock item was not found in the current tenant.");
      return stockFromRow(row);
    } catch (error) {
      if (error instanceof CoreDomainError) throw error;
      throw mapPgError(error);
    }
  }

  async validateMigration(): Promise<{ ok: boolean; migrations: string[]; checks: string[] }> {
    const result = await this.pool.query<{ id: string }>("SELECT id FROM schema_migrations ORDER BY id");
    return {
      ok: result.rows.some((row) => row.id === "001_core_persistence.sql"),
      migrations: result.rows.map((row) => row.id),
      checks: ["schema_migrations ledger", "tenant composite foreign keys", "stock relationship independence", "quantity zero permitted"]
    };
  }

  private async ensureTenantWithClient(client: pg.PoolClient, context: RequestContext): Promise<void> {
    await client.query(
      `INSERT INTO tenants (id, name, slug, status)
       VALUES ($1, $2, $3, 'active')
       ON CONFLICT (id) DO NOTHING`,
      [context.tenant.tenantId, context.tenant.tenantName, context.tenant.tenantCode.toLowerCase()]
    );
  }

  private async replaceCompanyRoles(client: pg.PoolClient, context: RequestContext, companyId: string, roles: CompanyRole[]): Promise<void> {
    await client.query("DELETE FROM company_roles WHERE tenant_id = $1 AND company_id = $2", [context.tenant.tenantId, companyId]);
    for (const role of roles) {
      await client.query("INSERT INTO company_roles (tenant_id, company_id, role) VALUES ($1, $2, $3)", [context.tenant.tenantId, companyId, role]);
    }
  }

  private async replacePartAlternates(client: pg.PoolClient, context: RequestContext, partId: string, alternates: string[]): Promise<void> {
    await client.query("DELETE FROM part_alternates WHERE tenant_id = $1 AND part_id = $2", [context.tenant.tenantId, partId]);
    for (const alternate of alternates) {
      await client.query("INSERT INTO part_alternates (tenant_id, part_id, alternate_part_number) VALUES ($1, $2, $3)", [
        context.tenant.tenantId,
        partId,
        alternate
      ]);
    }
  }

  private async getCompanyByIdUsing(queryable: Queryable, context: RequestContext, id: string): Promise<CompanyRecord | null> {
    const result = await queryable.query<CompanyRow>(
      `SELECT c.*, COALESCE(array_agg(cr.role ORDER BY cr.role) FILTER (WHERE cr.role IS NOT NULL), '{}') AS roles
       FROM companies c
       LEFT JOIN company_roles cr ON cr.tenant_id = c.tenant_id AND cr.company_id = c.id
       WHERE c.tenant_id = $1 AND (c.id = $2 OR c.legacy_id = $2)
       GROUP BY c.id
       LIMIT 1`,
      [context.tenant.tenantId, id]
    );
    return result.rows[0] ? companyFromRow(result.rows[0]) : null;
  }

  private async getContactById(context: RequestContext, id: string): Promise<ContactRecord | null> {
    const result = await this.pool.query<ContactRow>("SELECT * FROM contacts WHERE tenant_id = $1 AND (id = $2 OR legacy_id = $2) LIMIT 1", [
      context.tenant.tenantId,
      id
    ]);
    return result.rows[0] ? contactFromRow(result.rows[0]) : null;
  }

  private async getPartByIdUsing(queryable: Queryable, context: RequestContext, id: string): Promise<PartRecord | null> {
    const result = await queryable.query<PartRow>(
      `SELECT p.*, COALESCE(array_agg(pa.alternate_part_number ORDER BY pa.alternate_part_number) FILTER (WHERE pa.alternate_part_number IS NOT NULL), '{}') AS alternates
       FROM part_numbers p
       LEFT JOIN part_alternates pa ON pa.tenant_id = p.tenant_id AND pa.part_id = p.id
       WHERE p.tenant_id = $1 AND (p.id = $2 OR p.legacy_id = $2 OR p.part_number = $2)
       GROUP BY p.id
       LIMIT 1`,
      [context.tenant.tenantId, id]
    );
    return result.rows[0] ? partFromRow(result.rows[0]) : null;
  }
}
