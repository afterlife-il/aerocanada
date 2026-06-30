import test from "node:test";
import assert from "node:assert/strict";
import { sampleInternalStock, sampleRfqs, sampleTenant, sampleUsers } from "./sample-data.js";

test("Qty 0 remains represented as zero", () => {
  const zeroQty = sampleInternalStock.find((item) => item.qty === 0);
  assert.ok(zeroQty);
  assert.equal(zeroQty?.qty, 0);
});

test("RFQ_ID remains present on RFQ summaries", () => {
  assert.ok(sampleRfqs.every((rfq) => rfq.rfqId.length > 0));
});

test("first tenant seed has an admin user and tenant-owned records", () => {
  const admin = sampleUsers.find((user) => user.tenantId === sampleTenant.id && user.roles.includes("owner_admin"));
  assert.ok(admin);
  assert.equal(sampleTenant.name, "AEROCANADA INDUSTRIES 770 INC.");
  assert.equal(sampleInternalStock.every((item) => item.tenantId === sampleTenant.id), true);
  assert.equal(sampleRfqs.every((rfq) => rfq.tenantId === sampleTenant.id), true);
});
