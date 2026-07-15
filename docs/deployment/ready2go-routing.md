# Ready2Go staging routing

The approved product route is `aviation.ready2go.aero`, separate from the abandoned stack at `aerocanada.ready2go.aero` and from all legacy AeroCanada/Yoyamic domains.

The domain-specific Plesk reverse proxy must forward HTTPS traffic to `http://127.0.0.1:8180`. The staging web container serves the static frontend and forwards `/api/` to the internal Express service. Authorization, request body, method, host, and forwarded protocol/IP headers must be preserved. CORS is restricted to `https://aviation.ready2go.aero`.

Required checks before a graceful reload are `nginx -t` and `apachectl configtest`. DNS success must be reported only when `aviation.ready2go.aero` resolves to `217.182.69.159`. The old `aerocanada.ready2go.aero` route remains available for rollback during acceptance.
