<?php
/**
 * AeroCanada v2 — Shipping API (NEW MODULE)
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, CSRF, Database};

Auth::requireAuth();
$action = $_REQUEST['action'] ?? '';
$db = Database::getInstance();
header('Content-Type: application/json; charset=utf-8');

// Ensure tables exist
$db->pdo()->exec("
    CREATE TABLE IF NOT EXISTS tbl_Shipping (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shipping_number VARCHAR(50) NOT NULL UNIQUE,
        invoice_id INT,
        rfq_id VARCHAR(100),
        company_id INT,
        ship_from TEXT,
        ship_to TEXT,
        shipper_id INT,
        tracking_number VARCHAR(200),
        ship_date DATE,
        estimated_arrival DATE,
        actual_arrival DATE,
        weight VARCHAR(50),
        dimensions VARCHAR(100),
        status ENUM('preparing','shipped','in_transit','delivered','returned') DEFAULT 'preparing',
        notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_company (company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->pdo()->exec("
    CREATE TABLE IF NOT EXISTS tbl_Shipping_Items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shipping_id INT NOT NULL,
        part_id INT,
        serial_number VARCHAR(100),
        quantity INT DEFAULT 1,
        condition_id INT,
        release_id INT,
        FOREIGN KEY (shipping_id) REFERENCES tbl_Shipping(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

try {
    switch ($action) {
        case 'datatable':
            $where = '1=1';
            $params = [];
            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (sh.shipping_number LIKE ? OR sh.tracking_number LIKE ? OR c.Fld_Company_Name LIKE ?)';
                array_push($params, "%$search%", "%$search%", "%$search%");
            }

            $total = $db->fetchColumn("SELECT COUNT(*) FROM tbl_Shipping sh LEFT JOIN tb_company c ON c.Fld_Company_ID = sh.company_id WHERE $where", $params);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $rows = $db->fetchAll(
                "SELECT sh.*, c.Fld_Company_Name AS company_name,
                        s.Fld_Shipper_Text AS shipper_name,
                        inv.invoice_number
                 FROM tbl_Shipping sh
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = sh.company_id
                 LEFT JOIN tbl_Shipper s ON s.Fld_Shipper_ID = sh.shipper_id
                 LEFT JOIN tbl_Invoices inv ON inv.id = sh.invoice_id
                 WHERE $where ORDER BY sh.id DESC LIMIT $length OFFSET $start", $params
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
            $lastNum = $db->fetchColumn("SELECT MAX(id) FROM tbl_Shipping") ?? 0;
            $number = 'SHP-' . date('Y') . '-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

            $id = $db->insert('tbl_Shipping', [
                'shipping_number'   => $number,
                'invoice_id'        => (int)($_POST['invoice_id'] ?? 0) ?: null,
                'rfq_id'            => $_POST['rfq_id'] ?? null,
                'company_id'        => (int)($_POST['company_id'] ?? 0),
                'ship_from'         => $_POST['ship_from'] ?? '',
                'ship_to'           => $_POST['ship_to'] ?? '',
                'shipper_id'        => (int)($_POST['shipper_id'] ?? 0),
                'tracking_number'   => $_POST['tracking_number'] ?? '',
                'ship_date'         => $_POST['ship_date'] ?? date('Y-m-d'),
                'estimated_arrival' => $_POST['estimated_arrival'] ?? null,
                'weight'            => $_POST['weight'] ?? '',
                'dimensions'        => $_POST['dimensions'] ?? '',
                'notes'             => $_POST['notes'] ?? '',
                'created_by'        => Auth::userId(),
            ]);

            echo json_encode(['ok' => true, 'id' => $id, 'shipping_number' => $number]);
            break;

        case 'update_status':
            CSRF::verify();
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['preparing', 'shipped', 'in_transit', 'delivered', 'returned'];
            if (!in_array($status, $allowed)) throw new \Exception('Invalid status');

            $data = ['status' => $status];
            if ($status === 'delivered') $data['actual_arrival'] = date('Y-m-d');

            $db->update('tbl_Shipping', $data, 'id = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'update_tracking':
            CSRF::verify();
            $id = (int)($_POST['id'] ?? 0);
            $db->update('tbl_Shipping', [
                'tracking_number' => $_POST['tracking_number'] ?? '',
                'status' => 'shipped',
            ], 'id = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
