<?php
declare(strict_types=1);

// Destructive operations are confined to an ephemeral MariaDB socket/database.
$socket = getenv('AEROCANADA_TEST_SOCKET') ?: '';
$database = getenv('AEROCANADA_TEST_DATABASE') ?: '';
if (!preg_match('~^/tmp/aerocanada-real-ddl-[A-Za-z0-9._-]+/mysql\.sock$~D', $socket)
    || $database !== 'synthetic_yoyamic') {
    fwrite(STDERR, "Refusing non-isolated schema test target\n");
    exit(2);
}

require __DIR__ . '/erp_gateway.php';

$setupDb = new PDO("mysql:unix_socket=$socket;dbname=$database;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$setupDb->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE'");

function ident(string $value): string
{
    if (!preg_match('/^[A-Za-z0-9_]+$/D', $value)) {
        throw new LogicException('Invalid fixture identifier');
    }
    return '`' . $value . '`';
}

function fixtureValue(string $columnType, string $column, string $idColumn, int $id): mixed
{
    if ($column === $idColumn) {
        return $id;
    }
    if (preg_match('/^(tinyint|smallint|mediumint|int|bigint|year)/', $columnType)) {
        return 0;
    }
    if (preg_match('/^(decimal|numeric|float|double)/', $columnType)) {
        return '0';
    }
    if (str_starts_with($columnType, 'enum(') || str_starts_with($columnType, 'set(')) {
        if (preg_match("/^enum\\('((?:[^'\\\\]|\\\\.)*)'/", $columnType, $match)) {
            return stripcslashes($match[1]);
        }
        return '';
    }
    if (preg_match('/^date$/', $columnType)) {
        return '2026-11-24';
    }
    if (preg_match('/^(datetime|timestamp)/', $columnType)) {
        return '2026-11-24 00:00:00';
    }
    if (str_starts_with($columnType, 'json')) {
        return '{}';
    }
    return '';
}

function seedLookup(PDO $db, string $table, string $idColumn, int $id = 1, array $overrides = []): void
{
    $metadata = $db->prepare('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, GENERATION_EXPRESSION
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION');
    $metadata->execute([$table]);
    $columns = [];
    $values = [];
    foreach ($metadata->fetchAll() as $column) {
        $name = $column['COLUMN_NAME'];
        if (str_contains((string)$column['EXTRA'], 'auto_increment')
            || (string)($column['GENERATION_EXPRESSION'] ?? '') !== '') {
            continue;
        }
        if (array_key_exists($name, $overrides)) {
            $columns[] = $name;
            $values[] = $overrides[$name];
            continue;
        }
        if ($column['IS_NULLABLE'] === 'YES' || $column['COLUMN_DEFAULT'] !== null) {
            continue;
        }
        $columns[] = $name;
        $values[] = fixtureValue($column['COLUMN_TYPE'], $name, $idColumn, $id);
    }
    if (!in_array($idColumn, $columns, true)) {
        $columns[] = $idColumn;
        $values[] = $id;
    }
    $sql = 'INSERT INTO ' . ident($table) . ' (' . implode(', ', array_map('ident', $columns)) . ') VALUES ('
        . implode(', ', array_fill(0, count($columns), '?')) . ')';
    $db->prepare($sql)->execute($values);
}

$lookups = [
    ['tbl_Company_Type', 'Fld_Company_Type_ID'], ['tbl_Division', 'Fld_Division_ID'],
    ['tbl_Payment', 'Fld_Payment_Term_ID'], ['tbl_Currency', 'Fld_Currency_ID', ['Fld_Currency_Text' => 'USD']],
    ['tbl_Region', 'Region_ID'], ['tbl_Aircraft', 'Fld_AC_ID', ['Fld_AC_Model' => 'A320']],
    ['tbl_Priority', 'Fld_Priority_ID'], ['tbl_RFQ_Type', 'Fld_RFQ_Type_ID'],
    ['tbl_Condition', 'Fld_Condition_ID', ['Fld_Condition_Text' => 'NEW']],
    ['tbl_Release', 'Fld_Release_ID'], ['tbl_Status', 'Fld_Status_ID'],
    ['tbl_Stock_Location', 'Fld_Stock_Location_ID'], ['tbl_Warehouse_Location', 'Id'],
];
foreach ($lookups as $lookup) {
    [$table, $idColumn] = $lookup;
    seedLookup($setupDb, $table, $idColumn, 1, $lookup[2] ?? []);
}
seedLookup($setupDb, 'tbl_Employee', 'Employee_ID', 77, [
    'Employee_Name' => 'Synthetic MCP Operator', 'email' => 'operator@synthetic.invalid',
    'statut' => 'SuperAdmin', 'position' => 'SuperAdmin', 'pw' => '',
]);
seedLookup($setupDb, 'tbl_Employee', 'Employee_ID', 78, [
    'Employee_Name' => 'Synthetic Sales User', 'email' => 'sales@synthetic.invalid',
    'statut' => 'Sales', 'position' => 'Sales', 'pw' => '',
]);
$auditExists = $setupDb->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mcp_erp_audit'")->fetchColumn();
if ((int)$auditExists === 0) {
    $setupDb->exec(file_get_contents(__DIR__ . '/erp_audit.sql'));
}
$testUser = getenv('AEROCANADA_TEST_DB_USER') ?: '';
if ($testUser !== '') {
    $testPassFile = getenv('AEROCANADA_TEST_DB_PASSWORD_FILE') ?: '';
    $testPassword = $testPassFile !== '' ? rtrim(erp_private_file($testPassFile, 256), "\r\n") : '';
    if (!preg_match('/^[A-Za-z0-9_]{1,32}$/D', $testUser) || $testPassword === '') {
        throw new RuntimeException('Invalid isolated writer credentials');
    }
    $db = new PDO("mysql:unix_socket=$socket;dbname=$database;charset=utf8mb4", $testUser, $testPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
} else {
    $db = $setupDb;
}

$temporaryToken = sys_get_temp_dir() . '/aerocanada-synthetic-mail-' . bin2hex(random_bytes(8));
file_put_contents($temporaryToken, 'synthetic-google-access-token-123456');
chmod($temporaryToken, 0600);
$fakeTransport = static function (string $path, array $query, string $token, ?array $body = null): array {
    if ($token !== 'synthetic-google-access-token-123456') {
        throw new RuntimeException('Unexpected synthetic Gmail token');
    }
    if ($path === '/profile') {
        return ['emailAddress' => 'operator@synthetic.invalid'];
    }
    if ($path === '/messages/synthetic-rfq') {
        $plain = "PN: SYNTH-PN-001\nQTY: 2";
        return ['id' => 'synthetic-rfq', 'threadId' => 'synthetic-thread', 'snippet' => 'Synthetic RFQ',
            'payload' => ['mimeType' => 'text/plain', 'headers' => [
                ['name' => 'From', 'value' => 'Buyer <buyer@synthetic.invalid>'],
                ['name' => 'Subject', 'value' => 'Synthetic RFQ'],
            ], 'body' => ['data' => rtrim(strtr(base64_encode($plain), '+/', '-_'), '=')]]];
    }
    if ($path === '/drafts' && is_array($body)) {
        return ['id' => 'synthetic-draft-id', 'message' => ['id' => 'synthetic-draft-message']];
    }
    throw new RuntimeException('Unexpected fake Gmail request: ' . $path);
};
$binding = [
    'enabled' => true, 'employeeId' => 77, 'employeeEmail' => 'operator@synthetic.invalid',
    'operations' => [], 'gmail' => ['employee' => [
        'email' => 'operator@synthetic.invalid', 'readTokenFile' => $temporaryToken,
        'draftTokenFile' => $temporaryToken,
    ]],
];
$operations = erp_operations();
$mailOperations = erp_mail_operations($fakeTransport);
$operations['create_rfq_from_gmail'] = $mailOperations['create_rfq_from_gmail'];
$operations['create_quote_gmail_draft'] = $mailOperations['create_quote_gmail_draft'];

$requestNumber = 0;
$tested = [];
$write = static function (string $name, array $args) use (&$requestNumber, &$tested, $db, $binding, $operations): array {
    if (!isset($operations[$name]) || str_ends_with($operations[$name]['scope'], '.read')
        || str_ends_with($operations[$name]['scope'], '.send')) {
        throw new LogicException('Not a database mutation: ' . $name);
    }
    $binding['operations'][] = $name;
    $requestNumber++;
    $request = [
        'issuer' => 'https://issuer.synthetic.invalid/', 'subject' => 'auth0|synthetic-yoyamic-employee',
        'operation' => $name, 'request_id' => 'real_schema_test_' . $requestNumber, 'args' => $args,
    ];
    $result = erp_execute($db, $binding, $request, $operations[$name]);
    $tested[$name] = true;
    echo "PASS $name\n";
    return $result;
};

try {
    putenv('AEROCANADA_YOYAMIC_PAGES=/opt/aerocanada/pages');
    $customer = $write('create_company', ['name' => 'Synthetic Test Customer']);
    $supplier = $write('create_company', ['name' => 'Synthetic Test Supplier']);
    $customerId = $customer['id'];
    $supplierId = $supplier['id'];
    $write('update_company', ['company_id' => $customerId, 'website' => 'https://synthetic.invalid']);
    $write('update_company_payment_terms', ['company_id' => $customerId, 'customer_term_id' => 1,
        'customer_amount' => '30', 'customer_currency_id' => 1]);
    $address = $write('create_company_address', ['company_id' => $customerId, 'company_type_id' => 1,
        'address_type_id' => 1, 'city' => 'Synthetic City', 'country' => 'Synthetic Country']);
    $write('update_company_address', ['company_id' => $customerId, 'address_id' => $address['id'], 'street' => 'Test Street']);
    $fleet = $write('add_company_aircraft', ['company_id' => $customerId, 'region_id' => 1, 'aircraft_id' => 1,
        'serial_number' => 'SYNTH001', 'registration' => 'C-SYNTH']);
    $write('update_company_aircraft', ['company_id' => $customerId, 'fleet_id' => $fleet['id'], 'unit' => 'A320']);
    $write('add_company_competitor', ['company_id' => $customerId, 'competitor_id' => $supplierId]);
    $customerContact = $write('create_contact', ['company_id' => $customerId, 'name' => 'Synthetic Buyer',
        'email' => 'buyer@synthetic.invalid']);
    $supplierContact = $write('create_contact', ['company_id' => $supplierId, 'name' => 'Synthetic Supplier Contact',
        'email' => 'supplier@synthetic.invalid']);
    $write('update_contact', ['company_id' => $customerId, 'contact_id' => $customerContact['id'], 'phone' => '+1 555 0100']);
    $write('archive_contact', ['company_id' => $customerId, 'contact_id' => $customerContact['id']]);
    $write('restore_contact', ['company_id' => $customerId, 'contact_id' => $customerContact['id']]);
    $part = $write('create_part', ['part_number' => 'SYNTH-PN-001', 'manufacturer_id' => $supplierId]);
    $parts = $write('create_parts', ['parts' => [
        ['part_number' => 'SYNTH-PN-002', 'manufacturer_id' => $supplierId],
        ['part_number' => 'SYNTH-PN-003', 'manufacturer_id' => $supplierId],
    ]]);
    $partId = $part['part_id'];
    $write('update_part', ['part_id' => $partId, 'description' => 'Synthetic part update', 'ata_chapter' => 32]);
    $stock = $write('create_internal_stock', ['part_id' => $partId, 'owner_id' => $supplierId,
        'quantity' => 2, 'status_id' => 1, 'condition_id' => 1, 'serial_number' => 'SYNTH001']);
    $write('update_internal_stock', ['stock_id' => $stock['stock_id'], 'quantity' => 3]);
    $external = $write('create_external_stock', ['part_id' => $partId, 'owner_id' => $supplierId, 'quantity' => 4]);
    $write('update_external_stock', ['stock_id' => $external['stock_id'], 'quantity' => 5]);
    $rfq = $write('create_rfq', ['rfq_reference' => 'MCP-TEST-0000001', 'company_id' => $customerId,
        'contact_id' => $customerContact['id'], 'priority_id' => 1, 'payment_term_id' => 1,
        'lines' => [
            ['part_id' => $partId, 'quantity' => 2, 'rfq_type_id' => 1, 'condition_id' => 1],
            ['part_id' => $parts['part_ids'][0], 'quantity' => 1, 'rfq_type_id' => 1, 'condition_id' => 1],
        ]]);
    $lineId = $rfq['line_ids'][0];
    $write('add_rfq_line', ['rfq_reference' => $rfq['rfq_reference'], 'line' => [
        'part_id' => $parts['part_ids'][1], 'quantity' => 1, 'rfq_type_id' => 1, 'condition_id' => 1,
    ]]);
    $write('update_rfq_line', ['line_id' => $lineId, 'quantity' => 3]);
    $supplierQuote = $write('create_supplier_quote', ['rfq_line_id' => $lineId, 'supplier_id' => $supplierId,
        'contact_id' => $supplierContact['id'], 'quantity' => 3, 'price' => '25.50', 'condition_id' => 1,
        'currency_id' => 1, 'payment_term_id' => 1, 'lead_time' => '5 days']);
    $write('update_supplier_quote', ['id' => $supplierQuote['id'], 'price' => '26.00']);
    $quote = $write('create_customer_quotes', ['rfq_reference' => $rfq['rfq_reference'], 'lines' => [[
        'rfq_line_id' => $lineId, 'quantity' => 3, 'condition_id' => 1, 'price' => '35.00', 'currency_id' => 1,
        'source_type' => 'SQ', 'source_id' => $supplierQuote['id'], 'moq' => '1', 'lead_time' => '7 days',
    ]]]);
    $quoteId = $quote['quote_ids'][0];
    $write('update_customer_quote', ['id' => $quoteId, 'price' => '36.00']);
    $draft = $write('create_order_draft_from_quote', ['quote_id' => $quoteId]);
    $write('update_order_draft', ['id' => $draft['id'], 'customer_po_number' => 'SYNTH-PO-01']);
    $write('validate_customer_po', ['id' => $draft['id'], 'accepted_price' => '36.00', 'accepted_qty' => 3]);
    $invoice = $write('create_invoice', ['company_id' => $customerId, 'contact_id' => $customerContact['id'],
        'currency_id' => 1, 'rfq_reference' => $rfq['rfq_reference'], 'invoice_type' => 'commercial']);
    $item = $write('add_invoice_item', ['invoice_id' => $invoice['id'], 'part_id' => $partId, 'condition_id' => 1,
        'description' => 'Synthetic invoice line', 'quantity' => 3, 'unit_price' => '36.00']);
    $write('update_invoice_item', ['invoice_id' => $invoice['id'], 'item_id' => $item['id'], 'part_id' => $partId,
        'condition_id' => 1, 'description' => 'Synthetic invoice line updated', 'quantity' => 4, 'unit_price' => '36.00']);
    $write('mark_invoice_sent', ['id' => $invoice['id']]);
    $write('mark_invoice_paid', ['id' => $invoice['id']]);
    $shipper = $write('create_shipper', ['name' => 'Synthetic Carrier', 'contact_name' => 'Test Contact', 'contact_phone' => '5550100']);
    $write('update_shipper', ['shipper_id' => $shipper['shipper_id'], 'name' => 'Synthetic Carrier 2',
        'contact_name' => 'Test Contact', 'contact_phone' => '5550100']);
    $shipment = $write('create_shipment', ['invoice_id' => $invoice['id'], 'rfq_reference' => $rfq['rfq_reference'],
        'company_id' => $customerId, 'shipper_id' => $shipper['shipper_id'], 'ship_from' => 'Synthetic Origin',
        'ship_to' => 'Synthetic Destination']);
    $write('update_shipment_tracking', ['id' => $shipment['id'], 'tracking_number' => 'SYNTH-TRACK-1']);
    $write('update_shipment_status', ['id' => $shipment['id'], 'status' => 'in_transit']);
    $write('import_supplier_capabilities', ['supplier_id' => $supplierId, 'items' => [[
        'part_number' => 'SYNTH-PN-001', 'manufacturer' => 'Synthetic OEM', 'aircraft' => 'A320',
        'ata' => '32', 'capability' => 'Synthetic inspection', 'code_oem' => '7',
    ]]]);
    $write('update_yoyamic_user_role', ['employee_id' => 78, 'expected_role' => 'Sales', 'role' => 'Manager']);
    $write('create_rfq_from_gmail', ['mailbox' => 'employee', 'message_id' => 'synthetic-rfq',
        'company_id' => $customerId, 'contact_id' => $customerContact['id'], 'priority_id' => 1,
        'payment_term_id' => 1, 'rfq_type_id' => 1, 'condition_id' => 1]);
    $write('archive_contact', ['company_id' => $supplierId, 'contact_id' => $supplierContact['id']]);
    $write('archive_part', ['part_id' => $parts['part_ids'][1]]);
    $write('archive_company', ['company_id' => $supplierId]);

    // Gmail create is the additional non-ERP mutation. Execute its network call
    // only through an in-process fake transport, never the Gmail API.
    $write('create_quote_gmail_draft', ['mailbox' => 'employee', 'quote_id' => $quoteId]);

    $expectedBusinessOperations = array_filter($operations, static fn(array $op, string $name): bool =>
        !str_ends_with($op['scope'], '.read') && !str_ends_with($op['scope'], '.send')
        && $name !== 'create_quote_gmail_draft', ARRAY_FILTER_USE_BOTH);
    $missing = array_diff(array_keys($expectedBusinessOperations), array_keys($tested));
    if ($missing !== []) {
        throw new RuntimeException('Untested DB mutation tools: ' . implode(', ', $missing));
    }
    if (count($expectedBusinessOperations) !== 44 || count($tested) !== 45) {
        throw new RuntimeException('Unexpected mutation manifest/test count: ' . count($expectedBusinessOperations) . '/' . count($tested));
    }
    $auditCount = (int)$db->query('SELECT COUNT(*) FROM mcp_erp_audit')->fetchColumn();
    if ($auditCount !== $requestNumber) {
        throw new RuntimeException('Audit rows persisted: ' . $auditCount . ', expected one row for each of ' . $requestNumber . ' requests');
    }
    foreach ([
        'company' => ['tb_company', 'Fld_Company_ID', $customerId],
        'contact' => ['tb_company_contact', 'id_company_contact', $customerContact['id']],
        'part' => ['tbl_Parts', 'Fld_Part_ID', $partId],
        'rfq line' => ['tbl_RFQ_1', 'ID', $lineId],
        'supplier quote' => ['tbl_RFQ_2', 'ID', $supplierQuote['id']],
        'customer quote' => ['tbl_RFQ_3', 'ID', $quoteId],
        'invoice' => ['tbl_Invoices', 'id', $invoice['id']],
        'shipment' => ['tbl_Shipping', 'id', $shipment['id']],
    ] as $label => [$table, $key, $id]) {
        $check = $db->prepare('SELECT COUNT(*) FROM ' . ident($table) . ' WHERE ' . ident($key) . ' = ?');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() !== 1) {
            throw new RuntimeException('Synthetic persistence check failed for ' . $label);
        }
    }
    if ($testUser !== '') {
        foreach ([
            'DELETE FROM tb_company WHERE Fld_Company_ID = ' . (int)$customerId,
            'CREATE TABLE mcp_writer_ddl_probe (id INT)',
        ] as $forbiddenSql) {
            try {
                $db->exec($forbiddenSql);
                throw new RuntimeException('Writer unexpectedly had forbidden DELETE/DDL privilege');
            } catch (PDOException $denied) {
                if (!in_array((string)$denied->getCode(), ['42000', '1142', '1143'], true)) {
                    throw $denied;
                }
            }
        }
        echo "PASS writer denied DELETE and DDL\n";
    }
    echo "PASS 44/44 Yoyamic DB mutation tools, 1/1 fake Gmail draft, $requestNumber audit rows, persistence checks\n";
} finally {
    @unlink($temporaryToken);
}
