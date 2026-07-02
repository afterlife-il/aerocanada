import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DocumentPanel } from "@/components/modules/document-panel";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { stockColumns } from "@/components/modules/stock-columns";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { data, getPart } from "@/lib/data";
import { getEntityDocumentReadModel } from "@/lib/documents";
import { getPart360ReadModel } from "@/lib/part-stock";

export const dynamicParams = false;

export function generateStaticParams() {
  return data.parts.map((part) => ({ id: part.id }));
}

export default async function PartDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const part = getPart(id);
  const part360 = getPart360ReadModel(part.id);
  const documents = getEntityDocumentReadModel("part", part.id);
  const internalStock = part360?.internalStock ?? [];
  const externalStock = part360?.externalStock ?? [];
  const rfqs = part360?.rfqs ?? [];

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
        <DetailPanel title="Availability / Margin">
          <div className="grid gap-4 md:grid-cols-3">
            <KeyValue label="ACI Units" value={part360?.stockAvailability.internalUnits ?? 0} />
            <KeyValue label="External Units" value={part360?.stockAvailability.externalUnits ?? 0} />
            <KeyValue label="Available Units" value={part360?.stockAvailability.availableUnits ?? 0} />
            <KeyValue label="Zero Qty Rows" value={part360?.stockAvailability.zeroQtyRows ?? 0} />
            <KeyValue label="Quoted Value" value={`$${(part360?.margin.quotedValue ?? 0).toLocaleString("en-US")}`} />
            <KeyValue label="Margin" value={`${(part360?.margin.marginPct ?? 0).toFixed(1)}%`} />
          </div>
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_1fr]">
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
        <DetailPanel title="Quotes / Supplier Quotes">
          <DataTable
            rows={[...(part360?.customerQuotes ?? []), ...(part360?.supplierQuotes ?? [])]}
            columns={[
              { key: "kind", header: "Kind", cell: (row) => ("quoteNumber" in row ? "Customer" : "Supplier") },
              { key: "ref", header: "Ref", cell: (row) => <span className="font-mono">{("quoteNumber" in row ? row.quoteNumber : row.id)}</span> },
              { key: "rfq", header: "RFQ_ID", cell: (row) => <span className="font-mono">{row.rfqId}</span> },
              { key: "company", header: "Company", cell: (row) => ("customerName" in row ? row.customerName : row.supplierName) },
              { key: "status", header: "Status", cell: (row) => row.status },
              { key: "due", header: "Due", cell: (row) => row.dueAt }
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
        <DetailPanel title="History / Traceability">
          <DataTable
            rows={[...(part360?.purchaseHistory ?? []), ...(part360?.salesHistory ?? []), ...(part360?.serviceHistory ?? [])]}
            columns={[
              { key: "kind", header: "Kind", cell: (row) => ("orderNumber" in row ? row.kind : row.kind) },
              { key: "ref", header: "Reference", cell: (row) => <span className="font-mono">{"orderNumber" in row ? row.orderNumber : row.reference}</span> },
              { key: "company", header: "Company", cell: (row) => ("companyName" in row ? row.companyName : "-") },
              { key: "status", header: "Status", cell: (row) => row.status },
              { key: "due", header: "Date", cell: (row) => ("dueAt" in row ? row.dueAt : "-") }
            ]}
          />
        </DetailPanel>
        <DocumentPanel title="Linked Documents" documents={documents.documents} />
        {part360 ? <WorkflowBoundaryPanel title="Quick Action Boundaries" actions={part360.quickActions} /> : null}
      </div>
    </AppShell>
  );
}
