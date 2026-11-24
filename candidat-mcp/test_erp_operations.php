<?php
declare(strict_types=1);

class OperationsStatement extends PDOStatement
{
    private array $rows = [];
    public function __construct(private OperationsFixture $fixture, private string $sql) {}
    public function execute(?array $params = null): bool
    {
        $this->rows = $this->fixture->executeSql($this->sql, $params ?? []);
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return array_shift($this->rows) ?? false;
    }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }
    public function fetchColumn(int $column = 0): mixed
    {
        return array_values($this->fetch() ?: [])[$column] ?? false;
    }
}

class OperationsFixture extends PDO
{
    public array $executions = [];
    public function __construct() {}
    public function inTransaction(): bool { return true; }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new OperationsStatement($this, $query);
    }
    public function lastInsertId(?string $name = null): string|false { return '9'; }
    public function executeSql(string $query, array $params): array
    {
        if (substr_count($query, '?') !== count($params)) {
            throw new LogicException('Missing SQL parameters');
        }
        $this->executions[] = [$query, $params];
        if (str_contains($query, 'WHERE Fld_Shipper_Text')) {
            return [];
        }
        if (str_starts_with($query, 'SELECT Fld_Shipper_ID FROM') && $params === [9]) {
            return [['id' => 9]];
        }
        if (str_starts_with($query, 'SELECT statut')) {
            return [['statut' => 'Sales']];
        }
        if (str_starts_with($query, 'SELECT')) {
            return [['synthetic_id' => 1]];
        }
        return [];
    }
}

$db = new OperationsFixture();
$operations = require __DIR__ . '/erp_operations.php';
foreach ($operations as $action => $def) {
    $args = match ($action) {
        'create_shipper' => ['name' => 'Transporteur synthétique'],
        'update_shipper' => ['shipper_id' => 9, 'name' => 'Modifié', 'contact_name' => '', 'contact_phone' => ''],
        'update_yoyamic_user_role' => ['employee_id' => 9, 'expected_role' => 'Sales', 'role' => 'Manager'],
        'list_yoyamic_mcp_audit' => ['employee_id' => 1],
        default => [],
    };
    $result = $def['handler']($db, $args, 1);
    if (!$result) {
        throw new LogicException('No result for ' . $action);
    }
}
if (count($db->executions) < count($operations)) {
    throw new LogicException('Unexpected number of operation handlers');
}
echo count($operations) . " outils transporteurs, audit, administration et référentiels exercés avec PDO simulé.\n";
