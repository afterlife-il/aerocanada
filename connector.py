"""Read-only AeroCanada REST and MCP resource server."""
import asyncio
import logging
import os
import json
import re
from contextlib import asynccontextmanager, closing
from typing import Annotated
from urllib.parse import urlsplit

import jwt
import pymysql
from mcp.server.auth.provider import AccessToken
from mcp.server.auth.middleware.auth_context import get_access_token
from mcp.server.auth.settings import AuthSettings
from mcp.server.auth.routes import create_protected_resource_routes
from mcp.server.fastmcp import FastMCP
from mcp.server.transport_security import TransportSecuritySettings
from mcp.types import CallToolResult, TextContent
from pydantic import AnyHttpUrl, BaseModel, StringConstraints, ValidationError
from starlette.requests import Request
from starlette.responses import JSONResponse
from starlette.routing import Mount, Route
from starlette.applications import Starlette

from business_reads import BUSINESS_QUERIES, BUSINESS_SCOPES, RecordId

SCOPES = ("aerocanada.parts.read", "aerocanada.stock.read", "aerocanada.history.read")
SOURCES = ("aerocanada", "aerocanada_yoyamic")
LIMIT = 20
PN_PATTERN = r"[A-Za-z0-9][A-Za-z0-9 ._+/()#-]{0,79}"
PartNumber = Annotated[str, StringConstraints(pattern=f"^{PN_PATTERN}$", max_length=80)]
LOG = logging.getLogger("aerocanada_mcp")

PART = """
SELECT Fld_Part_ID AS part_id, Fld_Part_Nbr AS part_number,
       LEFT(Fld_Part_Desc, 200) AS description
FROM {db}.tbl_Parts WHERE Fld_Part_Nbr = %s ORDER BY Fld_Part_ID
"""
STOCK = """
SELECT s.{pk} AS stock_id, p.Fld_Part_Nbr AS part_number,
       s.Fld_Qty AS recorded_quantity, s.Fld_Physical_Stock AS physical_stock,
       s.Fld_Status_ID AS status_id, LEFT(t.Fld_Status_Text, 80) AS stock_status
FROM {db}.tbl_Parts p JOIN {db}.{table} s ON s.Fld_Part_ID = p.Fld_Part_ID
LEFT JOIN {db}.tbl_Status t ON t.Fld_Status_ID = s.Fld_Status_ID
WHERE p.Fld_Part_Nbr = %s ORDER BY s.{pk} DESC
"""
RFQ = """
SELECT r.ID AS line_id, r.Fld_RFQ_ID AS rfq_id, r.date AS event_date,
       c.Fld_Company_ID AS company_id, LEFT(c.Fld_Company_Name, 160) AS company
FROM {db}.tbl_RFQ_1 r LEFT JOIN {db}.tbl_Parts p ON p.Fld_Part_ID = r.Fld_Part_ID
LEFT JOIN {db}.tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
WHERE p.Fld_Part_Nbr = %s OR r.pn_rfq = %s ORDER BY r.ID DESC
"""
SUPPLIER = """
SELECT q.ID AS quote_id, q.Fld_RFQ_ID AS rfq_id, q.Fld_Current_Date AS event_date,
       c.Fld_Company_ID AS company_id, LEFT(c.Fld_Company_Name, 160) AS company
FROM {db}.tbl_Parts p JOIN {db}.tbl_RFQ_2 q ON q.Fld_Part_ID = p.Fld_Part_ID
LEFT JOIN {db}.tb_company c ON c.Fld_Company_ID = q.Fld_Supplier_ID
WHERE p.Fld_Part_Nbr = %s ORDER BY q.ID DESC
"""
CUSTOMER = """
SELECT q.ID AS quote_id, q.Fld_RFQ_ID AS rfq_id, q.Fld_Quote_Date AS event_date,
       c.Fld_Company_ID AS company_id, LEFT(c.Fld_Company_Name, 160) AS company
FROM {db}.tbl_Parts p JOIN {db}.tbl_RFQ_3 q ON q.Fld_Part_Id = p.Fld_Part_ID
LEFT JOIN {db}.tbl_RFQ_1 r ON r.ID = q.id_tbl_rfq1 AND r.Fld_RFQ_ID = q.Fld_RFQ_ID
LEFT JOIN {db}.tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
WHERE p.Fld_Part_Nbr = %s ORDER BY q.ID DESC
"""
QUERIES = {
    "search_part": [("parts", PART, 1)],
    "get_stock": [
        ("internal_stock", STOCK.replace("{table}", "tbl_Stock").replace("{pk}", "Fld_Stock_ID"), 1),
        ("external_stock", STOCK.replace("{table}", "tbl_Stock_external").replace("{pk}", "Fld_Stock_externe_ID"), 1),
    ],
    "get_part_history": [
        ("rfq", RFQ, 2), ("supplier_quote", SUPPLIER, 1), ("customer_quote", CUSTOMER, 1)
    ],
}
TOOL_SCOPES = dict(zip(QUERIES, SCOPES))


class OperationUnavailable(Exception):
    pass


class IdentityProbeDenied(Exception):
    pass


class Lookup(BaseModel, extra="forbid"):
    part_number: PartNumber


def db_factory():
    user = os.environ["AEROCANADA_DB_USER"]
    if not user.strip() or user.strip().lower() == "root":
        raise ValueError("A dedicated MariaDB reader is required")
    socket = os.environ["AEROCANADA_DB_SOCKET"]
    password_file = os.environ["AEROCANADA_DB_PASSWORD_FILE"]
    with open(password_file, encoding="utf-8") as secret:
        password = secret.read().rstrip("\r\n")
    if not password:
        raise ValueError("MariaDB password file is empty")

    def connect():
        return pymysql.connect(
            unix_socket=socket, user=user, password=password, charset="utf8mb4",
            connect_timeout=2, read_timeout=4, write_timeout=4, autocommit=False,
            cursorclass=pymysql.cursors.DictCursor,
        )

    return connect


def lookup(connect, operation, part_number):
    if not re.fullmatch(PN_PATTERN, part_number):
        raise ValueError("Invalid part_number")
    return read_records(connect, operation, part_number, QUERIES[operation])


def read_records(connect, operation, value, queries):
    rows = []
    truncated = False
    try:
        with closing(connect()) as conn, conn.cursor() as cursor:
            cursor.execute("START TRANSACTION READ ONLY")
            try:
                for db in SOURCES:
                    for kind, query, arity in queries:
                        # Identifiers come exclusively from static constants.
                        sql = "SET STATEMENT max_statement_time=1 FOR " + query.format(db=db) + "LIMIT %s"
                        cursor.execute(sql, (value,) * arity + (LIMIT + 1,))
                        batch = cursor.fetchall()
                        truncated |= len(batch) > LIMIT
                        for row in batch[:LIMIT]:
                            rows.append({"source": db, "kind": kind, **row})
            finally:
                conn.rollback()
    except (pymysql.MySQLError, OSError):
        LOG.warning("operation=%s outcome=database_error", operation)
        raise OperationUnavailable("Consultation temporairement indisponible") from None
    LOG.info("operation=%s outcome=%s", operation, "ok" if rows else "empty")
    if not rows:
        return "Aucun résultat"
    for row in rows:
        for key, value in row.items():
            if value is not None and not isinstance(value, (str, int, float, bool)):
                row[key] = str(value)
    return {"items": rows, "truncated": truncated, "limit_per_source_and_kind": LIMIT}


class Verifier:
    def __init__(self, issuer, audience, jwks):
        self.issuer = issuer
        self.audience = audience
        self.jwks = jwks

    async def verify_token(self, token: str) -> AccessToken | None:
        if len(token) > 8192:
            return None
        try:
            if jwt.get_unverified_header(token).get("alg") != "RS256":
                return None
            key = await asyncio.to_thread(self.jwks.get_signing_key_from_jwt, token)
            claims = jwt.decode(
                token, key.key, algorithms=["RS256"], issuer=self.issuer, audience=self.audience,
                options={"require": ["exp", "iss", "aud", "sub"]},
            )
        except (jwt.PyJWTError, OSError, ValueError, TypeError, OverflowError):
            return None
        scope = claims.get("scope")
        subject = claims.get("sub")
        if not isinstance(scope, str) or not isinstance(subject, str) or not subject:
            return None
        return AccessToken(
            token=token, client_id=subject, subject=subject,
            scopes=[item for item in scope.split() if item in SCOPES or item in BUSINESS_SCOPES.values()], expires_at=int(claims["exp"]),
        )


def verified_subject(token):
    """Return only the subject populated after this server's JWT verification."""
    if token is None or TOOL_SCOPES["search_part"] not in token.scopes:
        raise IdentityProbeDenied
    subject = token.subject
    if not isinstance(subject, str) or not subject or subject != token.client_id:
        raise IdentityProbeDenied
    return subject


async def invoke(connect, operation, part_number):
    return await asyncio.to_thread(lookup, connect, operation, part_number)


def build_app(connect, verifier, issuer, resource, allowed_hosts, *, business_reads=False):
    exposed_scopes = {**TOOL_SCOPES, **BUSINESS_SCOPES} if business_reads else TOOL_SCOPES
    mcp = FastMCP(
        "AeroCanada", stateless_http=True, json_response=True, token_verifier=verifier,
        auth=AuthSettings(issuer_url=AnyHttpUrl(issuer), resource_server_url=AnyHttpUrl(resource),
                          required_scopes=[], validate_token_resource=False),
        transport_security=TransportSecuritySettings(allowed_hosts=allowed_hosts, allowed_origins=[]),
    )

    metadata_url = f"{resource.removesuffix('/mcp')}/.well-known/oauth-protected-resource/mcp"
    capacity = asyncio.Semaphore(4)

    @mcp.tool(
        name="verify_delegated_sub",
        annotations={"readOnlyHint": True, "openWorldHint": False},
        meta={"securitySchemes": [{"type": "oauth2", "scopes": [TOOL_SCOPES["search_part"]]}]},
        structured_output=False,
    )
    async def verify_delegated_sub():
        """Temporary identity check: validate the current OAuth token and return only its signed sub claim."""
        try:
            return verified_subject(get_access_token())
        except IdentityProbeDenied:
            return CallToolResult(
                content=[TextContent(type="text", text="Accès refusé")],
                isError=True,
            )

    async def query(operation, value):
        try:
            await asyncio.wait_for(capacity.acquire(), timeout=0.25)
        except asyncio.TimeoutError:
            LOG.warning("operation=%s outcome=busy", operation)
            raise OperationUnavailable("Consultation temporairement indisponible") from None
        try:
            if operation in BUSINESS_QUERIES:
                category, sql = BUSINESS_QUERIES[operation]
                return await asyncio.to_thread(read_records, connect, operation, value, [(category, sql, 1)])
            return await invoke(connect, operation, value)
        finally:
            capacity.release()

    async def call(operation, value):
        token = get_access_token()
        if token is None or exposed_scopes[operation] not in token.scopes:
            LOG.warning("operation=%s outcome=denied", operation)
            scope = exposed_scopes[operation]
            challenge = (
                f'Bearer resource_metadata="{metadata_url}", scope="{scope}", '
                'error="insufficient_scope", error_description="Required AeroCanada scope missing"'
            )
            return CallToolResult(
                content=[TextContent(type="text", text="Accès refusé")],
                isError=True, _meta={"mcp/www_authenticate": [challenge]},
            )
        return await query(operation, value)

    @mcp.tool(
        annotations={"readOnlyHint": True, "openWorldHint": False},
        meta={"securitySchemes": [{"type": "oauth2", "scopes": [TOOL_SCOPES["search_part"]]}]},
        structured_output=False,
    )
    async def search_part(part_number: PartNumber):
        """Recherche exacte d’une référence de pièce dans les deux catalogues."""
        return await call("search_part", part_number)

    @mcp.tool(
        annotations={"readOnlyHint": True, "openWorldHint": False},
        meta={"securitySchemes": [{"type": "oauth2", "scopes": [TOOL_SCOPES["get_stock"]]}]},
        structured_output=False,
    )
    async def get_stock(part_number: PartNumber):
        """Consulte les quantités et statuts enregistrés, sans promesse de disponibilité."""
        return await call("get_stock", part_number)

    @mcp.tool(
        annotations={"readOnlyHint": True, "openWorldHint": False},
        meta={"securitySchemes": [{"type": "oauth2", "scopes": [TOOL_SCOPES["get_part_history"]]}]},
        structured_output=False,
    )
    async def get_part_history(part_number: PartNumber):
        """Consulte les RFQ, devis et noms de sociétés associés, sans prix ni contacts."""
        return await call("get_part_history", part_number)

    if business_reads:
        def business_tool(name, description):
            async def read(record_id: RecordId):
                return await call(name, record_id)

            mcp.add_tool(
                read, name=name, description=description,
                annotations={"readOnlyHint": True, "openWorldHint": False},
                meta={"securitySchemes": [{"type": "oauth2", "scopes": [exposed_scopes[name]]}]},
                structured_output=False,
            )

        business_tool("get_contacts", "Liste les noms des contacts actifs de la société record_id.")
        business_tool("get_customer", "Consulte la société record_id si elle a une demande RFQ client.")
        business_tool("get_supplier", "Consulte la société record_id si elle a un devis fournisseur.")
        business_tool("get_rfq", "Consulte les lignes de la demande RFQ record_id.")
        business_tool("get_supplier_quote", "Consulte le devis fournisseur record_id, sans prix.")

    async def api(request: Request):
        operation = request.path_params["operation"]
        if operation not in TOOL_SCOPES:
            return JSONResponse({"error": "Outil inconnu"}, status_code=404)
        scheme, _, value = request.headers.get("authorization", "").partition(" ")
        token = await verifier.verify_token(value) if scheme.lower() == "bearer" and value else None
        if token is None:
            return JSONResponse({"error": "Authentification requise"}, status_code=401,
                                headers={"WWW-Authenticate": "Bearer"})
        if TOOL_SCOPES[operation] not in token.scopes:
            LOG.warning("operation=%s outcome=denied", operation)
            return JSONResponse({"error": "Accès refusé"}, status_code=403)
        body = bytearray()
        async for chunk in request.stream():
            if len(body) + len(chunk) > 1024:
                return JSONResponse({"error": "Corps trop volumineux"}, status_code=413)
            body.extend(chunk)
        try:
            part = Lookup.model_validate(json.loads(body))
            if not re.fullmatch(PN_PATTERN, part.part_number):
                raise ValueError("Invalid part")
        except (ValidationError, ValueError):
            return JSONResponse({"error": "part_number invalide"}, status_code=400)
        try:
            result = await query(operation, part.part_number)
        except OperationUnavailable:
            return JSONResponse({"error": "Consultation temporairement indisponible"}, status_code=503)
        return JSONResponse(result, headers={"Cache-Control": "no-store"})

    mcp_app = mcp.streamable_http_app()

    @asynccontextmanager
    async def lifespan(_app):
        async with mcp.session_manager.run():
            yield

    metadata_routes = create_protected_resource_routes(
        AnyHttpUrl(resource), [AnyHttpUrl(issuer)], scopes_supported=list(exposed_scopes.values()),
    )
    routes = [Route("/v1/{operation}", api, methods=["POST"]), *metadata_routes, Mount("/", mcp_app)]
    return Starlette(routes=routes, lifespan=lifespan)


def locked_app():
    async def unavailable(_request):
        return JSONResponse(
            {"error": "Activation OAuth en attente"},
            status_code=503, headers={"Cache-Control": "no-store"},
        )

    return Starlette(routes=[
        Route("/mcp", unavailable, methods=["GET", "POST", "DELETE"]),
        Route("/.well-known/oauth-protected-resource/mcp", unavailable, methods=["GET"]),
    ])


def create_app():
    if os.environ.get("AEROCANADA_ACTIVATION_LOCK") == "1":
        return locked_app()

    issuer = os.environ["AEROCANADA_OAUTH_ISSUER"]
    resource = os.environ["AEROCANADA_OAUTH_RESOURCE"]
    jwks = os.environ["AEROCANADA_OAUTH_JWKS"]
    for value in (issuer, resource, jwks):
        parsed = urlsplit(value)
        if parsed.scheme != "https" or not parsed.hostname or parsed.username or parsed.password or parsed.fragment:
            raise ValueError("OAuth endpoints must use HTTPS")
    if urlsplit(resource).path != "/mcp" or urlsplit(resource).query:
        raise ValueError("OAuth resource must end at /mcp")
    allowed_hosts = [urlsplit(resource).netloc, "127.0.0.1:*", "localhost:*"]
    logging.basicConfig(level=logging.INFO)
    # SDK errors may include tool arguments. Emit only the application audit records.
    sdk_log = logging.getLogger("mcp")
    sdk_log.addHandler(logging.NullHandler())
    sdk_log.propagate = False
    return build_app(
        db_factory(), Verifier(issuer, resource, jwt.PyJWKClient(jwks, timeout=3)),
        issuer, resource, allowed_hosts,
        business_reads=os.getenv("AEROCANADA_ENABLE_BUSINESS_READS") == "1",
    )
