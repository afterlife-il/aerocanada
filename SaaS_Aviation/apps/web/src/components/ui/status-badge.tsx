import type { EntityStatus } from "@saas-aviation/shared";
import { cn } from "@/lib/utils";

const tones: Record<string, string> = {
  available: "border-[oklch(0.78_0.08_155)] bg-[oklch(0.94_0.04_155)] text-[oklch(0.34_0.11_155)]",
  reserved: "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  sold: "border-[oklch(0.78_0.08_247)] bg-[oklch(0.94_0.03_247)] text-[oklch(0.36_0.07_247)]",
  "purchase-order": "border-[oklch(0.78_0.09_245)] bg-[oklch(0.95_0.04_245)] text-[oklch(0.36_0.1_245)]",
  "work-order": "border-[oklch(0.78_0.08_300)] bg-[oklch(0.95_0.03_300)] text-[oklch(0.36_0.09_300)]",
  consignment: "border-border bg-panel-muted text-muted",
  quarantine: "border-[oklch(0.74_0.17_25)] bg-[oklch(0.94_0.04_25)] text-[oklch(0.4_0.15_25)]",
  repair: "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  exchange: "border-[oklch(0.76_0.1_245)] bg-[oklch(0.94_0.04_245)] text-[oklch(0.35_0.1_245)]",
  operational: "border-[oklch(0.78_0.08_155)] bg-[oklch(0.94_0.04_155)] text-[oklch(0.34_0.11_155)]",
  passing: "border-[oklch(0.78_0.08_155)] bg-[oklch(0.94_0.04_155)] text-[oklch(0.34_0.11_155)]",
  "in-progress": "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  foundation: "border-[oklch(0.78_0.09_245)] bg-[oklch(0.95_0.04_245)] text-[oklch(0.36_0.1_245)]",
  planned: "border-border bg-panel-muted text-muted",
  "not-started": "border-border bg-panel-muted text-muted",
  blocked: "border-[oklch(0.74_0.17_25)] bg-[oklch(0.94_0.04_25)] text-[oklch(0.4_0.15_25)]",
  failing: "border-[oklch(0.74_0.17_25)] bg-[oklch(0.94_0.04_25)] text-[oklch(0.4_0.15_25)]",
  "in-stock": "border-[oklch(0.78_0.08_155)] bg-[oklch(0.94_0.04_155)] text-[oklch(0.34_0.11_155)]",
  "external-only": "border-[oklch(0.78_0.09_245)] bg-[oklch(0.95_0.04_245)] text-[oklch(0.36_0.1_245)]",
  "quoted-only": "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  "no-stock": "border-[oklch(0.74_0.17_25)] bg-[oklch(0.94_0.04_25)] text-[oklch(0.4_0.15_25)]",
  present: "border-[oklch(0.78_0.08_155)] bg-[oklch(0.94_0.04_155)] text-[oklch(0.34_0.11_155)]",
  missing: "border-[oklch(0.74_0.17_25)] bg-[oklch(0.94_0.04_25)] text-[oklch(0.4_0.15_25)]",
  "pending-review": "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  "expires-soon": "border-[oklch(0.78_0.09_78)] bg-[oklch(0.95_0.04_78)] text-[oklch(0.42_0.11_78)]",
  unknown: "border-border bg-panel-muted text-muted"
};

export function StatusBadge({ status, className }: { status: EntityStatus | string; className?: string }) {
  return (
    <span className={cn("inline-flex rounded-md border px-2 py-1 text-xs font-semibold capitalize", tones[status] ?? tones.unknown, className)}>
      {status.replace("-", " ")}
    </span>
  );
}
