import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { Suspense } from "react";
import { PersistentPartWorkspace } from "@/components/modules/persistent-part-workspace";

export default function PartsPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Part Number hub" title="Part Numbers" description="Searchable aviation PN workspace for stock, external availability, RFQs, PO history, repair, exchange, and alternates." />
      <Suspense fallback={<div className="mt-4 text-sm text-muted">Loading Part workspace…</div>}><PersistentPartWorkspace /></Suspense>
    </AppShell>
  );
}
