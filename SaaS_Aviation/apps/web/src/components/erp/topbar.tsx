import Link from "next/link";
import { ButtonLink } from "@/components/ui/button";
import { currentSession } from "@/lib/data";

export function Topbar() {
  return (
    <header className="sticky top-0 z-20 border-b border-border bg-background/95 px-4 py-3 backdrop-blur">
      <div className="flex items-center gap-3">
        <Link href="/dashboard" className="font-semibold text-foreground lg:hidden">
          AeroCanada ERP
        </Link>
        <div className="hidden min-w-0 flex-1 lg:block">
          <input
            className="h-9 w-full max-w-2xl rounded-md border border-border bg-panel px-3 text-sm outline-none focus:border-accent"
            placeholder="Search PN, serial number, company, RFQ_ID, PO..."
          />
        </div>
        <div className="ml-auto flex items-center gap-2">
          <div className="hidden text-right sm:block">
            <div className="text-xs font-semibold text-foreground">{currentSession.user.name}</div>
            <div className="text-[11px] text-muted">{currentSession.tenant.code}</div>
          </div>
          <ButtonLink href="/stock/internal" variant="secondary">
            Find Stock
          </ButtonLink>
          <ButtonLink href="/login" variant="quiet">
            Session
          </ButtonLink>
        </div>
      </div>
    </header>
  );
}
