import { randomUUID } from "node:crypto";
import { writeFile } from "node:fs/promises";
import { Pool, type PoolClient } from "pg";
import { normalizePartNumber } from "@saas-aviation/shared";
import {
  LiveYoyamicReadonlySource,
  YOYAMIC_IMPORTER_VERSION,
  auditYoyamicSnapshot,
  normalizeCompanyComparisonName,
  parseLegacyAlternates,
  sourceChecksum,
  splitLegacyContactName,
  validEmail,
  type LegacyCompanyDetailRow,
  type LegacyCompanyRow,
  type LegacyContactRow,
  type LegacyPartRow,
  type YoyamicLegacySnapshot
} from "./yoyamic-live-audit.js";

type SampleEntity = "company" | "company-address" | "contact" | "part";
type ImportStatus = "inserted" | "unchanged" | "warning" | "quarantined";

interface SampleResult {
  batchId: string;
  sourceReadOnly: true;
  imported: Record<SampleEntity, number>;
  unchanged: Record<SampleEntity, number>;
  quarantined: Record<SampleEntity, number>;
  warnings: string[];
  fullImportGate: "blocked" | "pass";
  blockingCodes: string[];
}

const roleByType: Record<number, string[]> = {
  1: ["repair-station", "supplier"],
  2: ["airline", "customer"],
  3: ["broker", "supplier", "customer"],
  4: ["broker", "supplier", "customer"],
  5: ["distributor", "supplier"],
  6: ["manufacturer", "supplier"],
  15: ["stock-owner", "supplier"]
};

function emptyCounts(): Record<SampleEntity, number> {
  return { company: 0, "company-address": 0, contact: 0, part: 0 };
}

function safeText(value: unknown, maximum = 500): string | null {
  const text = String(value ?? "").trim().replace(/\s+/g, " ");
  return text ? text.slice(0, maximum) : null;
}

function activeCompany(row: LegacyCompanyRow): boolean {
  return !["vrai", "delete"].includes(String(row.deletedFlag ?? "").trim().toLowerCase()) && String(row.status ?? "").trim().toLowerCase() !== "archive";
}

export function selectSample(snapshot: YoyamicLegacySnapshot): {
  companies: LegacyCompanyRow[];
  details: LegacyCompanyDetailRow[];
  contacts: LegacyContactRow[];
  parts: LegacyPartRow[];
} {
  const nameCounts = new Map<string, number>();
  for (const company of snapshot.companies) {
    const key = normalizeCompanyComparisonName(company.name);
    if (key) nameCounts.set(key, (nameCounts.get(key) ?? 0) + 1);
  }
  const detailsByCompany = new Map<number, LegacyCompanyDetailRow[]>();
  for (const detail of snapshot.companyDetails) {
    const rows = detailsByCompany.get(detail.companyId) ?? [];
    rows.push(detail);
    detailsByCompany.set(detail.companyId, rows);
  }
  const contactsByCompany = new Map<number, LegacyContactRow[]>();
  for (const contact of snapshot.contacts) {
    const rows = contactsByCompany.get(contact.companyId) ?? [];
    rows.push(contact);
    contactsByCompany.set(contact.companyId, rows);
  }
  const safeCompanies = snapshot.companies.filter((company) => {
    const key = normalizeCompanyComparisonName(company.name);
    return activeCompany(company) && Boolean(key) && nameCounts.get(key) === 1 && (detailsByCompany.get(company.id)?.length ?? 0) > 0;
  });
  const selected = new Map<number, LegacyCompanyRow>();
  for (const typeId of [2, 1, 3, 5, 6, 15]) {
    const candidate = safeCompanies.find((company) => detailsByCompany.get(company.id)?.some((detail) => detail.companyTypeId === typeId));
    if (candidate) selected.set(candidate.id, candidate);
  }
  const multipleDetails = safeCompanies.find((company) => (detailsByCompany.get(company.id)?.length ?? 0) > 1);
  const multipleContacts = safeCompanies.find((company) => (contactsByCompany.get(company.id)?.length ?? 0) > 1);
  const incompleteOptional = safeCompanies.find((company) => {
    const detail = detailsByCompany.get(company.id)?.[0];
    return detail && !safeText(detail.email) && !safeText(detail.phone);
  });
  for (const candidate of [multipleDetails, multipleContacts, incompleteOptional]) if (candidate) selected.set(candidate.id, candidate);

  const companies = [...selected.values()].slice(0, 9);
  const selectedIds = new Set(companies.map((company) => company.id));
  const details = snapshot.companyDetails.filter((detail) => selectedIds.has(detail.companyId));
  const contacts = snapshot.contacts
    .filter((contact) => selectedIds.has(contact.companyId) && Boolean(safeText(contact.name)))
    .filter((contact, index, rows) => rows.filter((row) => row.companyId === contact.companyId).indexOf(contact) < 3);

  const companyIds = new Set(snapshot.companies.map((company) => company.id));
  const aircraftIds = new Set(snapshot.aircraft.map((aircraft) => aircraft.id));
  const keyCounts = new Map<string, number>();
  for (const part of snapshot.parts) {
    if (!part.partNumber?.trim()) continue;
    const key = `${normalizePartNumber(part.partNumber)}:${part.manufacturerId ?? 0}`;
    keyCounts.set(key, (keyCounts.get(key) ?? 0) + 1);
  }
  const safeParts = snapshot.parts.filter((part) => {
    if (!part.partNumber?.trim()) return false;
    const key = `${normalizePartNumber(part.partNumber)}:${part.manufacturerId ?? 0}`;
    return keyCounts.get(key) === 1 && (!part.manufacturerId || companyIds.has(part.manufacturerId)) && (!part.aircraftId || aircraftIds.has(part.aircraftId));
  });
  const partChoices = [
    safeParts.find((part) => Boolean(safeText(part.manufacturerId))),
    safeParts.find((part) => parseLegacyAlternates(part.alternatesText).length > 0),
    safeParts.find((part) => Boolean(part.aircraftId)),
    safeParts.find((part) => !safeText(part.description)),
    safeParts.find((part) => /[-/. ]/.test(part.partNumber ?? "")),
    ...safeParts.slice(0, 5)
  ].filter((part): part is LegacyPartRow => Boolean(part));
  const uniqueParts = [...new Map(partChoices.map((part) => [part.id, part])).values()].slice(0, 7);
  return { companies, details, contacts, parts: uniqueParts };
}

async function recordImport(client: PoolClient, input: {
  batchId: string; tenantId: string; table: string; sourceId: number; checksum: string; entity: SampleEntity; destinationId: string; status: ImportStatus; warnings?: number;
}): Promise<void> {
  await client.query(
    `INSERT INTO imported_records (batch_id, tenant_id, source_system, source_table, source_primary_key, source_checksum, destination_entity, destination_id, import_status, warning_count, reconciliation_status, reconciled_at)
     VALUES ($1,$2,'yoyamic',$3,$4,$5,$6,$7,$8,$9,'matched',now())
     ON CONFLICT (tenant_id, source_system, source_table, source_primary_key, destination_entity)
     DO UPDATE SET batch_id=EXCLUDED.batch_id, import_status=EXCLUDED.import_status, imported_at=now(), reconciliation_status='matched', reconciled_at=now()`,
    [input.batchId, input.tenantId, input.table, String(input.sourceId), input.checksum, input.entity, input.destinationId, input.status, input.warnings ?? 0]
  );
  if (input.entity !== "company-address") {
    await client.query(
      `INSERT INTO legacy_mappings (source_system,source_table,source_id,tenant_id,target_entity_type,target_entity_id,checksum)
       VALUES ('yoyamic',$1,$2,$3,$4,$5,$6)
       ON CONFLICT (source_system,source_table,source_id,tenant_id,target_entity_type)
       DO UPDATE SET target_entity_id=EXCLUDED.target_entity_id, imported_at=now(), checksum=EXCLUDED.checksum`,
      [input.table, String(input.sourceId), input.tenantId, input.entity, input.destinationId, input.checksum]
    );
  }
}

async function quarantineRecord(client: PoolClient, input: {
  batchId: string; tenantId: string; table: string; sourceId: number; checksum: string; entity: SampleEntity; reason: string; details?: Record<string, unknown>;
}): Promise<void> {
  await client.query(
    `INSERT INTO import_quarantine (batch_id,tenant_id,source_system,source_table,source_primary_key,entity_type,reason_code,severity,source_checksum,details)
     VALUES ($1,$2,'yoyamic',$3,$4,$5,$6,'manual-review',$7,$8)
     ON CONFLICT (tenant_id,source_system,source_table,source_primary_key,entity_type,reason_code)
     DO UPDATE SET batch_id=EXCLUDED.batch_id, source_checksum=EXCLUDED.source_checksum, details=EXCLUDED.details, created_at=now(), resolved_at=NULL`,
    [input.batchId,input.tenantId,input.table,String(input.sourceId),input.entity,input.reason,input.checksum,input.details ?? {}]
  );
}

async function existingChecksum(client: PoolClient, tenantId: string, table: string, sourceId: number, entity: SampleEntity): Promise<string | null> {
  const result = await client.query<{ source_checksum: string }>(
    `SELECT source_checksum FROM imported_records WHERE tenant_id=$1 AND source_system='yoyamic' AND source_table=$2 AND source_primary_key=$3 AND destination_entity=$4`,
    [tenantId, table, String(sourceId), entity]
  );
  return result.rows[0]?.source_checksum ?? null;
}

async function importSample(client: PoolClient, snapshot: YoyamicLegacySnapshot, reportPath: string): Promise<SampleResult> {
  const audit = auditYoyamicSnapshot(snapshot, "aerocanada_yoyamic");
  const sample = selectSample(snapshot);
  const tenantResult = await client.query<{ id: string }>("SELECT id FROM tenants WHERE lower(code)=lower('aci770')");
  const tenantId = tenantResult.rows[0]?.id;
  if (!tenantId) throw new Error("Tenant aci770 does not exist.");
  const batchId = `yoyamic-sample-${randomUUID()}`;
  const imported = emptyCounts();
  const unchanged = emptyCounts();
  const quarantined = emptyCounts();
  const warnings: string[] = [];
  const companyById = new Map(snapshot.companies.map((company) => [company.id, company]));
  const companyTypeById = new Map(snapshot.companyTypes.map((type) => [type.id, type.name]));
  const aircraftById = new Map(snapshot.aircraft.map((aircraft) => [aircraft.id, aircraft.name]));

  await client.query("BEGIN");
  try {
    await client.query(
      `INSERT INTO import_batches (id,tenant_id,source_system,mode,status,importer_version,metadata,source_counts)
       VALUES ($1,$2,'yoyamic','sample','running',$3,$4,$5)`,
      [batchId, tenantId, YOYAMIC_IMPORTER_VERSION, { scope: "company-contact-part", stockImported: false }, audit.sourceCounts]
    );
    for (const company of sample.companies) {
      const checksum = sourceChecksum(company);
      const id = `yoyamic-company-${company.id}`;
      const previous = await existingChecksum(client, tenantId, "tb_company", company.id, "company");
      if (previous && previous !== checksum) {
        quarantined.company++;
        warnings.push("company-source-changed");
        await quarantineRecord(client,{batchId,tenantId,table:"tb_company",sourceId:company.id,checksum,entity:"company",reason:"source-checksum-changed"});
        continue;
      }
      const primary = sample.details.find((detail) => detail.companyId === company.id);
      const roles = [...new Set((sample.details.filter((detail) => detail.companyId === company.id).flatMap((detail) => roleByType[detail.companyTypeId] ?? ["customer"])))];
      const insert = await client.query(
        `INSERT INTO companies (id,tenant_id,legacy_id,name,status,email,phone,website,address_line_1,city,state,postal_code,country,risk,notes,tags,created_by,updated_by)
         VALUES ($1,$2,$3,$4,'active',$5,$6,$7,$8,$9,$10,$11,$12,'normal',$13,$14,$15,$15)
         ON CONFLICT DO NOTHING RETURNING id`,
        [id, tenantId, String(company.id), safeText(company.name,240), validEmail(primary?.email) ? safeText(primary?.email,250) : null, safeText(primary?.phone,250), safeText(company.website,250), safeText(primary?.street), safeText(primary?.city,250), safeText(primary?.state,250), safeText(primary?.postalCode,250), safeText(primary?.country,250), safeText(primary?.notes), ["legacy-yoyamic"], "yoyamic-importer"]
      );
      if (!insert.rowCount) {
        const exact = await client.query("SELECT 1 FROM companies WHERE tenant_id=$1 AND id=$2 AND legacy_id=$3",[tenantId,id,String(company.id)]);
        if (!exact.rowCount) {
          quarantined.company++;
          warnings.push("company-destination-conflict");
          await quarantineRecord(client,{batchId,tenantId,table:"tb_company",sourceId:company.id,checksum,entity:"company",reason:"destination-unique-conflict"});
          continue;
        }
      }
      const status: ImportStatus = insert.rowCount ? "inserted" : "unchanged";
      if (status === "inserted") imported.company++;
      else unchanged.company++;
      for (const role of roles) await client.query("INSERT INTO company_roles (tenant_id,company_id,role) VALUES ($1,$2,$3) ON CONFLICT DO NOTHING", [tenantId,id,role]);
      await recordImport(client,{batchId,tenantId,table:"tb_company",sourceId:company.id,checksum,entity:"company",destinationId:id,status});
    }
    const importedCompanyIds = new Set(sample.companies.map((company) => company.id));
    for (const detail of sample.details.filter((row) => importedCompanyIds.has(row.companyId))) {
      const companyId = `yoyamic-company-${detail.companyId}`;
      const exists = await client.query("SELECT 1 FROM companies WHERE tenant_id=$1 AND id=$2",[tenantId,companyId]);
      if (!exists.rowCount) continue;
      const id = `yoyamic-company-address-${detail.id}`;
      const checksum = sourceChecksum(detail);
      const insert = await client.query(
        `INSERT INTO company_addresses (id,tenant_id,company_id,label,address_line_1,city,state,postal_code,country,is_primary,created_by,updated_by)
         VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,false,$10,$10) ON CONFLICT DO NOTHING RETURNING id`,
        [id,tenantId,companyId,safeText(detail.label,200) ?? `Legacy ${detail.id}`,safeText(detail.street) ?? "Legacy address unavailable",safeText(detail.city,250),safeText(detail.state,250),safeText(detail.postalCode,250),safeText(detail.country,250) ?? "Unknown","yoyamic-importer"]
      );
      const status: ImportStatus=insert.rowCount?"inserted":"unchanged";
      if(status==="inserted") imported["company-address"]++;
      else unchanged["company-address"]++;
      await recordImport(client,{batchId,tenantId,table:"tbl_Company_Details",sourceId:detail.id,checksum,entity:"company-address",destinationId:id,status,warnings:detail.companyTypeId&&!companyTypeById.has(detail.companyTypeId)?1:0});
    }
    for (const contact of sample.contacts) {
      const companyId=`yoyamic-company-${contact.companyId}`;
      const exists=await client.query("SELECT 1 FROM companies WHERE tenant_id=$1 AND id=$2",[tenantId,companyId]); if(!exists.rowCount) continue;
      const id=`yoyamic-contact-${contact.id}`; const checksum=sourceChecksum(contact); const name=splitLegacyContactName(contact.name); const email=validEmail(contact.email)?safeText(contact.email,250):null;
      const previous=await existingChecksum(client,tenantId,"tb_company_contact",contact.id,"contact");
      if(previous&&previous!==checksum){
        quarantined.contact++;
        warnings.push("contact-source-changed");
        await quarantineRecord(client,{batchId,tenantId,table:"tb_company_contact",sourceId:contact.id,checksum,entity:"contact",reason:"source-checksum-changed"});
        continue;
      }
      const insert=await client.query(
        `INSERT INTO contacts (id,tenant_id,company_id,legacy_id,first_name,last_name,job_title,email,phone,mobile,status,notes,created_by,updated_by)
         VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$13) ON CONFLICT DO NOTHING RETURNING id`,
        [id,tenantId,companyId,String(contact.id),name.firstName,name.lastName||"Legacy",safeText(contact.title,200),email,safeText(contact.phone,30),safeText(contact.mobile,30),String(contact.status).toLowerCase()==="available"?"active":"inactive",safeText(contact.notes),"yoyamic-importer"]
      );
      if(!insert.rowCount){
        const exact=await client.query("SELECT 1 FROM contacts WHERE tenant_id=$1 AND id=$2 AND legacy_id=$3",[tenantId,id,String(contact.id)]);
        if(!exact.rowCount){
          quarantined.contact++;
          warnings.push("contact-destination-conflict");
          await quarantineRecord(client,{batchId,tenantId,table:"tb_company_contact",sourceId:contact.id,checksum,entity:"contact",reason:"destination-unique-conflict"});
          continue;
        }
      }
      const warningCount=(name.incomplete?1:0)+(!validEmail(contact.email)?1:0); const status:ImportStatus=insert.rowCount?(warningCount?"warning":"inserted"):"unchanged";
      if(status==="unchanged") unchanged.contact++;
      else imported.contact++;
      await recordImport(client,{batchId,tenantId,table:"tb_company_contact",sourceId:contact.id,checksum,entity:"contact",destinationId:id,status,warnings:warningCount});
    }
    for (const part of sample.parts) {
      const id=`yoyamic-part-${part.id}`; const checksum=sourceChecksum(part); const previous=await existingChecksum(client,tenantId,"tbl_Parts",part.id,"part");
      if(previous&&previous!==checksum){
        quarantined.part++;
        warnings.push("part-source-changed");
        await quarantineRecord(client,{batchId,tenantId,table:"tbl_Parts",sourceId:part.id,checksum,entity:"part",reason:"source-checksum-changed"});
        continue;
      }
      const manufacturer=part.manufacturerId?safeText(companyById.get(part.manufacturerId)?.name,250):null; const aircraft=part.aircraftId?safeText(aircraftById.get(part.aircraftId),250):null; const alternates=parseLegacyAlternates(part.alternatesText).slice(0,100);
      const insert=await client.query(
        `INSERT INTO part_numbers (id,tenant_id,legacy_id,part_number,normalized_part_number,description,manufacturer,manufacturer_code,ata,aircraft,status,alternates,created_by,updated_by)
         VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,'active',$11,$12,$12) ON CONFLICT DO NOTHING RETURNING id`,
        [id,tenantId,String(part.id),part.partNumber!.trim(),normalizePartNumber(part.partNumber!),safeText(part.description,250)??`Legacy part ${part.partNumber}`,manufacturer,safeText(part.cageCode,20),part.ata?safeText(part.ata,20):null,aircraft?[aircraft]:[],alternates,"yoyamic-importer"]
      );
      if(!insert.rowCount){
        const exact=await client.query("SELECT 1 FROM part_numbers WHERE tenant_id=$1 AND id=$2 AND legacy_id=$3",[tenantId,id,String(part.id)]);
        if(!exact.rowCount){
          quarantined.part++;
          warnings.push("part-destination-conflict");
          await quarantineRecord(client,{batchId,tenantId,table:"tbl_Parts",sourceId:part.id,checksum,entity:"part",reason:"destination-unique-conflict"});
          continue;
        }
      }
      const warningCount=safeText(part.description)?0:1; const status:ImportStatus=insert.rowCount?(warningCount?"warning":"inserted"):"unchanged";
      if(status==="unchanged") unchanged.part++;
      else imported.part++;
      if(insert.rowCount) for(const alternate of alternates) await client.query("INSERT INTO part_alternates (tenant_id,part_id,alternate_part_number) VALUES ($1,$2,$3) ON CONFLICT DO NOTHING",[tenantId,id,alternate]);
      await recordImport(client,{batchId,tenantId,table:"tbl_Parts",sourceId:part.id,checksum,entity:"part",destinationId:id,status,warnings:warningCount});
    }
    await client.query(
      `UPDATE import_batches SET status='completed',completed_at=now(),imported_counts=$2,warning_count=$3,error_count=$4 WHERE id=$1`,
      [batchId, imported, warnings.length, Object.values(quarantined).reduce((sum,value)=>sum+value,0)]
    );
    await client.query("COMMIT");
  } catch(error) { await client.query("ROLLBACK"); throw error; }
  const result:SampleResult={batchId,sourceReadOnly:true,imported,unchanged,quarantined,warnings:[...new Set(warnings)],fullImportGate:audit.fullImportGate,blockingCodes:audit.blockingCodes};
  await writeFile(reportPath,`${JSON.stringify(result,null,2)}\n`,{mode:0o600});
  return result;
}

async function main(): Promise<void> {
  if(process.env.SAMPLE_IMPORT_APPROVED!=="true") throw new Error("Set SAMPLE_IMPORT_APPROVED=true for the controlled sample import.");
  const sourceUrl=process.env.YOYAMIC_DATABASE_URL; const databaseUrl=process.env.DATABASE_URL; const reportPath=process.env.YOYAMIC_SAMPLE_REPORT_PATH;
  if(!sourceUrl||!databaseUrl||!reportPath) throw new Error("Set YOYAMIC_DATABASE_URL, DATABASE_URL and YOYAMIC_SAMPLE_REPORT_PATH.");
  const source=await LiveYoyamicReadonlySource.connect(sourceUrl); const pool=new Pool({connectionString:databaseUrl,max:1}); const client=await pool.connect();
  try { const result=await importSample(client,await source.readSnapshot(),reportPath); console.log(JSON.stringify(result,null,2)); }
  finally { client.release(); await pool.end(); await source.close(); }
}

if(process.argv[1]?.endsWith("yoyamic-sample-import.ts")||process.argv[1]?.endsWith("yoyamic-sample-import.js")) main().catch((error:unknown)=>{console.error(error instanceof Error?error.message:"Sample import failed.");process.exitCode=1;});
