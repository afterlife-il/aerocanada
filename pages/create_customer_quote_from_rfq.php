<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
    exit;
}

function rfq_quote_value($key, $default = '') {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function rfq_quote_company_id($value) {
    $value = trim((string)$value);
    if ($value === '') return '';
    $parts = explode(',', $value);
    return preg_replace('/[^0-9]/', '', $parts[0]);
}

function rfq_quote_int($key) {
    return (int)rfq_quote_value($key, '0');
}

function rfq_quote_sql($value) {
    global $db_link;
    return mysqli_real_escape_string($db_link, (string)$value);
}

$rfqId = rfq_quote_value('Fld_RFQ_ID');
$lineId = rfq_quote_int('idrfq1');
$partId = rfq_quote_int('part_id');

if ($rfqId === '' || $lineId <= 0 || $partId <= 0) {
    echo "<div class=\"alert alert-danger\">Missing RFQ line context. Please return to the RFQ and select a source again.</div>";
    exit;
}

$traceabilityId = rfq_quote_company_id(rfq_quote_value('Fld_Traceability_ID'));
$tagInfoId = rfq_quote_company_id(rfq_quote_value('Fld_Tag_Info_ID'));
$quoteDate = date("d-m-y");
$sourceType = rfq_quote_value('selected_source_type');
$sourceId = rfq_quote_int('selected_source_id');

$sql = "INSERT INTO tbl_RFQ_3 (
        Fld_RFQ_ID,
        Fld_Quote_Date,
        Fld_Part_Id,
        Fld_Part_SN,
        Fld_Qty,
        Fld_Condition,
        Fld_Price,
        Fld_Price_Min,
        Fld_Price_Max,
        Fld_Currency_ID,
        Fld_Remark,
        Fld_Supply_Date,
        Fld_Traceability_ID,
        Fld_Tag_Info_ID,
        Fld_Tag_Date,
        Fld_Release_ID,
        Fld_Linked_ID,
        Fld_Exch_Core_Value,
        Fld_Exch_Core_Value_Currency_ID,
        Fld_Exch_Cond,
        Fld_IsBeen_Chosen,
        Fld_Send_Mail,
        Fld_Exch_Core_RCVD,
        moq,
        lead_time,
        Fld_Priority_ID,
        id_tbl_rfq1,
        source_type,
        source_id
    ) VALUES (
        '".rfq_quote_sql($rfqId)."',
        '".rfq_quote_sql($quoteDate)."',
        '".$partId."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Part_SN'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Qty'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Condition_ID'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Price'))."',
        '',
        '',
        '".rfq_quote_sql(rfq_quote_value('FldCurrencyID'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Remark'))."',
        '',
        '".rfq_quote_sql($traceabilityId)."',
        '".rfq_quote_sql($tagInfoId)."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Tag_Date'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Release_ID'))."',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '".rfq_quote_sql(rfq_quote_value('moq'))."',
        '".rfq_quote_sql(rfq_quote_value('lead_time'))."',
        '".rfq_quote_sql(rfq_quote_value('Fld_Priority_ID'))."',
        '".$lineId."',
        '".rfq_quote_sql($sourceType)."',
        '".$sourceId."'
    )";

if (!mysql2_query($sql)) {
    echo "<div class=\"alert alert-danger\">Unable to create customer quotation: ".htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8')."</div>";
    exit;
}

$quoteId = mysql2_insert_id();
echo "<meta http-equiv=\"refresh\" content=\"0;URL=modif_quotations.php?ID=".$quoteId."&mode=clean\">";
exit;
?>
