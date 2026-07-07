import type { PartCertificationIndicator, PartConditionSummaryRow } from "@saas-aviation/shared";
import { StatusBadge } from "@/components/ui/status-badge";

export function ConditionSummaryChips({ rows }: { rows: PartConditionSummaryRow[] }) {
  if (rows.length === 0) return <span className="text-sm text-muted">No stock lines yet</span>;
  return (
    <div className="flex flex-wrap gap-2">
      {rows.map((row) => (
        <span key={row.condition} className="rounded-md border border-border bg-panel-muted px-2 py-1 text-xs font-semibold text-foreground">
          {row.condition} × {row.qty} ({row.lines} {row.lines === 1 ? "line" : "lines"})
        </span>
      ))}
    </div>
  );
}

export function CertificationIndicatorChips({ indicators }: { indicators: PartCertificationIndicator[] }) {
  return (
    <div className="flex flex-wrap gap-2">
      {indicators.map((indicator) => (
        <span key={indicator.documentType} className="inline-flex items-center gap-1.5 rounded-md border border-border bg-panel-muted px-2 py-1 text-xs font-semibold text-foreground">
          {indicator.documentType}
          <StatusBadge status={indicator.status} />
        </span>
      ))}
    </div>
  );
}
