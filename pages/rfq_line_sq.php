<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") { exit; }

$rfqId = isset($_GET['rfq_id']) ? trim($_GET['rfq_id']) : '';
$lineId = (int)($_GET['line_id'] ?? 0);
$partId = (int)($_GET['part_id'] ?? 0);

if ($rfqId === '' && $partId === 0) {
    echo "<p class='text-muted'>No RFQ/Part specified.</p>";
    exit;
}

$where = "WHERE 1=1";
if ($rfqId !== '') $where .= " AND r2.Fld_RFQ_ID='".addslashes($rfqId)."'";
if ($lineId > 0) $where .= " AND (r2.id_tbl_rfq1='".$lineId."' OR r2.id_tbl_rfq1 IS NULL)";
if ($partId > 0) $where .= " AND r2.Fld_Part_ID='".$partId."'";
$where .= " AND r2.Fld_Supplier_ID IS NOT NULL AND r2.Fld_Supplier_ID <> '' AND r2.Fld_Supplier_ID <> '0'";

$sql = "SELECT r2.*,
        s.Fld_Company_Name AS supplier_name,
        cc.Fld_Contact_Name AS contact_name,
        cond.Fld_Condition_Text,
        cur.Fld_Currency_Text,
        rel.Fld_Release_Text
    FROM tbl_RFQ_2 r2
    LEFT JOIN tb_company s ON r2.Fld_Supplier_ID = s.Fld_Company_ID
    LEFT JOIN tb_company_contact cc ON r2.Fld_Supplier_Contact_ID = cc.id_company_contact
    LEFT JOIN tbl_Condition cond ON r2.Fld_Condition_ID = cond.Fld_Condition_ID
    LEFT JOIN tbl_Currency cur ON r2.Fld_Currency_ID = cur.Fld_Currency_ID
    LEFT JOIN tbl_Release rel ON r2.Fld_Release_ID = rel.Fld_Release_ID
    $where
    ORDER BY r2.ID DESC";

$req = mysql2_query($sql);
$count = $req ? mysqli_num_rows($req) : 0;

if ($count == 0) {
    echo "<div class='alert alert-info' style='margin:8px 0'>No supplier quotes found for this part/RFQ.</div>";
    exit;
}

echo "<table class='table table-condensed table-bordered' style='margin:8px 0; font-size:12px; background:#f9f9f9'>";
echo "<thead style='background:#eee'><tr>";
echo "<th>Supplier</th><th>Contact</th><th>Qty</th><th>Condition</th><th>Price</th><th>Currency</th><th>Lead Time</th><th>Release</th><th>Date</th><th>Action</th>";
echo "</tr></thead><tbody>";

while ($r = mysqli_fetch_assoc($req)) {
    $price = ($r['Fld_Price'] !== null && $r['Fld_Price'] !== '') ? number_format((float)$r['Fld_Price'], 2, '.', ',') : '';
    echo "<tr>";
    echo "<td>".htmlspecialchars($r['supplier_name'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['contact_name'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['Fld_Qty'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['Fld_Condition_Text'] ?? '')."</td>";
    echo "<td><strong>".$price."</strong></td>";
    echo "<td>".htmlspecialchars($r['Fld_Currency_Text'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['lead_time'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['Fld_Release_Text'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['Fld_Current_Date'] ?? '')."</td>";
    $supplierAttr = htmlspecialchars($r['supplier_name'] ?? '', ENT_QUOTES);
    $priceAttr = htmlspecialchars($r['Fld_Price'] ?? '', ENT_QUOTES);
    $currencyAttr = htmlspecialchars($r['Fld_Currency_Text'] ?? '', ENT_QUOTES);
    $leadTimeAttr = htmlspecialchars($r['lead_time'] ?? '', ENT_QUOTES);
    echo "<td>";
    echo "<button type='button' class='btn btn-xs btn-success js-use-sq-source' data-quote-id='".(int)$r['ID']."' data-supplier='".$supplierAttr."' data-price='".$priceAttr."' data-currency='".$currencyAttr."' data-lead-time='".$leadTimeAttr."'>Use this SQ</button> ";
    echo "<a href='modif_suppliers_quote.php?ID=".(int)$r['ID']."' class='btn btn-xs btn-default'><i class='fa fa-pencil'></i></a>";
    echo "</td>";
    echo "</tr>";
}
echo "</tbody></table>";
