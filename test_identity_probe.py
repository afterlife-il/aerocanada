"""Verify the temporary identity probe is absent after its removal."""
import time
import unittest
from types import SimpleNamespace

import jwt
from cryptography.hazmat.primitives.asymmetric import rsa
from starlette.testclient import TestClient

from connector import Verifier, build_app

ISSUER = "https://issuer.example.test/"
RESOURCE = "https://aerocanada.example.test/mcp"
READ_SCOPE = "aerocanada.parts.read"
PRIVATE_KEY = rsa.generate_private_key(public_exponent=65537, key_size=2048)


class StaticJwks:
    def get_signing_key_from_jwt(self, _token):
        return SimpleNamespace(key=PRIVATE_KEY.public_key())


def make_jwt():
    now = int(time.time())
    return jwt.encode({
        "iss": ISSUER, "aud": RESOURCE, "sub": "synthetic-user",
        "scope": READ_SCOPE, "iat": now, "exp": now + 300,
    }, PRIVATE_KEY, algorithm="RS256", headers={"kid": "synthetic-key"})


class IdentityProbeRemovalTests(unittest.TestCase):
    def make_app(self):
        verifier = Verifier(ISSUER, RESOURCE, StaticJwks())
        return build_app(
            connect=lambda: self.fail("catalog check must not touch MariaDB"),
            verifier=verifier,
            issuer=ISSUER,
            resource=RESOURCE,
            allowed_hosts=["aerocanada.example.test"],
            business_reads=False,
        )

    def test_temporary_probe_is_removed_and_original_tools_remain(self):
        headers = {
            "Authorization": f"Bearer {make_jwt()}",
            "Accept": "application/json, text/event-stream",
        }
        with TestClient(self.make_app(), base_url="https://aerocanada.example.test") as client:
            response = client.post("/mcp", headers=headers, json={
                "jsonrpc": "2.0", "id": 1, "method": "tools/list", "params": {},
            })
        self.assertEqual(response.status_code, 200, response.text)
        names = sorted(tool["name"] for tool in response.json()["result"]["tools"])
        self.assertEqual(names, ["get_part_history", "get_stock", "search_part"])
        self.assertNotIn("verify_delegated_sub", names)

    def test_unauthenticated_mcp_is_still_rejected(self):
        with TestClient(self.make_app(), base_url="https://aerocanada.example.test") as client:
            response = client.post("/mcp", json={
                "jsonrpc": "2.0", "id": 1, "method": "tools/list", "params": {},
            })
        self.assertEqual(response.status_code, 401)


if __name__ == "__main__":
    unittest.main()
