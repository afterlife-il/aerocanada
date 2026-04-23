<?php
/**
 * AeroCanada ERP v2 - Dashboard Module
 * Provides aggregate statistics and recent-activity feeds.
 * Does NOT extend Module — standalone class using Database directly.
 */

namespace AeroCanada\Modules\Dashboard;

use AeroCanada\Core\Database;

class Dashboard
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all dashboard statistics in a single call.
     */
    public function getStats(): array
    {
        return [
            'totalParts'           => $this->totalParts(),
            'totalCompanies'       => $this->totalCompanies(),
            'totalStock'           => $this->totalStock(),
            'openRFQs'             => $this->openRFQs(),
            'pendingQuotes'        => $this->pendingQuotes(),
            'monthlyRFQCount'      => $this->monthlyRFQCount(),
            'topSuppliers'         => $this->topSuppliers(),
            'recentActivity'       => $this->recentActivity(),
            'stockValueByCondition' => $this->stockValueByCondition(),
            'rfqsByPriority'       => $this->rfqsByPriority(),
        ];
    }

    // ── Individual Stat Methods ─────────────────────────────────────

    /**
     * Total active parts in the catalog.
     */
    public function totalParts(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_Parts` WHERE `status` IS NULL OR `status` != 'archive'"
        );
    }

    /**
     * Total active companies.
     */
    public function totalCompanies(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tb_company` WHERE `status` IS NULL OR `status` != 'archive'"
        );
    }

    /**
     * Total stock items.
     */
    public function totalStock(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_Stock`"
        );
    }

    /**
     * Count of open/active RFQs.
     */
    public function openRFQs(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_RFQ` WHERE `Fld_RFQ_Status` = ?",
            ['open']
        );
    }

    /**
     * Count of pending customer quotations.
     */
    public function pendingQuotes(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_RFQ_3` WHERE `Fld_Quote_Status` = ?",
            ['pending']
        );
    }

    /**
     * Number of RFQs created in the current month.
     */
    public function monthlyRFQCount(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM `tbl_RFQ`
             WHERE YEAR(`Fld_RFQ_Date`) = YEAR(CURDATE())
               AND MONTH(`Fld_RFQ_Date`) = MONTH(CURDATE())"
        );
    }

    /**
     * Top suppliers by number of quotes submitted.
     */
    public function topSuppliers(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT sq.Fld_Supplier_ID, co.Fld_Company_Name,
                    COUNT(*) AS quote_count,
                    AVG(sq.Fld_SQuote_Price) AS avg_price
             FROM `tbl_RFQ_2` sq
             LEFT JOIN `tb_company` co ON co.Fld_Company_ID = sq.Fld_Supplier_ID
             GROUP BY sq.Fld_Supplier_ID, co.Fld_Company_Name
             ORDER BY quote_count DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Recent activity across RFQs, quotes, and stock.
     * Returns a unified feed of the latest events.
     */
    public function recentActivity(int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM (
                SELECT 'rfq' AS activity_type,
                       `Fld_RFQ_ID` AS ref_id,
                       `Fld_RFQ_Nbr` AS ref_label,
                       `Fld_RFQ_Date` AS activity_date
                FROM `tbl_RFQ`
                ORDER BY `Fld_RFQ_Date` DESC
                LIMIT ?
             ) AS rfq_feed
             UNION ALL
             SELECT * FROM (
                SELECT 'quote' AS activity_type,
                       `Fld_RFQ3_ID` AS ref_id,
                       `Fld_Quote_Nbr` AS ref_label,
                       `Fld_Quote_Date` AS activity_date
                FROM `tbl_RFQ_3`
                ORDER BY `Fld_Quote_Date` DESC
                LIMIT ?
             ) AS quote_feed
             UNION ALL
             SELECT * FROM (
                SELECT 'stock' AS activity_type,
                       `Fld_Stock_ID` AS ref_id,
                       `Fld_Part_SN` AS ref_label,
                       `Fld_Entry_Date` AS activity_date
                FROM `tbl_Stock`
                ORDER BY `Fld_Entry_Date` DESC
                LIMIT ?
             ) AS stock_feed
             ORDER BY activity_date DESC
             LIMIT ?",
            [$limit, $limit, $limit, $limit]
        );
    }

    /**
     * Stock value grouped by condition.
     */
    public function stockValueByCondition(): array
    {
        return $this->db->fetchAll(
            "SELECT c.Fld_Condition AS condition_name,
                    COUNT(s.Fld_Stock_ID) AS item_count,
                    COALESCE(SUM(s.Fld_Part_Price * s.Fld_Qty), 0) AS total_value
             FROM `tbl_Stock` s
             LEFT JOIN `tbl_Condition` c ON c.Fld_Condition_ID = s.Fld_Condition_ID
             GROUP BY s.Fld_Condition_ID, c.Fld_Condition
             ORDER BY total_value DESC"
        );
    }

    /**
     * RFQ count grouped by priority.
     */
    public function rfqsByPriority(): array
    {
        return $this->db->fetchAll(
            "SELECT p.Fld_RFQ_Priority AS priority_name,
                    COUNT(r.Fld_RFQ_ID) AS rfq_count
             FROM `tbl_RFQ` r
             LEFT JOIN `tbl_RFQ_Priority` p ON p.Fld_RFQ_Priority_ID = r.Fld_RFQ_Priority_ID
             GROUP BY r.Fld_RFQ_Priority_ID, p.Fld_RFQ_Priority
             ORDER BY rfq_count DESC"
        );
    }
}
