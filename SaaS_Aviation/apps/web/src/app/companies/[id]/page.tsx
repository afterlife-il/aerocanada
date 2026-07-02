import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { stockColumns } from "@/components/modules/stock-columns";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { data, getCompany } from "@/lib/data";
import { getCompanyInventoryReadModel } from "@/lib/part-stock";

export const dynamicParams = false;

export function generateStaticParams() {
  return data.companies.map((company) => ({ id: company.id }));
}

export default async function CompanyDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const company = getCompany(id);
  const contacts = data.contacts.filter((contact) => contact.companyId === company.id);
  const inventory = getCompanyInventoryReadModel();
  const inventoryRow = inventory.rows.find((row) => row.companyId === company.id);
  const stock = inventoryRow?.stockLines ?? [];

  return (
    <AppShell>
      <PageHeader eyebrow="Company 360" title={company.name} description="One workspace for profile, contacts, inventory, RFQ/quote context, documents, users, and activity." />
      <EntityTabs tabs={["Overview", "Contacts", "Inventory", "External Inventory", "RFQ", "Quotes", "PO", "Documents", "Users", "Activity"]} />
      <div className="grid gap-4 xl:grid-cols-[1fr_1.3fr]">
        <DetailPanel title="Company Profile">
          <div className="grid gap-4 md:grid-cols-2">
            <KeyValue label="Type" value={company.type} />
            <KeyValue label="Legacy ID" value={<span className="font-mono">{company.legacyId}</span>} />
            <KeyValue label="Location" value={[company.city, company.country].filter(Boolean).join(", ")} />
            <KeyValue label="Tags" value={company.tags.join(", ")} />
            <KeyValue label="ACI Units" value={inventoryRow?.internalUnits ?? 0} />
            <KeyValue label="External Units" value={inventoryRow?.externalUnits ?? 0} />
            <KeyValue label="Stock Value" value={`$${(inventoryRow?.stockValue ?? 0).toLocaleString("en-US")}`} />
            <KeyValue label="Zero Qty Rows" value={inventoryRow?.zeroQtyRows ?? 0} />
          </div>
        </DetailPanel>
        <DetailPanel title="Contacts">
          <DataTable
            rows={contacts}
            columns={[
              { key: "name", header: "Name", cell: (row) => <span className="font-semibold">{row.name}</span> },
              { key: "title", header: "Title", cell: (row) => row.title ?? "-" },
              { key: "email", header: "Email", cell: (row) => row.email ?? "-" },
              { key: "division", header: "Division", cell: (row) => row.division ?? "-" }
            ]}
          />
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1.5fr_1fr]">
        <DetailPanel title="Related Stock">
          <DataTable rows={stock} rowHref={(row) => `/stock/internal/${row.id}`} columns={stockColumns()} />
        </DetailPanel>
        <DetailPanel title="Activity Timeline">
          <EntityTimeline events={data.audit} />
        </DetailPanel>
      </div>
      <div className="mt-4">
        <WorkflowBoundaryPanel title="Company Inventory Boundaries" actions={inventory.quickActions} />
      </div>
    </AppShell>
  );
}
