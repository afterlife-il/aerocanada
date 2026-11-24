<?php
declare(strict_types=1);

/** Private CLI gateway. MCP owns verification of the Auth0 access token. */
final class ErpAccessDenied extends RuntimeException {}

function erp_operations(): array
{
    $operations = [];
    foreach (['companies', 'inventory', 'workflows', 'documents', 'operations', 'mail'] as $module) {
        $path = __DIR__ . '/erp_' . $module . '.php';
        if (!is_file($path)) {
            continue;
        }
        $entries = require $path;
        foreach ($entries as $name => $operation) {
            if (isset($operations[$name]) || !preg_match('/^[a-z][a-z0-9_]{1,63}$/D', $name)
                || !preg_match('/^aerocanada\.[a-z_]+\.(read|write|create|update|send)$/D', $operation['scope'])
                || !is_callable($operation['handler']) || empty($operation['roles'])) {
                throw new LogicException('Invalid ERP operation descriptor');
            }
            // Legacy module descriptors use .write internally. Publish a least-privilege
            // CREATE or UPDATE scope based on the business action before MCP discovery.
            if (str_ends_with($operation['scope'], '.write')) {
                if (preg_match('/^(create|add|import)_/', $name)) {
                    $permission = 'create';
                } elseif (preg_match('/^(update|archive|restore|mark|validate)_/', $name)) {
                    $permission = 'update';
                } else {
                    throw new LogicException('Mutation has no explicit CREATE or UPDATE class: ' . $name);
                }
                $operation['scope'] = substr($operation['scope'], 0, -5) . $permission;
            }
            if (!preg_match('/^aerocanada\.[a-z_]+\.(read|create|update|send)$/D', $operation['scope'])) {
                throw new LogicException('Unsupported published ERP permission');
            }
            $operations[$name] = $operation;
        }
    }
    return $operations;
}

function erp_manifest(array $operations): array
{
    $manifest = [];
    foreach ($operations as $name => $operation) {
        $schema = $operation['inputSchema'] ?? $operation['schema'] ?? null;
        if (!is_array($schema) || ($schema['type'] ?? null) !== 'object') {
            throw new LogicException('Missing ERP input schema: ' . $name);
        }
        if (($schema['properties'] ?? null) === []) {
            $schema['properties'] = new stdClass();
        }
        $write = !str_ends_with($operation['scope'], '.read');
        if ($write && empty($operation['writeTables'])) {
            throw new LogicException('Missing ERP write tables: ' . $name);
        }
        $extraScopes = $operation['additionalScopes'] ?? [];
        foreach ($extraScopes as $scope) {
            if (!is_string($scope) || !preg_match('/^aerocanada\.[a-z_]+\.read$/D', $scope)) {
                throw new LogicException('Invalid additional ERP scope: ' . $name);
            }
        }
        $manifest[$name] = [
            'scope' => $operation['scope'],
            'additionalScopes' => $extraScopes,
            'description' => $operation['description'] ?? str_replace('_', ' ', $name),
            'inputSchema' => $schema,
        ];
    }
    return $manifest;
}

function erp_private_file(string $path, int $bytes): string
{
    if ($path === '' || $path[0] !== '/' || is_link($path)) {
        throw new RuntimeException('Invalid private ERP path');
    }
    $file = fopen($path, 'rb');
    if ($file === false) {
        throw new RuntimeException('Private ERP file unavailable');
    }
    try {
        $stat = fstat($file);
        $owner = function_exists('posix_geteuid') ? posix_geteuid() : -1;
        if (($stat['mode'] & 0170000) !== 0100000 || ($stat['mode'] & 0077) !== 0
            || !in_array($stat['uid'], [0, $owner], true)
            || $stat['size'] > $bytes) {
            throw new RuntimeException('Unexpected private ERP file owner, type or mode');
        }
        $value = stream_get_contents($file, $bytes + 1);
        if (!is_string($value) || strlen($value) > $bytes) {
            throw new RuntimeException('Invalid private ERP file contents');
        }
        return $value;
    } finally {
        fclose($file);
    }
}

function erp_actor_binding(array $config, array $request): array
{
    $issuer = $request['issuer'] ?? null;
    $subject = $request['subject'] ?? null;
    $action = $request['operation'] ?? null;
    if (!is_string($issuer) || $issuer !== ($config['issuer'] ?? null)
        || !is_string($subject) || $subject === '' || strlen($subject) > 255 || str_ends_with($subject, '@clients')
        || !is_string($action)) {
        throw new ErpAccessDenied();
    }
    $binding = $config['subjects'][$subject] ?? null;
    if (!is_array($binding) || ($binding['enabled'] ?? false) !== true
        || !is_int($binding['employeeId'] ?? null) || $binding['employeeId'] < 1
        || !filter_var($binding['employeeEmail'] ?? null, FILTER_VALIDATE_EMAIL)
        || !in_array($action, $binding['operations'] ?? [], true)) {
        throw new ErpAccessDenied();
    }
    return $binding;
}

function erp_db(array $config): PDO
{
    $socket = $config['socket'] ?? '';
    $db = $config['database'] ?? '';
    $user = $config['user'] ?? '';
    if (!is_string($socket) || !preg_match('~^/[a-zA-Z0-9/._-]+$~D', $socket)
        || !is_string($db) || !preg_match('/^[a-zA-Z0-9_]+$/D', $db)
        || !is_string($user) || $user === '' || strtolower($user) === 'root') {
        throw new RuntimeException('Invalid ERP database configuration');
    }
    $password = rtrim(erp_private_file($config['passwordFile'] ?? '', 4096), "\r\n");
    if ($password === '') {
        throw new RuntimeException('Empty ERP database credential');
    }
    return new PDO("mysql:unix_socket=$socket;dbname=$db;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ]);
}

function erp_lock_and_check_write_tables(PDO $db, array $tables): void
{
    // Lock table metadata for this transaction before checking the engine.
    // All identifiers are declared in packaged PHP operation descriptors.
    $tables = array_unique(array_merge($tables, ['mcp_erp_audit']));
    sort($tables, SORT_STRING);
    foreach ($tables as $table) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $table)) {
            throw new LogicException('Invalid ERP table descriptor');
        }
        $db->query("SELECT 1 FROM `$table` LIMIT 0")->closeCursor();
        $find = $db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND BINARY TABLE_NAME = ?');
        $find->execute([$table]);
        if ($find->fetchColumn() !== 'InnoDB') {
            throw new RuntimeException('ERP mutation requires transactional tables');
        }
    }
}

function erp_normalize_args($value)
{
    if (!is_array($value)) {
        return $value;
    }
    if (!array_is_list($value)) {
        ksort($value, SORT_STRING);
    }
    return array_map('erp_normalize_args', $value);
}

function erp_execute(PDO $db, array $binding, array $request, array $operation): array
{
    $write = !str_ends_with($operation['scope'], '.read');
    $read = str_ends_with($operation['scope'], '.read');
    $args = $request['args'] ?? null;
    if ((!$read && !$write) || !is_array($args) || ($args && array_is_list($args))) {
        throw new InvalidArgumentException('Arguments objet requis');
    }
    $requestId = $request['request_id'] ?? null;
    if ($write && (!is_string($requestId) || !preg_match('/^[a-zA-Z0-9_-]{16,80}$/D', $requestId))) {
        throw new InvalidArgumentException('Identifiant de requête invalide');
    }
    if ($read) {
        $db->exec('SET TRANSACTION READ ONLY');
    }
    $db->beginTransaction();
    try {
        $identity = $db->prepare('SELECT email, statut FROM tbl_Employee WHERE Employee_ID = ?' . ($write ? ' FOR UPDATE' : ''));
        $identity->execute([$binding['employeeId']]);
        $employee = $identity->fetch();
        $identity->closeCursor();
        if (!$employee || strcasecmp((string) $employee['email'], $binding['employeeEmail']) !== 0
            || !in_array($employee['statut'], $operation['roles'], true)) {
            throw new ErpAccessDenied();
        }

        if ($write) {
            erp_lock_and_check_write_tables($db, $operation['writeTables']);
            $actorHash = hash('sha256', $request['issuer'] . "\n" . $request['subject']);
            $inputHash = hash('sha256', json_encode(erp_normalize_args($args), JSON_THROW_ON_ERROR));
            $find = $db->prepare('SELECT input_hash, result_json FROM mcp_erp_audit WHERE actor_hash = ? AND employee_id = ? AND operation = ? AND request_id = ? FOR UPDATE');
            $key = [$actorHash, $binding['employeeId'], $request['operation'], $requestId];
            $find->execute($key);
            $previous = $find->fetch();
            $find->closeCursor();
            if ($previous) {
                if (!hash_equals($previous['input_hash'], $inputHash)) {
                    throw new DomainException('Identifiant de requête déjà utilisé avec d’autres paramètres');
                }
                $db->commit();
                $priorResult = json_decode($previous['result_json'], true, 512, JSON_THROW_ON_ERROR);
                if (($operation['externalEffect'] ?? null) && ($priorResult['pending_external'] ?? false)) {
                    $messageId = hash('sha256', json_encode($key, JSON_THROW_ON_ERROR));
                    $result = $operation['reconcile']($args, $binding, $messageId, $priorResult);
                    return erp_finish_external_effect($db, $key, $result);
                }
                return $priorResult;
            }
        }

        if ($write && ($operation['externalEffect'] ?? null)) {
            $messageId = hash('sha256', json_encode($key, JSON_THROW_ON_ERROR));
            $prepared = $operation['handler']($db, $args, $binding, $messageId);
            $pending = ['pending_external' => true] + ($prepared['reconciliation'] ?? []);
            $claim = $db->prepare('INSERT INTO mcp_erp_audit
                (actor_hash, employee_id, operation, request_id, input_hash, result_json, delivery_key)
                VALUES (?, ?, ?, ?, ?, ?, ?)');
            try {
                $claim->execute(array_merge($key, [$inputHash, json_encode($pending, JSON_THROW_ON_ERROR),
                    $prepared['delivery_key'] ?? null]));
            } catch (PDOException $error) {
                if (isset($prepared['delivery_key']) && $error->getCode() === '23000') {
                    throw new DomainException('L’envoi de ce brouillon a déjà été réclamé');
                }
                throw $error;
            }
            $db->commit();
            // A durable claim makes retries reconcile without repeating the external POST.
            return erp_finish_external_effect($db, $key, $prepared['execute']());
        }

        $result = ($operation['mailboxBinding'] ?? false) === true
            ? $operation['handler']($db, $args, $binding['employeeId'], $binding)
            : $operation['handler']($db, $args, $binding['employeeId']);
        if ($write) {
            $encoded = json_encode($result, JSON_THROW_ON_ERROR);
            if (strlen($encoded) > 196608) {
                throw new RuntimeException('ERP write response too large');
            }
            $insert = $db->prepare('INSERT INTO mcp_erp_audit (actor_hash, employee_id, operation, request_id, input_hash, result_json) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute(array_merge($key, [$inputHash, $encoded]));
            $db->commit();
        } else {
            $db->rollBack();
        }
        return $result;
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

function erp_finish_external_effect(PDO $db, array $key, array $result): array
{
    $statement = $db->prepare('UPDATE mcp_erp_audit SET result_json = ? WHERE actor_hash = ? AND employee_id = ? AND operation = ? AND request_id = ?');
    $statement->execute(array_merge([json_encode($result, JSON_THROW_ON_ERROR)], $key));
    return $result;
}

function erp_cli(array $argv): void
{
    try {
        $operations = erp_operations();
        if (($argv[1] ?? null) === '--manifest') {
            echo json_encode(erp_manifest($operations), JSON_THROW_ON_ERROR);
            return;
        }
        $raw = stream_get_contents(STDIN, 65537);
        if (strlen($raw) > 65536) {
            throw new InvalidArgumentException('Requête trop volumineuse');
        }
        $request = json_decode($raw, true, 48, JSON_THROW_ON_ERROR);
        if (!is_array($request) || !is_string($request['operation'] ?? null)) {
            throw new InvalidArgumentException('Requête ERP invalide');
        }
        $config = json_decode(erp_private_file(getenv('AEROCANADA_ERP_CONFIG') ?: '', 524288), true, 32, JSON_THROW_ON_ERROR);
        $binding = erp_actor_binding($config, $request);
        $operation = $operations[$request['operation']] ?? null;
        if (!$operation || (str_ends_with($operation['scope'], '.send') && ($config['enableSend'] ?? false) !== true)) {
            throw new ErpAccessDenied();
        }
        $pages = $config['yoyamicPages'] ?? '';
        if ($pages) {
            putenv('AEROCANADA_YOYAMIC_PAGES=' . $pages);
        }
        $result = erp_execute(erp_db($config), $binding, $request, $operation);
        echo json_encode(['ok' => true, 'result' => $result], JSON_THROW_ON_ERROR);
    } catch (ErpAccessDenied $error) {
        echo '{"ok":false,"error":"access_denied","message":"Accès Yoyamic refusé"}';
    } catch (InvalidArgumentException | OutOfBoundsException | DomainException $error) {
        $code = $error instanceof OutOfBoundsException ? 'not_found'
            : ($error instanceof DomainException ? 'conflict' : 'invalid_input');
        echo json_encode(['ok' => false, 'error' => $code, 'message' => $error->getMessage()], JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Throwable $error) {
        echo '{"ok":false,"error":"unavailable","message":"Opération Yoyamic indisponible"}';
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    erp_cli($argv);
}
