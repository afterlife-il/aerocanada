<?php
/**
 * AeroCanada ERP v2 - Suppliers Module
 * Manages supplier quotes (tbl_RFQ_2) and shippers (tbl_Shipper).
 */

namespace AeroCanada\Modules\Suppliers;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Suppliers extends Module
{
    protected string $table      = 'tbl_RFQ_2';
    protected string $primaryKey = 'Fld_RFQ2_ID';

    // ── Supplier Quotes ─────────────────────────────────────────────

    /**
     * Add a supplier quote.
     */
    public function addSupplierQuote(array $data): int
    {
        $allowed = [
            'Fld_RFQ1_ID', 'Fld_RFQ_ID', 'Fld_Supplier_ID',
            'Fld_SQuote_Date', 'Fld_SQuote_Nbr', 'Fld_SQuote_Price',
            'Fld_SQuote_Currency_ID', 'Fld_SQuote_Condition_ID',
            'Fld_SQuote_Lead_Time', 'Fld_SQuote_Remark',
            'Fld_SQuote_Status', 'Fld_SQuote_Validity',
            'aci_contact_entry',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_SQuote_Date'])) {
            $filtered['Fld_SQuote_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filtered);
    }

    /**
     * Update a supplier quote.
     */
    public function updateSupplierQuote(int $id, array $data): int
    {
        $allowed = [
            'Fld_Supplier_ID', 'Fld_SQuote_Date', 'Fld_SQuote_Nbr',
            'Fld_SQuote_Price', 'Fld_SQuote_Currency_ID',
            'Fld_SQuote_Condition_ID', 'Fld_SQuote_Lead_Time',
            'Fld_SQuote_Remark', 'Fld_SQuote_Status', 'Fld_SQuote_Validity',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

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
     * Delete a supplier quote.
     */
    public function deleteSupplierQuote(int $id): int
    {
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    // ── Shippers ────────────────────────────────────────────────────

    /**
     * Get all shippers.
     */
    public function getShippers(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Shipper` ORDER BY `Fld_Shipper_Name` ASC"
        );
    }

    /**
     * Add a shipper.
     */
    public function addShipper(array $data): int
    {
        $allowed = [
            'Fld_Shipper_Name', 'Fld_Shipper_Web', 'Fld_Shipper_Phone',
            'Fld_Shipper_Account', 'Fld_Shipper_Remark',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        return $this->db->insert('tbl_Shipper', $filtered);
    }

    /**
     * Update a shipper.
     */
    public function updateShipper(int $id, array $data): int
    {
        $allowed = [
            'Fld_Shipper_Name', 'Fld_Shipper_Web', 'Fld_Shipper_Phone',
            'Fld_Shipper_Account', 'Fld_Shipper_Remark',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            'tbl_Shipper',
            $filtered,
            '`Fld_Shipper_ID` = ?',
            [$id]
        );
    }

    /**
     * Delete a shipper.
     */
    public function deleteShipper(int $id): int
    {
        return $this->db->delete('tbl_Shipper', '`Fld_Shipper_ID` = ?', [$id]);
    }

    // ── DataTables ──────────────────────────────────────────────────

    /**
     * DataTables server-side handler for supplier quotes listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_RFQ2_ID'],
            ['db' => 'Fld_RFQ_ID'],
            ['db' => 'Fld_Supplier_ID'],
            ['db' => 'Fld_SQuote_Nbr'],
            ['db' => 'Fld_SQuote_Date'],
            ['db' => 'Fld_SQuote_Price'],
            ['db' => 'Fld_SQuote_Status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
