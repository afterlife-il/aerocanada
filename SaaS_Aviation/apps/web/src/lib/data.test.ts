import test from "node:test";
import assert from "node:assert/strict";
import { data, getStock } from "./data.js";

test("web data keeps internal and external stock separated", () => {
  assert.equal(data.internalStock.every((stock) => stock.source === "internal"), true);
  assert.equal(data.externalStock.every((stock) => stock.source === "external"), true);
});

test("web data does not hide Qty 0 stock", () => {
  const stock = getStock("stock-2");
  assert.equal(stock.qty, 0);
});
