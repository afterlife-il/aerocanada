import type { PartHeaderSummary } from "@saas-aviation/shared";
import { StatusBadge } from "@/components/ui/status-badge";
import { CertificationIndicatorChips, ConditionSummaryChips } from "@/components/modules/part-header-chips";

export function PartHeaderSummaryBar({ header }: { header: PartHeaderSummary }) {
  return (
    <div className="mb-4 flex flex-wrap items-start gap-6 rounded-lg border border-border bg-panel p-4">
      <div>
        <div className="text-xs font-semibold uppercase text-muted">Status</div>
        <div className="mt-1">
          <StatusBadge status={header.availabilityStatus} />
        </div>
      </div>
      <div>
        <div className="text-xs font-semibold uppercase text-muted">Last Update</div>
        <div className="mt-1 text-sm font-medium text-foreground">{header.lastUpdatedAt ?? "-"}</div>
      </div>
      <div className="min-w-[240px] flex-1">
        <div className="text-xs font-semibold uppercase text-muted">Condition Summary</div>
        <div className="mt-1">
          <ConditionSummaryChips rows={header.conditionSummary} />
        </div>
      </div>
      <div className="min-w-[240px] flex-1">
        <div className="text-xs font-semibold uppercase text-muted">Certification Indicators</div>
        <div className="mt-1">
          <CertificationIndicatorChips indicators={header.certificationIndicators} />
        </div>
      </div>
    </div>
  );
}
