import Link from "next/link";

export function EntityTabs({ tabs }: { tabs: string[] }) {
  return (
    <div className="mb-4 flex gap-1 overflow-x-auto border-b border-border">
      {tabs.map((tab, index) => (
        <Link
          key={tab}
          href="#"
          className={
            index === 0
              ? "border-b-2 border-accent px-3 py-2 text-sm font-semibold text-foreground"
              : "px-3 py-2 text-sm font-medium text-muted hover:text-foreground"
          }
        >
          {tab}
        </Link>
      ))}
    </div>
  );
}
