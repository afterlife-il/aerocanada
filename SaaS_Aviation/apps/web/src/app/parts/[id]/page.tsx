import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { stockColumns } from "@/components/modules/stock-columns";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { data, getPart } from "@/lib/data";

export const dynamicParams = false;

export function generateStaticParams() {
  return data.parts.map((part) => ({ id: part.id }));
}

export default async function PartDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const part = getPart(id);
  const internalStock = data.internalStock.filter((stock) => stock.partId === part.id);
  const externalStock = data.externalStock.filter((stock) => stock.partId === part.id);
  const rfqs = data.rfqs.filter((rfq) => rfq.partNumber === part.pn);

  return (
    <AppShell>
      <PageHeader eyebrow="Part 360" title={part.pn} description={part.description} />
      <EntityTabs tabs={["Overview", "ACI Stock", "External Stock", "RFQ", "Quotes", "PO", "Repair", "Exchange", "Documents", "History"]} />
      <div className="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
        <DetailPanel title="Part Identity">
          <div className="grid gap-4 md:grid-cols-2">
            <KeyValue label="Manufacturer" value={part.manufacturer ?? "-"} />
            <KeyValue label="ATA" value={part.ata ?? "-"} />
            <KeyValue label="IPC" value={part.ipc ?? "-"} />
            <KeyValue label="Aircraft" value={part.aircraft?.join(", ") ?? "-"} />
            <KeyValue label="Alternates" value={part.alternates.join(", ") || "-"} />
            <KeyValue label="Legacy ID" value={<span className="font-mono">{part.legacyId}</span>} />
          </div>
        </DetailPanel>
        <DetailPanel title="Demand / RFQ Context">
          <DataTable
            rows={rfqs}
            columns={[
              { key: "rfq", header: "RFQ_ID", cell: (row) => <span className="font-mono font-semibold">{row.rfqId}</span> },
              { key: "customer", header: "Customer", cell: (row) => row.customerName },
              { key: "qty", header: "Qty", cell: (row) => row.qty },
              { key: "priority", header: "Priority", cell: (row) => <StatusBadge status={row.priority} /> },
              { key: "status", header: "Status", cell: (row) => row.status }
            ]}
          />
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4">
        <DetailPanel title="ACI Internal Stock">
          <DataTable rows={internalStock} rowHref={(row) => `/stock/internal/${row.id}`} columns={stockColumns()} />
        </DetailPanel>
        <DetailPanel title="External Supplier Stock">
          <DataTable rows={externalStock} columns={stockColumns()} />
        </DetailPanel>
      </div>
    </AppShell>
  );
}
