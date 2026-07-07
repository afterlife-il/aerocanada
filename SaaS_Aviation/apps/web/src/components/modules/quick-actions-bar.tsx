import { ButtonLink } from "@/components/ui/button";

export function QuickActionsBar({ viewStockHref }: { viewStockHref: string }) {
  return (
    <div className="flex flex-wrap items-center gap-2">
      <ButtonLink href="#quick-actions" variant="primary">
        Create RFQ
      </ButtonLink>
      <ButtonLink href="#quick-actions" variant="secondary">
        Add Supplier Quote
      </ButtonLink>
      <ButtonLink href="#quick-actions" variant="secondary">
        Add Stock
      </ButtonLink>
      <ButtonLink href="#quick-actions" variant="secondary">
        Upload Certificate
      </ButtonLink>
      <ButtonLink href={viewStockHref} variant="quiet">
        View Stock 360
      </ButtonLink>
    </div>
  );
}
