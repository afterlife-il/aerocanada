# Part 360

Last updated: 2026-07-07

## Scope

Part 360 is the tenant-scoped **business workspace** for a single Part Number in `SaaS_Aviation/`. It is an
aggregation layer: it connects part identity, alternates, aircraft/ATA/IPC metadata, ACI stock, external stock,
RFQs, supplier quotes, customer quotes, purchase and sales history, service history, documents/certificates, and
traceability, plus header-level status, condition, and certification summaries and margin context. It never
implements Warehouse, RFQ, Supplier Quotes, Customer Quotes, Purchase Orders, Sales Orders, or Documents workflow
logic — those modules own their own workflows; Part 360 only exposes their read data and marks the workflow
boundary where a future module would take over.

## Implementation

- Shared read-model builder: `SaaS_Aviation/packages/shared/src/part-stock-service.ts`.
- Shared types: `SaaS_Aviation/packages/shared/src/types.ts` (`Part360ReadModel`, `PartHeaderSummary`,
  `PartTraceabilitySummary`, and their supporting row types).
- API contract: `GET /v1/parts/{id}/360` (component schemas in `SaaS_Aviation/apps/api/src/openapi/openapi.ts`).
- Static web adapter: `SaaS_Aviation/apps/web/src/lib/part-stock.ts`.
- UI surfaces: `/parts` and `/parts/[id]`.

All reads require `RequestContext` in the shared/API layer. Data is filtered by `context.tenant.tenantId` before
relationships are composed.

## Part 360 Workspace Panels

The `/parts/[id]` page composes the following panels, each backed by its own section of the `Part360ReadModel`:

1. **Part Header** (`PartHeaderSummaryBar`) — availability status (`in-stock` / `external-only` / `quoted-only` /
   `no-stock`), condition summary chips, certification indicator chips (8130-3 / EASA Form 1 / CoC present vs.
   missing), and last-updated timestamp, alongside the page title (PN/description) and the Quick Actions bar.
2. **Part Identity / Availability** — manufacturer, ATA, IPC, aircraft, alternates, legacy ID, stock availability
   counts, and margin, read-only.
3. **Internal Stock** and **External Stock** — dense stock tables reusing `stockColumns()`, each wrapped in a
   `StatePanel` with explicit loading/empty/error handling (see below). Read-only; no Warehouse logic.
4. **Related RFQs**, **Supplier Quotes**, **Customer Quotes** — each its own panel with a "Create RFQ" /
   "Add Supplier Quote" / "Add Customer Quote" workflow-boundary link in the panel header, scrolling to the Quick
   Action Boundaries panel. Supplier Quotes carries a boundary note explaining that price/lead time/condition are
   not modeled yet because the Supplier Quote module does not exist as a first-class module.
5. **Purchase History** and **Sales History** — explicit placeholder-style panels. They read already-aggregated
   `OrderSummary` rows linked through this part's RFQs (no PO/SO workflow logic is implemented), with a boundary
   note and an empty-state future-integration message when there is nothing linked yet.
6. **Documents & Certificates** — reuses the existing `DocumentPanel` from the Documents module (entity-linked
   reads only) with an "Upload Certificate" workflow-boundary link. Documents module internals are untouched.
7. **Traceability** — a dedicated `TraceabilityPanel` showing previous owner(s), origin/supplier(s), serial
   traceability (per serialized stock line), repair references (service workflows of kind `repair`), certification
   chain (linked cert alerts), and a traceability event timeline. Read-only.
8. **Quick Action Boundaries** — the existing `WorkflowBoundaryPanel`, anchored as `#quick-actions` and linked from
   every panel-level boundary CTA above.

## Loading / Empty / Error States

Part 360 read-model computation is synchronous (static sample fixtures, no network I/O), so a fabricated per-panel
spinner would never actually render — that would be a fake state. Instead:

- **Loading** is handled by the real Next.js route convention: `apps/web/src/app/parts/[id]/loading.tsx` renders
  skeleton panels via the shared `LoadingState` component during route transitions/streaming.
- **Empty** and **error** are handled per panel by `apps/web/src/lib/panel-data.ts` (`resolvePanelRows`), which
  wraps each panel's data access in a try/catch: zero rows renders the shared `EmptyState`, a thrown error renders
  the shared `ErrorState`. This is deliberately real, not decorative — once a panel's data source is a live
  network-backed adapter, `resolvePanelRows` will already report its failures without further changes to the page.

## Yoyamic Logic Preserved

- Part number remains the identity key users recognize.
- `RFQ_ID` remains visible and is used for workflow links.
- ACI-owned stock and external supplier stock stay separate.
- Supplier quote and customer quote contexts are separate.
- Certificates and traceability are represented as linked read data.
- Margin is derived from customer quote value and cost, not from display-only text.

## Mutation Boundaries

Part 360 quick actions are boundary panels only:

- Create RFQ: future RFQ module.
- Create supplier quote: future Supplier Quote module.
- Create customer quote: future Customer Quote module.
- Add stock: future Inventory module.
- Upload certificate/document: future Document module.

No action persists data, modifies Yoyamic, or touches a live database in this slice.

## Shared Code Changes And Why

Part 360 completion required a small number of additive, backward-compatible shared changes:

- `Part360ReadModel` gained two new fields, `header: PartHeaderSummary` and
  `traceabilitySummary: PartTraceabilitySummary`. Both are computed in `part-stock-service.ts` from data already
  present in the read model (stock, documents, service history, audit events) — no new sample data or fake fields
  were introduced. Existing fields (`traceability: AuditEvent[]`, etc.) were left in place for backward
  compatibility.
- `DetailPanel`, `DocumentPanel`, and `WorkflowBoundaryPanel` (all in `apps/web/src/components`) gained an optional
  `actions`/`id` prop so Part 360 panels can carry header-level workflow-boundary links and real anchor targets.
  Both are optional and additive; Company 360 and Stock 360 usage is unchanged.
- `EntityTabs` now optionally accepts `{ label, href }` tab entries in addition to plain strings, so Part 360's tab
  strip can be real in-page anchors instead of placeholder `#` links. Existing string-array callers are unaffected.
- `StatusBadge` gained tone entries for the new `PartAvailabilityStatus` and certification-indicator status values.

## Known Gaps

- Real Yoyamic read adapter queries are still pending.
- Purchase/sales/service history is sample-backed until legacy mapping is approved.
- Document upload needs storage, malware scanning, retention, and audit design.
- Mutations remain blocked until persistent auth/session/audit foundations are complete.
- Supplier Quote fields (price, lead time, condition, validity) are not modeled yet; the Supplier Quotes panel
  intentionally shows only the fields the current `SupplierQuoteSummary` type carries (supplier, RFQ, qty, status,
  due date) rather than inventing placeholder data.
## Persistence Foundation Phase 2

Part 360 remains deployed as a static/sample read-model screen. Locally, the Express API now has tenant-scoped Part Number CRUD foundation routes backed by repository contracts, validation, and selectable memory/PostgreSQL providers:

- `GET/POST /v1/parts`
- `GET/PATCH /v1/parts/:id`

Part numbers are normalized for search and uniqueness while preserving the display part number. The uniqueness rule includes manufacturer/manufacturer code so distinct manufacturer parts are not silently collapsed.

PostgreSQL mode is local/dev only and requires explicit `DATABASE_URL` configuration. The static frontend does not connect to it unless `persistent-api` mode is selected locally.

This does not make deployed Part 360 mutations operational. Create/edit part number remains local API foundation only until the API runtime, dedicated SaaS database, production RBAC, and audit persistence are deployed.
