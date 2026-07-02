import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { getCompanyInventoryReadModel } from "@/lib/part-stock";

export default function CompanyInventoryPage() {
  const inventory = getCompanyInventoryReadModel();

  return (
    <AppShell>
      <PageHeader eyebrow="Company Inventory" title="Company Inventory" description="Tenant-scoped owner, supplier, tag-info, and traceability inventory view. Rows may show the same stock under different company relationships; totals count unique stock lines." />
      <div className="grid gap-4 md:grid-cols-5">
        <DetailPanel title="ACI Units">
          <KeyValue label="Internal" value={inventory.totals.internalUnits} />
        </DetailPanel>
        <DetailPanel title="External Units">
          <KeyValue label="Supplier" value={inventory.totals.externalUnits} />
        </DetailPanel>
        <DetailPanel title="Stock Value">
          <KeyValue label={inventory.totals.currency} value={`$${inventory.totals.stockValue.toLocaleString("en-US")}`} />
        </DetailPanel>
        <DetailPanel title="Zero Qty">
          <KeyValue label="Visible Rows" value={inventory.totals.zeroQtyRows} />
        </DetailPanel>
        <DetailPanel title="Tenant">
          <KeyValue label="Scope" value={inventory.tenantCode} />
        </DetailPanel>
      </div>
      <div className="mt-4">
        <DetailPanel title="Inventory by Company Relationship">
          <DataTable
            rows={inventory.rows}
            rowHref={(row) => `/companies/${row.companyId}`}
            columns={[
              { key: "company", header: "Company", cell: (row) => <span className="font-semibold">{row.companyName}</span> },
              { key: "type", header: "Type", cell: (row) => row.companyType },
              { key: "internal", header: "ACI Units", cell: (row) => row.internalUnits },
              { key: "external", header: "External Units", cell: (row) => row.externalUnits },
              { key: "zero", header: "Zero Qty", cell: (row) => row.zeroQtyRows },
              { key: "value", header: "Value", cell: (row) => `$${row.stockValue.toLocaleString("en-US")}` },
              { key: "lines", header: "Lines", cell: (row) => row.stockLines.length },
              { key: "docs", header: "Docs", cell: (row) => row.documents.length },
              { key: "rfq", header: "RFQs", cell: (row) => row.linkedRfqs.length }
            ]}
          />
        </DetailPanel>
      </div>
      <div className="mt-4">
        <WorkflowBoundaryPanel title="Company Inventory Action Boundaries" actions={inventory.quickActions} />
      </div>
    </AppShell>
  );
}
