import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { stockColumns } from "@/components/modules/stock-columns";
import { DataTable } from "@/components/ui/data-table";
import { FilterBar } from "@/components/ui/filter-bar";
import { data } from "@/lib/data";

export default function ExternalStockPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Supplier availability" title="External Stock" description="Supplier stock remains separate from ACI-owned inventory. Owner, supplier, and tag info must not be conflated." />
      <FilterBar placeholder="Search supplier PN, tag info, condition, release, price..." />
      <div className="mt-3">
        <DataTable rows={data.externalStock} columns={stockColumns()} />
      </div>
    </AppShell>
  );
}
