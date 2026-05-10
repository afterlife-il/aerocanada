<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

error_reporting(E_ALL);
ini_set("display_errors", 0);
ob_start();
header('Content-Type: application/json; charset=utf-8');

$requestData = $_REQUEST;
$draw = intval($requestData['draw'] ?? 0);
$start = max(0, intval($requestData['start'] ?? 0));
$length = max(10, intval($requestData['length'] ?? 25));

/*
 * Must match pages/stock_external.php THEAD exactly:
 * 0 PN
 * 1 DESCRIPTION
 * 2 Fld_Qty
 * 3 Fld_Condition_ID (display condition label)
 * 4 Company
 * 5 Fld_Physical_Stock
 * 6 Fld_Entry_Date
 */
$columns = array(
    0 => 'p.Fld_Part_Nbr',
    1 => 'p.Fld_Part_Desc',
    2 => 'se.Fld_Qty',
    3 => 'cond.Fld_Condition_Text',
    4 => 'company.Fld_Company_Name',
    5 => 'se.Fld_Physical_Stock',
    6 => 'se.Fld_Entry_Date'
);

$baseSql = "
    FROM tbl_Stock_external se
    LEFT JOIN tbl_Parts p ON se.Fld_Part_ID = p.Fld_Part_ID
    LEFT JOIN tbl_Condition cond ON se.Fld_Condition_ID = cond.Fld_Condition_ID
    LEFT JOIN tb_company company ON se.Fld_Company_ID = company.Fld_Company_ID
    WHERE 1=1
";

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tbl_Stock_external");
$totalData = $totalQuery ? intval(mysqli_fetch_assoc($totalQuery)['c']) : 0;

$search = trim($requestData['search']['value'] ?? '');
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $baseSql .= "
      AND (
        p.Fld_Part_Nbr LIKE '%$s%' OR
        p.Fld_Part_Desc LIKE '%$s%' OR
        se.Fld_Qty LIKE '%$s%' OR
        cond.Fld_Condition_Text LIKE '%$s%' OR
        company.Fld_Company_Name LIKE '%$s%' OR
        se.Fld_Physical_Stock LIKE '%$s%' OR
        se.Fld_Entry_Date LIKE '%$s%' OR
        se.Fld_Stock_Remark LIKE '%$s%' OR
        se.Fld_Sales_Remark LIKE '%$s%'
      )
    ";
}

$filteredQuery = mysqli_query($conn, "SELECT COUNT(*) AS c $baseSql");
$totalFiltered = $filteredQuery ? intval(mysqli_fetch_assoc($filteredQuery)['c']) : 0;

$orderIdx = intval($requestData['order'][0]['column'] ?? 0);
$dir = strtolower($requestData['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
$orderCol = $columns[$orderIdx] ?? 'p.Fld_Part_Nbr';

$sql = "
    SELECT
      se.Fld_Stock_externe_ID,
      p.Fld_Part_Nbr,
      p.Fld_Part_Desc,
      se.Fld_Qty,
      cond.Fld_Condition_Text,
      company.Fld_Company_Name,
      se.Fld_Physical_Stock,
      se.Fld_Entry_Date
    $baseSql
    ORDER BY $orderCol $dir, se.Fld_Stock_externe_ID DESC
    LIMIT $start, $length
";

$query = mysqli_query($conn, $sql);
if (!$query) {
    $out = [
        "draw" => $draw,
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalFiltered,
        "data" => [],
        "error" => "SQL: " . mysqli_error($conn)
    ];
    ob_end_clean();
    echo json_encode($out);
    exit;
}

$data = array();
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = array(
        htmlspecialchars($row['Fld_Part_Nbr'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Part_Desc'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Qty'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Condition_Text'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Company_Name'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Physical_Stock'] ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($row['Fld_Entry_Date'] ?? '', ENT_QUOTES, 'UTF-8')
    );
}

$out = array(
    "draw" => $draw,
    "recordsTotal" => $totalData,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
);

$junk = ob_get_contents();
ob_end_clean();
if (trim($junk) !== '') $out['debug'] = $junk;

echo json_encode($out);
?>
