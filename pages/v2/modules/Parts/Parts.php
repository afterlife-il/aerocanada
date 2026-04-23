<?php
/**
 * AeroCanada ERP v2 - Parts Module
 * Manages parts catalog: CRUD, multi-add, document attachments, DataTables.
 */

namespace AeroCanada\Modules\Parts;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;
use AeroCanada\Core\FileUpload;

class Parts extends Module
{
    protected string $table      = 'tbl_Parts';
    protected string $primaryKey = 'Fld_Part_ID';

    /**
     * Add a new part.
     */
    public function addPart(array $data): int
    {
        $allowed = [
            'Fld_Part_Nbr', 'Fld_Part_Desc', 'Fld_Part_MFG', 'Fld_AC_ID',
            'Fld_Part_List_Price', 'Fld_Part_Price_Currency_ID', 'Fld_Remark',
            'status', 'alt_pn', 'Fld_Add_PN_Date', 'aci_contact_entry',
            'ata_chapter', 'cage_code', 'moq', 'oem_lead_time',
            'core_value', 'id_currency_core_value',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_Add_PN_Date'])) {
            $filtered['Fld_Add_PN_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filtered);
    }

    /**
     * Update an existing part.
     */
    public function updatePart(int $id, array $data): int
    {
        $allowed = [
            'Fld_Part_Nbr', 'Fld_Part_Desc', 'Fld_Part_MFG', 'Fld_AC_ID',
            'Fld_Part_List_Price', 'Fld_Part_Price_Currency_ID', 'Fld_Remark',
            'status', 'alt_pn', 'aci_contact_entry',
            'ata_chapter', 'cage_code', 'moq', 'oem_lead_time',
            'core_value', 'id_currency_core_value',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            $this->table,
            $filtered,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Soft-archive a part.
     */
    public function archivePart(int $id): int
    {
        return $this->db->update(
            $this->table,
            ['status' => 'archive'],
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Find a part by its part number (exact match).
     */
    public function getPartByNumber(string $pn): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `Fld_Part_Nbr` = ?",
            [$pn]
        );
    }

    /**
     * Search parts by number, description, or manufacturer.
     */
    public function searchParts(string $term): array
    {
        $like = "%{$term}%";
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}`
             WHERE `Fld_Part_Nbr`  LIKE ?
                OR `Fld_Part_Desc` LIKE ?
                OR `Fld_Part_MFG`  LIKE ?
                OR `alt_pn`        LIKE ?
             ORDER BY `Fld_Part_Nbr` ASC",
            [$like, $like, $like, $like]
        );
    }

    /**
     * Bulk-add multiple parts in a single transaction.
     */
    public function addMultiParts(array $items): array
    {
        $ids = [];
        $this->db->beginTransaction();
        try {
            foreach ($items as $item) {
                $ids[] = $this->addPart($item);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $ids;
    }

    /**
     * Attach a document to a part.
     */
    public function addDocument(int $partId, array $file): int
    {
        $upload   = new FileUpload();
        $dir      = __DIR__ . '/../../uploads/parts/';
        $filename = $upload->upload('file', $dir);

        if ($filename === null) {
            throw new \RuntimeException('File upload failed.');
        }

        return $this->db->insert('tbl_docs_attachment_part', [
            'part_id'    => $partId,
            'filename'   => $filename,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete a document attachment.
     */
    public function deleteDocument(int $docId): int
    {
        $doc = $this->db->fetch(
            "SELECT `filename` FROM `tbl_docs_attachment_part` WHERE `id` = ?",
            [$docId]
        );

        if ($doc) {
            $upload = new FileUpload();
            $upload->delete(__DIR__ . '/../../uploads/parts/', $doc['filename']);
        }

        return $this->db->delete('tbl_docs_attachment_part', '`id` = ?', [$docId]);
    }

    /**
     * DataTables server-side handler for parts listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_Part_ID'],
            ['db' => 'Fld_Part_Nbr'],
            ['db' => 'Fld_Part_Desc'],
            ['db' => 'Fld_Part_MFG'],
            ['db' => 'Fld_AC_ID'],
            ['db' => 'Fld_Part_List_Price'],
            ['db' => 'status'],
            ['db' => 'alt_pn'],
            ['db' => 'ata_chapter'],
            ['db' => 'cage_code'],
        ];

        return $this->dataTable($request, $columns);
    }
}
