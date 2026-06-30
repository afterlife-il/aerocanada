export interface AuthenticatedUser {
  id: string;
  email: string;
  name: string;
  tenantId: string;
  roles: string[];
  mfaVerified: boolean;
}

export interface AuthProvider {
  getCurrentUser(token?: string): Promise<AuthenticatedUser | null>;
  createLoginAuditEvent(user: AuthenticatedUser): Promise<void>;
}

export class MockAuthProvider implements AuthProvider {
  async getCurrentUser(_token?: string): Promise<AuthenticatedUser> {
    return {
      id: "mock-user",
      email: "ops@aerocanada-industries.com",
      name: "AeroCanada Ops",
      tenantId: "tenant-aci",
      roles: ["admin", "inventory", "sales"],
      mfaVerified: false
    };
  }

  async createLoginAuditEvent(_user: AuthenticatedUser): Promise<void> {
    return;
  }
}
