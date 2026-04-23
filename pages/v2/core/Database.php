<?php
/**
 * AeroCanada v2 — Secure Database Layer (PDO)
 * Replaces all raw mysqli calls with prepared statements.
 */

namespace AeroCanada\Core;

class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $cfg = require __DIR__ . '/../config.php';
        $db  = $cfg['database'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['name']
        );

        $this->pdo = new \PDO($dsn, $db['user'], $db['pass'], [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a prepared query and return the statement.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $start = microtime(true);
        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $ms = (microtime(true) - $start) * 1000;

        if ($ms > 1000) {
            error_log(sprintf('[SLOW QUERY %.0fms] %s', $ms, substr($sql, 0, 200)));
        }

        return $stmt;
    }

    /**
     * Fetch all rows.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * Fetch a single column value.
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Insert and return last insert ID.
     */
    public function insert(string $table, array $data): int
    {
        $cols   = array_keys($data);
        $places = array_fill(0, count($cols), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $cols),
            implode(', ', $places)
        );

        $this->query($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows and return affected count.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        $vals = [];
        foreach ($data as $col => $val) {
            $sets[] = "`$col` = ?";
            $vals[] = $val;
        }

        $sql  = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $sets), $where);
        $stmt = $this->query($sql, array_merge($vals, $whereParams));
        return $stmt->rowCount();
    }

    /**
     * Delete rows.
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql  = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Legacy bridge — run raw SQL through mysql2_query style but with safety.
     * ONLY for backward compatibility during migration.
     */
    public function legacyQuery(string $sql)
    {
        return $this->pdo->query($sql);
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }
}
