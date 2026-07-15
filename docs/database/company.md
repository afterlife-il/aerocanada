# Company 360 Database

Status: implemented, locally validated on 2026-07-14, and deployed to dedicated staging PostgreSQL on 2026-07-15.

Migration `002_company_360_production.sql` adds ICAO, IATA, VAT, and tags to `companies`; tenant-scoped `company_addresses`; and the Company activity timeline. Existing `companies`, `company_roles`, `contacts`, and `stock_items` remain authoritative. Activity is not an immutable audit ledger because safe Company deletion cascades its rows.

All reads and writes require `tenant_id`. Address and activity foreign keys use `(tenant_id, company_id)` and cascade only when a Company deletion is allowed. A partial unique index permits one primary address per tenant/company. Company deletion is blocked by PostgreSQL when stock still references the Company; Contacts, roles, addresses, and activity are removed transactionally when deletion is otherwise safe.

Inventory association preserves owner, supplier, tag-info, and traceability as independent relationships. Quantity zero remains valid. No Yoyamic table, legacy PHP schema, live database, or production database was touched.

Validation: migrations 001/002 applied locally with checksums; PostgreSQL integration passed with reconnect persistence, full Contact CRUD, address persistence, activity persistence, Company delete behavior, stock linkage, and cross-tenant denial.
