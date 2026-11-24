<?php
declare(strict_types=1);

$ops = require __DIR__ . '/erp_workflows.php';

final class WorkflowPdo extends PDO
{
    public array $responses = [];
    public array $statements = [];
    public int $lastId = 0;
    public bool $transaction = true;

    public function __construct() {}
    public function inTransaction(): bool { return $this->transaction; }
    public function lastInsertId(?string $name = null): string|false { return (string)$this->lastId; }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new WorkflowStatement($this, $query);
    }
}

final class WorkflowStatement extends PDOStatement
{
    private array $bound = [];
    private mixed $response;

    public function __construct(private WorkflowPdo $db, private string $sql) {}

    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $expected = $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        if ($type !== $expected) {
            throw new RuntimeException('Unexpected bind type');
        }
        $this->bound[$param] = $value;
        return true;
    }

    public function execute(?array $params = null): bool
    {
        if (!$this->db->responses) {
            throw new RuntimeException('Unexpected SQL: ' . $this->sql);
        }
        $this->db->statements[] = [$this->sql, $this->bound];
        $this->response = array_shift($this->db->responses);
        if (str_starts_with($this->sql, 'INSERT INTO')) {
            $this->db->lastId = (int)$this->response;
        }
        return true;
    }

    public function fetchColumn(int $column = 0): mixed { return $this->response; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->response;
    }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->response; }
}

function expectWorkflow(mixed $value, mixed $expected, string $label): void
{
    if ($value !== $expected) {
        throw new RuntimeException($label . ': ' . var_export($value, true));
    }
}

$db = new WorkflowPdo();
$called = [];
$call = static function (string $name, array $args, array $responses, mixed $expected) use ($db, $ops, &$called): void {
    $called[$name] = true;
    $db->responses = $responses;
    $result = $ops[$name]['handler']($db, $args, 77);
    expectWorkflow($result, $expected, $name);
    expectWorkflow($db->responses, [], $name . ' queries');
};

$rfq = '2026-11-23-120000';
$line = ['part_id' => 1, 'quantity' => 5, 'rfq_type_id' => 1, 'condition_id' => 1];
$part = ['Fld_Part_Nbr' => 'PART-1', 'Fld_Part_Desc' => 'Synthetic'];
$lineInput = ['Fld_RFQ_ID' => $rfq, 'Fld_Part_ID' => 1, 'Fld_Priority_ID' => 1];
$in = ['search' => '%SYNTH_', 'limit' => 1, 'priority_id' => 1];
$call('search_rfqs', $in, [[['id' => 1], ['id' => 2]]], ['items' => [['id' => 1]], 'next_offset' => 1]);
expectWorkflow($db->statements[0][1], [1 => '%SYNTH_', 2 => '%SYNTH_', 3 => '%SYNTH_', 4 => 1, 5 => 2, 6 => 0], 'prepared literal search');
$call('create_rfq', ['rfq_reference' => $rfq, 'company_id' => 1, 'contact_id' => 1, 'priority_id' => 1,
    'payment_term_id' => 1, 'lines' => [$line, $line + ['observation' => 'Different item description']]],
    [[], [], [], [], false, false, $part, [], [], $part, [], [], 1, 1, 2],
    ['id' => 1, 'rfq_reference' => $rfq, 'line_ids' => [1, 2]]);
$call('get_yoyamic_rfq', ['rfq_reference' => $rfq], [['reference' => $rfq], [['line_id' => 1]]],
    ['reference' => $rfq, 'lines' => ['items' => [['line_id' => 1]], 'next_offset' => null]]);
$call('add_rfq_line', ['rfq_reference' => $rfq, 'line' => $line],
    [[], ['Fld_Customer_ID' => 1, 'date' => '2026-11-23', 'Fld_Priority_ID' => 1,
        'Fld_Payment_Term_ID' => 1, 'id_company_contact' => 1], $part, [], [], 3],
    ['id' => 3, 'rfq_reference' => $rfq]);
$call('update_rfq_line', ['line_id' => 1, 'quantity' => 4], [$lineInput, 1], ['id' => 1, 'rfq_reference' => $rfq]);

$quoteTerms = ['quantity' => 5, 'condition_id' => 1, 'currency_id' => 1, 'price' => '25.50'];
$call('create_customer_quotes', ['rfq_reference' => $rfq, 'lines' => [
    ['rfq_line_id' => 1] + $quoteTerms, ['rfq_line_id' => 2] + $quoteTerms,
]], ['date', $lineInput, [], [], $lineInput, [], [], 1, 2], ['quote_ids' => [1, 2], 'rfq_reference' => $rfq]);
$call('update_customer_quote', ['id' => 1, 'price' => '26.50'], [$lineInput, 1], ['id' => 1, 'rfq_reference' => $rfq]);
$call('list_customer_quotes', ['rfq_reference' => $rfq], [[['id' => 1]]], ['items' => [['id' => 1]], 'next_offset' => null]);
$call('get_customer_quote', ['id' => 1], [['id' => 1]], ['id' => 1]);
$call('create_supplier_quote', ['rfq_line_id' => 1, 'supplier_id' => 2, 'contact_id' => 2, 'payment_term_id' => 1] + $quoteTerms,
    [$lineInput, [], [], [], [], [], 1, false, 1], ['id' => 1, 'rfq_reference' => $rfq]);
$call('update_supplier_quote', ['id' => 1, 'price' => '22.50'], [$lineInput, 1], ['id' => 1, 'rfq_reference' => $rfq]);
$call('list_supplier_quotes', ['rfq_reference' => $rfq], [[['id' => 1]]], ['items' => [['id' => 1]], 'next_offset' => null]);

$call('create_customer_quotes', ['rfq_reference' => $rfq, 'lines' => [
    ['rfq_line_id' => 1, 'source_type' => 'SQ', 'source_id' => 1] + $quoteTerms,
]], ['date', $lineInput, [], [], 1, [], 3], ['quote_ids' => [3], 'rfq_reference' => $rfq]);

$quote = $lineInput + ['id_tbl_rfq1' => 1, 'Fld_Part_Id' => 1, 'company_id' => 1, 'contact_id' => 1,
    'Fld_Qty' => 5, 'Fld_Condition' => 1, 'Fld_Price' => '26.50', 'Fld_Currency_ID' => 1];
$call('create_order_draft_from_quote', ['quote_id' => 1], [$quote, false, 1], ['id' => 1, 'number' => 'DRAFT-Q1']);
$call('create_order_draft_from_quote', ['quote_id' => 1], [$quote, ['id' => 1, 'po_number' => 'DRAFT-Q1']],
    ['id' => 1, 'number' => 'DRAFT-Q1']);
$call('update_order_draft', ['id' => 1, 'customer_po_number' => 'SYNTH-123'], [['status' => 'DRAFT'], 1],
    ['id' => 1, 'status' => 'DRAFT']);
$call('validate_customer_po', ['id' => 1], [['status' => 'DRAFT', 'customer_po_number' => 'SYNTH-123',
    'accepted_price' => '26.50', 'accepted_qty' => 5], 1], ['id' => 1, 'status' => 'ACCEPTED_ORDER']);

$call('create_invoice', ['invoice_type' => 'commercial', 'company_id' => 1, 'contact_id' => 1, 'currency_id' => 1,
    'rfq_reference' => $rfq], [[], [], [], [], null, 1], ['id' => 1, 'number' => 'INV-' . date('Y') . '-00001']);
$call('add_invoice_item', ['invoice_id' => 1, 'part_id' => 1, 'condition_id' => 1, 'description' => 'Synthetic',
    'quantity' => 3, 'unit_price' => '12.99'], [['status' => 'draft'], [], [], 1, 1], ['id' => 1, 'invoice_id' => 1]);
$call('update_invoice_item', ['invoice_id' => 1, 'item_id' => 1, 'part_id' => 1, 'condition_id' => 1,
    'description' => 'Synthetic revised', 'quantity' => 4, 'unit_price' => '11.25'],
    [['status' => 'draft'], ['id' => 1], [], [], 1, 1], ['id' => 1, 'invoice_id' => 1]);
$call('mark_invoice_sent', ['id' => 1], [['status' => 'draft'], 1], ['id' => 1, 'status' => 'sent']);
$call('mark_invoice_paid', ['id' => 1], [['status' => 'sent'], 1], ['id' => 1, 'status' => 'paid']);

$call('create_shipment', ['company_id' => 1, 'invoice_id' => 1, 'rfq_reference' => $rfq, 'shipper_id' => 1,
    'ship_from' => 'SYNTH A', 'ship_to' => 'SYNTH B'], [[], [], [], [], null, 1],
    ['id' => 1, 'number' => 'SHP-' . date('Y') . '-00001']);
$call('update_shipment_tracking', ['id' => 1, 'tracking_number' => 'SYNTH-TRACK'],
    [['status' => 'preparing'], 1], ['id' => 1, 'status' => 'shipped']);
$call('update_shipment_status', ['id' => 1, 'status' => 'delivered'], [['status' => 'shipped'], 1],
    ['id' => 1, 'status' => 'delivered']);
foreach ([
    ['list_invoices', 'get_invoice', 'paid'],
    ['list_shipments', 'get_shipment', 'delivered'],
    ['list_order_drafts', 'get_order_draft', 'ACCEPTED_ORDER'],
] as [$list, $get, $status]) {
    $call($list, ['company_id' => 1, 'status' => $status], [[['id' => 1]]], ['items' => [['id' => 1]], 'next_offset' => null]);
    $detail = ['id' => 1];
    $responses = [$detail];
    if ($get !== 'get_order_draft') {
        $responses[] = [];
        $detail['lines'] = ['items' => [], 'next_offset' => null];
    }
    $call($get, ['id' => 1], $responses, $detail);
}
$call('list_supplier_capabilities', ['company_id' => 2, 'part_number' => 'PART-1'], [[]], ['items' => [], 'next_offset' => null]);
$capability = ['part_number' => 'PART-1', 'aircraft' => 'A320', 'capability' => 'Inspection'];
$call('import_supplier_capabilities', ['supplier_id' => 2, 'items' => [$capability, $capability]],
    [[], false, 1, 1], ['ids' => [1], 'skipped_indexes' => [1]]);
foreach (['sales', 'purchase'] as $kind) {
    $call("list_{$kind}_orders", ['supplier_id' => 2],
        [[['id' => 1]]], ['items' => [['id' => 1]], 'next_offset' => null]);
    $call("get_{$kind}_order", ['id' => 1], [['id' => 1]], ['id' => 1]);
}
$call('get_erp_dashboard', [], [2, 0, 3, 3], ['active_companies' => 2, 'internal_stock_rows' => 0,
    'rfq_lines_current_month' => 3, 'customer_quote_rows' => 3]);

if (($argv[1] ?? null) === '--sql') {
    foreach ($db->statements as [$sql, $params]) {
        $index = 0;
        echo preg_replace_callback('/\?/', static function () use (&$index, $params): string {
            $value = $params[++$index];
            if ($value === null) {
                return 'NULL';
            }
            return is_int($value) ? (string)$value : "CONVERT(X'" . bin2hex($value) . "' USING utf8mb4)";
        }, $sql), ";\n";
    }
    exit;
}

foreach ($ops as $name => $operation) {
    expectWorkflow(isset($called[$name]), true, "$name invoked");
    expectWorkflow((bool)$operation['writeTables'], str_ends_with($operation['scope'], '.write') || str_ends_with($operation['scope'], '.create') || str_ends_with($operation['scope'], '.update'), "$name tables");
}

$negative = static function (string $name, array $args, array $responses, string $error) use ($db, $ops): void {
    $db->responses = $responses;
    try {
        $ops[$name]['handler']($db, $args, 77);
        throw new RuntimeException('Expected rejection');
    } catch (Throwable $e) {
        if (!($e instanceof $error)) {
            throw $e;
        }
    }
    expectWorkflow($db->responses, [], "$name rejects before further SQL");
};
$negative('get_erp_dashboard', ['unknown' => 1], [], InvalidArgumentException::class);
$negative('update_rfq_line', ['line_id' => '1', 'quantity' => 3], [], InvalidArgumentException::class);
$negative('update_rfq_line', ['line_id' => 1, 'quantity' => 3], [false], OutOfBoundsException::class);
expectWorkflow($db->statements[count($db->statements) - 1][1], [1 => 1, 2 => 77], 'verified employee binding');
$negative('add_invoice_item', ['invoice_id' => 1, 'part_id' => 1, 'condition_id' => 1, 'description' => '',
    'quantity' => 2, 'unit_price' => '1.1e4'], [], InvalidArgumentException::class);
$negative('create_rfq', ['rfq_reference' => $rfq, 'company_id' => 1, 'contact_id' => 1, 'priority_id' => 1,
    'payment_term_id' => 1, 'lines' => [$line]], [[], [], [], [], 1], DomainException::class);
$negative('create_rfq', ['rfq_reference' => $rfq, 'company_id' => 1, 'contact_id' => 1, 'priority_id' => 1,
    'payment_term_id' => 1, 'lines' => [$line], 'request_date' => '2026-02-29'], [], InvalidArgumentException::class);
$negative('create_rfq', ['rfq_reference' => '12345678901234567890', 'company_id' => 1, 'contact_id' => 1,
    'priority_id' => 1, 'payment_term_id' => 1, 'lines' => [$line]], [], InvalidArgumentException::class);
$negative('update_shipment_status', ['id' => 1, 'status' => 'delivered'], [['status' => 'preparing']],
    DomainException::class);
$db->transaction = false;
$negative('mark_invoice_paid', ['id' => 1], [], LogicException::class);
echo count($called), " outils workflow exercés en PHP\n";
