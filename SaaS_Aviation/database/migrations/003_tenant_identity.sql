ALTER TABLE tenants
  ADD COLUMN IF NOT EXISTS code text,
  ADD COLUMN IF NOT EXISTS primary_company_id text;

CREATE UNIQUE INDEX IF NOT EXISTS idx_tenants_lower_code_unique
  ON tenants (lower(code)) WHERE code IS NOT NULL;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conname = 'tenants_primary_company_id_fkey'
      AND conrelid = 'tenants'::regclass
  ) THEN
    ALTER TABLE tenants
      ADD CONSTRAINT tenants_primary_company_id_fkey
      FOREIGN KEY (id, primary_company_id) REFERENCES companies(tenant_id, id);
  END IF;
END
$$;
