"use client";

import { FormEvent, useMemo, useState } from "react";
import { Button } from "@/components/ui/button";
import { getDataSourceConfig } from "@/lib/data-source-mode";
import { persistentApi } from "@/lib/persistent-api";

export function PersistentLoginForm() {
  const config = useMemo(() => getDataSourceConfig(), []);
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (config.mode !== "persistent-api") return;
    const data = new FormData(event.currentTarget);
    setBusy(true); setError(null);
    try {
      await persistentApi.login(String(data.get("email") ?? ""), String(data.get("password") ?? ""), config);
      window.location.assign("/companies");
    } catch (cause) { setError(cause instanceof Error ? cause.message : "Sign-in failed."); setBusy(false); }
  }

  return <form className="mt-5 space-y-4" onSubmit={submit}>
    {config.mode !== "persistent-api" ? <div className="rounded border border-border bg-panel-muted p-3 text-sm text-muted">Sample-static mode is read-only. Local API sign-in is enabled only in persistent-api mode.</div> : null}
    {error ? <div className="rounded border border-[oklch(0.74_0.17_25)] p-3 text-sm text-[oklch(0.4_0.15_25)]">{error}</div> : null}
    <label className="block text-sm font-semibold">Email<input className="mt-1 h-10 w-full rounded-md border border-border bg-background px-3 text-sm outline-none focus:border-accent" name="email" type="email" autoComplete="email" required /></label>
    <label className="block text-sm font-semibold">Password<input className="mt-1 h-10 w-full rounded-md border border-border bg-background px-3 text-sm outline-none focus:border-accent" name="password" type="password" autoComplete="current-password" required /></label>
    <Button className="w-full" variant="primary" type="submit" disabled={busy || config.mode !== "persistent-api"}>{busy ? "Signing in…" : "Continue"}</Button>
  </form>;
}
