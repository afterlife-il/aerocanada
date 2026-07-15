import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { CompanyProductionWorkspace } from "@/components/modules/company-production-workspace";

export default function CompaniesPage() {
  return (
    <AppShell>
      <PageHeader eyebrow="Company workspace" title="Companies" description="Customers, suppliers, owners, repair vendors, and future SaaS tenant candidates." />
      <CompanyProductionWorkspace initialCompanies={[]} />
    </AppShell>
  );
}
