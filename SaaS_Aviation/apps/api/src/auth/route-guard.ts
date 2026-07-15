import type { Request, Response } from "express";
import type { Permission, RequestContext } from "@saas-aviation/shared";
import type { AuthProvider } from "./auth-provider.js";
import { requestContextFromSession } from "./auth-provider.js";

export interface ProtectedRequest extends Request {
  tenantContext: RequestContext;
}

export function sessionCredential(req: Request): string | undefined {
  if (req.headers.authorization) return req.headers.authorization;
  const session = req.headers.cookie?.split(";").map((item) => item.trim()).find((item) => item.startsWith("saas_session="))?.slice("saas_session=".length);
  return session ? `Bearer ${decodeURIComponent(session)}` : undefined;
}

export async function requireSession(req: Request, res: Response, auth: AuthProvider): Promise<RequestContext | null> {
  const session = await auth.getCurrentSession(sessionCredential(req));

  if (!session) {
    res.status(401).json({ error: "unauthorized" });
    return null;
  }

  return requestContextFromSession(session);
}

export function hasPermission(context: RequestContext, permission: Permission): boolean {
  return context.tenant.permissions.includes(permission);
}

export function requirePermission(context: RequestContext, res: Response, permission: Permission): boolean {
  if (hasPermission(context, permission)) {
    return true;
  }

  res.status(403).json({ error: "forbidden", permission });
  return false;
}
