const listResponse = (schemaRef: string) => ({
  type: "object",
  required: ["data"],
  properties: {
    data: {
      type: "array",
      items: { $ref: schemaRef }
    }
  }
});

export const openApiDocument = {
  openapi: "3.1.0",
  info: {
    title: "SaaS Aviation ERP API",
    version: "0.1.0",
    description: "REST API contract for the next-generation aviation ERP foundation."
  },
  tags: [
    { name: "System", description: "Health and contract metadata." },
    { name: "Session", description: "Current authenticated session." },
    { name: "Companies", description: "Company 360 source data." },
    { name: "Parts", description: "Part Number 360 source data." },
    { name: "Stock", description: "Internal and external stock source data." },
    { name: "Audit", description: "Audit timeline events." }
  ],
  paths: {
    "/health": {
      get: {
        tags: ["System"],
        operationId: "getHealth",
        summary: "Health check",
        responses: {
          "200": {
            description: "API service is running.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/HealthResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/session": {
      get: {
        tags: ["Session"],
        operationId: "getSession",
        summary: "Get current session",
        responses: {
          "200": {
            description: "Current user session.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/SessionResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/companies": {
      get: {
        tags: ["Companies"],
        operationId: "listCompanies",
        summary: "List companies",
        responses: {
          "200": {
            description: "Company list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/CompanyListResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/parts": {
      get: {
        tags: ["Parts"],
        operationId: "listParts",
        summary: "List part numbers",
        responses: {
          "200": {
            description: "Part number list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/PartNumberListResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/stock/internal": {
      get: {
        tags: ["Stock"],
        operationId: "listInternalStock",
        summary: "List internal ACI770 stock",
        responses: {
          "200": {
            description: "Internal ACI770 stock list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/StockItemListResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/stock/external": {
      get: {
        tags: ["Stock"],
        operationId: "listExternalStock",
        summary: "List external stock",
        responses: {
          "200": {
            description: "External stock list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/StockItemListResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/audit": {
      get: {
        tags: ["Audit"],
        operationId: "listAuditEvents",
        summary: "List audit events",
        responses: {
          "200": {
            description: "Audit event list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/AuditEventListResponse" }
              }
            }
          }
        }
      }
    }
  },
  components: {
    schemas: {
      HealthResponse: {
        type: "object",
        required: ["status", "service"],
        properties: {
          status: { type: "string", enum: ["ok"] },
          service: { type: "string", const: "saas-aviation-api" }
        }
      },
      SessionUser: {
        type: "object",
        required: ["id", "email", "name", "roles", "tenantId"],
        properties: {
          id: { type: "string" },
          email: { type: "string", format: "email" },
          name: { type: "string" },
          roles: { type: "array", items: { type: "string" } },
          tenantId: { type: "string" }
        }
      },
      SessionResponse: {
        type: "object",
        required: ["user"],
        properties: {
          user: { $ref: "#/components/schemas/SessionUser" }
        }
      },
      Company: {
        type: "object",
        required: ["id", "legacyId", "tenantId", "name", "type", "tags", "riskLevel"],
        properties: {
          id: { type: "string" },
          legacyId: { oneOf: [{ type: "string" }, { type: "number" }] },
          tenantId: { type: "string" },
          name: { type: "string" },
          type: { type: "string", enum: ["customer", "supplier", "owner", "repair-vendor", "mixed"] },
          country: { type: "string" },
          city: { type: "string" },
          website: { type: "string" },
          primaryEmail: { type: "string" },
          tags: { type: "array", items: { type: "string" } },
          riskLevel: { type: "string", enum: ["normal", "watch", "blocked"] },
          lastActivityAt: { type: "string" }
        }
      },
      PartNumber: {
        type: "object",
        required: ["id", "legacyId", "pn", "description", "alternates"],
        properties: {
          id: { type: "string" },
          legacyId: { oneOf: [{ type: "string" }, { type: "number" }] },
          pn: { type: "string" },
          description: { type: "string" },
          ata: { type: "string" },
          ipc: { type: "string" },
          aircraft: { type: "array", items: { type: "string" } },
          manufacturer: { type: "string" },
          alternates: { type: "array", items: { type: "string" } },
          supersededBy: { type: "string" }
        }
      },
      StockItem: {
        type: "object",
        required: ["id", "legacyId", "tenantId", "source", "pn", "partId", "description", "qty", "status"],
        properties: {
          id: { type: "string" },
          legacyId: { oneOf: [{ type: "string" }, { type: "number" }] },
          tenantId: { type: "string" },
          source: { type: "string", enum: ["internal", "external"] },
          pn: { type: "string" },
          partId: { type: "string" },
          description: { type: "string" },
          serialNumber: { type: "string" },
          qty: { type: "number" },
          condition: { type: "string" },
          release: { type: "string" },
          status: {
            type: "string",
            enum: ["available", "reserved", "sold", "purchase-order", "work-order", "consignment", "quarantine", "repair", "exchange", "unknown"]
          },
          location: { type: "string" },
          ownerCompany: { type: "string" },
          supplierCompany: { type: "string" },
          tagInfoCompany: { type: "string" },
          traceabilityCompany: { type: "string" },
          price: { type: "number" },
          currency: { type: "string" },
          entryDate: { type: "string" },
          remarks: { type: "string" }
        }
      },
      AuditEvent: {
        type: "object",
        required: ["id", "tenantId", "actor", "action", "entityType", "entityId", "occurredAt", "summary"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          actor: { type: "string" },
          action: { type: "string" },
          entityType: { type: "string" },
          entityId: { type: "string" },
          rfqId: { type: "string" },
          occurredAt: { type: "string", format: "date-time" },
          summary: { type: "string" }
        }
      },
      CompanyListResponse: listResponse("#/components/schemas/Company"),
      PartNumberListResponse: listResponse("#/components/schemas/PartNumber"),
      StockItemListResponse: listResponse("#/components/schemas/StockItem"),
      AuditEventListResponse: listResponse("#/components/schemas/AuditEvent")
    }
  }
};
