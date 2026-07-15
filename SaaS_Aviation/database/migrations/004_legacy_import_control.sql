CREATE TABLE IF NOT EXISTS import_batches (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  source_system text NOT NULL CHECK (source_system = 'yoyamic'),
  mode text NOT NULL CHECK (mode IN ('sample', 'full')),
  status text NOT NULL CHECK (status IN ('running', 'completed', 'failed', 'rolled-back')),
  importer_version text NOT NULL,
  metadata jsonb NOT NULL DEFAULT '{}',
  source_counts jsonb NOT NULL DEFAULT '{}',
  imported_counts jsonb NOT NULL DEFAULT '{}',
  warning_count integer NOT NULL DEFAULT 0,
  error_count integer NOT NULL DEFAULT 0,
  started_at timestamptz NOT NULL DEFAULT now(),
  completed_at timestamptz
);

CREATE TABLE IF NOT EXISTS imported_records (
  id bigserial PRIMARY KEY,
  batch_id text NOT NULL REFERENCES import_batches(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  source_system text NOT NULL CHECK (source_system = 'yoyamic'),
  source_table text NOT NULL,
  source_primary_key text NOT NULL,
  source_checksum text NOT NULL,
  source_updated_at timestamptz,
  destination_entity text NOT NULL CHECK (destination_entity IN ('company', 'company-address', 'contact', 'part')),
  destination_id text NOT NULL,
  import_status text NOT NULL CHECK (import_status IN ('inserted', 'unchanged', 'updated', 'warning', 'quarantined')),
  warning_count integer NOT NULL DEFAULT 0,
  error_count integer NOT NULL DEFAULT 0,
  reconciliation_status text NOT NULL DEFAULT 'pending' CHECK (reconciliation_status IN ('pending', 'matched', 'mismatch', 'not-applicable')),
  field_mismatch_count integer NOT NULL DEFAULT 0,
  imported_at timestamptz NOT NULL DEFAULT now(),
  reconciled_at timestamptz,
  UNIQUE (tenant_id, source_system, source_table, source_primary_key, destination_entity)
);

CREATE TABLE IF NOT EXISTS import_quarantine (
  id bigserial PRIMARY KEY,
  batch_id text REFERENCES import_batches(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  source_system text NOT NULL CHECK (source_system = 'yoyamic'),
  source_table text NOT NULL,
  source_primary_key text NOT NULL,
  entity_type text NOT NULL CHECK (entity_type IN ('company', 'company-address', 'contact', 'part')),
  reason_code text NOT NULL,
  severity text NOT NULL CHECK (severity IN ('warning', 'manual-review', 'rejected')),
  source_checksum text NOT NULL,
  details jsonb NOT NULL DEFAULT '{}',
  created_at timestamptz NOT NULL DEFAULT now(),
  resolved_at timestamptz,
  UNIQUE (tenant_id, source_system, source_table, source_primary_key, entity_type, reason_code)
);

CREATE INDEX IF NOT EXISTS idx_import_batches_tenant_started ON import_batches (tenant_id, started_at DESC);
CREATE INDEX IF NOT EXISTS idx_imported_records_batch ON imported_records (batch_id, destination_entity, import_status);
CREATE INDEX IF NOT EXISTS idx_imported_records_destination ON imported_records (tenant_id, destination_entity, destination_id);
CREATE INDEX IF NOT EXISTS idx_import_quarantine_batch ON import_quarantine (batch_id, severity, entity_type);

CREATE UNIQUE INDEX IF NOT EXISTS idx_companies_tenant_legacy_unique
  ON companies (tenant_id, legacy_id) WHERE legacy_id IS NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS idx_contacts_tenant_legacy_unique
  ON contacts (tenant_id, legacy_id) WHERE legacy_id IS NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS idx_parts_tenant_legacy_unique
  ON part_numbers (tenant_id, legacy_id) WHERE legacy_id IS NOT NULL;
