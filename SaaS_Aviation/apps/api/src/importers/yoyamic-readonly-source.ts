import { createHash } from "node:crypto";
import type { CoreEntityType, LegacyMappingRecord, TenantId } from "@saas-aviation/shared";
import type { ImportDryRunReport, LegacyYoyamicSnapshot } from "./yoyamic-core-importer.js";

export interface YoyamicReadOptions {
  tenantId: string;
  limit: number;
  timeoutMs: number;
  since?: string;
  offset?: number;
}

export interface YoyamicReadonlySource {
  readCompanies(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["companies"]>;
  readContacts(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["contacts"]>;
  readParts(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["parts"]>;
  readStock(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["stock"]>;
}

export type YoyamicReadEntity = keyof LegacyYoyamicSnapshot;

export interface YoyamicReadQueryPlan {
  entity: YoyamicReadEntity;
  sql: string;
  params: readonly unknown[];
  limit: number;
  offset: number;
  timeoutMs: number;
}

export interface YoyamicSourceMapping {
  entity: YoyamicReadEntity;
  sourceTable: string;
  sourceIdColumn: string;
  updatedAtColumn?: string;
  orderBy: string;
}

export interface YoyamicReconciliationSummary {
  tenantId: string;
  sourceRows: number;
  candidateRows: number;
  skippedRows: number;
  duplicateRows: number;
  failedRows: number;
  zeroQuantityRows: number;
  anomalyCount: number;
  anomalyCodes: string[];
}

export const yoyamicReadonlyPolicy = {
  allowedSql: ["SELECT", "SHOW"],
  forbiddenSql: ["INSERT", "UPDATE", "DELETE", "ALTER", "DROP", "CREATE", "TRUNCATE", "REPLACE", "CALL"],
  defaultTimeoutMs: 10_000,
  defaultLimit: 1_000,
  maximumLimit: 10_000,
  maximumOffset: 10_000_000,
  credentialPolicy: "Use environment variables only; never log connection strings, usernames, or passwords.",
  importPolicy: "Read from Yoyamic through this interface, then pass a snapshot to dryRunYoyamicCoreImport before writing to SaaS_Aviation."
} as const;

export const yoyamicSourceMappings: Record<YoyamicReadEntity, YoyamicSourceMapping> = {
  companies: {
    entity: "companies",
    sourceTable: "tb_company",
    sourceIdColumn: "CompanyID",
    updatedAtColumn: "ModifiedDate",
    orderBy: "CompanyID"
  },
  contacts: {
    entity: "contacts",
    sourceTable: "tb_company_contact",
    sourceIdColumn: "ContactID",
    updatedAtColumn: "ModifiedDate",
    orderBy: "ContactID"
  },
  parts: {
    entity: "parts",
    sourceTable: "tbl_Parts",
    sourceIdColumn: "PartID",
    updatedAtColumn: "ModifiedDate",
    orderBy: "PartID"
  },
  stock: {
    entity: "stock",
    sourceTable: "tb_stock_part",
    sourceIdColumn: "StockID",
    updatedAtColumn: "ModifiedDate",
    orderBy: "StockID"
  }
};

function stripSqlComments(sql: string): string {
  return sql.replace(/\/\*[\s\S]*?\*\//g, " ").replace(/--.*$/gm, " ");
}

function firstSqlKeyword(sql: string): string {
  return stripSqlComments(sql).trim().match(/^[a-z]+/i)?.[0].toUpperCase() ?? "";
}

function singleStatement(sql: string): boolean {
  const withoutComments = stripSqlComments(sql).trim();
  if (!withoutComments) return false;
  const semicolons = [...withoutComments.matchAll(/;/g)];
  return semicolons.length === 0 || (semicolons.length === 1 && withoutComments.endsWith(";"));
}

function withoutTrailingSemicolon(sql: string): string {
  return sql.trim().replace(/;\s*$/, "");
}

export function normalizeYoyamicReadOptions(options: YoyamicReadOptions): Required<YoyamicReadOptions> {
  if (!options.tenantId.trim()) {
    throw new Error("tenantId is required for Yoyamic read-only imports.");
  }
  if (!Number.isInteger(options.limit) || options.limit < 1 || options.limit > yoyamicReadonlyPolicy.maximumLimit) {
    throw new Error(`limit must be between 1 and ${yoyamicReadonlyPolicy.maximumLimit}.`);
  }
  if (!Number.isInteger(options.timeoutMs) || options.timeoutMs < 100 || options.timeoutMs > 60_000) {
    throw new Error("timeoutMs must be between 100 and 60000.");
  }
  const offset = options.offset ?? 0;
  if (!Number.isInteger(offset) || offset < 0 || offset > yoyamicReadonlyPolicy.maximumOffset) {
    throw new Error(`offset must be between 0 and ${yoyamicReadonlyPolicy.maximumOffset}.`);
  }
  return {
    tenantId: options.tenantId.trim(),
    limit: options.limit,
    timeoutMs: options.timeoutMs,
    since: options.since ?? "",
    offset
  };
}

export function assertSafeYoyamicReadOptions(options: YoyamicReadOptions): void {
  normalizeYoyamicReadOptions(options);
}

export function assertYoyamicSelectOnly(sql: string): void {
  const normalized = stripSqlComments(sql);
  const keyword = firstSqlKeyword(normalized);

  if (!yoyamicReadonlyPolicy.allowedSql.includes(keyword as "SELECT" | "SHOW")) {
    throw new Error("Yoyamic read adapter only allows SELECT/SHOW statements.");
  }

  if (!singleStatement(normalized)) {
    throw new Error("Yoyamic read adapter only allows one statement at a time.");
  }

  const forbiddenPattern = new RegExp(`\\b(${yoyamicReadonlyPolicy.forbiddenSql.join("|")})\\b`, "i");
  const withoutQuotedIdentifiers = normalized.replace(/`[^`]+`/g, "");
  if (forbiddenPattern.test(withoutQuotedIdentifiers)) {
    throw new Error("Yoyamic read adapter rejected a write-capable SQL statement.");
  }

  if (/\bFOR\s+UPDATE\b/i.test(normalized) || /\bLOCK\s+IN\s+SHARE\s+MODE\b/i.test(normalized)) {
    throw new Error("Yoyamic read adapter rejected a locking read.");
  }

  if (/\bINTO\s+(OUTFILE|DUMPFILE)\b/i.test(normalized)) {
    throw new Error("Yoyamic read adapter rejected a file-writing read.");
  }
}

export function buildYoyamicReadQuery(entity: YoyamicReadEntity, options: YoyamicReadOptions): YoyamicReadQueryPlan {
  const safeOptions = normalizeYoyamicReadOptions(options);
  const mapping = yoyamicSourceMappings[entity];
  const where = safeOptions.since && mapping.updatedAtColumn ? `WHERE ${mapping.updatedAtColumn} >= ?` : "";
  const params = safeOptions.since && mapping.updatedAtColumn ? [safeOptions.since] : [];
  const baseSql = `SELECT * FROM ${mapping.sourceTable} ${where} ORDER BY ${mapping.orderBy}`;
  assertYoyamicSelectOnly(baseSql);
  return {
    entity,
    sql: `${withoutTrailingSemicolon(baseSql)} LIMIT ? OFFSET ?`,
    params: [...params, safeOptions.limit, safeOptions.offset],
    limit: safeOptions.limit,
    offset: safeOptions.offset,
    timeoutMs: safeOptions.timeoutMs
  };
}

export function buildYoyamicBatchPlan(entity: YoyamicReadEntity, options: YoyamicReadOptions, batchCount: number): YoyamicReadQueryPlan[] {
  if (!Number.isInteger(batchCount) || batchCount < 1) {
    throw new Error("batchCount must be a positive integer.");
  }
  const safeOptions = normalizeYoyamicReadOptions(options);
  return Array.from({ length: batchCount }, (_unused, index) =>
    buildYoyamicReadQuery(entity, {
      ...safeOptions,
      offset: safeOptions.offset + index * safeOptions.limit
    })
  );
}

export function legacySourceChecksum(value: unknown): string {
  return createHash("sha256").update(JSON.stringify(value)).digest("hex");
}

export function buildLegacyMappingRecord(input: {
  tenantId: TenantId;
  sourceTable: string;
  sourceId: string | number;
  targetEntityType: CoreEntityType;
  targetEntityId: string;
  sourceUpdatedAt?: string;
  sourceRow?: unknown;
  importedAt?: string;
}): LegacyMappingRecord {
  if (!input.sourceTable.trim()) {
    throw new Error("sourceTable is required for a legacy mapping.");
  }
  if (!String(input.sourceId).trim()) {
    throw new Error("sourceId is required for a legacy mapping.");
  }
  if (!input.targetEntityId.trim()) {
    throw new Error("targetEntityId is required for a legacy mapping.");
  }
  return {
    sourceSystem: "yoyamic",
    sourceTable: input.sourceTable,
    sourceId: String(input.sourceId),
    tenantId: input.tenantId,
    targetEntityType: input.targetEntityType,
    targetEntityId: input.targetEntityId,
    importedAt: input.importedAt ?? new Date().toISOString(),
    sourceUpdatedAt: input.sourceUpdatedAt,
    checksum: input.sourceRow === undefined ? undefined : legacySourceChecksum(input.sourceRow)
  };
}

export function summarizeYoyamicReconciliation(tenantId: string, report: ImportDryRunReport): YoyamicReconciliationSummary {
  const sourceRows = report.entities.companies + report.entities.contacts + report.entities.parts + report.entities.stock;
  return {
    tenantId,
    sourceRows,
    candidateRows: report.inserted + report.updated,
    skippedRows: report.skipped,
    duplicateRows: report.duplicate,
    failedRows: report.failed,
    zeroQuantityRows: report.anomalies.filter((anomaly) => anomaly.startsWith("quantity_zero_stock=")).length,
    anomalyCount: report.anomalies.length,
    anomalyCodes: [...new Set(report.anomalies.map((anomaly) => anomaly.split("=")[0] ?? anomaly))].sort()
  };
}
