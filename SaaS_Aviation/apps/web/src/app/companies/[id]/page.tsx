import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { ButtonLink } from "@/components/ui/button";
import { DocumentPanel } from "@/components/modules/document-panel";
import { EntityTabs } from "@/components/modules/entity-tabs";
import { stockColumns } from "@/components/modules/stock-columns";
import { WorkflowBoundaryPanel } from "@/components/modules/workflow-boundary-panel";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, EmptyState, KeyValue } from "@/components/ui/panels";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { SummaryCard } from "@/components/ui/summary-card";
import { StatusBadge } from "@/components/ui/status-badge";
import { data, getCompany360ReadModel } from "@/lib/data";
import { getDataSourceConfig } from "@/lib/data-source-mode";

export const dynamicParams = false;

export function generateStaticParams() {
  if (getDataSourceConfig().mode === "persistent-api") return [{ id: "persistent" }];
  return data.companies.map((company) => ({ id: company.id }));
}

export default async function CompanyDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  if (getDataSourceConfig().mode === "persistent-api") {
    return <AppShell><PageHeader eyebrow="Company 360" title="Persistent Company" description="Company details are loaded from PostgreSQL in the operational workspace." /><DetailPanel title="Persistent Company workspace"><p className="text-sm text-muted">Legacy static Company detail routes are disabled in persistent staging.</p><ButtonLink href={`/companies/?id=${encodeURIComponent(id)}`} variant="primary">Open Companies</ButtonLink></DetailPanel></AppShell>;
  }
  const company360 = getCompany360ReadModel(id);
  const { company } = company360;
  const editCompanyAction = company360.boundaryActions.find((action) => action.id === "edit-company");
  const createContactAction = company360.boundaryActions.find((action) => action.id === "create-contact");
  const addDocumentAction = company360.boundaryActions.find((action) => action.id === "add-document");
  const inventoryAction = company360.boundaryActions.find((action) => action.id === "view-company-inventory");

  return (
    <AppShell>
      <PageHeader
        eyebrow="Company 360"
        title={company.name}
        description="One workspace for profile, contacts, inventory, RFQ/quote context, documents, users, and activity."
        actions={
          <>
            {editCompanyAction ? (
              <ButtonLink href="#workflow-boundaries" variant="primary">
                {editCompanyAction.label}
              </ButtonLink>
            ) : null}
            {createContactAction ? <ButtonLink href="#contacts">{createContactAction.label}</ButtonLink> : null}
            {addDocumentAction ? <ButtonLink href="#documents">{addDocumentAction.label}</ButtonLink> : null}
          </>
        }
      />
      <EntityTabs tabs={["Overview", "Contacts", "Inventory", "External Inventory", "RFQ", "Quotes", "PO", "Documents", "Users", "Activity"]} />
      <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        {company360.overviewKpis.map((kpi) => (
          <SummaryCard key={kpi.label} label={kpi.label} value={kpi.value} trend={kpi.trend} tone={kpi.tone} />
        ))}
      </div>
      <div className="grid gap-4 xl:grid-cols-[1fr_1.3fr]">
        <DetailPanel title="Company Overview">
          <div className="grid gap-4 md:grid-cols-2">
            <KeyValue label="Type" value={company.type} />
            <KeyValue label="Status" value={<StatusBadge status={company360.inventorySummary.stockLines.length || company360.contacts.length ? "active" : "inactive"} />} />
            <KeyValue label="Risk" value={<StatusBadge status={company.riskLevel} />} />
            <KeyValue label="Legacy ID" value={<span className="font-mono">{company.legacyId}</span>} />
            <KeyValue label="Location" value={[company.city, company.country].filter(Boolean).join(", ")} />
            <KeyValue label="Primary Email" value={company.primaryEmail ?? "-"} />
            <KeyValue label="Tags" value={company.tags.length ? company.tags.join(", ") : "-"} />
            <KeyValue label="Last Activity" value={company.lastActivityAt ?? "-"} />
          </div>
        </DetailPanel>
        <DetailPanel id="contacts" title="Contacts">
          {company360.contacts.length === 0 ? (
            <EmptyState title="No contacts" detail="Workflow Boundary: create the first contact in the future Contact module." />
          ) : (
            <DataTable
              rows={company360.contacts}
              columns={[
                { key: "name", header: "Name", cell: (row) => <span className="font-semibold">{row.name}</span> },
                { key: "role", header: "Role", cell: (row) => row.title ?? row.division ?? "-" },
                { key: "email", header: "Email", cell: (row) => row.email ?? "-" },
                { key: "phone", header: "Phone", cell: (row) => row.phone ?? "-" },
                { key: "mobile", header: "Mobile", cell: () => "-" },
                { key: "actions", header: "Actions", cell: () => <a className="font-semibold text-accent" href="#workflow-boundaries">Edit Contact</a> }
              ]}
            />
          )}
        </DetailPanel>
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1.5fr_1fr]">
        <DetailPanel title="Company Inventory">
          <div className="mb-4 grid gap-4 md:grid-cols-4">
            <KeyValue label="ACI Units" value={company360.inventorySummary.internalUnits} />
            <KeyValue label="External Units" value={company360.inventorySummary.externalUnits} />
            <KeyValue label="Stock Value" value={`$${company360.inventorySummary.stockValue.toLocaleString("en-US")} ${company360.inventorySummary.currency}`} />
            <KeyValue label="Zero Qty Rows" value={company360.inventorySummary.zeroQtyRows} />
          </div>
          {company360.inventorySummary.stockLines.length === 0 ? (
            <EmptyState title="No inventory linked" detail="No stock lines are linked to this company in the current tenant read model." />
          ) : (
            <DataTable
              rows={company360.inventorySummary.stockLines}
              rowHref={(row) => (row.source === "internal" ? `/stock/internal/${row.id}` : "/stock/external")}
              columns={stockColumns()}
            />
          )}
          {inventoryAction ? <div className="mt-3 text-xs text-muted">Workflow Boundary: {inventoryAction.note}</div> : null}
        </DetailPanel>
        <DetailPanel title="Activity Timeline">
          {company360.activity.length === 0 ? (
            <EmptyState title="No activity" detail="No tenant activity is linked to this company yet." />
          ) : (
            <EntityTimeline events={company360.activity} />
          )}
        </DetailPanel>
      </div>
      <div id="documents" className="mt-4">
        <DocumentPanel title="Company Documents" documents={company360.documents.documents} />
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-5">
        {Object.values(company360.commercialActivity).map((panel) => (
          <DetailPanel key={panel.label} title={panel.label}>
            {panel.rows.length === 0 ? (
              <EmptyState title="No activity" detail={`${panel.boundaryAction.label} continues in ${panel.boundaryAction.futureOwner}.`} />
            ) : (
              <div className="text-sm text-foreground">
                {panel.rows.length} linked record{panel.rows.length === 1 ? "" : "s"}
              </div>
            )}
            <div className="mt-3 rounded border border-border bg-panel-muted px-3 py-2 text-xs text-muted">Open workflow: {panel.boundaryAction.note}</div>
          </DetailPanel>
        ))}
      </div>
      <div id="workflow-boundaries" className="mt-4">
        <WorkflowBoundaryPanel title="Company Workflow Boundaries" actions={company360.boundaryActions} />
      </div>
    </AppShell>
  );
}
