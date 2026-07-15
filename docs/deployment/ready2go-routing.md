# Ready2Go staging routing

The approved Ready2Go Aviation SaaS platform route is `aviation.ready2go.aero`, separate from the abandoned stack at `aerocanada.ready2go.aero` and from all legacy AeroCanada/Yoyamic domains. AeroCanada is the first tenant and is routed under `/AeroCanada`.

The Plesk subdomain and domain-specific Apache proxy forward traffic to `http://127.0.0.1:8180`. The staging web container serves the static frontend and forwards `/api/` to the internal Express service. Forced-host validation proved Authorization, PATCH method/body, host routing and static assets. CORS is restricted to `https://aviation.ready2go.aero`.

The A record `aviation.ready2go.aero -> 217.182.69.159` is visible through the server resolver, Google DNS, Cloudflare DNS, and the authoritative path. Plesk issued and assigned `Lets Encrypt aviation.ready2go.aero`, valid from 2026-07-15 through 2026-10-13. Both `nginx -t` and `apachectl configtest` pass; public pages, API, OpenAPI, and assets return 200 over verified HTTPS. `neo.ready2go.aero` continues to resolve, present its existing certificate, and serve its intended redirect. The old `aerocanada.ready2go.aero` route remains available for rollback.
