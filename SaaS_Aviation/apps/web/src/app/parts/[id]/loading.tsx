import { AppShell } from "@/components/erp/app-shell";
import { LoadingState } from "@/components/ui/panels";

export default function PartDetailLoading() {
  return (
    <AppShell>
      <div className="mb-4 h-16 animate-pulse rounded-lg bg-panel-muted" />
      <div className="mb-4 h-20 animate-pulse rounded-lg bg-panel-muted" />
      <div className="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
        <div className="rounded-lg border border-border bg-panel p-4">
          <LoadingState rows={4} />
        </div>
        <div className="rounded-lg border border-border bg-panel p-4">
          <LoadingState rows={4} />
        </div>
      </div>
      <div className="mt-4 grid gap-4">
        <div className="rounded-lg border border-border bg-panel p-4">
          <LoadingState rows={3} />
        </div>
        <div className="rounded-lg border border-border bg-panel p-4">
          <LoadingState rows={3} />
        </div>
      </div>
    </AppShell>
  );
}
