import type { ReactNode } from "react";
import { DetailPanel, EmptyState, ErrorState } from "@/components/ui/panels";
import type { PanelResult } from "@/lib/panel-data";

export function StatePanel<T>({
  id,
  title,
  actions,
  result,
  emptyTitle,
  emptyDetail,
  render
}: {
  id?: string;
  title: string;
  actions?: ReactNode;
  result: PanelResult<T>;
  emptyTitle: string;
  emptyDetail: string;
  render: (rows: T[]) => ReactNode;
}) {
  return (
    <DetailPanel id={id} title={title} actions={actions}>
      {result.status === "error" ? (
        <ErrorState title="Could not load this panel" detail={result.message} />
      ) : result.status === "empty" ? (
        <EmptyState title={emptyTitle} detail={emptyDetail} />
      ) : (
        render(result.rows)
      )}
    </DetailPanel>
  );
}
