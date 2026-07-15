import { createCipheriv, createDecipheriv, createHash, createHmac, randomBytes } from "node:crypto";

const BASE32 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

export function normalizeE164(value: string): string | null {
  const normalized = value.replace(/[\s().-]/g, "");
  return /^\+[1-9]\d{7,14}$/.test(normalized) ? normalized : null;
}

export function generateTotpSecret(): string {
  let bits = 0; let value = 0; let output = "";
  for (const byte of randomBytes(20)) {
    value = (value << 8) | byte; bits += 8;
    while (bits >= 5) { output += BASE32[(value >>> (bits - 5)) & 31]; bits -= 5; }
  }
  return output;
}

function decodeBase32(input: string): Buffer {
  let bits = 0; let value = 0; const output: number[] = [];
  for (const character of input.toUpperCase().replace(/=+$/g, "")) {
    const index = BASE32.indexOf(character); if (index < 0) throw new Error("Invalid base32 secret.");
    value = (value << 5) | index; bits += 5;
    if (bits >= 8) { output.push((value >>> (bits - 8)) & 255); bits -= 8; }
  }
  return Buffer.from(output);
}

export function totp(secret: string, at = Date.now()): string {
  const counter = Math.floor(at / 30_000); const buffer = Buffer.alloc(8); buffer.writeBigUInt64BE(BigInt(counter));
  const digest = createHmac("sha1", decodeBase32(secret)).update(buffer).digest();
  const offset = digest[digest.length - 1]! & 15;
  const value = (((digest[offset]! & 127) << 24) | (digest[offset + 1]! << 16) | (digest[offset + 2]! << 8) | digest[offset + 3]!) % 1_000_000;
  return String(value).padStart(6, "0");
}

export function verifyTotp(secret: string, code: string, at = Date.now()): boolean {
  return /^\d{6}$/.test(code) && [-1, 0, 1].some((window) => totp(secret, at + window * 30_000) === code);
}

function encryptionKey(value: string): Buffer {
  if (value.length < 32) throw new Error("AUTH_ENCRYPTION_KEY must contain at least 32 characters.");
  return createHash("sha256").update(value).digest();
}

export function encryptSecret(value: string, keyValue: string): string {
  const iv = randomBytes(12); const cipher = createCipheriv("aes-256-gcm", encryptionKey(keyValue), iv);
  const encrypted = Buffer.concat([cipher.update(value, "utf8"), cipher.final()]);
  return [iv, cipher.getAuthTag(), encrypted].map((part) => part.toString("base64url")).join(".");
}

export function decryptSecret(value: string, keyValue: string): string {
  const [iv, tag, ciphertext] = value.split(".").map((part) => Buffer.from(part ?? "", "base64url"));
  const decipher = createDecipheriv("aes-256-gcm", encryptionKey(keyValue), iv!); decipher.setAuthTag(tag!);
  return Buffer.concat([decipher.update(ciphertext!), decipher.final()]).toString("utf8");
}

export function secretDigest(value: string): string { return createHash("sha256").update(value).digest("hex"); }
