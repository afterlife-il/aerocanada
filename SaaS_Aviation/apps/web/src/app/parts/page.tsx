import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DataTable } from "@/components/ui/data-table";
import { FilterBar } from "@/components/ui/filter-bar";
import { data } from "@/lib/data";

export default function PartsPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Part Number hub" title="Part Numbers" description="Searchable aviation PN workspace for stock, external availability, RFQs, PO history, repair, exchange, and alternates." />
      <FilterBar placeholder="Search PN, description, ATA, IPC, aircraft, alternate..." />
      <div className="mt-3">
        <DataTable
          rows={data.parts}
          rowHref={(row) => `/parts/${row.id}`}
          columns={[
            { key: "pn", header: "PN", cell: (row) => <span className="font-mono font-semibold">{row.pn}</span> },
            { key: "description", header: "Description", cell: (row) => row.description },
            { key: "manufacturer", header: "Manufacturer", cell: (row) => row.manufacturer ?? "-" },
            { key: "ata", header: "ATA", cell: (row) => row.ata ?? "-" },
            { key: "ipc", header: "IPC", cell: (row) => row.ipc ?? "-" },
            { key: "aircraft", header: "Aircraft", cell: (row) => row.aircraft?.join(", ") ?? "-" },
            { key: "alternates", header: "Alternates", cell: (row) => row.alternates.join(", ") || "-" }
          ]}
        />
      </div>
    </AppShell>
  );
}
