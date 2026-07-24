import source from "../../../../module-status.json" with { type: "json" };

export type CriterionState = "not_started" | "partial" | "passed" | "failed" | "blocked" | "not_applicable";
export type ModuleStatus = "not_started" | "architecture_only" | "in_progress" | "blocked" | "testing" | "partially_operational" | "operational" | "validated" | "deprecated";
export type EvidenceFreshness = "build-time" | "last-recorded" | "runtime-live";

export interface WeightedCriterion {
  id: string;
  label: string;
  weight: number;
  state: CriterionState;
  partialScore?: number;
  evidence?: string;
}

export interface ValidationExample {
  tenantCode: string;
  entityType: string;
  sourceSystem: string;
  sourceLegacyId?: string;
  saasEntityId: string;
  safeDisplayLabel: string;
  scenario: string;
  expectedResult: string;
  actualResult: string;
  validationDate: string;
  validator: string;
  route: string;
}

export interface ModuleOption {
  name: string;
  status: CriterionState;
  subOptions: Array<{ name: string; status: CriterionState }>;
}

export interface ModuleRecord {
  id: string;
  name: string;
  category: string;
  owner: string;
  status: ModuleStatus;
  criteria: WeightedCriterion[];
  options: ModuleOption[];
  dependencies: string[];
  blockers: string[];
  tests: string[];
  testResults: string;
  validationExamples: ValidationExample[];
  deployedCommit: string;
  documentationCommit: string;
  runtimeRevision: string;
  lastValidationDate: string;
  lastValidator: string;
  nextAction: string;
  targetPhase: string;
  notes: string[];
}

export interface ModuleWithProgress extends ModuleRecord {
  percentage: number;
  validatedCriteria: number;
  applicableCriteria: number;
  hasRegression: boolean;
}

export function calculatePercentage(criteria: WeightedCriterion[]): number {
  const applicable = criteria.filter((criterion) => criterion.state !== "not_applicable");
  const totalWeight = applicable.reduce((total, criterion) => total + criterion.weight, 0);
  if (totalWeight === 0) return 0;
  const earned = applicable.reduce((total, criterion) => {
    if (criterion.state === "passed") return total + criterion.weight;
    if (criterion.state === "partial") {
      const partial = criterion.partialScore;
      if (partial === undefined || partial < 0 || partial > criterion.weight) {
        throw new Error(`Criterion ${criterion.id} requires an explicit partialScore between 0 and its weight`);
      }
      return total + partial;
    }
    return total;
  }, 0);
  return Math.round((earned / totalWeight) * 100);
}

export function deriveStatus(module: ModuleRecord): ModuleStatus {
  if (module.status === "deprecated") return "deprecated";
  const percentage = calculatePercentage(module.criteria);
  const hasFailure = module.criteria.some((criterion) => criterion.state === "failed");
  const hasBlocker = module.criteria.some((criterion) => criterion.state === "blocked") || module.blockers.length > 0;
  const fullyPassed = module.criteria
    .filter((criterion) => criterion.state !== "not_applicable")
    .every((criterion) => criterion.state === "passed");
  const hasPublicEvidence = module.criteria.some((criterion) => criterion.id === "public-staging" && criterion.state === "passed");
  const hasTenantExample = module.validationExamples.some((example) => example.tenantCode === "aci770");
  if (fullyPassed && percentage === 100 && hasPublicEvidence && hasTenantExample) return "validated";
  if (hasFailure) return "blocked";
  if (hasBlocker) return "blocked";
  if (percentage === 0) return "not_started";
  if (module.criteria.some((criterion) => criterion.id === "business-spec" && criterion.state === "passed") && percentage <= 15) return "architecture_only";
  if (percentage >= 60) return "partially_operational";
  return "in_progress";
}

function assertCanonicalStatus(modules: ModuleRecord[]): void {
  const ids = new Set<string>();
  for (const module of modules) {
    if (ids.has(module.id)) throw new Error(`Duplicate module ID: ${module.id}`);
    ids.add(module.id);
    const total = module.criteria.reduce((sum, criterion) => sum + criterion.weight, 0);
    if (total !== 100) throw new Error(`${module.id} criteria weights total ${total}, expected 100`);
    const derived = deriveStatus(module);
    if (module.status !== derived) throw new Error(`${module.id} stores ${module.status}, but evidence derives ${derived}`);
  }
}

export function maskSafeLabel(label: string): string {
  return label
    .replace(/[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/g, "[masked-email]")
    .replace(/(?:\+?\d[\d\s().-]{6,}\d)/g, "[masked-phone]");
}

export function getCtoStatus() {
  const raw = source.modules as ModuleRecord[];
  const template = raw.find((module) => module.id === "placeholder");
  if (!template) throw new Error("Canonical status template is missing");
  const expanded = source.expandTemplateFor.map(([id, name, category]) => ({
    ...template,
    id,
    name,
    category,
    criteria: template.criteria.map((criterion) => ({ ...criterion })),
    options: [],
    dependencies: [],
    blockers: [],
    tests: [],
    validationExamples: [],
    notes: []
  })) as ModuleRecord[];
  const modules = [...raw.filter((module) => module.id !== "placeholder"), ...expanded];
  assertCanonicalStatus(modules);
  const calculated: ModuleWithProgress[] = modules.map((module) => ({
    ...module,
    percentage: calculatePercentage(module.criteria),
    status: deriveStatus(module),
    validatedCriteria: module.criteria.filter((criterion) => criterion.state === "passed").length,
    applicableCriteria: module.criteria.filter((criterion) => criterion.state !== "not_applicable").length,
    hasRegression: module.criteria.some((criterion) => criterion.state === "failed")
  }));
  const overallPercentage = Math.round(calculated.reduce((sum, module) => sum + module.percentage, 0) / calculated.length);
  return {
    schemaVersion: source.schemaVersion,
    lastUpdated: source.lastUpdated,
    freshness: source.freshness as EvidenceFreshness,
    dataNote: source.dataNote,
    tenant: source.tenant,
    runtime: source.runtime,
    tests: source.tests,
    modules: calculated,
    overallPercentage,
    validatedModules: calculated.filter((module) => module.status === "validated").length,
    partialModules: calculated.filter((module) => ["in_progress", "partially_operational", "testing", "architecture_only"].includes(module.status)).length,
    blockedModules: calculated.filter((module) => module.status === "blocked").length,
    regressions: calculated.filter((module) => module.hasRegression).length
  };
}
