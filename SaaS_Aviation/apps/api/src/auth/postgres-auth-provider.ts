import { createHash, randomBytes, randomUUID, scrypt as scryptCallback, timingSafeEqual } from "node:crypto";
import { promisify } from "node:util";
import { Pool } from "pg";
import type { AuthSession, Permission, Role, Tenant, User } from "@saas-aviation/shared";
import { sampleUsers } from "@saas-aviation/shared";
import type { AuthProvider, AuthenticatedUser, PasswordAuthenticationResult, PhoneOtpRequestResult, TotpEnrollment } from "./auth-provider.js";
import type { PostgresConfig } from "../persistence/config.js";
import { decryptSecret, encryptSecret, generateTotpSecret, secretDigest, verifyTotp } from "./mfa-crypto.js";
import { StagingSpoolPhoneOtpProvider } from "./phone-otp-provider.js";

const scrypt = promisify(scryptCallback);
const SESSION_TTL_MS = 1000 * 60 * 60 * 8;
const LOCK_THRESHOLD = 5;
const LOCK_MS = 1000 * 60 * 15;

function digest(value: string): string { return createHash("sha256").update(value).digest("hex"); }
async function passwordDigest(password: string, salt: string): Promise<string> {
  return (await scrypt(password, salt, 64) as Buffer).toString("hex");
}

interface AuthRow {
  id: string; tenant_id: string; email: string; name: string; status: User["status"]; roles: Role[]; permissions: Permission[];
  mfa_enabled: boolean; failed_attempts: number; locked_until: Date | null; password_hash: string; password_salt: string;
  tenant_name: string; tenant_code: string | null; tenant_slug: string; tenant_status: Tenant["status"]; primary_company_id: string | null; created_at: Date;
}

export class PostgresAuthProvider implements AuthProvider {
  private readonly pool: Pool;
  private initialized: Promise<void> | null = null;

  constructor(private readonly config: PostgresConfig, private readonly env: NodeJS.ProcessEnv = process.env) {
    this.pool = new Pool({ connectionString: config.connectionString, min: config.poolMin, max: config.poolMax, ssl: config.ssl ? { rejectUnauthorized: true } : false });
  }

  private ensureInitialized(): Promise<void> {
    this.initialized ??= this.seedStagingAdministrator();
    return this.initialized;
  }

  private async seedStagingAdministrator(): Promise<void> {
    const email = this.env.STAGING_ADMIN_EMAIL?.trim().toLowerCase();
    const password = this.env.STAGING_ADMIN_PASSWORD;
    if (!email || !password || password.length < 16) throw new Error("Persistent staging authentication requires STAGING_ADMIN_EMAIL and a password of at least 16 characters.");
    const template = sampleUsers[0];
    if (!template) throw new Error("Staging administrator template is unavailable.");
    const tenant = await this.pool.query<{ id: string }>("SELECT id FROM tenants WHERE lower(code)=lower('aci770') LIMIT 1");
    const tenantId = tenant.rows[0]?.id;
    if (!tenantId) throw new Error("Tenant aci770 is unavailable for authentication bootstrap.");
    const existing = await this.pool.query("SELECT 1 FROM auth_users WHERE lower(email)=lower($1)", [email]);
    if (existing.rowCount) return;
    const salt = randomBytes(32).toString("hex");
    const hash = await passwordDigest(password, salt);
    const client = await this.pool.connect();
    try {
      await client.query("BEGIN");
      await client.query(
        `INSERT INTO auth_users (id,tenant_id,email,name,status,roles,permissions,mfa_enabled,password_changed_at)
         VALUES ($1,$2,$3,$4,'active',$5,$6,false,now())`,
        [template.id, tenantId, email, template.name, template.roles, template.permissions]
      );
      await client.query("INSERT INTO auth_credentials (user_id,password_hash,password_salt,algorithm) VALUES ($1,$2,$3,'scrypt-v1')", [template.id, hash, salt]);
      await client.query("COMMIT");
    } catch (error) { await client.query("ROLLBACK"); throw error; }
    finally { client.release(); }
  }

  private async audit(category: string, outcome: "success" | "failure", tenantId?: string, userId?: string): Promise<void> {
    await this.pool.query("INSERT INTO auth_audit_events (tenant_id,user_id,category,outcome) VALUES ($1,$2,$3,$4)", [tenantId ?? null, userId ?? null, category, outcome]);
  }

  async authenticateWithPassword(email: string, password: string): Promise<AuthSession | null> {
    const result = await this.beginPasswordAuthentication(email, password);
    return result && "token" in result ? result : null;
  }

  async beginPasswordAuthentication(email: string, password: string): Promise<PasswordAuthenticationResult | null> {
    await this.ensureInitialized();
    const result = await this.pool.query<AuthRow>(
      `SELECT u.*,c.password_hash,c.password_salt,t.name tenant_name,t.code tenant_code,t.slug tenant_slug,t.status tenant_status,t.primary_company_id
       FROM auth_users u JOIN auth_credentials c ON c.user_id=u.id JOIN tenants t ON t.id=u.tenant_id
       WHERE lower(u.email)=lower($1) LIMIT 1`, [email.trim()]
    );
    const row = result.rows[0];
    if (!row || row.status !== "active" || row.tenant_status !== "active") { await this.audit("login-failure", "failure"); return null; }
    if (row.locked_until && row.locked_until.getTime() > Date.now()) { await this.audit("lockout", "failure", row.tenant_id, row.id); return null; }
    const candidate = Buffer.from(await passwordDigest(password, row.password_salt), "hex");
    const expected = Buffer.from(row.password_hash, "hex");
    if (candidate.length !== expected.length || !timingSafeEqual(candidate, expected)) {
      const attempts = row.failed_attempts + 1;
      await this.pool.query("UPDATE auth_users SET failed_attempts=$2::integer,locked_until=CASE WHEN $2::integer >= $3::integer THEN now()+($4::integer * interval '1 millisecond') ELSE NULL END,updated_at=now() WHERE id=$1", [row.id, attempts, LOCK_THRESHOLD, LOCK_MS]);
      await this.audit(attempts >= LOCK_THRESHOLD ? "lockout" : "login-failure", "failure", row.tenant_id, row.id);
      return null;
    }
    await this.pool.query("UPDATE auth_users SET failed_attempts=0,locked_until=NULL,last_login_at=now(),updated_at=now() WHERE id=$1", [row.id]);
    if (row.mfa_enabled) {
      const challengeId = randomUUID(); const expiresAt = new Date(Date.now() + 5 * 60_000);
      await this.pool.query("INSERT INTO auth_otp_challenges (id,user_id,tenant_id,purpose,channel,expires_at) VALUES ($1,$2,$3,'login-mfa','totp',$4)", [challengeId, row.id, row.tenant_id, expiresAt]);
      return { mfaRequired: true, challengeId, methods: ["totp", "recovery"], expiresAt: expiresAt.toISOString() };
    }
    return this.issueSession(row);
  }

  private async issueSession(row: AuthRow): Promise<AuthSession> {
    const token = randomBytes(32).toString("base64url");
    const csrfToken = randomBytes(32).toString("base64url");
    const expiresAt = new Date(Date.now() + SESSION_TTL_MS);
    await this.pool.query("INSERT INTO auth_sessions (id,user_id,tenant_id,token_hash,csrf_hash,expires_at) VALUES ($1,$2,$3,$4,$5,$6)", [randomUUID(), row.id, row.tenant_id, digest(token), digest(csrfToken), expiresAt]);
    await this.audit("login-success", "success", row.tenant_id, row.id);
    return { token, csrfToken, user: this.user(row), tenant: this.tenant(row), expiresAt: expiresAt.toISOString() };
  }

  async beginTotpEnrollment(userId: string, tenantId: string): Promise<TotpEnrollment> {
    await this.ensureInitialized(); const key = this.env.AUTH_ENCRYPTION_KEY; if (!key) throw new Error("AUTH_ENCRYPTION_KEY is required for MFA.");
    const secret = generateTotpSecret();
    await this.pool.query(`INSERT INTO auth_totp_factors (user_id,tenant_id,secret_ciphertext,enabled_at) VALUES ($1,$2,$3,NULL)
      ON CONFLICT (user_id) DO UPDATE SET secret_ciphertext=excluded.secret_ciphertext,enabled_at=NULL,updated_at=now()`, [userId, tenantId, encryptSecret(secret, key)]);
    const email = (await this.pool.query<{ email: string }>("SELECT email FROM auth_users WHERE id=$1 AND tenant_id=$2", [userId, tenantId])).rows[0]?.email ?? userId;
    await this.audit("totp-enroll", "success", tenantId, userId);
    return { secret, otpauthUri: `otpauth://totp/Ready2Go:${encodeURIComponent(email)}?secret=${secret}&issuer=Ready2Go&period=30&digits=6` };
  }

  async confirmTotpEnrollment(userId: string, tenantId: string, code: string): Promise<string[] | null> {
    await this.ensureInitialized(); const key = this.env.AUTH_ENCRYPTION_KEY; if (!key) throw new Error("AUTH_ENCRYPTION_KEY is required for MFA.");
    const factor = (await this.pool.query<{ secret_ciphertext: string }>("SELECT secret_ciphertext FROM auth_totp_factors WHERE user_id=$1 AND tenant_id=$2 AND enabled_at IS NULL", [userId, tenantId])).rows[0];
    if (!factor || !verifyTotp(decryptSecret(factor.secret_ciphertext, key), code)) { await this.audit("mfa-challenge-failure", "failure", tenantId, userId); return null; }
    const codes = Array.from({ length: 10 }, () => randomBytes(6).toString("hex").toUpperCase()); const client = await this.pool.connect();
    try { await client.query("BEGIN"); await client.query("DELETE FROM auth_recovery_codes WHERE user_id=$1", [userId]);
      for (const recovery of codes) await client.query("INSERT INTO auth_recovery_codes (id,user_id,tenant_id,code_hash) VALUES ($1,$2,$3,$4)", [randomUUID(), userId, tenantId, secretDigest(recovery)]);
      await client.query("UPDATE auth_totp_factors SET enabled_at=now(),updated_at=now() WHERE user_id=$1 AND tenant_id=$2", [userId, tenantId]);
      await client.query("UPDATE auth_users SET mfa_enabled=true,updated_at=now() WHERE id=$1 AND tenant_id=$2", [userId, tenantId]); await client.query("COMMIT");
    } catch (error) { await client.query("ROLLBACK"); throw error; } finally { client.release(); }
    await this.audit("mfa-change", "success", tenantId, userId); return codes;
  }

  async completeMfaChallenge(challengeId: string, code: string): Promise<AuthSession | null> {
    await this.ensureInitialized(); const key = this.env.AUTH_ENCRYPTION_KEY; if (!key) throw new Error("AUTH_ENCRYPTION_KEY is required for MFA.");
    const result = await this.pool.query<AuthRow & { challenge_id: string; attempt_count: number; max_attempts: number; secret_ciphertext: string }>(
      `SELECT u.*,c.password_hash,c.password_salt,t.name tenant_name,t.code tenant_code,t.slug tenant_slug,t.status tenant_status,t.primary_company_id,
       q.id challenge_id,q.attempt_count,q.max_attempts,f.secret_ciphertext FROM auth_otp_challenges q JOIN auth_users u ON u.id=q.user_id AND u.tenant_id=q.tenant_id
       JOIN auth_credentials c ON c.user_id=u.id JOIN tenants t ON t.id=u.tenant_id JOIN auth_totp_factors f ON f.user_id=u.id
       WHERE q.id=$1 AND q.purpose='login-mfa' AND q.consumed_at IS NULL AND q.expires_at>now() AND q.attempt_count<q.max_attempts`, [challengeId]);
    const row = result.rows[0]; if (!row) return null;
    let valid = verifyTotp(decryptSecret(row.secret_ciphertext, key), code);
    if (!valid) {
      const recovery = await this.pool.query<{ id: string }>("SELECT id FROM auth_recovery_codes WHERE user_id=$1 AND code_hash=$2 AND used_at IS NULL", [row.id, secretDigest(code.trim().toUpperCase())]);
      if (recovery.rows[0]) { valid = true; await this.pool.query("UPDATE auth_recovery_codes SET used_at=now() WHERE id=$1", [recovery.rows[0].id]); await this.audit("recovery-code-used", "success", row.tenant_id, row.id); }
    }
    if (!valid) { await this.pool.query("UPDATE auth_otp_challenges SET attempt_count=attempt_count+1 WHERE id=$1", [challengeId]); await this.audit("mfa-challenge-failure", "failure", row.tenant_id, row.id); return null; }
    await this.pool.query("UPDATE auth_otp_challenges SET consumed_at=now() WHERE id=$1", [challengeId]); await this.audit("mfa-challenge-success", "success", row.tenant_id, row.id); return this.issueSession(row);
  }

  async disableTotp(userId: string, tenantId: string, code: string): Promise<boolean> {
    await this.ensureInitialized(); const key = this.env.AUTH_ENCRYPTION_KEY; if (!key) throw new Error("AUTH_ENCRYPTION_KEY is required for MFA.");
    const factor = (await this.pool.query<{ secret_ciphertext: string }>("SELECT secret_ciphertext FROM auth_totp_factors WHERE user_id=$1 AND tenant_id=$2 AND enabled_at IS NOT NULL", [userId, tenantId])).rows[0];
    if (!factor || !verifyTotp(decryptSecret(factor.secret_ciphertext, key), code)) return false;
    const client = await this.pool.connect(); try { await client.query("BEGIN"); await client.query("DELETE FROM auth_totp_factors WHERE user_id=$1", [userId]); await client.query("DELETE FROM auth_recovery_codes WHERE user_id=$1", [userId]); await client.query("UPDATE auth_users SET mfa_enabled=false,updated_at=now() WHERE id=$1 AND tenant_id=$2", [userId, tenantId]); await client.query("COMMIT"); } catch (error) { await client.query("ROLLBACK"); throw error; } finally { client.release(); }
    await this.audit("totp-disable", "success", tenantId, userId); return true;
  }

  async requestPhoneEnrollment(userId: string, tenantId: string, phone: string): Promise<PhoneOtpRequestResult | null> {
    await this.ensureInitialized(); const { normalizeE164 } = await import("./mfa-crypto.js"); const phoneE164 = normalizeE164(phone);
    const spool = this.env.PHONE_OTP_STAGING_SPOOL; if (!phoneE164 || !spool || this.env.PHONE_OTP_PROVIDER !== "staging-spool") return null;
    const previous = await this.pool.query("SELECT 1 FROM auth_otp_challenges WHERE user_id=$1 AND tenant_id=$2 AND purpose='phone-enrollment' AND resend_available_at>now()", [userId, tenantId]);
    if (previous.rowCount) return null;
    const challengeId = randomUUID(); const code = String(randomBytes(4).readUInt32BE() % 1_000_000).padStart(6, "0");
    const expiresAt = new Date(Date.now() + 10 * 60_000); const resendAvailableAt = new Date(Date.now() + 60_000);
    await this.pool.query("INSERT INTO auth_otp_challenges (id,user_id,tenant_id,purpose,channel,code_hash,expires_at,resend_available_at) VALUES ($1,$2,$3,'phone-enrollment','phone',$4,$5,$6)", [challengeId, userId, tenantId, secretDigest(code), expiresAt, resendAvailableAt]);
    await this.pool.query(`INSERT INTO auth_phone_factors (user_id,tenant_id,phone_e164,verified_at) VALUES ($1,$2,$3,NULL)
      ON CONFLICT (user_id) DO UPDATE SET phone_e164=excluded.phone_e164,verified_at=NULL,updated_at=now()`, [userId, tenantId, phoneE164]);
    await new StagingSpoolPhoneOtpProvider(spool).deliver({ challengeId, phoneE164, code, expiresAt: expiresAt.toISOString() });
    await this.audit("phone-otp-request", "success", tenantId, userId);
    return { challengeId, expiresAt: expiresAt.toISOString(), resendAvailableAt: resendAvailableAt.toISOString(), delivery: "staging-spool" };
  }

  async verifyPhoneEnrollment(userId: string, tenantId: string, challengeId: string, code: string): Promise<boolean> {
    await this.ensureInitialized();
    const challenge = await this.pool.query<{ code_hash: string }>("SELECT code_hash FROM auth_otp_challenges WHERE id=$1 AND user_id=$2 AND tenant_id=$3 AND purpose='phone-enrollment' AND consumed_at IS NULL AND expires_at>now() AND attempt_count<max_attempts", [challengeId, userId, tenantId]);
    if (!challenge.rows[0] || challenge.rows[0].code_hash !== secretDigest(code)) { await this.pool.query("UPDATE auth_otp_challenges SET attempt_count=attempt_count+1 WHERE id=$1 AND user_id=$2", [challengeId, userId]); await this.audit("phone-otp-failure", "failure", tenantId, userId); return false; }
    const client = await this.pool.connect(); try { await client.query("BEGIN"); await client.query("UPDATE auth_otp_challenges SET consumed_at=now() WHERE id=$1", [challengeId]); await client.query("UPDATE auth_phone_factors SET verified_at=now(),updated_at=now() WHERE user_id=$1 AND tenant_id=$2", [userId, tenantId]); await client.query("COMMIT"); } catch (error) { await client.query("ROLLBACK"); throw error; } finally { client.release(); }
    await this.audit("phone-otp-success", "success", tenantId, userId); return true;
  }

  async getCurrentSession(authorizationHeader?: string): Promise<AuthSession | null> {
    await this.ensureInitialized();
    const token = authorizationHeader?.replace(/^Bearer\s+/i, "");
    if (!token) return null;
    const result = await this.pool.query<AuthRow & { expires_at: Date; csrf_hash: string }>(
      `SELECT u.*,c.password_hash,c.password_salt,t.name tenant_name,t.code tenant_code,t.slug tenant_slug,t.status tenant_status,t.primary_company_id,s.expires_at,s.csrf_hash
       FROM auth_sessions s JOIN auth_users u ON u.id=s.user_id AND u.tenant_id=s.tenant_id JOIN auth_credentials c ON c.user_id=u.id JOIN tenants t ON t.id=u.tenant_id
       WHERE s.token_hash=$1 AND s.revoked_at IS NULL AND s.expires_at>now() AND u.status='active' LIMIT 1`, [digest(token)]
    );
    const row = result.rows[0];
    if (!row) return null;
    await this.pool.query("UPDATE auth_sessions SET last_seen_at=now() WHERE token_hash=$1", [digest(token)]);
    return { token, user: this.user(row), tenant: this.tenant(row), expiresAt: row.expires_at.toISOString() };
  }

  async validateCsrf(token: string, csrfToken: string): Promise<boolean> {
    await this.ensureInitialized();
    const result = await this.pool.query("SELECT 1 FROM auth_sessions WHERE token_hash=$1 AND csrf_hash=$2 AND revoked_at IS NULL AND expires_at>now()", [digest(token), digest(csrfToken)]);
    return Boolean(result.rowCount);
  }

  async revokeSession(token: string): Promise<void> {
    await this.ensureInitialized();
    const result = await this.pool.query<{ tenant_id: string; user_id: string }>("UPDATE auth_sessions SET revoked_at=now() WHERE token_hash=$1 AND revoked_at IS NULL RETURNING tenant_id,user_id", [digest(token)]);
    const row = result.rows[0]; if (row) await this.audit("logout", "success", row.tenant_id, row.user_id);
  }

  async revokeAllSessions(userId: string, tenantId: string): Promise<void> {
    await this.ensureInitialized();
    await this.pool.query("UPDATE auth_sessions SET revoked_at=now() WHERE user_id=$1 AND tenant_id=$2 AND revoked_at IS NULL", [userId, tenantId]);
    await this.audit("revoke-all", "success", tenantId, userId);
  }

  async createLoginAuditEvent(_user: AuthenticatedUser): Promise<void> { return; }
  private user(row: AuthRow): User { return { id: row.id, tenantId: row.tenant_id, email: row.email, name: row.name, status: row.status, roles: row.roles, permissions: row.permissions, mfaEnabled: row.mfa_enabled, authProviders: ["password"], createdAt: row.created_at.toISOString() }; }
  private tenant(row: AuthRow): Tenant { return { id: row.tenant_id, name: row.tenant_name, code: row.tenant_code ?? "", verifiedDomains: [], status: row.tenant_status, primaryCompanyId: row.primary_company_id ?? "" }; }
  async close(): Promise<void> { await this.pool.end(); }
}
