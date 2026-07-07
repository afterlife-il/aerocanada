# Part 360 Data Model Notes

Last updated: 2026-07-07

## Current State

No live database schema was added for this slice. Part 360 remains a pure aggregation layer over the existing
tenant-scoped TypeScript fixtures and read-model builders in `SaaS_Aviation/packages/shared`. It does not own any
entity; every field it exposes is either read directly from `PartNumber` / `StockItem` / `RfqSummary` /
`QuoteSummary` / `SupplierQuoteSummary` / `OrderSummary` / `ServiceWorkflowSummary` / `DocumentAlert` /
`AuditEvent` (see `docs/database/stock.md`), or derived from them in `part-stock-service.ts`.

## Read Model Entities (New For This Slice)

- `PartHeaderSummary` — derived, not persisted. Computed per request from the part's linked stock, documents,
  quotes, and audit events:
  - `availabilityStatus`: `in-stock` if internal units > 0, else `external-only` if external units > 0, else
    `quoted-only` if a customer quote exists, else `no-stock`.
  - `conditionSummary`: stock lines grouped by `condition` (qty and line count per condition).
  - `certificationIndicators`: for `8130-3` / `EASA Form 1` / `CoC`, whether a linked `DocumentAlert` of that type
    exists (`status` mirrors the alert's status, or `missing` if none).
  - `lastUpdatedAt`: the latest of stock `entryDate`, customer quote `dueAt`, RFQ `createdAt`, order `dueAt`,
    service workflow `dueAt`, and audit event `occurredAt` for this part.
- `PartTraceabilitySummary` — derived, not persisted:
  - `previousOwners`: unique `traceabilityCompany` values across the part's stock lines.
  - `origins`: unique `supplierCompany` values across the part's stock lines.
  - `repairReferences`: the part's `ServiceWorkflowSummary` rows where `kind === "repair"`.
  - `certificationChain`: the part's certificate-type `DocumentAlert` rows (same set as `Part360ReadModel.certificates`).
  - `serialTraceability`: one row per stock line that has a `serialNumber`, carrying stock id/legacy id, source,
    condition, status, owner company, and traceability company.
  - `events`: the part's linked `AuditEvent` rows (same set as `Part360ReadModel.traceability`).

Both are attached to `Part360ReadModel` as `header` and `traceabilitySummary` respectively. See
`SaaS_Aviation/packages/shared/src/types.ts` for the full field-level types and
`SaaS_Aviation/packages/shared/src/part-stock-service.ts` for the derivation functions
(`partHeaderSummary`, `partTraceabilitySummary`, `partConditionSummary`, `partCertificationIndicators`,
`partAvailabilityStatus`, `latestIsoDate`).

## Future Persistence Requirements

Before real persistence, in addition to the requirements already listed in `docs/database/stock.md`:

- `PartHeaderSummary` and `PartTraceabilitySummary` should remain **derived views**, not their own tables — they
  are aggregation-layer projections over stock/document/quote/order/audit rows that already carry `tenant_id`.
  Recomputing them per request (or via a cached read-model refresh) keeps Part 360 from owning data that belongs
  to another module.
- If a future Supplier Quote module adds price/lead-time/condition/validity fields to supplier quotes, Part 360's
  Supplier Quotes panel should start rendering them without any Part 360 schema change — it already renders
  whatever `SupplierQuoteSummary` exposes.
- If future Purchase Orders / Sales Orders modules become first-class (their own tables, own workflow), Part 360's
  Purchase History / Sales History panels should keep consuming their read API without embedding PO/SO business
  logic here.

## Migration Notes From Yoyamic

No changes beyond what `docs/database/stock.md` already documents. Part 360 does not introduce new Yoyamic-facing
behavior; it aggregates the same stock/RFQ/quote/order/document/audit rows that Stock 360 and Company Inventory
already read.
