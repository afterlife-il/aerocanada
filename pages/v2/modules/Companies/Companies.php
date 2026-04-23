<?php
/**
 * AeroCanada ERP v2 - Companies Module
 * Manages companies, addresses, contacts, fleet, bank accounts,
 * forwarders, competitors, and document attachments.
 */

namespace AeroCanada\Modules\Companies;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;
use AeroCanada\Core\FileUpload;

class Companies extends Module
{
    protected string $table      = 'tb_company';
    protected string $primaryKey = 'Fld_Company_ID';

    // ── Company CRUD ────────────────────────────────────────────────

    /**
     * Add a new company.
     */
    public function addCompany(array $data): int
    {
        $allowed = [
            'Fld_Company_Name', 'Fld_Company_Type_ID', 'Fld_Company_Web',
            'Fld_Company_Email', 'Fld_Company_Phone', 'Fld_Company_Fax',
            'Fld_Company_Remark', 'status', 'Fld_Add_Company_Date',
            'aci_contact_entry',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['Fld_Add_Company_Date'])) {
            $filtered['Fld_Add_Company_Date'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $filtered);
    }

    /**
     * Update an existing company.
     */
    public function updateCompany(int $id, array $data): int
    {
        $allowed = [
            'Fld_Company_Name', 'Fld_Company_Type_ID', 'Fld_Company_Web',
            'Fld_Company_Email', 'Fld_Company_Phone', 'Fld_Company_Fax',
            'Fld_Company_Remark', 'status', 'aci_contact_entry',
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
     * Soft-archive a company.
     */
    public function archiveCompany(int $id): int
    {
        return $this->db->update(
            $this->table,
            ['status' => 'archive'],
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Get full company detail (company row + addresses + contacts).
     */
    public function getCompanyDetail(int $id): ?array
    {
        $company = $this->find($id);
        if (!$company) {
            return null;
        }
        $company['addresses'] = $this->getAddresses($id);
        $company['contacts']  = $this->getContacts($id);
        return $company;
    }

    // ── Addresses ───────────────────────────────────────────────────

    /**
     * Get all addresses for a company.
     */
    public function getAddresses(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Company_Details` WHERE `Fld_Company_ID` = ? ORDER BY `Fld_Address_Type_ID` ASC",
            [$companyId]
        );
    }

    /**
     * Add an address to a company.
     */
    public function addAddress(int $companyId, array $data): int
    {
        $allowed = [
            'Fld_Address_Type_ID', 'Fld_Address_1', 'Fld_Address_2',
            'Fld_City', 'Fld_State', 'Fld_Zip', 'Fld_Country_ID',
            'Fld_Phone', 'Fld_Fax', 'Fld_Email',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_Company_ID'] = $companyId;

        return $this->db->insert('tbl_Company_Details', $filtered);
    }

    /**
     * Update a company address.
     */
    public function updateAddress(int $addressId, array $data): int
    {
        $allowed = [
            'Fld_Address_Type_ID', 'Fld_Address_1', 'Fld_Address_2',
            'Fld_City', 'Fld_State', 'Fld_Zip', 'Fld_Country_ID',
            'Fld_Phone', 'Fld_Fax', 'Fld_Email',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            'tbl_Company_Details',
            $filtered,
            '`Fld_Address_ID` = ?',
            [$addressId]
        );
    }

    /**
     * Delete a company address.
     */
    public function deleteAddress(int $addressId): int
    {
        return $this->db->delete('tbl_Company_Details', '`Fld_Address_ID` = ?', [$addressId]);
    }

    // ── Contacts ────────────────────────────────────────────────────

    /**
     * Get all contacts for a company.
     */
    public function getContacts(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tb_company_contact` WHERE `Fld_Company_ID` = ? AND (`status` IS NULL OR `status` != 'archive') ORDER BY `Fld_Contact_Last` ASC",
            [$companyId]
        );
    }

    /**
     * Add a contact to a company.
     */
    public function addContact(int $companyId, array $data): int
    {
        $allowed = [
            'Fld_Contact_First', 'Fld_Contact_Last', 'Fld_Contact_Title',
            'Fld_Contact_Email', 'Fld_Contact_Phone', 'Fld_Contact_Mobile',
            'Fld_Contact_Fax', 'Fld_Contact_Remark', 'status',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_Company_ID'] = $companyId;

        return $this->db->insert('tb_company_contact', $filtered);
    }

    /**
     * Update a contact.
     */
    public function updateContact(int $contactId, array $data): int
    {
        $allowed = [
            'Fld_Contact_First', 'Fld_Contact_Last', 'Fld_Contact_Title',
            'Fld_Contact_Email', 'Fld_Contact_Phone', 'Fld_Contact_Mobile',
            'Fld_Contact_Fax', 'Fld_Contact_Remark', 'status',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return 0;
        }

        return $this->db->update(
            'tb_company_contact',
            $filtered,
            '`Fld_Contact_ID` = ?',
            [$contactId]
        );
    }

    /**
     * Soft-archive a contact.
     */
    public function archiveContact(int $contactId): int
    {
        return $this->db->update(
            'tb_company_contact',
            ['status' => 'archive'],
            '`Fld_Contact_ID` = ?',
            [$contactId]
        );
    }

    // ── Fleet ───────────────────────────────────────────────────────

    /**
     * Get fleet records for a company.
     */
    public function getFleet(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Fleet` WHERE `Fld_Company_ID` = ? ORDER BY `Fld_Fleet_ID` ASC",
            [$companyId]
        );
    }

    /**
     * Add a fleet record.
     */
    public function addFleet(int $companyId, array $data): int
    {
        $allowed = [
            'Fld_AC_Type_ID', 'Fld_AC_Registration', 'Fld_AC_Serial',
            'Fld_AC_Remark', 'Fld_Fleet_Qty',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_Company_ID'] = $companyId;

        return $this->db->insert('tbl_Fleet', $filtered);
    }

    /**
     * Batch-update fleet records.
     */
    public function updateFleet(array $items): void
    {
        $allowed = [
            'Fld_AC_Type_ID', 'Fld_AC_Registration', 'Fld_AC_Serial',
            'Fld_AC_Remark', 'Fld_Fleet_Qty',
        ];

        $this->db->beginTransaction();
        try {
            foreach ($items as $item) {
                if (empty($item['Fld_Fleet_ID'])) {
                    continue;
                }
                $filtered = array_intersect_key($item, array_flip($allowed));
                if (!empty($filtered)) {
                    $this->db->update(
                        'tbl_Fleet',
                        $filtered,
                        '`Fld_Fleet_ID` = ?',
                        [$item['Fld_Fleet_ID']]
                    );
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Bank Accounts ───────────────────────────────────────────────

    /**
     * Get bank accounts for a company.
     */
    public function getBankAccounts(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Company_Bank_Account` WHERE `Fld_Company_ID` = ?",
            [$companyId]
        );
    }

    /**
     * Add a bank account.
     */
    public function addBankAccount(int $companyId, array $data): int
    {
        $allowed = [
            'bank_name', 'account_name', 'account_number',
            'routing_number', 'swift_code', 'currency_id', 'remarks',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_Company_ID'] = $companyId;

        return $this->db->insert('tbl_Company_Bank_Account', $filtered);
    }

    /**
     * Batch-update bank accounts.
     */
    public function updateBankAccount(array $items): void
    {
        $allowed = [
            'bank_name', 'account_name', 'account_number',
            'routing_number', 'swift_code', 'currency_id', 'remarks',
        ];

        $this->db->beginTransaction();
        try {
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    continue;
                }
                $filtered = array_intersect_key($item, array_flip($allowed));
                if (!empty($filtered)) {
                    $this->db->update(
                        'tbl_Company_Bank_Account',
                        $filtered,
                        '`id` = ?',
                        [$item['id']]
                    );
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Forwarders ──────────────────────────────────────────────────

    /**
     * Get forwarders for a company.
     */
    public function getForwarders(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Forwarder` WHERE `Fld_Company_ID` = ?",
            [$companyId]
        );
    }

    /**
     * Add a forwarder.
     */
    public function addForwarder(int $companyId, array $data): int
    {
        $allowed = [
            'forwarder_name', 'contact_name', 'phone', 'email',
            'account_number', 'remarks',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered['Fld_Company_ID'] = $companyId;

        return $this->db->insert('tbl_Forwarder', $filtered);
    }

    // ── Competitors ─────────────────────────────────────────────────

    /**
     * Get competitors linked to a company.
     */
    public function getCompetitors(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, comp.Fld_Company_Name AS competitor_name
             FROM `tbl_Competitor` c
             JOIN `tb_company` comp ON comp.Fld_Company_ID = c.competitor_company_id
             WHERE c.Fld_Company_ID = ?",
            [$companyId]
        );
    }

    /**
     * Add a competitor relation.
     */
    public function addCompetitor(int $companyId, int $competitorId): int
    {
        return $this->db->insert('tbl_Competitor', [
            'Fld_Company_ID'       => $companyId,
            'competitor_company_id' => $competitorId,
        ]);
    }

    // ── Documents ───────────────────────────────────────────────────

    /**
     * Attach a document to a company.
     */
    public function addDocument(int $companyId, string $name, array $file): int
    {
        $upload   = new FileUpload();
        $dir      = __DIR__ . '/../../uploads/companies/';
        $filename = $upload->upload('file', $dir);

        if ($filename === null) {
            throw new \RuntimeException('File upload failed.');
        }

        return $this->db->insert('tbl_docs_attachment_company', [
            'Fld_Company_ID' => $companyId,
            'doc_name'       => $name,
            'filename'       => $filename,
            'uploaded_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete a document attachment.
     */
    public function deleteDocument(int $docId): int
    {
        $doc = $this->db->fetch(
            "SELECT `filename` FROM `tbl_docs_attachment_company` WHERE `id` = ?",
            [$docId]
        );

        if ($doc) {
            $upload = new FileUpload();
            $upload->delete(__DIR__ . '/../../uploads/companies/', $doc['filename']);
        }

        return $this->db->delete('tbl_docs_attachment_company', '`id` = ?', [$docId]);
    }

    // ── Lookups ─────────────────────────────────────────────────────

    /**
     * Get all company types.
     */
    public function getCompanyTypes(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Company_Type` ORDER BY `Fld_Company_Type` ASC"
        );
    }

    /**
     * Get all address types.
     */
    public function getAddressTypes(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `tbl_Address_Type` ORDER BY `Fld_Address_Type` ASC"
        );
    }

    // ── DataTables ──────────────────────────────────────────────────

    /**
     * DataTables server-side handler for companies listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_Company_ID'],
            ['db' => 'Fld_Company_Name'],
            ['db' => 'Fld_Company_Type_ID'],
            ['db' => 'Fld_Company_Web'],
            ['db' => 'Fld_Company_Email'],
            ['db' => 'Fld_Company_Phone'],
            ['db' => 'status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
