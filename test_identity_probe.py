"""Synthetic OAuth and MCP tests for the temporary subject-only identity tool."""
import asyncio
import time
import unittest
from types import SimpleNamespace

import jwt
from cryptography.hazmat.primitives.asymmetric import rsa
from starlette.testclient import TestClient

from connector import (
    IdentityProbeDenied,
    Verifier,
    build_app,
    verified_subject,
)


ISSUER = "https://issuer.example.test/"
RESOURCE = "https://aerocanada.example.test/mcp"
READ_SCOPE = "aerocanada.parts.read"
PRIVATE_KEY = rsa.generate_private_key(public_exponent=65537, key_size=2048)


class StaticJwks:
    def get_signing_key_from_jwt(self, _token):
        return SimpleNamespace(key=PRIVATE_KEY.public_key())


def make_jwt(*, sub="google-oauth2|synthetic-test-user", scope=READ_SCOPE,
             iss=ISSUER, aud=RESOURCE, exp=None):
    now = int(time.time())
    return jwt.encode({
        "iss": iss,
        "aud": aud,
        "sub": sub,
        "scope": scope,
        "iat": now,
        "exp": exp if exp is not None else now + 300,
    }, PRIVATE_KEY, algorithm="RS256", headers={"kid": "synthetic-test-key"})


class IdentityProbeTests(unittest.TestCase):
    def setUp(self):
        self.verifier = Verifier(ISSUER, RESOURCE, StaticJwks())

    def test_verifier_returns_only_verified_subject_field(self):
        signed = make_jwt()
        verified = asyncio.run(self.verifier.verify_token(signed))
        self.assertIsNotNone(verified)
        self.assertEqual(verified.subject, "google-oauth2|synthetic-test-user")
        self.assertEqual(verified.client_id, verified.subject)

    def test_verifier_rejects_wrong_issuer_audience_expiry_and_tampering(self):
        cases = [
            make_jwt(iss="https://evil.example.test/"),
            make_jwt(aud="https://other.example.test/mcp"),
            make_jwt(exp=int(time.time()) - 1),
        ]
        tampered = make_jwt().split(".")
        tampered[1] = ("A" if tampered[1][0] != "A" else "B") + tampered[1][1:]
        cases.append(".".join(tampered))
        for candidate in cases:
            with self.subTest(candidate=candidate[:24]):
                self.assertIsNone(asyncio.run(self.verifier.verify_token(candidate)))

    def test_subject_output_requires_verified_subject_and_existing_read_scope(self):
        token = SimpleNamespace(
            subject="google-oauth2|synthetic-test-user",
            client_id="google-oauth2|synthetic-test-user",
            scopes=[READ_SCOPE],
        )
        self.assertEqual(verified_subject(token), "google-oauth2|synthetic-test-user")
        for denied in (
            None,
            SimpleNamespace(subject="known", client_id="known", scopes=[]),
            SimpleNamespace(subject=None, client_id="known", scopes=[READ_SCOPE]),
            SimpleNamespace(subject="first", client_id="second", scopes=[READ_SCOPE]),
        ):
            with self.subTest(denied=denied):
                with self.assertRaises(IdentityProbeDenied):
                    verified_subject(denied)

    def test_mcp_publishes_probe_and_returns_only_synthetic_sub(self):
        app = build_app(
            connect=lambda: self.fail("identity probe must not touch MariaDB"),
            verifier=self.verifier,
            issuer=ISSUER,
            resource=RESOURCE,
            allowed_hosts=["aerocanada.example.test"],
            business_reads=False,
        )
        headers = {
            "Authorization": f"Bearer {make_jwt()}",
            "Accept": "application/json, text/event-stream",
        }
        with TestClient(app, base_url="https://aerocanada.example.test") as client:
            init = client.post("/mcp", headers=headers, json={
                "jsonrpc": "2.0", "id": 1, "method": "initialize",
                "params": {
                    "protocolVersion": "2025-03-26", "capabilities": {},
                    "clientInfo": {"name": "synthetic-test", "version": "1"},
                },
            })
            self.assertEqual(init.status_code, 200, init.text)
            listed = client.post("/mcp", headers=headers, json={
                "jsonrpc": "2.0", "id": 2, "method": "tools/list", "params": {},
            })
            self.assertEqual(listed.status_code, 200, listed.text)
            tools = listed.json()["result"]["tools"]
            self.assertEqual(
                sorted(tool["name"] for tool in tools),
                ["get_part_history", "get_stock", "search_part", "verify_delegated_sub"],
            )
            identity_tool = next(tool for tool in tools if tool["name"] == "verify_delegated_sub")
            self.assertEqual(identity_tool["annotations"]["readOnlyHint"], True)
            self.assertEqual(identity_tool["_meta"]["securitySchemes"][0]["scopes"], [READ_SCOPE])
            result = client.post("/mcp", headers=headers, json={
                "jsonrpc": "2.0", "id": 3, "method": "tools/call",
                "params": {"name": "verify_delegated_sub", "arguments": {}},
            })
            self.assertEqual(result.status_code, 200, result.text)
            body = result.json()["result"]
            self.assertEqual([item["text"] for item in body["content"]],
                             ["google-oauth2|synthetic-test-user"])
            self.assertNotIn("eyJ", result.text)

            no_scope_headers = {
                **headers,
                "Authorization": f"Bearer {make_jwt(scope='')}",
            }
            denied = client.post("/mcp", headers=no_scope_headers, json={
                "jsonrpc": "2.0", "id": 4, "method": "tools/call",
                "params": {"name": "verify_delegated_sub", "arguments": {}},
            })
            self.assertEqual(denied.status_code, 200, denied.text)
            denied_body = denied.json()["result"]
            self.assertTrue(denied_body["isError"])
            self.assertEqual([item["text"] for item in denied_body["content"]], ["Accès refusé"])

    def test_unauthenticated_tool_list_is_rejected(self):
        app = build_app(
            connect=lambda: self.fail("unauthenticated request must not touch MariaDB"),
            verifier=self.verifier,
            issuer=ISSUER,
            resource=RESOURCE,
            allowed_hosts=["aerocanada.example.test"],
            business_reads=False,
        )
        with TestClient(app, base_url="https://aerocanada.example.test") as client:
            response = client.post("/mcp", json={
                "jsonrpc": "2.0", "id": 1, "method": "tools/list", "params": {},
            })
        self.assertEqual(response.status_code, 401)


if __name__ == "__main__":
    unittest.main()
