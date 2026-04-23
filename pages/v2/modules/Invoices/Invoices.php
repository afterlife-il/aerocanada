<?php
/**
 * AeroCanada ERP v2 - Invoices Module (NEW)
 * Manages invoices (proforma, commercial, credit notes) with line items.
 * Includes auto-creation of tables if they do not exist.
 */

namespace AeroCanada\Modules\Invoices;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Invoices extends Module
{
    protected string $table      = 'tbl_Invoices';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTablesExist();
    }

    /**
     * Create the invoice tables if they do not already exist.
     */
    public function ensureTablesExist(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tbl_Invoices` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `invoice_number` VARCHAR(50) NOT NULL,
                `invoice_type`   ENUM('proforma','commercial','credit_note') NOT NULL DEFAULT 'proforma',
                `rfq_id`         INT UNSIGNED NULL,
                `company_id`     INT UNSIGNED NULL,
                `contact_id`     INT UNSIGNED NULL,
                `invoice_date`   DATE NOT NULL,
                `due_date`       DATE NULL,
                `currency_id`    INT UNSIGNED NULL,
                `subtotal`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `tax_rate`       DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `tax_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `status`         ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
                `notes`          TEXT NULL,
                `created_by`     INT UNSIGNED NULL,
                `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_invoice_company` (`company_id`),
                INDEX `idx_invoice_rfq`     (`rfq_id`),
                INDEX `idx_invoice_status`  (`status`),
                INDEX `idx_invoice_date`    (`invoice_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tbl_Invoice_Items` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `invoice_id`   INT UNSIGNED NOT NULL,
                `part_id`      INT UNSIGNED NULL,
                `description`  VARCHAR(500) NOT NULL DEFAULT '',
                `quantity`     DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                `unit_price`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `condition_id` INT UNSIGNED NULL,
                INDEX `idx_item_invoice` (`invoice_id`),
                CONSTRAINT `fk_invoice_item_invoice`
                    FOREIGN KEY (`invoice_id`) REFERENCES `tbl_Invoices`(`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // ── Invoice CRUD ────────────────────────────────────────────────

    /**
     * Create a new invoice.
     */
    public function createInvoice(array $data): int
    {
        $allowed = [
            'invoice_number', 'invoice_type', 'rfq_id', 'company_id',
            'contact_id', 'invoice_date', 'due_date', 'currency_id',
            'subtotal', 'tax_rate', 'tax_amount', 'total',
            'status', 'notes', 'created_by',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['invoice_date'])) {
            $filtered['invoice_date'] = date('Y-m-d');
        }

        return $this->db->insert($this->table, $filtered);
    }

    // ── Invoice Items ───────────────────────────────────────────────

    /**
     * Add a line item to an invoice.
     */
    public function addItem(int $invoiceId, array $data): int
    {
        $allowed = [
            'part_id', 'description', 'quantity', 'unit_price',
            'total', 'condition_id',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['invoice_id'] = $invoiceId;

        // Auto-calculate line total if not provided
        if (!isset($filtered['total']) && isset($filtered['quantity'], $filtered['unit_price'])) {
            $filtered['total'] = round((float)$filtered['quantity'] * (float)$filtered['unit_price'], 2);
        }

        $id = $this->db->insert('tbl_Invoice_Items', $filtered);
        $this->calculateTotals($invoiceId);
        return $id;
    }

    /**
     * Update an invoice line item.
     */
    public function updateItem(int $itemId, array $data): int
    {
        $allowed = [
            'part_id', 'description', 'quantity', 'unit_price',
            'total', 'condition_id',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        // Auto-calculate line total if quantity and price present
        if (isset($filtered['quantity'], $filtered['unit_price'])) {
            $filtered['total'] = round((float)$filtered['quantity'] * (float)$filtered['unit_price'], 2);
        }

        $affected = $this->db->update(
            'tbl_Invoice_Items',
            $filtered,
            '`id` = ?',
            [$itemId]
        );

        // Recalculate parent invoice
        $item = $this->db->fetch("SELECT `invoice_id` FROM `tbl_Invoice_Items` WHERE `id` = ?", [$itemId]);
        if ($item) {
            $this->calculateTotals($item['invoice_id']);
        }

        return $affected;
    }

    /**
     * Remove an invoice line item.
     */
    public function removeItem(int $itemId): int
    {
        $item = $this->db->fetch("SELECT `invoice_id` FROM `tbl_Invoice_Items` WHERE `id` = ?", [$itemId]);

        $affected = $this->db->delete('tbl_Invoice_Items', '`id` = ?', [$itemId]);

        if ($item) {
            $this->calculateTotals($item['invoice_id']);
        }

        return $affected;
    }

    // ── Totals ──────────────────────────────────────────────────────

    /**
     * Recalculate subtotal, tax, and total for an invoice.
     */
    public function calculateTotals(int $invoiceId): void
    {
        $subtotal = (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(`total`), 0) FROM `tbl_Invoice_Items` WHERE `invoice_id` = ?",
            [$invoiceId]
        );

        $invoice = $this->find($invoiceId);
        $taxRate  = $invoice ? (float) $invoice['tax_rate'] : 0.00;
        $taxAmt   = round($subtotal * $taxRate / 100, 2);
        $total    = round($subtotal + $taxAmt, 2);

        $this->db->update(
            $this->table,
            [
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmt,
                'total'      => $total,
            ],
            '`id` = ?',
            [$invoiceId]
        );
    }

    // ── Status Transitions ──────────────────────────────────────────

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(int $id): int
    {
        return $this->db->update(
            $this->table,
            ['status' => 'sent'],
            '`id` = ?',
            [$id]
        );
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(int $id): int
    {
        return $this->db->update(
            $this->table,
            ['status' => 'paid'],
            '`id` = ?',
            [$id]
        );
    }

    // ── PDF Generation ──────────────────────────────────────────────

    /**
     * Generate a PDF representation of the invoice.
     * Returns an associative array with all data needed for PDF rendering.
     */
    public function generatePDF(int $id): array
    {
        $invoice = $this->find($id);
        if (!$invoice) {
            throw new \RuntimeException('Invoice not found.');
        }

        $invoice['items'] = $this->db->fetchAll(
            "SELECT ii.*, p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    c.Fld_Condition AS condition_name
             FROM `tbl_Invoice_Items` ii
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = ii.part_id
             LEFT JOIN `tbl_Condition` c ON c.Fld_Condition_ID = ii.condition_id
             WHERE ii.`invoice_id` = ?
             ORDER BY ii.`id` ASC",
            [$id]
        );

        $invoice['company'] = null;
        if (!empty($invoice['company_id'])) {
            $invoice['company'] = $this->db->fetch(
                "SELECT * FROM `tb_company` WHERE `Fld_Company_ID` = ?",
                [$invoice['company_id']]
            );
        }

        $invoice['contact'] = null;
        if (!empty($invoice['contact_id'])) {
            $invoice['contact'] = $this->db->fetch(
                "SELECT * FROM `tb_company_contact` WHERE `Fld_Contact_ID` = ?",
                [$invoice['contact_id']]
            );
        }

        return $invoice;
    }

    // ── Queries ─────────────────────────────────────────────────────

    /**
     * Get all invoices for a company.
     */
    public function getByCompany(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `company_id` = ? ORDER BY `invoice_date` DESC",
            [$companyId]
        );
    }

    // ── DataTables ──────────────────────────────────────────────────

    /**
     * DataTables server-side handler for invoices listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'id'],
            ['db' => 'invoice_number'],
            ['db' => 'invoice_type'],
            ['db' => 'company_id'],
            ['db' => 'invoice_date'],
            ['db' => 'due_date'],
            ['db' => 'total'],
            ['db' => 'status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
