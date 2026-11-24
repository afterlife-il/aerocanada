<?php
declare(strict_types=1);

$operations = require __DIR__ . '/erp_inventory.php';

final class InventoryTestStatement extends PDOStatement
{
    private array $bound = [];
    private mixed $answer = null;

    public function __construct(private InventoryTestPdo $db, private string $sql) {}

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        if (!is_int($value) && $type !== PDO::PARAM_STR || is_int($value) && $type !== PDO::PARAM_INT) {
            throw new RuntimeException('Incorrect bind type');
        }
        $this->bound[$param] = $value;
        return true;
    }

    public function execute(?array $params = null): bool
    {
        $this->db->trace[] = [$this->sql, $this->bound];
        $this->answer = $this->db->answers ? array_shift($this->db->answers) : null;
        if ($this->answer === null) {
            throw new RuntimeException('Unexpected query: ' . $this->sql);
        }
        if (str_starts_with($this->sql, 'INSERT INTO')) {
            $this->db->insertId = (int)$this->answer;
        }
        return true;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->answer;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->answer;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->answer;
    }
}

final class InventoryTestPdo extends PDO
{
    public array $answers = [];
    public array $trace = [];
    public int $insertId = 0;
    public bool $transaction = true;

    public function __construct() {}

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new InventoryTestStatement($this, $query);
    }

    public function inTransaction(): bool
    {
        return $this->transaction;
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return (string)$this->insertId;
    }
}

function verify(mixed $actual, mixed $expected, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException($label . ': ' . var_export($actual, true));
    }
}

function expectError(callable $method, string $class): void
{
    try {
        $method();
    } catch (Throwable $error) {
        if ($error instanceof $class) {
            return;
        }
        throw $error;
    }
    throw new RuntimeException('Expected ' . $class);
}

$db = new InventoryTestPdo();

$call = static function (string $name, array $args, array $answers) use ($db, $operations): array {
    $db->answers = $answers;
    $result = $operations[$name]['handler']($db, $args, 9);
    verify(count($db->answers), 0, 'Expected SQL calls for ' . $name);
    return $result;
};

$names = [];
foreach ($operations as $name => $operation) {
    $names[] = $name;
    verify(is_callable($operation['handler']), true, $name);
    verify($operation['inputSchema']['additionalProperties'], false, $name);
    verify((bool)$operation['writeTables'], str_ends_with($operation['scope'], '.write') || str_ends_with($operation['scope'], '.create') || str_ends_with($operation['scope'], '.update'), $name);
}

$record = ['part_id' => 1];
verify($call('search_parts', ['query' => 'ALT_%!', 'limit' => 1], [[$record, ['part_id' => 2]]]),
    ['items' => [$record], 'next_offset' => 1], 'literal search pagination');
verify($db->trace[0][1][2], '%ALT!_!%!!%', 'literal LIKE escapes reserved characters');
verify($call('search_parts', ['query' => 'SYNTH-PN', 'exact' => true], [[$record]]),
    ['items' => [$record], 'next_offset' => null], 'exact search');
verify($call('get_part', ['part_id' => 1], [$record]), $record, 'part get');
verify($call('list_part_documents', ['part_id' => 1], [$record, [['document_id' => 1]]]),
    ['items' => [['document_id' => 1]], 'next_offset' => null], 'document listing');
verify($call('create_part', ['part_number' => 'SYNTH-CREATED', 'manufacturer_id' => 1], [false, 1, 2]),
    ['part_id' => 2], 'part create');
verify(in_array(9, end($db->trace)[1], true), true, 'use verified employee as creator');
verify($call('create_parts', ['parts' => [['part_number' => 'SYNTH-BATCH1'], ['part_number' => 'SYNTH-BATCH2']]],
    [false, 3, false, 4]), ['part_ids' => [3, 4]], 'bulk part create');
verify($call('update_part', ['part_id' => 2, 'description' => 'Updated description', 'wanted' => true], [$record, 1]),
    ['part_id' => 2], 'update part');
verify($call('archive_part', ['part_id' => 3], [$record, 1]), ['part_id' => 3, 'status' => 'archive'], 'archive part');

verify($call('search_inventory', ['kind' => 'external', 'owner_id' => 1, 'part_number' => 'SYNTH-PN'], [[]]),
    ['items' => [], 'next_offset' => null], 'external search');
verify($call('get_inventory_item', ['kind' => 'internal', 'stock_id' => 1], [['stock_id' => 1]]),
    ['stock_id' => 1], 'internal detail');
verify($call('create_internal_stock', ['part_id' => 1, 'owner_id' => 1, 'quantity' => 2, 'status_id' => 1, 'serial_number' => 'S1'],
    [$record, 1, 1, 1, false, 2]), ['kind' => 'internal', 'stock_id' => 2], 'internal create');
verify($call('update_internal_stock', ['stock_id' => 2, 'quantity' => 3, 'warehouse_location_id' => 1],
    [['Fld_Part_ID' => 1], $record, ['Fld_Part_ID' => 1], 1, 1]), ['kind' => 'internal', 'stock_id' => 2], 'internal update');
verify($call('create_external_stock', ['part_id' => 1, 'owner_id' => 1, 'quantity' => 7, 'serial_number' => 'E1'],
    [$record, 1, 1, false, 1]), ['kind' => 'external', 'stock_id' => 1], 'external create');
verify($call('update_external_stock', ['stock_id' => 1, 'entry_date' => '2026-11-23', 'tag_company_id' => 1],
    [['Fld_Part_ID' => 1], $record, ['Fld_Part_ID' => 1], 1, 1]), ['kind' => 'external', 'stock_id' => 1], 'external update');

foreach (['aircraft', 'condition', 'release', 'status', 'location', 'warehouse_location', 'currency'] as $kind) {
    $call('list_inventory_lookups', ['kind' => $kind], [[['id' => 1, 'label' => 'Synthetic']]]);
}

$call('list_wanted_parts', [], [[$record]]);
$call('rank_parts', ['query' => 'SYNTH'], [[['part_number' => 'SYNTH-PN', 'rfq_line_count' => 2]]]);
$call('list_part_prices', ['part_number' => 'SYNTH-PN', 'company_id' => 1], [[['price_entry_id' => 1]]]);

$positiveTrace = $db->trace;
$priorCount = count($db->trace);
foreach ([
    ['create_part', ['part_number' => 'SYNTH', 'Employee_ID' => 1]],
    ['update_internal_stock', ['stock_id' => 1, 'quantity' => -1]],
    ['update_external_stock', ['stock_id' => 1, 'entry_date' => '2026-02-30']],
    ['get_part', ['part_id' => '1']],
    ['search_inventory', ['kind' => 'unrestricted']],
] as [$name, $args]) {
    expectError(static fn() => $call($name, $args, []), InvalidArgumentException::class);
}
verify(count($db->trace), $priorCount, 'Invalid inputs must not reach SQL');

expectError(static fn() => $operations['create_part']['handler']($db, ['part_number' => 'SYNTH'], 0), LogicException::class);
expectError(static fn() => $call('create_part', ['part_number' => 'SYNTH-PN'], [1]), DomainException::class);
expectError(static fn() => $call('get_part', ['part_id' => 99], [false]), OutOfBoundsException::class);
expectError(static fn() => $call('create_internal_stock',
    ['part_id' => 1, 'owner_id' => 1, 'quantity' => 2, 'status_id' => 1, 'serial_number' => 'S1'],
    [$record, 1, 1, 1, 2]), DomainException::class);
expectError(static fn() => $call('create_external_stock', ['part_id' => 1, 'owner_id' => 999, 'quantity' => 1], [$record, 1, false]), DomainException::class);
$db->transaction = false;
expectError(static fn() => $call('create_part', ['part_number' => 'SYNTH'], []), LogicException::class);
verify(count($names), 17, 'all inventory tools covered');

if (($argv[1] ?? '') === '--sql') {
    foreach ($positiveTrace as [$sql, $parameters]) {
        $pieces = explode('?', $sql);
        verify(count($pieces), count($parameters) + 1, 'SQL placeholder count');
        $rendered = array_shift($pieces);
        foreach ($parameters as $value) {
            $rendered .= (is_int($value) ? $value : "CONVERT(X'" . bin2hex($value) . "' USING utf8mb4)") . array_shift($pieces);
        }
        echo $rendered, ";\n";
    }
} else {
    echo "17 catalog and stock operations passed isolated handler tests\n";
}
