export interface NormalizeFormOptions {
  arrayFields?: readonly string[];
  booleanFields?: readonly string[];
}

export function normalizeFormData(data: FormData, options: NormalizeFormOptions = {}): Record<string, unknown> {
  const arrayFields = new Set(options.arrayFields ?? []);
  const booleanFields = new Set(options.booleanFields ?? []);
  const normalized: Record<string, unknown> = {};

  for (const [key, rawValue] of data.entries()) {
    if (booleanFields.has(key)) continue;
    const value = String(rawValue).trim();
    if (arrayFields.has(key)) {
      normalized[key] = value ? value.split(",").map((item) => item.trim()).filter(Boolean) : [];
    } else if (value) {
      normalized[key] = value;
    }
  }

  for (const key of booleanFields) normalized[key] = data.get(key) === "on";
  return normalized;
}
