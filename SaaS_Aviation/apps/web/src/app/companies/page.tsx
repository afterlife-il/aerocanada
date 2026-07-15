import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { CompanyProductionWorkspace } from "@/components/modules/company-production-workspace";
import { getCompanyListReadModel } from "@/lib/data";
import { getDataSourceConfig } from "@/lib/data-source-mode";

export default function CompaniesPage() {
  const config = getDataSourceConfig();
  const initialCompanies = config.mode === "persistent-api" ? [] : getCompanyListReadModel({ pageSize: 100 }).rows.map(({ company }) => ({ id: company.id, tenantId: company.tenantId, name: company.name, roles: [company.type], status: company.riskLevel === "blocked" ? "blocked" as const : "active" as const, ...(company.primaryEmail ? { email: company.primaryEmail } : {}), ...(company.website ? { website: company.website } : {}), ...(company.city ? { city: company.city } : {}), ...(company.country ? { country: company.country } : {}), tags: company.tags, updatedAt: company.lastActivityAt ?? "" }));

  return (
    <AppShell>
      <PageHeader eyebrow="Company workspace" title="Companies" description="Customers, suppliers, owners, repair vendors, and future SaaS tenant candidates." />
      <CompanyProductionWorkspace initialCompanies={initialCompanies} />
    </AppShell>
  );
}
