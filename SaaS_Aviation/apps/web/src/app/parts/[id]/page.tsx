import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { BoundaryLink, BoundaryNote } from "@/components/modules/boundary-note";
import { DocumentPanel } from "@/components/modules/document-panel";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { PartHeaderSummaryBar } from "@/components/modules/part-header-summary";
import { QuickActionsBar } from "@/components/modules/quick-actions-bar";
import { StatePanel } from "@/components/modules/state-panel";
import { stockColumns } from "@/components/modules/stock-columns";
import { TraceabilityPanel } from "@/components/modules/traceability-panel";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, ErrorState, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { data, getPart } from "@/lib/data";
import { getEntityDocumentReadModel } from "@/lib/documents";
import { resolvePanelRows } from "@/lib/panel-data";
import { getPart360ReadModel } from "@/lib/part-stock";
import { getDataSourceConfig } from "@/lib/data-source-mode";
import Link from "next/link";

export const dynamicParams = false;

export function generateStaticParams() {
  return data.parts.map((part) => ({ id: part.id }));
}

export default async function PartDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  if (getDataSourceConfig().mode === "persistent-api") return <AppShell><PageHeader eyebrow="Part 360" title="Persistent Part" description="Part details are loaded from PostgreSQL in the operational workspace." /><div className="rounded border border-border bg-panel p-5 text-sm">This legacy static detail path is disabled in persistent staging. <Link className="font-semibold text-accent" href={`/parts/?id=${encodeURIComponent(id)}`}>Open the persistent Part record</Link>.</div></AppShell>;
  const part = getPart(id);
  const part360 = getPart360ReadModel(part.id);
  const documents = getEntityDocumentReadModel("part", part.id);

  if (!part360) {
    return (
      <AppShell>
        <PageHeader eyebrow="Part 360" title={part.pn} description={part.description} />
        <ErrorState
          title="Part 360 data unavailable"
          detail="This part could not be resolved for the current tenant context. Verify tenant scope before retrying."
        />
      </AppShell>
    );
  }

  const internalStockResult = resolvePanelRows(() => part360.internalStock);
  const externalStockResult = resolvePanelRows(() => part360.externalStock);
  const rfqsResult = resolvePanelRows(() => part360.rfqs);
  const supplierQuotesResult = resolvePanelRows(() => part360.supplierQuotes);
  const customerQuotesResult = resolvePanelRows(() => part360.customerQuotes);
  const purchaseHistoryResult = resolvePanelRows(() => part360.purchaseHistory);
  const salesHistoryResult = resolvePanelRows(() => part360.salesHistory);

  const stockViewHref = part360.internalStock[0]
    ? `/stock/internal/${part360.internalStock[0].id}`
    : part360.externalStock[0]
      ? `/stock/internal/${part360.externalStock[0].id}`
      : "/stock/internal";

  return (
    <AppShell>
      <PageHeader
        eyebrow="Part 360"
        title={part.pn}
        description={part.description}
        actions={<QuickActionsBar viewStockHref={stockViewHref} />}
      />
      <PartHeaderSummaryBar header={part360.header} />
      <EntityTabs
        tabs={[
          { label: "Overview", href: "#overview" },
          { label: "ACI Stock", href: "#internal-stock" },
          { label: "External Stock", href: "#external-stock" },
          { label: "RFQ", href: "#rfqs" },
          { label: "Quotes", href: "#customer-quotes" },
          { label: "Purchase", href: "#purchase-history" },
          { label: "Sales", href: "#sales-history" },
          { label: "Documents", href: "#documents" },
          { label: "Traceability", href: "#traceability" },
          { label: "Boundaries", href: "#quick-actions" }
        ]}
      />

      <div id="overview" className="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
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
            <KeyValue label="ACI Units" value={part360.stockAvailability.internalUnits} />
            <KeyValue label="External Units" value={part360.stockAvailability.externalUnits} />
            <KeyValue label="Available Units" value={part360.stockAvailability.availableUnits} />
            <KeyValue label="Zero Qty Rows" value={part360.stockAvailability.zeroQtyRows} />
            <KeyValue label="Quoted Value" value={`$${part360.margin.quotedValue.toLocaleString("en-US")}`} />
            <KeyValue label="Margin" value={`${part360.margin.marginPct.toFixed(1)}%`} />
          </div>
        </DetailPanel>
      </div>

      <div className="mt-4 grid gap-4">
        <StatePanel
          id="internal-stock"
          title="Internal Stock (ACI)"
          result={internalStockResult}
          emptyTitle="No internal stock"
          emptyDetail="No ACI-owned stock is currently linked to this part."
          render={(rows) => <DataTable rows={rows} rowHref={(row) => `/stock/internal/${row.id}`} columns={stockColumns()} />}
        />
        <StatePanel
          id="external-stock"
          title="External Stock"
          result={externalStockResult}
          emptyTitle="No external stock"
          emptyDetail="No external supplier availability is currently linked to this part."
          render={(rows) => <DataTable rows={rows} columns={stockColumns()} />}
        />
      </div>

      <div className="mt-4 grid gap-4">
        <StatePanel
          id="rfqs"
          title="Related RFQs"
          actions={<BoundaryLink href="#quick-actions" label="Create RFQ" />}
          result={rfqsResult}
          emptyTitle="No related RFQs"
          emptyDetail="No RFQs currently reference this part number."
          render={(rows) => (
            <DataTable
              rows={rows}
              columns={[
                { key: "rfq", header: "RFQ Number", cell: (row) => <span className="font-mono font-semibold">{row.rfqId}</span> },
                { key: "customer", header: "Customer", cell: (row) => row.customerName },
                { key: "priority", header: "Priority", cell: (row) => <StatusBadge status={row.priority} /> },
                { key: "status", header: "Status", cell: (row) => row.status },
                { key: "created", header: "Creation Date", cell: (row) => row.createdAt }
              ]}
            />
          )}
        />
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-2">
        <StatePanel
          id="supplier-quotes"
          title="Supplier Quotes"
          actions={<BoundaryLink href="#quick-actions" label="Add Supplier Quote" />}
          result={supplierQuotesResult}
          emptyTitle="No supplier quotes"
          emptyDetail="No supplier quotes have been requested for this part yet."
          render={(rows) => (
            <>
              <BoundaryNote>
                Supplier Quote module not yet implemented. Showing linked RFQ, supplier, quantity, status, and due date only —
                price, lead time, and condition will populate once the Supplier Quote module is built.
              </BoundaryNote>
              <DataTable
                rows={rows}
                columns={[
                  { key: "supplier", header: "Supplier", cell: (row) => row.supplierName },
                  { key: "rfq", header: "RFQ_ID", cell: (row) => <span className="font-mono">{row.rfqId}</span> },
                  { key: "qty", header: "Qty", cell: (row) => row.qty },
                  { key: "status", header: "Status", cell: (row) => row.status },
                  { key: "due", header: "Validity", cell: (row) => row.dueAt }
                ]}
              />
            </>
          )}
        />
        <StatePanel
          id="customer-quotes"
          title="Customer Quotes"
          actions={<BoundaryLink href="#quick-actions" label="Add Customer Quote" />}
          result={customerQuotesResult}
          emptyTitle="No customer quotes"
          emptyDetail="No customer quotes have been issued for this part yet."
          render={(rows) => (
            <DataTable
              rows={rows}
              columns={[
                { key: "customer", header: "Customer", cell: (row) => row.customerName },
                { key: "quote", header: "Quote Number", cell: (row) => <span className="font-mono">{row.quoteNumber}</span> },
                { key: "price", header: "Price", cell: (row) => `$${row.value.toLocaleString("en-US")}` },
                { key: "status", header: "Status", cell: (row) => row.status },
                { key: "date", header: "Date", cell: (row) => row.dueAt }
              ]}
            />
          )}
        />
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-2">
        <StatePanel
          id="purchase-history"
          title="Purchase History"
          result={purchaseHistoryResult}
          emptyTitle="No purchase history"
          emptyDetail="Purchase Orders module is not yet implemented. Linked purchase records will appear here once available."
          render={(rows) => (
            <>
              <BoundaryNote>
                Purchase Orders are owned by the future Purchase Orders module. Rows below are read-only linked order
                records; full PO workflow is not implemented here.
              </BoundaryNote>
              <DataTable
                rows={rows}
                columns={[
                  { key: "order", header: "Order Number", cell: (row) => <span className="font-mono">{row.orderNumber}</span> },
                  { key: "company", header: "Company", cell: (row) => row.companyName },
                  { key: "status", header: "Status", cell: (row) => row.status },
                  { key: "value", header: "Value", cell: (row) => `$${row.value.toLocaleString("en-US")}` },
                  { key: "due", header: "Due", cell: (row) => row.dueAt }
                ]}
              />
            </>
          )}
        />
        <StatePanel
          id="sales-history"
          title="Sales History"
          result={salesHistoryResult}
          emptyTitle="No sales history"
          emptyDetail="Sales Orders module is not yet implemented. Linked sales records will appear here once available."
          render={(rows) => (
            <>
              <BoundaryNote>
                Sales Orders are owned by the future Sales Orders module. Rows below are read-only linked order records;
                full SO workflow is not implemented here.
              </BoundaryNote>
              <DataTable
                rows={rows}
                columns={[
                  { key: "order", header: "Order Number", cell: (row) => <span className="font-mono">{row.orderNumber}</span> },
                  { key: "company", header: "Company", cell: (row) => row.companyName },
                  { key: "status", header: "Status", cell: (row) => row.status },
                  { key: "value", header: "Value", cell: (row) => `$${row.value.toLocaleString("en-US")}` },
                  { key: "due", header: "Due", cell: (row) => row.dueAt }
                ]}
              />
            </>
          )}
        />
      </div>

      <div className="mt-4">
        <DocumentPanel
          title="Documents & Certificates"
          actions={<BoundaryLink href="#quick-actions" label="Upload Certificate" />}
          documents={documents.documents}
        />
      </div>

      <div className="mt-4">
        <TraceabilityPanel id="traceability" traceability={part360.traceabilitySummary} />
      </div>

      <div className="mt-4">
        <WorkflowBoundaryPanel id="quick-actions" title="Quick Action Boundaries" actions={part360.quickActions} />
      </div>
    </AppShell>
  );
}
