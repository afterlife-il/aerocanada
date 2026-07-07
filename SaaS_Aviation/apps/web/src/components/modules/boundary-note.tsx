import type { ReactNode } from "react";

export function BoundaryNote({ children }: { children: ReactNode }) {
  return <div className="mb-3 rounded-md border border-dashed border-border bg-panel-muted px-3 py-2 text-xs text-muted">{children}</div>;
}

export function BoundaryLink({ href, label }: { href: string; label: string }) {
  return (
    <a
      href={href}
      className="inline-flex h-7 items-center rounded-md border border-border bg-panel px-2 text-xs font-semibold text-foreground hover:bg-panel-muted"
    >
      {label} <span className="ml-1 text-muted">↓</span>
    </a>
  );
}
