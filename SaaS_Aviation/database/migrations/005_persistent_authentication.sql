CREATE TABLE IF NOT EXISTS auth_users (
  id text PRIMARY KEY,
  tenant_id text NOT NULL REFERENCES tenants(id),
  email text NOT NULL,
  name text NOT NULL,
  status text NOT NULL CHECK (status IN ('active', 'invited', 'disabled')),
  roles text[] NOT NULL DEFAULT '{}',
  permissions text[] NOT NULL DEFAULT '{}',
  mfa_enabled boolean NOT NULL DEFAULT false,
  failed_attempts integer NOT NULL DEFAULT 0,
  locked_until timestamptz,
  password_changed_at timestamptz,
  last_login_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (tenant_id, id)
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_auth_users_lower_email_unique ON auth_users (lower(email));

CREATE TABLE IF NOT EXISTS auth_credentials (
  user_id text PRIMARY KEY REFERENCES auth_users(id) ON DELETE CASCADE,
  password_hash text NOT NULL,
  password_salt text NOT NULL,
  algorithm text NOT NULL CHECK (algorithm = 'scrypt-v1'),
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS auth_sessions (
  id text PRIMARY KEY,
  user_id text NOT NULL REFERENCES auth_users(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  token_hash text NOT NULL UNIQUE,
  csrf_hash text NOT NULL,
  expires_at timestamptz NOT NULL,
  last_seen_at timestamptz NOT NULL DEFAULT now(),
  revoked_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  FOREIGN KEY (tenant_id, user_id) REFERENCES auth_users(tenant_id, id)
);

CREATE INDEX IF NOT EXISTS idx_auth_sessions_user_active ON auth_sessions (tenant_id, user_id, expires_at) WHERE revoked_at IS NULL;

CREATE TABLE IF NOT EXISTS auth_audit_events (
  id bigserial PRIMARY KEY,
  tenant_id text REFERENCES tenants(id),
  user_id text REFERENCES auth_users(id),
  category text NOT NULL CHECK (category IN ('login-success', 'login-failure', 'logout', 'revoke-all', 'lockout', 'password-change', 'mfa-change')),
  outcome text NOT NULL CHECK (outcome IN ('success', 'failure')),
  correlation_id text,
  metadata jsonb NOT NULL DEFAULT '{}',
  occurred_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_auth_audit_tenant_time ON auth_audit_events (tenant_id, occurred_at DESC);
