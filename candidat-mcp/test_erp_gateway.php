<?php
declare(strict_types=1);

require __DIR__ . '/erp_gateway.php';

final class GatewayStatement extends PDOStatement
{
    private array $result = [];
    public function __construct(private Closure $run) {}
    public function execute(?array $params = null): bool
    {
        $this->result = ($this->run)($params ?? []);
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->result ?: false;
    }
    public function fetchColumn(int $column = 0): mixed
    {
        return array_values($this->result)[$column] ?? false;
    }
    public function closeCursor(): bool { return true; }
}

final class GatewayDatabase extends PDO
{
    public array $employee = ['email' => 'synthetic@example.test', 'statut' => 'Sales'];
    public string $engine = 'InnoDB';
    public array $audit = [];
    public ?array $pending = null;
    public bool $transaction = false;
    public bool $rejectAudit = false;
    public array $events = [];
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new GatewayStatement(function (array $params) use ($query): array {
            return match (true) {
                str_contains($query, 'FROM tbl_Employee') => $this->employee,
                str_contains($query, 'information_schema.TABLES') => [$this->engine],
                str_starts_with($query, 'SELECT input_hash') => $this->audit[json_encode($params)] ?? [],
                str_starts_with($query, 'INSERT INTO mcp_erp_audit') => $this->auditInsert($params),
                str_starts_with($query, 'UPDATE mcp_erp_audit') => $this->auditUpdate($params),
                default => throw new RuntimeException('Unexpected statement: ' . $query),
            };
        });
    }
    private function auditUpdate(array $values): array
    {
        $key = json_encode(array_slice($values, 1));
        $this->audit[$key]['result_json'] = $values[0];
        return [];
    }
    private function auditInsert(array $values): array
    {
        if ($this->rejectAudit) {
            throw new RuntimeException('Synthetic audit failure');
        }
        $this->pending = [json_encode(array_slice($values, 0, 4)),
            ['input_hash' => $values[4], 'result_json' => $values[5]]];
        return [];
    }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->events[] = $query;
        return new GatewayStatement(static fn (array $params): array => []);
    }
    public function exec(string $statement): int|false { $this->events[] = $statement; return 0; }
    public function beginTransaction(): bool { $this->transaction = true; $this->events[] = 'begin'; return true; }
    public function inTransaction(): bool { return $this->transaction; }
    public function rollBack(): bool
    {
        $this->pending = null;
        $this->transaction = false;
        $this->events[] = 'rollback';
        return true;
    }
    public function commit(): bool
    {
        if ($this->pending) {
            $this->audit[$this->pending[0]] = $this->pending[1];
            $this->pending = null;
        }
        $this->transaction = false;
        $this->events[] = 'commit';
        return true;
    }
}

function gateway_check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new LogicException($message);
    }
}

function gateway_reject(callable $operation, string $expected): void
{
    try {
        $operation();
    } catch (Throwable $error) {
        gateway_check(get_class($error) === $expected, 'Unexpected error class: ' . get_class($error));
        return;
    }
    throw new LogicException('Unsuccessful rejection: ' . $expected);
}

$config = ['issuer' => 'https://issuer.example', 'subjects' => [
    'verified|synthetic' => ['employeeId' => 19, 'employeeEmail' => 'synthetic@example.test',
        'enabled' => true, 'operations' => ['write_example', 'read_example']],
]];
$request = ['issuer' => $config['issuer'], 'subject' => 'verified|synthetic', 'operation' => 'write_example',
    'request_id' => 'synthetic-identity-01', 'args' => ['b' => 2, 'a' => 1]];
$binding = erp_actor_binding($config, $request);
foreach (['issuer' => 'https://wrong.example', 'subject' => 'wrong', 'operation' => 'ungranted'] as $key => $value) {
    gateway_reject(fn () => erp_actor_binding($config, array_replace($request, [$key => $value])), ErpAccessDenied::class);
}
$db = new GatewayDatabase();
$invocations = 0;
$handler = static function (PDO $db, array $args, int $id) use (&$invocations): array {
    gateway_check($id === 19, 'Subject resolved to the wrong employee');
    gateway_check($db->inTransaction(), 'Missing transaction');
    $invocations++;
    return ['id' => 42];
};
$operation = ['scope' => 'aerocanada.examples.update', 'roles' => ['Sales'], 'handler' => $handler,
    'writeTables' => ['tbl_Example']];
foreach ([['email' => 'other@example.test', 'statut' => 'Sales'], ['email' => 'synthetic@example.test', 'statut' => 'User'], []] as $identity) {
    $db->employee = $identity;
    gateway_reject(fn () => erp_execute($db, $binding, $request, $operation), ErpAccessDenied::class);
}
gateway_check($invocations === 0, 'Unauthorized handler execution');
$db->employee = ['email' => 'synthetic@example.test', 'statut' => 'Sales'];
$db->engine = 'MyISAM';
gateway_reject(fn () => erp_execute($db, $binding, $request, $operation), RuntimeException::class);
gateway_check($invocations === 0, 'Nontransactional handler execution');
$db->engine = 'InnoDB';
gateway_check(erp_execute($db, $binding, $request, $operation) === ['id' => 42], 'Mutation result');
gateway_check(count($db->audit) === 1 && end($db->events) === 'commit', 'Atomic audit missing');
gateway_check(erp_execute($db, $binding, array_replace($request, ['args' => ['a' => 1, 'b' => 2]]), $operation) === ['id' => 42], 'Idempotent replay');
gateway_check($invocations === 1, 'Idempotent replay ran twice');
gateway_reject(fn () => erp_execute($db, $binding, array_replace($request, ['args' => ['a' => 3]]), $operation), DomainException::class);
$db->rejectAudit = true;
gateway_reject(fn () => erp_execute($db, $binding, array_replace($request, ['request_id' => 'synthetic-identity-02']), $operation), RuntimeException::class);
gateway_check(count($db->audit) === 1 && end($db->events) === 'rollback', 'Audit failure must roll back');
$read = array_replace($operation, ['scope' => 'aerocanada.examples.read']);
erp_execute($db, $binding, $request, $read);
gateway_check(in_array('SET TRANSACTION READ ONLY', $db->events, true) && end($db->events) === 'rollback', 'Read transaction not enforced');
$db->rejectAudit = false;
$posted = 0;
$reconciled = 0;
$external = array_replace($operation, ['externalEffect' => 'draft',
    'handler' => static function (PDO $db, array $args, array $binding, string $message) use (&$posted): array {
        return ['execute' => static function () use ($db, &$posted): array {
            gateway_check(!$db->inTransaction(), 'External call must follow a durable claim');
            $posted++;
            throw new RuntimeException('Synthetic interruption after POST');
        }];
    },
    'reconcile' => static function (array $args, array $binding, string $message) use (&$reconciled): array {
        $reconciled++;
        return ['draft_id' => 'synthetic-draft'];
    }]);
$draftRequest = array_replace($request, ['request_id' => 'synthetic-identity-03']);
gateway_reject(fn () => erp_execute($db, $binding, $draftRequest, $external), RuntimeException::class);
gateway_check(erp_execute($db, $binding, $draftRequest, $external)['draft_id'] === 'synthetic-draft', 'Draft reconciliation');
gateway_check(erp_execute($db, $binding, $draftRequest, $external)['draft_id'] === 'synthetic-draft', 'Draft replay');
gateway_check($posted === 1 && $reconciled === 1, 'Draft POST repeated after an uncertain response');

echo "Passerelle PHP : identité, rôle, refus, idempotence, contrôle transactionnel, audit et rollback vérifiés.\n";
