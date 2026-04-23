<?php
/**
 * AeroCanada v2 — Invoices API (NEW MODULE)
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, CSRF, Database};

Auth::requireAuth();
$action = $_REQUEST['action'] ?? '';
$db = Database::getInstance();
header('Content-Type: application/json; charset=utf-8');

// Ensure tables exist
$db->pdo()->exec("
    CREATE TABLE IF NOT EXISTS tbl_Invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(50) NOT NULL UNIQUE,
        invoice_type ENUM('proforma','commercial','credit_note') NOT NULL DEFAULT 'proforma',
        rfq_id VARCHAR(100),
        company_id INT,
        contact_id INT,
        invoice_date DATE NOT NULL,
        due_date DATE,
        currency_id INT,
        subtotal DECIMAL(12,2) DEFAULT 0,
        tax_rate DECIMAL(5,2) DEFAULT 0,
        tax_amount DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        status ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
        notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_company (company_id),
        INDEX idx_status (status),
        INDEX idx_type (invoice_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->pdo()->exec("
    CREATE TABLE IF NOT EXISTS tbl_Invoice_Items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        part_id INT,
        description VARCHAR(500),
        quantity INT DEFAULT 1,
        unit_price DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        condition_id INT,
        FOREIGN KEY (invoice_id) REFERENCES tbl_Invoices(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

try {
    switch ($action) {
        case 'datatable':
            $where = '1=1';
            $params = [];
            $type = $_GET['type'] ?? '';
            if ($type) { $where .= ' AND i.invoice_type = ?'; $params[] = $type; }

            $search = $_GET['search']['value'] ?? '';
            if ($search !== '') {
                $where .= ' AND (i.invoice_number LIKE ? OR c.Fld_Company_Name LIKE ?)';
                array_push($params, "%$search%", "%$search%");
            }

            $total = $db->fetchColumn("SELECT COUNT(*) FROM tbl_Invoices i LEFT JOIN tb_company c ON c.Fld_Company_ID = i.company_id WHERE $where", $params);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 25);

            $rows = $db->fetchAll(
                "SELECT i.*, c.Fld_Company_Name AS company_name, cur.Fld_Currency_Text AS currency
                 FROM tbl_Invoices i
                 LEFT JOIN tb_company c ON c.Fld_Company_ID = i.company_id
                 LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = i.currency_id
                 WHERE $where ORDER BY i.id DESC LIMIT $length OFFSET $start", $params
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
            $type = $_POST['invoice_type'] ?? 'proforma';
            $prefix = ['proforma' => 'PI', 'commercial' => 'INV', 'credit_note' => 'CN'];
            $lastNum = $db->fetchColumn("SELECT MAX(id) FROM tbl_Invoices") ?? 0;
            $number = ($prefix[$type] ?? 'INV') . '-' . date('Y') . '-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

            $id = $db->insert('tbl_Invoices', [
                'invoice_number' => $number,
                'invoice_type'   => $type,
                'rfq_id'         => $_POST['rfq_id'] ?? null,
                'company_id'     => (int)($_POST['company_id'] ?? 0),
                'contact_id'     => (int)($_POST['contact_id'] ?? 0),
                'invoice_date'   => $_POST['invoice_date'] ?? date('Y-m-d'),
                'due_date'       => $_POST['due_date'] ?? null,
                'currency_id'    => (int)($_POST['currency_id'] ?? 0),
                'notes'          => $_POST['notes'] ?? '',
                'created_by'     => Auth::userId(),
            ]);

            echo json_encode(['ok' => true, 'id' => $id, 'invoice_number' => $number]);
            break;

        case 'add_item':
            CSRF::verify();
            $invoiceId = (int)($_POST['invoice_id'] ?? 0);
            $qty = (int)($_POST['quantity'] ?? 1);
            $price = (float)($_POST['unit_price'] ?? 0);

            $itemId = $db->insert('tbl_Invoice_Items', [
                'invoice_id'   => $invoiceId,
                'part_id'      => (int)($_POST['part_id'] ?? 0),
                'description'  => $_POST['description'] ?? '',
                'quantity'     => $qty,
                'unit_price'   => $price,
                'total'        => $qty * $price,
                'condition_id' => (int)($_POST['condition_id'] ?? 0),
            ]);

            // Recalculate totals
            $subtotal = (float)$db->fetchColumn('SELECT SUM(total) FROM tbl_Invoice_Items WHERE invoice_id = ?', [$invoiceId]);
            $invoice = $db->fetch('SELECT tax_rate FROM tbl_Invoices WHERE id = ?', [$invoiceId]);
            $taxRate = (float)($invoice['tax_rate'] ?? 0);
            $taxAmount = $subtotal * ($taxRate / 100);

            $db->update('tbl_Invoices', [
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmount,
                'total'      => $subtotal + $taxAmount,
            ], 'id = ?', [$invoiceId]);

            echo json_encode(['ok' => true, 'item_id' => $itemId]);
            break;

        case 'mark_sent':
            $id = (int)($_POST['id'] ?? 0);
            $db->update('tbl_Invoices', ['status' => 'sent'], 'id = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'mark_paid':
            $id = (int)($_POST['id'] ?? 0);
            $db->update('tbl_Invoices', ['status' => 'paid'], 'id = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
