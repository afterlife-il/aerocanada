FROM node:20-alpine AS build
ARG NEXT_PUBLIC_BASE_PATH=""
ARG NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE=persistent-api
ARG NEXT_PUBLIC_SAAS_API_BASE_URL=/api
ENV NEXT_PUBLIC_BASE_PATH=$NEXT_PUBLIC_BASE_PATH \
    NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE=$NEXT_PUBLIC_SAAS_DATA_SOURCE_MODE \
    NEXT_PUBLIC_SAAS_API_BASE_URL=$NEXT_PUBLIC_SAAS_API_BASE_URL
WORKDIR /app
COPY package.json package-lock.json ./
COPY apps/api/package.json apps/api/package.json
COPY apps/web/package.json apps/web/package.json
COPY packages/shared/package.json packages/shared/package.json
RUN npm ci
COPY tsconfig.base.json ./
COPY packages/shared packages/shared
COPY apps/web apps/web
RUN npm run build -w @saas-aviation/shared && npm run build -w @saas-aviation/web

FROM nginxinc/nginx-unprivileged:1.27-alpine AS runtime
ARG BUILD_COMMIT
ARG BUILD_TIMESTAMP
LABEL org.opencontainers.image.title="Ready2Go SaaS_Aviation Web" \
      org.opencontainers.image.revision="$BUILD_COMMIT" \
      org.opencontainers.image.created="$BUILD_TIMESTAMP" \
      org.opencontainers.image.version="0.1.0-staging"
COPY apps/web/nginx.staging.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/apps/web/out /usr/share/nginx/html
EXPOSE 8080
