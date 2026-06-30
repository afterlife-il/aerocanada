# OpenAPI Contracts

Last updated: 2026-06-30

## Scope

The first API contract pass covers the existing Express API read routes in `SaaS_Aviation/apps/api/src/server.ts`.

## Contract Source

`SaaS_Aviation/apps/api/src/openapi/openapi.ts`

## Covered Routes

- `GET /health`
- `POST /v1/auth/login`
- `POST /v1/auth/logout`
- `GET /v1/session`
- `GET /v1/companies`
- `GET /v1/parts`
- `GET /v1/stock/internal`
- `GET /v1/stock/external`
- `GET /v1/audit`

## Component Schemas

- `HealthResponse`
- `SessionUser`
- `SessionResponse`
- `LoginRequest`
- `AuthSession`
- `AuthSessionResponse`
- `Tenant`
- `Company`
- `PartNumber`
- `StockItem`
- `AuditEvent`
- list response wrappers

## Rules

Mutation routes are intentionally absent until RBAC, tenant isolation, validation, audit persistence, and rollback behavior are designed.

`RFQ_ID` remains the business workflow key for future workflow routes.

Business read routes require bearer sessions. Repository methods must receive tenant context before returning business data.
