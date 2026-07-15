# OpenAPI Contracts

Last updated: 2026-07-15

## Scope

The first API contract pass covers the existing Express API read routes in `SaaS_Aviation/apps/api/src/server.ts`.

## Contract Source

`SaaS_Aviation/apps/api/src/openapi/openapi.ts`

## Covered Routes

- `GET /health`
- `POST /v1/auth/login`
- `POST /v1/auth/logout`
- `POST /v1/auth/revoke-all`
- `POST /v1/auth/mfa/challenge`
- `POST /v1/auth/mfa/totp/enroll`
- `POST /v1/auth/mfa/totp/confirm`
- `POST /v1/auth/mfa/totp/disable`
- `POST /v1/auth/phone/enroll/request`
- `POST /v1/auth/phone/enroll/verify`
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

Company, Contact, Address, Part and Stock mutations are covered by the live contract. Cookie-authenticated state changes require the matching CSRF header. Password login may return HTTP 202 with a short-lived MFA challenge instead of a session. Phone OTP request responses never contain the code.

`RFQ_ID` remains the business workflow key for future workflow routes.

Business routes accept controlled bearer clients or secure same-origin cookies. Repository methods must receive tenant context before returning or changing business data.
