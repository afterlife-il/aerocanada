import type { AuditEvent, AuditRepository, RequestContext } from "@saas-aviation/shared";

export class AuditService {
  constructor(private readonly repository: AuditRepository) {}

  list(context: RequestContext): Promise<AuditEvent[]> {
    return this.repository.listAuditEvents(context);
  }

  recordView(actor: string, tenantId: string, entityType: string, entityId: string): Promise<AuditEvent> {
    return this.repository.recordAuditEvent({
      actor,
      tenantId,
      action: `${entityType}.view`,
      entityType,
      entityId,
      summary: `${entityType} ${entityId} viewed`
    });
  }
}
