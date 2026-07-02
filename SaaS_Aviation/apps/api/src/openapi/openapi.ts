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
    { name: "Session", description: "Password auth, session lifecycle, and current tenant context." },
    { name: "Companies", description: "Company 360 source data." },
    { name: "Parts", description: "Part Number 360 source data." },
    { name: "Stock", description: "Internal and external stock source data." },
    { name: "Inventory", description: "Tenant-scoped stock and company inventory read models." },
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
    "/v1/auth/login": {
      post: {
        tags: ["Session"],
        operationId: "loginWithPassword",
        summary: "Create an email/password session",
        requestBody: {
          required: true,
          content: {
            "application/json": {
              schema: { $ref: "#/components/schemas/LoginRequest" }
            }
          }
        },
        responses: {
          "200": {
            description: "Session created.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/AuthSessionResponse" }
              }
            }
          },
          "401": {
            description: "Invalid credentials.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/ErrorResponse" }
              }
            }
          }
        }
      }
    },
    "/v1/auth/logout": {
      post: {
        tags: ["Session"],
        operationId: "logout",
        summary: "Revoke current bearer session",
        responses: {
          "204": {
            description: "Session revoked."
          }
        }
      }
    },
    "/v1/companies": {
      get: {
        tags: ["Companies"],
        operationId: "listCompanies",
        summary: "List companies",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "Company list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/CompanyListResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    },
    "/v1/parts": {
      get: {
        tags: ["Parts"],
        operationId: "listParts",
        summary: "List part numbers",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "Part number list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/PartNumberListResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    },
    "/v1/parts/{id}/360": {
      get: {
        tags: ["Parts"],
        operationId: "getPart360",
        summary: "Get tenant-scoped Part 360 read model",
        security: [{ bearerAuth: [] }],
        parameters: [{ name: "id", in: "path", required: true, schema: { type: "string" } }],
        responses: {
          "200": {
            description: "Part 360 read model.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/Part360Response" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" },
          "404": { $ref: "#/components/responses/NotFound" }
        }
      }
    },
    "/v1/stock/internal": {
      get: {
        tags: ["Stock"],
        operationId: "listInternalStock",
        summary: "List internal ACI770 stock",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "Internal ACI770 stock list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/StockItemListResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    },
    "/v1/stock/external": {
      get: {
        tags: ["Stock"],
        operationId: "listExternalStock",
        summary: "List external stock",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "External stock list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/StockItemListResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    },
    "/v1/stock/{id}/360": {
      get: {
        tags: ["Stock"],
        operationId: "getStock360",
        summary: "Get tenant-scoped Stock 360 read model",
        security: [{ bearerAuth: [] }],
        parameters: [{ name: "id", in: "path", required: true, schema: { type: "string" } }],
        responses: {
          "200": {
            description: "Stock 360 read model.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/Stock360Response" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" },
          "404": { $ref: "#/components/responses/NotFound" }
        }
      }
    },
    "/v1/company-inventory": {
      get: {
        tags: ["Inventory"],
        operationId: "getCompanyInventory",
        summary: "Get tenant-scoped company inventory read model",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "Company inventory read model.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/CompanyInventoryResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    },
    "/v1/audit": {
      get: {
        tags: ["Audit"],
        operationId: "listAuditEvents",
        summary: "List audit events",
        security: [{ bearerAuth: [] }],
        responses: {
          "200": {
            description: "Audit event list.",
            content: {
              "application/json": {
                schema: { $ref: "#/components/schemas/AuditEventListResponse" }
              }
            }
          },
          "401": { $ref: "#/components/responses/Unauthorized" }
        }
      }
    }
  },
  components: {
    securitySchemes: {
      bearerAuth: {
        type: "http",
        scheme: "bearer"
      }
    },
    responses: {
      Unauthorized: {
        description: "Missing, expired, or invalid bearer session.",
        content: {
          "application/json": {
            schema: { $ref: "#/components/schemas/ErrorResponse" }
          }
        }
      },
      NotFound: {
        description: "Tenant-scoped record was not found.",
        content: {
          "application/json": {
            schema: { $ref: "#/components/schemas/ErrorResponse" }
          }
        }
      }
    },
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
        required: ["id", "email", "name", "roles", "permissions", "tenantId", "mfaEnabled", "authProviders", "status"],
        properties: {
          id: { type: "string" },
          email: { type: "string", format: "email" },
          name: { type: "string" },
          roles: { type: "array", items: { type: "string" } },
          permissions: { type: "array", items: { type: "string" } },
          tenantId: { type: "string" },
          status: { type: "string", enum: ["active", "invited", "disabled"] },
          mfaEnabled: { type: "boolean" },
          authProviders: { type: "array", items: { type: "string", enum: ["password", "google", "linkedin", "microsoft", "apple"] } },
          createdAt: { type: "string", format: "date-time" }
        }
      },
      Tenant: {
        type: "object",
        required: ["id", "name", "code", "verifiedDomains", "status", "primaryCompanyId"],
        properties: {
          id: { type: "string" },
          name: { type: "string" },
          code: { type: "string" },
          verifiedDomains: { type: "array", items: { type: "string" } },
          status: { type: "string", enum: ["active", "suspended"] },
          primaryCompanyId: { type: "string" }
        }
      },
      SessionResponse: {
        type: "object",
        required: ["session"],
        properties: {
          session: {
            oneOf: [{ $ref: "#/components/schemas/AuthSession" }, { type: "null" }]
          }
        }
      },
      LoginRequest: {
        type: "object",
        required: ["email", "password"],
        properties: {
          email: { type: "string", format: "email" },
          password: { type: "string", minLength: 8 }
        }
      },
      AuthSession: {
        type: "object",
        required: ["token", "user", "tenant", "expiresAt"],
        properties: {
          token: { type: "string" },
          user: { $ref: "#/components/schemas/SessionUser" },
          tenant: { $ref: "#/components/schemas/Tenant" },
          expiresAt: { type: "string", format: "date-time" }
        }
      },
      AuthSessionResponse: {
        type: "object",
        required: ["session"],
        properties: {
          session: { $ref: "#/components/schemas/AuthSession" }
        }
      },
      ErrorResponse: {
        type: "object",
        required: ["error"],
        properties: {
          error: { type: "string" }
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
        required: ["id", "legacyId", "tenantId", "pn", "description", "alternates"],
        properties: {
          id: { type: "string" },
          legacyId: { oneOf: [{ type: "string" }, { type: "number" }] },
          tenantId: { type: "string" },
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
      WorkflowBoundaryAction: {
        type: "object",
        required: ["id", "label", "tenantId", "entityType", "entityId", "mode", "persistence", "requiredData", "contextChecks", "futureOwner", "note"],
        properties: {
          id: { type: "string" },
          label: { type: "string" },
          tenantId: { type: "string" },
          entityType: { type: "string", enum: ["part", "stock", "company-inventory"] },
          entityId: { type: "string" },
          mode: { type: "string", const: "boundary" },
          persistence: { type: "string", const: "none" },
          requiredData: { type: "array", items: { type: "string" } },
          contextChecks: { type: "array", items: { type: "string" } },
          futureOwner: { type: "string" },
          note: { type: "string" }
        }
      },
      StockAvailabilitySummary: {
        type: "object",
        required: ["internalUnits", "externalUnits", "internalLines", "externalLines", "availableUnits", "reservedUnits", "zeroQtyRows", "totalValue", "currency"],
        properties: {
          internalUnits: { type: "number" },
          externalUnits: { type: "number" },
          internalLines: { type: "number" },
          externalLines: { type: "number" },
          availableUnits: { type: "number" },
          reservedUnits: { type: "number" },
          zeroQtyRows: { type: "number" },
          totalValue: { type: "number" },
          currency: { type: "string" }
        }
      },
      MarginSummary: {
        type: "object",
        required: ["quotedValue", "quotedCost", "grossMargin", "marginPct", "currency"],
        properties: {
          quotedValue: { type: "number" },
          quotedCost: { type: "number" },
          grossMargin: { type: "number" },
          marginPct: { type: "number" },
          currency: { type: "string" }
        }
      },
      Part360ReadModel: {
        type: "object",
        required: ["tenantId", "tenantCode", "part", "stockAvailability", "internalStock", "externalStock", "rfqs", "supplierQuotes", "customerQuotes", "purchaseHistory", "salesHistory", "serviceHistory", "certificates", "documents", "traceability", "margin", "quickActions"],
        properties: {
          tenantId: { type: "string" },
          tenantCode: { type: "string" },
          part: { $ref: "#/components/schemas/PartNumber" },
          stockAvailability: { $ref: "#/components/schemas/StockAvailabilitySummary" },
          internalStock: { type: "array", items: { $ref: "#/components/schemas/StockItem" } },
          externalStock: { type: "array", items: { $ref: "#/components/schemas/StockItem" } },
          rfqs: { type: "array", items: { $ref: "#/components/schemas/RfqSummary" } },
          supplierQuotes: { type: "array", items: { $ref: "#/components/schemas/SupplierQuoteSummary" } },
          customerQuotes: { type: "array", items: { $ref: "#/components/schemas/QuoteSummary" } },
          purchaseHistory: { type: "array", items: { $ref: "#/components/schemas/OrderSummary" } },
          salesHistory: { type: "array", items: { $ref: "#/components/schemas/OrderSummary" } },
          serviceHistory: { type: "array", items: { $ref: "#/components/schemas/ServiceWorkflowSummary" } },
          certificates: { type: "array", items: { $ref: "#/components/schemas/DocumentAlert" } },
          documents: { type: "array", items: { $ref: "#/components/schemas/DocumentAlert" } },
          traceability: { type: "array", items: { $ref: "#/components/schemas/AuditEvent" } },
          margin: { $ref: "#/components/schemas/MarginSummary" },
          quickActions: { type: "array", items: { $ref: "#/components/schemas/WorkflowBoundaryAction" } }
        }
      },
      Stock360ReadModel: {
        type: "object",
        required: ["tenantId", "tenantCode", "stock", "part", "rfqs", "supplierQuotes", "customerQuotes", "purchaseOrders", "salesOrders", "serviceHistory", "certificates", "documents", "lifecycle", "margin", "quickActions"],
        properties: {
          tenantId: { type: "string" },
          tenantCode: { type: "string" },
          stock: { $ref: "#/components/schemas/StockItem" },
          part: { oneOf: [{ $ref: "#/components/schemas/PartNumber" }, { type: "null" }] },
          ownerCompany: { oneOf: [{ $ref: "#/components/schemas/Company" }, { type: "null" }] },
          supplierCompany: { oneOf: [{ $ref: "#/components/schemas/Company" }, { type: "null" }] },
          tagInfoCompany: { oneOf: [{ $ref: "#/components/schemas/Company" }, { type: "null" }] },
          traceabilityCompany: { oneOf: [{ $ref: "#/components/schemas/Company" }, { type: "null" }] },
          rfqs: { type: "array", items: { $ref: "#/components/schemas/RfqSummary" } },
          supplierQuotes: { type: "array", items: { $ref: "#/components/schemas/SupplierQuoteSummary" } },
          customerQuotes: { type: "array", items: { $ref: "#/components/schemas/QuoteSummary" } },
          purchaseOrders: { type: "array", items: { $ref: "#/components/schemas/OrderSummary" } },
          salesOrders: { type: "array", items: { $ref: "#/components/schemas/OrderSummary" } },
          serviceHistory: { type: "array", items: { $ref: "#/components/schemas/ServiceWorkflowSummary" } },
          certificates: { type: "array", items: { $ref: "#/components/schemas/DocumentAlert" } },
          documents: { type: "array", items: { $ref: "#/components/schemas/DocumentAlert" } },
          lifecycle: { type: "array", items: { $ref: "#/components/schemas/AuditEvent" } },
          margin: { $ref: "#/components/schemas/MarginSummary" },
          quickActions: { type: "array", items: { $ref: "#/components/schemas/WorkflowBoundaryAction" } }
        }
      },
      CompanyInventoryRow: {
        type: "object",
        required: ["tenantId", "companyId", "companyName", "companyType", "internalUnits", "externalUnits", "zeroQtyRows", "stockValue", "currency", "stockLines", "documents", "linkedRfqs"],
        properties: {
          tenantId: { type: "string" },
          companyId: { type: "string" },
          companyName: { type: "string" },
          companyType: { type: "string" },
          internalUnits: { type: "number" },
          externalUnits: { type: "number" },
          zeroQtyRows: { type: "number" },
          stockValue: { type: "number" },
          currency: { type: "string" },
          stockLines: { type: "array", items: { $ref: "#/components/schemas/StockItem" } },
          documents: { type: "array", items: { $ref: "#/components/schemas/DocumentAlert" } },
          linkedRfqs: { type: "array", items: { $ref: "#/components/schemas/RfqSummary" } }
        }
      },
      CompanyInventoryReadModel: {
        type: "object",
        required: ["tenantId", "tenantCode", "rows", "totals", "quickActions"],
        properties: {
          tenantId: { type: "string" },
          tenantCode: { type: "string" },
          rows: { type: "array", items: { $ref: "#/components/schemas/CompanyInventoryRow" } },
          totals: {
            type: "object",
            required: ["internalUnits", "externalUnits", "stockValue", "zeroQtyRows", "currency"],
            properties: {
              internalUnits: { type: "number" },
              externalUnits: { type: "number" },
              stockValue: { type: "number" },
              zeroQtyRows: { type: "number" },
              currency: { type: "string" }
            }
          },
          quickActions: { type: "array", items: { $ref: "#/components/schemas/WorkflowBoundaryAction" } }
        }
      },
      RfqSummary: {
        type: "object",
        required: ["id", "tenantId", "rfqId", "customerName", "partNumber", "qty", "status", "priority", "createdAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          rfqId: { type: "string" },
          customerName: { type: "string" },
          partNumber: { type: "string" },
          qty: { type: "number" },
          status: { type: "string" },
          priority: { type: "string" },
          createdAt: { type: "string" }
        }
      },
      QuoteSummary: {
        type: "object",
        required: ["id", "tenantId", "quoteNumber", "rfqId", "customerName", "partNumber", "status", "value", "cost", "currency", "marginPct", "dueAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          quoteNumber: { type: "string" },
          rfqId: { type: "string" },
          customerName: { type: "string" },
          partNumber: { type: "string" },
          status: { type: "string" },
          value: { type: "number" },
          cost: { type: "number" },
          currency: { type: "string" },
          marginPct: { type: "number" },
          dueAt: { type: "string" }
        }
      },
      SupplierQuoteSummary: {
        type: "object",
        required: ["id", "tenantId", "rfqId", "supplierName", "partNumber", "qty", "status", "dueAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          rfqId: { type: "string" },
          supplierName: { type: "string" },
          partNumber: { type: "string" },
          qty: { type: "number" },
          status: { type: "string" },
          dueAt: { type: "string" }
        }
      },
      OrderSummary: {
        type: "object",
        required: ["id", "tenantId", "orderNumber", "kind", "companyName", "status", "value", "currency", "dueAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          orderNumber: { type: "string" },
          kind: { type: "string" },
          companyName: { type: "string" },
          rfqId: { type: "string" },
          status: { type: "string" },
          value: { type: "number" },
          currency: { type: "string" },
          dueAt: { type: "string" }
        }
      },
      ServiceWorkflowSummary: {
        type: "object",
        required: ["id", "tenantId", "kind", "reference", "companyName", "partNumber", "status", "dueAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          kind: { type: "string" },
          reference: { type: "string" },
          companyName: { type: "string" },
          partNumber: { type: "string" },
          status: { type: "string" },
          dueAt: { type: "string" }
        }
      },
      DocumentAlert: {
        type: "object",
        required: ["id", "tenantId", "documentType", "entityType", "entityId", "status", "dueAt"],
        properties: {
          id: { type: "string" },
          tenantId: { type: "string" },
          documentType: { type: "string" },
          entityType: { type: "string" },
          entityId: { type: "string" },
          status: { type: "string" },
          dueAt: { type: "string" }
        }
      },
      CompanyListResponse: listResponse("#/components/schemas/Company"),
      PartNumberListResponse: listResponse("#/components/schemas/PartNumber"),
      StockItemListResponse: listResponse("#/components/schemas/StockItem"),
      Part360Response: {
        type: "object",
        required: ["data"],
        properties: { data: { $ref: "#/components/schemas/Part360ReadModel" } }
      },
      Stock360Response: {
        type: "object",
        required: ["data"],
        properties: { data: { $ref: "#/components/schemas/Stock360ReadModel" } }
      },
      CompanyInventoryResponse: {
        type: "object",
        required: ["data"],
        properties: { data: { $ref: "#/components/schemas/CompanyInventoryReadModel" } }
      },
      AuditEventListResponse: listResponse("#/components/schemas/AuditEvent")
    }
  }
};
