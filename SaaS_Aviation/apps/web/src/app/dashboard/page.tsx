import Link from "next/link";
import type { ReactNode } from "react";
import type { AccountingAlert, DashboardMetric, MarginKpi, OrderSummary, QuoteSummary, RfqSummary, SupplierQuoteSummary } from "@saas-aviation/shared";
import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { ButtonLink } from "@/components/ui/button";
import { DataTable } from "@/components/ui/data-table";
import { EntityTimeline } from "@/components/ui/entity-timeline";
import { DetailPanel } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { cn } from "@/lib/utils";
import { getDashboardData } from "@/lib/dashboard";

function money(value: number, currency = "USD") {
  return new Intl.NumberFormat("en-US", { style: "currency", currency, maximumFractionDigits: 0 }).format(value);
}

function ToneText({ tone, children }: { tone: DashboardMetric["tone"] | MarginKpi["tone"]; children: ReactNode }) {
  const toneClass = {
    neutral: "text-muted",
    good: "text-[oklch(0.36_0.12_155)]",
    warning: "text-[oklch(0.42_0.12_78)]",
    critical: "text-[oklch(0.46_0.17_25)]"
  }[tone];

  return <span className={toneClass}>{children}</span>;
}

function MetricCell({ metric }: { metric: DashboardMetric }) {
  return (
    <section className="min-h-[92px] border-r border-b border-border bg-panel px-3 py-3 last:border-r-0">
      <div className="text-[11px] font-semibold uppercase text-muted">{metric.label}</div>
      <div className="mt-2 font-mono text-2xl font-semibold leading-none text-foreground">{metric.value}</div>
      <div className="mt-2 text-xs font-semibold">
        <ToneText tone={metric.tone}>{metric.detail}</ToneText>
      </div>
    </section>
  );
}

function MarginCell({ item }: { item: MarginKpi }) {
  return (
    <div className="border-b border-border py-3 last:border-b-0">
      <div className="flex items-baseline justify-between gap-3">
        <span className="text-sm font-semibold text-foreground">{item.label}</span>
        <span className="font-mono text-lg font-semibold text-foreground">{item.value}</span>
      </div>
      <div className="mt-1 text-xs font-medium">
        <ToneText tone={item.tone}>{item.detail}</ToneText>
      </div>
    </div>
  );
}

function SectionTitle({ title, href }: { title: string; href?: string | undefined }) {
  return (
    <div className="mb-2 flex items-center justify-between gap-3">
      <h3 className="text-sm font-semibold text-foreground">{title}</h3>
      {href ? (
        <Link className="text-xs font-semibold text-muted hover:text-foreground" href={href}>
          Open
        </Link>
      ) : null}
    </div>
  );
}

function WorkflowMiniTable({
  title,
  children,
  href
}: {
  title: string;
  children: ReactNode;
  href?: string | undefined;
}) {
  return (
    <section className="rounded-lg border border-border bg-panel p-3">
      <SectionTitle title={title} href={href} />
      {children}
    </section>
  );
}

function PriorityRow({ label, value, tone = "neutral" }: { label: string; value: string; tone?: "neutral" | "warning" | "critical" }) {
  const toneClass = {
    neutral: "text-muted",
    warning: "text-[oklch(0.42_0.12_78)]",
    critical: "text-[oklch(0.46_0.17_25)]"
  }[tone];

  return (
    <div className="flex items-center justify-between border-b border-border py-2 text-sm last:border-b-0">
      <span className="font-medium text-foreground">{label}</span>
      <span className={cn("font-mono text-xs font-semibold", toneClass)}>{value}</span>
    </div>
  );
}

const rfqColumns = [
  { key: "rfq", header: "RFQ_ID", cell: (row: RfqSummary) => <span className="font-mono font-semibold">{row.rfqId}</span> },
  { key: "customer", header: "Customer", cell: (row: RfqSummary) => row.customerName },
  { key: "pn", header: "PN", cell: (row: RfqSummary) => <span className="font-mono">{row.partNumber}</span> },
  { key: "qty", header: "Qty", cell: (row: RfqSummary) => row.qty, className: "text-right" },
  { key: "priority", header: "Priority", cell: (row: RfqSummary) => <StatusBadge status={row.priority} /> },
  { key: "status", header: "Status", cell: (row: RfqSummary) => <StatusBadge status={row.status} /> }
];

const quoteColumns = [
  { key: "quote", header: "Quote", cell: (row: QuoteSummary) => <span className="font-mono font-semibold">{row.quoteNumber}</span> },
  { key: "rfq", header: "RFQ_ID", cell: (row: QuoteSummary) => <span className="font-mono">{row.rfqId}</span> },
  { key: "customer", header: "Customer", cell: (row: QuoteSummary) => row.customerName },
  { key: "value", header: "Value", cell: (row: QuoteSummary) => money(row.value, row.currency), className: "text-right" },
  { key: "margin", header: "Margin", cell: (row: QuoteSummary) => `${row.marginPct.toFixed(1)}%`, className: "text-right font-mono" },
  { key: "status", header: "Status", cell: (row: QuoteSummary) => <StatusBadge status={row.status} /> }
];

const supplierQuoteColumns = [
  { key: "rfq", header: "RFQ_ID", cell: (row: SupplierQuoteSummary) => <span className="font-mono font-semibold">{row.rfqId}</span> },
  { key: "supplier", header: "Supplier", cell: (row: SupplierQuoteSummary) => row.supplierName },
  { key: "pn", header: "PN", cell: (row: SupplierQuoteSummary) => <span className="font-mono">{row.partNumber}</span> },
  { key: "qty", header: "Qty", cell: (row: SupplierQuoteSummary) => row.qty, className: "text-right" },
  { key: "due", header: "Due", cell: (row: SupplierQuoteSummary) => row.dueAt },
  { key: "status", header: "Status", cell: (row: SupplierQuoteSummary) => <StatusBadge status={row.status} /> }
];

const orderColumns = [
  { key: "order", header: "Order", cell: (row: OrderSummary) => <span className="font-mono font-semibold">{row.orderNumber}</span> },
  { key: "company", header: "Company", cell: (row: OrderSummary) => row.companyName },
  { key: "rfq", header: "RFQ_ID", cell: (row: OrderSummary) => (row.rfqId ? <span className="font-mono">{row.rfqId}</span> : "-") },
  { key: "value", header: "Value", cell: (row: OrderSummary) => money(row.value, row.currency), className: "text-right" },
  { key: "status", header: "Status", cell: (row: OrderSummary) => <StatusBadge status={row.status} /> }
];

function AccountingList({ alerts }: { alerts: AccountingAlert[] }) {
  return (
    <div className="space-y-2">
      {alerts.map((alert) => (
        <div key={alert.id} className="rounded-md border border-border bg-panel-muted px-3 py-2">
          <div className="flex items-start justify-between gap-3">
            <div>
              <div className="text-sm font-semibold text-foreground">{alert.title}</div>
              <div className="mt-1 text-xs text-muted">{alert.companyName} · due {alert.dueAt}</div>
            </div>
            <div className="text-right font-mono text-sm font-semibold text-foreground">{money(alert.amount, alert.currency)}</div>
          </div>
        </div>
      ))}
    </div>
  );
}

export default function DashboardPage() {
  const dashboard = getDashboardData();

  return (
    <AppShell>
      <PageHeader
        eyebrow={`${dashboard.tenantCode} tenant cockpit`}
        title="AEROCANADA INDUSTRIES 770 INC."
        description="Tenant-scoped ERP command surface for sales, purchasing, inventory, service work, documents, accounting, and margin control."
        actions={<ButtonLink href="/stock/internal" variant="primary">Open Inventory</ButtonLink>}
      />

      <div className="overflow-hidden rounded-lg border border-border bg-panel">
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-8">
          {dashboard.metrics.map((metric) => (
            <MetricCell key={metric.label} metric={metric} />
          ))}
        </div>
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.8fr)_380px]">
        <div className="space-y-4">
          <DetailPanel id="rfq" title="RFQs open">
            <DataTable rows={dashboard.rfqsOpen} columns={rfqColumns} />
          </DetailPanel>

          <DetailPanel id="quotes" title="Quotes pending customer / draft">
            <DataTable rows={dashboard.quotesPending} columns={quoteColumns} />
          </DetailPanel>

          <div className="grid gap-4 2xl:grid-cols-2">
            <WorkflowMiniTable title="Supplier quotes pending" href="#supplier-quotes">
              <div id="supplier-quotes">
                <DataTable rows={dashboard.supplierQuotesPending} columns={supplierQuoteColumns} />
              </div>
            </WorkflowMiniTable>
            <WorkflowMiniTable title="Company inventory">
              <div className="space-y-2">
                {dashboard.companyInventory.map((company) => (
                  <div key={company.companyId} className="grid grid-cols-[1fr_auto] gap-3 border-b border-border py-2 text-sm last:border-b-0">
                    <div>
                      <Link href={`/companies/${company.companyId}`} className="font-semibold text-foreground hover:text-accent">
                        {company.companyName}
                      </Link>
                      <div className="mt-1 text-xs text-muted">{company.internalUnits} internal · {company.externalUnits} external · {company.watchItems} watch</div>
                    </div>
                    <div className="font-mono text-sm font-semibold text-foreground">{money(company.stockValue, company.currency)}</div>
                  </div>
                ))}
              </div>
            </WorkflowMiniTable>
          </div>

          <div className="grid gap-4 2xl:grid-cols-2">
            <DetailPanel id="po" title="Purchase orders">
              <DataTable rows={dashboard.purchaseOrders} columns={orderColumns} />
            </DetailPanel>
            <DetailPanel title="Sales orders">
              <DataTable rows={dashboard.salesOrders} columns={orderColumns} />
            </DetailPanel>
          </div>
        </div>

        <aside className="space-y-4">
          <DetailPanel title="Quick actions">
            <div className="grid grid-cols-2 gap-2">
              {dashboard.quickActions.map((action) => (
                <ButtonLink key={action.label} href={action.href} variant={action.priority === "primary" ? "primary" : "secondary"} className="justify-start">
                  {action.label}
                </ButtonLink>
              ))}
            </div>
          </DetailPanel>

          <DetailPanel title="KPIs and margins">
            {dashboard.marginKpis.map((item) => (
              <MarginCell key={item.label} item={item} />
            ))}
            <div className="mt-3 rounded-md border border-border bg-panel-muted p-3">
              <PriorityRow label="Internal units" value={String(dashboard.stockValue.internalUnits)} />
              <PriorityRow label="External units" value={String(dashboard.stockValue.externalUnits)} />
              <PriorityRow label="Qty 0 visible" value={String(dashboard.stockValue.zeroQtyVisible)} tone={dashboard.stockValue.zeroQtyVisible > 0 ? "warning" : "neutral"} />
            </div>
          </DetailPanel>

          <DetailPanel id="repair" title="Repairs / exchanges / leases">
            <div className="space-y-2">
              {dashboard.serviceWorkflows.map((workflow) => (
                <div key={workflow.id} className="grid grid-cols-[1fr_auto] gap-3 border-b border-border py-2 text-sm last:border-b-0">
                  <div>
                    <div className="font-mono font-semibold text-foreground">{workflow.reference}</div>
                    <div className="mt-1 text-xs text-muted">{workflow.kind} · {workflow.companyName} · {workflow.partNumber}</div>
                  </div>
                  <StatusBadge status={workflow.status} />
                </div>
              ))}
            </div>
          </DetailPanel>

          <DetailPanel id="documents" title="Documents pending">
            <div className="space-y-2">
              {dashboard.documentsPending.map((document) => (
                <div key={document.id} className="flex items-center justify-between gap-3 border-b border-border py-2 text-sm last:border-b-0">
                  <div>
                    <div className="font-semibold text-foreground">{document.documentType}</div>
                    <div className="mt-1 text-xs text-muted">{document.entityType} · {document.entityId} · due {document.dueAt}</div>
                  </div>
                  <StatusBadge status={document.status} />
                </div>
              ))}
            </div>
          </DetailPanel>

          <DetailPanel title="Accounting alerts">
            <AccountingList alerts={dashboard.accountingAlerts} />
          </DetailPanel>

          <DetailPanel title="Recent activity">
            <EntityTimeline events={dashboard.recentActivity} />
          </DetailPanel>
        </aside>
      </div>
    </AppShell>
  );
}
