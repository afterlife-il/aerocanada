import { pathToFileURL } from "node:url";
import pg from "pg";

const { Pool } = pg;
const tenant = {
  id: "tenant-aci",
  code: "aci770",
  slug: "AeroCanada",
  name: "AEROCANADA INDUSTRIES 770 INC.",
  primaryCompanyId: "company-aci770-primary"
} as const;

async function seed(): Promise<void> {
  const connectionString = process.env.DATABASE_URL;
  if (!connectionString) throw new Error("DATABASE_URL is required for staging seed.");
  const pool = new Pool({ connectionString });
  const client = await pool.connect();
  try {
    await client.query("BEGIN");
    await client.query(
      `INSERT INTO tenants (id, code, slug, name, status)
       VALUES ($1, $2, $3, $4, 'active')
       ON CONFLICT (id) DO NOTHING`,
      [tenant.id, tenant.code, tenant.slug, tenant.name]
    );
    const tenantRow = await client.query<{ code: string | null; slug: string; name: string }>(
      "SELECT code, slug, name FROM tenants WHERE id=$1",
      [tenant.id]
    );
    const existingTenant = tenantRow.rows[0];
    if (!existingTenant || existingTenant.code !== tenant.code || existingTenant.slug !== tenant.slug || existingTenant.name !== tenant.name) {
      throw new Error("Existing tenant identity does not match the canonical aci770 seed.");
    }
    await client.query(
      `INSERT INTO companies (
         id, tenant_id, name, legal_name, code, status, risk, tags, created_by, updated_by
       ) VALUES ($1,$2,$3,$3,$4,'active','normal',$5,'staging-seed','staging-seed')
       ON CONFLICT (id) DO NOTHING`,
      [tenant.primaryCompanyId, tenant.id, tenant.name, tenant.code, ["initial-tenant", "Ready2Go"]]
    );
    const companyRow = await client.query<{ tenant_id: string; name: string }>(
      "SELECT tenant_id, name FROM companies WHERE id=$1",
      [tenant.primaryCompanyId]
    );
    if (companyRow.rows[0]?.tenant_id !== tenant.id || companyRow.rows[0]?.name !== tenant.name) {
      throw new Error("Existing primary company does not match the canonical aci770 seed.");
    }
    await client.query(
      `INSERT INTO company_roles (tenant_id, company_id, role)
       VALUES ($1,$2,'stock-owner') ON CONFLICT DO NOTHING`,
      [tenant.id, tenant.primaryCompanyId]
    );
    await client.query(
      "UPDATE tenants SET primary_company_id=$2 WHERE id=$1 AND primary_company_id IS NULL",
      [tenant.id, tenant.primaryCompanyId]
    );
    await client.query("COMMIT");
    console.log("Staging tenant seed verified for code aci770 and slug AeroCanada.");
  } catch (error) {
    await client.query("ROLLBACK");
    throw error;
  } finally {
    client.release();
    await pool.end();
  }
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  seed().catch((error) => {
    console.error(error instanceof Error ? error.message : "Staging seed failed.");
    process.exitCode = 1;
  });
}
