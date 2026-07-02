# Part 360

Last updated: 2026-07-02

## Scope

The first Part 360 slice is a tenant-scoped read-model foundation for `SaaS_Aviation/`. It connects part identity, alternates, aircraft/ATA/IPC metadata, ACI stock, external stock, RFQs, supplier quotes, customer quotes, purchase and sales history, service history, documents, traceability, and margin context.

## Implementation

- Shared read-model builder: `SaaS_Aviation/packages/shared/src/part-stock-service.ts`.
- API contract: `GET /v1/parts/{id}/360`.
- Static web adapter: `SaaS_Aviation/apps/web/src/lib/part-stock.ts`.
- UI surfaces: `/parts` and `/parts/[id]`.

All reads require `RequestContext` in the shared/API layer. Data is filtered by `context.tenant.tenantId` before relationships are composed.

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

## Known Gaps

- Real Yoyamic read adapter queries are still pending.
- Purchase/sales/service history is sample-backed until legacy mapping is approved.
- Document upload needs storage, malware scanning, retention, and audit design.
- Mutations remain blocked until persistent auth/session/audit foundations are complete.
