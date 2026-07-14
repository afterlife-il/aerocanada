export type CtoModuleStatus = "operational" | "in-progress" | "foundation" | "planned" | "not-started" | "blocked";

export interface CtoModuleRow {
  module: string;
  status: CtoModuleStatus;
  progressPct: number;
  sprint: string;
  lastCommit: string;
  reviewStatus: string;
  deployStatus: string;
  nextAction: string;
}

export interface CtoGlobalStatus {
  version: string;
  branch: string;
  lastDeployedCommit: string;
  lastGithubCommit: string;
  buildStatus: "passing" | "failing" | "unknown";
  testStatus: "passing" | "failing" | "unknown";
  deploymentStatus: string;
}

export interface CtoBuildMetadata {
  branch: string;
  latestLocalCommit: string;
  latestOriginMainCommit: string;
  buildTimestamp: string;
  staticExportMode: string;
}

export interface CtoDeploymentMetadata {
  lastDeployedCommit: string;
  lastDeploymentDate: string;
  environment: string;
  backupPath: string;
  protectedAdminStatus: string;
}

export interface CtoChecksMetadata {
  testStatus: "passing" | "failing" | "unknown";
  typecheckStatus: "passing" | "failing" | "unknown";
  lintStatus: "passing" | "failing" | "unknown";
  buildStatus: "passing" | "failing" | "unknown";
  lastCheckedAt: string;
}

export interface CtoSecurityMetadata {
  adminProtectedByBasicAuth: boolean;
  ctoRouteHiddenFromPublicSidebar: boolean;
  apiRuntimeDeployed: boolean;
  dbOrYoyamicTouched: boolean;
  notes: string[];
}

export interface CtoActivityEntry {
  commit: string;
  date: string;
  author: string;
  summary: string;
}

export interface CtoDashboardData {
  lastUpdated: string;
  dataNote: string;
  global: CtoGlobalStatus;
  buildMetadata: CtoBuildMetadata;
  deployment: CtoDeploymentMetadata;
  checks: CtoChecksMetadata;
  security: CtoSecurityMetadata;
  modules: CtoModuleRow[];
  currentSprint: string;
  nextSprint: string;
  blockers: string[];
  technicalDebt: string[];
  architectureDecisions: string[];
  activity: CtoActivityEntry[];
}

/**
 * Snapshot, not a live query: the frontend is a static export with no server runtime
 * (`output: "export"` in next.config.ts), so this mirrors CTO_STATUS.md / PROJECT_STATE.json
 * as of the last edit to this file, the same way every other page here is sample-data-backed.
 */
export function getCtoStatus(): CtoDashboardData {
  return {
    lastUpdated: "2026-07-13",
    dataNote:
      "Static snapshot baked at build time from APP_RECAP.md / PROJECT_STATE.json / git log - not a live query, and it cannot self-reference the commit that introduces a status change. Update this file alongside CTO_STATUS.md on every status-relevant change.",
    global: {
      version: "0.1.0",
      branch: "main",
      lastDeployedCommit: "bb0ba80",
      lastGithubCommit: "8a266ba",
      buildStatus: "passing",
      testStatus: "passing",
      deploymentStatus:
        "Static frontend on staging/public path, deployed 2026-07-07 with /admin protected by Apache Basic Auth. Local commits through Yoyamic read-only adapter hardening are not deployed or pushed. API runtime and database never deployed."
    },
    buildMetadata: {
      branch: "main",
      latestLocalCommit: "Harden Company 360 persistent workflows",
      latestOriginMainCommit: "5862d57",
      buildTimestamp: "2026-07-14T19:00:00+03:00",
      staticExportMode: "Next.js output export"
    },
    deployment: {
      lastDeployedCommit: "bb0ba80",
      lastDeploymentDate: "2026-07-07T15:52:53Z",
      environment: "staging/public static frontend",
      backupPath: "/var/www/vhosts/aerocanada-industries.com/httpdocs/SaaS_Aviation_backup_20260707_155253",
      protectedAdminStatus: "/SaaS_Aviation/admin/ protected by Apache Basic Auth"
    },
    checks: {
      testStatus: "passing",
      typecheckStatus: "passing",
      lintStatus: "passing",
      buildStatus: "passing",
      lastCheckedAt: "2026-07-14T19:00:00+03:00"
    },
    security: {
      adminProtectedByBasicAuth: true,
      ctoRouteHiddenFromPublicSidebar: true,
      apiRuntimeDeployed: false,
      dbOrYoyamicTouched: false,
      notes: [
        "/admin is protected by Apache Basic Auth in the deployed static folder.",
        "The CTO route is hidden from the public sidebar.",
        "The Express API runtime was not deployed.",
        "No database, Yoyamic, or legacy PHP changes were made during the CTO protection deploy.",
        "PostgreSQL provider code is local only; runtime database verification is blocked on this machine because no postgres, psql, Docker, Podman, WSL distro, DATABASE_URL, or TEST_DATABASE_URL is available.",
        "Root CI now has a PostgreSQL service job to execute the gated integration tests when pushed.",
        "Yoyamic read-only adapter hardening is local-only and offline; no live Yoyamic query or write occurred."
      ]
    },
    modules: [
      { module: "Core", status: "operational", progressPct: 85, sprint: "Foundation", lastCommit: "141fee0", reviewStatus: "Not formally reviewed", deployStatus: "Static export only", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Authentication", status: "foundation", progressPct: 35, sprint: "Auth/Tenant Foundation", lastCommit: "5372dc2", reviewStatus: "Not reviewed", deployStatus: "Not deployed (API runtime never deployed)", nextAction: "Persistent session/audit store, MFA, rate limiting" },
      { module: "Dashboard", status: "foundation", progressPct: 65, sprint: "Tenant ERP Dashboard", lastCommit: "05b04d7", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to legacy adapter" },
      { module: "Company 360", status: "in-progress", progressPct: 90, sprint: "Company 360 Production Hardening Phase 1.1", lastCommit: "Harden Company 360 persistent workflows", reviewStatus: "PostgreSQL 15/15 zero skips; login/CRUD/restart/tenant/tests/typecheck/lint/build passed; browser unavailable", deployStatus: "Not deployed; local persistent foundation, public UI sample-static", nextAction: "Persistent auth/session and operational prerequisites" },
      { module: "Part 360", status: "foundation", progressPct: 75, sprint: "Persistent Data Foundation Phase 2", lastCommit: "786aaa7", reviewStatus: "Not reviewed", deployStatus: "Local only; API/DB not deployed", nextAction: "Run PostgreSQL integration against isolated test DB before calling workflows operational" },
      { module: "Stock 360", status: "foundation", progressPct: 60, sprint: "Persistent Data Foundation Phase 2", lastCommit: "786aaa7", reviewStatus: "Not reviewed", deployStatus: "Local only; API/DB not deployed", nextAction: "Run PostgreSQL integration against isolated test DB before calling workflows operational" },
      { module: "Company Inventory", status: "foundation", progressPct: 50, sprint: "Part/Stock/Company Inventory read models", lastCommit: "366e375", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Use hardened read-only Yoyamic adapter plans for future mapping" },
      { module: "Documents", status: "in-progress", progressPct: 40, sprint: "Documents Phase 1.1 / Phase 2 planning", lastCommit: "088e9d8", reviewStatus: "46f6e72, f14ddd4 reviewed; 088e9d8 pending review", deployStatus: "Static UI on staging; API runtime not deployed", nextAction: "Review 088e9d8; hold Phase 2 build for persistent audit + API deploy" },
      { module: "Warehouse", status: "planned", progressPct: 10, sprint: "Architecture design", lastCommit: "4df9e14", reviewStatus: "Not reviewed", deployStatus: "Documentation only; no Warehouse production code", nextAction: "Review docs/architecture/warehouse.md before any schema/API/UI implementation" },
      { module: "RFQ", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated RFQ workspace and read model" },
      { module: "Supplier Quotes", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated workspace and read model" },
      { module: "Customer Quotes", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated workspace and read model" },
      { module: "Purchase Orders", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated workspace and read model" },
      { module: "Sales Orders", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated workspace and read model" },
      { module: "Repair / Exchange / Lease", status: "planned", progressPct: 15, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Design dedicated workspace and read model" },
      { module: "Accounting", status: "planned", progressPct: 10, sprint: "-", lastCommit: "05b04d7", reviewStatus: "-", deployStatus: "Not deployed (dashboard sample model only)", nextAction: "Not yet scoped as a distinct module" },
      { module: "Reports", status: "not-started", progressPct: 0, sprint: "-", lastCommit: "-", reviewStatus: "-", deployStatus: "-", nextAction: "Not yet scoped" },
      { module: "Administration", status: "not-started", progressPct: 0, sprint: "-", lastCommit: "-", reviewStatus: "-", deployStatus: "-", nextAction: "Tenant admin / RBAC UI not yet scoped" },
      { module: "AI", status: "planned", progressPct: 5, sprint: "-", lastCommit: "-", reviewStatus: "-", deployStatus: "-", nextAction: "No LLM provider selected yet; tool-layer architecture only" },
      { module: "API", status: "foundation", progressPct: 47, sprint: "Persistent Data Foundation Phase 2 / Yoyamic adapter hardening", lastCommit: "c0d901d", reviewStatus: "Local validation passed; PostgreSQL runtime test blocked locally", deployStatus: "Never deployed as a running service", nextAction: "Run CI/local PostgreSQL integration; deploy API only after explicit approval" },
      { module: "Security", status: "foundation", progressPct: 35, sprint: "CTO protection", lastCommit: "bb0ba80", reviewStatus: "Basic Auth protection deployed for /admin", deployStatus: "Static /admin protected on staging", nextAction: "Persistent audit storage, RBAC hardening, rate limiting, secrets manager" },
      { module: "Multi-Tenant", status: "foundation", progressPct: 50, sprint: "-", lastCommit: "f14ddd4", reviewStatus: "Tenant scoping reviewed per-module", deployStatus: "Not deployed", nextAction: "Validate isolation with a second real tenant; add DB-level constraints once persistence exists" }
    ],
    currentSprint:
      "Persistent Data Foundation Phase 2 added local memory/postgres provider selection, gated PostgreSQL tests, CI Postgres service configuration, Warehouse architecture documentation, and hardened offline Yoyamic read-only migration adapter guardrails. PostgreSQL runtime verification remains blocked locally.",
    nextSprint:
      "Provision an isolated local PostgreSQL test database or use the CI Postgres job, then run migration apply/status and PostgreSQL integration tests before persistent business workflows are called operational.",
    blockers: [
      "No persistent auth/session/audit storage - blocks real mutations and Documents Phase 2 audit requirements.",
      "API runtime has never been deployed - required before Documents Phase 2 or any real upload/signed-URL work.",
      "PostgreSQL runtime verification is blocked locally: no postgres, psql, Docker, Podman, WSL distro, DATABASE_URL, or TEST_DATABASE_URL is available.",
      "Documents Phase 2 build blocked on infra decisions (object storage provider, scan engine, OCR vendor).",
      "Authenticated Yoyamic browser verification blocked (no credentials available).",
      "DB schema changes and production deployment are not approved.",
      "/admin is protected by Apache Basic Auth on staging; keep this route protected before any future public static deploy."
    ],
    technicalDebt: [
      "Legacy Yoyamic pages remain tightly coupled, mixing SQL, HTML, and workflow logic.",
      "Some staging deployment truth is report-based and should be reverified before any deployment.",
      "Authenticated Yoyamic visual verification has been blocked by login/session access.",
      "DocumentAlert (legacy compliance/expiry model) and DocumentRecord (Phase 1 file model) still need code-level reconciliation; currently only clarified in docs.",
      "Persistent API frontend client exists, but authenticated persistent workflow UI is not operational yet.",
      "Yoyamic read-only adapter now validates SQL intent and query bounds, but it still has no live read implementation or approved credentials.",
      "This CTO dashboard's data is a hand-maintained snapshot; it will drift from reality if not updated alongside future commits."
    ],
    architectureDecisions: [
      "Adapter-first: legacy Yoyamic is read through adapters only, never mutated from this codebase.",
      "Tenant isolation is enforced server-side via RequestContext at the repository/read-model layer, not as a UI filter.",
      "RFQ_ID remains the canonical business workflow key; quote_id is not a substitute.",
      "Document ownership is single-sourced from the DocumentLinkRecord with relation \"primary\"; DocumentRecord does not duplicate owner fields (Phase 1.1, f14ddd4).",
      "The web frontend ships as a static export with no server runtime yet; every page's data is baked at build time from typed fixtures, not live queries.",
      "PostgreSQL persistence mode fails fast without DATABASE_URL and never silently falls back to memory.",
      "Yoyamic migration reads must go through SELECT/SHOW-only validation, bounded query plans, dry-run reports, and legacy mapping checksums before any import is considered.",
      "Warehouse is documented as a future physical execution layer; Stock 360 remains the current stock read model and no Warehouse production code exists.",
      "CTO dashboard protection is server-side Apache Basic Auth for /SaaS_Aviation/admin/; credentials are not committed.",
      "Mutations stay blocked until persistent auth/session/audit storage replaces the current in-memory foundation."
    ],
    activity: [
      { commit: "c0d901d", date: "2026-07-13", author: "Afterlife", summary: "Harden Yoyamic read-only migration adapter" },
      { commit: "e473550", date: "2026-07-10", author: "Afterlife", summary: "Update project operational status" },
      { commit: "4df9e14", date: "2026-07-10", author: "Afterlife", summary: "Add Warehouse architecture design" },
      { commit: "11baac6", date: "2026-07-10", author: "Afterlife", summary: "Harden PostgreSQL persistence provider" },
      { commit: "786aaa7", date: "2026-07-10", author: "Afterlife", summary: "Implement PostgreSQL persistence provider" },
      { commit: "8a266ba", date: "2026-07-10", author: "Afterlife", summary: "Build persistent data foundation" },
      { commit: "ba1755f", date: "2026-07-09", author: "Afterlife", summary: "Complete Company 360 foundation" },
      { commit: "7e7fe5e", date: "2026-07-08", author: "Afterlife", summary: "Complete Part 360 foundation" },
      { commit: "d803c9d", date: "2026-07-07", author: "Afterlife", summary: "Improve CTO Dashboard metadata" },
      { commit: "bb0ba80", date: "2026-07-07", author: "Afterlife", summary: "Protect CTO Dashboard" }
    ]
  };
}
