import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { ButtonLink } from "@/components/ui/button";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { SummaryCard } from "@/components/ui/summary-card";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { data } from "@/lib/data";

export default function DashboardPage() {
  return (
    <AppShell>
      <PageHeader
        eyebrow="Operations cockpit"
        title="Aviation ERP Dashboard"
        description="A dense operational view for RFQ pressure, stock exceptions, repair returns, and recent audit activity."
        actions={<ButtonLink href="/stock/internal" variant="primary">Open Inventory</ButtonLink>}
      />
      <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
        {data.kpis.map((kpi) => (
          <SummaryCard key={kpi.label} {...kpi} />
        ))}
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1.6fr_1fr]">
        <DetailPanel title="Priority RFQ Queue">
          <DataTable
            rows={data.rfqs}
            columns={[
              { key: "rfq", header: "RFQ_ID", cell: (row) => <span className="font-mono font-semibold">{row.rfqId}</span> },
              { key: "customer", header: "Customer", cell: (row) => row.customerName },
              { key: "pn", header: "PN", cell: (row) => row.partNumber },
              { key: "qty", header: "Qty", cell: (row) => row.qty },
              { key: "priority", header: "Priority", cell: (row) => <StatusBadge status={row.priority} /> },
              { key: "status", header: "Status", cell: (row) => row.status }
            ]}
          />
        </DetailPanel>
        <DetailPanel title="Audit Activity">
          <EntityTimeline events={data.audit} />
        </DetailPanel>
      </div>
    </AppShell>
  );
}
