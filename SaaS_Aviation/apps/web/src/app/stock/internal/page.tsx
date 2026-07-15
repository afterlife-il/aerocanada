import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { Suspense } from "react";
import { PersistentStockWorkspace } from "@/components/modules/persistent-stock-workspace";

export default function InternalStockPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Inventory" title="ACI Internal Stock" description="Owned/managed stock with explicit owner, tag info, traceability, status, and lifecycle context. Qty 0 rows remain visible." />
      <Suspense fallback={<div className="mt-4 text-sm text-muted">Loading Stock workspace…</div>}><PersistentStockWorkspace /></Suspense>
    </AppShell>
  );
}
