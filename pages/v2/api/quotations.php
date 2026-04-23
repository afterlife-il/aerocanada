<?php
/**
 * AeroCanada v2 — Quotations API
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, Database};
Auth::requireAuth();
$db = Database::getInstance();
$action = $_REQUEST['action'] ?? '';
header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {
        case 'datatable':
            $where = '1=1';
            $params = [];
            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (q.Fld_RFQ_ID LIKE ? OR p.Fld_Part_Nbr LIKE ?)';
                array_push($params, "%$search%", "%$search%");
            }
            $period = $_GET['period'] ?? 'month';
            switch ($period) {
                case 'today': $where .= " AND q.Fld_Quote_Date = CURDATE()"; break;
                case 'week': $where .= " AND q.Fld_Quote_Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
                case 'month': $where .= " AND q.Fld_Quote_Date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"; break;
            }

            $total = $db->fetchColumn("SELECT COUNT(*) FROM tbl_RFQ_3 q LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = q.Fld_Part_Id WHERE $where", $params);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $rows = $db->fetchAll(
                "SELECT q.*, p.Fld_Part_Nbr AS part_number,
                        c.Fld_Company_Name AS customer_name,
                        co.Fld_Condition_Text AS condition_text,
                        cur.Fld_Currency_Text AS currency_text
                 FROM tbl_RFQ_3 q
                 LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = q.Fld_Part_Id
                 LEFT JOIN tbl_RFQ_1 r1 ON r1.ID = q.id_tbl_rfq1
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = r1.Fld_Customer_ID
                 LEFT JOIN tbl_Condition co ON co.Fld_Condition_ID = q.Fld_Condition
                 LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = q.Fld_Currency_ID
                 WHERE $where ORDER BY q.ID DESC LIMIT $length OFFSET $start", $params
            );

            echo json_encode([
                'draw' => (int)($_GET['draw'] ?? 1),
                'recordsTotal' => (int)$total,
                'recordsFiltered' => (int)$total,
                'data' => $rows,
            ]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
