<?php
/**
 * AeroCanada v2 — RFQ API Endpoint
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

            if (!empty($_GET['priority'])) {
                $where .= ' AND p.Fld_Priority_Text = ?';
                $params[] = $_GET['priority'];
            }
            if (!empty($_GET['employee_id'])) {
                $where .= ' AND r.Employee_ID = ?';
                $params[] = (int)$_GET['employee_id'];
            }
            if (!empty($_GET['period'])) {
                switch ($_GET['period']) {
                    case 'today': $where .= ' AND r.date = CURDATE()'; break;
                    case 'week': $where .= ' AND r.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'; break;
                    case 'month': $where .= ' AND r.date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")'; break;
                    case 'quarter': $where .= ' AND r.date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)'; break;
                }
            }

            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (r.Fld_RFQ_ID LIKE ? OR r.pn_rfq LIKE ? OR c.Fld_Company_Name LIKE ?)';
                $s = "%$search%";
                array_push($params, $s, $s, $s);
            }

            $totalAll = $db->fetchColumn('SELECT COUNT(*) FROM tbl_RFQ_1');
            $totalFiltered = $db->fetchColumn(
                "SELECT COUNT(*) FROM tbl_RFQ_1 r
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
                 LEFT JOIN tbl_Priority p ON p.Fld_Priority_ID = r.Fld_Priority_ID
                 WHERE $where", $params
            );

            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);
            $orderDir = ($_GET['order'][0]['dir'] ?? 'desc') === 'desc' ? 'DESC' : 'ASC';

            $rows = $db->fetchAll(
                "SELECT r.*, c.Fld_Company_Name AS customer_name,
                        cc.Fld_Contact_Name AS contact_name,
                        p.Fld_Priority_Text AS priority_text,
                        rt.Fld_RFQ_Type_Text AS rfq_type,
                        e.Employee_Name AS employee_name
                 FROM tbl_RFQ_1 r
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
                 LEFT JOIN tb_company_contact cc ON cc.id_company_contact = r.id_company_contact
                 LEFT JOIN tbl_Priority p ON p.Fld_Priority_ID = r.Fld_Priority_ID
                 LEFT JOIN tbl_RFQ_Type rt ON rt.Fld_RFQ_Type_ID = r.Fld_RFQ_Type_ID
                 LEFT JOIN tbl_Employee e ON e.Employee_ID = r.Employee_ID
                 WHERE $where
                 ORDER BY r.ID $orderDir
                 LIMIT $length OFFSET $start",
                $params
            );

            echo json_encode([
                'draw' => (int)($_GET['draw'] ?? 1),
                'recordsTotal' => (int)$totalAll,
                'recordsFiltered' => (int)$totalFiltered,
                'data' => $rows,
            ]);
            break;

        case 'create':
            CSRF::verify();
            $rfqId = $_POST['Fld_RFQ_ID'] ?? date('Y-m-d-His');
            $companyId = (int)($_POST['Fld_Customer_ID'] ?? 0);

            $id = $db->insert('tbl_RFQ_1', [
                'Fld_RFQ_ID'          => $rfqId,
                'Fld_Qty'             => $_POST['Fld_Qty'] ?? 1,
                'Fld_Part_ID'         => $_POST['Fld_Part_ID'] ?? '',
                'Fld_Observation'     => $_POST['Fld_Observation'] ?? '',
                'Fld_Customer_ID'     => $companyId,
                'date'                => $_POST['date'] ?? date('Y-m-d'),
                'Fld_RFQ_Type_ID'     => $_POST['Fld_RFQ_Type_ID'] ?? '',
                'Fld_Priority_ID'     => $_POST['Fld_Priority_ID'] ?? '',
                'Employee_ID'         => $_POST['Employee_ID'] ?? Auth::userId(),
                'id_company_contact'  => $_POST['id_company_contact'] ?? '',
                'Fld_Payment_Term_ID' => $_POST['Fld_Payment_Term_ID'] ?? '',
                'Fld_Condition_ID'    => $_POST['Fld_Condition_ID'] ?? '',
                'pn_rfq'              => $_POST['pn_rfq'] ?? '',
                'description_rfq'     => $_POST['description_rfq'] ?? '',
            ]);

            // Create master RFQ record if needed
            $exists = $db->fetchColumn('SELECT COUNT(*) FROM tbl_RFQ WHERE Fld_RFQ_ID = ?', [$rfqId]);
            if (!$exists) {
                $db->insert('tbl_RFQ', [
                    'Fld_RFQ_ID'             => $rfqId,
                    'Fld_Date'               => $_POST['date'] ?? date('Y-m-d'),
                    'Fld_Step_1'             => 'TRUE',
                    'Fld_Step_2'             => 'FALSE',
                    'Fld_Step_3'             => 'FALSE',
                    'Fld_Priority_ID'        => $_POST['Fld_Priority_ID'] ?? '',
                    'Fld_RFQ_ACI_Employee_Id' => Auth::userId(),
                    'Fld_Customer_ID'        => $companyId,
                ]);
            }

            echo json_encode(['ok' => true, 'id' => $id, 'rfq_id' => $rfqId]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id) {
                $db->delete('tbl_RFQ_1', 'ID = ?', [$id]);
                echo json_encode(['ok' => true]);
            } else {
                throw new \Exception('Invalid ID');
            }
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
