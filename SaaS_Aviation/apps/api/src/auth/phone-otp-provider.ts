import { appendFile, chmod, mkdir } from "node:fs/promises";
import { dirname } from "node:path";

export interface PhoneOtpDeliveryProvider {
  readonly name: "staging-spool";
  deliver(input: { challengeId: string; phoneE164: string; code: string; expiresAt: string }): Promise<void>;
}

export class StagingSpoolPhoneOtpProvider implements PhoneOtpDeliveryProvider {
  readonly name = "staging-spool" as const;
  constructor(private readonly path: string) {}

  async deliver(input: { challengeId: string; phoneE164: string; code: string; expiresAt: string }): Promise<void> {
    await mkdir(dirname(this.path), { recursive: true, mode: 0o700 });
    await appendFile(this.path, `${JSON.stringify({ ...input, createdAt: new Date().toISOString() })}\n`, { encoding: "utf8", mode: 0o600 });
    await chmod(this.path, 0o600);
  }
}
