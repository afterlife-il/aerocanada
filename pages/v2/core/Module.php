<?php
/**
 * AeroCanada v2 — Module Base Class
 * Every ERP/CRM module extends this. Provides common CRUD,
 * DataTables server-side, and export functionality.
 */

namespace AeroCanada\Core;

abstract class Module
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find by primary key.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Fetch all (with optional conditions).
     */
    public function all(string $where = '1=1', array $params = [], string $orderBy = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE $where";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update a record.
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $data,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Soft-delete (archive) a record.
     */
    public function archive(int $id): int
    {
        return $this->db->update(
            $this->table,
            ['status' => 'archive'],
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Hard delete a record.
     */
    public function destroy(int $id): int
    {
        return $this->db->delete(
            $this->table,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Count records.
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE $where",
            $params
        );
    }

    /**
     * DataTables server-side handler.
     * Returns JSON-ready array for DataTables.
     */
    public function dataTable(array $request, array $columns, string $extraWhere = '1=1', array $extraParams = []): array
    {
        $draw    = (int)($request['draw'] ?? 1);
        $start   = (int)($request['start'] ?? 0);
        $length  = (int)($request['length'] ?? 25);
        $search  = $request['search']['value'] ?? '';

        // Base query
        $baseWhere  = $extraWhere;
        $baseParams = $extraParams;

        // Search filter
        if ($search !== '') {
            $searchClauses = [];
            foreach ($columns as $col) {
                if (isset($col['searchable']) && $col['searchable'] === false) continue;
                $searchClauses[] = "`{$col['db']}` LIKE ?";
                $baseParams[]    = "%$search%";
            }
            if ($searchClauses) {
                $baseWhere .= ' AND (' . implode(' OR ', $searchClauses) . ')';
            }
        }

        // Total records (unfiltered)
        $totalRecords = $this->count($extraWhere, $extraParams);

        // Filtered records
        $filteredRecords = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE $baseWhere",
            $baseParams
        );

        // Ordering
        $orderCol = $columns[0]['db'];
        $orderDir = 'ASC';
        if (isset($request['order'][0])) {
            $colIdx   = (int) $request['order'][0]['column'];
            $orderCol = $columns[$colIdx]['db'] ?? $columns[0]['db'];
            $orderDir = ($request['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        }

        // Fetch data
        $sql = "SELECT * FROM `{$this->table}` WHERE $baseWhere ORDER BY `$orderCol` $orderDir LIMIT $length OFFSET $start";
        $data = $this->db->fetchAll($sql, $baseParams);

        return [
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ];
    }
}
