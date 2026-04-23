<?php
/**
 * AeroCanada ERP v2 - Quotations Module
 * Read-focused module for customer quotations (tbl_RFQ_3 + tbl_RFQ_1 joins).
 * Provides date/customer filtering and email dispatch.
 */

namespace AeroCanada\Modules\Quotations;

use AeroCanada\Core\Module;
use AeroCanada\Core\Database;

class Quotations extends Module
{
    protected string $table      = 'tbl_RFQ_3';
    protected string $primaryKey = 'Fld_RFQ3_ID';

    /**
     * Get quotations within a date range.
     */
    public function getQuotationsByDate(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT q.*, li.Fld_Part_ID, li.Fld_Qty, li.Fld_Condition_ID,
                    p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    r.Fld_RFQ_Nbr, r.Fld_Company_ID,
                    co.Fld_Company_Name
             FROM `tbl_RFQ_3` q
             LEFT JOIN `tbl_RFQ_1` li ON li.Fld_RFQ1_ID = q.Fld_RFQ1_ID
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = li.Fld_Part_ID
             LEFT JOIN `tbl_RFQ` r ON r.Fld_RFQ_ID = q.Fld_RFQ_ID
             LEFT JOIN `tb_company` co ON co.Fld_Company_ID = r.Fld_Company_ID
             WHERE q.`Fld_Quote_Date` BETWEEN ? AND ?
             ORDER BY q.`Fld_Quote_Date` DESC",
            [$from, $to]
        );
    }

    /**
     * Get quotations for a specific customer/company.
     */
    public function getQuotationsByCustomer(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT q.*, li.Fld_Part_ID, li.Fld_Qty, li.Fld_Condition_ID,
                    p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    r.Fld_RFQ_Nbr
             FROM `tbl_RFQ_3` q
             LEFT JOIN `tbl_RFQ_1` li ON li.Fld_RFQ1_ID = q.Fld_RFQ1_ID
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = li.Fld_Part_ID
             LEFT JOIN `tbl_RFQ` r ON r.Fld_RFQ_ID = q.Fld_RFQ_ID
             WHERE r.`Fld_Company_ID` = ?
             ORDER BY q.`Fld_Quote_Date` DESC",
            [$companyId]
        );
    }

    /**
     * Send a quotation by email.
     * Builds a summary from the quote data and dispatches via PHP mail.
     */
    public function sendQuotationEmail(int $quoteId, string $recipientEmail): bool
    {
        $quote = $this->db->fetch(
            "SELECT q.*, li.Fld_Qty, li.Fld_Condition_ID,
                    p.Fld_Part_Nbr, p.Fld_Part_Desc,
                    r.Fld_RFQ_Nbr, r.Fld_Company_ID,
                    co.Fld_Company_Name,
                    cond.Fld_Condition AS condition_name
             FROM `tbl_RFQ_3` q
             LEFT JOIN `tbl_RFQ_1` li ON li.Fld_RFQ1_ID = q.Fld_RFQ1_ID
             LEFT JOIN `tbl_Parts` p ON p.Fld_Part_ID = li.Fld_Part_ID
             LEFT JOIN `tbl_RFQ` r ON r.Fld_RFQ_ID = q.Fld_RFQ_ID
             LEFT JOIN `tb_company` co ON co.Fld_Company_ID = r.Fld_Company_ID
             LEFT JOIN `tbl_Condition` cond ON cond.Fld_Condition_ID = li.Fld_Condition_ID
             WHERE q.`Fld_RFQ3_ID` = ?",
            [$quoteId]
        );

        if (!$quote) {
            throw new \RuntimeException('Quotation not found.');
        }

        $subject = sprintf(
            'AeroCanada Quotation %s - %s',
            $quote['Fld_Quote_Nbr'] ?? $quote['Fld_RFQ3_ID'],
            $quote['Fld_Part_Nbr'] ?? 'N/A'
        );

        $body = sprintf(
            "Dear Customer,\n\n"
            . "Please find below our quotation:\n\n"
            . "Quote #: %s\n"
            . "RFQ #: %s\n"
            . "Part: %s - %s\n"
            . "Condition: %s\n"
            . "Quantity: %s\n"
            . "Price: %s %s\n"
            . "Lead Time: %s\n"
            . "Validity: %s\n\n"
            . "Remarks: %s\n\n"
            . "Best regards,\n"
            . "AeroCanada Industries",
            $quote['Fld_Quote_Nbr'] ?? '',
            $quote['Fld_RFQ_Nbr'] ?? '',
            $quote['Fld_Part_Nbr'] ?? '',
            $quote['Fld_Part_Desc'] ?? '',
            $quote['condition_name'] ?? '',
            $quote['Fld_Qty'] ?? '',
            $quote['Fld_Quote_Price'] ?? '',
            $quote['Fld_Quote_Currency_ID'] ?? '',
            $quote['Fld_Quote_Lead_Time'] ?? '',
            $quote['Fld_Quote_Validity'] ?? '',
            $quote['Fld_Quote_Remark'] ?? ''
        );

        $headers = [
            'From: quotes@aerocanada-industries.com',
            'Reply-To: quotes@aerocanada-industries.com',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $sent = mail($recipientEmail, $subject, $body, implode("\r\n", $headers));

        if ($sent) {
            $this->db->update(
                'tbl_RFQ_3',
                ['Fld_Quote_Status' => 'sent'],
                '`Fld_RFQ3_ID` = ?',
                [$quoteId]
            );
        }

        return $sent;
    }

    /**
     * DataTables server-side handler for quotations listing.
     */
    public function dataTableHandler(array $request): array
    {
        $columns = [
            ['db' => 'Fld_RFQ3_ID'],
            ['db' => 'Fld_RFQ_ID'],
            ['db' => 'Fld_Quote_Nbr'],
            ['db' => 'Fld_Quote_Date'],
            ['db' => 'Fld_Quote_Price'],
            ['db' => 'Fld_Quote_Status'],
        ];

        return $this->dataTable($request, $columns);
    }
}
