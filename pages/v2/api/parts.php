<?php
/**
 * AeroCanada v2 — Parts API Endpoint
 * Handles all AJAX requests for the Parts module.
 */

require_once __DIR__ . '/../bootstrap.php';

use AeroCanada\Core\{Auth, CSRF, Database};
use AeroCanada\Modules\Parts\Parts;

Auth::requireAuth();

$action = $_REQUEST['action'] ?? '';
$db     = Database::getInstance();

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {

        case 'datatable':
            $parts = new Parts();
            $request = $_GET;

            // Build base WHERE
            $where  = '1=1';
            $params = [];

            $status = $_GET['status'] ?? 'Available';
            if ($status) {
                $where .= ' AND p.status = ?';
                $params[] = $status;
            }

            $aircraftId = $_GET['aircraft_id'] ?? '';
            if ($aircraftId) {
                $where .= ' AND p.Fld_AC_ID = ?';
                $params[] = (int)$aircraftId;
            }

            // Search
            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (p.Fld_Part_Nbr LIKE ? OR p.Fld_Part_Desc LIKE ? OR p.alt_pn LIKE ? OR p.ata_chapter LIKE ?)';
                $s = "%$search%";
                array_push($params, $s, $s, $s, $s);
            }

            // Count
            $totalAll = $db->fetchColumn('SELECT COUNT(*) FROM tbl_Parts');
            $totalFiltered = $db->fetchColumn(
                "SELECT COUNT(*) FROM tbl_Parts p WHERE $where",
                $params
            );

            // Order
            $orderCols = ['p.Fld_Part_Nbr','p.Fld_Part_Desc','c.Fld_Company_Name','a.Fld_AC_Model','p.Fld_Part_List_Price','p.ata_chapter','p.status','p.Fld_Part_ID'];
            $orderIdx  = (int)($_GET['order'][0]['column'] ?? 0);
            $orderDir  = ($_GET['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
            $orderBy   = ($orderCols[$orderIdx] ?? 'p.Fld_Part_Nbr') . ' ' . $orderDir;

            $start  = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $sql = "SELECT p.*,
                           c.Fld_Company_Name AS mfg_name,
                           a.Fld_AC_Model AS aircraft_model,
                           cur.Fld_Currency_Text AS currency_text
                    FROM tbl_Parts p
                    LEFT JOIN tb_company c ON c.Fld_Company_ID = p.Fld_Part_MFG
                    LEFT JOIN tbl_Aircraft a ON a.Fld_AC_ID = p.Fld_AC_ID
                    LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = p.Fld_Part_Price_Currency_ID
                    WHERE $where
                    ORDER BY $orderBy
                    LIMIT $length OFFSET $start";

            $rows = $db->fetchAll($sql, $params);

            // Format data
            foreach ($rows as &$row) {
                $row['list_price_fmt'] = $row['Fld_Part_List_Price']
                    ? number_format((float)$row['Fld_Part_List_Price'], 2) . ' ' . ($row['currency_text'] ?? 'USD')
                    : '-';
            }

            echo json_encode([
                'draw'            => (int)($_GET['draw'] ?? 1),
                'recordsTotal'    => (int)$totalAll,
                'recordsFiltered' => (int)$totalFiltered,
                'data'            => $rows,
            ]);
            break;

        case 'detail':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { throw new \Exception('Invalid ID'); }

            $part = $db->fetch(
                'SELECT p.*, c.Fld_Company_Name AS mfg_name, a.Fld_AC_Model AS aircraft_model,
                        cur.Fld_Currency_Text AS currency_text
                 FROM tbl_Parts p
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = p.Fld_Part_MFG
                 LEFT JOIN tbl_Aircraft a ON a.Fld_AC_ID = p.Fld_AC_ID
                 LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = p.Fld_Part_Price_Currency_ID
                 WHERE p.Fld_Part_ID = ?',
                [$id]
            );

            $part['list_price_fmt'] = $part['Fld_Part_List_Price']
                ? number_format((float)$part['Fld_Part_List_Price'], 2) . ' ' . ($part['currency_text'] ?? 'USD')
                : '-';

            // Documents
            $docs = $db->fetchAll(
                'SELECT * FROM tbl_docs_attachment_pn WHERE pn_id = ?',
                [$id]
            );

            echo json_encode(['ok' => true, 'data' => $part, 'documents' => $docs]);
            break;

        case 'create':
            CSRF::verify();
            $parts = new Parts();
            $id = $parts->addPart($_POST);
            echo json_encode(['ok' => true, 'id' => $id, 'message' => 'Part created successfully']);
            break;

        case 'update':
            CSRF::verify();
            $parts = new Parts();
            $id = (int)($_POST['Fld_Part_ID'] ?? 0);
            $parts->updatePart($id, $_POST);
            echo json_encode(['ok' => true, 'message' => 'Part updated']);
            break;

        case 'archive':
            $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $id = (int)($data['id'] ?? 0);
            $db->update('tbl_Parts', ['status' => 'archive'], 'Fld_Part_ID = ?', [$id]);
            echo json_encode(['ok' => true, 'message' => 'Part archived']);
            break;

        case 'search':
            $term = $_GET['q'] ?? '';
            $results = $db->fetchAll(
                "SELECT Fld_Part_ID, Fld_Part_Nbr, Fld_Part_Desc FROM tbl_Parts
                 WHERE (Fld_Part_Nbr LIKE ? OR Fld_Part_Desc LIKE ?) AND status = 'Available'
                 LIMIT 20",
                ["%$term%", "%$term%"]
            );
            echo json_encode($results);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
