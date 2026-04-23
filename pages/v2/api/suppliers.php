<?php
/**
 * AeroCanada v2 — Supplier Quotes API
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, CSRF, Database};
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
                $where .= ' AND (sq.Fld_RFQ_ID LIKE ? OR c.Fld_Company_Name LIKE ?)';
                array_push($params, "%$search%", "%$search%");
            }

            $total = $db->fetchColumn("SELECT COUNT(*) FROM tbl_RFQ_2 sq LEFT JOIN tb_company c ON c.Fld_Company_ID = sq.Fld_Supplier_ID WHERE $where", $params);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $rows = $db->fetchAll(
                "SELECT sq.*, c.Fld_Company_Name AS supplier_name,
                        p.Fld_Part_Nbr AS part_number,
                        co.Fld_Condition_Text AS condition_text,
                        cur.Fld_Currency_Text AS currency_text
                 FROM tbl_RFQ_2 sq
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = sq.Fld_Supplier_ID
                 LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = sq.Fld_Part_ID
                 LEFT JOIN tbl_Condition co ON co.Fld_Condition_ID = sq.Fld_Condition_ID
                 LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = sq.Fld_Currency_ID
                 WHERE $where ORDER BY sq.ID DESC LIMIT $length OFFSET $start", $params
            );

            echo json_encode([
                'draw' => (int)($_GET['draw'] ?? 1),
                'recordsTotal' => (int)$total,
                'recordsFiltered' => (int)$total,
                'data' => $rows,
            ]);
            break;

        case 'create':
            CSRF::verify();
            $id = $db->insert('tbl_RFQ_2', [
                'Fld_RFQ_ID'             => $_POST['Fld_RFQ_ID'] ?? date('Y-m-d-His'),
                'Fld_Supplier_ID'        => (int)$_POST['Fld_Supplier_ID'],
                'Fld_Qty'                => $_POST['Fld_Qty'] ?? 1,
                'Fld_Condition_ID'       => $_POST['Fld_Condition_ID'] ?? '',
                'Fld_Payment_Term_ID'    => $_POST['Fld_Payment_Term_ID'] ?? '',
                'Fld_Delivery'           => $_POST['Fld_Delivery'] ?? '',
                'Fld_Price'              => $_POST['Fld_Price'] ?? 0,
                'Fld_Currency_ID'        => $_POST['Fld_Currency_ID'] ?? '',
                'Fld_Traceability_ID'    => $_POST['Fld_Traceability_ID'] ?? '',
                'Fld_Tag_Info_ID'        => $_POST['Fld_Tag_Info_ID'] ?? '',
                'Fld_Tag_Date'           => $_POST['Fld_Tag_Date'] ?? '',
                'Fld_Release_ID'         => $_POST['Fld_Release_ID'] ?? '',
                'Fld_Part_ID'            => $_POST['Fld_Part_ID'] ?? '',
                'Fld_Remark'             => $_POST['Fld_Remark'] ?? '',
                'Fld_Current_Date'       => $_POST['Fld_Current_Date'] ?? date('Y-m-d'),
                'Fld_Part_SN'            => $_POST['Fld_Part_SN'] ?? '',
                'Fld_Supplier_Contact_ID' => $_POST['Fld_Supplier_Contact_ID'] ?? '',
                'lead_time'              => $_POST['lead_time'] ?? '',
                'aci_contact'            => Auth::userId(),
            ]);
            echo json_encode(['ok' => true, 'id' => $id]);
            break;

        case 'delete':
            $id = (int)($_REQUEST['id'] ?? 0);
            $db->delete('tbl_RFQ_2', 'ID = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
