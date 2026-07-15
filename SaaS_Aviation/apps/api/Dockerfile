FROM node:20-alpine AS build
WORKDIR /app
COPY package.json package-lock.json ./
COPY apps/api/package.json apps/api/package.json
COPY apps/web/package.json apps/web/package.json
COPY packages/shared/package.json packages/shared/package.json
RUN npm ci
COPY tsconfig.base.json ./
COPY packages/shared packages/shared
COPY apps/api apps/api
RUN npm run build -w @saas-aviation/shared && npm run build -w @saas-aviation/api
RUN npm prune --omit=dev

FROM node:20-alpine AS runtime
ARG BUILD_COMMIT
ARG BUILD_TIMESTAMP
LABEL org.opencontainers.image.title="Ready2Go SaaS_Aviation API" \
      org.opencontainers.image.revision="$BUILD_COMMIT" \
      org.opencontainers.image.created="$BUILD_TIMESTAMP" \
      org.opencontainers.image.version="0.1.0-staging"
ENV NODE_ENV=production
WORKDIR /app
COPY --from=build --chown=node:node /app/node_modules ./node_modules
COPY --from=build --chown=node:node /app/apps/api/package.json ./apps/api/package.json
COPY --from=build --chown=node:node /app/apps/api/dist ./apps/api/dist
COPY --from=build --chown=node:node /app/packages/shared/package.json ./packages/shared/package.json
COPY --from=build --chown=node:node /app/packages/shared/dist ./packages/shared/dist
COPY --chown=node:node database/migrations ./database/migrations
USER node
EXPOSE 4107
CMD ["node", "apps/api/dist/server.js"]
