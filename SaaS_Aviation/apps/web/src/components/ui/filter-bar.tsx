import { Button } from "./button";

export function FilterBar({ placeholder = "Search PN, company, RFQ, quote, PO" }: { placeholder?: string }) {
  return (
    <div className="flex flex-col gap-2 rounded-lg border border-border bg-panel p-3 md:flex-row md:items-center">
      <input className="h-9 min-w-0 flex-1 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent" placeholder={placeholder} />
      <select className="h-9 rounded-md border border-border bg-white px-3 text-sm outline-none focus:border-accent">
        <option>All statuses</option>
        <option>Available</option>
        <option>Reserved</option>
        <option>Repair</option>
        <option>Exchange</option>
        <option>Quarantine</option>
      </select>
      <Button>Filter</Button>
    </div>
  );
}
