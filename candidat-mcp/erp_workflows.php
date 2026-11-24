<?php
declare(strict_types=1);

namespace AeroCanada\Mcp\Workflows;

use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;
use PDO;
use PDOStatement;

function query(PDO $db, string $sql, array $args = []): PDOStatement
{
    $statement = $db->prepare($sql);
    foreach (array_values($args) as $index => $value) {
        $type = $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        $statement->bindValue($index + 1, $value, $type);
    }
    $statement->execute();
    return $statement;
}

function one(PDO $db, string $sql, array $args, string $error): array
{
    $row = query($db, $sql, $args)->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        throw new OutOfBoundsException($error);
    }
    return $row;
}

function insert(PDO $db, string $table, array $data): int
{
    $columns = implode(', ', array_keys($data));
    $values = implode(', ', array_fill(0, count($data), '?'));
    query($db, "INSERT INTO $table ($columns) VALUES ($values)", array_values($data));
    return (int)$db->lastInsertId();
}

function update(PDO $db, string $table, int $id, array $data): void
{
    $pairs = implode(', ', array_map(static function (string $column): string {
        return "$column = ?";
    }, array_keys($data)));
    query($db, "UPDATE $table SET $pairs WHERE id = ?", array_merge(array_values($data), [$id]));
}

function idSchema(): array
{
    return ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647];
}

function stringSchema(int $max, int $min = 1): array
{
    return ['type' => 'string', 'minLength' => $min, 'maxLength' => $max];
}

function enumSchema(array $options): array
{
    return ['type' => 'string', 'enum' => $options];
}

function dateSchema(): array
{
    return stringSchema(10, 10) + ['pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}$', 'format' => 'date'];
}

function rfqSchema(): array
{
    // Yoyamic tbl_RFQ.Fld_RFQ_ID is VARCHAR(19); the line table is wider.
    return stringSchema(19) + ['pattern' => '^[A-Za-z0-9][A-Za-z0-9._/-]*$'];
}

function moneySchema(): array
{
    return stringSchema(13) + ['pattern' => '^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$'];
}

function objectSchema(array $properties, array $required = []): array
{
    return ['type' => 'object', 'properties' => $properties ?: (object)[], 'required' => $required, 'additionalProperties' => false];
}

function validate($value, array $schema, string $path = 'arguments'): void
{
    $invalid = static function () use ($path): void {
        throw new InvalidArgumentException("Paramètre invalide : $path");
    };
    switch ($schema['type']) {
        case 'object':
            if (!is_array($value)) {
                $invalid();
            }
            foreach ($schema['required'] as $key) {
                if (!array_key_exists($key, $value)) {
                    throw new InvalidArgumentException("Paramètre obligatoire : $path.$key");
                }
            }
            $properties = (array)$schema['properties'];
            foreach ($value as $key => $item) {
                if (!isset($properties[$key])) {
                    throw new InvalidArgumentException("Paramètre inconnu : $path.$key");
                }
                validate($item, $properties[$key], "$path.$key");
            }
            break;
        case 'array':
            if (!is_array($value) || array_values($value) !== $value
                || count($value) < $schema['minItems'] || count($value) > $schema['maxItems']) {
                $invalid();
            }
            foreach ($value as $index => $item) {
                validate($item, $schema['items'], "$path.$index");
            }
            break;
        case 'integer':
            if (!is_int($value) || $value < $schema['minimum'] || $value > $schema['maximum']) {
                $invalid();
            }
            break;
        case 'string':
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')
                || strpos($value, "\0") !== false) {
                $invalid();
            }
            if (isset($schema['enum']) && !in_array($value, $schema['enum'], true)) {
                $invalid();
            }
            if (isset($schema['maxLength']) && (mb_strlen(trim($value), 'UTF-8') < $schema['minLength']
                    || mb_strlen($value, 'UTF-8') > $schema['maxLength'])) {
                $invalid();
            }
            if (isset($schema['pattern']) && preg_match('~' . $schema['pattern'] . '~D', $value) !== 1) {
                $invalid();
            }
            if (($schema['format'] ?? null) === 'date') {
                [$year, $month, $day] = array_map('intval', explode('-', $value));
                if (!checkdate($month, $day, $year) || $year < 1000) {
                    $invalid();
                }
            }
            break;
    }
}

function operation(string $scope, string $description, array $schema, callable $handler, array $writeTables = []): array
{
    $roles = ['Sales', 'Manager', 'Admin', 'SuperAdmin'];
    if (substr($scope, -5) === '.read') {
        array_unshift($roles, 'User');
    }
    return [
        'scope' => "aerocanada.$scope",
        'roles' => $roles,
        'description' => $description,
        'inputSchema' => $schema,
        'writeTables' => $writeTables,
        'handler' => static function (PDO $db, array $args, int $employeeId) use ($schema, $handler, $scope): array {
            validate($args, $schema);
            if (substr($scope, -6) === '.write' && ($employeeId < 1 || !$db->inTransaction())) {
                throw new \LogicException('Identité vérifiée et transaction requises');
            }
            return $handler($db, $args, $employeeId);
        },
    ];
}

function ref(PDO $db, string $kind, int $id, bool $lock = false): array
{
    $lookups = [
        'company' => ['tb_company', 'Fld_Company_ID'],
        'part' => ['tbl_Parts', 'Fld_Part_ID'],
        'priority' => ['tbl_Priority', 'Fld_Priority_ID'],
        'rfq_type' => ['tbl_RFQ_Type', 'Fld_RFQ_Type_ID'],
        'payment_term' => ['tbl_Payment', 'Fld_Payment_Term_ID'],
        'condition' => ['tbl_Condition', 'Fld_Condition_ID'],
        'currency' => ['tbl_Currency', 'Fld_Currency_ID'],
        'shipper' => ['tbl_Shipper', 'Fld_Shipper_ID'],
        'release' => ['tbl_Release', 'Fld_Release_ID'],
    ];
    [$table, $column] = $lookups[$kind];
    return one($db, "SELECT $column FROM $table WHERE $column = ?" . ($lock ? ' FOR UPDATE' : ''), [$id],
        "Référence ERP introuvable : $kind");
}

function contact(PDO $db, int $companyId, int $contactId): void
{
    one($db, "SELECT id_company_contact FROM tb_company_contact
              WHERE id_company_contact = ? AND Fld_Company_ID = ? AND status = 'Available'",
        [$contactId, $companyId], 'Contact actif introuvable dans cette compagnie');
}

function rfqLine(PDO $db, int $id, int $employee): array
{
    return one($db, 'SELECT * FROM tbl_RFQ_1 WHERE ID = ? AND Employee_ID = ? FOR UPDATE',
        [$id, $employee], 'Ligne RFQ introuvable pour cet employé');
}

function lineData(PDO $db, array $line): array
{
    $part = one($db, 'SELECT Fld_Part_Nbr, Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID = ?',
        [$line['part_id']], 'Pièce introuvable');
    ref($db, 'rfq_type', $line['rfq_type_id']);
    ref($db, 'condition', $line['condition_id']);
    return [
        'Fld_Part_ID' => $line['part_id'], 'Fld_Qty' => $line['quantity'], 'Fld_Condition_ID' => $line['condition_id'],
        'Fld_RFQ_Type_ID' => $line['rfq_type_id'], 'Fld_Observation' => trim($line['observation'] ?? ''),
        'pn_rfq' => $part['Fld_Part_Nbr'], 'description_rfq' => $part['Fld_Part_Desc'] ?? '',
    ];
}

function createRfq(PDO $db, array $args, int $employee): array
{
    $rfq = $args['rfq_reference'];
    $company = $args['company_id'];
    ref($db, 'company', $company, true);
    contact($db, $company, $args['contact_id']);
    ref($db, 'priority', $args['priority_id']);
    ref($db, 'payment_term', $args['payment_term_id']);
    if (query($db, 'SELECT ID FROM tbl_RFQ WHERE Fld_RFQ_ID = ? FOR UPDATE', [$rfq])->fetchColumn() !== false
        || query($db, 'SELECT ID FROM tbl_RFQ_1 WHERE Fld_RFQ_ID = ? LIMIT 1 FOR UPDATE', [$rfq])->fetchColumn() !== false) {
        throw new DomainException('Référence RFQ déjà utilisée');
    }
    $requestDate = $args['request_date'] ?? date('Y-m-d');
    $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $requestDate);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $requestDate) {
        throw new InvalidArgumentException('Date RFQ invalide');
    }
    // Match the legacy Yoyamic widths without truncation: header VARCHAR(9),
    // line.date VARCHAR(15). The line retains the four-digit year.
    $headerDate = $parsedDate->format('d-M-y');
    $lineDate = $parsedDate->format('d/m/Y');
    $common = [
        'Fld_RFQ_ID' => $rfq, 'Fld_Customer_ID' => $company, 'date' => $lineDate,
        'Fld_Priority_ID' => $args['priority_id'], 'Fld_Payment_Term_ID' => $args['payment_term_id'],
        'Employee_ID' => $employee, 'id_company_contact' => $args['contact_id'],
    ];
    $lines = [];
    foreach ($args['lines'] as $line) {
        $lines[] = $common + lineData($db, $line);
    }
    $master = insert($db, 'tbl_RFQ', [
        'Fld_RFQ_ID' => $rfq, 'Fld_Date' => $headerDate, 'Fld_Step_1' => 'TRUE', 'Fld_Step_2' => 'FALSE',
        'Fld_Step_3' => 'FALSE', 'Fld_Priority_ID' => $args['priority_id'],
        'Fld_RFQ_ACI_Employee_Id' => $employee, 'Fld_Customer_ID' => $company,
    ]);
    $lineIds = [];
    foreach ($lines as $line) {
        // DATE is reserved and the native field is lower-case.
        $line['`date`'] = $line['date'];
        unset($line['date']);
        $lineIds[] = insert($db, 'tbl_RFQ_1', $line);
    }
    return ['id' => $master, 'rfq_reference' => $rfq, 'line_ids' => $lineIds];
}

function page(PDO $db, string $sql, array $args = [], array $filters = []): array
{
    $limit = $args['limit'] ?? 25;
    $offset = $args['offset'] ?? 0;
    $rows = query($db, $sql . ' LIMIT ? OFFSET ?', array_merge($filters, [$limit + 1, $offset]))
        ->fetchAll(PDO::FETCH_ASSOC);
    $more = count($rows) > $limit;
    return ['items' => array_slice($rows, 0, $limit), 'next_offset' => $more ? $offset + $limit : null];
}

function searchRfq(PDO $db, array $args): array
{
    $filters = [];
    $conditions = [];
    if (isset($args['search'])) {
        $phrase = trim($args['search']);
        $conditions[] = '(LOCATE(?, r.Fld_RFQ_ID) > 0 OR LOCATE(?, r.pn_rfq) > 0 OR LOCATE(?, c.Fld_Company_Name) > 0)';
        array_push($filters, $phrase, $phrase, $phrase);
    }
    if (isset($args['company_id'])) {
        $conditions[] = 'r.Fld_Customer_ID = ?';
        $filters[] = $args['company_id'];
    }
    if (isset($args['priority_id'])) {
        $conditions[] = 'r.Fld_Priority_ID = ?';
        $filters[] = $args['priority_id'];
    }
    $sql = 'SELECT r.ID AS id, r.Fld_RFQ_ID AS rfq_reference, r.date AS request_date,
                   r.Fld_Customer_ID AS company_id, c.Fld_Company_Name AS company_name,
                   r.pn_rfq AS part_number, r.Fld_Qty AS quantity, r.Fld_Priority_ID AS priority_id
            FROM tbl_RFQ_1 r LEFT JOIN tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID';
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    return page($db, $sql . ' ORDER BY r.ID DESC', $args, $filters);
}

function updateRfqLine(PDO $db, array $args, int $employee): array
{
    $id = $args['line_id'];
    $line = rfqLine($db, $id, $employee);
    $data = [];
    if (isset($args['quantity'])) {
        $data['Fld_Qty'] = $args['quantity'];
    }
    if (isset($args['condition_id'])) {
        ref($db, 'condition', $args['condition_id']);
        $data['Fld_Condition_ID'] = $args['condition_id'];
    }
    if (isset($args['observation'])) {
        $data['Fld_Observation'] = trim($args['observation']);
    }
    if (!$data) {
        throw new InvalidArgumentException('Aucune donnée modifiable fournie');
    }
    update($db, 'tbl_RFQ_1', $id, $data);
    return ['id' => $id, 'rfq_reference' => $line['Fld_RFQ_ID']];
}

function addRfqLine(PDO $db, array $args, int $employee): array
{
    $rfq = $args['rfq_reference'];
    one($db, 'SELECT ID FROM tbl_RFQ WHERE Fld_RFQ_ID = ? AND Fld_RFQ_ACI_Employee_Id = ? FOR UPDATE',
        [$rfq, $employee], 'RFQ introuvable pour cet employé');
    $line = one($db, 'SELECT Fld_Customer_ID, `date`, Fld_Priority_ID, Fld_Payment_Term_ID, id_company_contact
            FROM tbl_RFQ_1 WHERE Fld_RFQ_ID = ? AND Employee_ID = ? ORDER BY ID LIMIT 1 FOR UPDATE',
        [$rfq, $employee], 'Ligne de référence RFQ introuvable');
    $line['`date`'] = $line['date'];
    unset($line['date']);
    $id = insert($db, 'tbl_RFQ_1', $line + ['Fld_RFQ_ID' => $rfq, 'Employee_ID' => $employee]
        + lineData($db, $args['line']));
    return ['id' => $id, 'rfq_reference' => $rfq];
}

function quoteData(PDO $db, array $args): array
{
    $data = [];
    $inputs = [
        'quantity' => 'Fld_Qty', 'condition_id' => 'Fld_Condition', 'price' => 'Fld_Price',
        'currency_id' => 'Fld_Currency_ID', 'serial_number' => 'Fld_Part_SN', 'notes' => 'Fld_Remark',
        'lead_time' => 'lead_time', 'release_id' => 'Fld_Release_ID',
    ];
    foreach (['condition_id' => 'condition', 'currency_id' => 'currency', 'release_id' => 'release'] as $key => $ref) {
        if (isset($args[$key])) {
            ref($db, $ref, $args[$key]);
        }
    }
    foreach ($inputs as $key => $column) {
        if (isset($args[$key])) {
            $data[$column] = $args[$key];
        }
    }
    return $data;
}

function quoteSource(PDO $db, array $args, int $rfqLineId, array $line): array
{
    $sourceType = $args['source_type'] ?? null;
    $sourceId = $args['source_id'] ?? null;
    if ($sourceType === null && $sourceId === null) {
        return [];
    }
    if ($sourceType === null || $sourceId === null) {
        throw new InvalidArgumentException('Le type et l’identifiant de source sont tous deux requis');
    }
    $partId = (int)$line['Fld_Part_ID'];
    $fields = ['source_type' => $sourceType, 'source_id' => $sourceId];
    if ($sourceType === 'SQ') {
        $params = [$sourceId, $line['Fld_RFQ_ID'], $partId];
        $hasLine = query($db, "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'tbl_RFQ_2' AND COLUMN_NAME = 'id_tbl_rfq1'")->fetchColumn();
        $lineClause = '';
        if ((int)$hasLine > 0) {
            $lineClause = ' AND (id_tbl_rfq1 = ? OR id_tbl_rfq1 IS NULL OR id_tbl_rfq1 = 0)';
            $params[] = $rfqLineId;
        }
        one($db, 'SELECT ID FROM tbl_RFQ_2 WHERE ID = ? AND Fld_RFQ_ID = ? AND Fld_Part_ID = ?' . $lineClause,
            $params, 'Cotation fournisseur hors de la ligne RFQ sélectionnée');
    } elseif ($sourceType === 'STOCK-ACI770') {
        one($db, 'SELECT Fld_Stock_ID FROM tbl_Stock WHERE Fld_Stock_ID = ? AND Fld_Part_ID = ?',
            [$sourceId, $partId], 'Stock interne introuvable pour cette pièce');
    } else {
        one($db, 'SELECT Fld_Stock_externe_ID FROM tbl_Stock_external WHERE Fld_Stock_externe_ID = ? AND Fld_Part_ID = ?',
            [$sourceId, $partId], 'Stock externe introuvable pour cette pièce');
    }
    return $fields;
}

function createQuotes(PDO $db, array $args, int $employee): array
{
    $rfq = $args['rfq_reference'];
    $ids = array_column($args['lines'], 'rfq_line_id');
    if (count(array_unique($ids)) !== count($ids)) {
        throw new InvalidArgumentException('Une ligne RFQ apparaît plusieurs fois');
    }
    $dateType = query($db, "SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_RFQ_3' AND COLUMN_NAME = 'Fld_Quote_Date'")->fetchColumn();
    if ($dateType === false) {
        throw new \LogicException('Type de date de cotation introuvable');
    }
    $date = in_array($dateType, ['date', 'datetime', 'timestamp'], true) ? date('Y-m-d') : date('d-m-y');
    $rows = [];
    foreach ($args['lines'] as $line) {
        $source = rfqLine($db, $line['rfq_line_id'], $employee);
        if ((string)$source['Fld_RFQ_ID'] !== $rfq) {
            throw new InvalidArgumentException('La ligne n’appartient pas à cette RFQ');
        }
        $rows[] = quoteData($db, $line) + quoteSource($db, $line, $line['rfq_line_id'], $source) + [
            'Fld_RFQ_ID' => $rfq, 'Fld_Quote_Date' => $date, 'Fld_Part_Id' => $source['Fld_Part_ID'],
            'Fld_Priority_ID' => $source['Fld_Priority_ID'], 'id_tbl_rfq1' => $line['rfq_line_id'],
        ];
    }
    $ids = [];
    foreach ($rows as $row) {
        $ids[] = insert($db, 'tbl_RFQ_3', $row);
    }
    return ['quote_ids' => $ids, 'rfq_reference' => $rfq];
}

function ownedQuote(PDO $db, int $id, int $employee): array
{
    return one($db, 'SELECT q.*, r.Fld_Customer_ID AS company_id, r.id_company_contact AS contact_id
        FROM tbl_RFQ_3 q JOIN tbl_RFQ_1 r ON r.ID = q.id_tbl_rfq1 AND r.Fld_RFQ_ID = q.Fld_RFQ_ID
        WHERE q.ID = ? AND r.Employee_ID = ? FOR UPDATE', [$id, $employee],
        'Cotation introuvable pour cet employé');
}

function updateQuote(PDO $db, array $args, int $employee): array
{
    $id = $args['id'];
    $existing = ownedQuote($db, $id, $employee);
    if (!in_array(strtoupper(trim((string)($existing['Fld_Send_Mail'] ?? ''))), ['', '0', 'FALSE', 'NO'], true)) {
        throw new DomainException('Une cotation envoyée ne peut pas être modifiée ici');
    }
    $data = quoteData($db, $args);
    if (!$data) {
        throw new InvalidArgumentException('Aucune donnée modifiable fournie');
    }
    update($db, 'tbl_RFQ_3', $id, $data);
    return ['id' => $id, 'rfq_reference' => $existing['Fld_RFQ_ID']];
}

function createOrderDraft(PDO $db, array $args, int $employee): array
{
    $quote = ownedQuote($db, $args['quote_id'], $employee);
    $existing = query($db, 'SELECT id, po_number FROM tbl_PO_Draft WHERE quotation_id = ?', [$args['quote_id']])
        ->fetch(PDO::FETCH_ASSOC);
    if ($existing !== false) {
        return ['id' => (int)$existing['id'], 'number' => $existing['po_number']];
    }
    $sourceType = strtoupper((string)($quote['source_type'] ?? ''));
    $sourceId = (int)($quote['source_id'] ?? 0);
    $sourceCompany = null;
    if ($sourceId > 0) {
        if (strpos($sourceType, 'SQ') !== false) {
            $sql = 'SELECT Fld_Supplier_ID FROM tbl_RFQ_2 WHERE ID = ? AND Fld_Part_ID = ?';
        } elseif (strpos($sourceType, 'EXTERNAL') !== false) {
            $sql = 'SELECT Fld_Company_ID FROM tbl_Stock_external WHERE Fld_Stock_externe_ID = ? AND Fld_Part_ID = ?';
        } elseif (strpos($sourceType, 'STOCK') !== false || strpos($sourceType, 'INTERNAL') !== false) {
            $sql = 'SELECT COALESCE(Fld_Supplier_ID, Fld_Owner_ID) FROM tbl_Stock WHERE Fld_Stock_ID = ? AND Fld_Part_ID = ?';
        } else {
            throw new InvalidArgumentException('Type de source de la cotation inconnu');
        }
        $value = query($db, $sql, [$sourceId, (int)$quote['Fld_Part_Id']])->fetchColumn();
        if ($value === false) {
            throw new InvalidArgumentException('Source de cotation introuvable');
        }
        $sourceCompany = (int)$value ?: null;
    }
    $number = 'DRAFT-Q' . $args['quote_id'];
    $id = insert($db, 'tbl_PO_Draft', [
        'quotation_id' => $args['quote_id'], 'rfq_id' => $quote['Fld_RFQ_ID'],
        'rfq_line_id' => (int)$quote['id_tbl_rfq1'], 'part_id' => (int)$quote['Fld_Part_Id'],
        'source_type' => $sourceType, 'source_id' => $sourceId, 'source_company_id' => $sourceCompany,
        'customer_company_id' => (int)$quote['company_id'], 'customer_contact_id' => (int)$quote['contact_id'],
        'qty' => $quote['Fld_Qty'], 'condition_id' => (int)$quote['Fld_Condition'],
        'price' => $quote['Fld_Price'], 'currency_id' => (int)$quote['Fld_Currency_ID'],
        'release_id' => (int)($quote['Fld_Release_ID'] ?? 0), 'delivery' => $quote['lead_time'] ?? '',
        'remarks' => $quote['Fld_Remark'] ?? '', 'po_number' => $number,
        'accepted_price' => $quote['Fld_Price'], 'accepted_qty' => $quote['Fld_Qty'],
        'accepted_condition_id' => (int)$quote['Fld_Condition'], 'status' => 'DRAFT', 'created_by' => $employee,
    ]);
    return ['id' => $id, 'number' => $number];
}

function ownedDocument(PDO $db, string $table, int $id, int $employee): array
{
    return one($db, "SELECT * FROM $table WHERE id = ? AND created_by = ? FOR UPDATE",
        [$id, $employee], 'Document introuvable pour cet employé');
}

function salesDocumentNumber(PDO $db, string $table, string $prefix): string
{
    // The native series is based on the table maximum. Its UNIQUE index makes
    // simultaneous creation fail atomically instead of creating duplicate numbers.
    $last = (int)query($db, "SELECT MAX(id) FROM $table")->fetchColumn();
    return $prefix . '-' . date('Y') . '-' . str_pad((string)($last + 1), 5, '0', STR_PAD_LEFT);
}

function validateCompanyRfq(PDO $db, ?string $rfq, int $company): void
{
    if ($rfq !== null) {
        one($db, 'SELECT ID FROM tbl_RFQ WHERE Fld_RFQ_ID = ? AND Fld_Customer_ID = ? LIMIT 1',
            [$rfq, $company], 'RFQ introuvable dans cette compagnie');
    }
}

function createInvoice(PDO $db, array $args, int $employee): array
{
    $company = $args['company_id'];
    ref($db, 'company', $company, true);
    contact($db, $company, $args['contact_id']);
    ref($db, 'currency', $args['currency_id']);
    validateCompanyRfq($db, $args['rfq_reference'] ?? null, $company);
    $type = $args['invoice_type'];
    $number = salesDocumentNumber($db, 'tbl_Invoices', ['proforma' => 'PI', 'commercial' => 'INV', 'credit_note' => 'CN'][$type]);
    $date = $args['invoice_date'] ?? date('Y-m-d');
    if (isset($args['due_date']) && $args['due_date'] < $date) {
        throw new InvalidArgumentException('L’échéance précède la date de facturation');
    }
    $id = insert($db, 'tbl_Invoices', [
        'invoice_number' => $number, 'invoice_type' => $type, 'rfq_id' => $args['rfq_reference'] ?? null,
        'company_id' => $company, 'contact_id' => $args['contact_id'], 'invoice_date' => $date,
        'due_date' => $args['due_date'] ?? null, 'currency_id' => $args['currency_id'],
        'notes' => $args['notes'] ?? '', 'created_by' => $employee,
    ]);
    return ['id' => $id, 'number' => $number];
}

function addInvoiceItem(PDO $db, array $args, int $employee): array
{
    $id = $args['invoice_id'];
    $invoice = ownedDocument($db, 'tbl_Invoices', $id, $employee);
    if ($invoice['status'] !== 'draft') {
        throw new DomainException('La facture doit être en brouillon');
    }
    ref($db, 'part', $args['part_id']);
    ref($db, 'condition', $args['condition_id']);
    query($db, 'INSERT INTO tbl_Invoice_Items
        (invoice_id, part_id, description, quantity, unit_price, total, condition_id)
        VALUES (?, ?, ?, ?, ?, CAST(? AS DECIMAL(12,2)) * ?, ?)', [
        $id, $args['part_id'], $args['description'], $args['quantity'], $args['unit_price'],
        $args['unit_price'], $args['quantity'], $args['condition_id'],
    ]);
    $itemId = (int)$db->lastInsertId();
    recalculateInvoice($db, $id);
    return ['id' => $itemId, 'invoice_id' => $id];
}

function recalculateInvoice(PDO $db, int $invoiceId): void
{
    query($db, 'UPDATE tbl_Invoices i
        JOIN (SELECT COALESCE(SUM(total), 0) AS subtotal FROM tbl_Invoice_Items WHERE invoice_id = ?) s
        SET i.subtotal = s.subtotal, i.tax_amount = ROUND(s.subtotal * i.tax_rate / 100, 2),
            i.total = s.subtotal + ROUND(s.subtotal * i.tax_rate / 100, 2) WHERE i.id = ?', [$invoiceId, $invoiceId]);
}

function updateInvoiceItem(PDO $db, array $args, int $employee): array
{
    $id = $args['invoice_id'];
    $item = $args['item_id'];
    if (ownedDocument($db, 'tbl_Invoices', $id, $employee)['status'] !== 'draft') {
        throw new DomainException('La facture doit être en brouillon');
    }
    one($db, 'SELECT id FROM tbl_Invoice_Items WHERE id = ? AND invoice_id = ? FOR UPDATE', [$item, $id],
        'Ligne introuvable dans cette facture');
    ref($db, 'part', $args['part_id']);
    ref($db, 'condition', $args['condition_id']);
    query($db, 'UPDATE tbl_Invoice_Items SET part_id = ?, condition_id = ?, description = ?,
        quantity = ?, unit_price = ?, total = CAST(? AS DECIMAL(12,2)) * ?
        WHERE id = ? AND invoice_id = ?', [$args['part_id'], $args['condition_id'], $args['description'],
        $args['quantity'], $args['unit_price'], $args['unit_price'], $args['quantity'], $item, $id]);
    recalculateInvoice($db, $id);
    return ['id' => $item, 'invoice_id' => $id];
}

function markInvoice(PDO $db, array $args, int $employee, string $status): array
{
    $id = $args['id'];
    $invoice = ownedDocument($db, 'tbl_Invoices', $id, $employee);
    $transitions = ['sent' => ['draft'], 'paid' => ['sent', 'overdue']];
    if ($invoice['status'] !== $status) {
        if (!in_array($invoice['status'], $transitions[$status], true)) {
            throw new DomainException('Transition de facture interdite');
        }
        update($db, 'tbl_Invoices', $id, ['status' => $status]);
    }
    return ['id' => $id, 'status' => $status];
}

function createShipment(PDO $db, array $args, int $employee): array
{
    $company = $args['company_id'];
    ref($db, 'company', $company, true);
    ref($db, 'shipper', $args['shipper_id']);
    validateCompanyRfq($db, $args['rfq_reference'] ?? null, $company);
    if (isset($args['invoice_id'])) {
        one($db, 'SELECT id FROM tbl_Invoices WHERE id = ? AND company_id = ? AND created_by = ?',
            [$args['invoice_id'], $company, $employee], 'Facture introuvable pour cette compagnie et cet employé');
    }
    $number = salesDocumentNumber($db, 'tbl_Shipping', 'SHP');
    $date = $args['ship_date'] ?? date('Y-m-d');
    if (isset($args['estimated_arrival']) && $args['estimated_arrival'] < $date) {
        throw new InvalidArgumentException('L’arrivée estimée précède le départ');
    }
    $id = insert($db, 'tbl_Shipping', [
        'shipping_number' => $number, 'invoice_id' => $args['invoice_id'] ?? null,
        'rfq_id' => $args['rfq_reference'] ?? null, 'company_id' => $company,
        'ship_from' => $args['ship_from'], 'ship_to' => $args['ship_to'], 'shipper_id' => $args['shipper_id'],
        'ship_date' => $date, 'estimated_arrival' => $args['estimated_arrival'] ?? null,
        'weight' => $args['weight'] ?? '', 'dimensions' => $args['dimensions'] ?? '',
        'notes' => $args['notes'] ?? '', 'created_by' => $employee,
    ]);
    return ['id' => $id, 'number' => $number];
}

function updateShipment(PDO $db, array $args, int $employee): array
{
    $id = $args['id'];
    $shipment = ownedDocument($db, 'tbl_Shipping', $id, $employee);
    $data = [];
    if (isset($args['tracking_number'])) {
        if (!in_array($shipment['status'], ['preparing', 'shipped'], true)) {
            throw new DomainException('Suivi modifiable seulement avant le transit');
        }
        $data = ['tracking_number' => $args['tracking_number'], 'status' => 'shipped'];
    } else {
        $status = $args['status'];
        $transitions = [
            'preparing' => ['shipped'], 'shipped' => ['in_transit', 'delivered', 'returned'],
            'in_transit' => ['delivered', 'returned'], 'delivered' => ['returned'], 'returned' => [],
        ];
        if ($status === $shipment['status']) {
            return ['id' => $id, 'status' => $status];
        }
        if (!in_array($status, $transitions[$shipment['status']] ?? [], true)) {
            throw new DomainException('Transition d’expédition interdite');
        }
        $data = ['status' => $status];
        if ($status === 'delivered') {
            $data['actual_arrival'] = date('Y-m-d');
        }
    }
    update($db, 'tbl_Shipping', $id, $data);
    return ['id' => $id, 'status' => $data['status']];
}

function lookupDocument(PDO $db, array $args, string $document, bool $detail = false): array
{
    $definitions = [
        'invoice' => [
            'table' => 'tbl_Invoices', 'number' => 'invoice_number',
            'fields' => 'id, invoice_number, invoice_type, rfq_id, company_id, contact_id, invoice_date,
                         due_date, currency_id, subtotal, tax_rate, tax_amount, total, status, notes',
            'items' => 'SELECT id, part_id, description, quantity, unit_price, total, condition_id
                         FROM tbl_Invoice_Items WHERE invoice_id = ? ORDER BY id',
        ],
        'shipment' => [
            'table' => 'tbl_Shipping', 'number' => 'shipping_number',
            'fields' => 'id, shipping_number, invoice_id, rfq_id, company_id, ship_from, ship_to,
                         shipper_id, tracking_number, ship_date, estimated_arrival, actual_arrival, weight,
                         dimensions, status, notes',
            'items' => 'SELECT id, part_id, serial_number, quantity, condition_id, release_id
                         FROM tbl_Shipping_Items WHERE shipping_id = ? ORDER BY id',
        ],
        'order' => [
            'table' => 'tbl_PO_Draft', 'number' => 'po_number',
            'fields' => 'id, quotation_id, rfq_id, rfq_line_id, part_id, source_type, source_id, source_company_id,
                         customer_company_id AS company_id, customer_contact_id, qty, condition_id, price,
                         currency_id, delivery, po_number, customer_po_number, accepted_price, accepted_qty,
                         accepted_condition_id, status',
        ],
    ];
    $definition = $definitions[$document];
    $company = $document === 'order' ? 'customer_company_id' : 'company_id';
    $sql = 'SELECT ' . $definition['fields'] . ' FROM ' . $definition['table'];
    if ($detail) {
        $result = one($db, $sql . ' WHERE id = ?', [$args['id']], 'Document introuvable');
        if (isset($definition['items'])) {
            $result['lines'] = page($db, $definition['items'], $args, [$args['id']]);
        }
        return $result;
    }
    $conditions = [];
    $values = [];
    if (isset($args['company_id'])) {
        $conditions[] = "$company = ?";
        $values[] = $args['company_id'];
    }
    if (isset($args['status'])) {
        $conditions[] = 'status = ?';
        $values[] = $args['status'];
    }
    if (isset($args['number'])) {
        $conditions[] = $definition['number'] . ' = ?';
        $values[] = $args['number'];
    }
    return page($db, $sql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY id DESC',
        $args, $values);
}

function listQuotes(PDO $db, array $args): array
{
    $sql = 'SELECT q.ID AS id, q.Fld_RFQ_ID AS rfq_reference, q.Fld_Quote_Date AS quote_date,
                   q.Fld_Part_Id AS part_id, p.Fld_Part_Nbr AS part_number, q.Fld_Qty AS quantity,
                   q.Fld_Condition AS condition_id, q.Fld_Price AS price, q.Fld_Currency_ID AS currency_id,
                   q.Fld_Remark AS notes, q.lead_time, q.id_tbl_rfq1 AS rfq_line_id,
                   q.source_type, q.source_id, r.Fld_Customer_ID AS company_id, q.Fld_Send_Mail AS sent
            FROM tbl_RFQ_3 q
            LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = q.Fld_Part_Id
            LEFT JOIN tbl_RFQ_1 r ON r.ID = q.id_tbl_rfq1 AND r.Fld_RFQ_ID = q.Fld_RFQ_ID';
    if (isset($args['id'])) {
        return one($db, $sql . ' WHERE q.ID = ?', [$args['id']], 'Cotation introuvable');
    }
    $conditions = [];
    $values = [];
    foreach (['rfq_reference' => 'q.Fld_RFQ_ID', 'company_id' => 'r.Fld_Customer_ID', 'part_id' => 'q.Fld_Part_Id'] as $key => $column) {
        if (isset($args[$key])) {
            $conditions[] = "$column = ?";
            $values[] = $args[$key];
        }
    }
    return page($db, $sql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY q.ID DESC',
        $args, $values);
}

function listCapabilities(PDO $db, array $args): array
{
    $sql = 'SELECT cl.id_capa_list AS id, cl.id_company AS company_id, c.Fld_Company_Name AS company_name,
                   cl.pn AS part_number, cl.descriptioin AS description, cl.aircraft, cl.ata,
                   cl.capability, cl.pma, cl.doa, cl.der, cl.code_oem, cl.design_oem
            FROM tbl_capa_list cl LEFT JOIN tb_company c ON c.Fld_Company_ID = cl.id_company';
    $conditions = [];
    $values = [];
    if (isset($args['company_id'])) {
        $conditions[] = 'cl.id_company = ?';
        $values[] = $args['company_id'];
    }
    if (isset($args['part_number'])) {
        $conditions[] = 'cl.pn = ?';
        $values[] = $args['part_number'];
    }
    return page($db, $sql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY cl.id_capa_list DESC',
        $args, $values);
}

function listSupplierQuotes(PDO $db, array $args): array
{
    $sql = 'SELECT q.ID AS id, q.Fld_RFQ_ID AS rfq_reference, q.Fld_Part_ID AS part_id,
                   q.Fld_Supplier_ID AS company_id, c.Fld_Company_Name AS company_name,
                   q.Fld_Current_Date AS quote_date FROM tbl_RFQ_2 q
                   LEFT JOIN tb_company c ON c.Fld_Company_ID = q.Fld_Supplier_ID';
    $conditions = [];
    $values = [];
    foreach (['rfq_reference' => 'q.Fld_RFQ_ID', 'company_id' => 'q.Fld_Supplier_ID', 'part_id' => 'q.Fld_Part_ID'] as $key => $column) {
        if (isset($args[$key])) {
            $conditions[] = "$column = ?";
            $values[] = $args[$key];
        }
    }
    return page($db, $sql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY q.ID DESC',
        $args, $values);
}

function dashboard(PDO $db): array
{
    $counts = [
        'active_companies' => "SELECT COUNT(*) FROM tb_company WHERE status = 'Available'",
        'internal_stock_rows' => 'SELECT COUNT(*) FROM tbl_Stock',
        'rfq_lines_current_month' => 'SELECT COUNT(*) FROM tbl_RFQ_1 WHERE date >= DATE_FORMAT(CURRENT_DATE, "%Y-%m-01")',
        'customer_quote_rows' => 'SELECT COUNT(*) FROM tbl_RFQ_3',
    ];
    foreach ($counts as $key => $sql) {
        $counts[$key] = (int)query($db, $sql)->fetchColumn();
    }
    return $counts;
}

function supplierQuoteData(PDO $db, array $args): array
{
    $data = quoteData($db, $args);
    if (isset($data['Fld_Condition'])) {
        $data['Fld_Condition_ID'] = $data['Fld_Condition'];
        unset($data['Fld_Condition']);
    }
    if (isset($args['payment_term_id'])) {
        ref($db, 'payment_term', $args['payment_term_id']);
        $data['Fld_Payment_Term_ID'] = $args['payment_term_id'];
    }
    if (isset($args['delivery'])) {
        $data['Fld_Delivery'] = $args['delivery'];
    }
    return $data;
}

function createSupplierQuote(PDO $db, array $args, int $employee): array
{
    $line = rfqLine($db, $args['rfq_line_id'], $employee);
    $supplier = $args['supplier_id'];
    ref($db, 'company', $supplier, true);
    contact($db, $supplier, $args['contact_id']);
    $values = supplierQuoteData($db, $args);
    $withLine = query($db, "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tbl_RFQ_2' AND COLUMN_NAME = 'id_tbl_rfq1'")->fetchColumn();
    $lookup = [$line['Fld_RFQ_ID'], $supplier, (int)$line['Fld_Part_ID']];
    $lineClause = '';
    if ((int)$withLine > 0) {
        $lineClause = ' AND (id_tbl_rfq1 = ? OR id_tbl_rfq1 IS NULL OR id_tbl_rfq1 = 0)';
        $lookup[] = $args['rfq_line_id'];
        $values['id_tbl_rfq1'] = $args['rfq_line_id'];
    }
    if (query($db, 'SELECT ID FROM tbl_RFQ_2
        WHERE Fld_RFQ_ID = ? AND Fld_Supplier_ID = ? AND Fld_Part_ID = ?' . $lineClause . ' LIMIT 1 FOR UPDATE',
        $lookup)->fetchColumn() !== false) {
        throw new DomainException('Cotation fournisseur déjà présente pour cette pièce et cette référence');
    }
    $id = insert($db, 'tbl_RFQ_2', $values + [
        'Fld_RFQ_ID' => $line['Fld_RFQ_ID'], 'Fld_Supplier_ID' => $supplier, 'Fld_Part_ID' => (int)$line['Fld_Part_ID'],
        'Fld_Supplier_Contact_ID' => $args['contact_id'], 'Fld_Current_Date' => date('Y-m-d'), 'aci_contact' => $employee,
    ]);
    return ['id' => $id, 'rfq_reference' => $line['Fld_RFQ_ID']];
}

function updateSupplierQuote(PDO $db, array $args, int $employee): array
{
    $id = $args['id'];
    $quote = one($db, 'SELECT Fld_RFQ_ID FROM tbl_RFQ_2 WHERE ID = ? AND aci_contact = ? FOR UPDATE',
        [$id, $employee], 'Cotation fournisseur introuvable pour cet employé');
    $values = supplierQuoteData($db, $args);
    if (!$values) {
        throw new InvalidArgumentException('Aucune donnée modifiable fournie');
    }
    update($db, 'tbl_RFQ_2', $id, $values);
    return ['id' => $id, 'rfq_reference' => $quote['Fld_RFQ_ID']];
}

function updateOrderDraft(PDO $db, array $args, int $employee, bool $accept = false): array
{
    $id = $args['id'];
    $draft = ownedDocument($db, 'tbl_PO_Draft', $id, $employee);
    if (!in_array($draft['status'], ['DRAFT', 'CUSTOMER_PO_RECEIVED'], true)) {
        throw new DomainException('Ce brouillon ne peut plus être modifié');
    }
    $values = [];
    foreach (['customer_po_number', 'accepted_price', 'accepted_qty', 'accepted_condition_id', 'payment_terms',
        'shipping_terms', 'shipping_address', 'acceptance_notes'] as $field) {
        if (isset($args[$field])) {
            $values[$field] = $args[$field];
        }
    }
    if (isset($values['accepted_condition_id'])) {
        ref($db, 'condition', $values['accepted_condition_id']);
    }
    if ($accept) {
        $effective = $values + $draft;
        $proof = trim((string)($effective['customer_po_number'] ?? '')) !== ''
            || trim((string)($effective['customer_po_file'] ?? '')) !== '';
        $terms = trim((string)($effective['accepted_price'] ?? '')) !== ''
            && (int)($effective['accepted_qty'] ?? 0) > 0;
        $values['status'] = $proof && $terms ? 'ACCEPTED_ORDER' : 'CUSTOMER_PO_RECEIVED';
    }
    if (!$values) {
        throw new InvalidArgumentException('Aucune donnée modifiable fournie');
    }
    update($db, 'tbl_PO_Draft', $id, $values);
    return ['id' => $id, 'status' => $values['status'] ?? $draft['status']];
}

function readLegacyOrder(PDO $db, array $args, bool $sales): array
{
    $table = $sales ? 'tbl_Sales_Order' : 'tbl_PO_Customer';
    $fields = 'Fld_Row_ID AS id, Fld_PO AS customer_po_number, Fld_ACI_PO AS aci_po_number,
               Fld_Current_Date AS order_date, Fld_Company_Supplier_ID AS supplier_id';
    if ($sales) {
        $fields .= ', Fld_RFQ_ID AS rfq_reference, Fld_PO_Still_Open AS is_open';
    }
    $sql = "SELECT $fields FROM $table";
    if (isset($args['id'])) {
        return one($db, "$sql WHERE Fld_Row_ID = ?", [$args['id']], 'Commande historique introuvable');
    }
    $filters = [];
    $values = [];
    foreach (['supplier_id' => 'Fld_Company_Supplier_ID', 'aci_po_number' => 'Fld_ACI_PO',
        'rfq_reference' => 'Fld_RFQ_ID'] as $field => $column) {
        if (isset($args[$field])) {
            $filters[] = "$column = ?";
            $values[] = $args[$field];
        }
    }
    return page($db, $sql . ($filters ? ' WHERE ' . implode(' AND ', $filters) : '') . ' ORDER BY Fld_Row_ID DESC',
        $args, $values);
}

function normalizeAircraft(string $aircraft): string
{
    $aircraft = trim($aircraft);
    $model = str_replace(['-', '/', '\\'], ' ', strtoupper($aircraft));
    $family = '';
    if (strpos($model, 'ATR') !== false) {
        $family = 'ATR';
    } elseif (preg_match('/A3(18|19|20|21)/', $model)) {
        $family = 'A320 FAMILY';
    } elseif (preg_match('/B7(27|37|47|57|67|77|87)/', $model) || strpos($model, 'BOEING') !== false) {
        $family = 'BOEING';
    } elseif (preg_match('/DASH 8|Q400|DH8/', $model)) {
        $family = 'DASH 8';
    }
    return $family !== '' && stripos($aircraft, $family) === false ? "$family | $aircraft" : $aircraft;
}

function importCapabilities(PDO $db, array $args): array
{
    $company = $args['supplier_id'];
    ref($db, 'company', $company, true);
    $ids = [];
    $skipped = [];
    foreach ($args['items'] as $index => $item) {
        $partNumber = trim($item['part_number']);
        $aircraft = normalizeAircraft($item['aircraft'] ?? '');
        $capability = trim($item['capability']);
        if (query($db, "SELECT id_capa_list FROM tbl_capa_list
            WHERE id_company = ? AND pn = ? AND COALESCE(aircraft, '') = ? AND capability = ?
            LIMIT 1 FOR UPDATE", [$company, $partNumber, $aircraft, $capability])->fetchColumn() !== false) {
            $skipped[] = $index;
            continue;
        }
        $ids[] = insert($db, 'tbl_capa_list', [
            'pn' => $partNumber, 'descriptioin' => trim($item['description'] ?? ''),
            'aircraft' => $aircraft, 'ata' => trim($item['ata'] ?? ''), 'capability' => $capability,
            'pma' => trim($item['pma'] ?? ''), 'doa' => trim($item['doa'] ?? ''), 'der' => trim($item['der'] ?? ''),
            'code_oem' => trim($item['code_oem'] ?? ''), 'design_oem' => trim($item['design_oem'] ?? ''),
            'id_company' => $company, 'status' => '', 'entry_date' => date('Y-m-d'), 'comments' => '',
        ]);
    }
    return ['ids' => $ids, 'skipped_indexes' => $skipped];
}

$id = idSchema();
$pages = [
    'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
    'offset' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100000],
];
$line = objectSchema([
    'part_id' => $id, 'quantity' => $id, 'rfq_type_id' => $id,
    'condition_id' => $id, 'observation' => stringSchema(1000, 0),
], ['part_id', 'quantity', 'rfq_type_id', 'condition_id']);
$quoteFields = [
    'quantity' => $id, 'condition_id' => $id, 'price' => moneySchema(), 'currency_id' => $id,
    'serial_number' => stringSchema(100, 0), 'notes' => stringSchema(1000, 0),
    'lead_time' => stringSchema(100, 0), 'release_id' => $id,
];
$supplierQuoteFields = $quoteFields + ['payment_term_id' => $id, 'delivery' => stringSchema(200, 0)];
$orderTerms = [
    'customer_po_number' => stringSchema(100, 0), 'accepted_price' => moneySchema(),
    'accepted_qty' => $id, 'accepted_condition_id' => $id, 'payment_terms' => stringSchema(255, 0),
    'shipping_terms' => stringSchema(255, 0), 'shipping_address' => stringSchema(1000, 0),
    'acceptance_notes' => stringSchema(1000, 0),
];
$quoteFilters = ['rfq_reference' => rfqSchema(), 'company_id' => $id, 'part_id' => $id];
$capabilityItem = objectSchema([
    'part_number' => stringSchema(80), 'description' => stringSchema(200, 0), 'aircraft' => stringSchema(80, 0),
    'ata' => stringSchema(100, 0), 'capability' => stringSchema(100), 'pma' => stringSchema(100, 0),
    'doa' => stringSchema(100, 0), 'der' => stringSchema(100, 0), 'code_oem' => stringSchema(100, 0),
    'design_oem' => stringSchema(100, 0),
], ['part_number', 'capability']);
$invoiceStatus = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
$shipmentStatus = ['preparing', 'shipped', 'in_transit', 'delivered', 'returned'];
$orderStatus = ['DRAFT', 'CUSTOMER_PO_RECEIVED', 'ACCEPTED_ORDER', 'DOCS_PENDING', 'READY_TO_SHIP',
    'SHIPPED', 'CLOSED', 'CANCELLED'];
$idInput = objectSchema(['id' => $id], ['id']);
$ops = [
    'search_rfqs' => operation('rfqs.read', 'Recherche les lignes RFQ par référence, pièce, compagnie ou priorité.',
        objectSchema($pages + ['search' => stringSchema(100), 'company_id' => $id, 'priority_id' => $id]),
        __NAMESPACE__ . '\searchRfq'),
    'get_yoyamic_rfq' => operation('rfqs.read', 'Consulte une RFQ par référence exacte avec ses lignes paginées.',
        objectSchema(['rfq_reference' => rfqSchema()] + $pages, ['rfq_reference']),
        static function (PDO $db, array $args, int $employee): array {
            $rfq = $args['rfq_reference'];
            $header = one($db, 'SELECT Fld_RFQ_ID AS reference, Fld_Date AS request_date, Fld_Customer_ID AS company_id,
                Fld_RFQ_ACI_Employee_Id AS employee_id, Fld_Priority_ID AS priority_id,
                Fld_Step_1 AS step_1, Fld_Step_2 AS step_2, Fld_Step_3 AS step_3
                FROM tbl_RFQ WHERE Fld_RFQ_ID = ? ORDER BY ID DESC LIMIT 1', [$rfq], 'RFQ introuvable');
            $header['lines'] = page($db,
                'SELECT ID AS line_id, Fld_Part_ID AS part_id, pn_rfq AS part_number, Fld_Qty AS quantity,
                id_company_contact AS contact_id, Fld_Condition_ID AS condition_id, Fld_Observation AS observation
                FROM tbl_RFQ_1 WHERE Fld_RFQ_ID = ? ORDER BY ID', $args, [$rfq]);
            return $header;
        }),
    'create_rfq' => operation('rfqs.write', 'Crée une RFQ Yoyamic avec une ou plusieurs lignes dans une transaction.',
        objectSchema([
            'rfq_reference' => rfqSchema(), 'company_id' => $id, 'contact_id' => $id,
            'priority_id' => $id, 'payment_term_id' => $id, 'request_date' => dateSchema(),
            'lines' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 50, 'items' => $line],
        ], ['rfq_reference', 'company_id', 'contact_id', 'priority_id', 'payment_term_id', 'lines']),
        __NAMESPACE__ . '\createRfq', ['tbl_RFQ', 'tbl_RFQ_1']),
    'add_rfq_line' => operation('rfqs.write', 'Ajoute une ligne à une RFQ créée par cet employé.',
        objectSchema(['rfq_reference' => rfqSchema(), 'line' => $line], ['rfq_reference', 'line']),
        __NAMESPACE__ . '\addRfqLine', ['tbl_RFQ_1']),
    'update_rfq_line' => operation('rfqs.write', 'Modifie la quantité, la condition ou la remarque d’une ligne RFQ.',
        objectSchema(['line_id' => $id, 'quantity' => $id, 'condition_id' => $id, 'observation' => stringSchema(1000, 0)], ['line_id']),
        __NAMESPACE__ . '\updateRfqLine', ['tbl_RFQ_1']),
    'list_customer_quotes' => operation('quotations.read', 'Liste les cotations client par référence RFQ, compagnie ou pièce.',
        objectSchema($pages + $quoteFilters), __NAMESPACE__ . '\listQuotes'),
    'get_customer_quote' => operation('quotations.read', 'Consulte une cotation client et sa ligne RFQ.',
        $idInput, __NAMESPACE__ . '\listQuotes'),
    'create_customer_quotes' => operation('quotations.write', 'Crée des cotations client pour une ou plusieurs lignes de la RFQ, sans envoi.',
        objectSchema([
            'rfq_reference' => rfqSchema(),
            'lines' => [
                'type' => 'array', 'minItems' => 1, 'maxItems' => 50,
                'items' => objectSchema($quoteFields + [
                    'rfq_line_id' => $id, 'source_type' => enumSchema(['SQ', 'STOCK-ACI770', 'STOCK-External']),
                    'source_id' => $id,
                ],
                    ['rfq_line_id', 'quantity', 'condition_id', 'price', 'currency_id']),
            ],
        ], ['rfq_reference', 'lines']), __NAMESPACE__ . '\createQuotes', ['tbl_RFQ_3']),
    'update_customer_quote' => operation('quotations.write', 'Modifie les champs commerciaux d’une cotation client non envoyée.',
        objectSchema($quoteFields + ['id' => $id], ['id']), __NAMESPACE__ . '\updateQuote', ['tbl_RFQ_3']),
    'list_supplier_quotes' => operation('supplier_quotes.read', 'Liste les cotations fournisseur par RFQ, pièce ou fournisseur.',
        objectSchema($pages + $quoteFilters), __NAMESPACE__ . '\listSupplierQuotes'),
    'create_supplier_quote' => operation('supplier_quotes.write', 'Enregistre une cotation fournisseur sur une ligne RFQ de cet employé.',
        objectSchema($supplierQuoteFields + ['rfq_line_id' => $id, 'supplier_id' => $id, 'contact_id' => $id],
            ['rfq_line_id', 'supplier_id', 'contact_id', 'quantity', 'price', 'condition_id', 'currency_id', 'payment_term_id']),
        __NAMESPACE__ . '\createSupplierQuote', ['tbl_RFQ_2']),
    'update_supplier_quote' => operation('supplier_quotes.write', 'Modifie une cotation fournisseur enregistrée par cet employé.',
        objectSchema($supplierQuoteFields + ['id' => $id], ['id']),
        __NAMESPACE__ . '\updateSupplierQuote', ['tbl_RFQ_2']),
    'create_order_draft_from_quote' => operation('orders.write', 'Crée ou retrouve le brouillon de commande lié à une cotation client.',
        objectSchema(['quote_id' => $id], ['quote_id']), __NAMESPACE__ . '\createOrderDraft', ['tbl_PO_Draft']),
    'update_order_draft' => operation('orders.write', 'Modifie les conditions et le numéro de PO d’un brouillon de commande.',
        objectSchema($orderTerms + ['id' => $id], ['id']), __NAMESPACE__ . '\updateOrderDraft', ['tbl_PO_Draft']),
    'validate_customer_po' => operation('orders.write', 'Applique la transition native du PO client selon les pièces et valeurs disponibles.',
        objectSchema($orderTerms + ['id' => $id], ['id']), static function (PDO $db, array $args, int $employee): array {
            return updateOrderDraft($db, $args, $employee, true);
        }, ['tbl_PO_Draft']),
    'create_invoice' => operation('invoices.write', 'Crée une facture Yoyamic en brouillon.',
        objectSchema([
            'company_id' => $id, 'contact_id' => $id, 'currency_id' => $id, 'rfq_reference' => rfqSchema(),
            'invoice_type' => enumSchema(['proforma', 'commercial', 'credit_note']),
            'invoice_date' => dateSchema(), 'due_date' => dateSchema(), 'notes' => stringSchema(4000, 0),
        ], ['company_id', 'contact_id', 'currency_id', 'invoice_type']), __NAMESPACE__ . '\createInvoice', ['tbl_Invoices']),
    'add_invoice_item' => operation('invoices.write', 'Ajoute une ligne à une facture brouillon et recalcule les totaux décimaux.',
        objectSchema([
            'invoice_id' => $id, 'part_id' => $id, 'condition_id' => $id, 'description' => stringSchema(500, 0),
            'quantity' => $id, 'unit_price' => moneySchema(),
        ], ['invoice_id', 'part_id', 'condition_id', 'description', 'quantity', 'unit_price']),
        __NAMESPACE__ . '\addInvoiceItem', ['tbl_Invoices', 'tbl_Invoice_Items']),
    'update_invoice_item' => operation('invoices.write', 'Modifie une ligne d’une facture brouillon et recalcule les totaux.',
        objectSchema([
            'invoice_id' => $id, 'item_id' => $id, 'part_id' => $id, 'condition_id' => $id,
            'description' => stringSchema(500, 0), 'quantity' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000000],
            'unit_price' => moneySchema(),
        ], ['invoice_id', 'item_id', 'part_id', 'condition_id', 'description', 'quantity', 'unit_price']),
        __NAMESPACE__ . '\updateInvoiceItem', ['tbl_Invoices', 'tbl_Invoice_Items']),
    'mark_invoice_sent' => operation('invoices.write', 'Marque une facture comme envoyée dans Yoyamic. Cette action n’envoie aucun message.',
        $idInput, static function (PDO $db, array $args, int $employee): array {
            return markInvoice($db, $args, $employee, 'sent');
        }, ['tbl_Invoices']),
    'mark_invoice_paid' => operation('invoices.write', 'Enregistre le statut payé pour une facture envoyée ou échue.',
        $idInput, static function (PDO $db, array $args, int $employee): array {
            return markInvoice($db, $args, $employee, 'paid');
        }, ['tbl_Invoices']),
    'create_shipment' => operation('shipments.write', 'Crée une expédition en préparation avec un transporteur.',
        objectSchema([
            'invoice_id' => $id, 'rfq_reference' => rfqSchema(), 'company_id' => $id, 'shipper_id' => $id,
            'ship_from' => stringSchema(1000), 'ship_to' => stringSchema(1000), 'ship_date' => dateSchema(),
            'estimated_arrival' => dateSchema(), 'weight' => stringSchema(50, 0),
            'dimensions' => stringSchema(100, 0), 'notes' => stringSchema(4000, 0),
        ], ['company_id', 'shipper_id', 'ship_from', 'ship_to']), __NAMESPACE__ . '\createShipment', ['tbl_Shipping']),
    'update_shipment_status' => operation('shipments.write', 'Enregistre un changement de statut d’expédition.',
        objectSchema(['id' => $id, 'status' => enumSchema($shipmentStatus)], ['id', 'status']),
        __NAMESPACE__ . '\updateShipment', ['tbl_Shipping']),
    'update_shipment_tracking' => operation('shipments.write', 'Enregistre le numéro de suivi et le statut expédié.',
        objectSchema(['id' => $id, 'tracking_number' => stringSchema(200)], ['id', 'tracking_number']),
        __NAMESPACE__ . '\updateShipment', ['tbl_Shipping']),
    'list_supplier_capabilities' => operation('repairs.read', 'Liste les capacités de réparation attestées par fournisseur ou pièce.',
        objectSchema($pages + ['company_id' => $id, 'part_number' => stringSchema(80)]), __NAMESPACE__ . '\listCapabilities'),
    'import_supplier_capabilities' => operation('repairs.write', 'Importe un lot structuré de capacités et signale les doublons ignorés.',
        objectSchema([
            'supplier_id' => $id,
            'items' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 100, 'items' => $capabilityItem],
        ], ['supplier_id', 'items']), __NAMESPACE__ . '\importCapabilities', ['tbl_capa_list']),
    'get_erp_dashboard' => operation('reports.read', 'Consulte les compteurs Yoyamic de compagnies, stock, RFQ et cotations.',
        objectSchema([]), __NAMESPACE__ . '\dashboard'),
];
foreach ([
    ['invoice', 'invoices', 'list_invoices', 'get_invoice', 'factures', $invoiceStatus],
    ['shipment', 'shipments', 'list_shipments', 'get_shipment', 'expéditions', $shipmentStatus],
    ['order', 'orders', 'list_order_drafts', 'get_order_draft', 'brouillons de commande', $orderStatus],
] as [$kind, $scope, $listName, $getName, $label, $statuses]) {
    $ops[$listName] = operation("$scope.read", "Liste les $label par compagnie, numéro ou statut.",
        objectSchema($pages + ['company_id' => $id, 'number' => stringSchema(60), 'status' => enumSchema($statuses)]),
        static function (PDO $db, array $args) use ($kind): array {
            return lookupDocument($db, $args, $kind);
        });
    $ops[$getName] = operation("$scope.read", "Consulte le détail et les lignes des $label.",
        objectSchema($pages + ['id' => $id], ['id']),
        static function (PDO $db, array $args) use ($kind): array {
            return lookupDocument($db, $args, $kind, true);
        });
}
foreach ([
    [true, 'sales', 'de vente'],
    [false, 'purchase', 'd’achat'],
] as [$sales, $label, $description]) {
    $legacyFilters = $pages + ['supplier_id' => $id, 'aci_po_number' => stringSchema(60)];
    if ($sales) {
        $legacyFilters['rfq_reference'] = rfqSchema();
    }
    $ops["list_{$label}_orders"] = operation('orders.read', "Liste les commandes $description enregistrées.",
        objectSchema($legacyFilters), static function (PDO $db, array $args) use ($sales): array {
            return readLegacyOrder($db, $args, $sales);
        });
    $ops["get_{$label}_order"] = operation('orders.read', "Consulte une commande $description enregistrée.",
        $idInput, static function (PDO $db, array $args) use ($sales): array {
            return readLegacyOrder($db, $args, $sales);
        });
}
return $ops;
