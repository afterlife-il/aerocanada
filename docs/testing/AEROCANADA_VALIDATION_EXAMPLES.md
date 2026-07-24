# AeroCanada Validation Examples

Last updated: 2026-07-24

AEROCANADA INDUSTRIES 770 INC. (`aci770`, slug `AeroCanada`) is the first validation tenant, not the Ready2Go
Aviation platform identity. Examples in `SaaS_Aviation/module-status.json` may contain only tenant code, entity
type, source, appropriate legacy identifier, SaaS identifier, safe label, scenario, expected and actual result,
date, validator, and an internal route.

Email addresses, phone numbers, credentials, personal notes, document contents, and other sensitive data must not
be committed. The dashboard applies defensive email and phone masking to safe labels. Fixture records and sample
read models are never validation evidence. Imported Yoyamic records require an approved read-only provenance; a
manual record must be labeled staging.

Company, Contact, Part, and Stock example matrices remain incomplete in this reconciliation because approved safe
labels and scenarios were not verified on 2026-07-24. Those missing examples are blockers, not fabricated rows.
The recorded tenant, authentication, migration sample, and dashboard examples contain no personal contact data.
