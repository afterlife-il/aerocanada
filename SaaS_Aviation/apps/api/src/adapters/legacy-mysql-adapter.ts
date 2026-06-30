import type { AviationErpDataSource } from "@saas-aviation/shared";
import { SampleDataSource } from "./sample-data-source.js";

/**
 * Placeholder adapter boundary for Yoyamic MySQL.
 * Keep initialization lazy so builds and tests never require legacy DB secrets.
 */
export function getLegacyDataSource(): AviationErpDataSource {
  // TODO: replace with read-only MySQL implementation after credentials and query plan are approved.
  return new SampleDataSource();
}
