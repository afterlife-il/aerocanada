import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DataTable } from "@/components/ui/data-table";
import { FilterBar } from "@/components/ui/filter-bar";
import { data } from "@/lib/data";
import { getPart360ReadModel } from "@/lib/part-stock";

const rows = data.parts.map((part) => {
  const readModel = getPart360ReadModel(part.id);
  return {
    ...part,
    stockAvailability: readModel?.stockAvailability,
    rfqCount: readModel?.rfqs.length ?? 0,
    quoteCount: readModel?.customerQuotes.length ?? 0,
    supplierQuoteCount: readModel?.supplierQuotes.length ?? 0,
    marginPct: readModel?.margin.marginPct ?? 0
  };
});

export default function PartsPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Part Number hub" title="Part Numbers" description="Searchable aviation PN workspace for stock, external availability, RFQs, PO history, repair, exchange, and alternates." />
      <FilterBar placeholder="Search PN, description, ATA, IPC, aircraft, alternate..." />
      <div className="mt-3">
        <DataTable
          rows={rows}
          rowHref={(row) => `/parts/${row.id}`}
          columns={[
            { key: "pn", header: "PN", cell: (row) => <span className="font-mono font-semibold">{row.pn}</span> },
            { key: "description", header: "Description", cell: (row) => row.description },
            { key: "manufacturer", header: "Manufacturer", cell: (row) => row.manufacturer ?? "-" },
            { key: "ata", header: "ATA", cell: (row) => row.ata ?? "-" },
            { key: "stock", header: "Stock", cell: (row) => `${row.stockAvailability?.internalUnits ?? 0} ACI / ${row.stockAvailability?.externalUnits ?? 0} ext` },
            { key: "rfq", header: "RFQ", cell: (row) => row.rfqCount },
            { key: "quotes", header: "Quotes", cell: (row) => `${row.quoteCount} C / ${row.supplierQuoteCount} S` },
            { key: "margin", header: "Margin", cell: (row) => `${row.marginPct.toFixed(1)}%` },
            { key: "alternates", header: "Alternates", cell: (row) => row.alternates.join(", ") || "-" }
          ]}
        />
      </div>
    </AppShell>
  );
}
