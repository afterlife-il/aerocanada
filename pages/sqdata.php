<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE);

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
    echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[]]);
    exit;
}

include_once "conf.php";
include_once "page_titles.php";
if (!isset($conn) && isset($connection)) { $conn = $connection; }
if (!$conn) { echo json_encode(["draw"=>0,"recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[],"error"=>"No DB"]); exit; }
mysqli_set_charset($conn, 'utf8mb4');

$req  = $_REQUEST ?? [];
$draw = (int)($req['draw'] ?? 0);

/* Colonnes dans l'ordre EXACT de ton THEAD (15 visibles + Actions) :
   0 RFQ ID
   1 PN
   2 SN
   3 SUPPLIER NAME
   4 CONTACT NAME
   5 QTY
   6 CONDITION
   7 PRICE
   8 $/€
   9 LEAD TIME
   10 RELEASE
   11 TAG INFO
   12 TAG DATE
   13 Traced To
   14 SALES REMARKS
   15 ACTIONS
*/
$columns = [
  0  => 'rfq_sort',                 // alias calculé pour trier par RFQ ID
  1  => 'p.Fld_Part_Nbr',
  2  => 'r2.Fld_Part_SN',
  3  => 's.Fld_Company_Name',
  4  => 'cc.Fld_Contact_Name',
  5  => 'r2.Fld_Qty',
  6  => 'r2.Fld_Condition_ID',
  7  => 'r2.Fld_Price',
  8  => 'cur.Fld_Currency_Text',
  9  => 'r2.lead_time',
  10 => 'rel.Fld_Release_Text',
  11 => 'tag.Fld_Company_Name',
  12 => 'r2.Fld_Tag_Date',
  13 => 'r2.Fld_Traceability_ID',
  14 => 'r2.Fld_Remark',
  15 => 'r2.ID'
];

$columns = [
  0  => 'r2.ID',
  1  => 'rfq_sort',
  2  => 'p.Fld_Part_Nbr',
  3  => 'r2.Fld_Part_SN',
  4  => 's.Fld_Company_Name',
  5  => 'cc.Fld_Contact_Name',
  6  => 'r2.Fld_Qty',
  7  => 'r2.Fld_Condition_ID',
  8  => 'r2.Fld_Price',
  9  => 'cur.Fld_Currency_Text',
  10 => 'r2.lead_time',
  11 => 'rel.Fld_Release_Text',
  12 => 'tag.Fld_Company_Name',
  13 => 'r2.Fld_Tag_Date',
  14 => 'r2.Fld_Traceability_ID',
  15 => 'r2.Fld_Remark',
  16 => 'r2.ID'
];

/* Totaux */
$resTot = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tbl_RFQ_2");
$totalData = $resTot ? (int)mysqli_fetch_assoc($resTot)['c'] : 0;

/* Normalisation/tri du RFQ ID
   On essaye plusieurs formats puis on formate en YYYY-MM-DD-HHMMSS pour l’affichage.
*/
$RFQ_SORT = "
  CASE
    -- 2025-08-11-211645
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{6}$'
      THEN STR_TO_DATE(r2.Fld_RFQ_ID, '%Y-%m-%d-%H%i%s')
    -- 2025-08-11 21:16:45
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$'
      THEN STR_TO_DATE(r2.Fld_RFQ_ID, '%Y-%m-%d %H:%i:%s')
    -- 2025-08-11 21:16
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$'
      THEN STR_TO_DATE(CONCAT(r2.Fld_RFQ_ID, ':00'), '%Y-%m-%d %H:%i:%s')
    -- 2025-08-11
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
      THEN STR_TO_DATE(CONCAT(r2.Fld_RFQ_ID, ' 00:00:00'), '%Y-%m-%d %H:%i:%s')
    -- 11/08/2025 21:16:45 (FR)
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}$'
      THEN STR_TO_DATE(r2.Fld_RFQ_ID, '%d/%m/%Y %H:%i:%s')
    -- 11/08/2025 21:16 (FR)
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}$'
      THEN STR_TO_DATE(CONCAT(r2.Fld_RFQ_ID, ':00'), '%d/%m/%Y %H:%i:%s')
    -- 11/08/2025 (FR)
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
      THEN STR_TO_DATE(CONCAT(r2.Fld_RFQ_ID, ' 00:00:00'), '%d/%m/%Y %H:%i:%s')
    -- 20250811-211645 (collé)
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{8}-[0-9]{6}$'
      THEN STR_TO_DATE(r2.Fld_RFQ_ID, '%Y%m%d-%H%i%s')
    -- 20250811211645 (collé)
    WHEN r2.Fld_RFQ_ID REGEXP '^[0-9]{14}$'
      THEN STR_TO_DATE(r2.Fld_RFQ_ID, '%Y%m%d%H%i%s')
    ELSE NULL
  END
";

$RFQ_DISPLAY = "
  IF(($RFQ_SORT) IS NOT NULL,
     DATE_FORMAT(($RFQ_SORT), '%Y-%m-%d-%H%i%s'),
     r2.Fld_RFQ_ID
  )
";

/* FROM + LEFT JOIN  */
$baseSql = "
  FROM tbl_RFQ_2 r2
  LEFT JOIN tbl_Parts p            ON r2.Fld_Part_ID             = p.Fld_Part_ID
  LEFT JOIN tb_company s           ON r2.Fld_Supplier_ID         = s.Fld_Company_ID
  LEFT JOIN tb_company_contact cc  ON r2.Fld_Supplier_Contact_ID = cc.id_company_contact
  LEFT JOIN tbl_Release rel        ON r2.Fld_Release_ID          = rel.Fld_Release_ID
  LEFT JOIN tbl_Currency cur       ON r2.Fld_Currency_ID         = cur.Fld_Currency_ID
  LEFT JOIN tb_company tag         ON r2.Fld_Tag_Info_ID         = tag.Fld_Company_ID
  WHERE 1=1
";

/* Recherche globale */
$search = trim($req['search']['value'] ?? '');
if ($search !== '') {
  $s = mysqli_real_escape_string($conn, $search);
  $baseSql .= "
    AND (
      r2.Fld_RFQ_ID LIKE '%$s%' OR
      p.Fld_Part_Nbr LIKE '%$s%' OR
      r2.Fld_Part_SN LIKE '%$s%' OR
      s.Fld_Company_Name LIKE '%$s%' OR
      cc.Fld_Contact_Name LIKE '%$s%' OR
      r2.Fld_Qty LIKE '%$s%' OR
      r2.Fld_Price LIKE '%$s%' OR
      cur.Fld_Currency_Text LIKE '%$s%' OR
      rel.Fld_Release_Text LIKE '%$s%' OR
      tag.Fld_Company_Name LIKE '%$s%' OR
      r2.Fld_Tag_Date LIKE '%$s%' OR
      r2.Fld_Remark LIKE '%$s%'
    )
  ";
}

/* Nb filtré */
$resCount = mysqli_query($conn, "SELECT COUNT(*) AS c $baseSql");
$totalFiltered = $resCount ? (int)mysqli_fetch_assoc($resCount)['c'] : 0;

/* Tri & pagination */
$orderIdx = (int)($req['order'][0]['column'] ?? 0);
$dir      = (strtolower($req['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';
$orderCol = $columns[$orderIdx] ?? 'rfq_sort';

$start  = max(0, (int)($req['start']  ?? 0));
$length = max(10,(int)($req['length'] ?? 25));

/* Pour les colonnes texte, on force une collation prévisible pour le tri */
$textCols = [
  'p.Fld_Part_Nbr','r2.Fld_Part_SN','s.Fld_Company_Name','cc.Fld_Contact_Name',
  'cur.Fld_Currency_Text','rel.Fld_Release_Text','tag.Fld_Company_Name','r2.Fld_Remark'
];
$orderExpr = in_array($orderCol, $textCols, true)
  ? "$orderCol COLLATE utf8mb4_unicode_ci $dir, r2.ID $dir"
  : ($orderCol === 'rfq_sort'
        ? "rfq_sort $dir, r2.ID $dir"
        : "$orderCol $dir, r2.ID $dir");

/* Query finale */
$sql = "
  SELECT
    r2.*,
    ($RFQ_SORT)   AS rfq_sort,
    $RFQ_DISPLAY  AS rfq_display,
    p.Fld_Part_Nbr,
    s.Fld_Company_Name   AS Supplier_Name,
    cc.Fld_Contact_Name,
    rel.Fld_Release_Text,
    tag.Fld_Company_Name AS Tag_Info_Name,
    cur.Fld_Currency_Text
  $baseSql
  ORDER BY
    ".($orderCol === 'rfq_sort' ? "rfq_sort $dir, r2.ID $dir" : "$orderCol $dir, r2.ID $dir")."
  LIMIT $start, $length
";
$q = mysqli_query($conn, $sql);
if (!$q) {
  $out = ["draw"=>$draw,"recordsTotal"=>$totalData,"recordsFiltered"=>$totalFiltered,"data"=>[],"error"=>"SQL: ".mysqli_error($conn)];
  $junk = ob_get_contents(); ob_end_clean();
  if (trim($junk)!=='') $out['debug']=$junk;
  echo json_encode($out); exit;
}

/* Lignes : 16 colonnes exactement (15 + actions) */
$data = [];
while ($r = mysqli_fetch_assoc($q)) {
  // Prix formaté US 1,234.56 (si vide/non num. -> '')
  $priceFormatted = '';
  if ($r['Fld_Price'] !== null && $r['Fld_Price'] !== '') {
    $priceFormatted = number_format((float)$r['Fld_Price'], 2, '.', ',');
  }

  $row = [];
  $row[] = "<a href='modif_suppliers_quote.php?ID=".$r['ID']."'>".htmlspecialchars($r['rfq_display'])."</a>"; // 0 RFQ ID
  $row[] = htmlspecialchars($r['Fld_Part_Nbr'] ?? '');                               // 1 PN
  $row[] = htmlspecialchars($r['Fld_Part_SN'] ?? '');                                // 2 SN
  $row[] = htmlspecialchars($r['Supplier_Name'] ?? '');                               // 3 Supplier
  $row[] = htmlspecialchars($r['Fld_Contact_Name'] ?? '');                            // 4 Contact
  $row[] = htmlspecialchars($r['Fld_Qty'] ?? '');                                     // 5 Qty
  $row[] = htmlspecialchars($r['Fld_Condition_ID'] ?? '');                            // 6 Condition (ID)
  $row[] = $priceFormatted;                                                           // 7 PRICE
  $row[] = htmlspecialchars($r['Fld_Currency_Text'] ?? '');                           // 8 $/€
  $row[] = htmlspecialchars($r['lead_time'] ?? '');                                   // 9 Lead time
  $row[] = htmlspecialchars($r['Fld_Release_Text'] ?? '');                            // 10 Release
  $row[] = htmlspecialchars($r['Tag_Info_Name'] ?? '');                               // 11 Tag info
  $row[] = htmlspecialchars($r['Fld_Tag_Date'] ?? '');                                // 12 Tag date
  $row[] = htmlspecialchars($r['Fld_Traceability_ID'] ?? '');                         // 13 Traced to
  $row[] = htmlspecialchars($r['Fld_Remark'] ?? '');                                  // 14 Sales remarks
  $row[] =
    '<a href="modif_suppliers_quote.php?ID='.$r['ID'].'" title="Edit"><i class="fa fa-pencil"></i></a> '.
    '<a href="sup_sq.php?ID='.$r['ID'].'" class="text-danger js-del-sq" title="Delete"><i class="fa fa-trash"></i></a>'; // 15 Actions

  array_unshift($row, (int)$r['ID']);
  $data[] = $row;
}

$out = [
  "draw"            => $draw,
  "recordsTotal"    => $totalData,
  "recordsFiltered" => $totalFiltered,
  "data"            => $data
];

$junk = ob_get_contents(); ob_end_clean();
if (trim($junk)!=='') { $out['debug'] = $junk; }

echo json_encode($out);
