<?php
/**
 * AeroCanada v2 — Stock API Endpoint
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, CSRF, Database};

Auth::requireAuth();
$action = $_REQUEST['action'] ?? '';
$db = Database::getInstance();
header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {
        case 'datatable':
            $where = '1=1';
            $params = [];
            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (p.Fld_Part_Nbr LIKE ? OR s.Fld_Part_SN LIKE ? OR p.Fld_Part_Desc LIKE ?)';
                $s = "%$search%";
                array_push($params, $s, $s, $s);
            }

            $totalAll = $db->fetchColumn('SELECT COUNT(*) FROM tbl_Stock');
            $totalFiltered = $db->fetchColumn(
                "SELECT COUNT(*) FROM tbl_Stock s
                 LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = s.Fld_Part_ID WHERE $where", $params
            );

            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $rows = $db->fetchAll(
                "SELECT s.*, p.Fld_Part_Nbr AS part_number, p.Fld_Part_Desc AS part_description,
                        sup.Fld_Company_Name AS supplier_name,
                        co.Fld_Condition_Text AS condition_text,
                        cur.Fld_Currency_Text AS currency_text
                 FROM tbl_Stock s
                 LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = s.Fld_Part_ID
                 LEFT JOIN tb_company sup ON sup.Fld_Company_ID = s.Fld_Supplier_ID
                 LEFT JOIN tbl_Condition co ON co.Fld_Condition_ID = s.Fld_Condition_ID
                 LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = s.Fld_Price_Currency_ID
                 WHERE $where ORDER BY s.Fld_Stock_ID DESC LIMIT $length OFFSET $start",
                $params
            );

            foreach ($rows as &$row) {
                $row['price_fmt'] = $row['Fld_Part_Price']
                    ? number_format((float)$row['Fld_Part_Price'], 2) . ' ' . ($row['currency_text'] ?? '')
                    : '-';
                $row['location_name'] = $row['Fld_Stock_Location_ID'] ?: '-';
            }

            echo json_encode([
                'draw' => (int)($_GET['draw'] ?? 1),
                'recordsTotal' => (int)$totalAll,
                'recordsFiltered' => (int)$totalFiltered,
                'data' => $rows,
            ]);
            break;

        case 'create':
            CSRF::verify();
            $id = $db->insert('tbl_Stock', [
                'Fld_Part_ID'        => (int)$_POST['Fld_Part_ID'],
                'Fld_Part_SN'        => $_POST['Fld_Part_SN'] ?? '',
                'Fld_Supplier_ID'    => (int)($_POST['Fld_Supplier_ID'] ?? 0),
                'Fld_Entry_Date'     => date('Y-m-d'),
                'Fld_Part_Price'     => $_POST['Fld_Part_Price'] ?? 0,
                'Fld_Price_Currency_ID' => $_POST['Fld_Price_Currency_ID'] ?? '',
                'Fld_Qty'            => $_POST['Fld_Qty'] ?? 1,
                'Fld_Condition_ID'   => $_POST['Fld_Condition_ID'] ?? '',
                'Fld_Release_ID'     => $_POST['Fld_Release_ID'] ?? '',
                'Fld_Tag_Info_ID'    => $_POST['Fld_Tag_Info_ID'] ?? '',
                'Fld_Tag_Date'       => $_POST['Fld_Tag_Date'] ?? '',
                'Fld_Traceability_ID' => $_POST['Fld_Traceability_ID'] ?? '',
                'Fld_Owner_ID'       => $_POST['Fld_Owner_ID'] ?? '',
                'Fld_Stock_Location_ID' => $_POST['Fld_Stock_Location_ID'] ?? '',
                'Fld_Status_ID'      => $_POST['Fld_Status_ID'] ?? '',
                'Fld_Stock_Remark'   => $_POST['Fld_Remark'] ?? '',
                'Fld_Shelf_Life_Limit' => $_POST['Fld_Shelf_Life_Limit'] ?? '',
            ]);
            echo json_encode(['ok' => true, 'id' => $id]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
