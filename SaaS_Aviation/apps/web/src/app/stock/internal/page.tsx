import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { stockColumns } from "@/components/modules/stock-columns";
import { DataTable } from "@/components/ui/data-table";
import { FilterBar } from "@/components/ui/filter-bar";
import { data } from "@/lib/data";

export default function InternalStockPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Inventory" title="ACI Internal Stock" description="Owned/managed stock with explicit owner, tag info, traceability, status, and lifecycle context. Qty 0 rows remain visible." />
      <FilterBar placeholder="Search PN, serial, owner, tag info, release, location..." />
      <div className="mt-3">
        <DataTable rows={data.internalStock} rowHref={(row) => `/stock/internal/${row.id}`} columns={stockColumns()} />
      </div>
    </AppShell>
  );
}
