import type { LegacyYoyamicSnapshot } from "./yoyamic-core-importer.js";

export interface YoyamicReadOptions {
  tenantId: string;
  limit: number;
  timeoutMs: number;
  since?: string;
}

export interface YoyamicReadonlySource {
  readCompanies(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["companies"]>;
  readContacts(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["contacts"]>;
  readParts(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["parts"]>;
  readStock(options: YoyamicReadOptions): Promise<LegacyYoyamicSnapshot["stock"]>;
}

export const yoyamicReadonlyPolicy = {
  allowedSql: ["SELECT", "SHOW"],
  forbiddenSql: ["INSERT", "UPDATE", "DELETE", "ALTER", "DROP", "CREATE", "TRUNCATE", "REPLACE"],
  defaultTimeoutMs: 10_000,
  defaultLimit: 1_000,
  maximumLimit: 10_000,
  credentialPolicy: "Use environment variables only; never log connection strings, usernames, or passwords.",
  importPolicy: "Read from Yoyamic through this interface, then pass a snapshot to dryRunYoyamicCoreImport before writing to SaaS_Aviation."
} as const;

export function assertSafeYoyamicReadOptions(options: YoyamicReadOptions): void {
  if (!options.tenantId.trim()) {
    throw new Error("tenantId is required for Yoyamic read-only imports.");
  }
  if (!Number.isInteger(options.limit) || options.limit < 1 || options.limit > yoyamicReadonlyPolicy.maximumLimit) {
    throw new Error(`limit must be between 1 and ${yoyamicReadonlyPolicy.maximumLimit}.`);
  }
  if (!Number.isInteger(options.timeoutMs) || options.timeoutMs < 100 || options.timeoutMs > 60_000) {
    throw new Error("timeoutMs must be between 100 and 60000.");
  }
}
