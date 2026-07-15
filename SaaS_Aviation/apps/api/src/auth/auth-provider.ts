import { createHash, randomUUID } from "node:crypto";
import { sampleTenants, sampleUsers } from "@saas-aviation/shared";
import type { AuthSession, AuthUserRecord, Permission, RequestContext, Role, Tenant, User } from "@saas-aviation/shared";
import { getPersistenceConfig } from "../persistence/config.js";
import { PostgresAuthProvider } from "./postgres-auth-provider.js";

export interface AuthenticatedUser {
  id: string;
  email: string;
  name: string;
  tenantId: string;
  roles: Role[];
  permissions: Permission[];
  mfaVerified: boolean;
}

export type PasswordAuthenticationResult = AuthSession | { mfaRequired: true; challengeId: string; methods: Array<"totp" | "recovery">; expiresAt: string };

export interface TotpEnrollment {
  secret: string;
  otpauthUri: string;
}

export interface PhoneOtpRequestResult { challengeId: string; expiresAt: string; resendAvailableAt: string; delivery: "staging-spool"; }

export interface AuthProvider {
  authenticateWithPassword(email: string, password: string): Promise<AuthSession | null>;
  beginPasswordAuthentication?(email: string, password: string): Promise<PasswordAuthenticationResult | null>;
  completeMfaChallenge?(challengeId: string, code: string): Promise<AuthSession | null>;
  beginTotpEnrollment?(userId: string, tenantId: string): Promise<TotpEnrollment>;
  confirmTotpEnrollment?(userId: string, tenantId: string, code: string): Promise<string[] | null>;
  disableTotp?(userId: string, tenantId: string, code: string): Promise<boolean>;
  requestPhoneEnrollment?(userId: string, tenantId: string, phone: string): Promise<PhoneOtpRequestResult | null>;
  verifyPhoneEnrollment?(userId: string, tenantId: string, challengeId: string, code: string): Promise<boolean>;
  getCurrentSession(token?: string): Promise<AuthSession | null>;
  revokeSession(token: string): Promise<void>;
  revokeAllSessions?(userId: string, tenantId: string): Promise<void>;
  validateCsrf?(token: string, csrfToken: string): Promise<boolean>;
  createLoginAuditEvent(user: AuthenticatedUser): Promise<void>;
}

const SESSION_TTL_MS = 1000 * 60 * 60 * 8;

function publicUser(user: AuthUserRecord): User {
  const safeUser: AuthUserRecord = { ...user };
  delete safeUser.passwordHash;
  return safeUser;
}

function sha256(value: string): string {
  return `sha256:${createHash("sha256").update(value).digest("hex")}`;
}

function bearerToken(header?: string): string | null {
  if (!header) {
    return null;
  }

  const [scheme, token] = header.split(" ");
  if (scheme?.toLowerCase() !== "bearer" || !token) {
    return null;
  }

  return token;
}

export function requestContextFromSession(session: AuthSession): RequestContext {
  return {
    tenant: {
      tenantId: session.tenant.id,
      tenantCode: session.tenant.code,
      tenantName: session.tenant.name,
      userId: session.user.id,
      roles: session.user.roles,
      permissions: session.user.permissions
    }
  };
}

export class InMemoryAuthProvider implements AuthProvider {
  private readonly sessions = new Map<string, AuthSession>();

  constructor(
    private readonly users: AuthUserRecord[] = sampleUsers,
    private readonly tenants: Tenant[] = sampleTenants
  ) {}

  async authenticateWithPassword(email: string, password: string): Promise<AuthSession | null> {
    const normalizedEmail = email.trim().toLowerCase();
    const user = this.users.find((candidate) => candidate.email.toLowerCase() === normalizedEmail);

    if (!user || user.status !== "active" || !user.passwordHash || user.passwordHash !== sha256(password)) {
      return null;
    }

    const tenant = this.tenants.find((candidate) => candidate.id === user.tenantId && candidate.status === "active");
    if (!tenant) {
      return null;
    }

    const session: AuthSession = {
      token: randomUUID(),
      user: publicUser(user),
      tenant,
      expiresAt: new Date(Date.now() + SESSION_TTL_MS).toISOString()
    };

    this.sessions.set(session.token, session);
    return session;
  }

  async getCurrentSession(authorizationHeader?: string): Promise<AuthSession | null> {
    const token = bearerToken(authorizationHeader);
    if (!token) {
      return null;
    }

    const session = this.sessions.get(token);
    if (!session) {
      return null;
    }

    if (Date.parse(session.expiresAt) <= Date.now()) {
      this.sessions.delete(token);
      return null;
    }

    return session;
  }

  async revokeSession(token: string): Promise<void> {
    this.sessions.delete(token);
  }

  async createLoginAuditEvent(_user: AuthenticatedUser): Promise<void> {
    return;
  }
}

export function createDefaultAuthProvider(env: NodeJS.ProcessEnv = process.env): AuthProvider {
  if (env.NODE_ENV !== "production") return new InMemoryAuthProvider();
  const persistence = getPersistenceConfig(env);
  if (persistence.provider === "postgres" && persistence.postgres) return new PostgresAuthProvider(persistence.postgres, env);
  const password = env.STAGING_ADMIN_PASSWORD;
  if (!password || password.length < 16) {
    throw new Error("STAGING_ADMIN_PASSWORD with at least 16 characters is required in production staging.");
  }
  const email = env.STAGING_ADMIN_EMAIL?.trim().toLowerCase() || sampleUsers[0]?.email;
  if (!email || !sampleUsers[0]) throw new Error("Staging admin identity is unavailable.");
  return new InMemoryAuthProvider([{ ...sampleUsers[0], email, passwordHash: sha256(password) }], sampleTenants);
}
