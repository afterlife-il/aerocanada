import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { getCtoStatus, maskSafeLabel, type ModuleWithProgress } from "@/lib/cto-status";

const tone: Record<string, string> = {
  validated: "bg-emerald-100 text-emerald-800",
  operational: "bg-emerald-100 text-emerald-800",
  partially_operational: "bg-amber-100 text-amber-900",
  blocked: "bg-red-100 text-red-800",
  in_progress: "bg-blue-100 text-blue-800",
  architecture_only: "bg-blue-100 text-blue-800",
  testing: "bg-blue-100 text-blue-800",
  not_started: "bg-slate-100 text-slate-700",
  deprecated: "bg-slate-100 text-slate-700"
};

function Metric({ label, value }: { label: string; value: string | number }) {
  return <div className="rounded border border-border bg-panel p-3"><div className="text-xs uppercase tracking-wide text-muted">{label}</div><div className="mt-1 text-xl font-semibold">{value}</div></div>;
}

function Progress({ module }: { module: ModuleWithProgress }) {
  const bar = module.hasRegression ? "bg-red-600" : module.status === "validated" ? "bg-emerald-600" : module.status === "blocked" ? "bg-amber-500" : "bg-blue-600";
  return <div><div className="mb-1 flex justify-between text-xs"><span>{module.validatedCriteria}/{module.applicableCriteria} passed</span><strong>{module.percentage}%</strong></div><div className="h-2 overflow-hidden rounded bg-slate-200"><div className={`h-full ${bar}`} style={{ width: `${module.percentage}%` }} /></div></div>;
}

function ModuleDetail({ module }: { module: ModuleWithProgress }) {
  return (
    <details className="rounded border border-border bg-panel">
      <summary className="grid cursor-pointer list-none gap-3 p-3 md:grid-cols-[minmax(12rem,1.2fr)_8rem_1fr_minmax(12rem,1fr)]">
        <div><div className="font-semibold">{module.name}</div><div className="text-xs text-muted">{module.category} · {module.owner}</div></div>
        <span className={`h-fit w-fit rounded px-2 py-1 text-xs font-semibold ${tone[module.status]}`}>{module.status.replaceAll("_", " ")}</span>
        <Progress module={module} />
        <div className="text-xs"><strong>Next:</strong> {module.nextAction}</div>
      </summary>
      <div className="grid gap-4 border-t border-border p-4 xl:grid-cols-2">
        <section><h3 className="mb-2 font-semibold">Weighted checklist</h3><div className="space-y-1">{module.criteria.map((criterion) => <div key={criterion.id} className="grid grid-cols-[1fr_5rem_5rem] gap-2 border-b border-border py-1 text-xs"><span>{criterion.label}</span><span>{criterion.weight}%</span><span className={criterion.state === "failed" ? "text-red-700" : ""}>{criterion.state}{criterion.state === "partial" ? ` (${criterion.partialScore}/${criterion.weight})` : ""}</span></div>)}</div></section>
        <section><h3 className="mb-2 font-semibold">Options and sub-options</h3>{module.options.length ? module.options.map((option) => <details key={option.name} className="mb-2 rounded bg-panel-muted p-2"><summary className="cursor-pointer text-sm font-medium">{option.name} · {option.status}</summary><ul className="mt-2 pl-4 text-xs">{option.subOptions.map((sub) => <li key={sub.name}>{sub.name} — {sub.status}</li>)}</ul></details>) : <p className="text-sm text-muted">No implemented options recorded.</p>}
          <h3 className="mb-1 mt-4 font-semibold">Evidence and runtime</h3><dl className="grid grid-cols-[8rem_1fr] gap-1 text-xs"><dt>Tests</dt><dd>{module.testResults}</dd><dt>Deployed</dt><dd>{module.deployedCommit}</dd><dt>Runtime</dt><dd>{module.runtimeRevision}</dd><dt>Last validation</dt><dd>{module.lastValidationDate} · {module.lastValidator}</dd><dt>Blockers</dt><dd>{module.blockers.join("; ") || "None recorded"}</dd></dl>
        </section>
        {module.validationExamples.length > 0 && <section className="xl:col-span-2"><h3 className="mb-2 font-semibold">Safe AeroCanada validation examples</h3><div className="grid gap-2 md:grid-cols-2">{module.validationExamples.map((example) => <a className="rounded border border-border p-2 text-xs hover:bg-panel-muted" href={example.route} key={`${example.entityType}-${example.saasEntityId}`}><strong>{maskSafeLabel(example.safeDisplayLabel)}</strong><br />{example.scenario}: {example.actualResult}<br /><span className="text-muted">{example.sourceSystem} · {example.validationDate}</span></a>)}</div></section>}
      </div>
    </details>
  );
}

export default function CtoDashboardPage() {
  const status = getCtoStatus();
  return (
    <AppShell>
      <PageHeader eyebrow="Protected internal control center" title="CTO Module Progress Dashboard" description="Evidence-based Ready2Go Aviation delivery tracking. AeroCanada (aci770) is the first validation tenant, not the platform identity." />
      <div className="mb-4 rounded border border-blue-300 bg-blue-50 p-3 text-sm"><strong>{status.freshness}:</strong> {status.dataNote}</div>
      <div className="grid gap-3 sm:grid-cols-3 xl:grid-cols-6"><Metric label="Overall" value={`${status.overallPercentage}%`} /><Metric label="Modules" value={status.modules.length} /><Metric label="Validated" value={status.validatedModules} /><Metric label="Partial" value={status.partialModules} /><Metric label="Blocked" value={status.blockedModules} /><Metric label="Regressions" value={status.regressions} /></div>
      <section className="mt-4 rounded border border-border bg-panel p-4"><h2 className="font-semibold">Deployment and test evidence</h2><div className="mt-2 grid gap-2 text-xs md:grid-cols-3"><div><strong>Web:</strong> {status.runtime.webRevision}<br /><strong>API:</strong> {status.runtime.apiRevision}</div><div><strong>Database:</strong> {status.runtime.databaseMigrations}<br /><strong>Containers:</strong> {status.runtime.containerHealth}</div><div><strong>Tests:</strong> {status.tests.lastRun}<br /><strong>Public acceptance:</strong> {status.tests.publicAcceptance}</div></div></section>
      <section className="mt-4"><div className="mb-2 flex items-end justify-between"><h2 className="text-lg font-semibold">Module progress grid</h2><span className="text-xs text-muted">Expand a module for evidence</span></div><div className="space-y-2">{status.modules.map((module) => <ModuleDetail key={module.id} module={module} />)}</div></section>
    </AppShell>
  );
}
