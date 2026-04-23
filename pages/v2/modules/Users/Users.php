<?php
/**
 * AeroCanada ERP v2 - Users Module
 * Manages employee/user accounts: CRUD, password hashing, profiles, DataTables.
 */

namespace AeroCanada\Modules\Users;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Users extends Module
{
    protected string $table      = 'tbl_Employee';
    protected string $primaryKey = 'Employee_ID';

    /**
     * Get all active users.
     */
    public function getAllUsers(): array
    {
        return $this->db->fetchAll(
            "SELECT `Employee_ID`, `Employee_First`, `Employee_Last`,
                    `Employee_Email`, `Employee_Role`, `Employee_Status`,
                    `Employee_Created`
             FROM `{$this->table}`
             WHERE `Employee_Status` IS NULL OR `Employee_Status` != 'archive'
             ORDER BY `Employee_Last` ASC"
        );
    }

    /**
     * Get a single user by ID (excludes password hash).
     */
    public function getUser(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT `Employee_ID`, `Employee_First`, `Employee_Last`,
                    `Employee_Email`, `Employee_Phone`, `Employee_Role`,
                    `Employee_Status`, `Employee_Created`, `Employee_Last_Login`
             FROM `{$this->table}`
             WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Create a new user with a hashed password.
     */
    public function createUser(array $data): int
    {
        $allowed = [
            'Employee_First', 'Employee_Last', 'Employee_Email',
            'Employee_Phone', 'Employee_Role', 'Employee_Status',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        // Hash the password
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required.');
        }
        $filtered['Employee_Password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $filtered['Employee_Created'] = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, $filtered);
    }

    /**
     * Update user details. If password is provided, it will be re-hashed.
     */
    public function updateUser(int $id, array $data): int
    {
        $allowed = [
            'Employee_First', 'Employee_Last', 'Employee_Email',
            'Employee_Phone', 'Employee_Role', 'Employee_Status',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        // Only hash and include password if explicitly provided
        if (!empty($data['password'])) {
            $filtered['Employee_Password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            $this->table,
            $filtered,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Hard-delete a user.
     */
    public function deleteUser(int $id): int
    {
        return $this->db->delete(
            $this->table,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Get full user profile including related data.
     */
    public function getUserProfile(int $id): ?array
    {
        $user = $this->getUser($id);
        if (!$user) {
            return null;
        }

        // Count RFQs created by this user
        $user['rfq_count'] = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_RFQ` WHERE `aci_contact_entry` = ?",
            [$id]
        );

        // Count quotes created by this user
        $user['quote_count'] = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_RFQ_3` WHERE `aci_contact_entry` = ?",
            [$id]
        );

        // Count parts entered by this user
        $user['parts_count'] = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_Parts` WHERE `aci_contact_entry` = ?",
            [$id]
        );

        return $user;
    }

    /**
     * DataTables server-side handler for users listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Employee_ID'],
            ['db' => 'Employee_First'],
            ['db' => 'Employee_Last'],
            ['db' => 'Employee_Email'],
            ['db' => 'Employee_Role'],
            ['db' => 'Employee_Status'],
            ['db' => 'Employee_Created'],
        ];

        return $this->dataTable($request, $columns);
    }
}
