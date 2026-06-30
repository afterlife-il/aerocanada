# SaaS Aviation ERP

This folder is the separate next-generation AeroCanada aviation SaaS ERP. It must not modify or deploy the legacy Yoyamic PHP application.

## Stack Decision

- **Frontend:** Next.js App Router, TypeScript, Tailwind CSS, component-driven ERP design system.
- **Backend API:** TypeScript Express REST API with OpenAPI contract placeholder.
- **Shared types:** TypeScript package consumed by web and API.
- **Data:** adapter layer first. Legacy Yoyamic MySQL remains the first source, with sample adapter data for safe local development.
- **Auth:** provider abstraction for Clerk, Supabase Auth, Firebase, or a custom legacy bridge.
- **Infra:** npm workspaces, Docker/dev compose, CI placeholders, production-readiness docs.

This stack is pragmatic: Next.js gives fast product iteration and server-rendered ERP pages; Express keeps the first API layer transparent and OpenAPI-friendly; shared TypeScript keeps adapters and UI aligned. The architecture remains future PostgreSQL and provider-auth ready.

## Run Locally

```bash
cd SaaS_Aviation
npm install
npm run dev
```

Open `http://localhost:3007`.

API dev server:

```bash
npm run dev:api
```

API health check: `http://localhost:4107/health`.

## Current Vertical Slices

- Login shell with provider-ready actions.
- ERP app shell with sidebar/topbar.
- Dashboard with aviation KPIs and work queues.
- Companies list and Company 360 shell.
- Parts list and Part 360 shell.
- Internal ACI770 stock list and stock detail shell.
- External stock list.
- Next.js API routes backed by adapter sample data.
- Express API routes backed by the same shared sample model.
- Auth abstraction, audit abstraction, legacy adapter structure.

## Non-Goals In This Foundation

- No production deployment.
- No writes to Yoyamic.
- No schema changes.
- No direct auth provider integration yet.
- No AI model integration yet.

## Legacy Rules Preserved

- `RFQ_ID` remains the business workflow key.
- `quote_id` must not replace `RFQ_ID`.
- Owner / Company and Tag Info remain independent.
- Ownership is not inferred.
- Qty `0` remains `0`.
- Legacy ACI770 candidates are not silently backfilled.
