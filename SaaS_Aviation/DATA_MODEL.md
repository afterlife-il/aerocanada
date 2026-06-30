# Data Model

## Legacy Source Tables

- `tb_company`
- company contacts tables
- `tbl_Parts`
- `tbl_Stock`
- `tbl_Stock_external`
- `tbl_RFQ`
- `tbl_RFQ_1`
- `tbl_RFQ_2`
- `tbl_RFQ_3`
- `tbl_PO_Draft`
- `tbl_PO_Customer`
- `Tbl_Customer_PO_Follow_UP`
- `tbl_Status`
- `tbl_Stock_Location`
- `oldacps_*` history and movement tables

## Target SaaS Domains

- Tenant
- User
- Role
- Company
- Contact
- PartNumber
- InternalStock
- ExternalStock
- RFQ
- SupplierQuote
- CustomerQuote
- PurchaseOrder
- SalesOrder
- StockMovement
- RepairOrder
- ExchangeOrder
- LeaseLoan
- Document
- Certificate
- AuditEvent
- Notification

## Invariants

- `RFQ_ID` remains the business workflow key.
- `quote_id` is not a replacement for `RFQ_ID`.
- Owner / Company and Tag Info are independent.
- Qty `0` is valid.
- Legacy candidate ownership is never inferred or backfilled silently.

## Future Migration

The first phase reads legacy data through adapters. Later phases can dual-read, dual-write, then cut over by module after validation.
