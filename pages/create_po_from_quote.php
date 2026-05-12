<?php
session_start();
include_once "conf.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
    exit;
}

$quoteId = (int)($_GET['quote_id'] ?? $_GET['ID'] ?? 0);
if ($quoteId <= 0) {
    die("Missing quotation ID.");
}

function poq_escape($value) {
    global $conn;
    return mysqli_real_escape_string($conn, trim((string)($value ?? '')));
}

function poq_ensure_table() {
    $sql = "CREATE TABLE IF NOT EXISTS tbl_PO_Draft (
        id INT(11) NOT NULL AUTO_INCREMENT,
        quotation_id INT(11) NOT NULL,
        rfq_id VARCHAR(40) DEFAULT NULL,
        rfq_line_id INT(11) DEFAULT NULL,
        part_id INT(11) DEFAULT NULL,
        source_type VARCHAR(40) DEFAULT NULL,
        source_id INT(11) DEFAULT NULL,
        source_company_id INT(11) DEFAULT NULL,
        customer_company_id INT(11) DEFAULT NULL,
        customer_contact_id INT(11) DEFAULT NULL,
        qty VARCHAR(20) DEFAULT NULL,
        condition_id INT(11) DEFAULT NULL,
        price VARCHAR(30) DEFAULT NULL,
        currency_id INT(11) DEFAULT NULL,
        release_id INT(11) DEFAULT NULL,
        delivery VARCHAR(200) DEFAULT NULL,
        remarks TEXT,
        po_number VARCHAR(60) DEFAULT NULL,
        customer_po_number VARCHAR(100) DEFAULT NULL,
        customer_po_file VARCHAR(255) DEFAULT NULL,
        accepted_price VARCHAR(30) DEFAULT NULL,
        accepted_qty VARCHAR(20) DEFAULT NULL,
        accepted_condition_id INT(11) DEFAULT NULL,
        payment_terms VARCHAR(255) DEFAULT NULL,
        shipping_terms VARCHAR(255) DEFAULT NULL,
        shipping_address TEXT,
        shipping_address_id INT(11) DEFAULT NULL,
        required_documents TEXT,
        missing_information TEXT,
        acceptance_notes TEXT,
        status VARCHAR(30) DEFAULT 'DRAFT',
        created_by INT(11) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_po_draft_quote (quotation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysql2_query($sql);
    $columns = array(
        'customer_po_number' => "ALTER TABLE tbl_PO_Draft ADD COLUMN customer_po_number VARCHAR(100) DEFAULT NULL AFTER po_number",
        'customer_po_file' => "ALTER TABLE tbl_PO_Draft ADD COLUMN customer_po_file VARCHAR(255) DEFAULT NULL AFTER customer_po_number",
        'accepted_price' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_price VARCHAR(30) DEFAULT NULL AFTER customer_po_file",
        'accepted_qty' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_qty VARCHAR(20) DEFAULT NULL AFTER accepted_price",
        'accepted_condition_id' => "ALTER TABLE tbl_PO_Draft ADD COLUMN accepted_condition_id INT(11) DEFAULT NULL AFTER accepted_qty",
        'payment_terms' => "ALTER TABLE tbl_PO_Draft ADD COLUMN payment_terms VARCHAR(255) DEFAULT NULL AFTER accepted_condition_id",
        'shipping_terms' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_terms VARCHAR(255) DEFAULT NULL AFTER payment_terms",
        'shipping_address' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_address TEXT AFTER shipping_terms",
        'shipping_address_id' => "ALTER TABLE tbl_PO_Draft ADD COLUMN shipping_address_id INT(11) DEFAULT NULL AFTER shipping_address",
        'required_documents' => "ALTER TABLE tbl_PO_Draft ADD COLUMN required_documents TEXT AFTER shipping_address_id",
        'missing_information' => "ALTER TABLE tbl_PO_Draft ADD COLUMN missing_information TEXT AFTER required_documents",
        'acceptance_notes' => "ALTER TABLE tbl_PO_Draft ADD COLUMN acceptance_notes TEXT AFTER missing_information"
    );
    foreach ($columns as $column => $alterSql) {
        $exists = mysql2_query("SHOW COLUMNS FROM tbl_PO_Draft LIKE '".$column."'");
        if ($exists && mysqli_num_rows($exists) == 0) {
            mysql2_query($alterSql);
        }
    }
}

function poq_source_company($sourceType, $sourceId) {
    $sourceType = strtoupper((string)$sourceType);
    $sourceId = (int)$sourceId;
    if ($sourceId <= 0) return null;

    if (strpos($sourceType, 'SQ') !== false) {
        $res = mysql2_query("SELECT Fld_Supplier_ID AS company_id FROM tbl_RFQ_2 WHERE ID='".$sourceId."' LIMIT 1");
    } elseif (strpos($sourceType, 'EXTERNAL') !== false) {
        $res = mysql2_query("SELECT Fld_Company_ID AS company_id FROM tbl_Stock_external WHERE Fld_Stock_externe_ID='".$sourceId."' LIMIT 1");
    } else {
        $res = mysql2_query("SELECT COALESCE(Fld_Supplier_ID, Fld_Owner_ID) AS company_id FROM tbl_Stock WHERE Fld_Stock_ID='".$sourceId."' LIMIT 1");
    }
    $row = $res ? mysqli_fetch_assoc($res) : null;
    return $row && !empty($row['company_id']) ? (int)$row['company_id'] : null;
}

poq_ensure_table();

$existing = mysql2_query("SELECT id FROM tbl_PO_Draft WHERE quotation_id='".$quoteId."' LIMIT 1");
$existingRow = $existing ? mysqli_fetch_assoc($existing) : null;
if ($existingRow) {
    header("Location: modif_po_draft.php?id=".(int)$existingRow['id']);
    exit;
}

$quoteSql = "SELECT q.*, r.Fld_Customer_ID, r.id_company_contact
    FROM tbl_RFQ_3 q
    LEFT JOIN tbl_RFQ_1 r ON q.id_tbl_rfq1 = r.ID
    WHERE q.ID='".$quoteId."'
    LIMIT 1";
$quoteRes = mysql2_query($quoteSql);
$quote = $quoteRes ? mysqli_fetch_assoc($quoteRes) : null;
if (!$quote) {
    die("Quotation not found.");
}

if (empty($quote['Fld_Customer_ID']) && !empty($quote['Fld_RFQ_ID'])) {
    $fallback = mysql2_query("SELECT Fld_Customer_ID, id_company_contact FROM tbl_RFQ_1 WHERE Fld_RFQ_ID='".poq_escape($quote['Fld_RFQ_ID'])."' ORDER BY ID DESC LIMIT 1");
    $fallbackRow = $fallback ? mysqli_fetch_assoc($fallback) : null;
    if ($fallbackRow) {
        $quote['Fld_Customer_ID'] = $fallbackRow['Fld_Customer_ID'];
        $quote['id_company_contact'] = $fallbackRow['id_company_contact'];
    }
}

$sourceCompanyId = poq_source_company($quote['source_type'] ?? '', $quote['source_id'] ?? 0);
$createdBy = (int)($_SESSION['id_utilisateur'] ?? 0);
$poNumber = 'DRAFT-Q'.$quoteId;

$insert = "INSERT INTO tbl_PO_Draft (
        quotation_id, rfq_id, rfq_line_id, part_id, source_type, source_id, source_company_id,
        customer_company_id, customer_contact_id, qty, condition_id, price, currency_id, release_id,
        delivery, remarks, po_number, accepted_price, accepted_qty, accepted_condition_id, status, created_by
    ) VALUES (
        '".$quoteId."',
        '".poq_escape($quote['Fld_RFQ_ID'])."',
        '".(int)$quote['id_tbl_rfq1']."',
        '".(int)$quote['Fld_Part_Id']."',
        '".poq_escape($quote['source_type'] ?? '')."',
        '".(int)($quote['source_id'] ?? 0)."',
        ".($sourceCompanyId ? "'".$sourceCompanyId."'" : "NULL").",
        '".(int)($quote['Fld_Customer_ID'] ?? 0)."',
        '".(int)($quote['id_company_contact'] ?? 0)."',
        '".poq_escape($quote['Fld_Qty'])."',
        '".(int)$quote['Fld_Condition']."',
        '".poq_escape($quote['Fld_Price'])."',
        '".(int)$quote['Fld_Currency_ID']."',
        '".(int)$quote['Fld_Release_ID']."',
        '".poq_escape($quote['lead_time'])."',
        '".poq_escape($quote['Fld_Remark'])."',
        '".poq_escape($poNumber)."',
        '".poq_escape($quote['Fld_Price'])."',
        '".poq_escape($quote['Fld_Qty'])."',
        '".(int)$quote['Fld_Condition']."',
        'DRAFT',
        ".($createdBy > 0 ? "'".$createdBy."'" : "NULL")."
    )";

if (!mysql2_query($insert)) {
    die("Unable to create PO draft.");
}

$draftId = mysqli_insert_id($conn);
header("Location: modif_po_draft.php?id=".(int)$draftId);
exit;
?>
