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

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function moneyText($price, $currency) {
    $price = ($price === null) ? '' : trim((string)$price);
    $currency = trim((string)$currency);
    if ($price === '' || $price === '0') return '';
    return number_format((float)$price, 2, '.', ',') . ($currency !== '' ? ' ' . $currency : '');
}

function renderStockSection($title, $sourceType, $req, $emptyText) {
    $count = $req ? mysqli_num_rows($req) : 0;
    echo "<div class='stock-data' style='margin:10px 0'>";
    echo "<h5 style='margin:8px 0 6px;font-weight:bold'>".h($title)."</h5>";

    if ($count === 0) {
        echo "<div class='alert alert-warning' style='margin:6px 0'>".h($emptyText)."</div>";
        echo "</div>";
        return;
    }

    echo "<div class='text-muted' style='margin-bottom:4px'>".$count." row".($count > 1 ? "s" : "")." found.</div>";
    echo "<table class='table table-condensed table-bordered' style='margin:0 0 8px; font-size:12px; background:#fffff0'>";
    echo "<thead style='background:#ffe'><tr>";
    echo "<th>Action</th><th>Source Type</th><th>PN</th><th>Description</th><th>SN</th><th>Qty</th><th>Condition</th><th>Release/Certification</th><th>Supplier/Company</th><th>Location</th><th>Price/Cost</th><th>Remarks</th>";
    echo "</tr></thead><tbody>";

    while ($r = mysqli_fetch_assoc($req)) {
        $pn = $r['pn'] ?? $r['Fld_Part_Nbr'] ?? '';
        $desc = $r['description'] ?? $r['Fld_Part_Desc'] ?? '';
        $sn = $r['sn'] ?? $r['Fld_Part_SN'] ?? '';
        $qty = $r['qty'] ?? $r['Fld_Qty'] ?? '';
        $condition = $r['condition_text'] ?? $r['condition_part'] ?? '';
        $conditionId = $r['condition_id'] ?? $r['Fld_Condition_ID'] ?? '';
        $release = $r['release_text'] ?? '';
        if ($release === '' && isset($r['release_tag'])) {
            $release = trim(($r['release_tag'] ?? '') . ' ' . ($r['release_tag2'] ?? ''));
        }
        $supplier = $r['supplier_name'] ?? $r['company_name'] ?? '';
        $location = $r['location_text'] ?? $r['Fld_Warehouse_Location'] ?? $r['location'] ?? '';
        $priceRaw = $r['price'] ?? $r['Fld_Part_Price'] ?? '';
        $currency = $r['currency_text'] ?? '';
        $currencyId = $r['currency_id'] ?? $r['Fld_Price_Currency_ID'] ?? '';
        $releaseId = $r['release_id'] ?? $r['Fld_Release_ID'] ?? '';
        $price = moneyText($priceRaw, $currency);
        $remarks = trim(($r['remarks'] ?? $r['Fld_Stock_Remark'] ?? '') . ' ' . ($r['sales_remarks'] ?? $r['Fld_Sales_Remark'] ?? ''));
        $stockId = (int)($r['stock_id'] ?? $r['Fld_Stock_ID'] ?? $r['Fld_Stock_externe_ID'] ?? $r['id_stock_part'] ?? 0);

        echo "<tr>";
        echo "<td><button type='button' class='btn btn-xs btn-success js-use-stock-source'"
            ." data-stock-id='".$stockId."'"
            ." data-line-id='".(int)($_GET['line_id'] ?? 0)."'"
            ." data-part-id='".(int)($_GET['part_id'] ?? 0)."'"
            ." data-source-type='".h($sourceType)."'"
            ." data-pn='".h($pn)."'"
            ." data-description='".h($desc)."'"
            ." data-condition='".h($condition)."'"
            ." data-condition-id='".h($conditionId)."'"
            ." data-release-id='".h($releaseId)."'"
            ." data-supplier='".h($supplier)."'"
            ." data-location='".h($location)."'"
            ." data-sn='".h($sn)."'"
            ." data-qty='".h($qty)."'"
            ." data-price='".h($priceRaw)."'"
            ." data-price-text='".h($price)."'"
            ." data-currency-id='".h($currencyId)."'"
            ." data-currency='".h($currency)."'"
            ." data-remarks='".h($remarks)."'>Use this Stock</button></td>";
        echo "<td>".h($sourceType)."</td>";
        echo "<td>".h($pn)."</td>";
        echo "<td>".h($desc)."</td>";
        echo "<td>".h($sn)."</td>";
        echo "<td>".h($qty)."</td>";
        echo "<td>".h($condition)."</td>";
        echo "<td>".h($release)."</td>";
        echo "<td>".h($supplier)."</td>";
        echo "<td>".h($location)."</td>";
        echo "<td>".h($price)."</td>";
        echo "<td>".h($remarks)."</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "</div>";
}

$partWhereAci = $partId > 0 ? "s.Fld_Part_ID='".$partId."'" : "p.Fld_Part_Nbr='".addslashes($pn)."'";
$sqlAci = "SELECT
        s.Fld_Stock_ID AS stock_id,
        p.Fld_Part_Nbr AS pn,
        p.Fld_Part_Desc AS description,
        s.Fld_Part_SN,
        s.Fld_Qty,
        s.Fld_Condition_ID AS condition_id,
        cond.Fld_Condition_Text AS condition_text,
        s.Fld_Release_ID AS release_id,
        rel.Fld_Release_Text AS release_text,
        supplier.Fld_Company_Name AS supplier_name,
        s.Fld_Warehouse_Location,
        s.Fld_Part_Price AS price,
        s.Fld_Price_Currency_ID AS currency_id,
        cur.Fld_Currency_Text AS currency_text,
        s.Fld_Stock_Remark,
        s.Fld_Sales_Remark
    FROM tbl_Stock s
    LEFT JOIN tbl_Parts p ON s.Fld_Part_ID = p.Fld_Part_ID
    LEFT JOIN tbl_Condition cond ON s.Fld_Condition_ID = cond.Fld_Condition_ID
    LEFT JOIN tbl_Release rel ON s.Fld_Release_ID = rel.Fld_Release_ID
    LEFT JOIN tb_company supplier ON s.Fld_Supplier_ID = supplier.Fld_Company_ID
    LEFT JOIN tbl_Currency cur ON s.Fld_Price_Currency_ID = cur.Fld_Currency_ID
    WHERE $partWhereAci
    ORDER BY s.Fld_Stock_ID DESC
    LIMIT 50";

$partWhereExternal = $partId > 0 ? "se.Fld_Part_ID='".$partId."'" : "p.Fld_Part_Nbr='".addslashes($pn)."'";
$sqlExternal = "SELECT
        se.Fld_Stock_externe_ID AS stock_id,
        p.Fld_Part_Nbr AS pn,
        p.Fld_Part_Desc AS description,
        se.Fld_Part_SN,
        se.Fld_Qty,
        se.Fld_Condition_ID AS condition_id,
        cond.Fld_Condition_Text AS condition_text,
        se.Fld_Release_ID AS release_id,
        rel.Fld_Release_Text AS release_text,
        COALESCE(company.Fld_Company_Name, supplier.Fld_Company_Name) AS company_name,
        se.Fld_Warehouse_Location,
        se.Fld_External_Location,
        se.Fld_Part_Price AS price,
        se.Fld_Price_Currency_ID AS currency_id,
        cur.Fld_Currency_Text AS currency_text,
        se.Fld_Stock_Remark,
        se.Fld_Sales_Remark
    FROM tbl_Stock_external se
    LEFT JOIN tbl_Parts p ON se.Fld_Part_ID = p.Fld_Part_ID
    LEFT JOIN tbl_Condition cond ON se.Fld_Condition_ID = cond.Fld_Condition_ID
    LEFT JOIN tbl_Release rel ON se.Fld_Release_ID = rel.Fld_Release_ID
    LEFT JOIN tb_company supplier ON se.Fld_Supplier_ID = supplier.Fld_Company_ID
    LEFT JOIN tb_company company ON se.Fld_Company_ID = company.Fld_Company_ID
    LEFT JOIN tbl_Currency cur ON se.Fld_Price_Currency_ID = cur.Fld_Currency_ID
    WHERE $partWhereExternal
    ORDER BY se.Fld_Stock_externe_ID DESC
    LIMIT 50";

$reqAci = mysql2_query($sqlAci);
$reqExternal = mysql2_query($sqlExternal);

renderStockSection('ACI770 Stock', 'ACI770', $reqAci, 'No ACI770 stock found.');
renderStockSection('External Stock', 'External', $reqExternal, 'No External stock found.');
