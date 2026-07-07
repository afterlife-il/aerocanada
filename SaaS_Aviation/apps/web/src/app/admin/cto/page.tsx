import type { ReactNode } from "react";
import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DataTable } from "@/components/ui/data-table";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { getCtoStatus, type CtoModuleRow } from "@/lib/cto-status";

function ListPanel({ title, items }: { title: string; items: string[] }) {
  return (
    <DetailPanel title={title}>
      <ul className="space-y-2">
        {items.map((item) => (
          <li key={item} className="border-b border-border pb-2 text-sm text-foreground last:border-b-0 last:pb-0">
            {item}
          </li>
        ))}
      </ul>
    </DetailPanel>
  );
}

function MetadataCard({ title, rows }: { title: string; rows: Array<{ label: string; value: ReactNode }> }) {
  return (
    <DetailPanel title={title}>
      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
        {rows.map((row) => (
          <KeyValue key={row.label} label={row.label} value={row.value} />
        ))}
      </div>
    </DetailPanel>
  );
}

const moduleColumns = [
  { key: "module", header: "Module", cell: (row: CtoModuleRow) => <span className="font-semibold">{row.module}</span> },
  { key: "status", header: "Status", cell: (row: CtoModuleRow) => <StatusBadge status={row.status} /> },
  { key: "progress", header: "Progress", cell: (row: CtoModuleRow) => <span className="font-mono">{row.progressPct}%</span>, className: "text-right" },
  { key: "sprint", header: "Sprint", cell: (row: CtoModuleRow) => row.sprint },
  { key: "commit", header: "Last Commit", cell: (row: CtoModuleRow) => <span className="font-mono">{row.lastCommit}</span> },
  { key: "review", header: "Review Status", cell: (row: CtoModuleRow) => row.reviewStatus },
  { key: "deploy", header: "Deploy Status", cell: (row: CtoModuleRow) => row.deployStatus },
  { key: "next", header: "Next Action", cell: (row: CtoModuleRow) => row.nextAction }
];

export default function CtoDashboardPage() {
  const status = getCtoStatus();

  return (
    <AppShell>
      <PageHeader
        eyebrow="Internal — development team only"
        title="CTO Dashboard"
        description="Not part of the customer ERP. Project-status snapshot for engineering leadership: build/deploy health, module progress, blockers, and roadmap."
      />

      <div className="mb-4 rounded-lg border border-[oklch(0.74_0.17_25)] bg-[oklch(0.97_0.02_25)] px-4 py-3 text-sm text-[oklch(0.4_0.15_25)]">
        Internal tool. The deployed `/SaaS_Aviation/admin/` path is protected by Apache Basic Auth. {status.dataNote}
      </div>

      <div className="overflow-hidden rounded-lg border border-border bg-panel">
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7">
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Version" value={status.global.version} /></div>
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Branch" value={status.global.branch} /></div>
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Last Deployed Commit" value={status.global.lastDeployedCommit} /></div>
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Last GitHub Commit" value={status.global.lastGithubCommit} /></div>
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Build" value={<StatusBadge status={status.global.buildStatus} />} /></div>
          <div className="border-r border-b border-border px-3 py-3"><KeyValue label="Tests" value={<StatusBadge status={status.global.testStatus} />} /></div>
          <div className="border-b border-border px-3 py-3 lg:col-span-2 2xl:col-span-1"><KeyValue label="Deployment" value={status.global.deploymentStatus} /></div>
        </div>
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-2 2xl:grid-cols-4">
        <MetadataCard
          title="Build Metadata"
          rows={[
            { label: "Current Branch", value: status.buildMetadata.branch },
            { label: "Latest Local Commit", value: <span className="font-mono">{status.buildMetadata.latestLocalCommit}</span> },
            { label: "Latest Origin/Main", value: <span className="font-mono">{status.buildMetadata.latestOriginMainCommit}</span> },
            { label: "Build Timestamp", value: status.buildMetadata.buildTimestamp },
            { label: "Export Mode", value: status.buildMetadata.staticExportMode }
          ]}
        />
        <MetadataCard
          title="Deployment"
          rows={[
            { label: "Last Deployed Commit", value: <span className="font-mono">{status.deployment.lastDeployedCommit}</span> },
            { label: "Last Deployment Date", value: status.deployment.lastDeploymentDate },
            { label: "Environment", value: status.deployment.environment },
            { label: "Backup Path", value: <span className="break-all font-mono text-xs">{status.deployment.backupPath}</span> },
            { label: "Admin Status", value: status.deployment.protectedAdminStatus }
          ]}
        />
        <MetadataCard
          title="Checks"
          rows={[
            { label: "Tests", value: <StatusBadge status={status.checks.testStatus} /> },
            { label: "Typecheck", value: <StatusBadge status={status.checks.typecheckStatus} /> },
            { label: "Lint", value: <StatusBadge status={status.checks.lintStatus} /> },
            { label: "Build", value: <StatusBadge status={status.checks.buildStatus} /> },
            { label: "Last Checked", value: status.checks.lastCheckedAt }
          ]}
        />
        <MetadataCard
          title="Security"
          rows={[
            { label: "/Admin Basic Auth", value: status.security.adminProtectedByBasicAuth ? "Enabled" : "Missing" },
            { label: "Public Sidebar", value: status.security.ctoRouteHiddenFromPublicSidebar ? "CTO route hidden" : "CTO route exposed" },
            { label: "API Runtime", value: status.security.apiRuntimeDeployed ? "Deployed" : "Not deployed" },
            { label: "DB/Yoyamic", value: status.security.dbOrYoyamicTouched ? "Touched" : "Not touched" }
          ]}
        />
      </div>

      <div className="mt-4">
        <DetailPanel title="Modules">
          <div className="overflow-x-auto">
            <DataTable rows={status.modules} columns={moduleColumns} />
          </div>
        </DetailPanel>
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-2">
        <DetailPanel title="Current Sprint">
          <p className="text-sm text-foreground">{status.currentSprint}</p>
        </DetailPanel>
        <DetailPanel title="Next Sprint">
          <p className="text-sm text-foreground">{status.nextSprint}</p>
        </DetailPanel>
      </div>

      <div className="mt-4 grid gap-4 xl:grid-cols-3">
        <ListPanel title="Blockers" items={status.blockers} />
        <ListPanel title="Technical Debt" items={status.technicalDebt} />
        <ListPanel title="Architecture Decisions" items={status.architectureDecisions} />
      </div>

      <div className="mt-4">
        <DetailPanel title="Latest Activity">
          <div className="space-y-3">
            {status.activity.map((entry) => (
              <div key={entry.commit} className="grid gap-1 border-b border-border pb-3 text-sm last:border-b-0 last:pb-0 sm:grid-cols-[100px_90px_120px_1fr] sm:gap-3">
                <div className="font-mono text-xs text-muted">{entry.date}</div>
                <div className="font-mono text-xs text-muted">{entry.commit}</div>
                <div className="text-xs text-muted">{entry.author}</div>
                <div className="text-foreground">{entry.summary}</div>
              </div>
            ))}
          </div>
        </DetailPanel>
      </div>
    </AppShell>
  );
}
