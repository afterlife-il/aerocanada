<?php
declare(strict_types=1);

function erp_quote_document_data(PDO $db, array $args): array
{
    $id = $args['quote_id'] ?? null;
    if (count($args) !== 1 || !is_int($id) || $id < 1 || $id > 2147483647) {
        throw new InvalidArgumentException('Identifiant de cotation invalide');
    }
    $query = $db->prepare('SELECT q.ID AS quote_id, q.Fld_RFQ_ID AS rfq, q.Fld_Quote_Date AS quote_date,
        q.Fld_Qty AS quantity, q.Fld_Price AS price, q.lead_time, q.Fld_Remark AS remarks,
        p.Fld_Part_Nbr AS part_number, p.Fld_Part_Desc AS description,
        c.Fld_Company_Name AS customer, ct.Fld_Contact_Name AS contact,
        ct.Fld_Contact_Email AS recipient, cond.Fld_Condition_Text AS part_condition,
        cur.Fld_Currency_Text AS currency, rel.Fld_Release_Text AS certification,
        sender.Employee_Name AS sender, sender.email AS sender_email
        FROM tbl_RFQ_3 q
        JOIN tbl_RFQ_1 r ON r.ID = q.id_tbl_rfq1 AND r.Fld_RFQ_ID = q.Fld_RFQ_ID
        JOIN tb_company c ON c.Fld_Company_ID = r.Fld_Customer_ID
        LEFT JOIN tb_company_contact ct ON ct.id_company_contact = r.id_company_contact
            AND ct.Fld_Company_ID = r.Fld_Customer_ID AND ct.status = \'Available\'
        LEFT JOIN tbl_Parts p ON p.Fld_Part_ID = q.Fld_Part_Id
        LEFT JOIN tbl_Condition cond ON cond.Fld_Condition_ID = q.Fld_Condition
        LEFT JOIN tbl_Currency cur ON cur.Fld_Currency_ID = q.Fld_Currency_ID
        LEFT JOIN tbl_Release rel ON rel.Fld_Release_ID = q.Fld_Release_ID
        LEFT JOIN tbl_Employee sender ON sender.Employee_ID = COALESCE(q.sender_user_id, r.Employee_ID)
        WHERE q.ID = ?');
    $query->execute([$id]);
    $row = $query->fetch();
    if (!$row) {
        throw new OutOfBoundsException('Cotation client ou ligne RFQ associée introuvable');
    }
    return $row;
}

function erp_quote_pdf_text($value): string
{
    $text = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
    $text = function_exists('mb_strcut') ? mb_strcut($text, 0, 2000, 'UTF-8') : substr($text, 0, 2000);
    $converted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
    return $converted === false ? '' : $converted;
}

function erp_quote_render_pdf(array $quote): array
{
    $pages = getenv('AEROCANADA_YOYAMIC_PAGES');
    if (!$pages || $pages[0] !== '/' || !is_readable($pages . '/fpdf.php')) {
        throw new RuntimeException('Yoyamic PDF library unavailable');
    }
    if (!function_exists('get_magic_quotes_runtime')) {
        function get_magic_quotes_runtime() { return false; }
    }
    require_once $pages . '/fpdf.php';

    $pdf = new class extends FPDF {
        public function loadCoreFonts(): void
        {
            $width = array_fill_keys(array_map('chr', range(0, 255)), 600);
            $width[' '] = 278;
            $this->fonts['helvetica'] = [
                'type' => 'Core', 'name' => 'Helvetica', 'up' => -100, 'ut' => 50,
                'cw' => $width, 'subsetted' => false, 'i' => 1
            ];
        }
    };
    $pdf->loadCoreFonts();
    $pdf->SetTitle('Quote ' . (int) $quote['quote_id']);
    $pdf->SetMargins(12, 12, 12);
    $pdf->AddPage();
    $logo = $pages . '/images/logo-aei-email.png';
    if (is_readable($logo)) {
        $pdf->Image($logo, 12, 10, 22);
    }
    $pdf->SetFont('Arial', '', 15);
    $pdf->Cell(0, 10, 'AeroCanada Industries 770 Inc.', 0, 1, 'R');
    $pdf->Ln(8);
    $pdf->Cell(0, 10, 'CUSTOMER QUOTATION', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $fields = [
        'Quote' => $quote['quote_id'], 'RFQ' => $quote['rfq'], 'Date' => $quote['quote_date'],
        'Customer' => $quote['customer'], 'Contact' => $quote['contact'], 'Part' => $quote['part_number'],
        'Description' => $quote['description'], 'Quantity' => $quote['quantity'], 'Condition' => $quote['part_condition'],
        'Price' => trim(($quote['price'] ?? '') . ' ' . ($quote['currency'] ?? '')),
        'Certification' => $quote['certification'], 'Delivery' => $quote['lead_time'],
        'Remarks' => $quote['remarks'], 'Sender' => $quote['sender'], 'Sender email' => $quote['sender_email'],
    ];
    foreach ($fields as $name => $value) {
        $pdf->MultiCell(0, 7, $name . ': ' . erp_quote_pdf_text($value), 1);
    }
    $data = $pdf->Output('S');
    if (!is_string($data) || !str_starts_with($data, '%PDF-') || strlen($data) > 262144) {
        throw new RuntimeException('Invalid or oversized quotation PDF');
    }
    return ['filename' => 'ACI770-Quote-' . (int) $quote['quote_id'] . '.pdf',
        'mime_type' => 'application/pdf', 'data_base64' => base64_encode($data)];
}

function erp_document_read_chunk(string $root, string $filename, int $offset): array
{
    if ($filename === '' || $filename === '.' || $filename === '..'
        || str_contains($filename, '/') || str_contains($filename, chr(92))
        || preg_match('/[[:cntrl:]]/', $filename) || $offset < 0 || $offset > 10000000) {
        throw new InvalidArgumentException('Nom de fichier ou position invalide');
    }
    $parent = realpath($root);
    $candidate = $parent === false ? '' : $parent . DIRECTORY_SEPARATOR . $filename;
    $real = $candidate === '' ? false : realpath($candidate);
    if ($real === false || dirname($real) !== $parent || is_link($candidate) || !is_file($real)) {
        throw new OutOfBoundsException('Pièce jointe introuvable');
    }
    $handle = fopen($real, 'rb');
    if ($handle === false) {
        throw new OutOfBoundsException('Pièce jointe inaccessible');
    }
    try {
        $stat = fstat($handle);
        $expected = lstat($real);
        if (!$stat || !$expected || ($stat['mode'] & 0170000) !== 0100000
            || $stat['ino'] !== $expected['ino'] || $stat['dev'] !== $expected['dev']
            || $stat['size'] > 10000000 || $offset > $stat['size'] || fseek($handle, $offset) !== 0) {
            throw new DomainException('Fichier ou position indisponible');
        }
        $data = fread($handle, 196608);
        if (!is_string($data)) {
            throw new RuntimeException('Unable to read ERP attachment');
        }
        $next = $offset + strlen($data);
        return [
            'filename' => $filename, 'mime_type' => 'application/octet-stream', 'size_bytes' => $stat['size'],
            'offset' => $offset, 'next_offset' => $next < $stat['size'] ? $next : null, 'data_base64' => base64_encode($data),
        ];
    } finally {
        fclose($handle);
    }
}

function erp_download_document(PDO $db, array $args): array
{
    $kind = $args['kind'] ?? null;
    $parentId = $args['parent_id'] ?? null;
    $documentId = $args['document_id'] ?? null;
    $offset = $args['offset'] ?? 0;
    if (!is_int($parentId) || $parentId < 1 || !is_int($documentId) || $documentId < 1 || !is_int($offset)) {
        throw new InvalidArgumentException('Identifiants ou position invalides');
    }
    $sources = [
        'part' => ['docsattachment', 'SELECT docs_name FROM tbl_docs_attachment_pn WHERE id_docs_attachment_pn = ? AND pn_id = ?'],
        'company' => ['docsattachmentcompany', 'SELECT docs_name FROM tbl_docs_attachment_company WHERE id_docs_attachment_company = ? AND id_company = ?'],
    ];
    if (!is_string($kind) || !isset($sources[$kind])) {
        throw new InvalidArgumentException('Type de pièce jointe invalide');
    }
    [$directory, $sql] = $sources[$kind];
    $stmt = $db->prepare($sql);
    $stmt->execute([$documentId, $parentId]);
    $filename = $stmt->fetchColumn();
    if (!is_string($filename)) {
        throw new OutOfBoundsException('Pièce jointe introuvable pour cet objet');
    }
    $pages = getenv('AEROCANADA_YOYAMIC_PAGES');
    if (!$pages || $pages[0] !== '/') {
        throw new RuntimeException('Yoyamic attachment root unavailable');
    }
    return erp_document_read_chunk(dirname($pages) . '/' . $directory, $filename, $offset);
}

$documentSchema = [
    'type' => 'object', 'properties' => ['quote_id' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647]],
    'required' => ['quote_id'], 'additionalProperties' => false,
];
$documentRoles = ['Sales', 'Manager', 'Admin', 'SuperAdmin'];

return [
    'download_yoyamic_document' => [
        'scope' => 'aerocanada.documents.read', 'roles' => $documentRoles,
        'description' => 'Télécharge par blocs une pièce jointe liée à une compagnie ou à une pièce.',
        'inputSchema' => [
            'type' => 'object',
            'properties' => [
                'kind' => ['type' => 'string', 'enum' => ['part', 'company']],
                'parent_id' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647],
                'document_id' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647],
                'offset' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10000000],
            ],
            'required' => ['kind', 'parent_id', 'document_id'],
        ],
        'handler' => static fn (PDO $db, array $args, int $employeeId): array => erp_download_document($db, $args),
    ],
    'list_company_documents' => [
        'scope' => 'aerocanada.documents.read', 'roles' => $documentRoles,
        'description' => 'Liste les pièces jointes d’une compagnie.',
        'inputSchema' => ['type' => 'object', 'properties' => [
            'company_id' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647],
            'after_id' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647],
        ], 'required' => ['company_id']],
        'handler' => static function (PDO $db, array $args, int $employeeId): array {
            $companyId = $args['company_id'] ?? 0;
            $after = $args['after_id'] ?? 0;
            if (!is_int($companyId) || $companyId < 1 || !is_int($after) || $after < 0) {
                throw new InvalidArgumentException('Identifiants invalides');
            }
            $stmt = $db->prepare('SELECT id_docs_attachment_company AS document_id, name AS title
                FROM tbl_docs_attachment_company WHERE id_company = ? AND id_docs_attachment_company > ?
                ORDER BY id_docs_attachment_company LIMIT 50');
            $stmt->execute([$companyId, $after]);
            return ['items' => $stmt->fetchAll()];
        },
    ],
    'get_customer_quote_pdf' => [
        'scope' => 'aerocanada.quotations.read', 'roles' => $documentRoles,
        'description' => 'Génère le PDF d’une cotation client enregistrée.',
        'inputSchema' => $documentSchema,
        'handler' => static fn (PDO $db, array $args, int $employeeId): array =>
            erp_quote_render_pdf(erp_quote_document_data($db, $args)),
    ],
    'prepare_quote_email_draft' => [
        'scope' => 'aerocanada.emails.read', 'roles' => $documentRoles,
        'description' => 'Prépare le destinataire, le texte et la pièce jointe PDF d’une cotation, pour revue.',
        'inputSchema' => $documentSchema,
        'handler' => static function (PDO $db, array $args, int $employeeId): array {
            $quote = erp_quote_document_data($db, $args);
            if (!filter_var($quote['recipient'] ?? null, FILTER_VALIDATE_EMAIL)) {
                throw new DomainException('Adresse du contact actif indisponible');
            }
            return [
                'to' => $quote['recipient'],
                'subject' => 'AeroCanada quotation ' . (int) $quote['quote_id'],
                'body' => 'Please find the AeroCanada quotation attached.',
                'attachment' => erp_quote_render_pdf($quote),
                'persisted' => false,
            ];
        },
    ],
];
