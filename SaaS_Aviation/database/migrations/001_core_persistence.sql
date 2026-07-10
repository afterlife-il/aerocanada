-- SaaS_Aviation Persistent Data Foundation Phase 1
-- PostgreSQL-compatible schema for dedicated SaaS_Aviation persistence.
-- This migration is not deployed by this sprint.

CREATE TABLE IF NOT EXISTS schema_migrations (
  id text PRIMARY KEY,
  checksum text NOT NULL,
  applied_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS tenants (
  id text PRIMARY KEY,
  name text NOT NULL,
  slug text NOT NULL UNIQUE,
  status text NOT NULL CHECK (status IN ('active', 'suspended')),
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS companies (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  legacy_id text,
  name text NOT NULL,
  legal_name text,
  code text,
  status text NOT NULL CHECK (status IN ('active', 'inactive', 'blocked')),
  email text,
  phone text,
  website text,
  address_line_1 text,
  address_line_2 text,
  city text,
  state text,
  postal_code text,
  country text,
  risk text NOT NULL CHECK (risk IN ('normal', 'watch', 'blocked')),
  notes text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  UNIQUE (tenant_id, id)
);

CREATE TABLE IF NOT EXISTS company_roles (
  tenant_id text NOT NULL REFERENCES tenants(id),
  company_id text NOT NULL,
  role text NOT NULL CHECK (
    role IN (
      'customer',
      'supplier',
      'repair-station',
      'airline',
      'manufacturer',
      'broker',
      'distributor',
      'government',
      'military',
      'stock-owner',
      'consignment-owner'
    )
  ),
  created_at timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (tenant_id, company_id, role),
  FOREIGN KEY (tenant_id, company_id) REFERENCES companies(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS contacts (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  company_id text NOT NULL,
  legacy_id text,
  first_name text NOT NULL,
  last_name text NOT NULL,
  job_title text,
  email text,
  phone text,
  mobile text,
  preferred_language text,
  timezone text,
  status text NOT NULL CHECK (status IN ('active', 'inactive')),
  notes text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  FOREIGN KEY (tenant_id, company_id) REFERENCES companies(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS part_numbers (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  legacy_id text,
  part_number text NOT NULL,
  normalized_part_number text NOT NULL,
  description text NOT NULL,
  manufacturer text,
  manufacturer_code text,
  ata text,
  ipc text,
  aircraft text[] NOT NULL DEFAULT '{}',
  status text NOT NULL CHECK (status IN ('active', 'inactive')),
  alternates text[] NOT NULL DEFAULT '{}',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  UNIQUE (tenant_id, id)
);

CREATE TABLE IF NOT EXISTS part_alternates (
  tenant_id text NOT NULL REFERENCES tenants(id),
  part_id text NOT NULL,
  alternate_part_number text NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (tenant_id, part_id, alternate_part_number),
  FOREIGN KEY (tenant_id, part_id) REFERENCES part_numbers(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS stock_items (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  legacy_id text,
  part_id text NOT NULL,
  serial_number text,
  quantity numeric(14, 3) NOT NULL CHECK (quantity >= 0),
  condition text,
  release_type text,
  status text NOT NULL,
  location_text text,
  owner_company_id text,
  supplier_company_id text,
  tag_info_company_id text,
  traceability_company_id text,
  acquisition_cost numeric(14, 2),
  quoted_value numeric(14, 2),
  currency char(3),
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  FOREIGN KEY (tenant_id, part_id) REFERENCES part_numbers(tenant_id, id),
  FOREIGN KEY (tenant_id, owner_company_id) REFERENCES companies(tenant_id, id),
  FOREIGN KEY (tenant_id, supplier_company_id) REFERENCES companies(tenant_id, id),
  FOREIGN KEY (tenant_id, tag_info_company_id) REFERENCES companies(tenant_id, id),
  FOREIGN KEY (tenant_id, traceability_company_id) REFERENCES companies(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS legacy_mappings (
  source_system text NOT NULL,
  source_table text NOT NULL,
  source_id text NOT NULL,
  tenant_id text NOT NULL REFERENCES tenants(id),
  target_entity_type text NOT NULL CHECK (target_entity_type IN ('company', 'contact', 'part', 'stock')),
  target_entity_id text NOT NULL,
  imported_at timestamptz NOT NULL DEFAULT now(),
  source_updated_at timestamptz,
  checksum text,
  PRIMARY KEY (source_system, source_table, source_id, tenant_id, target_entity_type)
);

CREATE INDEX IF NOT EXISTS idx_companies_tenant_name ON companies (tenant_id, name);
CREATE UNIQUE INDEX IF NOT EXISTS idx_companies_tenant_lower_name_unique ON companies (tenant_id, lower(name));
CREATE INDEX IF NOT EXISTS idx_company_roles_tenant_role ON company_roles (tenant_id, role);
CREATE INDEX IF NOT EXISTS idx_contacts_tenant_company ON contacts (tenant_id, company_id);
CREATE INDEX IF NOT EXISTS idx_parts_tenant_normalized ON part_numbers (tenant_id, normalized_part_number);
CREATE UNIQUE INDEX IF NOT EXISTS idx_parts_tenant_normalized_manufacturer_unique
  ON part_numbers (tenant_id, normalized_part_number, COALESCE(manufacturer_code, manufacturer, ''));
CREATE INDEX IF NOT EXISTS idx_part_alternates_tenant_part ON part_alternates (tenant_id, part_id);
CREATE INDEX IF NOT EXISTS idx_stock_tenant_part ON stock_items (tenant_id, part_id);
CREATE INDEX IF NOT EXISTS idx_stock_tenant_owner ON stock_items (tenant_id, owner_company_id);
CREATE INDEX IF NOT EXISTS idx_stock_tenant_supplier ON stock_items (tenant_id, supplier_company_id);
CREATE INDEX IF NOT EXISTS idx_stock_tenant_tag_info ON stock_items (tenant_id, tag_info_company_id);
CREATE INDEX IF NOT EXISTS idx_stock_tenant_traceability ON stock_items (tenant_id, traceability_company_id);
