# Ready2Go staging routing

The approved product route is `aviation.ready2go.aero`, separate from the abandoned stack at `aerocanada.ready2go.aero` and from all legacy AeroCanada/Yoyamic domains.

The Plesk subdomain and domain-specific Apache proxy forward traffic to `http://127.0.0.1:8180`. The staging web container serves the static frontend and forwards `/api/` to the internal Express service. Forced-host validation proved Authorization, PATCH method/body, host routing and static assets. CORS is restricted to `https://aviation.ready2go.aero`.

Both `nginx -t` and `apachectl configtest` passed before the final graceful reload. Public DNS was still `NXDOMAIN` and the vhost served the default Plesk certificate on 2026-07-15. Required external record: `A aviation.ready2go.aero 217.182.69.159`; issue and assign Let’s Encrypt after propagation. The old `aerocanada.ready2go.aero` route remains available for rollback.
