import Link from "next/link";

export type EntityTab = string | { label: string; href: string };

export function EntityTabs({ tabs }: { tabs: EntityTab[] }) {
  return (
    <div className="mb-4 flex gap-1 overflow-x-auto border-b border-border">
      {tabs.map((tab, index) => {
        const label = typeof tab === "string" ? tab : tab.label;
        const href = typeof tab === "string" ? "#" : tab.href;
        return (
          <Link
            key={label}
            href={href}
            className={
              index === 0
                ? "border-b-2 border-accent px-3 py-2 text-sm font-semibold text-foreground"
                : "px-3 py-2 text-sm font-medium text-muted hover:text-foreground"
            }
          >
            {label}
          </Link>
        );
      })}
    </div>
  );
}
