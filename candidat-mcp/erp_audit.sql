CREATE TABLE mcp_erp_audit (
    actor_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    employee_id INT NOT NULL,
    operation VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    request_id VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    input_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    result_json MEDIUMTEXT NOT NULL,
    delivery_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (actor_hash, employee_id, operation, request_id),
    UNIQUE KEY send_once (delivery_key),
    KEY employee_history (employee_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
