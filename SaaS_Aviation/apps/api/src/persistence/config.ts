export type PersistenceProvider = "memory" | "postgres";

export interface PostgresConfig {
  connectionString: string;
  poolMin: number;
  poolMax: number;
  ssl: boolean;
}

export interface PersistenceConfig {
  provider: PersistenceProvider;
  postgres: PostgresConfig | null;
}

function integerEnv(name: string, fallback: number): number {
  const value = process.env[name];
  if (!value) return fallback;
  const parsed = Number.parseInt(value, 10);
  if (!Number.isFinite(parsed) || parsed < 0) {
    throw new Error(`${name} must be a non-negative integer.`);
  }
  return parsed;
}

export function getPersistenceConfig(env: NodeJS.ProcessEnv = process.env): PersistenceConfig {
  const provider = (env.PERSISTENCE_PROVIDER ?? env.DATA_SOURCE_MODE ?? "memory") as PersistenceProvider;
  if (provider !== "memory" && provider !== "postgres") {
    throw new Error("PERSISTENCE_PROVIDER must be memory or postgres.");
  }

  if (provider === "memory") {
    return { provider, postgres: null };
  }

  const connectionString = env.DATABASE_URL;
  if (!connectionString) {
    throw new Error("DATABASE_URL is required when PERSISTENCE_PROVIDER=postgres.");
  }

  return {
    provider,
    postgres: {
      connectionString,
      poolMin: integerEnv("DATABASE_POOL_MIN", 0),
      poolMax: integerEnv("DATABASE_POOL_MAX", 10),
      ssl: env.DATABASE_SSL === "true"
    }
  };
}

export function redactDatabaseUrl(value: string): string {
  try {
    const url = new URL(value);
    if (url.password) url.password = "redacted";
    if (url.username) url.username = "redacted";
    return url.toString();
  } catch {
    return "redacted";
  }
}
