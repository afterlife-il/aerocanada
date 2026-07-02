import type { WorkflowBoundaryAction } from "@saas-aviation/shared";
import { DetailPanel } from "@/components/ui/panels";

export function WorkflowBoundaryPanel({ title = "Workflow Boundaries", actions }: { title?: string; actions: WorkflowBoundaryAction[] }) {
  return (
    <DetailPanel title={title}>
      <div className="grid gap-3">
        {actions.map((action) => (
          <div key={action.id} className="rounded-md border border-border bg-panel-muted p-3">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div className="text-sm font-semibold text-foreground">{action.label}</div>
                <div className="mt-1 text-xs text-muted">{action.note}</div>
              </div>
              <div className="rounded border border-border bg-panel px-2 py-1 text-[11px] font-semibold uppercase text-muted">No persistence</div>
            </div>
            <div className="mt-3 grid gap-3 text-xs md:grid-cols-3">
              <div>
                <div className="font-semibold uppercase text-muted">Required Data</div>
                <div className="mt-1 font-mono text-foreground">{action.requiredData.join(", ")}</div>
              </div>
              <div>
                <div className="font-semibold uppercase text-muted">Context Checks</div>
                <div className="mt-1 text-foreground">{action.contextChecks.join(", ")}</div>
              </div>
              <div>
                <div className="font-semibold uppercase text-muted">Future Owner</div>
                <div className="mt-1 text-foreground">{action.futureOwner}</div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </DetailPanel>
  );
}
