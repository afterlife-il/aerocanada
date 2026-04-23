<?php
/**
 * AeroCanada v2 — Dashboard API
 */

require_once __DIR__ . '/../bootstrap.php';

use AeroCanada\Core\{Auth, Database};

Auth::requireAuth();

$db = Database::getInstance();
header('Content-Type: application/json; charset=utf-8');

try {
    $stats = [];

    // Total parts
    $stats['totalParts'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM tbl_Parts WHERE status = 'Available'");

    // Total companies
    $stats['totalCompanies'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM tb_company WHERE status = 'Available'");

    // Total stock items
    $stats['totalStock'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM tbl_Stock");

    // Open RFQs (this month)
    $monthStart = date('Y-m-01');
    $stats['openRFQs'] = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM tbl_RFQ_1 WHERE date >= ?",
        [$monthStart]
    );

    // Pending quotes
    $stats['pendingQuotes'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM tbl_RFQ_3");

    // Monthly RFQ count (last 12 months)
    $stats['monthlyRFQ'] = $db->fetchAll(
        "SELECT DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS count
         FROM tbl_RFQ_1
         WHERE date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY DATE_FORMAT(date, '%Y-%m')
         ORDER BY month"
    );

    // Top suppliers (by quote count)
    $stats['topSuppliers'] = $db->fetchAll(
        "SELECT c.Fld_Company_Name, COUNT(*) AS quote_count
         FROM tbl_RFQ_2 sq
         JOIN tb_company c ON c.Fld_Company_ID = sq.Fld_Supplier_ID
         GROUP BY sq.Fld_Supplier_ID
         ORDER BY quote_count DESC
         LIMIT 10"
    );

    // Recent activity (last RFQs)
    $stats['recentActivity'] = $db->fetchAll(
        "SELECT r.Fld_RFQ_ID, r.date, r.pn_rfq, r.description_rfq,
                c.Fld_Company_Name AS customer_name,
                e.Employee_Name AS employee_name
         FROM tbl_RFQ_1 r
         LEFT JOIN tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
         LEFT JOIN tbl_Employee e ON e.Employee_ID = r.Employee_ID
         ORDER BY r.ID DESC
         LIMIT 15"
    );

    // RFQs by priority
    $stats['rfqByPriority'] = $db->fetchAll(
        "SELECT p.Fld_Priority_Text AS priority, COUNT(*) AS count
         FROM tbl_RFQ_1 r
         LEFT JOIN tbl_Priority p ON p.Fld_Priority_ID = r.Fld_Priority_ID
         WHERE r.date >= ?
         GROUP BY r.Fld_Priority_ID
         ORDER BY count DESC",
        [$monthStart]
    );

    // Stock value by condition
    $stats['stockByCondition'] = $db->fetchAll(
        "SELECT co.Fld_Condition_Text AS condition_name,
                COUNT(*) AS item_count,
                SUM(s.Fld_Part_Price * s.Fld_Qty) AS total_value
         FROM tbl_Stock s
         LEFT JOIN tbl_Condition co ON co.Fld_Condition_ID = s.Fld_Condition_ID
         GROUP BY s.Fld_Condition_ID"
    );

    echo json_encode(['ok' => true, 'stats' => $stats]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
