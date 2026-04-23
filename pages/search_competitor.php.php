<?php
include_once "conf.php";
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['query']) ? trim($_GET['query']) : '';
$out = [];

if ($q !== '') {
  $qEsc = mysqli_real_escape_string($conn, $q);
  $res = mysql2_query("SELECT Fld_Company_ID, Fld_Company_Name
                       FROM tb_company
                       WHERE Fld_Company_Name LIKE '%{$qEsc}%'
                       ORDER BY Fld_Company_Name
                       LIMIT 25");
  while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
}

echo json_encode($out);
