<?php
declare(strict_types=1);

function erp_ops_string(array $args, string $key, int $max, bool $required = false): string
{
    $value = $args[$key] ?? '';
    if (!is_string($value) || strlen($value) > $max || preg_match('/[\x00-\x1f\x7f]/', $value)
        || ($required && trim($value) === '')) {
        throw new InvalidArgumentException('Champ invalide : ' . $key);
    }
    return trim($value);
}

function erp_ops_id(array $args, string $key): int
{
    $id = $args[$key] ?? null;
    if (!is_int($id) || $id < 1 || $id > 2147483647) {
        throw new InvalidArgumentException('Identifiant invalide : ' . $key);
    }
    return $id;
}

function erp_ops_list(PDO $db, string $sql, array $args, array $values = []): array
{
    $limit = $args['limit'] ?? 25;
    $offset = $args['offset'] ?? 0;
    if (!is_int($limit) || $limit < 1 || $limit > 100 || !is_int($offset) || $offset < 0 || $offset > 100000) {
        throw new InvalidArgumentException('Pagination invalide');
    }
    $stmt = $db->prepare($sql . " LIMIT $limit OFFSET $offset");
    $stmt->execute($values);
    return ['items' => $stmt->fetchAll()];
}

function erp_ops_shipper(PDO $db, array $args, bool $create): array
{
    $name = erp_ops_string($args, 'name', 120, true);
    $contact = erp_ops_string($args, 'contact_name', 120);
    $phone = erp_ops_string($args, 'contact_phone', 40);
    $id = $create ? null : erp_ops_id($args, 'shipper_id');

    $duplicate = $db->prepare('SELECT Fld_Shipper_ID FROM tbl_Shipper WHERE Fld_Shipper_Text = ?'
        . ($create ? '' : ' AND Fld_Shipper_ID <> ?') . ' LIMIT 1 FOR UPDATE');
    $duplicate->execute($create ? [$name] : [$name, $id]);
    if ($duplicate->fetchColumn() !== false) {
        throw new DomainException('Transporteur déjà enregistré');
    }
    if ($create) {
        $statement = $db->prepare('INSERT INTO tbl_Shipper
            (Fld_Shipper_Text, Fld_Shipper_Contact_Name, Fld_Shipper_Contact_Phone) VALUES (?, ?, ?)');
        $statement->execute([$name, $contact, $phone]);
        $id = (int) $db->lastInsertId();
        if ($id < 1) {
            throw new RuntimeException('Missing shipper ID');
        }
    } else {
        $find = $db->prepare('SELECT Fld_Shipper_ID FROM tbl_Shipper WHERE Fld_Shipper_ID = ? FOR UPDATE');
        $find->execute([$id]);
        if ($find->fetchColumn() === false) {
            throw new OutOfBoundsException('Transporteur introuvable');
        }
        $statement = $db->prepare('UPDATE tbl_Shipper SET Fld_Shipper_Text = ?,
            Fld_Shipper_Contact_Name = ?, Fld_Shipper_Contact_Phone = ? WHERE Fld_Shipper_ID = ?');
        $statement->execute([$name, $contact, $phone, $id]);
    }
    return ['shipper_id' => $id];
}

$id = ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647];
$page = ['limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
    'offset' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100000]];
$readRoles = ['User', 'Sales', 'Manager', 'Admin', 'SuperAdmin'];
$opsRoles = ['Manager', 'Admin', 'SuperAdmin'];
$shipperFields = [
    'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 20],
    'contact_name' => ['type' => 'string', 'maxLength' => 30],
    'contact_phone' => ['type' => 'string', 'maxLength' => 20],
];
$schema = static fn (array $properties, array $required = []): array =>
    ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
$recover = [
    'list_yoyamic_mcp_audit' => [
        'scope' => 'aerocanada.admin.read', 'roles' => ['SuperAdmin'],
        'description' => 'Liste les identifiants et dates des écritures auditées par MCP.',
        'inputSchema' => $schema($page + ['employee_id' => $id]),
        'handler' => static function (PDO $db, array $args, int $employeeId): array {
            $filter = isset($args['employee_id']) ? ' WHERE employee_id = ?' : '';
            $bind = isset($args['employee_id']) ? [erp_ops_id($args, 'employee_id')] : [];
            return erp_ops_list($db, 'SELECT employee_id, operation, request_id, created_at
                FROM mcp_erp_audit' . $filter . ' ORDER BY created_at DESC, request_id', $args, $bind);
        },
    ],
    'list_shippers' => [
        'scope' => 'aerocanada.shipping.read', 'roles' => $readRoles,
        'description' => 'Liste des transporteurs et de leurs coordonnées.',
        'inputSchema' => $schema($page),
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_ops_list($db,
            'SELECT Fld_Shipper_ID AS shipper_id, Fld_Shipper_Text AS name,
                Fld_Shipper_Contact_Name AS contact_name, Fld_Shipper_Contact_Phone AS contact_phone
                FROM tbl_Shipper ORDER BY Fld_Shipper_ID', $args),
    ],
    'create_shipper' => [
        'scope' => 'aerocanada.shipping.write', 'roles' => $opsRoles,
        'description' => 'Crée un transporteur dans Yoyamic.',
        'inputSchema' => $schema($shipperFields, ['name']), 'writeTables' => ['tbl_Shipper'],
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_ops_shipper($db, $args, true),
    ],
    'update_shipper' => [
        'scope' => 'aerocanada.shipping.write', 'roles' => $opsRoles,
        'description' => 'Remplace les coordonnées d’un transporteur existant.',
        'inputSchema' => $schema($shipperFields + ['shipper_id' => $id], ['shipper_id', 'name',
            'contact_name', 'contact_phone']), 'writeTables' => ['tbl_Shipper'],
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_ops_shipper($db, $args, false),
    ],
    'list_yoyamic_users' => [
        'scope' => 'aerocanada.admin.read', 'roles' => ['SuperAdmin'],
        'description' => 'Liste les employés Yoyamic et leurs rôles.',
        'inputSchema' => $schema($page),
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_ops_list($db,
            'SELECT Employee_ID AS employee_id, Employee_Name AS name, email, statut AS role, position
                FROM tbl_Employee ORDER BY Employee_ID', $args),
    ],
    'update_yoyamic_user_role' => [
        'scope' => 'aerocanada.admin.write', 'roles' => ['SuperAdmin'],
        'description' => 'Modifie le rôle d’un autre employé après vérification du rôle actuel.',
        'inputSchema' => $schema([
            'employee_id' => $id,
            'expected_role' => ['type' => 'string', 'enum' => $readRoles],
            'role' => ['type' => 'string', 'enum' => $readRoles],
        ], ['employee_id', 'expected_role', 'role']),
        'writeTables' => ['tbl_Employee'],
        'handler' => static function (PDO $db, array $args, int $employeeId) use ($readRoles): array {
            $target = erp_ops_id($args, 'employee_id');
            $role = $args['role'] ?? null;
            $expected = $args['expected_role'] ?? null;
            if (!in_array($role, $readRoles, true) || !in_array($expected, $readRoles, true)) {
                throw new InvalidArgumentException('Rôle Yoyamic invalide');
            }
            if ($target === $employeeId || $employeeId < 1 || !$db->inTransaction()) {
                throw new DomainException('Modification du rôle refusée pour cet employé');
            }
            $read = $db->prepare('SELECT statut FROM tbl_Employee WHERE Employee_ID = ? FOR UPDATE');
            $read->execute([$target]);
            $current = $read->fetchColumn();
            if ($current === false) {
                throw new OutOfBoundsException('Employé introuvable');
            }
            if ($current !== $expected) {
                throw new DomainException('Le rôle actuel a changé');
            }
            if ($current !== $role) {
                $update = $db->prepare('UPDATE tbl_Employee SET statut = ? WHERE Employee_ID = ?');
                $update->execute([$role, $target]);
            }
            return ['employee_id' => $target, 'previous_role' => $current, 'role' => $role];
        },
    ],
    'get_yoyamic_profile' => [
        'scope' => 'aerocanada.profile.read', 'roles' => $readRoles,
        'description' => 'Consulte le profil Yoyamic de l’employé associé au jeton.',
        'inputSchema' => $schema([]),
        'handler' => static function (PDO $db, array $args, int $employeeId): array {
            $statement = $db->prepare('SELECT Employee_ID AS employee_id, Employee_Name AS name, email,
                statut AS role, position, tel, mobile FROM tbl_Employee WHERE Employee_ID = ?');
            $statement->execute([$employeeId]);
            return $statement->fetch() ?: throw new OutOfBoundsException('Employé introuvable');
        },
    ],
];
$references = [
    'currencies' => ['tbl_Currency', 'Fld_Currency_ID', 'Fld_Currency_Text'],
    'rfq_priorities' => ['tbl_Priority', 'Fld_Priority_ID', 'Fld_Priority_Text'],
    'rfq_types' => ['tbl_RFQ_Type', 'Fld_RFQ_Type_ID', 'Fld_RFQ_Type_Text'],
    'part_conditions' => ['tbl_Condition', 'Fld_Condition_ID', 'Fld_Condition_Text'],
    'release_types' => ['tbl_Release', 'Fld_Release_ID', 'Fld_Release_Text'],
];
foreach ($references as $resource => [$table, $fieldId, $label]) {
    $recover['list_' . $resource] = [
        'scope' => 'aerocanada.reference.read', 'roles' => $readRoles,
        'description' => 'Liste les valeurs du référentiel Yoyamic ' . $resource,
        'inputSchema' => $schema($page),
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_ops_list($db,
            "SELECT `$fieldId` AS id, `$label` AS label FROM `$table` ORDER BY `$fieldId`", $args),
    ];
}
return $recover;
