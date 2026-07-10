# Stock Data Model Notes

Last updated: 2026-07-02

## Current State

No live database schema was changed for this slice. The implementation uses tenant-scoped TypeScript fixtures and read-model builders in `SaaS_Aviation/packages/shared`.

## Read Model Entities

- `PartNumber`: tenant-owned part identity and alternate metadata.
- `StockItem`: tenant-owned stock line with source, owner/company, supplier, tag info, traceability, location, condition, quantity, status, cost, and legacy id.
- `DocumentAlert`: read-only document/certificate status linked to stock, quote, PO, or SO entities.
- `AuditEvent`: read-only lifecycle/traceability event source.
- `CompanyInventoryReadModel`: derived tenant inventory by company relationship.
- `Part360ReadModel` and `Stock360ReadModel`: derived views for operational screens.

## Future Persistence Requirements

Before real persistence:

- Every stock, document, certificate, traceability, reservation, and movement row must include `tenant_id`.
- Repository methods must require `RequestContext`.
- Queries must filter by `tenant_id` at database level.
- Stock movement and reservation need auditable ledger tables.
- Document upload needs storage keys, malware scan status, retention metadata, and entity relationship records.
- Owner/company, supplier, tag info company, and traceability company must remain separate foreign keys.

## Migration Notes From Yoyamic

Yoyamic business behavior to preserve:

- ACI770 stock is distinct from external supplier availability.
- Qty `0` rows are meaningful and must remain visible.
- Owner/company is not the same as tag info.
- RFQ workflow links should preserve `RFQ_ID`.
- Stock lifecycle changes must be explicit and auditable.
## Persistence Foundation Phase 1

Dedicated SaaS_Aviation stock persistence is defined in `stock_items` in `SaaS_Aviation/database/migrations/001_core_persistence.sql`.

The schema preserves independent company relationships for owner, supplier, tag info, and traceability. Quantity `0` records are valid. `location_text` is a temporary migration field until Warehouse/location modeling is approved.

Legacy Yoyamic source references identified from PHP code include `tb_stock_part`, `tbl_Stock_external`, `tbl_Condition`, `tbl_Release`, and `tbl_Currency`. These were not queried live in this sprint.
