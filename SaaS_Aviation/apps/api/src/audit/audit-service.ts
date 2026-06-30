import type { AuditEvent, AuditRepository } from "@saas-aviation/shared";

export class AuditService {
  constructor(private readonly repository: AuditRepository) {}

  list(): Promise<AuditEvent[]> {
    return this.repository.listAuditEvents();
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
