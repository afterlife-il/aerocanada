# Core SaaS_Aviation Schema

Status: PostgreSQL migration and local provider foundation, not deployed.

Migration: `SaaS_Aviation/database/migrations/001_core_persistence.sql`.

## Entities

- `schema_migrations`: migration ledger used by the explicit local migration runner.
- `tenants`: tenant identity and lifecycle.
- `companies`: tenant-scoped legal/business company record.
- `company_roles`: many-role company classification. A company can be customer, supplier, repair station, stock owner, consignment owner, and more at the same time.
- `contacts`: tenant-scoped contacts owned by companies.
- `part_numbers`: tenant-scoped normalized part numbers while preserving display part number.
- `part_alternates`: tenant-scoped alternate part number rows for future search and interchangeability.
- `stock_items`: tenant-scoped stock rows with independent owner, supplier, tag-info company, and traceability company relationships.
- `legacy_mappings`: repeatable import mapping from Yoyamic source table/id to SaaS target entity.

## Important Rules

- Every business table includes `tenant_id`.
- Cross-table relationships use tenant-composite foreign keys where the related table is tenant-owned.
- Part uniqueness is `(tenant_id, normalized_part_number, manufacturer/manufacturer_code)`.
- Quantity `0` is valid and must remain visible.
- Owner, supplier, tag-info, and traceability are four separate company relationships.
- `location_text` is temporary for migration until Warehouse/location modeling is approved.
- Audit fields are present on persisted business records: `created_at`, `updated_at`, `created_by`, and `updated_by`.

## Indexing

The migration includes tenant-leading indexes for common Company, Contact, Part, Stock, and legacy mapping lookups. Expression uniqueness is implemented with PostgreSQL unique indexes rather than invalid table-level expression constraints.

## Not Deployed

This migration has not been run against a live database or staging database. In this environment there was no local PostgreSQL binary, Docker runtime, `psql`, `DATABASE_URL`, or `TEST_DATABASE_URL`, so database runtime application was not executed here. The migration runner and PostgreSQL integration test are implemented and gated on an isolated development/test database.
