<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") { exit; }

$partId = (int)($_GET['part_id'] ?? 0);
$pn = trim($_GET['pn'] ?? '');

if ($partId === 0 && $pn === '') {
    echo "<p class='text-muted'>No part specified.</p>";
    exit;
}

$where = "WHERE 1=1";
if ($partId > 0) $where .= " AND part_id='".$partId."'";
elseif ($pn !== '') $where .= " AND pn='".addslashes($pn)."'";

$sql = "SELECT * FROM tb_stock_part $where ORDER BY id_stock_part DESC LIMIT 20";
$req = mysql2_query($sql);
$count = $req ? mysqli_num_rows($req) : 0;

if ($count == 0) {
    echo "<div class='alert alert-warning stock-data' style='margin:8px 0'>No stock records found for this part.</div>";
    exit;
}

echo "<div class='text-muted stock-data' style='margin-top:8px'>".$count." stock row".($count > 1 ? "s" : "")." found.</div>";
echo "<table class='table table-condensed table-bordered stock-data' style='margin:8px 0; font-size:12px; background:#fffff0'>";
echo "<thead style='background:#ffe'><tr>";
echo "<th>PN</th><th>SN</th><th>Stock Qty</th><th>Condition</th><th>Location</th><th>Release/Cert</th><th>Tag Date</th><th>Trace</th><th>ACI PO</th><th>Remarks</th><th>Action</th>";
echo "</tr></thead><tbody>";

while ($r = mysqli_fetch_assoc($req)) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($r['pn'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['sn'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['moq'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['condition_part'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['location'] ?? '')."</td>";
    echo "<td>".htmlspecialchars(trim(($r['release_tag'] ?? '').' '.($r['release_tag2'] ?? '')))."</td>";
    echo "<td>".htmlspecialchars($r['release_tag_date'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['trace'] ?? '')."</td>";
    echo "<td>".htmlspecialchars($r['aci_po'] ?? '')."</td>";
    echo "<td></td>";
    $pnAttr = htmlspecialchars($r['pn'] ?? '', ENT_QUOTES);
    $conditionAttr = htmlspecialchars($r['condition_part'] ?? '', ENT_QUOTES);
    $locationAttr = htmlspecialchars($r['location'] ?? '', ENT_QUOTES);
    $snAttr = htmlspecialchars($r['sn'] ?? '', ENT_QUOTES);
    $traceAttr = htmlspecialchars($r['trace'] ?? '', ENT_QUOTES);
    $qtyAttr = htmlspecialchars($r['moq'] ?? '', ENT_QUOTES);
    echo "<td><button type='button' class='btn btn-xs btn-success js-use-stock-source' data-stock-id='".(int)$r['id_stock_part']."' data-pn='".$pnAttr."' data-condition='".$conditionAttr."' data-location='".$locationAttr."' data-sn='".$snAttr."' data-trace='".$traceAttr."' data-qty='".$qtyAttr."'>Use this Stock</button></td>";
    echo "</tr>";
}
echo "</tbody></table>";
