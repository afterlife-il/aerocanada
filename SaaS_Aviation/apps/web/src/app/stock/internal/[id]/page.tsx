import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { data, getStock } from "@/lib/data";
import { getStock360ReadModel } from "@/lib/part-stock";

export const dynamicParams = false;

export function generateStaticParams() {
  return [...data.internalStock, ...data.externalStock].map((stock) => ({ id: stock.id }));
}

export default async function StockDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const stock = getStock(id);
  const stock360 = getStock360ReadModel(stock.id);

  return (
    <AppShell>
      <PageHeader
        eyebrow="Stock 360"
        title={`${stock.pn} ${stock.serialNumber ? ` / ${stock.serialNumber}` : ""}`}
        description="Traceability, ownership, tag info, lifecycle, document, quote, and PO context for one stock line."
      />
      <EntityTabs tabs={["Overview", "Traceability", "Lifecycle", "RFQ / Quotes", "PO", "Documents", "Audit"]} />
      <div className="grid gap-4 xl:grid-cols-[1.25fr_1fr]">
        <DetailPanel title="Stock Identity">
          <div className="grid gap-4 md:grid-cols-3">
            <KeyValue label="PN" value={<span className="font-mono">{stock.pn}</span>} />
            <KeyValue label="Serial" value={stock.serialNumber ?? "-"} />
            <KeyValue label="Qty" value={<span className="font-semibold">{stock.qty}</span>} />
            <KeyValue label="Condition" value={stock.condition ?? "-"} />
            <KeyValue label="Release" value={stock.release ?? "-"} />
            <KeyValue label="Status" value={<StatusBadge status={stock.status} />} />
            <KeyValue label="Location" value={stock.location ?? "-"} />
            <KeyValue label="Entry Date" value={stock.entryDate ?? "-"} />
            <KeyValue label="Legacy ID" value={<span className="font-mono">{stock.legacyId}</span>} />
          </div>
        </DetailPanel>
        <DetailPanel title="Ownership / Traceability">
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
            <KeyValue label="Owner / Company" value={stock360?.ownerCompany?.name ?? stock.ownerCompany ?? "-"} />
            <KeyValue label="Supplier" value={stock360?.supplierCompany?.name ?? stock.supplierCompany ?? "-"} />
            <KeyValue label="Tag Info" value={stock360?.tagInfoCompany?.name ?? stock.tagInfoCompany ?? "-"} />
            <KeyValue label="Traceability" value={stock360?.traceabilityCompany?.name ?? stock.traceabilityCompany ?? "-"} />
            <KeyValue label="Cost" value={`$${(stock.price ?? 0).toLocaleString("en-US")}`} />
            <KeyValue label="Margin Context" value={`${(stock360?.margin.marginPct ?? 0).toFixed(1)}% from linked customer quotes`} />
          </div>
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_1fr]">
        <DetailPanel title="Lifecycle Timeline">
          <EntityTimeline events={stock360?.lifecycle.length ? stock360.lifecycle : data.audit.filter((event) => event.entityId === stock.id)} />
        </DetailPanel>
        <DetailPanel title="Documents / Certificates">
          <DataTable
            rows={stock360?.documents ?? []}
            columns={[
              { key: "type", header: "Type", cell: (row) => row.documentType },
              { key: "entity", header: "Entity", cell: (row) => <span className="font-mono">{row.entityId}</span> },
              { key: "status", header: "Status", cell: (row) => row.status },
              { key: "due", header: "Due", cell: (row) => row.dueAt }
            ]}
          />
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_1fr]">
        <DetailPanel title="RFQ / Quote / Order Links">
          <DataTable
            rows={[...(stock360?.rfqs ?? []), ...(stock360?.customerQuotes ?? []), ...(stock360?.supplierQuotes ?? []), ...(stock360?.purchaseOrders ?? []), ...(stock360?.salesOrders ?? [])]}
            columns={[
              { key: "kind", header: "Kind", cell: (row) => ("rfqId" in row && "priority" in row ? "RFQ" : "quoteNumber" in row ? "Customer quote" : "supplierName" in row ? "Supplier quote" : row.kind) },
              { key: "ref", header: "Reference", cell: (row) => <span className="font-mono">{("rfqId" in row && "priority" in row ? row.rfqId : "quoteNumber" in row ? row.quoteNumber : "supplierName" in row ? row.id : row.orderNumber)}</span> },
              { key: "company", header: "Company", cell: (row) => ("customerName" in row ? row.customerName : "supplierName" in row ? row.supplierName : "companyName" in row ? row.companyName : "-") },
              { key: "status", header: "Status", cell: (row) => row.status }
            ]}
          />
        </DetailPanel>
        <DetailPanel title="Repair / Exchange / Lease">
          <DataTable
            rows={stock360?.serviceHistory ?? []}
            columns={[
              { key: "kind", header: "Kind", cell: (row) => row.kind },
              { key: "ref", header: "Reference", cell: (row) => <span className="font-mono">{row.reference}</span> },
              { key: "company", header: "Company", cell: (row) => row.companyName },
              { key: "status", header: "Status", cell: (row) => row.status },
              { key: "due", header: "Due", cell: (row) => row.dueAt }
            ]}
          />
        </DetailPanel>
      </div>
      {stock360 ? (
        <div className="mt-4">
          <WorkflowBoundaryPanel title="Stock Action Boundaries" actions={stock360.quickActions} />
        </div>
      ) : null}
    </AppShell>
  );
}
