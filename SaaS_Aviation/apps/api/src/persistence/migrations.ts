import { createHash } from "node:crypto";
import { readdir, readFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath, pathToFileURL } from "node:url";
import pg from "pg";
import { getPersistenceConfig, type PostgresConfig } from "./config.js";

const { Pool } = pg;

export interface MigrationStatusRow {
  id: string;
  applied: boolean;
  checksum: string;
  appliedAt: string | null;
}

function migrationsDirectory(): string {
  return process.env.MIGRATIONS_DIR ?? fileURLToPath(new URL("../../../../database/migrations/", import.meta.url));
}

function checksum(sql: string): string {
  return createHash("sha256").update(sql).digest("hex");
}

function createPool(config: PostgresConfig): pg.Pool {
  return new Pool({
    connectionString: config.connectionString,
    min: config.poolMin,
    max: config.poolMax,
    ssl: config.ssl ? { rejectUnauthorized: true } : undefined
  });
}

async function loadMigrations(): Promise<Array<{ id: string; sql: string; checksum: string }>> {
  const dir = migrationsDirectory();
  const files = (await readdir(dir)).filter((file) => file.endsWith(".sql")).sort((left, right) => left.localeCompare(right));
  const migrations = [];
  for (const file of files) {
    const sql = await readFile(path.join(dir, file), "utf8");
    migrations.push({ id: file, sql, checksum: checksum(sql) });
  }
  return migrations;
}

async function ensureLedger(client: pg.PoolClient): Promise<void> {
  await client.query(`
    CREATE TABLE IF NOT EXISTS schema_migrations (
      id text PRIMARY KEY,
      checksum text NOT NULL,
      applied_at timestamptz NOT NULL DEFAULT now()
    )
  `);
}

export async function getMigrationStatus(config = getPersistenceConfig()): Promise<MigrationStatusRow[]> {
  if (!config.postgres) {
    throw new Error("Migration status requires PERSISTENCE_PROVIDER=postgres and DATABASE_URL.");
  }
  const pool = createPool(config.postgres);
  try {
    const migrations = await loadMigrations();
    const client = await pool.connect();
    try {
      await ensureLedger(client);
      const applied = await client.query<{ id: string; checksum: string; applied_at: Date }>(
        "SELECT id, checksum, applied_at FROM schema_migrations ORDER BY id"
      );
      const appliedById = new Map(applied.rows.map((row) => [row.id, row]));
      return migrations.map((migration) => {
        const row = appliedById.get(migration.id);
        return {
          id: migration.id,
          applied: Boolean(row),
          checksum: migration.checksum,
          appliedAt: row?.applied_at.toISOString() ?? null
        };
      });
    } finally {
      client.release();
    }
  } finally {
    await pool.end();
  }
}

export async function applyMigrations(config = getPersistenceConfig()): Promise<MigrationStatusRow[]> {
  if (!config.postgres) {
    throw new Error("Migration apply requires PERSISTENCE_PROVIDER=postgres and DATABASE_URL.");
  }
  const pool = createPool(config.postgres);
  try {
    const migrations = await loadMigrations();
    const client = await pool.connect();
    try {
      await ensureLedger(client);
      for (const migration of migrations) {
        await client.query("BEGIN");
        try {
          const applied = await client.query<{ checksum: string }>("SELECT checksum FROM schema_migrations WHERE id = $1 FOR UPDATE", [migration.id]);
          const existing = applied.rows[0];
          if (existing) {
            if (existing.checksum !== migration.checksum) {
              throw new Error(`Migration checksum mismatch for ${migration.id}.`);
            }
            await client.query("COMMIT");
            continue;
          }
          await client.query(migration.sql);
          await client.query("INSERT INTO schema_migrations (id, checksum) VALUES ($1, $2)", [migration.id, migration.checksum]);
          await client.query("COMMIT");
        } catch (error) {
          await client.query("ROLLBACK");
          throw error;
        }
      }
    } finally {
      client.release();
    }
  } finally {
    await pool.end();
  }
  return getMigrationStatus(config);
}

async function main(): Promise<void> {
  const command = process.argv[2];
  if (command !== "status" && command !== "apply") {
    throw new Error("Usage: tsx src/persistence/migrations.ts <status|apply>");
  }
  const rows = command === "status" ? await getMigrationStatus() : await applyMigrations();
  console.table(rows);
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  main().catch((error) => {
    console.error(error instanceof Error ? error.message : "Migration command failed.");
    process.exitCode = 1;
  });
}
