import type { Request, Response } from "express";
import type { Permission, RequestContext } from "@saas-aviation/shared";
import type { AuthProvider } from "./auth-provider.js";
import { requestContextFromSession } from "./auth-provider.js";

export interface ProtectedRequest extends Request {
  tenantContext: RequestContext;
}

export async function requireSession(req: Request, res: Response, auth: AuthProvider): Promise<RequestContext | null> {
  const session = await auth.getCurrentSession(req.headers.authorization);

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
