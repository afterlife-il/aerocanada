# Tenant Dashboard Business Workflows

Last updated: 2026-07-01

## Tenant

The dashboard is scoped to tenant `ACI770` / `AEROCANADA INDUSTRIES 770 INC.` through `RequestContext`. Dashboard records carry `tenantId` and are filtered before aggregation.

## Workflow Keys

`RFQ_ID` remains the business workflow key for RFQ-linked customer quotes, supplier quotes, purchase orders, and sales orders. `quote_id` is not introduced for workflow relationships.

## Dashboard Service

Shared dashboard composition lives in `SaaS_Aviation/packages/shared/src/dashboard-service.ts`. It accepts a `RequestContext` and a dashboard source adapter, filters all source collections by tenant, and derives the cockpit read model.

The sample API adapter implements `getDashboard(context)` in `SaaS_Aviation/apps/api/src/adapters/sample-data-source.ts`. The web static app uses `SaaS_Aviation/apps/web/src/lib/dashboard.ts` for the same tenant-scoped read model.

## Covered Business Areas

- Open RFQ pressure.
- Pending customer quote value and gross margin.
- Supplier quote follow-up load.
- Purchase and sales order exposure.
- Internal and external stock value.
- Company inventory with watch counts.
- Repairs, exchanges, and leases where records exist.
- Document follow-up.
- Accounting holds and supplier payment alerts.
- Recent tenant activity.

## Gaps Before Real Operations

- Replace sample fixtures with read-only legacy adapter queries.
- Persist sessions/audit logs before mutations.
- Add API route/OpenAPI schema for the dashboard read if the web app stops using static fixtures.
- Validate monetary fields and margin formulas against Yoyamic source tables.
