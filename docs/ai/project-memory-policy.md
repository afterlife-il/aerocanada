# Project Memory Policy

Last updated: 2026-06-30

## Permanent Files

`APP_RECAP.md` is the executive dashboard. It must stay short enough to read in about five minutes.

`PROJECT_STATE.json` is the machine-readable AI memory. It must be updated after implementation work.

## Detail Placement

Detailed architecture, business, security, deployment, sprint, testing, migration, and AI notes belong under `docs/`.

Do not duplicate large report content in `APP_RECAP.md`.

## Update Rule

Every implementation should update:

- `APP_RECAP.md`
- `PROJECT_STATE.json`
- the relevant `docs/` file
- `IMPLEMENTATION_REPORT.md`

## Product Boundary

Yoyamic is the legacy staging and migration-reference system. `SaaS_Aviation/` is the future product. New product work should go into `SaaS_Aviation/` unless the task explicitly requests a Yoyamic fix.
