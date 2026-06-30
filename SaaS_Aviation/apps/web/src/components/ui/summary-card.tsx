import { cn } from "@/lib/utils";

export function SummaryCard({
  label,
  value,
  trend,
  tone = "neutral"
}: {
  label: string;
  value: string;
  trend: string;
  tone?: "neutral" | "good" | "warning" | "critical";
}) {
  const toneClass = {
    neutral: "text-muted",
    good: "text-[oklch(0.38_0.12_155)]",
    warning: "text-[oklch(0.43_0.12_78)]",
    critical: "text-[oklch(0.45_0.16_25)]"
  }[tone];

  return (
    <section className="rounded-lg border border-border bg-panel p-4">
      <div className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</div>
      <div className="mt-2 font-mono text-2xl font-semibold text-foreground">{value}</div>
      <div className={cn("mt-1 text-xs font-medium", toneClass)}>{trend}</div>
    </section>
  );
}
