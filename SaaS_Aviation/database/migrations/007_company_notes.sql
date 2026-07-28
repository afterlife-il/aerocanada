CREATE TABLE IF NOT EXISTS company_notes (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  company_id text NOT NULL,
  body text NOT NULL CHECK (length(trim(body)) BETWEEN 1 AND 5000),
  pinned boolean NOT NULL DEFAULT false,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by text NOT NULL,
  updated_by text NOT NULL,
  UNIQUE (tenant_id, id),
  FOREIGN KEY (tenant_id, company_id) REFERENCES companies(tenant_id, id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_company_notes_timeline
  ON company_notes (tenant_id, company_id, pinned DESC, updated_at DESC);
