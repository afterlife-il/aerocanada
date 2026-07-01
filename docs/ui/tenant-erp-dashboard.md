# Tenant ERP Dashboard UI

Last updated: 2026-07-01

## Scope

The first SaaS Aviation dashboard is a dense tenant cockpit for `AEROCANADA INDUSTRIES 770 INC.` (`ACI770`). It replaces the previous small dashboard shell with an operational ERP surface.

## Visible Surfaces

- Tenant context in the page header.
- Metric strip for open RFQs, pending customer quotes, supplier quote follow-ups, purchase orders, sales orders, stock value, pending documents, and accounting alerts.
- RFQ and quote work queues with `RFQ_ID` visible.
- Supplier quote queue.
- Company inventory summary by owner/supplier company.
- Purchase order and sales order tables.
- Repairs, exchanges, and leases panel when service workflow data exists.
- Documents pending panel.
- Accounting alerts panel.
- Recent tenant activity feed.
- Quick actions for the most common cockpit moves.

## Design Notes

The dashboard uses the existing SaaS shell and restrained OKLCH theme. The layout favors compact tables, panels, and action density over decorative cards. Cards are used only as framed operational panels or metric cells.

## Current Limitations

The UI is backed by tenant-scoped sample fixtures until the read-only Yoyamic adapter maps real workflow tables. The static web export remains presentation only; the Express API and future server runtime remain the security boundary.
