import type { ReactNode } from "react";
import { cn } from "@/lib/utils";

export function DetailPanel({
  id,
  title,
  actions,
  children,
  className
}: {
  id?: string | undefined;
  title: string;
  actions?: ReactNode;
  children: ReactNode;
  className?: string;
}) {
  return (
    <section id={id} className={cn("rounded-lg border border-border bg-panel", className)}>
      <header className="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-3">
        <h2 className="text-sm font-semibold uppercase text-foreground">{title}</h2>
        {actions ? <div className="flex items-center gap-2">{actions}</div> : null}
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

export function ErrorState({ title, detail, actions }: { title: string; detail: string; actions?: ReactNode }) {
  return (
    <div className="rounded-lg border border-[oklch(0.74_0.17_25)] bg-[oklch(0.97_0.02_25)] p-6">
      <div className="text-sm font-semibold text-[oklch(0.4_0.15_25)]">{title}</div>
      <div className="mt-1 text-sm text-[oklch(0.4_0.15_25)]">{detail}</div>
      {actions ? <div className="mt-3 flex gap-2">{actions}</div> : null}
    </div>
  );
}

export function LoadingState({ rows = 3 }: { rows?: number }) {
  return (
    <div className="animate-pulse space-y-2" role="status" aria-label="Loading">
      {Array.from({ length: rows }).map((_, index) => (
        <div key={index} className="h-8 rounded-md bg-panel-muted" />
      ))}
    </div>
  );
}
