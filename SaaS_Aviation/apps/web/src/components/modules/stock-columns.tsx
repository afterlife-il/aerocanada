import type { StockItem } from "@saas-aviation/shared";
import { StatusBadge } from "@/components/ui/status-badge";

export function stockColumns() {
  return [
    { key: "pn", header: "PN", cell: (row: StockItem) => <span className="font-semibold">{row.pn}</span> },
    { key: "desc", header: "Description", cell: (row: StockItem) => row.description },
    { key: "sn", header: "Serial", cell: (row: StockItem) => row.serialNumber ?? "-" },
    { key: "qty", header: "Qty", cell: (row: StockItem) => <span className="font-semibold">{row.qty}</span> },
    { key: "condition", header: "Cond", cell: (row: StockItem) => row.condition ?? "-" },
    { key: "release", header: "Release", cell: (row: StockItem) => row.release ?? "-" },
    { key: "status", header: "Status", cell: (row: StockItem) => <StatusBadge status={row.status} /> },
    { key: "owner", header: "Owner / Supplier", cell: (row: StockItem) => row.ownerCompany ?? row.supplierCompany ?? "-" },
    { key: "tag", header: "Tag Info", cell: (row: StockItem) => row.tagInfoCompany ?? "-" },
    { key: "location", header: "Location", cell: (row: StockItem) => row.location ?? "-" }
  ];
}
