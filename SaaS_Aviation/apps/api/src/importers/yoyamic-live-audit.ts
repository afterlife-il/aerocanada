import { createHash } from "node:crypto";
import { writeFile } from "node:fs/promises";
import mysql, { type Connection, type RowDataPacket } from "mysql2/promise";
import { normalizePartNumber } from "@saas-aviation/shared";
import { assertYoyamicSelectOnly } from "./yoyamic-readonly-source.js";

export interface LegacyCompanyRow extends RowDataPacket {
  id: number;
  name: string;
  deletedFlag: string;
  status: string;
  website: string;
  cageCode: string;
  lastActivity: Date | null;
}

export interface LegacyCompanyDetailRow extends RowDataPacket {
  id: number;
  companyId: number;
  companyTypeId: number;
  country: string;
  city: string;
  state: string;
  street: string;
  postalCode: string;
  fax: string;
  phone: string;
  email: string;
  score: string;
  notes: string;
  vatNumber: string;
  firstContact: string;
  addressType: number;
  timezone: string;
  label: string;
}

export interface LegacyContactRow extends RowDataPacket {
  id: number;
  companyId: number;
  name: string;
  phone: string;
  phone2: string;
  fax: string;
  mobile: string;
  divisionId: number;
  email: string;
  title: string;
  notes: string;
  status: string;
  entryDate: string;
  modifiedDate: Date | null;
}

export interface LegacyPartRow extends RowDataPacket {
  id: number;
  partNumber: string | null;
  description: string | null;
  manufacturerId: number | null;
  aircraftId: number | null;
  notes: string;
  status: string;
  alternatesText: string;
  addedDate: string;
  ata: number;
  cageCode: string;
}

interface IdNameRow extends RowDataPacket { id: number; name: string }

export interface YoyamicLegacySnapshot {
  companies: LegacyCompanyRow[];
  companyDetails: LegacyCompanyDetailRow[];
  contacts: LegacyContactRow[];
  parts: LegacyPartRow[];
  companyTypes: IdNameRow[];
  aircraft: IdNameRow[];
}

export interface AuditFinding {
  entity: "company" | "company-address" | "contact" | "part";
  code: string;
  severity: "warning" | "manual-review" | "rejected";
  count: number;
}

export interface YoyamicAuditReport {
  mode: "dry-run";
  sourceSystem: "yoyamic";
  databaseName: string;
  tenantCode: string;
  generatedAt: string;
  importerVersion: string;
  sourceReadOnly: true;
  sourceCounts: Record<string, number>;
  candidateCounts: Record<string, number>;
  findings: AuditFinding[];
  warningCount: number;
  manualReviewCount: number;
  rejectionCount: number;
  probableDuplicateCount: number;
  orphanCount: number;
  unmappedFieldCount: number;
  fullImportGate: "blocked" | "pass";
  blockingCodes: string[];
  estimatedPostgresGrowthBytes: number;
}

export const YOYAMIC_IMPORTER_VERSION = "1.0.0";

function comparisonText(value: unknown): string {
  return String(value ?? "").trim().replace(/\s+/g, " ").toLocaleLowerCase("en");
}

export function normalizeCompanyComparisonName(value: unknown): string {
  return comparisonText(value).replace(/\b(incorporated|inc\.?|limited|ltd\.?|corporation|corp\.?|llc)\b/g, "").replace(/[^a-z0-9]+/g, "");
}

export function normalizeEmailForComparison(value: unknown): string | null {
  const normalized = comparisonText(value);
  return normalized || null;
}

export function validEmail(value: unknown): boolean {
  const normalized = comparisonText(value);
  return !normalized || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized);
}

export function splitLegacyContactName(value: unknown): { firstName: string; lastName: string; incomplete: boolean } {
  const normalized = String(value ?? "").trim().replace(/\s+/g, " ");
  if (!normalized) return { firstName: "Legacy", lastName: "Contact", incomplete: true };
  const pieces = normalized.split(" ");
  if (pieces.length === 1) return { firstName: pieces[0]!, lastName: "", incomplete: true };
  return { firstName: pieces[0]!, lastName: pieces.slice(1).join(" "), incomplete: false };
}

export function parseLegacyAlternates(value: unknown): string[] {
  return [...new Set(String(value ?? "").split(/[;,|\r\n]+/).map((item) => item.trim()).filter(Boolean))];
}

export function sourceChecksum(value: unknown): string {
  return createHash("sha256").update(JSON.stringify(value)).digest("hex");
}

function duplicateGroupCount(values: string[]): number {
  const counts = new Map<string, number>();
  for (const value of values.filter(Boolean)) counts.set(value, (counts.get(value) ?? 0) + 1);
  return [...counts.values()].filter((count) => count > 1).length;
}

function addFinding(findings: AuditFinding[], entity: AuditFinding["entity"], code: string, severity: AuditFinding["severity"], count: number): void {
  if (count > 0) findings.push({ entity, code, severity, count });
}

export function auditYoyamicSnapshot(snapshot: YoyamicLegacySnapshot, databaseName: string, tenantCode = "aci770"): YoyamicAuditReport {
  const findings: AuditFinding[] = [];
  const companyIds = new Set(snapshot.companies.map((row) => row.id));
  const companyTypeIds = new Set(snapshot.companyTypes.map((row) => row.id));
  const aircraftIds = new Set(snapshot.aircraft.map((row) => row.id));
  const normalizedCompanyNames = snapshot.companies.map((row) => normalizeCompanyComparisonName(row.name)).filter(Boolean);
  const partKeys = snapshot.parts
    .filter((row) => Boolean(row.partNumber?.trim()))
    .map((row) => `${normalizePartNumber(row.partNumber!)}:${row.manufacturerId ?? 0}`);

  const companyNameDuplicates = duplicateGroupCount(normalizedCompanyNames);
  const contactEmailDuplicates = duplicateGroupCount(snapshot.contacts.map((row) => `${row.companyId}:${normalizeEmailForComparison(row.email) ?? ""}`).filter((key) => !key.endsWith(":")));
  const partDuplicates = duplicateGroupCount(partKeys);
  const orphanDetails = snapshot.companyDetails.filter((row) => !companyIds.has(row.companyId)).length;
  const orphanContacts = snapshot.contacts.filter((row) => !companyIds.has(row.companyId)).length;
  const missingContactNames = snapshot.contacts.filter((row) => !String(row.name ?? "").trim()).length;
  const invalidContactEmails = snapshot.contacts.filter((row) => !validEmail(row.email)).length;
  const missingPartNumbers = snapshot.parts.filter((row) => !row.partNumber?.trim()).length;
  const blankPartDescriptions = snapshot.parts.filter((row) => !row.description?.trim()).length;
  const orphanManufacturers = snapshot.parts.filter((row) => Boolean(row.manufacturerId) && !companyIds.has(row.manufacturerId!)).length;
  const orphanAircraft = snapshot.parts.filter((row) => Boolean(row.aircraftId) && !aircraftIds.has(row.aircraftId!)).length;
  const unknownCompanyTypes = snapshot.companyDetails.filter((row) => Boolean(row.companyTypeId) && !companyTypeIds.has(row.companyTypeId)).length;

  addFinding(findings, "company", "missing-company-name", "rejected", snapshot.companies.filter((row) => !String(row.name ?? "").trim()).length);
  addFinding(findings, "company", "duplicate-normalized-company-name", "manual-review", companyNameDuplicates);
  addFinding(findings, "company-address", "orphan-company-detail", "rejected", orphanDetails);
  addFinding(findings, "company-address", "unknown-company-type", "warning", unknownCompanyTypes);
  addFinding(findings, "contact", "orphan-contact", "rejected", orphanContacts);
  addFinding(findings, "contact", "missing-contact-name", "warning", missingContactNames);
  addFinding(findings, "contact", "invalid-contact-email", "warning", invalidContactEmails);
  addFinding(findings, "contact", "duplicate-email-within-company", "manual-review", contactEmailDuplicates);
  addFinding(findings, "part", "missing-part-number", "rejected", missingPartNumbers);
  addFinding(findings, "part", "blank-part-description", "warning", blankPartDescriptions);
  addFinding(findings, "part", "duplicate-normalized-part-manufacturer", "manual-review", partDuplicates);
  addFinding(findings, "part", "orphan-manufacturer", "manual-review", orphanManufacturers);
  addFinding(findings, "part", "orphan-aircraft", "warning", orphanAircraft);

  const blockingCodes = findings.filter((finding) => finding.severity === "manual-review" || finding.severity === "rejected").map((finding) => finding.code);
  const warningCount = findings.filter((finding) => finding.severity === "warning").reduce((sum, finding) => sum + finding.count, 0);
  const manualReviewCount = findings.filter((finding) => finding.severity === "manual-review").reduce((sum, finding) => sum + finding.count, 0);
  const rejectionCount = findings.filter((finding) => finding.severity === "rejected").reduce((sum, finding) => sum + finding.count, 0);

  return {
    mode: "dry-run",
    sourceSystem: "yoyamic",
    databaseName,
    tenantCode,
    generatedAt: new Date().toISOString(),
    importerVersion: YOYAMIC_IMPORTER_VERSION,
    sourceReadOnly: true,
    sourceCounts: {
      companies: snapshot.companies.length,
      companyAddresses: snapshot.companyDetails.length,
      contacts: snapshot.contacts.length,
      parts: snapshot.parts.length
    },
    candidateCounts: {
      companies: snapshot.companies.length - rejectionCount,
      companyAddresses: snapshot.companyDetails.length - orphanDetails,
      contacts: snapshot.contacts.length - orphanContacts,
      parts: snapshot.parts.length - missingPartNumbers
    },
    findings,
    warningCount,
    manualReviewCount,
    rejectionCount,
    probableDuplicateCount: companyNameDuplicates + contactEmailDuplicates + partDuplicates,
    orphanCount: orphanDetails + orphanContacts + orphanManufacturers + orphanAircraft,
    unmappedFieldCount: 28,
    fullImportGate: blockingCodes.length ? "blocked" : "pass",
    blockingCodes,
    estimatedPostgresGrowthBytes: Math.ceil(
      snapshot.companies.length * 1800 + snapshot.companyDetails.length * 900 + snapshot.contacts.length * 1200 + snapshot.parts.length * 1000
    )
  };
}

export class LiveYoyamicReadonlySource {
  private constructor(private readonly connection: Connection, readonly databaseName: string) {}

  static async connect(url: string): Promise<LiveYoyamicReadonlySource> {
    const connection = await mysql.createConnection({ uri: url, charset: "utf8mb4", connectTimeout: 10_000, rowsAsArray: false });
    await connection.query("SET SESSION tx_read_only=1");
    const [rows] = await connection.query<RowDataPacket[]>("SELECT @@session.tx_read_only AS read_only");
    if (Number(rows[0]?.read_only) !== 1) {
      await connection.end();
      throw new Error("Yoyamic connection did not enter tx_read_only mode.");
    }
    const parsed = new URL(url);
    return new LiveYoyamicReadonlySource(connection, decodeURIComponent(parsed.pathname.replace(/^\//, "")));
  }

  private async select<T extends RowDataPacket>(sql: string): Promise<T[]> {
    assertYoyamicSelectOnly(sql);
    const [rows] = await this.connection.query<T[]>(sql);
    return rows;
  }

  async readSnapshot(): Promise<YoyamicLegacySnapshot> {
    const companies = await this.select<LegacyCompanyRow>(`SELECT Fld_Company_ID id, Fld_Company_Name name, \`delete\` deletedFlag, status, internet website, cage_code cageCode, last_activity lastActivity FROM tb_company ORDER BY Fld_Company_ID`);
    const companyDetails = await this.select<LegacyCompanyDetailRow>(`SELECT id_tbl_company_Details id, Fld_Company_ID companyId, Fld_Company_Type_ID companyTypeId, Fld_Company_Country country, Fld_Company_City city, Fld_Company_State state, Fld_Company_Street street, Fld_Company_ZipCode postalCode, Fld_Company_Fax fax, Fld_Company_Phone phone, Fld_Company_Email email, Fld_Company_Score score, Fld_Remark notes, Fld_VAT_Nbr vatNumber, Fld_Date_Of_First_Contact firstContact, Fld_Company_Address_Type addressType, UTC_timezone timezone, title_address label FROM tbl_Company_Details ORDER BY id_tbl_company_Details`);
    const contacts = await this.select<LegacyContactRow>(`SELECT id_company_contact id, Fld_Company_ID companyId, Fld_Contact_Name name, Fld_Contact_Phone phone, Fld_Contact_Phone2 phone2, Fld_Contact_Fax fax, Fld_Company_Mobile mobile, Fld_Contact_Division_ID divisionId, Fld_Contact_Email email, Fld_Contact_Title title, Fld_Contact_Remark notes, status, entry_date entryDate, modified_date modifiedDate FROM tb_company_contact ORDER BY id_company_contact`);
    const parts = await this.select<LegacyPartRow>(`SELECT Fld_Part_ID id, Fld_Part_Nbr partNumber, Fld_Part_Desc description, Fld_Part_MFG manufacturerId, Fld_AC_ID aircraftId, Fld_Remark notes, status, alt_pn alternatesText, Fld_Add_PN_Date addedDate, ata_chapter ata, cage_code cageCode FROM tbl_Parts ORDER BY Fld_Part_ID`);
    const companyTypes = await this.select<IdNameRow>(`SELECT Fld_Company_Type_ID id, Fld_Company_Type_Text name FROM tbl_Company_Type ORDER BY Fld_Company_Type_ID`);
    const aircraft = await this.select<IdNameRow>(`SELECT Fld_AC_ID id, CONCAT_WS(' ', Fld_AC_Manufacturer, Fld_AC_Model, Fld_AC_Series) name FROM tbl_Aircraft ORDER BY Fld_AC_ID`);
    return { companies, companyDetails, contacts, parts, companyTypes, aircraft };
  }

  async close(): Promise<void> { await this.connection.end(); }
}

async function main(): Promise<void> {
  const sourceUrl = process.env.YOYAMIC_DATABASE_URL;
  const reportPath = process.env.YOYAMIC_AUDIT_REPORT_PATH;
  if (!sourceUrl || !reportPath) throw new Error("Set YOYAMIC_DATABASE_URL and YOYAMIC_AUDIT_REPORT_PATH.");
  const source = await LiveYoyamicReadonlySource.connect(sourceUrl);
  try {
    const report = auditYoyamicSnapshot(await source.readSnapshot(), source.databaseName, process.env.TENANT_CODE ?? "aci770");
    await writeFile(reportPath, `${JSON.stringify(report, null, 2)}\n`, { mode: 0o600 });
    console.log(JSON.stringify({
      mode: report.mode,
      sourceReadOnly: report.sourceReadOnly,
      sourceCounts: report.sourceCounts,
      warningCount: report.warningCount,
      manualReviewCount: report.manualReviewCount,
      rejectionCount: report.rejectionCount,
      probableDuplicateCount: report.probableDuplicateCount,
      orphanCount: report.orphanCount,
      unmappedFieldCount: report.unmappedFieldCount,
      fullImportGate: report.fullImportGate,
      blockingCodes: report.blockingCodes,
      estimatedPostgresGrowthBytes: report.estimatedPostgresGrowthBytes
    }, null, 2));
  } finally {
    await source.close();
  }
}

if (process.argv[1]?.endsWith("yoyamic-live-audit.ts") || process.argv[1]?.endsWith("yoyamic-live-audit.js")) {
  main().catch((error: unknown) => {
    console.error(error instanceof Error ? error.message : "Yoyamic audit failed.");
    process.exitCode = 1;
  });
}
