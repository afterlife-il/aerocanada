import { AppShell } from "@/components/erp/app-shell";
import { ActionBar } from "@/components/erp/action-bar";
import { PageHeader } from "@/components/erp/page-header";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { Button } from "@/components/ui/button";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { data, getStock } from "@/lib/data";

export const dynamicParams = false;

export function generateStaticParams() {
  return [...data.internalStock, ...data.externalStock].map((stock) => ({ id: stock.id }));
}

export default async function StockDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const stock = getStock(id);

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
            <KeyValue label="Owner / Company" value={stock.ownerCompany ?? "-"} />
            <KeyValue label="Supplier" value={stock.supplierCompany ?? "-"} />
            <KeyValue label="Tag Info" value={stock.tagInfoCompany ?? "-"} />
            <KeyValue label="Traceability" value={stock.traceabilityCompany ?? "-"} />
          </div>
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_1fr]">
        <DetailPanel title="Lifecycle Timeline">
          <EntityTimeline events={data.audit} />
        </DetailPanel>
        <DetailPanel title="Documents / Certificates">
          <div className="grid gap-2 text-sm">
            <div className="rounded-md border border-border bg-panel-muted p-3">FAA 8130 / EASA / CoC document panel placeholder</div>
            <div className="rounded-md border border-border bg-panel-muted p-3">Upload workflow requires approved storage and virus scanning design</div>
          </div>
        </DetailPanel>
      </div>
      <ActionBar>
        <Button>Edit stock</Button>
        <Button variant="secondary">Create RFQ</Button>
        <Button variant="secondary">Send to repair</Button>
      </ActionBar>
    </AppShell>
  );
}
