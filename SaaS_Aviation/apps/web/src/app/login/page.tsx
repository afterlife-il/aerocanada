import { currentSession } from "@/lib/data";
import { PersistentLoginForm } from "@/components/modules/persistent-login-form";

const providers = ["Google", "Microsoft", "Apple", "LinkedIn"];

export default function LoginPage() {
  return (
    <main className="min-h-screen bg-background px-4 py-8 text-foreground">
      <section className="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-5xl items-center gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div className="max-w-xl">
          <div className="text-sm font-semibold text-accent">SaaS Aviation ERP</div>
          <h1 className="mt-3 text-3xl font-semibold leading-tight text-foreground">Secure tenant access for aviation operations.</h1>
          <p className="mt-3 max-w-prose text-sm leading-6 text-muted">
            Sign in to a tenant-scoped workspace for inventory, company, RFQ, quote, and audit workflows.
          </p>
          <div className="mt-6 grid max-w-lg gap-3 sm:grid-cols-3">
            <div className="rounded-md border border-border bg-panel px-3 py-3">
              <div className="text-xs text-muted">Tenant</div>
              <div className="mt-1 text-sm font-semibold">{currentSession.tenant.code}</div>
            </div>
            <div className="rounded-md border border-border bg-panel px-3 py-3">
              <div className="text-xs text-muted">Role</div>
              <div className="mt-1 text-sm font-semibold">Admin</div>
            </div>
            <div className="rounded-md border border-border bg-panel px-3 py-3">
              <div className="text-xs text-muted">MFA</div>
              <div className="mt-1 text-sm font-semibold">Supported</div>
            </div>
          </div>
        </div>

        <div className="rounded-lg border border-border bg-panel p-5">
          <div className="flex items-start justify-between gap-4 border-b border-border pb-4">
            <div>
              <h2 className="text-xl font-semibold">Sign in</h2>
              <p className="mt-1 text-sm text-muted">{currentSession.tenant.name}</p>
            </div>
            <div className="rounded-md border border-border bg-panel-muted px-2 py-1 text-xs font-semibold text-muted">Password</div>
          </div>

          <PersistentLoginForm />

          <div className="mt-5 border-t border-border pt-4">
            <div className="text-xs font-semibold text-muted">External identity providers</div>
            <div className="mt-3 grid grid-cols-2 gap-2">
              {providers.map((provider) => (
                <button
                  key={provider}
                  className="min-h-12 rounded-md border border-border bg-background px-3 py-2 text-sm font-semibold text-muted"
                  type="button"
                  disabled
                >
                  <span className="block">{provider}</span>
                  <span className="block text-[10px] font-normal">Not configured for this staging environment</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
