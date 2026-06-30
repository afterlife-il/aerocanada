# Infrastructure Decisions

## Current Recommendation

Start with containerized web and API services plus managed database services. Keep Kubernetes/ECS optional until workload and tenant isolation requirements justify it.

## Production Readiness Placeholders

- Container registry: TBD.
- Deployment platform: TBD.
- Nginx/HTTPS: terminate TLS at platform load balancer or managed ingress.
- Observability: OpenTelemetry instrumentation, Sentry for app errors, Prometheus/Grafana for service metrics.
- Backup/PITR: required before any production write path.
- Rollback: immutable container tags plus database migration rollback plans.
