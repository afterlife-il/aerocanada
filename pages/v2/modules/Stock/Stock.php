<?php
/**
 * AeroCanada ERP v2 - Stock Module
 * Manages stock inventory: add, update, search by part or location, DataTables.
 */

namespace AeroCanada\Modules\Stock;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Stock extends Module
{
    protected string $table      = 'tbl_Stock';
    protected string $primaryKey = 'Fld_Stock_ID';

    /**
     * Add a new stock entry.
     */
    public function addStock(array $data): int
    {
        $allowed = [
            'Fld_Part_ID', 'Fld_Part_SN', 'Fld_Supplier_ID', 'Fld_Entry_Date',
            'Fld_Part_Price', 'Fld_Price_Currency_ID', 'Fld_BAX_PO_Nbr',
            'Fld_Qty', 'Fld_Condition_ID', 'Fld_Release_ID', 'Fld_Tag_Info_ID',
            'Fld_Tag_Date', 'Fld_Traceability_ID', 'Fld_Owner_ID',
            'Fld_Stock_Location_ID', 'Fld_Status_ID', 'Fld_Shelf_Life_Limit',
            'Fld_Remark', 'Fld_Stock_Date',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_Entry_Date'])) {
            $filtered['Fld_Entry_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filtered);
    }

    /**
     * Update a stock record.
     */
    public function updateStock(int $id, array $data): int
    {
        $allowed = [
            'Fld_Part_ID', 'Fld_Part_SN', 'Fld_Supplier_ID',
            'Fld_Part_Price', 'Fld_Price_Currency_ID', 'Fld_BAX_PO_Nbr',
            'Fld_Qty', 'Fld_Condition_ID', 'Fld_Release_ID', 'Fld_Tag_Info_ID',
            'Fld_Tag_Date', 'Fld_Traceability_ID', 'Fld_Owner_ID',
            'Fld_Stock_Location_ID', 'Fld_Status_ID', 'Fld_Shelf_Life_Limit',
            'Fld_Remark',
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
     * Get all stock entries for a given part.
     */
    public function getStockByPart(int $partId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    c.Fld_Condition AS condition_name,
                    loc.Fld_Location AS location_name
             FROM `{$this->table}` s
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = s.Fld_Part_ID
             LEFT JOIN `tbl_Condition` c ON c.Fld_Condition_ID = s.Fld_Condition_ID
             LEFT JOIN `tbl_Stock_Location` loc ON loc.Fld_Stock_Location_ID = s.Fld_Stock_Location_ID
             WHERE s.`Fld_Part_ID` = ?
             ORDER BY s.`Fld_Entry_Date` DESC",
            [$partId]
        );
    }

    /**
     * Get all stock at a given location.
     */
    public function getStockByLocation(int $locationId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    c.Fld_Condition AS condition_name
             FROM `{$this->table}` s
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = s.Fld_Part_ID
             LEFT JOIN `tbl_Condition` c ON c.Fld_Condition_ID = s.Fld_Condition_ID
             WHERE s.`Fld_Stock_Location_ID` = ?
             ORDER BY p.Fld_Part_Nbr ASC",
            [$locationId]
        );
    }

    /**
     * DataTables server-side handler for stock listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_Stock_ID'],
            ['db' => 'Fld_Part_ID'],
            ['db' => 'Fld_Part_SN'],
            ['db' => 'Fld_Supplier_ID'],
            ['db' => 'Fld_Entry_Date'],
            ['db' => 'Fld_Part_Price'],
            ['db' => 'Fld_Qty'],
            ['db' => 'Fld_Condition_ID'],
            ['db' => 'Fld_Stock_Location_ID'],
            ['db' => 'Fld_Status_ID'],
        ];

        return $this->dataTable($request, $columns);
    }
}
