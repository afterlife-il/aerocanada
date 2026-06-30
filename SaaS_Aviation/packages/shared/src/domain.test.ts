import test from "node:test";
import assert from "node:assert/strict";
import { sampleInternalStock, sampleRfqs } from "./sample-data.js";

test("Qty 0 remains represented as zero", () => {
  const zeroQty = sampleInternalStock.find((item) => item.qty === 0);
  assert.ok(zeroQty);
  assert.equal(zeroQty?.qty, 0);
});

test("RFQ_ID remains present on RFQ summaries", () => {
  assert.ok(sampleRfqs.every((rfq) => rfq.rfqId.length > 0));
});
