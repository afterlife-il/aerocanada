import { notFound } from "next/navigation";
import DashboardPage from "../dashboard/page";

const initialTenant = { slug: "AeroCanada", code: "aci770" } as const;

export function generateStaticParams() {
  return [{ tenantSlug: initialTenant.slug }];
}

export default async function TenantWorkspacePage({ params }: { params: Promise<{ tenantSlug: string }> }) {
  const { tenantSlug } = await params;
  if (tenantSlug !== initialTenant.slug) notFound();
  return <DashboardPage />;
}
