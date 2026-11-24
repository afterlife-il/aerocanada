<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

final class CompanyFixtureDb extends PDO
{
    public array $calls = [];
    public array $results = [];
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new CompanyFixtureStatement($this, $query);
    }
    public function lastInsertId(?string $name = null): string|false
    {
        return '12';
    }
}

final class CompanyFixtureStatement extends PDOStatement
{
    private array $parameters = [];
    private mixed $result = false;
    public function __construct(private CompanyFixtureDb $db, private string $sql) {}
    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->parameters[$param] = $value;
        return true;
    }
    public function execute(?array $params = null): bool
    {
        $this->db->calls[] = [$this->sql, array_values($this->parameters)];
        $this->result = $this->db->results === [] ? false : array_shift($this->db->results);
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->result;
    }
    public function fetchColumn(int $column = 0): mixed
    {
        return $this->result;
    }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->result ?: [];
    }
}

function checkCompanies(bool $passed, string $message): void
{
    if (!$passed) {
        throw new RuntimeException($message);
    }
}

$operations = require __DIR__ . '/erp_companies.php';
$calls = [];
$run = static function (string $name, array $input, array $queryResults, array $expected) use ($operations, &$calls): void {
    $db = new CompanyFixtureDb();
    $db->results = $queryResults;
    $operation = $operations[$name];
    checkCompanies($operation['handler']($db, $input, 42) === $expected, "$name result");
    checkCompanies($db->results === [], "$name did not consume the expected fixture");
    if (str_ends_with($operation['scope'], '.create') || str_ends_with($operation['scope'], '.update')) {
        checkCompanies(count($operation['writeTables']) === 1 && !in_array('User', $operation['roles'], true), "$name roles and write tables");
    }
    $calls[$name] = $db->calls;
};
$rows = [['id' => 7], ['id' => 9]];
$company = ['company_id' => 7];
$address = $company + ['address_id' => 4];
$contact = $company + ['contact_id' => 5];
$active = ['status' => 'Available'];

$run('search_companies', ['query' => 'A_%!', 'country' => 'Canada', 'limit' => 1], [$rows],
    ['items' => [['id' => 7]], 'next_after_id' => 7]);
checkCompanies($calls['search_companies'][0][1] === [0, 'Available', '%A!_!%!!%', '%A!_!%!!%', 'Canada', 2], 'search parameters');
$run('get_company', $company, [['id' => 7]], ['id' => 7]);
$run('lookup_company_cage_code', ['cage_code' => 'AB123'], [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('create_company', ['name' => "  Test d'Aviation  "], [false, false], ['id' => 12]);
checkCompanies(in_array(42, $calls['create_company'][1][1], true), 'verified actor on company');
$run('update_company', $company + ['cage_code' => 'AB123'], [$active, false], ['id' => 7]);
$run('get_company_payment_terms', $company, [['customer_term_id' => 2]], ['customer_term_id' => 2]);
$run('update_company_payment_terms', $company + ['customer_term_id' => 2, 'customer_amount' => '110.25', 'customer_currency_id' => 3],
    [$active, ['id' => 2], ['id' => 3], false], ['id' => 7]);
$run('archive_company', $company, [$active, false], ['id' => 7, 'status' => 'archive']);
$run('list_company_addresses', $company, [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('create_company_address', $company + ['company_type_id' => 2, 'address_type_id' => 3, 'city' => 'Montréal'],
    [$active, ['id' => 2], ['id' => 3], false], ['id' => 12]);
$run('update_company_address', $address + ['city' => 'Québec'], [$active, ['id' => 4], false], ['id' => 4]);
$run('list_company_fleet', $company, [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('add_company_aircraft', $company + ['region_id' => 1, 'aircraft_id' => 2, 'serial_number' => 'SN-12'],
    [$active, ['id' => 1], ['id' => 2], false], ['id' => 12]);
$run('update_company_aircraft', $company + ['fleet_id' => 4, 'registration' => 'C-TEST'],
    [$active, ['id' => 4], false], ['id' => 4]);
$run('list_company_forwarders', $company, [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('list_company_competitors', $company, [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('add_company_competitor', $company + ['competitor_id' => 9], [$active, $active, false, false], ['id' => 12]);
$run('search_contacts', $company + ['query' => 'Test'], [$rows], ['items' => $rows, 'next_after_id' => null]);
$run('get_contact', $contact, [['id' => 5]], ['id' => 5]);
$run('create_contact', $company + ['name' => 'Test', 'email' => 'test@example.invalid'], [$active, false, false], ['id' => 12] + $company);
checkCompanies(in_array(42, $calls['create_contact'][2][1], true), 'verified actor on contact');
$run('update_contact', $contact + ['phone' => '+1 555 0100'], [$active, ['id' => 5], false], ['id' => 5] + $company);
$run('archive_contact', $contact, [$active, ['id' => 5], false], ['id' => 5, 'status' => 'none']);
$run('restore_contact', $contact, [$active, ['id' => 5], false], ['id' => 5, 'status' => 'Available']);

foreach ([
    ['add_company_competitor', $company + ['competitor_id' => 7], 42, [], InvalidArgumentException::class],
    ['update_company_payment_terms', $company + ['customer_amount' => '12e4'], 42, [], InvalidArgumentException::class],
    ['create_company', ['name' => ''], 42, [], InvalidArgumentException::class],
    ['create_company', ['name' => 'Test', 'employee_id' => 7], 42, [], InvalidArgumentException::class],
    ['create_company', ['name' => 'Test'], 0, [], LogicException::class],
    ['create_company', ['name' => 'Existing'], 42, [7], DomainException::class],
    ['create_contact', $company + ['name' => 'Test', 'email' => 'invalid'], 42, [], InvalidArgumentException::class],
    ['update_contact', $contact + ['phone' => '1234'], 42, [$active, false], OutOfBoundsException::class],
    ['update_company_address', $address + ['city' => 'Test'], 42, [$active, false], OutOfBoundsException::class],
] as [$name, $input, $employee, $results, $expectedException]) {
    $db = new CompanyFixtureDb();
    $db->results = $results;
    try {
        $operations[$name]['handler']($db, $input, $employee);
        throw new RuntimeException("$name did not reject invalid context");
    } catch (Throwable $error) {
        checkCompanies($error instanceof $expectedException, "$name wrong exception: " . $error::class);
        checkCompanies(count($db->calls) === count($results), "$name attempted unexpected SQL after validation failure");
    }
}

if (($argv[1] ?? '') === '--emit-sql') {
    // Run only against a synthetic MariaDB schema. Values are fixture-generated.
    echo "START TRANSACTION;\n";
    foreach ($calls as $statements) {
        foreach ($statements as [$sql, $parameters]) {
            foreach ($parameters as $parameter) {
                $literal = is_int($parameter) ? (string) $parameter : "'" . str_replace("'", "''", $parameter) . "'";
                $offset = strpos($sql, '?');
                $sql = substr_replace($sql, $literal, $offset, 1);
            }
            echo "$sql;\n";
        }
    }
    echo <<<'SQL'
COMMIT;
CREATE TABLE company_assertions (ok INT NOT NULL CHECK (ok = 1));
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company
    WHERE Fld_Company_ID = 12 AND Fld_Company_Name = 'Test d''Aviation' AND aci_contact = 42;
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company
    WHERE Fld_Company_ID = 12 AND Company_Old_Id = 0 AND Fld_Company_Rating_ID = 0
        AND `delete` = '' AND logocompany = '' AND customer_payment_term_id = 0 AND aci_payment_term_id = 0;
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company
    WHERE Fld_Company_ID = 7 AND status = 'archive' AND customer_payment_term_amount = 110.25
        AND customer_payment_term_id = 2 AND customer_payment_term_currencyid = 3;
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tbl_Company_Details
    WHERE id_tbl_company_Details = 4 AND Fld_Company_City = 'Québec';
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company_contact
    WHERE id_company_contact = 5 AND status = 'Available' AND Fld_Contact_Phone = '+1 555 0100';
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tbl_Fleet WHERE id_Fleet = 4 AND immat = 'C-TEST';
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tbl_Competitor
    WHERE Fld_Linked_ID = 12 AND Fld_Company_ID = 7 AND Fld_Competitor_ID = 9;
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company_contact
    WHERE id_company_contact = 12 AND aci_contact = 42 AND Fld_Contact_Email = 'test@example.invalid';
INSERT INTO company_assertions SELECT COUNT(*) = 1 FROM tb_company_contact
    WHERE id_company_contact = 12 AND Fld_Linked_ID = 0 AND Company_Old_Id = 0
        AND Fld_Contact_Fax = '' AND Fld_Contact_Division_ID = 0 AND Fld_Contact_Remark = '';
SQL;
    echo "\n";
} else {
    echo count($calls) . " company/contact handlers and 9 rejection cases passed.\n";
}
