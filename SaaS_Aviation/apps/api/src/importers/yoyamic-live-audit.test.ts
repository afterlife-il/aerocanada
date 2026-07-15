import assert from "node:assert/strict";
import test from "node:test";
import type { RowDataPacket } from "mysql2";
import {
  auditYoyamicSnapshot,
  normalizeCompanyComparisonName,
  normalizeEmailForComparison,
  parseLegacyAlternates,
  sourceChecksum,
  splitLegacyContactName,
  validEmail,
  type YoyamicLegacySnapshot
} from "./yoyamic-live-audit.js";
import { assertYoyamicSelectOnly } from "./yoyamic-readonly-source.js";

const row = {} as RowDataPacket;

function snapshot(): YoyamicLegacySnapshot {
  return {
    companies: [
      { ...row, id: 1, name: "Acme Aviation Inc.", deletedFlag: "FAUX", status: "Available", website: "", cageCode: "", lastActivity: null },
      { ...row, id: 2, name: "ACME  AVIATION", deletedFlag: "FAUX", status: "Available", website: "", cageCode: "", lastActivity: null }
    ],
    companyDetails: [
      { ...row, id: 10, companyId: 1, companyTypeId: 1, country: "Canada", city: "", state: "", street: "", postalCode: "", fax: "", phone: "", email: "", score: "", notes: "", vatNumber: "", firstContact: "", addressType: 0, timezone: "", label: "" },
      { ...row, id: 11, companyId: 99, companyTypeId: 1, country: "Canada", city: "", state: "", street: "", postalCode: "", fax: "", phone: "", email: "", score: "", notes: "", vatNumber: "", firstContact: "", addressType: 0, timezone: "", label: "" }
    ],
    contacts: [
      { ...row, id: 20, companyId: 1, name: "Casey Morgan", phone: "", phone2: "", fax: "", mobile: "", divisionId: 0, email: "CASEY@example.test", title: "", notes: "", status: "Available", entryDate: "", modifiedDate: null },
      { ...row, id: 21, companyId: 1, name: "", phone: "", phone2: "", fax: "", mobile: "", divisionId: 0, email: "casey@example.test", title: "", notes: "", status: "Available", entryDate: "", modifiedDate: null },
      { ...row, id: 22, companyId: 99, name: "Orphan", phone: "", phone2: "", fax: "", mobile: "", divisionId: 0, email: "invalid", title: "", notes: "", status: "Available", entryDate: "", modifiedDate: null }
    ],
    parts: [
      { ...row, id: 30, partNumber: "ABC-123", description: "A", manufacturerId: 1, aircraftId: 1, notes: "", status: "Available", alternatesText: "ALT-1; ALT-2", addedDate: "", ata: 0, cageCode: "" },
      { ...row, id: 31, partNumber: "ABC123", description: "B", manufacturerId: 1, aircraftId: 1, notes: "", status: "Available", alternatesText: "", addedDate: "", ata: 0, cageCode: "" }
    ],
    companyTypes: [{ ...row, id: 1, name: "Supplier" }],
    aircraft: [{ ...row, id: 1, name: "Airbus A320" }]
  };
}

test("legacy normalization preserves display inputs while producing deterministic comparison values", () => {
  assert.equal(normalizeCompanyComparisonName("  ACME Aviation, Inc.  "), "acmeaviation");
  assert.equal(normalizeEmailForComparison(" CASEY@Example.Test "), "casey@example.test");
  assert.equal(validEmail("bad"), false);
  assert.deepEqual(splitLegacyContactName("Casey Morgan"), { firstName: "Casey", lastName: "Morgan", incomplete: false });
  assert.deepEqual(parseLegacyAlternates("ALT-1; ALT-2\nALT-1"), ["ALT-1", "ALT-2"]);
  assert.equal(sourceChecksum({ id: 1 }), sourceChecksum({ id: 1 }));
});

test("dry-run blocks ambiguous duplicates and orphans without exposing source rows", () => {
  const report = auditYoyamicSnapshot(snapshot(), "legacy_test");
  assert.equal(report.sourceReadOnly, true);
  assert.equal(report.fullImportGate, "blocked");
  assert.ok(report.blockingCodes.includes("duplicate-normalized-company-name"));
  assert.ok(report.blockingCodes.includes("duplicate-normalized-part-manufacturer"));
  assert.ok(report.blockingCodes.includes("orphan-contact"));
  assert.equal(JSON.stringify(report).includes("casey@example.test"), false);
});

test("source SQL allowlist rejects writes, locking reads, multi-statements and file writes", () => {
  assert.doesNotThrow(() => assertYoyamicSelectOnly("SELECT id FROM tb_company LIMIT 1"));
  for (const sql of [
    "UPDATE tb_company SET status='x'",
    "SELECT * FROM tb_company FOR UPDATE",
    "SELECT 1; DELETE FROM tb_company",
    "SELECT * INTO OUTFILE '/tmp/x' FROM tb_company",
    "CALL write_company()"
  ]) assert.throws(() => assertYoyamicSelectOnly(sql));
});
