<?php
require_once 'conf.php';
header('Content-Type: application/json; charset=utf-8');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
if ($term === '' && isset($_GET['query'])) {
  $term = trim($_GET['query']);
}
$exclude = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0;

$out = [];
if ($term !== '') {
  $esc = mysqli_real_escape_string($conn, $term);
  $sql = "
    SELECT Fld_Company_ID, Fld_Company_Name
    FROM tb_company
    WHERE Fld_Company_Name LIKE '%$esc%'
      " . ($exclude > 0 ? "AND Fld_Company_ID <> $exclude" : "") . "
    ORDER BY Fld_Company_Name
    LIMIT 20
  ";
  if ($rs = mysql2_query($sql)) {
    while ($row = mysqli_fetch_assoc($rs)) {
      $out[] = [
        'Fld_Company_ID'   => (int)$row['Fld_Company_ID'],
        'Fld_Company_Name' => $row['Fld_Company_Name']
      ];
    }
  }
}
echo json_encode($out);
