# Architecture

## System Shape

SaaS Aviation is a separate application from legacy Yoyamic.

```text
apps/web       Next.js ERP interface and BFF-style route handlers
apps/api       REST API service, OpenAPI-first, future external clients
packages/shared shared domain types, sample data, adapter contracts
```

## Architectural Principles

1. Legacy Yoyamic is a source system and workflow reference, not a codebase to mutate from this project.
2. All legacy data access goes through adapters.
3. Every future mutation must be auditable.
4. Tenant isolation is a server-side architecture concern, not a UI filter.
5. `RFQ_ID` remains the business workflow key.

## Data Access

The initial adapter uses sample data shaped from Yoyamic tables. The future MySQL adapter will implement the same interface:

- `CompanyRepository`
- `PartRepository`
- `StockRepository`
- `AuditRepository`

Runtime DB clients must be lazily initialized so builds never require secrets.

## API Strategy

REST first:

- stable resource URLs
- OpenAPI contract
- typed DTOs
- future GraphQL only for complex 360 workspaces if needed

## Frontend Strategy

The web app uses 360 workspaces:

- Company 360
- Part 360
- Stock 360

Each page should expose related facts, timelines, documents, actions, and workflow status without excessive navigation.

## Future Deployment

Initial deployment target can be a container platform or Vercel + containerized API. Kubernetes/ECS decision is documented separately in `docs/INFRA_DECISIONS.md`.
