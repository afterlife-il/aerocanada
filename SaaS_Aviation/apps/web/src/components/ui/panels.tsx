import type { ReactNode } from "react";
import { cn } from "@/lib/utils";

export function DetailPanel({ title, children, className }: { title: string; children: ReactNode; className?: string }) {
  return (
    <section className={cn("rounded-lg border border-border bg-panel", className)}>
      <header className="border-b border-border px-4 py-3">
        <h2 className="text-sm font-semibold uppercase text-foreground">{title}</h2>
      </header>
      <div className="p-4">{children}</div>
    </section>
  );
}

export function KeyValue({ label, value }: { label: string; value: ReactNode }) {
  return (
    <div>
      <div className="text-xs font-semibold uppercase text-muted">{label}</div>
      <div className="mt-1 text-sm font-medium text-foreground">{value || "-"}</div>
    </div>
  );
}

export function EmptyState({ title, detail }: { title: string; detail: string }) {
  return (
    <div className="rounded-lg border border-dashed border-border bg-panel p-6">
      <div className="text-sm font-semibold text-foreground">{title}</div>
      <div className="mt-1 text-sm text-muted">{detail}</div>
    </div>
  );
}
