<?php
/**
 * AeroCanada v2 — Companies API Endpoint
 */

require_once __DIR__ . '/../bootstrap.php';

use AeroCanada\Core\{Auth, CSRF, Database};

Auth::requireAuth();

$action = $_REQUEST['action'] ?? '';
$db     = Database::getInstance();

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {

        case 'datatable':
            $where  = "c.status = 'Available'";
            $params = [];

            if (!empty($_GET['contact_id'])) {
                $where .= ' AND c.aci_contact = ?';
                $params[] = (int)$_GET['contact_id'];
            }
            if (!empty($_GET['rating'])) {
                $where .= ' AND c.companyrating = ?';
                $params[] = $_GET['rating'];
            }
            if (!empty($_GET['status'])) {
                $where = "c.status = ?";
                $params = [$_GET['status']];
            }

            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (c.Fld_Company_Name LIKE ? OR c.cage_code LIKE ? OR d.Fld_Company_Country LIKE ?)';
                $s = "%$search%";
                array_push($params, $s, $s, $s);
            }

            $totalAll = $db->fetchColumn("SELECT COUNT(*) FROM tb_company");
            $totalFiltered = $db->fetchColumn(
                "SELECT COUNT(DISTINCT c.Fld_Company_ID) FROM tb_company c
                 LEFT JOIN tbl_Company_Details d ON d.Fld_Company_ID = c.Fld_Company_ID
                 WHERE $where",
                $params
            );

            $orderCols = ['c.Fld_Company_ID','c.logocompany','c.Fld_Company_Name','c.cage_code','ct.Fld_Company_Type_Text','d.Fld_Company_Country','','e.Employee_Name','c.companyrating'];
            $orderIdx  = (int)($_GET['order'][0]['column'] ?? 2);
            $orderDir  = ($_GET['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
            $orderBy   = ($orderCols[$orderIdx] ?? 'c.Fld_Company_Name') . ' ' . $orderDir;

            $start  = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $sql = "SELECT c.*,
                           d.Fld_Company_Country AS country,
                           d.Fld_Company_City AS city,
                           d.UTC_timezone,
                           ct.Fld_Company_Type_Text AS company_type,
                           e.Employee_Name AS aci_contact_name
                    FROM tb_company c
                    LEFT JOIN tbl_Company_Details d ON d.Fld_Company_ID = c.Fld_Company_ID
                    LEFT JOIN tbl_Company_Type ct ON ct.Fld_Company_Type_ID = d.Fld_Company_Type_ID
                    LEFT JOIN tbl_Employee e ON e.Employee_ID = c.aci_contact
                    WHERE $where
                    GROUP BY c.Fld_Company_ID
                    ORDER BY $orderBy
                    LIMIT $length OFFSET $start";

            $rows = $db->fetchAll($sql, $params);

            // Compute local time for each company
            foreach ($rows as &$row) {
                $row['local_time'] = '';
                if (!empty($row['UTC_timezone'])) {
                    try {
                        $tz = new \DateTimeZone($row['UTC_timezone']);
                        $now = new \DateTime('now', $tz);
                        $row['local_time'] = $now->format('H:i');
                    } catch (\Exception $e) {
                        $row['local_time'] = '';
                    }
                }
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
            if (!$id) throw new \Exception('Invalid ID');

            $company = $db->fetch('SELECT * FROM tb_company WHERE Fld_Company_ID = ?', [$id]);
            $addresses = $db->fetchAll('SELECT * FROM tbl_Company_Details WHERE Fld_Company_ID = ?', [$id]);
            $contacts = $db->fetchAll(
                "SELECT * FROM tb_company_contact WHERE Fld_Company_ID = ? AND status = 'Available'",
                [$id]
            );

            // Type and VAT from first address
            $type = '';
            $vat  = '';
            if ($addresses) {
                $typeId = $addresses[0]['Fld_Company_Type_ID'] ?? '';
                if ($typeId) {
                    $t = $db->fetch('SELECT Fld_Company_Type_Text FROM tbl_Company_Type WHERE Fld_Company_Type_ID = ?', [$typeId]);
                    $type = $t['Fld_Company_Type_Text'] ?? '';
                }
                $vat = $addresses[0]['Fld_VAT_Nbr'] ?? '';
            }

            echo json_encode([
                'ok'   => true,
                'data' => [
                    'company'   => $company,
                    'addresses' => $addresses,
                    'contacts'  => $contacts,
                    'type'      => $type,
                    'vat'       => $vat,
                ],
            ]);
            break;

        case 'create':
            CSRF::verify();
            // Sanitize
            $name = trim($_POST['Fld_Company_Name'] ?? '');
            if (empty($name)) throw new \Exception('Company name is required');

            $companyId = $db->insert('tb_company', [
                'Fld_Company_Name'                => $name,
                'companyrating'                    => $_POST['companyrating'] ?? '',
                'aci_contact'                      => $_POST['Employee_ID'] ?? Auth::userId(),
                'logocompany'                      => '', // handled separately
                'status'                           => 'Available',
                'internet'                         => $_POST['internet'] ?? '',
                'cage_code'                        => $_POST['cage_code'] ?? '',
                'customer_payment_term_id'         => $_POST['customer_payment_term_id'] ?? '',
                'customer_payment_term_amount'     => $_POST['customer_payment_term_amount'] ?? '',
                'customer_payment_term_currencyid' => $_POST['customer_payment_term_currencyid'] ?? '',
                'aci_payment_term_id'              => $_POST['aci_payment_term_id'] ?? '',
                'aci_payment_term_amount'          => $_POST['aci_payment_term_amount'] ?? '',
                'aci_payment_term_currencyid'      => $_POST['aci_payment_term_currencyid'] ?? '',
            ]);

            // Add first address
            if (!empty($_POST['Fld_Company_Country1']) || !empty($_POST['Fld_Company_City1'])) {
                $db->insert('tbl_Company_Details', [
                    'Fld_Company_ID'           => $companyId,
                    'Fld_Company_Type_ID'      => $_POST['Fld_Company_Type_ID'] ?? '',
                    'Fld_Company_Country'      => $_POST['Fld_Company_Country1'] ?? '',
                    'Fld_Company_City'         => $_POST['Fld_Company_City1'] ?? '',
                    'Fld_Company_State'        => $_POST['Fld_Company_State1'] ?? '',
                    'Fld_Company_Street'       => $_POST['Fld_Company_Street1'] ?? '',
                    'Fld_Company_ZipCode'      => $_POST['Fld_Company_ZipCode1'] ?? '',
                    'Fld_Company_Phone'        => $_POST['Fld_Company_Phone1'] ?? '',
                    'Fld_Company_Email'        => $_POST['Fld_Company_Email1'] ?? '',
                    'Fld_Company_BAX_Contact'  => Auth::userId(),
                    'Fld_VAT_Nbr'              => $_POST['Fld_VAT_Nbr'] ?? '',
                    'Fld_Company_Address_Type' => $_POST['Fld_Company_Address_Type1'] ?? '',
                    'UTC_timezone'             => $_POST['UTC_timezone1'] ?? '',
                    'title_address'            => $_POST['title_address1'] ?? '',
                ]);
            }

            // Handle logo upload
            if (!empty($_FILES['logocompany']['name'])) {
                $upload = new \AeroCanada\Core\FileUpload();
                $cfg = require __DIR__ . '/../config.php';
                $filename = $upload->upload('logocompany', $cfg['upload']['logo_dir'], ['images_only' => true]);
                if ($filename) {
                    $db->update('tb_company', ['logocompany' => $filename], 'Fld_Company_ID = ?', [$companyId]);
                }
            }

            echo json_encode(['ok' => true, 'id' => $companyId]);
            break;

        case 'archive':
            $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $id = (int)($data['id'] ?? 0);
            $db->update('tb_company', ['status' => 'archive'], 'Fld_Company_ID = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'search':
            $term = $_GET['q'] ?? '';
            $results = $db->fetchAll(
                "SELECT Fld_Company_ID, Fld_Company_Name, cage_code FROM tb_company
                 WHERE Fld_Company_Name LIKE ? AND status = 'Available'
                 ORDER BY Fld_Company_Name LIMIT 20",
                ["%$term%"]
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
