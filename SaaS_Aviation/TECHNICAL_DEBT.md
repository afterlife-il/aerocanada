# Technical Debt

## Known Legacy Debt To Avoid Recreating

- Page-level session checks.
- Inline SQL and duplicated query logic.
- Raw database labels in UI.
- Hidden business rules in remarks.
- Non-audited stock status edits.
- Hardcoded credentials.
- Plaintext password fallback.
- Owner / Tag Info conflation.

## New App Guardrails

- Use adapter contracts.
- Use typed DTOs.
- Keep auth and audit as cross-cutting services.
- Keep tenant scope explicit.
- Document placeholders honestly.
