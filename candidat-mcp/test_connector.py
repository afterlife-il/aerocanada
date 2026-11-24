import os
from contextlib import asynccontextmanager
import time
import unittest
from types import SimpleNamespace
from unittest.mock import patch

import jwt
import pymysql
from cryptography.hazmat.primitives.asymmetric import rsa
from mcp.server.auth.provider import AccessToken
from httpx import ASGITransport, AsyncClient

from business_reads import BUSINESS_SCOPES
from connector import TOOL_SCOPES, Verifier, build_app, locked_app
from preflight import check


SCOPES = TOOL_SCOPES | BUSINESS_SCOPES
ISSUER = "https://issuer.example"
RESOURCE = "https://erp.example/mcp"
HEADERS = {"accept": "application/json, text/event-stream"}
VALUES = {name: {"record_id": 7} for name in BUSINESS_SCOPES}
VALUES.update({name: {"part_number": "SYNTH-PN"} for name in TOOL_SCOPES})


class FakeDatabase:
    def __init__(self):
        self.calls = []
        self.fail = False
        self.batch = [{"synthetic_id": 7}]
        self.rollbacks = 0

    def connect(self):
        return self

    def close(self):
        pass

    def rollback(self):
        self.rollbacks += 1

    def cursor(self):
        return self

    def __enter__(self):
        return self

    def __exit__(self, *exc):
        pass

    def execute(self, sql, args=()):
        self.calls.append((sql, args))
        if args and self.fail:
            raise pymysql.OperationalError("synthetic error")

    def fetchall(self):
        return self.batch


class FakeVerifier:
    async def verify_token(self, token):
        if token not in SCOPES:
            return None
        return AccessToken(token=token, client_id="synthetic-user", scopes=[SCOPES[token]])


@asynccontextmanager
async def asgi_client(app):
    async with app.router.lifespan_context(app):
        async with AsyncClient(transport=ASGITransport(app=app), base_url=RESOURCE.removesuffix("/mcp")) as client:
            yield client


async def run_inline(function, /, *args, **kwargs):
    """Run the synthetic dependencies without crossing sandboxed worker threads."""
    return function(*args, **kwargs)


class ConnectorTests(unittest.IsolatedAsyncioTestCase):
    async def asyncSetUp(self):
        runner = patch("connector.asyncio.to_thread", run_inline)
        runner.start()
        self.addCleanup(runner.stop)
        self.db = FakeDatabase()
        context = asgi_client(self.app())
        self.client = await context.__aenter__()
        self.addAsyncCleanup(context.__aexit__, None, None, None)

    def app(self, business_reads=True):
        return build_app(self.db.connect, FakeVerifier(), ISSUER, RESOURCE,
                         ["erp.example"], business_reads=business_reads)

    async def request(self, method, params=None, token="search_part", client=None):
        payload = {"jsonrpc": "2.0", "id": 1, "method": method}
        if params is not None:
            payload["params"] = params
        headers = HEADERS | ({"authorization": f"Bearer {token}"} if token else {})
        return await (client or self.client).post("/mcp", json=payload, headers=headers)

    async def call(self, name, args, **kwargs):
        return await self.request("tools/call", {"name": name, "arguments": args}, **kwargs)

    async def test_registration_and_read_annotations(self):
        for enabled, expected in [(False, TOOL_SCOPES), (True, SCOPES)]:
            with self.subTest(enabled=enabled):
                async with asgi_client(self.app(enabled)) as client:
                    tools = (await self.request("tools/list", client=client)).json()["result"]["tools"]
                    self.assertEqual({tool["name"] for tool in tools}, set(expected))
                    for tool in tools:
                        self.assertTrue(tool["annotations"]["readOnlyHint"])
                        self.assertEqual(tool["_meta"]["securitySchemes"][0]["scopes"],
                                         [expected[tool["name"]]])

    async def test_individual_tools_and_parameters(self):
        for name, args in VALUES.items():
            with self.subTest(name=name):
                before = len(self.db.calls)
                response = await self.call(name, args, token=name)
                result = response.json()["result"]
                self.assertFalse(result.get("isError", False), result)
                self.assertEqual(self.db.calls[before], ("START TRANSACTION READ ONLY", ()))
                for sql, params in self.db.calls[before + 1:]:
                    self.assertTrue(sql.startswith("SET STATEMENT max_statement_time=1 FOR "))
                    raw_value = next(iter(args.values()))
                    expected = str(raw_value) if name == "get_rfq" else raw_value
                    self.assertEqual(params, (expected,) * (len(params) - 1) + (21,))
        self.assertEqual(self.db.rollbacks, len(VALUES))

    async def test_missing_and_mismatched_scope(self):
        for name, args in VALUES.items():
            with self.subTest(name=name):
                self.assertEqual((await self.call(name, args, token=None)).status_code, 401)
                wrong_token = "get_stock" if name == "search_part" else "search_part"
                result = (await self.call(name, args, token=wrong_token)).json()["result"]
                self.assertTrue(result["isError"])
                self.assertIn(SCOPES[name], result["_meta"]["mcp/www_authenticate"][0])
        self.assertEqual(self.db.calls, [])

    async def test_formatted_rfq_reference(self):
        reference = "2026-11-23-120000"
        result = (await self.call("get_rfq", {"record_id": reference}, token="get_rfq")).json()["result"]
        self.assertFalse(result.get("isError", False))
        self.assertEqual(self.db.calls[-1][1], (reference, 21))

    async def test_invalid_arguments_skip_database(self):
        for name, args in [
            ("get_contacts", {"record_id": "7"}),
            ("get_contacts", {"record_id": 0}),
            ("get_rfq", {"record_id": ""}),
            ("get_rfq", {"record_id": True}),
            ("get_rfq", {"record_id": "x" * 81}),
            ("search_part", {"part_number": "!"}),
        ]:
            with self.subTest(name=name, args=args):
                result = (await self.call(name, args, token=name)).json()["result"]
                self.assertTrue(result["isError"])
        self.assertEqual(self.db.calls, [])

    async def test_database_error_and_cleanup(self):
        self.db.fail = True
        result = (await self.call("search_part", VALUES["search_part"])).json()["result"]
        self.assertTrue(result["isError"])
        self.assertNotIn("synthetic error", str(result))
        self.assertEqual(self.db.rollbacks, 1)

    async def test_truncation_and_empty_response(self):
        self.db.batch = [{"synthetic_id": number} for number in range(21)]
        result = (await self.call("search_part", VALUES["search_part"])).json()["result"]
        self.assertIn('"truncated": true', result["content"][0]["text"].lower())
        self.db.batch = []
        result = (await self.call("search_part", VALUES["search_part"])).json()["result"]
        self.assertEqual(result["content"][0]["text"], "Aucun résultat")

    async def test_resource_metadata_scopes(self):
        response = await self.client.get("/.well-known/oauth-protected-resource/mcp")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["resource"], RESOURCE)
        self.assertEqual(set(response.json()["scopes_supported"]), set(SCOPES.values()))

    async def test_legacy_rest_permissions(self):
        payload = {"part_number": "SYNTH-PN"}
        url = "/v1/search_part"
        response = await self.client.post(url, json=payload)
        self.assertEqual(response.status_code, 401)
        response = await self.client.post(url, json=payload, headers={"authorization": "Bearer get_stock"})
        self.assertEqual(response.status_code, 403)
        self.assertEqual(self.db.calls, [])
        response = await self.client.post(url, json=payload, headers={"authorization": "Bearer search_part"})
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.headers["cache-control"], "no-store")

    async def test_lock_blocks_mcp_and_preflight(self):
        with patch.dict(os.environ, {"AEROCANADA_ACTIVATION_LOCK": "1"}, clear=True):
            self.assertFalse(check()[0]["ok"])
        async with asgi_client(locked_app()) as client:
            self.assertEqual((await client.post("/mcp")).status_code, 503)


class VerifierTests(unittest.IsolatedAsyncioTestCase):
    async def test_signed_oauth_tokens(self):
        key = rsa.generate_private_key(public_exponent=65537, key_size=2048)
        jwks = SimpleNamespace(get_signing_key_from_jwt=lambda _: SimpleNamespace(key=key.public_key()))
        verifier = Verifier(ISSUER, RESOURCE, jwks)
        claims = {"iss": ISSUER, "aud": RESOURCE, "sub": "synthetic-user",
                  "exp": int(time.time()) + 60, "scope": f"{SCOPES['search_part']} synthetic.write"}

        def sign(**overrides):
            return jwt.encode(claims | overrides, key, algorithm="RS256")

        runner = patch("connector.asyncio.to_thread", run_inline)
        runner.start()
        self.addCleanup(runner.stop)
        accepted = await verifier.verify_token(sign())
        self.assertEqual(accepted.scopes, [SCOPES["search_part"]])
        for token in [sign(aud="https://other.example"), sign(iss="https://other.example"),
                      sign(exp=1), sign(sub=""), sign(scope=[]),
                      jwt.encode(claims, "synthetic-key" * 4, algorithm="HS256")]:
            self.assertIsNone(await verifier.verify_token(token))
