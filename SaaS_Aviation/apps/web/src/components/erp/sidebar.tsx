import Link from "next/link";
import { currentSession } from "@/lib/data";

const sections = [
  {
    label: "Operate",
    items: [
      { href: "/dashboard", label: "Dashboard" },
      { href: "/companies", label: "Companies" },
      { href: "/parts", label: "Part Numbers" },
      { href: "/company-inventory", label: "Company Inventory" },
      { href: "/stock/internal", label: "ACI Stock" },
      { href: "/stock/external", label: "External Stock" },
      { href: "/documents", label: "Documents" }
    ]
  },
  {
    label: "Workflow",
    items: [
      { href: "/dashboard#rfq", label: "RFQ" },
      { href: "/dashboard#quotes", label: "Quotes" },
      { href: "/dashboard#po", label: "Purchase Orders" },
      { href: "/dashboard#repair", label: "Repair / Exchange" }
    ]
  },
  {
    label: "Internal (dev team only)",
    items: [{ href: "/admin/cto", label: "CTO Dashboard" }]
  }
];

export function Sidebar() {
  return (
    <aside className="hidden w-64 shrink-0 border-r border-border bg-panel lg:block">
      <div className="border-b border-border px-4 py-4">
        <div className="text-sm font-semibold text-foreground">SaaS Aviation</div>
        <div className="mt-1 text-xs text-muted">{currentSession.tenant.code} workspace</div>
      </div>
      <nav className="space-y-5 px-3 py-4">
        {sections.map((section) => (
          <div key={section.label}>
            <div className="px-2 text-[11px] font-semibold uppercase text-muted">{section.label}</div>
            <div className="mt-2 space-y-1">
              {section.items.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="block rounded-md px-2 py-2 text-sm font-medium text-foreground hover:bg-panel-muted"
                >
                  {item.label}
                </Link>
              ))}
            </div>
          </div>
        ))}
      </nav>
    </aside>
  );
}
