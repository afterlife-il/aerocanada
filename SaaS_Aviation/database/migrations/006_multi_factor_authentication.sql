CREATE TABLE IF NOT EXISTS auth_totp_factors (
  user_id text PRIMARY KEY REFERENCES auth_users(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  secret_ciphertext text NOT NULL,
  enabled_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  FOREIGN KEY (tenant_id, user_id) REFERENCES auth_users(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS auth_recovery_codes (
  id text PRIMARY KEY,
  user_id text NOT NULL REFERENCES auth_users(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  code_hash text NOT NULL,
  used_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (user_id, code_hash),
  FOREIGN KEY (tenant_id, user_id) REFERENCES auth_users(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS auth_phone_factors (
  user_id text PRIMARY KEY REFERENCES auth_users(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  phone_e164 text NOT NULL,
  verified_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (tenant_id, phone_e164),
  FOREIGN KEY (tenant_id, user_id) REFERENCES auth_users(tenant_id, id)
);

CREATE TABLE IF NOT EXISTS auth_otp_challenges (
  id text PRIMARY KEY,
  user_id text NOT NULL REFERENCES auth_users(id) ON DELETE CASCADE,
  tenant_id text NOT NULL REFERENCES tenants(id),
  purpose text NOT NULL CHECK (purpose IN ('phone-enrollment', 'login-mfa')),
  channel text NOT NULL CHECK (channel IN ('phone', 'totp', 'recovery')),
  code_hash text,
  attempt_count integer NOT NULL DEFAULT 0,
  max_attempts integer NOT NULL DEFAULT 5,
  expires_at timestamptz NOT NULL,
  resend_available_at timestamptz,
  consumed_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  FOREIGN KEY (tenant_id, user_id) REFERENCES auth_users(tenant_id, id)
);

CREATE INDEX IF NOT EXISTS idx_auth_otp_active ON auth_otp_challenges (tenant_id, user_id, purpose, expires_at DESC) WHERE consumed_at IS NULL;

ALTER TABLE auth_audit_events DROP CONSTRAINT IF EXISTS auth_audit_events_category_check;
ALTER TABLE auth_audit_events ADD CONSTRAINT auth_audit_events_category_check CHECK (category IN (
  'login-success', 'login-failure', 'logout', 'revoke-all', 'lockout', 'password-change', 'mfa-change',
  'totp-enroll', 'totp-disable', 'mfa-challenge-success', 'mfa-challenge-failure',
  'phone-enroll', 'phone-otp-request', 'phone-otp-success', 'phone-otp-failure', 'recovery-code-used'
));
