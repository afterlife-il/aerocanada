import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DataTable } from "@/components/ui/data-table";
import { FilterBar } from "@/components/ui/filter-bar";
import { StatusBadge } from "@/components/ui/status-badge";
import { data } from "@/lib/data";

export default function CompaniesPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Company workspace" title="Companies" description="Customers, suppliers, owners, repair vendors, and future SaaS tenant candidates." />
      <FilterBar placeholder="Search company, domain, city, owner, supplier..." />
      <div className="mt-3">
        <DataTable
          rows={data.companies}
          rowHref={(row) => `/companies/${row.id}`}
          columns={[
            { key: "name", header: "Company", cell: (row) => <span className="font-semibold">{row.name}</span> },
            { key: "type", header: "Type", cell: (row) => row.type },
            { key: "location", header: "Location", cell: (row) => [row.city, row.country].filter(Boolean).join(", ") },
            { key: "email", header: "Primary Email", cell: (row) => row.primaryEmail ?? "-" },
            { key: "tags", header: "Tags", cell: (row) => row.tags.join(", ") },
            { key: "risk", header: "Risk", cell: (row) => <StatusBadge status={row.riskLevel} /> },
            { key: "legacy", header: "Legacy ID", cell: (row) => <span className="font-mono">{row.legacyId}</span> }
          ]}
        />
      </div>
    </AppShell>
  );
}
