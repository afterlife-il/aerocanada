# Aviation ERP Benchmark & Feature Gap Analysis

Last updated: 2026-07-02
Role: Aviation Business Architect review. Functional/business analysis only. No implementation, no code.

## 1. Reference Systems and Market Segments

These nine systems do not compete in one category — comparing SaaS_Aviation against all of them at once hides
which gaps actually matter for AeroCanada. They split into three segments:

| Segment | Systems | What they optimize for |
|---|---|---|
| **Parts trading / distribution / brokerage** | Quantum Control, AvSight | RFQ→quote→PO→SO velocity, trace/certificate integrity, consignment and exchange economics, marketplace connectivity |
| **MRO / CAMO execution** | Ramco Aviation, Rusada ENVISION, AMOS, OASES, Traxxall | Work order execution, technical records, engineering compliance (AD/SB, LLP), quality/safety management, materials for a shop floor |
| **Enterprise ERP backbone** | SAP (S/4HANA + aviation add-ons), IFS Cloud Aerospace & Defense | Finance/GL, complex multi-entity consolidation, procurement, project/manufacturing, broad workflow governance |

AeroCanada's own `VISION.md` already names its target: *"combine the operational seriousness of Pentagon2000 and
Quantum Control"* with Salesforce/Odoo-grade usability. That means **Quantum Control and AvSight are the primary
bar** — AeroCanada is a parts trader/distributor with repair, exchange, and lease services, not an airline
Maintenance & Engineering shop. Ramco/Rusada/AMOS/OASES/Traxxall set the bar for capability AeroCanada should
approach *only where it touches MRO-adjacent services* (repair/exchange execution) or sells into airline/MRO
customers who expect that rigor from a supplier. SAP/IFS set the bar for finance and governance maturity as the
company scales into a real multi-entity SaaS platform.

## 2. Method

For each **implemented** SaaS_Aviation module, this review answers six questions against the reference systems:

1. What is missing (functional gap)?
2. What do aviation ERP professionals expect as table stakes?
3. What does AeroCanada specifically need (broker/distributor + repair/exchange/lease business)?
4. What would an MRO need if this module served shop-floor/CAMO customers?
5. What would a parts broker/distributor need (AeroCanada's direct peer group)?
6. What would an airline/operator need if they were a direct tenant or a demanding customer?

Implemented modules reviewed: Auth/Tenant Foundation, Tenant Dashboard, Company 360, Part 360, Stock 360,
Company Inventory. (Documents has an architecture proposal — `DOCUMENTS_ARCHITECTURE.md` — but no
implementation yet, so it is out of scope for this review and will get its own benchmark pass once built.)

---

## 3. Auth / Tenant Foundation

**Today:** email/password, tenant context, 5 roles, permission list, bearer sessions, in-memory storage, no MFA,
no OAuth wired in yet.

**What's missing**
- SSO/SAML/OIDC federation with corporate identity providers.
- Approval / delegation-of-authority workflows (e.g. PO over $X needs manager sign-off) — standard in every
  reference system above the smallest tier.
- Segregation-of-duties (SOD) controls — the person who creates a PO should not also be able to approve it.
- Field-level permission granularity (e.g. hide cost/margin from a role that can still see sell price).
- Multi-entity organizational hierarchy (holding company → subsidiaries → stations) — today's model is one
  tenant = one company.
- License/certification data tied to the user identity itself.
- Export-control clearance flags on the user record.
- Persistent, immutable audit logging (currently abstraction-only per `SECURITY.md`).

**What professionals expect:** enterprise SSO, granular field-level permissions, approval chains, audit-grade
logs that survive a compliance audit.

**What AeroCanada needs:** export-control clearance flags on users handling ITAR/EAR-sensitive parts; approval
thresholds on quotes/POs by dollar value; multi-entity support if AeroCanada operates more than one legal entity
or location.

**What MROs need:** license/certification linked to identity, since a mechanic's authorization to sign off work
is a regulatory requirement (this is core to AMOS, Ramco, OASES, Rusada user models).

**What brokers need:** SOD on high-value transactions, credit-limit-aware approval routing, rep-scoped data
visibility (a sales rep sees only their accounts unless elevated).

**What airlines need:** federation with the airline's own IAM, station-scoped access, SMS accountable-manager
role tracking.

**Feature recommendations**
1. SSO/OIDC provider integration (Google/Microsoft first, per existing `SECURITY.md` roadmap).
2. MFA/TOTP (already planned; treat as launch-blocking, not optional).
3. Approval-threshold workflow engine, reusable across quotes/POs/SOs.
4. Field-level permission masking (cost/margin visibility as the first use case).
5. Multi-entity tenant hierarchy model.
6. User-level export-control clearance attribute.
7. Persistent audit log with retention policy and legal-hold override (reuse the pattern already designed for
   documents in `DOCUMENTS_ARCHITECTURE.md` §18).

---

## 4. Tenant Dashboard

**Today:** one fixed ACI770 cockpit view — metrics, margin KPIs, RFQs, quotes, supplier quotes, POs, sales
orders, stock value, company inventory, service workflows, documents-pending, accounting alerts, recent
activity, quick actions. Sample-data driven, one layout for everyone.

**What's missing**
- Role-based dashboards (sales sees pipeline/quotes; purchasing sees open POs/supplier performance; finance
  sees AR/AP aging; ops sees WIP). Every reference system ships persona-specific home screens.
- Drill-down from a KPI tile straight into the filtered record list, not just a static number.
- Trend/period-over-period analytics (this week vs. last week, this quarter vs. last).
- Predictive/AI-driven insight surfacing (Ramco's demand-forecast tiles are the industry benchmark here).
- A compliance-specific view: certificate-expiry heatmap, AD/SB due list — currently only a generic
  "documents pending" list.
- Configurable/personalizable widget layout and saved views.
- Scheduled report delivery (email/PDF digest) and mobile-friendly summary.

**What professionals expect:** an exceptions-first view — "what needs my attention today," not a wall of totals.

**What AeroCanada needs:** broker-specific KPIs — margin by rep/customer, aging RFQs, quote win rate, days-to-
quote, AOG response time.

**What MROs need:** shop-floor status (WIP by work order), turnaround time (TAT), technician utilization vs.
backlog.

**What brokers need:** sales funnel/pipeline view, top customers/suppliers, credit exposure, commission
tracking.

**What airlines need:** fleet dispatch reliability, AOG dashboard, MEL/CDL status, maintenance due-list by tail
number.

**Feature recommendations**
1. Role-based dashboard variants (start with Sales, Purchasing, Operations, Finance).
2. Click-through from every metric tile to its underlying filtered list.
3. Trend deltas (week-over-week, quarter-over-quarter) on every KPI card.
4. Certificate/compliance-expiry heatmap as its own dashboard panel, generalizing `DocumentAlert`.
5. Saved/personalized views per user.
6. Scheduled digest export (daily/weekly email or PDF).

---

## 5. Company 360

**Today:** contacts, stock, RFQs, quotes, POs, documents, users, activity — read-only tabbed shell.

**What's missing**
- Credit management: credit limit, terms, aging balance, credit-hold enforcement against sales order release.
- Denied-party / export-control screening (OFAC, entity list) before any transaction — a hard regulatory
  requirement in aviation parts trading, present in Quantum Control and every serious brokerage tool.
- Vendor/customer certification tracking (AS9100, AS9120, NADCAP, FAA repair station certificate, EASA
  approval) with expiry alerts — you cannot legally buy from a supplier whose accreditation lapsed.
- Vendor performance scorecarding (on-time delivery, quote responsiveness, quality reject rate).
- Contract & pricing agreement management (negotiated terms, volume pricing, SLA commitments).
- Opportunity/pipeline tracking — a broker sales funnel, not just a flat quote list.
- Related-company hierarchy (parent/subsidiary/DBA).
- Communication/activity timeline (calls, emails, meetings) beyond system-generated audit events.
- Self-service portal provisioning for external customers/suppliers.

**What professionals expect:** a 360 that shows financial exposure and relationship health, not just
transactional record lists.

**What AeroCanada needs:** denied-party screening as a hard gate before transacting; credit-hold enforcement;
supplier certification expiry alerts feeding directly into purchasing eligibility.

**What MROs need:** customer-to-fleet association (which tails/aircraft does this customer operate) driving
service scheduling; warranty entitlement per customer.

**What brokers need:** win/loss tracking on quotes per company, rep/commission assignment, competitor
intelligence notes.

**What airlines need:** visible SLA/contract terms (response-time commitments), fleet data linkage, AOG
escalation contact path.

**Feature recommendations**
1. Credit management panel (limit, terms, aging, hold flag) wired to Sales Order release logic.
2. Denied-party/export screening as a transaction gate, not just a company flag.
3. Certification/accreditation tracking with expiry alerts, mirroring the certificate lifecycle already designed
   for documents.
4. Vendor scorecard (on-time %, quote responsiveness, reject rate).
5. Contract/pricing agreement records linked to Company.
6. Opportunity/pipeline view distinct from the flat RFQ/quote lists.
7. Related-company hierarchy (parent/subsidiary).

---

## 6. Part 360

**Today:** identity, alternates, aircraft/ATA/IPC metadata, ACI stock, external stock, RFQs, quotes, orders,
service history, documents, traceability, margin.

**What's missing**
- Life-limited part (LLP) cycle/hour tracking — mandatory for rotable components in every MRO-grade system
  (AMOS, Ramco, OASES, Rusada). Currently absent entirely.
- Shelf-life/cure-date tracking for consumables (seals, sealants, batteries).
- Hazmat/dangerous-goods classification.
- Export classification (ECCN/USML) at the part level, not just the company level.
- Obsolescence/DMSMS (diminishing manufacturing sources) flags.
- Engineering drawing/specification attachment.
- Repair capability matrix — which shops are approved/capable of repairing this specific PN.
- Market price intelligence/benchmarking (comparison against ILS/PartsBase-style market data).
- AI-driven demand forecasting (a signature Ramco capability).
- Core/exchange value tracking, essential for exchange-program economics.
- True interchangeability chains — today's `alternates`/`supersededBy` are flat fields, not a resolvable chain
  with directionality and approval basis.

**What professionals expect:** a part record that answers "can I sell or install this today," combining
identity, regulatory status, and life-limit status in one place — not just identity plus stock quantity.

**What AeroCanada needs:** shelf-life alerts for consumables; export classification to avoid compliance
violations on quotes; price benchmarking to stay competitive.

**What MROs need:** LLP tracking (non-negotiable for rotables), repair capability matrix, attached engineering
data.

**What brokers need:** market price trend, core value tracking for exchange deals, alternate-part chains for
AOG substitution.

**What airlines need:** fleet-serial-specific applicability (not just aircraft family), maintenance-program task
linkage, warranty status per installed unit.

**Feature recommendations**
1. LLP tracking fields (cycles/hours remaining, life limit source) on rotable part records.
2. Shelf-life/cure-date tracking on consumable part records.
3. Export classification (ECCN/USML) as a first-class Part field, feeding the Company 360 export-screening gate.
4. Repair capability matrix (approved shop list per PN).
5. Core/exchange value tracking.
6. Directional interchangeability/supersession chain, not a flat array.
7. Market price benchmarking panel (even manually curated before any live feed integration).

---

## 7. Stock 360

**Today:** owner/company, supplier, tag info, traceability, documents, lifecycle, RFQ/quote/order links,
reservation/movement/upload as non-persistent boundary panels.

**What's missing**
- Bin/location hierarchy — today's `location` is a flat string, not a warehouse/zone/bin structure.
- Lot/batch tracking and cycle counting.
- Incoming inspection/receiving workflow gated on certificate verification before stock becomes "available."
- Condition-coded pricing tiers (NE/NS/OH/SV/AR/RP) — a Quantum Control/parts-trading standard.
- Reserved vs. allocated vs. committed distinction — today's model has a single `status`, which risks overselling
  during concurrent quoting.
- Pick/pack/ship warehouse workflow with barcode/RFID scanning.
- Landed cost calculation (freight, duty, brokerage fees rolled into true unit cost).
- Consignment-vs-owned GL separation — currently ownership is tracked, but not the accounting treatment
  difference that consignment implies.
- Insurance/valuation tracking.
- Multi-warehouse/multi-bin transfer workflow.

**What professionals expect:** warehouse-grade stock control where a physical count reconciles to the system
count with a full audit trail — not just a status field on a stock row.

**What AeroCanada needs:** consignment-vs-owned separation (material for a broker holding supplier consignment
inventory) and landed cost visibility for accurate margin.

**What MROs need:** incoming-inspection/quarantine workflow tied to certificate verification, and core-return
tracking for exchange programs.

**What brokers need:** reserved/allocated/committed distinction to prevent overselling across simultaneous
quotes, and barcode scanning for fast pick during shipment.

**What airlines need:** bin-level location for AOG retrieval speed, and mobile stores integration matching
AMOS/OASES-style line-maintenance apps.

**Feature recommendations**
1. Bin/location hierarchy replacing the flat location string.
2. Reserved/allocated/committed as distinct, auditable states (this is the highest-leverage fix — it directly
   protects revenue integrity during concurrent quoting).
3. Condition-coded pricing tiers.
4. Landed cost fields on stock receipt.
5. Incoming inspection workflow gated on certificate presence/validity (ties directly to
   `DOCUMENTS_ARCHITECTURE.md`'s certificate-to-sale gate concept).
6. Consignment GL-treatment flag distinct from ownership.
7. Lot/batch and cycle-count support.

---

## 8. Company Inventory (Read Model)

**Today:** unique stock totals, company relationship rows, stock documents, linked RFQs — an aggregation view.

**What's missing**
- Valuation method (FIFO/LIFO/weighted-average) applied to the aggregation.
- Turnover/aging analysis per company relationship (how long has this consigned stock been sitting).
- Min/max stocking levels and automated replenishment triggers.
- Contractual stocking agreement terms (e.g. power-by-the-hour pools) tied to the aggregation.
- Exclusivity / right-of-first-refusal flags on consigned lines.

**What professionals expect:** not just "what do we have with this company" but "is this stocking relationship
healthy and profitable."

**What AeroCanada needs:** aging analysis to flag slow-moving consignment stock for return or renegotiation.

**What MROs need:** power-by-the-hour pool tracking if servicing airline maintenance contracts.

**What brokers need:** turnover rate to prioritize which consigned lines to actively market.

**What airlines need:** min/max stocking-level enforcement against contracted service levels.

**Feature recommendations**
1. Aging/turnover metric per company relationship.
2. Valuation method as an explicit, auditable field.
3. Min/max replenishment trigger support.
4. Contractual stocking-agreement record type, linked to Company and to the inventory aggregation.

---

## 9. Whole-Module Gaps — Nothing Built Yet

These are not gaps *within* an implemented module — they are entire categories every reference system treats as
core, and SaaS_Aviation has not started. Listed in rough priority order for AeroCanada's actual business:

| Missing module | Why it matters | Reference bar |
|---|---|---|
| **RFQ → Quote → PO → SO transaction engine** | This is the commercial heartbeat of a parts trading business — everything else in the app currently just *displays* sample data referencing it. Nothing persists a quote or an order today. | Quantum Control, AvSight |
| **Repair / Exchange / Lease / Loan execution** | Named explicitly in `VISION.md` as core to the product; currently sample-only. | Quantum Control, Ramco (repair order side) |
| **Quality & Compliance (QMS)** | AS9100/AS9120 nonconformance/CAPA, incoming inspection, denied-party screening automation, 8130-3/Form 1 generation-and-verification workflow. | Quantum Control, AMOS, Rusada, OASES |
| **Finance & Accounting integration** | AR/AP, GL, multi-currency revaluation, commission calculation — today only an "accounting alerts" stub exists. | SAP, IFS |
| **Warehouse & Logistics (WMS)** | Bin/pick/pack, cycle count, freight/customs/DG shipping, 3PL integration. | Quantum Control, IFS |
| **Maintenance execution (work orders/task cards)** | Needed only if AeroCanada executes repair work itself rather than brokering it out — but if so, this is non-negotiable. | AMOS, Ramco, OASES, Rusada |
| **Engineering & Airworthiness (AD/SB, LLP)** | Needed to sell into airline/MRO customers credibly; ties directly to the LLP gap already flagged on Part 360. | AMOS, Ramco, OASES |
| **CRM / Sales pipeline & e-commerce portal** | Opportunity management, quote-to-close funnel, customer self-service ordering. | AvSight |
| **Reliability & analytics** | Fleet reliability, repeat-defect tracking — airline/MRO-facing, not broker-facing. | AMOS, Ramco, OASES |
| **Customer/Supplier self-service portal** | Order status, document download, RFQ submission without a phone call or email. | AvSight, Quantum Control |
| **Marketplace / EDI integration** | ILS, PartsBase, Locatory, SPEC2000 messaging — where parts brokers actually source and sell in the real market. | Quantum Control |
| **Mobile applications** | Warehouse scanning, technician mobile, sales mobile. | Ramco, AMOS, Rusada (all mobile-first now) |
| **Contract & SLA management** | Response-time commitments, negotiated pricing, warranty terms — flagged already at the Company 360 level. | IFS, SAP |
| **CAMO (Continuing Airworthiness Management)** | Only relevant if AeroCanada serves operators directly rather than just trading parts. | AMOS, Rusada, Traxxall |
| **Reporting / BI** | Scheduled regulatory reporting packs, ad hoc report builder. | SAP, IFS |

---

## 10. Prioritized Recommendation Tiers

Ordered by fit to AeroCanada's actual business model (broker/distributor with repair-exchange-lease services),
not by generic "enterprise completeness."

**Tier 1 — Broker/Distributor Core (Quantum Control / AvSight parity).** This is the product. Without it,
everything shipped so far is a very well-designed read-only shell around a business that has no transaction
engine yet.
- RFQ → Quote → PO → SO workflow engine, with `RFQ_ID` as the persistent workflow key (already the stated
  business rule).
- Certificate/trace document generation and verification (builds directly on `DOCUMENTS_ARCHITECTURE.md`).
- Reserved/allocated/committed stock states.
- Consignment vs. owned separation with correct GL treatment.
- Condition-coded pricing and landed cost.
- Core/exchange value tracking.
- Denied-party/export-control screening as a transaction gate.

**Tier 2 — Compliance & Quality Baseline.** Every serious aviation company needs this regardless of whether it
executes MRO work itself.
- QMS module (nonconformance/CAPA, incoming inspection).
- Export classification at part and company level.
- Vendor/customer certification (AS9100/AS9120/repair-station) expiry tracking.
- Credit management and hold enforcement.

**Tier 3 — MRO-Adjacent (only if AeroCanada executes repair/exchange work itself, not just brokers it out).**
- Work order / task tracking.
- Technician certification tracking.
- LLP tracking.
- Tooling/calibration tracking.

**Tier 4 — Airline-Grade (only if targeting airline/operator customers directly, e.g. a CAMO-style service).**
- Fleet-serial part applicability.
- AD/SB compliance tracking.
- Reliability program/analytics.
- MEL/dispatch tooling.

**Tier 5 — Platform Maturity (expected by every professional buyer regardless of segment).**
- SSO/MFA, role-based dashboards, mobile access, scheduled BI/reporting, marketplace/EDI connectivity.

---

## 11. Summary Verdict

SaaS_Aviation's architectural discipline — tenant isolation, typed read models, adapter-first legacy migration,
audited boundary panels instead of fake mutations — is genuinely strong and ahead of where most greenfield
aviation ERP rewrites are at this stage. That is real, defensible engineering maturity.

Functionally, though, it is honest to say the product is still **pre-MVP relative to any of the nine reference
systems**, including its own stated benchmark (Quantum Control). What exists today is 360-degree *visibility*
scaffolding — Company, Part, Stock, Company Inventory — built on sample data, with no persisted transaction
(no RFQ, quote, PO, or SO can actually be created, only displayed). None of the nine reference systems would be
considered viable without a working commercial transaction engine, and that is the single most valuable gap to
close next, ahead of any additional read-model polish.

The recommended path is Tier 1 first, in full, before broadening into Tier 2+. A best-in-class Part 360 view
over a business that cannot yet issue a real quote is not yet a world-class aviation ERP — it is the correct
foundation for one.
