# Part Stock Inventory Read Model Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the first tenant-scoped read-model foundation for Part 360, Stock 360, and Company Inventory without real mutations.

**Architecture:** Add shared read-model composition in `packages/shared`, expose it through the sample adapter and protected API routes, then consume the same fixtures in dense Next.js pages. Boundary actions describe required data and future owner modules, but do not persist or touch Yoyamic.

**Tech Stack:** TypeScript, Node test runner, Express, Next.js App Router, existing SaaS Aviation components.

---

### Task 1: Shared Read Models

**Files:**
- Modify: `SaaS_Aviation/packages/shared/src/types.ts`
- Modify: `SaaS_Aviation/packages/shared/src/contracts.ts`
- Modify: `SaaS_Aviation/packages/shared/src/sample-data.ts`
- Create: `SaaS_Aviation/packages/shared/src/part-stock-service.ts`
- Modify: `SaaS_Aviation/packages/shared/src/domain.test.ts`
- Modify: `SaaS_Aviation/packages/shared/src/index.ts`

- [ ] Add tests proving tenant scoping, Part 360 links, Stock 360 document/lifecycle links, company inventory summaries, and action boundaries.
- [ ] Run `npm run test -w @saas-aviation/shared` and verify the new tests fail because the service does not exist.
- [ ] Implement tenant-filtered read-model builders and realistic sample fixtures.
- [ ] Run `npm run test -w @saas-aviation/shared` and verify the tests pass.

### Task 2: API Contracts

**Files:**
- Modify: `SaaS_Aviation/apps/api/src/adapters/sample-data-source.ts`
- Modify: `SaaS_Aviation/apps/api/src/server.ts`
- Modify: `SaaS_Aviation/apps/api/src/openapi/openapi.ts`
- Modify: `SaaS_Aviation/apps/api/src/server.test.ts`

- [ ] Add tests proving the sample source and OpenAPI expose Part 360, Stock 360, and Company Inventory read routes.
- [ ] Run `npm run test -w @saas-aviation/api` and verify the new tests fail before route/source implementation.
- [ ] Implement protected read route handlers and schemas only.
- [ ] Run `npm run test -w @saas-aviation/api` and verify the tests pass.

### Task 3: Web UI

**Files:**
- Modify: `SaaS_Aviation/apps/web/src/lib/data.ts`
- Create: `SaaS_Aviation/apps/web/src/lib/part-stock.ts`
- Modify: `SaaS_Aviation/apps/web/src/lib/data.test.ts`
- Create: `SaaS_Aviation/apps/web/src/components/modules/workflow-boundary-panel.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/parts/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/parts/[id]/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/stock/internal/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/stock/external/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/stock/internal/[id]/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/app/companies/[id]/page.tsx`
- Modify: `SaaS_Aviation/apps/web/src/components/erp/sidebar.tsx`

- [ ] Add web data tests for tenant-scoped read models and boundary actions.
- [ ] Run `npm run test -w @saas-aviation/web` and verify tests fail before web adapter implementation.
- [ ] Replace placeholder document/action text with explicit read-only workflow boundary panels.
- [ ] Add dense Part 360, Stock 360, and Company Inventory panels using shared read models.
- [ ] Run `npm run test -w @saas-aviation/web` and verify tests pass.

### Task 4: Docs, Verification, Commit

**Files:**
- Modify: `APP_RECAP.md`
- Modify: `PROJECT_STATE.json`
- Create: `docs/business/part-360.md`
- Create: `docs/business/stock.md`
- Create: `docs/database/stock.md`

- [ ] Document scope, Yoyamic business logic reused, mutation boundaries, and known gaps.
- [ ] Run `npm run test`, `npm run typecheck`, and `npm run build` from `SaaS_Aviation`.
- [ ] Inspect `git diff --check` and `git status --short`.
- [ ] Commit the implementation with a focused message.
