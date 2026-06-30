import { Button } from "@/components/ui/button";

export default function LoginPage() {
  return (
    <main className="grid min-h-screen place-items-center bg-background px-4">
      <section className="w-full max-w-md rounded-lg border border-border bg-panel p-6 shadow-sm">
        <div className="text-xs font-semibold uppercase text-muted">SaaS auth bridge</div>
        <h1 className="mt-2 text-2xl font-semibold text-foreground">Sign in to AeroCanada ERP</h1>
        <p className="mt-2 text-sm text-muted">
          Local mock flow for the foundation build. Provider integrations remain behind the auth abstraction until approved.
        </p>
        <form className="mt-5 space-y-3">
          <label className="block text-sm font-semibold">
            Email
            <input className="mt-1 h-10 w-full rounded-md border border-border px-3 text-sm outline-none focus:border-accent" defaultValue="ops@example.test" />
          </label>
          <label className="block text-sm font-semibold">
            Password
            <input className="mt-1 h-10 w-full rounded-md border border-border px-3 text-sm outline-none focus:border-accent" type="password" defaultValue="password" />
          </label>
          <Button className="w-full" variant="primary" type="button">
            Continue to dashboard
          </Button>
        </form>
        <div className="mt-4 grid grid-cols-3 gap-2 text-xs text-muted">
          <div className="rounded-md border border-border bg-panel-muted p-2 text-center">Google later</div>
          <div className="rounded-md border border-border bg-panel-muted p-2 text-center">Microsoft later</div>
          <div className="rounded-md border border-border bg-panel-muted p-2 text-center">TOTP later</div>
        </div>
      </section>
    </main>
  );
}
