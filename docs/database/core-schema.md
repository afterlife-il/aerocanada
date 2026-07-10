# Core SaaS_Aviation Schema

Status: PostgreSQL-compatible migration created, not deployed.

Migration: `SaaS_Aviation/database/migrations/001_core_persistence.sql`.

## Entities

- `tenants`: tenant identity and lifecycle.
- `companies`: tenant-scoped legal/business company record.
- `company_roles`: many-role company classification. A company can be customer, supplier, repair station, stock owner, consignment owner, and more at the same time.
- `contacts`: tenant-scoped contacts owned by companies.
- `part_numbers`: tenant-scoped normalized part numbers while preserving display part number.
- `stock_items`: tenant-scoped stock rows with independent owner, supplier, tag-info company, and traceability company relationships.
- `legacy_mappings`: repeatable import mapping from Yoyamic source table/id to SaaS target entity.

## Important Rules

- Every business table includes `tenant_id`.
- Part uniqueness is `(tenant_id, normalized_part_number, manufacturer/manufacturer_code)`.
- Quantity `0` is valid and must remain visible.
- Owner, supplier, tag-info, and traceability are four separate company relationships.
- `location_text` is temporary for migration until Warehouse/location modeling is approved.

## Not Deployed

This migration was validated by tests as a contract file only. It has not been run against a live database or staging database in this sprint.
