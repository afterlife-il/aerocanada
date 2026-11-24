"""HTTP/JSON-RPC tests using synthetic OAuth subjects and a private PHP fixture."""

import json
import tempfile
import time
from types import SimpleNamespace
from pathlib import Path
import unittest
from unittest.mock import patch

import jwt
from cryptography.hazmat.primitives.asymmetric import rsa
from mcp.server.auth.provider import AccessToken

from connector import Verifier, build_app
from erp_bridge import ErpBridge
from test_connector import HEADERS, ISSUER, RESOURCE, asgi_client


class BridgeTests(unittest.IsolatedAsyncioTestCase):
    async def test_existing_oauth_transport_with_new_tools(self):
        manifest = ErpBridge._process(["--manifest"], timeout=4)
        self.assertGreaterEqual(len(manifest), 60)
        self.assertTrue(all(value["scope"].endswith((".read", ".create", ".update", ".send"))
                            for value in manifest.values()))
        self.assertFalse(any(value["scope"].endswith((".write", ".delete")) for value in manifest.values()))
        self.assertEqual(manifest["create_company"]["scope"], "aerocanada.companies.create")
        self.assertEqual(manifest["update_company"]["scope"], "aerocanada.companies.update")
        # Validate every declared JSON schema, then activate just two operations.
        ErpBridge(manifest, "/synthetic", ISSUER)
        chosen = {name: manifest[name] for name in ("search_companies", "create_company", "update_company")}
        sent = []

        def invoke(body):
            request = json.loads(body)
            sent.append(request)
            if request["subject"] == "unmapped":
                return {"ok": False, "error": "access_denied", "message": "Accès refusé"}
            return {"ok": True, "result": {"company_id": 42}}

        bridge = ErpBridge(chosen, "/synthetic", ISSUER, invoke=invoke)

        class TestVerifier:
            async def verify_token(self, token):
                subject, level = token.split("-")
                return AccessToken(token=token, client_id=subject, scopes=[f"aerocanada.companies.{level}"])

        app = build_app(lambda: self.fail("Unexpected legacy DB access"), TestVerifier(), ISSUER,
                        RESOURCE, ["erp.example"], erp=bridge)

        async with asgi_client(app) as client:
            async def rpc(token, method, **params):
                response = await client.post("/mcp", headers=HEADERS | {"authorization": f"Bearer {token}"},
                    json={"jsonrpc": "2.0", "id": 1, "method": method, "params": params})
                self.assertEqual(response.status_code, 200)
                return response.json()["result"]

            metadata = (await client.get("/.well-known/oauth-protected-resource/mcp")).json()
            self.assertTrue(bridge.scopes <= set(metadata["scopes_supported"]))
            listed = {item["name"]: item for item in (await rpc("user-read", "tools/list"))["tools"]}
            self.assertIn("search_part", listed)
            self.assertIn("search_companies", listed)
            self.assertIn("update_company", listed)
            self.assertNotIn("list_yoyamic_users", listed)
            self.assertIn("request_id", listed["create_company"]["inputSchema"]["required"])

            args = {key: {"type": "Customer", "name": "Synthetic", "country": "CA"}.get(key, 1)
                    for key in listed["create_company"]["inputSchema"]["required"] if key != "request_id"}
            result = await rpc("user-read", "tools/call", name="create_company", arguments=args)
            self.assertTrue(result["isError"])
            self.assertIn("aerocanada.companies.create", result["_meta"]["mcp/www_authenticate"][0])
            result = await rpc("user-write", "tools/call", name="create_company", arguments=args)
            self.assertEqual(result["structuredContent"]["error"], "insufficient_scope")
            result = await rpc("user-create", "tools/call", name="update_company", arguments={})
            self.assertEqual(result["structuredContent"]["error"], "insufficient_scope")
            result = await rpc("user-create", "tools/call", name="create_company", arguments=args)
            self.assertEqual(result["structuredContent"]["error"], "invalid_input")
            result = await rpc("user-read", "tools/call", name="search_companies", arguments={"subject": "spoofed"})
            self.assertEqual(result["structuredContent"]["error"], "invalid_input")
            self.assertFalse(sent)

            args["request_id"] = "synthetic-retry-key-0001"

            async def run_inline(function, *args):
                return function(*args)

            with patch("erp_bridge.asyncio.to_thread", run_inline):
                for subject in ("claude", "chatgpt"):
                    result = await rpc(f"{subject}-create", "tools/call", name="create_company", arguments=args)
                    self.assertEqual(result["structuredContent"]["company_id"], 42)
                    self.assertEqual(sent[-1]["subject"], subject)
                    self.assertEqual(sent[-1]["issuer"], ISSUER)
                    self.assertNotIn("request_id", sent[-1]["args"])
                result = await rpc("unmapped-create", "tools/call", name="create_company", arguments=args)
                self.assertEqual(result["structuredContent"]["error"], "access_denied")

    async def test_distinct_synthetic_clients_with_signed_tokens(self):
        operation = "get_yoyamic_profile"
        scope = "aerocanada.profile.read"
        schema = {"type": "object", "properties": {}}
        seen = []

        def invoke(body):
            seen.append(json.loads(body))
            return {"ok": True, "result": {"employee_id": 4}}

        erp = ErpBridge({operation: {"scope": scope, "description": "Synthetic profile", "inputSchema": schema}},
                        "/synthetic", ISSUER, invoke=invoke)
        key = rsa.generate_private_key(public_exponent=65537, key_size=2048)
        jwks = SimpleNamespace(get_signing_key_from_jwt=lambda _: SimpleNamespace(key=key.public_key()))
        verifier = Verifier(ISSUER, RESOURCE, jwks, extra_scopes=erp.scopes)
        app = build_app(lambda: self.fail("Unexpected DB read"), verifier, ISSUER, RESOURCE, ["erp.example"], erp=erp)

        async def run_inline(function, *args):
            return function(*args)

        with patch("erp_bridge.asyncio.to_thread", run_inline):
            async with asgi_client(app) as client:
                async def request(subject, oauth_client, audience=RESOURCE):
                    token = jwt.encode({"iss": ISSUER, "aud": audience, "sub": subject, "azp": oauth_client,
                                        "scope": scope, "exp": int(time.time()) + 60}, key, algorithm="RS256")
                    return await client.post("/mcp", headers=HEADERS | {"authorization": f"Bearer {token}"},
                        json={"jsonrpc": "2.0", "id": 1, "method": "tools/call",
                              "params": {"name": operation, "arguments": {}}})

                for oauth_client in ("synthetic-claude-client", "synthetic-chatgpt-client"):
                    response = await request("auth0|synthetic-person", oauth_client)
                    self.assertEqual(response.json()["result"]["structuredContent"]["employee_id"], 4)
                    self.assertEqual(seen[-1]["subject"], "auth0|synthetic-person")
                wrong_audience = await request("auth0|synthetic-person", "synthetic-claude-client", "https://wrong.example")
                self.assertEqual(wrong_audience.status_code, 401)
                machine = await request("client-id@clients", "synthetic-chatgpt-client")
                self.assertEqual(machine.json()["result"]["structuredContent"]["error"], "access_denied")
                self.assertEqual(len(seen), 2)

    async def test_send_has_separate_scope_and_default_activation_lock(self):
        name = "send_approved_gmail_draft"
        environment = {"AEROCANADA_ERP_OPERATIONS": name, "AEROCANADA_ERP_CONFIG": "/synthetic"}
        with patch.dict("os.environ", environment, clear=True):
            with self.assertRaisesRegex(ValueError, "SEND operations are disabled"):
                ErpBridge.from_env(ISSUER)
        with patch.dict("os.environ", environment | {"AEROCANADA_ERP_ENABLE_SEND": "1"}, clear=True):
            bridge = ErpBridge.from_env(ISSUER)
            self.assertEqual(bridge.scopes, {"aerocanada.emails.send"})
            self.assertIn("request_id", bridge.catalog[name].inputSchema["required"])

        with tempfile.TemporaryDirectory() as directory:
            config = Path(directory) / "binding.json"
            config.write_text(json.dumps({"issuer": ISSUER, "enableSend": False, "subjects": {
                "synthetic": {"employeeId": 4, "employeeEmail": "admin@example.test", "enabled": True,
                              "operations": [name]}}}))
            config.chmod(0o600)
            request = {"issuer": ISSUER, "subject": "synthetic", "operation": name, "args": {},
                       "request_id": "synthetic-request-00001"}
            response = ErpBridge._process([], payload=json.dumps(request).encode(), config=str(config))
            self.assertEqual(response["error"], "access_denied")

    async def test_archive_and_delete_operations_cannot_be_activated(self):
        environment = {"AEROCANADA_ERP_OPERATIONS": "archive_company",
                       "AEROCANADA_ERP_CONFIG": "/synthetic"}
        with patch.dict("os.environ", environment, clear=True):
            with self.assertRaisesRegex(ValueError, "cannot be activated"):
                ErpBridge.from_env(ISSUER)

    async def test_gmail_import_requires_email_read_and_rfq_write(self):
        name = "create_rfq_from_gmail"
        schema = ErpBridge._process(["--manifest"], timeout=4)[name]
        bridge = ErpBridge({name: schema}, "/synthetic", ISSUER,
                           invoke=lambda _: self.fail("Unexpected ERP invocation"))
        for scopes in (["aerocanada.rfqs.create"], ["aerocanada.emails.read"]):
            token = AccessToken(token="synthetic", client_id="auth0|actor", scopes=scopes)
            response = await bridge.call(name, {}, token, RESOURCE)
            self.assertEqual(response.structuredContent["error"], "insufficient_scope")
        self.assertEqual(bridge.scopes, {"aerocanada.rfqs.create", "aerocanada.emails.read"})

    async def test_private_php_gateway_rejects_unbound_actor_before_db_access(self):
        with tempfile.TemporaryDirectory() as directory:
            config = Path(directory) / "binding.json"
            config.write_text(json.dumps({"issuer": ISSUER, "subjects": {}}))
            config.chmod(0o600)
            request = {"issuer": ISSUER, "subject": "unbound", "operation": "create_company",
                       "args": {}, "request_id": "synthetic-request-00001"}
            response = ErpBridge._process([], payload=json.dumps(request).encode(), config=str(config))
            self.assertEqual(response["error"], "access_denied")
            config.chmod(0o644)
            response = ErpBridge._process([], payload=json.dumps(request).encode(), config=str(config))
            self.assertEqual(response["error"], "unavailable")
