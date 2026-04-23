<?php
/**
 * AeroCanada ERP v2 - RFQ Module
 * Manages Requests for Quotation: master RFQ, line items, quotes,
 * duplication, lookups, and DataTables.
 */

namespace AeroCanada\Modules\RFQ;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class RFQ extends Module
{
    protected string $table      = 'tbl_RFQ';
    protected string $primaryKey = 'Fld_RFQ_ID';

    // ── RFQ Master ──────────────────────────────────────────────────

    /**
     * Create a new RFQ.
     */
    public function createRFQ(array $data): int
    {
        $allowed = [
            'Fld_RFQ_Nbr', 'Fld_RFQ_Date', 'Fld_Company_ID', 'Fld_Contact_ID',
            'Fld_RFQ_Type_ID', 'Fld_RFQ_Priority_ID', 'Fld_Term_ID',
            'Fld_Currency_ID', 'Fld_RFQ_Remark', 'Fld_RFQ_Status',
            'aci_contact_entry',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_RFQ_Date'])) {
            $filtered['Fld_RFQ_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filtered);
    }

    // ── Line Items (tbl_RFQ_1) ──────────────────────────────────────

    /**
     * Add a line item to an RFQ.
     */
    public function addLineItem(int $rfqId, array $data): int
    {
        $allowed = [
            'Fld_Part_ID', 'Fld_Qty', 'Fld_Condition_ID',
            'Fld_RFQ1_Remark', 'Fld_Unit_Price', 'Fld_Extended_Price',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_RFQ_ID'] = $rfqId;

        return $this->db->insert('tbl_RFQ_1', $filtered);
    }

    /**
     * Update an RFQ line item.
     */
    public function updateLineItem(int $id, array $data): int
    {
        $allowed = [
            'Fld_Part_ID', 'Fld_Qty', 'Fld_Condition_ID',
            'Fld_RFQ1_Remark', 'Fld_Unit_Price', 'Fld_Extended_Price',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            'tbl_RFQ_1',
            $filtered,
            '`Fld_RFQ1_ID` = ?',
            [$id]
        );
    }

    /**
     * Delete an RFQ line item.
     */
    public function deleteLineItem(int $id): int
    {
        return $this->db->delete('tbl_RFQ_1', '`Fld_RFQ1_ID` = ?', [$id]);
    }

    // ── Quotes (tbl_RFQ_3) ──────────────────────────────────────────

    /**
     * Add a customer quote.
     */
    public function addQuote(array $data): int
    {
        $allowed = [
            'Fld_RFQ1_ID', 'Fld_RFQ_ID', 'Fld_Quote_Date', 'Fld_Quote_Nbr',
            'Fld_Quote_Price', 'Fld_Quote_Currency_ID', 'Fld_Quote_Condition_ID',
            'Fld_Quote_Lead_Time', 'Fld_Quote_Remark', 'Fld_Quote_Status',
            'Fld_Quote_Validity', 'aci_contact_entry',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_Quote_Date'])) {
            $filtered['Fld_Quote_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert('tbl_RFQ_3', $filtered);
    }

    /**
     * Update a customer quote.
     */
    public function updateQuote(int $id, array $data): int
    {
        $allowed = [
            'Fld_Quote_Date', 'Fld_Quote_Nbr', 'Fld_Quote_Price',
            'Fld_Quote_Currency_ID', 'Fld_Quote_Condition_ID',
            'Fld_Quote_Lead_Time', 'Fld_Quote_Remark', 'Fld_Quote_Status',
            'Fld_Quote_Validity',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            'tbl_RFQ_3',
            $filtered,
            '`Fld_RFQ3_ID` = ?',
            [$id]
        );
    }

    /**
     * Delete a customer quote.
     */
    public function deleteQuote(int $id): int
    {
        return $this->db->delete('tbl_RFQ_3', '`Fld_RFQ3_ID` = ?', [$id]);
    }

    // ── Composite Queries ───────────────────────────────────────────

    /**
     * Get an RFQ with all its line items and quotes.
     */
    public function getRFQWithItems(int $rfqId): ?array
    {
        $rfq = $this->find($rfqId);
        if (!$rfq) {
            return null;
        }

        $rfq['line_items'] = $this->db->fetchAll(
            "SELECT li.*, p.Fld_Part_Nbr, p.Fld_Part_Desc, c.Fld_Condition AS condition_name
             FROM `tbl_RFQ_1` li
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = li.Fld_Part_ID
             LEFT JOIN `tbl_Condition` c ON c.Fld_Condition_ID = li.Fld_Condition_ID
             WHERE li.`Fld_RFQ_ID` = ?
             ORDER BY li.`Fld_RFQ1_ID` ASC",
            [$rfqId]
        );

        $rfq['quotes'] = $this->db->fetchAll(
            "SELECT q.*, li.Fld_Part_ID, p.Fld_Part_Nbr
             FROM `tbl_RFQ_3` q
             LEFT JOIN `tbl_RFQ_1` li ON li.Fld_RFQ1_ID = q.Fld_RFQ1_ID
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = li.Fld_Part_ID
             WHERE q.`Fld_RFQ_ID` = ?
             ORDER BY q.`Fld_Quote_Date` DESC",
            [$rfqId]
        );

        return $rfq;
    }

    /**
     * Duplicate an RFQ and all its line items.
     */
    public function duplicateRFQ(int $rfqId): int
    {
        $original = $this->find($rfqId);
        if (!$original) {
            throw new \RuntimeException('RFQ not found.');
        }

        // Remove PK and reset dates
        unset($original[$this->primaryKey]);
        $original['Fld_RFQ_Date']   = date('Y-m-d H:i:s');
        $original['Fld_RFQ_Status'] = 'open';
        $original['Fld_RFQ_Nbr']    = $original['Fld_RFQ_Nbr'] . '-COPY';

        $this->db->beginTransaction();
        try {
            $newRfqId = $this->db->insert($this->table, $original);

            $lineItems = $this->db->fetchAll(
                "SELECT * FROM `tbl_RFQ_1` WHERE `Fld_RFQ_ID` = ?",
                [$rfqId]
            );

            foreach ($lineItems as $item) {
                unset($item['Fld_RFQ1_ID']);
                $item['Fld_RFQ_ID'] = $newRfqId;
                $this->db->insert('tbl_RFQ_1', $item);
            }

            $this->db->commit();
            return $newRfqId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Lookups ─────────────────────────────────────────────────────

    /**
     * Get RFQ types.
     */
    public function getTypes(): array
    {
        return $this->db->fetchAll("SELECT * FROM `tbl_RFQ_Type` ORDER BY `Fld_RFQ_Type` ASC");
    }

    /**
     * Get RFQ priorities.
     */
    public function getPriorities(): array
    {
        return $this->db->fetchAll("SELECT * FROM `tbl_RFQ_Priority` ORDER BY `Fld_RFQ_Priority` ASC");
    }

    /**
     * Get payment terms.
     */
    public function getTerms(): array
    {
        return $this->db->fetchAll("SELECT * FROM `tbl_Term` ORDER BY `Fld_Term` ASC");
    }

    /**
     * Get part conditions.
     */
    public function getConditions(): array
    {
        return $this->db->fetchAll("SELECT * FROM `tbl_Condition` ORDER BY `Fld_Condition` ASC");
    }

    // ── DataTables ──────────────────────────────────────────────────

    /**
     * DataTables server-side handler for RFQ listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_RFQ_ID'],
            ['db' => 'Fld_RFQ_Nbr'],
            ['db' => 'Fld_RFQ_Date'],
            ['db' => 'Fld_Company_ID'],
            ['db' => 'Fld_RFQ_Type_ID'],
            ['db' => 'Fld_RFQ_Priority_ID'],
            ['db' => 'Fld_RFQ_Status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
