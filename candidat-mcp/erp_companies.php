<?php

declare(strict_types=1);

/** Yoyamic adapters. Reference: pages/v2/api/companies.php and classes/company.class.php. */

$readRoles = ['User', 'Sales', 'Manager', 'Admin', 'SuperAdmin'];
$writeRoles = ['Sales', 'Manager', 'Admin', 'SuperAdmin'];
$archiveRoles = ['Manager', 'Admin', 'SuperAdmin'];
$id = ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647];
$text = static fn (int $length): array => ['type' => 'string', 'maxLength' => $length];
$name = ['type' => 'string', 'minLength' => 1, 'maxLength' => 160];
$pagination = [
    'after_id' => $id,
    'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
];
$companyFields = [
    'name' => ['Fld_Company_Name', $name],
    'website' => ['internet', $text(250)],
    'cage_code' => ['cage_code', $text(20)],
];
$amount = ['type' => 'string', 'maxLength' => 11, 'pattern' => '^[0-9]{1,11}$'];
$paymentFields = [
    'customer_term_id' => ['customer_payment_term_id', $id],
    'customer_amount' => ['customer_payment_term_amount', $amount],
    'customer_currency_id' => ['customer_payment_term_currencyid', $id],
    'aci_term_id' => ['aci_payment_term_id', $id],
    'aci_amount' => ['aci_payment_term_amount', $amount],
    'aci_currency_id' => ['aci_payment_term_currencyid', $id],
];
$contactFields = [
    'name' => ['Fld_Contact_Name', ['type' => 'string', 'minLength' => 1, 'maxLength' => 150]],
    'email' => ['Fld_Contact_Email', $text(250)],
    'phone' => ['Fld_Contact_Phone', $text(30)],
    'alternate_phone' => ['Fld_Contact_Phone2', $text(30)],
    'mobile' => ['Fld_Company_Mobile', $text(30)],
    'title' => ['Fld_Contact_Title', $text(200)],
];
$fleetFields = [
    'region_id' => ['Fld_Region', $id],
    'aircraft_id' => ['Fld_AC_ID', $id],
    'engine' => ['Fld_Engine', $text(48)],
    'unit' => ['Fld_Unit', $text(17)],
    'serial_number' => ['msn', $text(10)],
    'registration' => ['immat', $text(10)],
];
$addressFields = [
    'company_type_id' => ['Fld_Company_Type_ID', $id],
    'address_type_id' => ['Fld_Company_Address_Type', $id],
    'title' => ['title_address', $text(100)],
    'country' => ['Fld_Company_Country', $text(100)],
    'city' => ['Fld_Company_City', $text(100)],
    'state' => ['Fld_Company_State', $text(100)],
    'street' => ['Fld_Company_Street', $text(200)],
    'postal_code' => ['Fld_Company_ZipCode', $text(32)],
    'phone' => ['Fld_Company_Phone', $text(50)],
    'email' => ['Fld_Company_Email', $text(254)],
    'vat_number' => ['Fld_VAT_Nbr', $text(64)],
    'timezone' => ['UTC_timezone', $text(64)],
];

$query = static function (PDO $db, string $sql, array $parameters = []): PDOStatement {
    $statement = $db->prepare($sql);
    foreach (array_values($parameters) as $index => $value) {
        $statement->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $statement->execute();
    return $statement;
};

$tool = static function (
    string $scope,
    array $roles,
    string $description,
    array $properties,
    array $required,
    callable $callback,
    array $writeTables = []
): array {
    return [
        'scope' => "aerocanada.$scope",
        'roles' => $roles,
        'description' => $description,
        'writeTables' => $writeTables,
        'inputSchema' => [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ],
        'handler' => static function (PDO $db, array $args, int $employeeId) use ($properties, $required, $callback): array {
            if ($employeeId < 1) {
                throw new LogicException('Employé Yoyamic non vérifié.');
            }
            if (array_diff_key($args, $properties) || array_diff($required, array_keys($args))) {
                throw new InvalidArgumentException('Arguments requis ou inconnus.');
            }
            foreach ($args as $key => &$value) {
                $rule = $properties[$key];
                if ($rule['type'] === 'integer') {
                    if (!is_int($value) || $value < $rule['minimum'] || $value > $rule['maximum']) {
                        throw new InvalidArgumentException("Entier hors limites pour $key.");
                    }
                    continue;
                }
                if (!is_string($value) || preg_match('//u', $value) !== 1 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
                    throw new InvalidArgumentException("Texte invalide pour $key.");
                }
                $value = trim($value);
                if (strlen($value) > $rule['maxLength'] || strlen($value) < ($rule['minLength'] ?? 0)) {
                    throw new InvalidArgumentException("Longueur invalide pour $key.");
                }
                if (isset($rule['pattern']) && !preg_match('/' . $rule['pattern'] . '/D', $value)) {
                    throw new InvalidArgumentException("Format invalide pour $key.");
                }
                if (isset($rule['enum']) && !in_array($value, $rule['enum'], true)) {
                    throw new InvalidArgumentException("Valeur invalide pour $key.");
                }
                if ($key === 'email' && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    throw new InvalidArgumentException('Adresse e-mail invalide.');
                }
                if ($key === 'timezone' && $value !== '' && !in_array($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
                    throw new InvalidArgumentException('Fuseau horaire IANA invalide.');
                }
            }
            unset($value);
            return $callback($db, $args, $employeeId);
        },
    ];
};

$properties = static fn (array $fields): array => array_map(static fn (array $field): array => $field[1], $fields);
$columnValues = static function (array $fields, array $args): array {
    $values = [];
    foreach (array_intersect_key($args, $fields) as $key => $value) {
        $values[$fields[$key][0]] = $value;
    }
    return $values;
};
$insert = static function (PDO $db, string $table, array $values) use ($query): int {
    $columns = implode(', ', array_map(static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`', array_keys($values)));
    $bindings = implode(', ', array_fill(0, count($values), '?'));
    $query($db, "INSERT INTO $table ($columns) VALUES ($bindings)", $values);
    $id = (int) $db->lastInsertId();
    if ($id < 1) {
        throw new RuntimeException('Identifiant créé indisponible.');
    }
    return $id;
};
$update = static function (PDO $db, string $table, string $key, int $recordId, array $values) use ($query): void {
    if (!$values) {
        throw new InvalidArgumentException('Au moins un champ modifiable est requis.');
    }
    $assignments = implode(', ', array_map(static fn (string $field): string => '`' . str_replace('`', '``', $field) . '` = ?', array_keys($values)));
    $key = '`' . str_replace('`', '``', $key) . '`';
    $query($db, "UPDATE $table SET $assignments WHERE $key = ?", [...array_values($values), $recordId]);
};
$one = static function (PDO $db, string $sql, array $parameters) use ($query): array {
    $row = $query($db, $sql, $parameters)->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        throw new OutOfBoundsException('Enregistrement introuvable.');
    }
    return $row;
};
$page = static function (PDO $db, string $sql, array $parameters, array $args) use ($query): array {
    $limit = $args['limit'] ?? 25;
    $rows = $query($db, "$sql LIMIT ?", [...$parameters, $limit + 1])->fetchAll(PDO::FETCH_ASSOC);
    $more = count($rows) > $limit;
    if ($more) {
        array_pop($rows);
    }
    return ['items' => $rows, 'next_after_id' => $more ? (int) $rows[count($rows) - 1]['id'] : null];
};
$match = static fn (string $value): string => '%' . strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
$companyColumns = 'c.Fld_Company_ID AS id, c.Fld_Company_Name AS name, c.internet AS website,
    c.cage_code, c.status, c.aci_contact AS employee_id, c.companyrating AS rating';
$paymentColumns = implode(', ', array_map(
    static fn (string $key, array $field): string => $field[0] . " AS $key",
    array_keys($paymentFields),
    array_values($paymentFields)
));
$contactColumns = 't.id_company_contact AS id, t.Fld_Company_ID AS company_id, t.Fld_Contact_Name AS name,
    t.Fld_Contact_Email AS email, t.Fld_Contact_Phone AS phone, t.Fld_Contact_Phone2 AS alternate_phone,
    t.Fld_Company_Mobile AS mobile, t.Fld_Contact_Title AS title, t.status';
$addressColumns = 'id_tbl_company_Details AS id, Fld_Company_ID AS company_id, Fld_Company_Type_ID AS company_type_id,
    Fld_Company_Address_Type AS address_type_id, title_address AS title, Fld_Company_Country AS country,
    Fld_Company_City AS city, Fld_Company_State AS state, Fld_Company_Street AS street,
    Fld_Company_ZipCode AS postal_code, Fld_Company_Phone AS phone, Fld_Company_Email AS email,
    Fld_VAT_Nbr AS vat_number, UTC_timezone AS timezone';
$lockCompany = static function (PDO $db, int $companyId) use ($one): array {
    return $one($db, 'SELECT status FROM tb_company WHERE Fld_Company_ID = ? FOR UPDATE', [$companyId]);
};
$lockAvailableCompany = static function (PDO $db, int $companyId) use ($lockCompany): void {
    if ($lockCompany($db, $companyId)['status'] !== 'Available') {
        throw new OutOfBoundsException('Compagnie active introuvable.');
    }
};
$checkCompanyName = static function (PDO $db, string $name, int $exceptId = 0) use ($query): void {
    $existing = $query($db, 'SELECT Fld_Company_ID FROM tb_company WHERE Fld_Company_Name = ?
        AND Fld_Company_ID <> ? LIMIT 1 FOR UPDATE', [$name, $exceptId])->fetchColumn();
    if ($existing !== false) {
        throw new DomainException('Une compagnie porte déjà ce nom.');
    }
};
$checkContactEmail = static function (PDO $db, int $companyId, string $email, int $exceptId = 0) use ($query): void {
    if ($email === '') {
        return;
    }
    $existing = $query($db, 'SELECT id_company_contact FROM tb_company_contact WHERE Fld_Company_ID = ?
        AND Fld_Contact_Email = ? AND id_company_contact <> ? LIMIT 1 FOR UPDATE',
        [$companyId, $email, $exceptId])->fetchColumn();
    if ($existing !== false) {
        throw new DomainException('Cette adresse e-mail existe déjà dans la compagnie.');
    }
};

$checkFleetReferences = static function (PDO $db, array $args) use ($one): void {
    if (isset($args['region_id'])) {
        $one($db, 'SELECT Region_ID FROM tbl_Region WHERE Region_ID = ?', [$args['region_id']]);
    }
    if (isset($args['aircraft_id'])) {
        $one($db, 'SELECT Fld_AC_ID FROM tbl_Aircraft WHERE Fld_AC_ID = ?', [$args['aircraft_id']]);
    }
};
$companyId = ['company_id' => $id];
$contactId = ['contact_id' => $id];
$addressId = ['address_id' => $id];
$companyRead = 'companies.read';
$companyWrite = 'companies.write';
$contactRead = 'contacts.read';
$contactWrite = 'contacts.write';

return [
    'search_companies' => $tool($companyRead, $readRoles, 'Recherche les compagnies par nom, code CAGE et pays.', [
        'query' => $text(120), 'cage_code' => $text(32), 'country' => $text(100),
        'company_type_id' => $id, 'employee_id' => $id, 'rating' => $text(32),
        'status' => ['type' => 'string', 'maxLength' => 10, 'enum' => ['Available', 'archive']],
    ] + $pagination, [], static function (PDO $db, array $args) use ($page, $companyColumns, $match): array {
        $sql = "SELECT $companyColumns FROM tb_company c WHERE c.Fld_Company_ID > ? AND c.status = ?";
        $parameters = [$args['after_id'] ?? 0, $args['status'] ?? 'Available'];
        if (($args['query'] ?? '') !== '') {
            $sql .= " AND (c.Fld_Company_Name LIKE ? ESCAPE '!' OR c.cage_code LIKE ? ESCAPE '!')";
            array_push($parameters, $match($args['query']), $match($args['query']));
        }
        if (($args['cage_code'] ?? '') !== '') {
            $sql .= ' AND c.cage_code = ?';
            $parameters[] = $args['cage_code'];
        }
        if (isset($args['employee_id'])) {
            $sql .= ' AND c.aci_contact = ?';
            $parameters[] = $args['employee_id'];
        }
        if (($args['rating'] ?? '') !== '') {
            $sql .= ' AND c.companyrating = ?';
            $parameters[] = $args['rating'];
        }
        $addressConditions = [];
        if (($args['country'] ?? '') !== '') {
            $addressConditions[] = 'd.Fld_Company_Country = ?';
            $parameters[] = $args['country'];
        }
        if (isset($args['company_type_id'])) {
            $addressConditions[] = 'd.Fld_Company_Type_ID = ?';
            $parameters[] = $args['company_type_id'];
        }
        if ($addressConditions) {
            $sql .= ' AND EXISTS (SELECT 1 FROM tbl_Company_Details d WHERE d.Fld_Company_ID = c.Fld_Company_ID AND '
                . implode(' AND ', $addressConditions) . ')';
        }
        return $page($db, "$sql ORDER BY c.Fld_Company_ID", $parameters, $args);
    }),
    'get_company' => $tool($companyRead, $readRoles, 'Consulte une fiche compagnie par identifiant Yoyamic.', $companyId,
        ['company_id'], static fn (PDO $db, array $args): array => $one($db,
            "SELECT $companyColumns FROM tb_company c WHERE c.Fld_Company_ID = ?", [$args['company_id']])),
    'lookup_company_cage_code' => $tool($companyRead, $readRoles, 'Recherche le code CAGE exact des compagnies actives.',
        ['cage_code' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 32]] + $pagination,
        ['cage_code'], static fn (PDO $db, array $args): array => $page($db,
            "SELECT $companyColumns FROM tb_company c WHERE c.cage_code = ? AND c.status = 'Available'
                AND c.Fld_Company_ID > ? ORDER BY c.Fld_Company_ID", [$args['cage_code'], $args['after_id'] ?? 0], $args)),
    'create_company' => $tool($companyWrite, $writeRoles, 'Crée une compagnie active au nom de l’employé vérifié.',
        $properties($companyFields), ['name'],
        static function (PDO $db, array $args, int $employeeId) use ($insert, $columnValues, $companyFields, $checkCompanyName): array {
            $checkCompanyName($db, $args['name']);
            $values = $columnValues($companyFields, $args) + [
                // tb_company has legacy NOT NULL columns without SQL defaults.
                // Match the values emitted by Yoyamic's native company form so
                // strict SQL mode and the real schema behave like the test schema.
                'Company_Old_Id' => 0, 'Fld_Company_Rating_ID' => 0, 'delete' => '',
                'aci_contact' => $employeeId, 'logocompany' => '', 'status' => 'Available',
                'internet' => '', 'cage_code' => '', 'companyrating' => 0,
                'customer_payment_term_id' => 0, 'customer_payment_term_amount' => 0,
                'customer_payment_term_currencyid' => 0, 'aci_payment_term_id' => 0,
                'aci_payment_term_amount' => 0, 'aci_payment_term_currencyid' => 0,
            ];
            return ['id' => $insert($db, 'tb_company', $values)];
        }, ['tb_company']),
    'update_company' => $tool($companyWrite, $writeRoles, 'Modifie le nom, le site ou le code CAGE d’une compagnie active.',
        $companyId + $properties($companyFields), ['company_id'],
        static function (PDO $db, array $args) use ($update, $columnValues, $companyFields, $lockAvailableCompany, $checkCompanyName): array {
            $id = $args['company_id'];
            $lockAvailableCompany($db, $id);
            if (isset($args['name'])) {
                $checkCompanyName($db, $args['name'], $id);
            }
            $update($db, 'tb_company', 'Fld_Company_ID', $id, $columnValues($companyFields, $args));
            return ['id' => $id];
        }, ['tb_company']),
    'archive_company' => $tool($companyWrite, $archiveRoles, 'Archive une compagnie en conservant ses enregistrements.',
        $companyId, ['company_id'], static function (PDO $db, array $args) use ($lockCompany, $update): array {
            $id = $args['company_id'];
            $lockCompany($db, $id);
            $update($db, 'tb_company', 'Fld_Company_ID', $id, ['status' => 'archive']);
            return ['id' => $id, 'status' => 'archive'];
        }, ['tb_company']),
    'get_company_payment_terms' => $tool($companyRead, $writeRoles, 'Consulte les échéances, montants et devises client et ACI.',
        $companyId, ['company_id'], static fn (PDO $db, array $args): array => $one($db,
            "SELECT $paymentColumns FROM tb_company WHERE Fld_Company_ID = ?", [$args['company_id']])),
    'update_company_payment_terms' => $tool($companyWrite, $archiveRoles, 'Modifie les conditions de paiement d’une compagnie active.',
        $companyId + $properties($paymentFields), ['company_id'],
        static function (PDO $db, array $args) use ($paymentFields, $columnValues, $lockAvailableCompany, $one, $update): array {
            $values = $columnValues($paymentFields, $args);
            if ($values === []) {
                throw new InvalidArgumentException('Au moins une condition de paiement est requise.');
            }
            $company = $args['company_id'];
            $lockAvailableCompany($db, $company);
            foreach (['customer', 'aci'] as $side) {
                $term = $args[$side . '_term_id'] ?? null;
                $amount = $args[$side . '_amount'] ?? null;
                $currency = $args[$side . '_currency_id'] ?? null;
                if ($term !== null) {
                    $one($db, 'SELECT Fld_Payment_Term_ID FROM tbl_Payment WHERE Fld_Payment_Term_ID = ?', [$term]);
                }
                if ($amount !== null && $currency === null) {
                    throw new InvalidArgumentException('Un montant doit préciser sa devise.');
                }
                if ($currency !== null) {
                    $one($db, 'SELECT Fld_Currency_ID FROM tbl_Currency WHERE Fld_Currency_ID = ?', [$currency]);
                }
            }
            $update($db, 'tb_company', 'Fld_Company_ID', $company, $values);
            return ['id' => $company];
        }, ['tb_company']),
    'list_company_addresses' => $tool($companyRead, $readRoles, 'Liste les adresses d’une compagnie.',
        $companyId + $pagination, ['company_id'], static fn (PDO $db, array $args): array => $page($db,
            "SELECT $addressColumns FROM tbl_Company_Details WHERE Fld_Company_ID = ?
                AND id_tbl_company_Details > ? ORDER BY id_tbl_company_Details",
            [$args['company_id'], $args['after_id'] ?? 0], $args)),
    'create_company_address' => $tool($companyWrite, $writeRoles, 'Ajoute une adresse à une compagnie active.',
        $companyId + $properties($addressFields), ['company_id', 'company_type_id', 'address_type_id'],
        static function (PDO $db, array $args, int $employeeId) use ($insert, $columnValues, $addressFields, $lockAvailableCompany, $one): array {
            if (($args['city'] ?? '') === '' && ($args['country'] ?? '') === '') {
                throw new InvalidArgumentException('Ville ou pays requis.');
            }
            $lockAvailableCompany($db, $args['company_id']);
            $one($db, 'SELECT Fld_Company_Type_ID FROM tbl_Company_Type WHERE Fld_Company_Type_ID = ?', [$args['company_type_id']]);
            $one($db, 'SELECT Fld_Division_ID FROM tbl_Division WHERE Fld_Division_ID = ?', [$args['address_type_id']]);
            $fields = $columnValues($addressFields, $args) + [
                // Match the legacy Yoyamic popup form's explicit defaults; the
                // production table has no defaults for these NOT NULL columns.
                'Fld_Linked_ID' => 0, 'Fld_Company_ID' => $args['company_id'], 'Company_Old_Id' => 0,
                'Fld_Company_Country' => $args['country'] ?? 'NO COUNTRY',
                'Fld_Company_City' => $args['city'] ?? 'NO CITY',
                'Fld_Company_State' => '', 'Fld_Company_Street' => 'No address',
                'Fld_Company_ZipCode' => '', 'Fld_Company_Fax' => '', 'Fld_Company_Phone' => '',
                'Fld_Company_Email' => '', 'Fld_Company_Score' => '',
                'Fld_Company_BAX_Contact' => (string)$employeeId, 'Fld_Remark' => 'No remark',
                'Fld_VAT_Nbr' => '', 'Fld_Date_Of_First_Contact' => '', 'UTC_timezone' => '', 'title_address' => '',
            ];
            return ['id' => $insert($db, 'tbl_Company_Details', $fields)];
        }, ['tbl_Company_Details']),
    'update_company_address' => $tool($companyWrite, $writeRoles, 'Modifie une adresse appartenant à la compagnie active indiquée.',
        $companyId + $addressId + $properties($addressFields), ['company_id', 'address_id'],
        static function (PDO $db, array $args) use ($update, $columnValues, $addressFields, $lockAvailableCompany, $one): array {
            $lockAvailableCompany($db, $args['company_id']);
            $one($db, 'SELECT id_tbl_company_Details FROM tbl_Company_Details
                WHERE id_tbl_company_Details = ? AND Fld_Company_ID = ? FOR UPDATE', [$args['address_id'], $args['company_id']]);
            if (isset($args['company_type_id'])) {
                $one($db, 'SELECT Fld_Company_Type_ID FROM tbl_Company_Type WHERE Fld_Company_Type_ID = ?', [$args['company_type_id']]);
            }
            if (isset($args['address_type_id'])) {
                $one($db, 'SELECT Fld_Division_ID FROM tbl_Division WHERE Fld_Division_ID = ?', [$args['address_type_id']]);
            }
            $update($db, 'tbl_Company_Details', 'id_tbl_company_Details', $args['address_id'], $columnValues($addressFields, $args));
            return ['id' => $args['address_id']];
        }, ['tbl_Company_Details']),
    'list_company_fleet' => $tool($companyRead, $readRoles, 'Liste les aéronefs associés à une compagnie.',
        $companyId + $pagination, ['company_id'], static fn (PDO $db, array $args): array => $page($db,
            'SELECT id_Fleet AS id, Fld_Region AS region_id, Fld_Engine AS engine, Fld_Unit AS unit,
                Fld_AC_ID AS aircraft_id, msn AS serial_number, immat AS registration FROM tbl_Fleet
                WHERE Fld_Company_ID = ? AND id_Fleet > ? ORDER BY id_Fleet',
            [$args['company_id'], $args['after_id'] ?? 0], $args)),
    'add_company_aircraft' => $tool($companyWrite, $writeRoles, 'Ajoute un aéronef au parc d’une compagnie active.',
        $companyId + $properties($fleetFields), ['company_id', 'region_id', 'aircraft_id'],
        static function (PDO $db, array $args) use ($fleetFields, $lockAvailableCompany, $checkFleetReferences, $columnValues, $insert): array {
            $lockAvailableCompany($db, $args['company_id']);
            $checkFleetReferences($db, $args);
            $values = $columnValues($fleetFields, $args) + [
                'Fld_Company_ID' => $args['company_id'], 'Fld_Engine' => '', 'Fld_Unit' => '', 'msn' => '', 'immat' => '',
            ];
            return ['id' => $insert($db, 'tbl_Fleet', $values)];
        }, ['tbl_Fleet']),
    'update_company_aircraft' => $tool($companyWrite, $writeRoles, 'Modifie un aéronef appartenant à la compagnie indiquée.',
        $companyId + ['fleet_id' => $id] + $properties($fleetFields), ['company_id', 'fleet_id'],
        static function (PDO $db, array $args) use ($fleetFields, $lockAvailableCompany, $checkFleetReferences, $columnValues, $one, $update): array {
            $lockAvailableCompany($db, $args['company_id']);
            $one($db, 'SELECT id_Fleet FROM tbl_Fleet WHERE id_Fleet = ? AND Fld_Company_ID = ? FOR UPDATE',
                [$args['fleet_id'], $args['company_id']]);
            $checkFleetReferences($db, $args);
            $update($db, 'tbl_Fleet', 'id_Fleet', $args['fleet_id'], $columnValues($fleetFields, $args));
            return ['id' => $args['fleet_id']];
        }, ['tbl_Fleet']),
    'list_company_forwarders' => $tool($companyRead, $writeRoles, 'Liste les transporteurs associés à une compagnie.',
        $companyId + $pagination, ['company_id'], static fn (PDO $db, array $args): array => $page($db,
            'SELECT f.Fld_Linked_ID AS id, f.Fld_Shipper_ID AS shipper_id, s.Fld_Shipper_Text AS name
                FROM tbl_Forwarder f JOIN tbl_Shipper s ON s.Fld_Shipper_ID = f.Fld_Shipper_ID
                WHERE f.Fld_Company_ID = ? AND f.Fld_Linked_ID > ? ORDER BY f.Fld_Linked_ID',
            [$args['company_id'], $args['after_id'] ?? 0], $args)),
    'list_company_competitors' => $tool($companyRead, $writeRoles, 'Liste les concurrents associés à une compagnie.',
        $companyId + $pagination, ['company_id'], static fn (PDO $db, array $args): array => $page($db,
            'SELECT p.Fld_Linked_ID AS id, p.Fld_Competitor_ID AS competitor_id, c.Fld_Company_Name AS name
                FROM tbl_Competitor p JOIN tb_company c ON c.Fld_Company_ID = p.Fld_Competitor_ID
                WHERE p.Fld_Company_ID = ? AND p.Fld_Linked_ID > ? ORDER BY p.Fld_Linked_ID',
            [$args['company_id'], $args['after_id'] ?? 0], $args)),
    'add_company_competitor' => $tool($companyWrite, $writeRoles, 'Associe une compagnie existante comme concurrente.',
        $companyId + ['competitor_id' => $id], ['company_id', 'competitor_id'],
        static function (PDO $db, array $args) use ($lockAvailableCompany, $query, $insert): array {
            $company = $args['company_id'];
            $competitor = $args['competitor_id'];
            if ($company === $competitor) {
                throw new InvalidArgumentException('Les deux compagnies doivent être distinctes.');
            }
            $lockAvailableCompany($db, min($company, $competitor));
            $lockAvailableCompany($db, max($company, $competitor));
            $existing = $query($db, 'SELECT Fld_Linked_ID FROM tbl_Competitor
                WHERE Fld_Company_ID = ? AND Fld_Competitor_ID = ? LIMIT 1 FOR UPDATE', [$company, $competitor])->fetchColumn();
            if ($existing !== false) {
                throw new DomainException('Concurrent déjà associé.');
            }
            return ['id' => $insert($db, 'tbl_Competitor', ['Fld_Company_ID' => $company, 'Fld_Competitor_ID' => $competitor])];
        }, ['tbl_Competitor']),
    'search_contacts' => $tool($contactRead, $readRoles, 'Recherche les contacts actifs d’une compagnie par nom ou e-mail.',
        $companyId + $pagination + ['query' => $text(120)], ['company_id'],
        static function (PDO $db, array $args) use ($page, $contactColumns, $match): array {
            $sql = "SELECT $contactColumns FROM tb_company_contact t JOIN tb_company c ON c.Fld_Company_ID = t.Fld_Company_ID
                WHERE c.status = 'Available' AND t.status = 'Available' AND t.Fld_Company_ID = ? AND t.id_company_contact > ?";
            $parameters = [$args['company_id'], $args['after_id'] ?? 0];
            if (($args['query'] ?? '') !== '') {
                $sql .= " AND (t.Fld_Contact_Name LIKE ? ESCAPE '!' OR t.Fld_Contact_Email LIKE ? ESCAPE '!')";
                array_push($parameters, $match($args['query']), $match($args['query']));
            }
            return $page($db, "$sql ORDER BY t.id_company_contact", $parameters, $args);
        }),
    'get_contact' => $tool($contactRead, $readRoles, 'Consulte un contact dans sa compagnie.', $companyId + $contactId,
        ['company_id', 'contact_id'], static fn (PDO $db, array $args): array => $one($db,
            "SELECT $contactColumns FROM tb_company_contact t WHERE t.id_company_contact = ? AND t.Fld_Company_ID = ?",
            [$args['contact_id'], $args['company_id']])),
    'create_contact' => $tool($contactWrite, $writeRoles, 'Crée un contact actif dans une compagnie existante.',
        $companyId + $properties($contactFields), ['company_id', 'name'],
        static function (PDO $db, array $args, int $employeeId) use ($lockAvailableCompany, $checkContactEmail, $insert, $columnValues, $contactFields): array {
            $id = $args['company_id'];
            $lockAvailableCompany($db, $id);
            $checkContactEmail($db, $id, $args['email'] ?? '');
            $values = $columnValues($contactFields, $args) + [
                // The legacy contact table also has required fields which are
                // not part of the MCP contact form. Preserve Yoyamic's empty
                // values rather than relying on permissive implicit defaults.
                'Fld_Linked_ID' => 0, 'Fld_Company_ID' => $id, 'Company_Old_Id' => 0,
                'Fld_Contact_Fax' => '', 'Fld_Contact_Division_ID' => 0, 'Fld_Contact_Remark' => '',
                'status' => 'Available', 'aci_contact' => $employeeId,
                'entry_date' => date('Y-m-d'), 'modified_date' => date('Y-m-d H:i:s'),
                'Fld_Contact_Email' => '', 'Fld_Contact_Phone' => '', 'Fld_Contact_Phone2' => '',
                'Fld_Company_Mobile' => '', 'Fld_Contact_Title' => '',
            ];
            return ['id' => $insert($db, 'tb_company_contact', $values), 'company_id' => $id];
        }, ['tb_company_contact']),
    'update_contact' => $tool($contactWrite, $writeRoles, 'Modifie les coordonnées d’un contact actif dans sa compagnie.',
        $companyId + $contactId + $properties($contactFields), ['company_id', 'contact_id'],
        static function (PDO $db, array $args) use ($lockAvailableCompany, $one, $checkContactEmail, $update, $columnValues, $contactFields): array {
            $company = $args['company_id'];
            $id = $args['contact_id'];
            $values = $columnValues($contactFields, $args);
            if (!$values) {
                throw new InvalidArgumentException('Au moins un champ modifiable est requis.');
            }
            $lockAvailableCompany($db, $company);
            $one($db, "SELECT id_company_contact FROM tb_company_contact
                WHERE id_company_contact = ? AND Fld_Company_ID = ? AND status = 'Available' FOR UPDATE", [$id, $company]);
            if (isset($args['email'])) {
                $checkContactEmail($db, $company, $args['email'], $id);
            }
            $update($db, 'tb_company_contact', 'id_company_contact', $id, $values + ['modified_date' => date('Y-m-d H:i:s')]);
            return ['id' => $id, 'company_id' => $company];
        }, ['tb_company_contact']),
] + array_combine(['archive_contact', 'restore_contact'], array_map(
    static function (string $status) use ($tool, $archiveRoles, $contactWrite, $companyId, $contactId, $lockAvailableCompany, $one, $update): array {
        $description = $status === 'Available' ? 'Réactive un contact archivé.' : 'Archive un contact sans supprimer ses données.';
        return $tool($contactWrite, $archiveRoles, $description, $companyId + $contactId, ['company_id', 'contact_id'],
            static function (PDO $db, array $args) use ($status, $lockAvailableCompany, $one, $update): array {
                $id = $args['contact_id'];
                $lockAvailableCompany($db, $args['company_id']);
                $one($db, 'SELECT id_company_contact FROM tb_company_contact WHERE id_company_contact = ?
                    AND Fld_Company_ID = ? FOR UPDATE', [$id, $args['company_id']]);
                $update($db, 'tb_company_contact', 'id_company_contact', $id, [
                    'status' => $status, 'modified_date' => date('Y-m-d H:i:s'),
                ]);
                return ['id' => $id, 'status' => $status];
            }, ['tb_company_contact']);
    },
    ['none', 'Available']
));
