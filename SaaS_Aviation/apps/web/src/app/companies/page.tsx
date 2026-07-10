import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { Button } from "@/components/ui/button";
import { DataTable } from "@/components/ui/data-table";
import { EmptyState } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { getCompanyListReadModel } from "@/lib/data";

export default function CompaniesPage() {
  const list = getCompanyListReadModel({ pageSize: 10 });

  return (
    <AppShell>
      <PageHeader eyebrow="Company workspace" title="Companies" description="Customers, suppliers, owners, repair vendors, and future SaaS tenant candidates." />
      <form className="grid gap-2 rounded-lg border border-border bg-panel p-3 lg:grid-cols-[minmax(220px,1fr)_160px_140px_150px_130px_auto] lg:items-center">
        <input
          name="q"
          className="h-9 min-w-0 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent"
          placeholder="Search name, code, email, phone..."
          defaultValue={list.filters.query}
        />
        <select name="type" className="h-9 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent" defaultValue={list.filters.type}>
          <option value="all">All types</option>
          {list.filters.availableTypes.map((type) => (
            <option key={type} value={type}>
              {type}
            </option>
          ))}
        </select>
        <select name="status" className="h-9 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent" defaultValue={list.filters.status}>
          <option value="all">All status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <select name="sort" className="h-9 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent" defaultValue={list.filters.sort}>
          <option value="name">Name</option>
          <option value="type">Type</option>
          <option value="location">Location</option>
          <option value="risk">Risk</option>
          <option value="lastActivity">Last activity</option>
        </select>
        <select name="direction" className="h-9 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent" defaultValue={list.filters.direction}>
          <option value="asc">Ascending</option>
          <option value="desc">Descending</option>
        </select>
        <Button type="submit" variant="primary">
          Filter
        </Button>
      </form>
      <div className="mt-3">
        {list.state === "error" ? (
          <EmptyState title="Companies unavailable" detail={list.error ?? "The Company read model returned an error."} />
        ) : list.rows.length === 0 ? (
          <EmptyState title={list.emptyState.title} detail={list.emptyState.detail} />
        ) : (
          <DataTable
            rows={list.rows}
            rowHref={(row) => `/companies/${row.company.id}`}
            columns={[
              { key: "name", header: "Company", cell: (row) => <span className="font-semibold">{row.company.name}</span> },
              { key: "type", header: "Type", cell: (row) => row.company.type },
              { key: "status", header: "Status", cell: (row) => <StatusBadge status={row.status} /> },
              { key: "location", header: "Location", cell: (row) => [row.company.city, row.company.country].filter(Boolean).join(", ") || "-" },
              { key: "email", header: "Email", cell: (row) => row.company.primaryEmail ?? row.primaryContact?.email ?? "-" },
              { key: "contacts", header: "Contacts", cell: (row) => row.contactCount },
              { key: "inventory", header: "Inventory", cell: (row) => `${row.internalUnits + row.externalUnits} units` },
              { key: "documents", header: "Docs", cell: (row) => row.documentCount },
              { key: "risk", header: "Risk", cell: (row) => <StatusBadge status={row.company.riskLevel} /> },
              { key: "legacy", header: "Legacy ID", cell: (row) => <span className="font-mono">{row.company.legacyId}</span> }
            ]}
          />
        )}
      </div>
      <div className="mt-3 text-xs text-muted">
        Showing {list.rows.length} of {list.pagination.totalRows} companies. Page {list.pagination.page} of {list.pagination.totalPages}.
      </div>
    </AppShell>
  );
}
