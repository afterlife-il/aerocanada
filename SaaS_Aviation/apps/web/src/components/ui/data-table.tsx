import Link from "next/link";
import type { ReactNode } from "react";
import { cn } from "@/lib/utils";

export interface Column<T> {
  key: string;
  header: string;
  cell: (row: T) => ReactNode;
  className?: string;
}

export function DataTable<T>({ columns, rows, rowHref }: { columns: Column<T>[]; rows: T[]; rowHref?: (row: T) => string }) {
  return (
    <div className="overflow-hidden rounded-lg border border-border bg-panel">
      <table className="w-full border-collapse text-left text-sm">
        <thead className="bg-panel-muted text-xs font-semibold uppercase text-muted">
          <tr>
            {columns.map((column) => (
              <th key={column.key} className={cn("border-b border-border px-3 py-2", column.className)}>
                {column.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, index) => {
            const href = rowHref?.(row);
            return (
              <tr key={index} className="hover:bg-panel-muted/70">
                {columns.map((column) => (
                  <td key={column.key} className={cn("border-b border-border px-3 py-2 align-top", column.className)}>
                    {href ? (
                      <Link href={href} className="block min-h-5 text-inherit">
                        {column.cell(row)}
                      </Link>
                    ) : (
                      column.cell(row)
                    )}
                  </td>
                ))}
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
