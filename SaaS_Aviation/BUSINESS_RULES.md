# Business Rules

## RFQ

`RFQ_ID` is the canonical business workflow key.

## Ownership

Owner / Company is explicit ownership. Tag Info is certification/tagging information. Supplier, status, location, and remarks do not imply ownership.

## Quantity

Qty `0` means zero. Do not render or migrate it as one.

## Lifecycle

Stock movement must be explicit and auditable. Sold, repair, exchange, lease, loan, reserved, quarantine, and owner transfer require movement events with actor, timestamp, source document, before/after state, and reason.

## Aviation Traceability

Certificates, release status, FAA/EASA/8130, traceability, shelf life, repair history, and core/exchange data are first-class ERP concepts.
