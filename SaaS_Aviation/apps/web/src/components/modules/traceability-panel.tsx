import type { PartTraceabilitySummary } from "@saas-aviation/shared";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, EmptyState, KeyValue } from "@/components/ui/panels";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { StatusBadge } from "@/components/ui/status-badge";

export function TraceabilityPanel({ id, traceability }: { id?: string; traceability: PartTraceabilitySummary }) {
  const hasData =
    traceability.previousOwners.length > 0 ||
    traceability.origins.length > 0 ||
    traceability.repairReferences.length > 0 ||
    traceability.certificationChain.length > 0 ||
    traceability.serialTraceability.length > 0 ||
    traceability.events.length > 0;

  return (
    <DetailPanel id={id} title="Traceability">
      {!hasData ? (
        <EmptyState title="No traceability data yet" detail="Previous owner, origin, repair references, certification chain, and serial traceability will appear once stock or service records are linked to this part." />
      ) : (
        <div className="grid gap-4">
          <div className="grid gap-4 md:grid-cols-2">
            <KeyValue label="Previous Owner(s)" value={traceability.previousOwners.join(", ") || "-"} />
            <KeyValue label="Origin / Supplier(s)" value={traceability.origins.join(", ") || "-"} />
          </div>

          <div>
            <div className="mb-2 text-xs font-semibold uppercase text-muted">Serial Traceability</div>
            {traceability.serialTraceability.length === 0 ? (
              <EmptyState title="No serialized units" detail="No stock lines with a serial number are linked to this part yet." />
            ) : (
              <DataTable
                rows={traceability.serialTraceability}
                columns={[
                  { key: "serial", header: "Serial", cell: (row) => <span className="font-mono font-semibold">{row.serialNumber}</span> },
                  { key: "source", header: "Source", cell: (row) => (row.source === "internal" ? "ACI" : "External") },
                  { key: "condition", header: "Condition", cell: (row) => row.condition ?? "-" },
                  { key: "status", header: "Status", cell: (row) => <StatusBadge status={row.status} /> },
                  { key: "owner", header: "Owner", cell: (row) => row.ownerCompany ?? "-" },
                  { key: "trace", header: "Traceability Company", cell: (row) => row.traceabilityCompany ?? "-" }
                ]}
              />
            )}
          </div>

          <div>
            <div className="mb-2 text-xs font-semibold uppercase text-muted">Repair References</div>
            {traceability.repairReferences.length === 0 ? (
              <EmptyState title="No repair references" detail="Repair workflow references linked to this part will appear here." />
            ) : (
              <DataTable
                rows={traceability.repairReferences}
                columns={[
                  { key: "ref", header: "Reference", cell: (row) => <span className="font-mono">{row.reference}</span> },
                  { key: "company", header: "Company", cell: (row) => row.companyName },
                  { key: "status", header: "Status", cell: (row) => row.status },
                  { key: "due", header: "Due", cell: (row) => row.dueAt }
                ]}
              />
            )}
          </div>

          <div>
            <div className="mb-2 text-xs font-semibold uppercase text-muted">Certification Chain</div>
            {traceability.certificationChain.length === 0 ? (
              <EmptyState title="No certification chain" detail="Certificates (8130-3, EASA Form 1, CoC) linked to this part's stock will appear here." />
            ) : (
              <DataTable
                rows={traceability.certificationChain}
                columns={[
                  { key: "type", header: "Type", cell: (row) => row.documentType },
                  { key: "status", header: "Status", cell: (row) => <StatusBadge status={row.status} /> },
                  { key: "due", header: "Due", cell: (row) => row.dueAt }
                ]}
              />
            )}
          </div>

          {traceability.events.length > 0 ? (
            <div>
              <div className="mb-2 text-xs font-semibold uppercase text-muted">Traceability Events</div>
              <EntityTimeline events={traceability.events} />
            </div>
          ) : null}
        </div>
      )}
    </DetailPanel>
  );
}
