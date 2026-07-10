export type DataSourceMode = "sample-static" | "persistent-api";

export interface DataSourceConfig {
  mode: DataSourceMode;
  apiBaseUrl: string | null;
  staticExport: boolean;
}

export function getDataSourceConfig(): DataSourceConfig {
  const mode = (process.env.NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE ?? "sample-static") as DataSourceMode;
  const apiBaseUrl = process.env.NEXT_PUBLIC_SAAS_API_BASE_URL ?? null;

  if (mode === "persistent-api" && !apiBaseUrl) {
    throw new Error("NEXT_PUBLIC_SAAS_API_BASE_URL is required when NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE=persistent-api.");
  }

  return {
    mode,
    apiBaseUrl,
    staticExport: true
  };
}

export function assertPersistentApiMode(config = getDataSourceConfig()): asserts config is DataSourceConfig & { apiBaseUrl: string } {
  if (config.mode !== "persistent-api" || !config.apiBaseUrl) {
    throw new Error("Persistent API mode is not enabled. The static frontend must not silently fall back to sample data.");
  }
}
