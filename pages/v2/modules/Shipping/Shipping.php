<?php
/**
 * AeroCanada ERP v2 - Shipping Module (NEW)
 * Manages shipments, tracking, status updates, and shipment items.
 * Includes auto-creation of tables if they do not exist.
 */

namespace AeroCanada\Modules\Shipping;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Shipping extends Module
{
    protected string $table      = 'tbl_Shipping';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTablesExist();
    }

    /**
     * Create the shipping tables if they do not already exist.
     */
    public function ensureTablesExist(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tbl_Shipping` (
                `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `shipping_number`   VARCHAR(50) NOT NULL,
                `invoice_id`        INT UNSIGNED NULL,
                `rfq_id`            INT UNSIGNED NULL,
                `company_id`        INT UNSIGNED NULL,
                `ship_from`         VARCHAR(255) NULL,
                `ship_to`           VARCHAR(255) NULL,
                `shipper_id`        INT UNSIGNED NULL,
                `tracking_number`   VARCHAR(100) NULL,
                `ship_date`         DATE NULL,
                `estimated_arrival` DATE NULL,
                `actual_arrival`    DATE NULL,
                `weight`            DECIMAL(10,2) NULL,
                `dimensions`        VARCHAR(100) NULL,
                `status`            ENUM('preparing','shipped','in_transit','delivered','returned') NOT NULL DEFAULT 'preparing',
                `notes`             TEXT NULL,
                `created_by`        INT UNSIGNED NULL,
                `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_shipping_invoice`  (`invoice_id`),
                INDEX `idx_shipping_rfq`      (`rfq_id`),
                INDEX `idx_shipping_company`  (`company_id`),
                INDEX `idx_shipping_status`   (`status`),
                INDEX `idx_shipping_tracking` (`tracking_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tbl_Shipping_Items` (
                `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `shipping_id`   INT UNSIGNED NOT NULL,
                `part_id`       INT UNSIGNED NULL,
                `serial_number` VARCHAR(100) NULL,
                `quantity`      DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                `condition_id`  INT UNSIGNED NULL,
                `release_id`    INT UNSIGNED NULL,
                INDEX `idx_sitem_shipping` (`shipping_id`),
                CONSTRAINT `fk_shipping_item_shipping`
                    FOREIGN KEY (`shipping_id`) REFERENCES `tbl_Shipping`(`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // ── Shipment CRUD ───────────────────────────────────────────────

    /**
     * Create a new shipment.
     */
    public function createShipment(array $data): int
    {
        $allowed = [
            'shipping_number', 'invoice_id', 'rfq_id', 'company_id',
            'ship_from', 'ship_to', 'shipper_id', 'tracking_number',
            'ship_date', 'estimated_arrival', 'actual_arrival',
            'weight', 'dimensions', 'status', 'notes', 'created_by',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        return $this->db->insert($this->table, $filtered);
    }

    // ── Shipment Items ──────────────────────────────────────────────

    /**
     * Add an item to a shipment.
     */
    public function addItem(int $shipmentId, array $data): int
    {
        $allowed = [
            'part_id', 'serial_number', 'quantity',
            'condition_id', 'release_id',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['shipping_id'] = $shipmentId;

        return $this->db->insert('tbl_Shipping_Items', $filtered);
    }

    // ── Tracking & Status ───────────────────────────────────────────

    /**
     * Update tracking number for a shipment.
     */
    public function updateTracking(int $id, string $tracking): int
    {
        return $this->db->update(
            $this->table,
            ['tracking_number' => $tracking],
            '`id` = ?',
            [$id]
        );
    }

    /**
     * Update shipment status.
     */
    public function updateStatus(int $id, string $status): int
    {
        $validStatuses = ['preparing', 'shipped', 'in_transit', 'delivered', 'returned'];
        if (!in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException('Invalid shipment status: ' . $status);
        }

        $data = ['status' => $status];

        // Auto-set actual arrival when delivered
        if ($status === 'delivered') {
            $data['actual_arrival'] = date('Y-m-d');
        }

        return $this->db->update(
            $this->table,
            $data,
            '`id` = ?',
            [$id]
        );
    }

    // ── Queries ─────────────────────────────────────────────────────

    /**
     * Get all shipments linked to an invoice.
     */
    public function getByInvoice(int $invoiceId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, sh.Fld_Shipper_Name AS shipper_name
             FROM `{$this->table}` s
             LEFT JOIN `tbl_Shipper` sh ON sh.Fld_Shipper_ID = s.shipper_id
             WHERE s.`invoice_id` = ?
             ORDER BY s.`ship_date` DESC",
            [$invoiceId]
        );
    }

    // ── DataTables ──────────────────────────────────────────────────

    /**
     * DataTables server-side handler for shipments listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'id'],
            ['db' => 'shipping_number'],
            ['db' => 'company_id'],
            ['db' => 'tracking_number'],
            ['db' => 'ship_date'],
            ['db' => 'estimated_arrival'],
            ['db' => 'status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
