"""Expose the private Yoyamic CLI operations through the existing MCP server."""

import asyncio
import json
import os
import re
import subprocess
from pathlib import Path
from urllib.parse import quote

from jsonschema import Draft202012Validator, ValidationError
from mcp.server.auth.middleware.auth_context import get_access_token
from mcp.server.fastmcp import FastMCP
from mcp.types import CallToolResult, TextContent, Tool

PHP = "/usr/bin/php"
GATEWAY = Path(__file__).with_name("erp_gateway.php")
REQUEST_ID = {"type": "string", "pattern": "^[a-zA-Z0-9_-]{16,80}$",
              "description": "Clé stable pour rejouer la même mutation sans la répéter."}


def result_error(code, message, meta=None):
    error = {"error": code, "message": message}
    return CallToolResult(content=[TextContent(type="text", text=json.dumps(error, ensure_ascii=False))],
                          structuredContent=error, isError=True, _meta=meta)


class ErpBridge:
    def __init__(self, operations, config, issuer, *, invoke=None):
        self.config = config
        self.issuer = issuer
        self.invoke = invoke or self._invoke
        self.slots = asyncio.Semaphore(4)
        self.validators = {}
        self.catalog = {}

        for name, operation in operations.items():
            scope = operation["scope"]
            if not re.fullmatch(r"aerocanada\.[a-z_]+\.(read|create|update|send)", scope):
                raise ValueError("Unsupported ERP scope")
            needed = [scope, *operation.get("additionalScopes", [])]
            if any(not re.fullmatch(r"aerocanada\.[a-z_]+\.read", extra) for extra in needed[1:]):
                raise ValueError("Unsupported additional ERP scope")
            write = not scope.endswith(".read")
            schema = dict(operation["inputSchema"])
            if write:
                schema["properties"] = {**schema.get("properties", {}), "request_id": REQUEST_ID}
                schema["required"] = [*schema.get("required", []), "request_id"]
            schema["additionalProperties"] = False
            Draft202012Validator.check_schema(schema)
            self.validators[name] = Draft202012Validator(schema)
            self.catalog[name] = Tool(
                name=name, description=operation["description"], inputSchema=schema,
                annotations={"readOnlyHint": not write, "openWorldHint": "gmail" in name,
                             "idempotentHint": True, "destructiveHint": write},
                _meta={"securitySchemes": [{"type": "oauth2", "scopes": needed}]},
            )

    @property
    def scopes(self):
        return {scope for tool in self.catalog.values() for scope in tool.meta["securitySchemes"][0]["scopes"]}

    @classmethod
    def from_env(cls, issuer):
        enabled = os.getenv("AEROCANADA_ERP_OPERATIONS", "").split(",")
        names = {name.strip() for name in enabled if name.strip()}
        if not names:
            return None
        config = os.environ["AEROCANADA_ERP_CONFIG"]
        if not Path(config).is_absolute():
            raise ValueError("ERP config path must be absolute")
        manifest = cls._process(["--manifest"], timeout=4)
        if not names.issubset(manifest):
            raise ValueError("Unknown ERP operation in deployment allowlist")
        if any(name.startswith(("archive_", "delete_", "remove_")) for name in names):
            raise ValueError("Archive and DELETE operations cannot be activated")
        if os.getenv("AEROCANADA_ERP_ENABLE_SEND") != "1" and any(
            manifest[name]["scope"].endswith(".send") for name in names
        ):
            raise ValueError("ERP SEND operations are disabled")
        return cls({name: manifest[name] for name in sorted(names)}, config, issuer)

    @staticmethod
    def _process(action, *, payload=None, config=None, timeout=12):
        environment = {"LANG": "C.UTF-8"}
        if config is not None:
            environment["AEROCANADA_ERP_CONFIG"] = config
        command = [PHP, "-d", "display_errors=0", "-d", "memory_limit=96M", str(GATEWAY), *action]
        completed = subprocess.run(command, input=payload, stdout=subprocess.PIPE, stderr=subprocess.DEVNULL,
                                   check=True, timeout=timeout, env=environment)
        if len(completed.stdout) > 524288:
            raise ValueError("ERP response exceeds limit")
        decoded = json.loads(completed.stdout)
        if not isinstance(decoded, dict):
            raise ValueError("Unexpected ERP response")
        return decoded

    def _invoke(self, payload):
        return self._process([], payload=payload, config=self.config)

    async def call(self, name, arguments, token, metadata_url):
        tool = self.catalog[name]
        required = tool.meta["securitySchemes"][0]["scopes"]
        scope = required[0]
        if token is None or not set(required).issubset(token.scopes):
            challenge = (f'Bearer resource_metadata="{quote(metadata_url, safe=":/.-")}", scope="{" ".join(required)}", '
                         'error="insufficient_scope"')
            return result_error("insufficient_scope", "Portée OAuth requise",
                                {"mcp/www_authenticate": [challenge]})
        if not token.client_id or token.client_id.endswith("@clients"):
            return result_error("access_denied", "Identité Auth0 requise")
        try:
            self.validators[name].validate(arguments)
        except ValidationError:
            return result_error("invalid_input", "Les arguments ne correspondent pas au schéma de l’outil")

        args = dict(arguments)
        request_id = args.pop("request_id", None) if not scope.endswith(".read") else None
        payload = json.dumps({"issuer": self.issuer, "subject": token.client_id, "operation": name,
                              "request_id": request_id, "args": args}, ensure_ascii=True).encode()
        if len(payload) > 65536:
            return result_error("invalid_input", "Requête trop volumineuse")

        try:
            await asyncio.wait_for(self.slots.acquire(), timeout=0.25)
        except asyncio.TimeoutError:
            return result_error("busy", "Passerelle Yoyamic occupée")
        worker = asyncio.create_task(asyncio.to_thread(self.invoke, payload))

        def release(task):
            self.slots.release()
            if not task.cancelled():
                task.exception()

        worker.add_done_callback(release)
        try:
            response = await asyncio.shield(worker)
            if response.get("ok") is True and isinstance(response.get("result"), (dict, list)):
                value = response["result"]
                return value if isinstance(value, dict) else {"items": value}
            code = response.get("error")
            message = response.get("message")
            if code in {"access_denied", "invalid_input", "not_found", "conflict", "unavailable"} and isinstance(message, str):
                return result_error(code, message)
        except (OSError, ValueError, subprocess.SubprocessError):
            pass
        return result_error("unavailable", "Opération Yoyamic indisponible")


class ErpFastMCP(FastMCP):
    def __init__(self, *args, erp=None, metadata_url, **kwargs):
        self.erp = erp
        self.erp_metadata_url = metadata_url
        super().__init__(*args, **kwargs)

    async def list_tools(self):
        base = await super().list_tools()
        return base + list(self.erp.catalog.values()) if self.erp else base

    async def call_tool(self, name, arguments):
        if self.erp and name in self.erp.catalog:
            return await self.erp.call(name, arguments, get_access_token(), self.erp_metadata_url)
        return await super().call_tool(name, arguments)
