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
    lastUpdated: "2026-07-07",
    dataNote:
      "Static snapshot baked at build time from APP_RECAP.md / PROJECT_STATE.json / git log - not a live query, and it cannot self-reference the commit that introduces a status change. Update this file alongside CTO_STATUS.md on every status-relevant change.",
    global: {
      version: "0.1.0",
      branch: "main",
      lastDeployedCommit: "bb0ba80",
      lastGithubCommit: "bb0ba80",
      buildStatus: "passing",
      testStatus: "passing",
      deploymentStatus: "Static frontend on staging/public path, deployed 2026-07-07 with /admin protected by Apache Basic Auth. API runtime never deployed."
    },
    buildMetadata: {
      branch: "main",
      latestLocalCommit: "bb0ba80",
      latestOriginMainCommit: "bb0ba80",
      buildTimestamp: "2026-07-07T15:42:00Z",
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
      lastCheckedAt: "2026-07-07T15:42:00Z"
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
        "No database, Yoyamic, or legacy PHP changes were made during the CTO protection deploy."
      ]
    },
    modules: [
      { module: "Core", status: "operational", progressPct: 85, sprint: "Foundation", lastCommit: "141fee0", reviewStatus: "Not formally reviewed", deployStatus: "Static export only", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Authentication", status: "foundation", progressPct: 35, sprint: "Auth/Tenant Foundation", lastCommit: "5372dc2", reviewStatus: "Not reviewed", deployStatus: "Not deployed (API runtime never deployed)", nextAction: "Persistent session/audit store, MFA, rate limiting" },
      { module: "Dashboard", status: "foundation", progressPct: 65, sprint: "Tenant ERP Dashboard", lastCommit: "05b04d7", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to legacy adapter" },
      { module: "Company 360", status: "foundation", progressPct: 55, sprint: "Part/Stock/Company Inventory read models", lastCommit: "366e375", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Part 360", status: "foundation", progressPct: 55, sprint: "Part/Stock/Company Inventory read models", lastCommit: "366e375", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Stock 360", status: "foundation", progressPct: 55, sprint: "Part/Stock/Company Inventory read models", lastCommit: "366e375", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Company Inventory", status: "foundation", progressPct: 50, sprint: "Part/Stock/Company Inventory read models", lastCommit: "366e375", reviewStatus: "Not reviewed", deployStatus: "Not deployed", nextAction: "Map to read-only Yoyamic adapter" },
      { module: "Documents", status: "in-progress", progressPct: 40, sprint: "Documents Phase 1.1 / Phase 2 planning", lastCommit: "088e9d8", reviewStatus: "46f6e72, f14ddd4 reviewed; 088e9d8 pending review", deployStatus: "Static UI on staging; API runtime not deployed", nextAction: "Review 088e9d8; hold Phase 2 build for persistent audit + API deploy" },
      { module: "Warehouse", status: "not-started", progressPct: 0, sprint: "-", lastCommit: "-", reviewStatus: "-", deployStatus: "-", nextAction: "Not yet scoped as a distinct module" },
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
      { module: "API", status: "foundation", progressPct: 40, sprint: "Documents Phase 1.1 hardening", lastCommit: "088e9d8", reviewStatus: "Reads reviewed as part of Documents review", deployStatus: "Never deployed as a running service", nextAction: "Deploy API runtime for the first time; add mutation routes behind RBAC" },
      { module: "Security", status: "foundation", progressPct: 35, sprint: "CTO protection", lastCommit: "bb0ba80", reviewStatus: "Basic Auth protection deployed for /admin", deployStatus: "Static /admin protected on staging", nextAction: "Persistent audit storage, RBAC hardening, rate limiting, secrets manager" },
      { module: "Multi-Tenant", status: "foundation", progressPct: 50, sprint: "-", lastCommit: "f14ddd4", reviewStatus: "Tenant scoping reviewed per-module", deployStatus: "Not deployed", nextAction: "Validate isolation with a second real tenant; add DB-level constraints once persistence exists" }
    ],
    currentSprint:
      "CTO Dashboard Phase 2 adds static build, deployment, check, security, and activity metadata to the protected /admin/cto route.",
    nextSprint:
      "Review the protected CTO metadata snapshot, then resume the read-only legacy MySQL adapter mapping for Dashboard/Part 360/Stock 360/Company Inventory. Hold Documents Phase 2 until infrastructure and API runtime decisions are approved.",
    blockers: [
      "No persistent auth/session/audit storage - blocks real mutations and Documents Phase 2 audit requirements.",
      "API runtime has never been deployed - required before Documents Phase 2 or any real upload/signed-URL work.",
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
      "This CTO dashboard's data is a hand-maintained snapshot; it will drift from reality if not updated alongside future commits."
    ],
    architectureDecisions: [
      "Adapter-first: legacy Yoyamic is read through adapters only, never mutated from this codebase.",
      "Tenant isolation is enforced server-side via RequestContext at the repository/read-model layer, not as a UI filter.",
      "RFQ_ID remains the canonical business workflow key; quote_id is not a substitute.",
      "Document ownership is single-sourced from the DocumentLinkRecord with relation \"primary\"; DocumentRecord does not duplicate owner fields (Phase 1.1, f14ddd4).",
      "The web frontend ships as a static export with no server runtime yet; every page's data is baked at build time from typed fixtures, not live queries.",
      "CTO dashboard protection is server-side Apache Basic Auth for /SaaS_Aviation/admin/; credentials are not committed.",
      "Mutations stay blocked until persistent auth/session/audit storage replaces the current in-memory foundation."
    ],
    activity: [
      { commit: "bb0ba80", date: "2026-07-07", author: "Afterlife", summary: "Protect CTO Dashboard" },
      { commit: "3c32468", date: "2026-07-07", author: "Afterlife", summary: "Build CTO Dashboard" },
      { commit: "20bdc67", date: "2026-07-07", author: "Afterlife", summary: "Add CTO status dashboard" },
      { commit: "088e9d8", date: "2026-07-06", author: "Afterlife", summary: "Harden documents read error handling" },
      { commit: "362c4a7", date: "2026-07-02", author: "Afterlife", summary: "Add documents phase 2 architecture" },
      { commit: "f14ddd4", date: "2026-07-02", author: "Afterlife", summary: "Correct documents phase 1 foundation" },
      { commit: "46f6e72", date: "2026-07-02", author: "Afterlife", summary: "Build documents foundation" },
      { commit: "366e375", date: "2026-07-02", author: "Afterlife", summary: "Build part stock inventory read models" },
      { commit: "05b04d7", date: "2026-07-01", author: "root", summary: "Build tenant ERP dashboard" },
      { commit: "5372dc2", date: "2026-06-30", author: "root", summary: "feat: add auth tenant foundation" }
    ]
  };
}
