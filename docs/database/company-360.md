# Company 360 Data Model Notes

Last updated: 2026-07-14

Company 360 now uses the dedicated local PostgreSQL schema described in `docs/database/company.md`. Migration 001 owns tenants, companies, roles, contacts, and stock relationships. Migration 002 adds ICAO/IATA/VAT/tags, multiple tenant-scoped addresses, and persisted Company activity.

The public static frontend remains fixture-backed. Local `persistent-api` mode uses PostgreSQL Company records and never falls back to fixtures. Its Company aggregate intentionally returns no fixture Documents; Documents remain a separate future persistence boundary.

Core rules:

- Every Company, Contact, Address, Activity, and Stock relationship is tenant-scoped.
- Company roles are many-to-many values, not one exclusive display type.
- One primary address is allowed per tenant/company.
- Owner, supplier, tag-info, and traceability Company relationships remain independent.
- Quantity zero remains valid and visible.
- Company deletion is rejected while stock references exist.
- RFQ/quote/order identifiers remain workflow boundaries until those modules are implemented.

No Yoyamic query, legacy PHP change, live database access, deployment, or production migration occurred.

Migration 007 adds `company_notes`, keyed and constrained by tenant/company composite ownership. Notes carry author/update audit fields, a pin flag, a 1-5,000 character database constraint, cascade with their Company, and sort pinned-first then most-recently-updated. It was applied locally on 2026-07-28 through the approved runner with checksum `25a4c322f4247e9f58d6fdb74808eae191197bdb5938557823773146b5f73aee`; re-apply preserved the original ledger timestamp and created no duplicate entry.
