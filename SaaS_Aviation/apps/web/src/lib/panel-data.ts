export type PanelResult<T> = { status: "error"; message: string } | { status: "empty" } | { status: "ok"; rows: T[] };

export function resolvePanelRows<T>(compute: () => T[]): PanelResult<T> {
  try {
    const rows = compute();
    return rows.length === 0 ? { status: "empty" } : { status: "ok", rows };
  } catch (error) {
    return { status: "error", message: error instanceof Error ? error.message : "Unknown error while reading this panel's data." };
  }
}
