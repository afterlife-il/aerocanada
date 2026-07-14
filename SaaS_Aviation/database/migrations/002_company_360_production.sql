ALTER TABLE companies
  ADD COLUMN IF NOT EXISTS icao_code text,
  ADD COLUMN IF NOT EXISTS iata_code text,
  ADD COLUMN IF NOT EXISTS vat_number text,
  ADD COLUMN IF NOT EXISTS tags text[] NOT NULL DEFAULT '{}';

CREATE TABLE IF NOT EXISTS company_addresses (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  company_id text NOT NULL,
  label text NOT NULL,
  address_line_1 text NOT NULL,
  address_line_2 text,
  city text,
  state text,
  postal_code text,
  country text NOT NULL,
  is_primary boolean NOT NULL DEFAULT false,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  UNIQUE (tenant_id, id),
  FOREIGN KEY (tenant_id, company_id) REFERENCES companies(tenant_id, id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_company_addresses_one_primary
  ON company_addresses (tenant_id, company_id) WHERE is_primary;
CREATE INDEX IF NOT EXISTS idx_company_addresses_company
  ON company_addresses (tenant_id, company_id);

CREATE TABLE IF NOT EXISTS company_activity (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  company_id text NOT NULL,
  category text NOT NULL CHECK (category IN ('company', 'contact', 'rfq', 'supplier-quote', 'customer-quote', 'purchase-order', 'sales-order', 'stock', 'document')),
  action text NOT NULL,
  summary text NOT NULL,
  reference_id text,
  occurred_at timestamptz NOT NULL DEFAULT now(),
  actor_id text NOT NULL,
  FOREIGN KEY (tenant_id, company_id) REFERENCES companies(tenant_id, id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_company_activity_timeline
  ON company_activity (tenant_id, company_id, occurred_at DESC);
CREATE INDEX IF NOT EXISTS idx_companies_search
  ON companies (tenant_id, lower(name));
