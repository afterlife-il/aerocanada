"""Emit SQL for a newly initialized, offline MariaDB bootstrap data directory."""
from pathlib import Path

from business_reads import BUSINESS_QUERIES
from connector import LIMIT, QUERIES, SOURCES

schema = Path(__file__).with_name("synthetic-schema.sql").read_text()
statements = []
for db in SOURCES:
    print(f"CREATE DATABASE {db};\nUSE {db};\n{schema}")
    for operation, queries in QUERIES.items():
        for kind, query, arity in queries:
            count = 3 if kind == "rfq" else 1
            statements.append((query.format(db=db), ("'SYNTH-PN'",) * arity, count))
    for operation, (_, query) in BUSINESS_QUERIES.items():
        args = ("'2026-11-23-120000'",) if operation == "get_rfq" else ("7",)
        statements.append((query.format(db=db), args, 2 if operation == "get_rfq" else 1))
    statements.append((BUSINESS_QUERIES["get_rfq"][1].format(db=db), ("'2026'",), 1))

print("CREATE TABLE assertions (ok INT NOT NULL CHECK (ok = 1));")
for query, args, count in statements:
    rendered = (query + "LIMIT %s") % (args + (LIMIT + 1,))
    print(f"START TRANSACTION READ ONLY;\nSET STATEMENT max_statement_time=1 FOR {rendered};\nROLLBACK;")
    print(f"INSERT INTO assertions SELECT COUNT(*) = {count} FROM ({rendered}) results;")
