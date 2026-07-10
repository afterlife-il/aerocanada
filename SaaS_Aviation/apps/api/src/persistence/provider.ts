import type { CorePersistence } from "@saas-aviation/shared";
import { getPersistenceConfig, type PersistenceConfig } from "./config.js";
import { InMemoryCorePersistence } from "./core-memory-repository.js";
import { PostgresCorePersistence } from "./postgres-core-repository.js";

export interface CorePersistenceProvider {
  mode: PersistenceConfig["provider"];
  repository: CorePersistence;
  close(): Promise<void>;
}

export function createCorePersistenceProvider(config = getPersistenceConfig()): CorePersistenceProvider {
  if (config.provider === "memory") {
    return {
      mode: "memory",
      repository: new InMemoryCorePersistence(),
      async close() {
        return;
      }
    };
  }

  if (!config.postgres) {
    throw new Error("PostgreSQL persistence was selected but DATABASE_URL is not configured.");
  }

  const repository = new PostgresCorePersistence(config.postgres);
  return {
    mode: "postgres",
    repository,
    async close() {
      await repository.close();
    }
  };
}
