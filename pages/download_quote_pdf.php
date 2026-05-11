<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
    exit;
}

if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() {
        return false;
    }
}
require_once "fpdf.php";

$quoteId = (int)($_GET['ID'] ?? $_GET['id'] ?? 0);
if ($quoteId <= 0) {
    die("Missing quotation ID.");
}

function pdf_text($value) {
    $value = trim(strip_tags((string)($value ?? '')));
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $value);
    return $converted !== false ? $converted : $value;
}

function pdf_money($price, $currency) {
    $price = trim((string)($price ?? ''));
    $currency = trim((string)($currency ?? ''));
    if ($price === '') return '';
    if (is_numeric($price)) $price = number_format((float)$price, 2, '.', ',');
    return trim($price.' '.$currency);
}

$sql = "SELECT q.*,
        p.Fld_Part_Nbr,
        p.Fld_Part_Desc,
        r.Fld_Customer_ID,
        r.id_company_contact,
        cust.Fld_Company_Name AS customer_name,
        contact.Fld_Contact_Name,
        contact.Fld_Contact_Email,
        cond.Fld_Condition_Text,
        cur.Fld_Currency_Text,
        rel.Fld_Release_Text,
        sender.Employee_Name AS sender_name,
        sender.position AS sender_title,
        sender.tel AS sender_tel,
        sender.mobile AS sender_mobile,
        sender.email AS sender_email,
        sender.skype AS sender_skype
    FROM tbl_RFQ_3 q
    LEFT JOIN tbl_Parts p ON q.Fld_Part_Id = p.Fld_Part_ID
    LEFT JOIN tbl_RFQ_1 r ON q.id_tbl_rfq1 = r.ID
    LEFT JOIN tb_company cust ON r.Fld_Customer_ID = cust.Fld_Company_ID
    LEFT JOIN tb_company_contact contact ON r.id_company_contact = contact.id_company_contact
    LEFT JOIN tbl_Condition cond ON q.Fld_Condition = cond.Fld_Condition_ID
    LEFT JOIN tbl_Currency cur ON q.Fld_Currency_ID = cur.Fld_Currency_ID
    LEFT JOIN tbl_Release rel ON q.Fld_Release_ID = rel.Fld_Release_ID
    LEFT JOIN tbl_Employee sender ON COALESCE(q.sender_user_id, r.Employee_ID) = sender.Employee_ID
    WHERE q.ID='".$quoteId."'
    LIMIT 1";
$req = mysql2_query($sql);
$quote = $req ? mysqli_fetch_assoc($req) : null;
if (!$quote) {
    die("Quotation not found.");
}

if (empty($quote['customer_name']) && !empty($quote['Fld_RFQ_ID'])) {
    $fallback = mysql2_query("SELECT cust.Fld_Company_Name AS customer_name, contact.Fld_Contact_Name, contact.Fld_Contact_Email
        FROM tbl_RFQ_1 r
        LEFT JOIN tb_company cust ON r.Fld_Customer_ID = cust.Fld_Company_ID
        LEFT JOIN tb_company_contact contact ON r.id_company_contact = contact.id_company_contact
        WHERE r.Fld_RFQ_ID='".mysqli_real_escape_string($conn, $quote['Fld_RFQ_ID'])."'
        ORDER BY r.ID DESC
        LIMIT 1");
    $fallbackRow = $fallback ? mysqli_fetch_assoc($fallback) : null;
    if ($fallbackRow) {
        if (empty($quote['customer_name'])) $quote['customer_name'] = $fallbackRow['customer_name'];
        if (empty($quote['Fld_Contact_Name'])) $quote['Fld_Contact_Name'] = $fallbackRow['Fld_Contact_Name'];
        if (empty($quote['Fld_Contact_Email'])) $quote['Fld_Contact_Email'] = $fallbackRow['Fld_Contact_Email'];
    }
}

class QuotePDF extends FPDF {
    function __construct($orientation='P', $unit='mm', $size='A4') {
        parent::__construct($orientation, $unit, $size);
        $this->loadCoreFonts();
    }

    function loadCoreFonts() {
        $cw = array();
        for ($i = 0; $i <= 255; $i++) {
            $cw[chr($i)] = 600;
        }
        $cw[' '] = 278;
        $this->fonts['helvetica'] = array('type'=>'Core', 'name'=>'Helvetica', 'up'=>-100, 'ut'=>50, 'cw'=>$cw, 'subsetted'=>false, 'i'=>1);
        $this->fonts['helveticaB'] = array('type'=>'Core', 'name'=>'Helvetica-Bold', 'up'=>-100, 'ut'=>50, 'cw'=>$cw, 'subsetted'=>false, 'i'=>2);
        $this->fonts['helveticaI'] = array('type'=>'Core', 'name'=>'Helvetica-Oblique', 'up'=>-100, 'ut'=>50, 'cw'=>$cw, 'subsetted'=>false, 'i'=>3);
    }

    function row($label, $value, $height = 7) {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(42, $height, pdf_text($label), 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(0, $height, pdf_text($value), 1, 'L');
    }

    function lineItemHeader() {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(24, 8, 'PN', 1, 0, 'C', true);
        $this->Cell(58, 8, 'Description', 1, 0, 'C', true);
        $this->Cell(14, 8, 'Qty', 1, 0, 'C', true);
        $this->Cell(24, 8, 'Condition', 1, 0, 'C', true);
        $this->Cell(32, 8, 'Price', 1, 0, 'C', true);
        $this->Cell(38, 8, 'Certification', 1, 1, 'C', true);
    }
}

$pdf = new QuotePDF('P', 'mm', 'A4');
$pdf->SetCompression(false);
$pdf->SetTitle('Quote '.$quote['ID']);
$pdf->AddPage();
$pdf->SetMargins(12, 12, 12);

$logo = __DIR__.'/images/logo-aei-email.png';
if (file_exists($logo)) {
    $pdf->Image($logo, 12, 10, 22);
}

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, 'AeroCanada Industries 770 Inc.', 0, 1, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, '99, Prince Street, 7th Floor, Suite #701', 0, 1, 'R');
$pdf->Cell(0, 5, 'Montreal QC H3C 2M7, Canada', 0, 1, 'R');
$pdf->Cell(0, 5, 'Tel. +1 514 800 6223 | Fax. +1 514 800 6224', 0, 1, 'R');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 9, 'CUSTOMER QUOTATION', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 7, pdf_text('Quote ID: '.$quote['ID']), 0, 0, 'L');
$pdf->Cell(95, 7, pdf_text('RFQ ID: '.$quote['Fld_RFQ_ID']), 0, 1, 'R');
$pdf->Cell(95, 7, pdf_text('Quote Date: '.$quote['Fld_Quote_Date']), 0, 0, 'L');
$pdf->Cell(95, 7, pdf_text('PDF Date: '.date('Y-m-d')), 0, 1, 'R');
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'Customer', 0, 1, 'L');
$pdf->row('Company', $quote['customer_name']);
$pdf->row('Contact', trim(($quote['Fld_Contact_Name'] ?? '').' <'.($quote['Fld_Contact_Email'] ?? '').'>'));
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'Quoted Item', 0, 1, 'L');
$pdf->lineItemHeader();
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(24, 10, pdf_text($quote['Fld_Part_Nbr']), 1, 0, 'L');
$pdf->Cell(58, 10, pdf_text($quote['Fld_Part_Desc']), 1, 0, 'L');
$pdf->Cell(14, 10, pdf_text($quote['Fld_Qty']), 1, 0, 'C');
$pdf->Cell(24, 10, pdf_text($quote['Fld_Condition_Text']), 1, 0, 'C');
$pdf->Cell(32, 10, pdf_text(pdf_money($quote['Fld_Price'], $quote['Fld_Currency_Text'])), 1, 0, 'R');
$pdf->Cell(38, 10, pdf_text($quote['Fld_Release_Text']), 1, 1, 'L');
$pdf->Ln(5);

$pdf->row('Delivery', $quote['lead_time']);
$pdf->row('Remarks / Comments', $quote['Fld_Remark'], 8);
$pdf->Ln(8);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'Sender', 0, 1, 'L');
$pdf->row('Name', $quote['sender_name']);
$pdf->row('Title', $quote['sender_title']);
$pdf->row('Phone', trim(($quote['sender_tel'] ?? '').' / '.($quote['sender_mobile'] ?? ''), ' /'));
$pdf->row('Email', $quote['sender_email']);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'I', 8);
$pdf->MultiCell(0, 5, pdf_text('Prices and availability are subject to change. This PDF is generated from the saved customer quotation and does not send email or create a duplicate quotation.'));

$filename = 'ACI770-Quote-'.$quote['ID'].'-'.$quote['Fld_Part_Nbr'].'.pdf';
$pdfData = $pdf->Output('S');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.strlen($pdfData));
echo $pdfData;
exit;
?>
