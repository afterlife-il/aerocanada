<?php
declare(strict_types=1);

namespace AeroCanada\Mcp\Inventory;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;
use PDO;
use PDOStatement;

function statement(PDO $db, string $sql, array $values = []): PDOStatement
{
    $query = $db->prepare($sql);
    foreach (array_values($values) as $index => $value) {
        $query->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $query->execute();
    return $query;
}

function one(PDO $db, string $sql, array $values = []): array
{
    $row = statement($db, $sql, $values)->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        throw new OutOfBoundsException('Record not found');
    }
    return $row;
}

function page(PDO $db, string $sql, array $values, array $args): array
{
    $limit = $args['limit'] ?? 25;
    $offset = $args['offset'] ?? 0;
    $rows = statement($db, "$sql LIMIT ? OFFSET ?", [...$values, $limit + 1, $offset])->fetchAll(PDO::FETCH_ASSOC);
    $more = count($rows) > $limit;
    return ['items' => array_slice($rows, 0, $limit), 'next_offset' => $more ? $offset + $limit : null];
}

function literalLike(string $term): string
{
    return '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}

function textField(int $length, bool $optionalEmpty = false): array
{
    return ['type' => 'string', 'minLength' => $optionalEmpty ? 0 : 1, 'maxLength' => $length];
}

function enumField(string ...$allowed): array
{
    return ['type' => 'string', 'enum' => $allowed];
}

function integerField(int $min = 1, int $max = 2147483647): array
{
    return ['type' => 'integer', 'minimum' => $min, 'maximum' => $max];
}

function decimalField(): array
{
    return ['type' => 'string', 'maxLength' => 14, 'pattern' => '^[0-9]{1,10}(\.[0-9]{1,3})?$'];
}

function objectField(array $properties, array $required = []): array
{
    return ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
}

function validate($value, array $field, string $name): void
{
    switch ($field['type']) {
        case 'object':
            if (!is_array($value) || ($value !== [] && array_is_list($value))) {
                throw new InvalidArgumentException("$name must be an object");
            }
            foreach ($field['required'] as $required) {
                if (!array_key_exists($required, $value)) {
                    throw new InvalidArgumentException("Required argument missing: $name.$required");
                }
            }
            foreach ($value as $key => $item) {
                if (!isset($field['properties'][$key])) {
                    throw new InvalidArgumentException("Unknown field in $name");
                }
                validate($item, $field['properties'][$key], "$name.$key");
            }
            return;
        case 'array':
            if (!is_array($value) || !array_is_list($value) || count($value) < $field['minItems'] || count($value) > $field['maxItems']) {
                throw new InvalidArgumentException("$name must be a nonempty bounded list");
            }
            foreach ($value as $item) {
                validate($item, $field['items'], $name);
            }
            return;
        case 'integer':
            if (!is_int($value) || $value < $field['minimum'] || $value > $field['maximum']) {
                throw new InvalidArgumentException("$name is outside the integer range");
            }
            return;
        case 'boolean':
            if (!is_bool($value)) {
                throw new InvalidArgumentException("$name must be a boolean");
            }
            return;
        case 'string':
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                throw new InvalidArgumentException("$name must be UTF-8 text");
            }
            if (isset($field['enum']) && !in_array($value, $field['enum'], true)) {
                throw new InvalidArgumentException("$name is not an allowed value");
            }
            $length = mb_strlen($value);
            if ($length < ($field['minLength'] ?? 0) || $length > ($field['maxLength'] ?? 2147483647)) {
                throw new InvalidArgumentException("$name is outside the text length limit");
            }
            if (isset($field['pattern']) && !preg_match('~' . $field['pattern'] . '~D', $value)) {
                throw new InvalidArgumentException("$name does not match the format");
            }
            if (($field['format'] ?? null) === 'date') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    throw new InvalidArgumentException("$name must be a date in YYYY-MM-DD format");
                }
            }
            return;
    }
    throw new \LogicException('Unsupported inventory field type');
}

function operation(string $scope, array $roles, string $description, array $schema, callable $handler, array $writeTables = []): array
{
    return [
        'scope' => $scope,
        'roles' => $roles,
        'description' => $description,
        'inputSchema' => $schema,
        'writeTables' => $writeTables,
        'handler' => static function (PDO $db, array $args, int $employeeId) use ($schema, $handler, $writeTables): array {
            if ($employeeId <= 0) {
                throw new \LogicException('Verified employee identity required');
            }
            validate($args, $schema, 'arguments');
            if ($writeTables && !$db->inTransaction()) {
                throw new \LogicException('Inventory mutation requires a transaction');
            }
            return $handler($db, $args, $employeeId);
        },
    ];
}

function localDate(string $format): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Toronto')))->format($format);
}

function reference(PDO $db, string $table, string $key, int $id): void
{
    if ($id > 0 && statement($db, "SELECT 1 FROM `$table` WHERE `$key` = ?", [$id])->fetchColumn() === false) {
        throw new DomainException('Unknown reference: ' . $key);
    }
}

function columns(array $args, array $mapping): array
{
    $data = [];
    foreach ($mapping as $input => $column) {
        if (array_key_exists($input, $args)) {
            $data[$column] = is_string($args[$input]) ? trim($args[$input]) : $args[$input];
        }
    }
    return $data;
}

function insert(PDO $db, string $table, array $data): int
{
    $columns = implode('`, `', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    statement($db, "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)", $data);
    return (int)$db->lastInsertId();
}

function update(PDO $db, string $table, string $key, int $id, array $data): void
{
    if (!$data) {
        throw new InvalidArgumentException('Provide at least one writable field');
    }
    $set = implode(', ', array_map(static fn(string $column): string => "`$column` = ?", array_keys($data)));
    statement($db, "UPDATE `$table` SET $set WHERE `$key` = ?", [...array_values($data), $id]);
}

const PART_TABLE = 'tbl_Parts';
const PART_KEY = 'Fld_Part_ID';

const PART_FIELDS = [
    'part_number' => 'Fld_Part_Nbr',
    'description' => 'Fld_Part_Desc',
    'manufacturer_id' => 'Fld_Part_MFG',
    'aircraft_id' => 'Fld_AC_ID',
    'list_price' => 'Fld_Part_List_Price',
    'currency_id' => 'Fld_Part_Price_Currency_ID',
    'remark' => 'Fld_Remark',
    'alternate_part_numbers' => 'alt_pn',
    'ata_chapter' => 'ata_chapter',
    'cage_code' => 'cage_code',
    'minimum_order_quantity' => 'moq',
    'oem_lead_time' => 'oem_lead_time',
    'core_value' => 'core_value',
    'core_currency_id' => 'id_currency_core_value',
];

const PART_QUERY = 'SELECT p.Fld_Part_ID AS part_id, p.Fld_Part_Nbr AS part_number,
    p.Fld_Part_Desc AS description, p.Fld_Part_MFG AS manufacturer_id, c.Fld_Company_Name AS manufacturer,
    p.Fld_AC_ID AS aircraft_id, a.Fld_AC_Model AS aircraft_model, p.alt_pn AS alternate_part_numbers,
    p.ata_chapter, p.cage_code, p.Fld_Part_List_Price AS list_price, p.Fld_Part_Price_Currency_ID AS currency_id,
    cur.Fld_Currency_Text AS currency, p.Fld_Remark AS remark, p.status,
    p.wanted, p.moq AS minimum_order_quantity, p.oem_lead_time, p.core_value, p.id_currency_core_value AS core_currency_id
    FROM tbl_Parts p
    LEFT JOIN tb_company c ON c.Fld_Company_ID = p.Fld_Part_MFG
    LEFT JOIN tbl_Aircraft a ON a.Fld_AC_ID = p.Fld_AC_ID
    LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = p.Fld_Part_Price_Currency_ID';

function partsSearch(PDO $db, array $args, bool $wantedOnly = false): array
{
    $where = ["p.status = ?"];
    $values = [$args['status'] ?? 'Available'];
    if ($wantedOnly) {
        $where[] = 'p.wanted = 1';
    }
    foreach (['aircraft_id' => 'Fld_AC_ID', 'manufacturer_id' => 'Fld_Part_MFG'] as $filter => $column) {
        if (isset($args[$filter])) {
            $where[] = "p.$column = ?";
            $values[] = $args[$filter];
        }
    }
    if (isset($args['query'])) {
        $term = trim($args['query']);
        if ($term === '') {
            throw new InvalidArgumentException('Provide a nonblank search term');
        }
        if (($args['exact'] ?? false) === true) {
            $where[] = 'p.Fld_Part_Nbr = ?';
            $values[] = $term;
        } else {
            $where[] = "(p.Fld_Part_Nbr LIKE ? ESCAPE '!' OR p.Fld_Part_Desc LIKE ? ESCAPE '!'
                OR p.alt_pn LIKE ? ESCAPE '!' OR p.ata_chapter LIKE ? ESCAPE '!')";
            array_push($values, ...array_fill(0, 4, literalLike($term)));
        }
    }
    $where = implode(' AND ', $where);
    return page($db, PART_QUERY . " WHERE $where ORDER BY p.Fld_Part_Nbr, p.Fld_Part_ID", $values, $args);
}

function partValues(PDO $db, array $args, ?int $existingId = null): array
{
    $data = columns($args, PART_FIELDS);
    if (isset($args['wanted'])) {
        $data['wanted'] = (int)$args['wanted'];
    }
    if (isset($data['Fld_Part_Nbr'])) {
        if ($data['Fld_Part_Nbr'] === '' || preg_match('/[\r\n\t]/', $data['Fld_Part_Nbr'])) {
            throw new InvalidArgumentException('Part number is required');
        }
        $query = 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr = ?';
        $params = [$data['Fld_Part_Nbr']];
        if ($existingId !== null) {
            $query .= ' AND Fld_Part_ID <> ?';
            $params[] = $existingId;
        }
        if (statement($db, "$query LIMIT 1 FOR UPDATE", $params)->fetchColumn() !== false) {
            throw new DomainException('That part number already exists');
        }
    }
    foreach ([
        'Fld_Part_MFG' => ['tb_company', 'Fld_Company_ID'],
        'Fld_AC_ID' => ['tbl_Aircraft', 'Fld_AC_ID'],
        'Fld_Part_Price_Currency_ID' => ['tbl_Currency', 'Fld_Currency_ID'],
        'id_currency_core_value' => ['tbl_Currency', 'Fld_Currency_ID'],
    ] as $column => [$table, $key]) {
        if (isset($data[$column])) {
            reference($db, $table, $key, $data[$column]);
        }
    }
    return $data;
}

function partCreate(PDO $db, array $args, int $employeeId): array
{
    $data = partValues($db, $args) + [
        'status' => 'Available', 'Fld_Add_PN_Date' => localDate('Y-m-d H:i:s'), 'aci_contact_entry' => $employeeId,
    ];
    return ['part_id' => insert($db, PART_TABLE, $data)];
}

function partUpdate(PDO $db, array $args): array
{
    $id = $args['part_id'];
    one($db, 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_ID = ? FOR UPDATE', [$id]);
    update($db, PART_TABLE, PART_KEY, $id, partValues($db, $args, $id));
    return ['part_id' => $id];
}

function partArchive(PDO $db, array $args): array
{
    $id = $args['part_id'];
    one($db, 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_ID = ? FOR UPDATE', [$id]);
    update($db, PART_TABLE, PART_KEY, $id, ['status' => 'archive']);
    return ['part_id' => $id, 'status' => 'archive'];
}

const STOCK_FIELDS = [
    'part_id' => 'Fld_Part_ID',
    'serial_number' => 'Fld_Part_SN',
    'supplier_id' => 'Fld_Supplier_ID',
    'owner_id' => 'Fld_Owner_ID',
    'condition_id' => 'Fld_Condition_ID',
    'release_id' => 'Fld_Release_ID',
    'tag_company_id' => 'Fld_Tag_Info_ID',
    'traceability_company_id' => 'Fld_Traceability_ID',
    'entry_date' => 'Fld_Entry_Date',
    'tag_date' => 'Fld_Tag_Date',
    'quantity' => 'Fld_Qty',
    'physical_stock' => 'Fld_Physical_Stock',
    'unit_price' => 'Fld_Part_Price',
    'currency_id' => 'Fld_Price_Currency_ID',
    'warehouse_location' => 'Fld_Warehouse_Location',
    'location_id' => 'Fld_Stock_Location_ID',
    'status_id' => 'Fld_Status_ID',
    'external_location_id' => 'Fld_External_Location',
    'warehouse_location_id' => 'Fld_Warehouse_Location_ID',
    'minimum_quantity' => 'Fld_Min_Qty',
    'status_date' => 'Fld_Status_Date',
    'stock_remark' => 'Fld_Stock_Remark',
    'sales_remark' => 'Fld_Sales_Remark',
    'shelf_life_limit' => 'Fld_Shelf_Life_Limit',
    'publish' => 'Fld_Publish',
];

function stockLocation(string $kind): array
{
    return $kind === 'internal'
        ? ['tbl_Stock', 'Fld_Stock_ID', 'Fld_Owner_ID']
        : ['tbl_Stock_external', 'Fld_Stock_externe_ID', 'Fld_Company_ID'];
}

function stockSelect(string $kind): string
{
    [$table, $key, $owner] = stockLocation($kind);
    $internal = $kind === 'internal'
        ? 's.Fld_Status_ID AS status_id, s.Fld_Status_Date AS status_date, s.Fld_Stock_Location_ID AS location_id, s.Fld_Shelf_Life_Limit AS shelf_life_limit,
            s.Fld_Warehouse_Location_ID AS warehouse_location_id, s.Fld_External_Location AS external_location_id, s.Fld_Min_Qty AS minimum_quantity,'
        : 's.status,';
    return "SELECT s.`$key` AS stock_id, s.Fld_Part_ID AS part_id, p.Fld_Part_Nbr AS part_number,
        s.Fld_Part_SN AS serial_number, s.Fld_Qty AS quantity, s.Fld_Physical_Stock AS physical_stock,
        s.Fld_Condition_ID AS condition_id, co.Fld_Condition_Text AS condition_label,
        s.Fld_Release_ID AS release_id, s.Fld_Tag_Info_ID AS tag_company_id, s.Fld_Tag_Date AS tag_date,
        s.Fld_Traceability_ID AS traceability_company_id, s.Fld_Supplier_ID AS supplier_id, s.`$owner` AS owner_id,
        s.Fld_Warehouse_Location AS warehouse_location, s.Fld_Entry_Date AS entry_date, $internal
        s.Fld_Part_Price AS unit_price, s.Fld_Price_Currency_ID AS currency_id, c.Fld_Currency_Text AS currency,
        s.Fld_Stock_Remark AS stock_remark, s.Fld_Sales_Remark AS sales_remark
        FROM `$table` s
        LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = s.Fld_Part_ID
        LEFT JOIN tbl_Condition co ON co.Fld_Condition_ID = s.Fld_Condition_ID
        LEFT JOIN tbl_Currency c ON c.Fld_Currency_ID = s.Fld_Price_Currency_ID";
}

function stockSearch(PDO $db, array $args): array
{
    [, $key, $owner] = stockLocation($args['kind']);
    $where = [];
    $values = [];
    foreach (['part_id' => 'Fld_Part_ID', 'owner_id' => $owner, 'condition_id' => 'Fld_Condition_ID'] as $filter => $column) {
        if (isset($args[$filter])) {
            $where[] = "s.`$column` = ?";
            $values[] = $args[$filter];
        }
    }
    foreach (['part_number' => 'p.Fld_Part_Nbr', 'serial_number' => 's.Fld_Part_SN'] as $filter => $column) {
        if (isset($args[$filter])) {
            $where[] = "$column = ?";
            $values[] = trim($args[$filter]);
        }
    }
    $criterion = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    return page($db, stockSelect($args['kind']) . "$criterion ORDER BY s.`$key` DESC", $values, $args);
}

function stockValues(PDO $db, string $kind, array $args): array
{
    $mapping = STOCK_FIELDS;
    if ($kind === 'external') {
        $mapping['owner_id'] = 'Fld_Company_ID';
    }
    $data = columns($args, $mapping);
    foreach ([
        'Fld_Part_ID' => [PART_TABLE, PART_KEY],
        'Fld_Company_ID' => ['tb_company', 'Fld_Company_ID'],
        'Fld_Supplier_ID' => ['tb_company', 'Fld_Company_ID'],
        'Fld_Owner_ID' => ['tb_company', 'Fld_Company_ID'],
        'Fld_Tag_Info_ID' => ['tb_company', 'Fld_Company_ID'],
        'Fld_Traceability_ID' => ['tb_company', 'Fld_Company_ID'],
        'Fld_Condition_ID' => ['tbl_Condition', 'Fld_Condition_ID'],
        'Fld_Release_ID' => ['tbl_Release', 'Fld_Release_ID'],
        'Fld_Price_Currency_ID' => ['tbl_Currency', 'Fld_Currency_ID'],
        'Fld_Status_ID' => ['tbl_Status', 'Fld_Status_ID'],
        'Fld_Stock_Location_ID' => ['tbl_Stock_Location', 'Fld_Stock_Location_ID'],
        'Fld_External_Location' => ['tbl_Stock_Location', 'Fld_Stock_Location_ID'],
        'Fld_Warehouse_Location_ID' => ['tbl_Warehouse_Location', 'Id'],
    ] as $column => [$table, $key]) {
        if (isset($data[$column])) {
            reference($db, $table, $key, $data[$column]);
        }
    }
    return $data;
}

function requireUniqueSerial(PDO $db, string $kind, int $part, int $ownerId, string $serial, ?int $existing = null): void
{
    if ($serial === '') {
        return;
    }
    [$table, $key, $owner] = stockLocation($kind);
    $where = "Fld_Part_ID = ? AND `$owner` = ? AND Fld_Part_SN = ?";
    $params = [$part, $ownerId, $serial];
    if ($existing !== null) {
        $where .= " AND `$key` <> ?";
        $params[] = $existing;
    }
    if (statement($db, "SELECT `$key` FROM `$table` WHERE $where LIMIT 1 FOR UPDATE", $params)->fetchColumn() !== false) {
        throw new DomainException('Stock with that part, serial and owner already exists');
    }
}

function stockCreate(PDO $db, string $kind, array $args): array
{
    [$table, , $owner] = stockLocation($kind);
    one($db, 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_ID = ? FOR UPDATE', [$args['part_id']]);
    $data = stockValues($db, $kind, $args) + [
        'Fld_Entry_Date' => localDate('Y-m-d'),
        'Fld_Part_SN' => '',
    ];
    if ($kind === 'internal') {
        $data += ['Fld_Status_Date' => localDate('Y-m-d'), 'Fld_Physical_Stock' => 'YES'];
    } else {
        $data += ['status' => 'available', 'Fld_Publish' => 'YES'];
    }
    requireUniqueSerial($db, $kind, $data['Fld_Part_ID'], $data[$owner], $data['Fld_Part_SN']);
    return ['kind' => $kind, 'stock_id' => insert($db, $table, $data)];
}

function stockUpdate(PDO $db, string $kind, array $args): array
{
    [$table, $key, $owner] = stockLocation($kind);
    $id = $args['stock_id'];
    // Keep the lock order used by creation and use the current stock values for a patch.
    $part = one($db, "SELECT Fld_Part_ID FROM `$table` WHERE `$key` = ?", [$id]);
    one($db, 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_ID = ? FOR UPDATE', [(int)$part['Fld_Part_ID']]);
    $current = one($db, "SELECT Fld_Part_ID, Fld_Part_SN, `$owner` FROM `$table` WHERE `$key` = ? FOR UPDATE", [$id]);
    if ((int)$part['Fld_Part_ID'] !== (int)$current['Fld_Part_ID']) {
        throw new DomainException('Stock part changed concurrently');
    }
    $data = stockValues($db, $kind, $args);
    if (array_key_exists('Fld_Part_SN', $data) || array_key_exists($owner, $data)) {
        $merged = $data + $current;
        requireUniqueSerial($db, $kind, (int)$merged['Fld_Part_ID'], (int)$merged[$owner], (string)$merged['Fld_Part_SN'], $id);
    }
    update($db, $table, $key, $id, $data);
    return ['kind' => $kind, 'stock_id' => $id];
}

$readers = ['User', 'Sales', 'Manager', 'Admin', 'SuperAdmin'];
$sales = ['Sales', 'Manager', 'Admin', 'SuperAdmin'];
$managers = ['Manager', 'Admin', 'SuperAdmin'];
$pagination = ['limit' => integerField(1, 100), 'offset' => integerField(0, 1000000)];
$id = integerField();
$optionalId = integerField(0);
$date = ['type' => 'string', 'format' => 'date', 'maxLength' => 10];
$partProperties = [
    'part_number' => textField(80), 'description' => textField(1000, true),
    'manufacturer_id' => $optionalId, 'aircraft_id' => $optionalId,
    'list_price' => decimalField(), 'currency_id' => $optionalId,
    'remark' => textField(4000, true), 'alternate_part_numbers' => textField(500, true),
    'ata_chapter' => textField(50, true), 'cage_code' => textField(50, true),
    'minimum_order_quantity' => integerField(0), 'oem_lead_time' => textField(100, true),
    'core_value' => decimalField(), 'core_currency_id' => $optionalId,
];
$stockBase = [
    'part_id' => $id, 'serial_number' => textField(120, true), 'supplier_id' => $optionalId, 'owner_id' => $id,
    'condition_id' => $optionalId, 'release_id' => $optionalId, 'tag_company_id' => $optionalId,
    'traceability_company_id' => $optionalId, 'entry_date' => $date, 'tag_date' => $date,
    'quantity' => integerField(0), 'unit_price' => decimalField(), 'currency_id' => $optionalId,
    'warehouse_location' => textField(200, true), 'stock_remark' => textField(4000, true),
    'sales_remark' => textField(4000, true),
];
$internalStock = $stockBase + [
    'physical_stock' => enumField('YES', 'NO', 'PHYSICAL_STK', 'VIRTUAL_STK'), 'location_id' => $optionalId,
    'status_id' => $id, 'status_date' => $date, 'shelf_life_limit' => textField(100, true),
    'external_location_id' => $optionalId, 'warehouse_location_id' => $optionalId, 'minimum_quantity' => integerField(0),
];
$externalStock = $stockBase + ['publish' => enumField('YES', 'NO')];
$internalChanges = array_diff_key($internalStock, array_flip(['part_id', 'supplier_id', 'unit_price', 'currency_id', 'warehouse_location']));
$externalChanges = array_intersect_key(
    $stockBase + ['physical_stock' => textField(100, true)],
    array_flip(['quantity', 'condition_id', 'physical_stock', 'entry_date', 'stock_remark', 'sales_remark', 'tag_company_id'])
);
$stockKind = enumField('internal', 'external');
$partFilters = $pagination + [
    'query' => textField(150), 'exact' => ['type' => 'boolean'],
    'aircraft_id' => $id, 'manufacturer_id' => $id,
];
$lookups = [
    'aircraft' => ['tbl_Aircraft', 'Fld_AC_ID', 'Fld_AC_Model'],
    'condition' => ['tbl_Condition', 'Fld_Condition_ID', 'Fld_Condition_Text'],
    'release' => ['tbl_Release', 'Fld_Release_ID', 'Fld_Release_Text'],
    'status' => ['tbl_Status', 'Fld_Status_ID', 'Fld_Status_Text'],
    'location' => ['tbl_Stock_Location', 'Fld_Stock_Location_ID', 'Fld_Stock_Location_Text'],
    'warehouse_location' => ['tbl_Warehouse_Location', 'Id', 'Fld_Location'],
    'currency' => ['tbl_Currency', 'Fld_Currency_ID', 'Fld_Currency_Text'],
];

return [
    'search_parts' => operation('aerocanada.parts.read', $readers,
        'Search Yoyamic parts by number, description, alternates, ATA chapter, aircraft or manufacturer.',
        objectField($partFilters + ['status' => enumField('Available', 'archive')]),
        static fn(PDO $db, array $a): array => partsSearch($db, $a)),
    'list_wanted_parts' => operation('aerocanada.parts.read', $readers,
        'List available parts marked wanted in the Yoyamic catalog.',
        objectField($partFilters), static fn(PDO $db, array $a): array => partsSearch($db, $a, true)),
    'rank_parts' => operation('aerocanada.rfqs.read', $readers,
        'Rank part numbers by Yoyamic RFQ line count since 2017.',
        objectField($pagination + ['query' => textField(80)]),
        static function (PDO $db, array $a): array {
            $where = "Fld_RFQ_ID > ? AND pn_rfq <> '' AND pn_rfq <> ?";
            $values = ['2017-00-00-000000', 'pntest23'];
            if (isset($a['query'])) {
                $where .= " AND pn_rfq LIKE ? ESCAPE '!'";
                $values[] = literalLike(trim($a['query']));
            }
            return page($db, "SELECT pn_rfq AS part_number, COUNT(*) AS rfq_line_count, MAX(date) AS latest_rfq_date
                FROM tbl_RFQ_1 WHERE $where GROUP BY pn_rfq ORDER BY rfq_line_count DESC, pn_rfq", $values, $a);
        }),
    'list_part_prices' => operation('aerocanada.stock.read', $readers,
        'List Yoyamic supplier price-list entries for a part number.',
        objectField($pagination + ['part_number' => textField(80), 'company_id' => $id], ['part_number']),
        static function (PDO $db, array $a): array {
            $company = '';
            $values = [trim($a['part_number'])];
            if (isset($a['company_id'])) {
                $company = ' AND x.id_company = ?';
                $values[] = $a['company_id'];
            }
            return page($db, "SELECT x.id AS price_entry_id, x.CMM_Reference AS cmm_reference, x.pn AS part_number,
                x.Description AS description, x.brand, x.model, x.Sales_Unit AS sale_unit, x.Lead_Time_Days AS lead_time_days,
                x.MOQ AS minimum_order_quantity, x.Unit_price AS unit_price, x.Currency AS currency, x.id_company AS company_id,
                c.Fld_Company_Name AS company
                FROM tbl_price_list x LEFT JOIN tb_company c ON c.Fld_Company_ID = x.id_company
                WHERE x.pn = ?$company ORDER BY x.id", $values, $a);
        }),
    'get_part' => operation('aerocanada.parts.read', $readers, 'Get a Yoyamic part by its catalog ID.',
        objectField(['part_id' => $id], ['part_id']),
        static fn(PDO $db, array $a): array => one($db, PART_QUERY . ' WHERE p.Fld_Part_ID = ?', [$a['part_id']])),
    'list_part_documents' => operation('aerocanada.documents.read', $readers, 'List document titles attached to a Yoyamic part.',
        objectField($pagination + ['part_id' => $id], ['part_id']),
        static function (PDO $db, array $a): array {
            one($db, 'SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_ID = ?', [$a['part_id']]);
            return page($db, 'SELECT id_docs_attachment_pn AS document_id, name AS title, pn_id AS part_id
                FROM tbl_docs_attachment_pn WHERE pn_id = ? ORDER BY id_docs_attachment_pn', [$a['part_id']], $a);
        }),
    'create_part' => operation('aerocanada.parts.write', $sales, 'Create a Yoyamic catalog part.',
        objectField($partProperties, ['part_number']), __NAMESPACE__ . '\\partCreate', [PART_TABLE]),
    'create_parts' => operation('aerocanada.parts.write', $sales, 'Create up to 25 catalog parts in one transaction.',
        objectField(['parts' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 25,
            'items' => objectField($partProperties, ['part_number'])]], ['parts']),
        static function (PDO $db, array $a, int $actor): array {
            $ids = [];
            foreach ($a['parts'] as $part) {
                $ids[] = partCreate($db, $part, $actor)['part_id'];
            }
            return ['part_ids' => $ids];
        }, [PART_TABLE]),
    'update_part' => operation('aerocanada.parts.write', $sales, 'Update supplied fields on a Yoyamic part.',
        objectField(['part_id' => $id, 'wanted' => ['type' => 'boolean']] + $partProperties, ['part_id']), __NAMESPACE__ . '\\partUpdate', [PART_TABLE]),
    'archive_part' => operation('aerocanada.parts.write', $managers, 'Archive a Yoyamic catalog part without deleting it.',
        objectField(['part_id' => $id], ['part_id']), __NAMESPACE__ . '\\partArchive', [PART_TABLE]),
    'search_inventory' => operation('aerocanada.stock.read', $readers,
        'List internal or external Yoyamic stock by part, serial, owner or condition.',
        objectField($pagination + ['kind' => $stockKind, 'part_id' => $id, 'part_number' => textField(80),
            'owner_id' => $id, 'condition_id' => $id, 'serial_number' => textField(120)], ['kind']),
        __NAMESPACE__ . '\\stockSearch'),
    'list_inventory_lookups' => operation('aerocanada.stock.read', $readers,
        'List Yoyamic inventory reference values for aircraft, condition, release, status, locations or currency.',
        objectField($pagination + ['kind' => enumField(...array_keys($lookups))], ['kind']),
        static function (PDO $db, array $a) use ($lookups): array {
            [$table, $id, $label] = $lookups[$a['kind']];
            return page($db, "SELECT `$id` AS id, `$label` AS label FROM `$table` ORDER BY `$label`, `$id`", [], $a);
        }),
    'get_inventory_item' => operation('aerocanada.stock.read', $readers, 'Get internal or external stock by stock ID.',
        objectField(['kind' => $stockKind, 'stock_id' => $id], ['kind', 'stock_id']),
        static function (PDO $db, array $a): array {
            [, $key] = stockLocation($a['kind']);
            return one($db, stockSelect($a['kind']) . " WHERE s.`$key` = ?", [$a['stock_id']]);
        }),
    'create_internal_stock' => operation('aerocanada.stock.write', $managers, 'Add internal stock for a catalog part.',
        objectField($internalStock, ['part_id', 'owner_id', 'quantity', 'status_id']),
        static fn(PDO $db, array $a): array => stockCreate($db, 'internal', $a), ['tbl_Stock']),
    'update_internal_stock' => operation('aerocanada.stock.write', $managers, 'Update supplied internal stock fields.',
        objectField(['stock_id' => $id] + $internalChanges, ['stock_id']),
        static fn(PDO $db, array $a): array => stockUpdate($db, 'internal', $a), ['tbl_Stock']),
    'create_external_stock' => operation('aerocanada.stock.write', $managers, 'Add external stock held by a company.',
        objectField($externalStock, ['part_id', 'owner_id', 'quantity']),
        static fn(PDO $db, array $a): array => stockCreate($db, 'external', $a), ['tbl_Stock_external']),
    'update_external_stock' => operation('aerocanada.stock.write', $managers, 'Update fields supported by the external stock form.',
        objectField(['stock_id' => $id] + $externalChanges, ['stock_id']),
        static fn(PDO $db, array $a): array => stockUpdate($db, 'external', $a), ['tbl_Stock_external']),
];
