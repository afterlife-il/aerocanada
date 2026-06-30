import type { AuditEvent } from "@saas-aviation/shared";

export function EntityTimeline({ events }: { events: AuditEvent[] }) {
  return (
    <div className="space-y-3">
      {events.map((event) => (
        <div key={event.id} className="grid grid-cols-[120px_1fr] gap-3 border-b border-border pb-3 text-sm last:border-b-0">
          <div className="font-mono text-xs text-muted">{event.occurredAt.slice(0, 10)}</div>
          <div>
            <div className="font-semibold text-foreground">{event.action}</div>
            <div className="text-muted">{event.summary}</div>
          </div>
        </div>
      ))}
    </div>
  );
}
