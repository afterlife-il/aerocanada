<?php
require_once 'conf.php';
header('Content-Type: application/json; charset=utf-8');

$parent = isset($_POST['companyid_parent']) ? (int)$_POST['companyid_parent'] : 0;
$comp   = isset($_POST['companyid1'])       ? (int)$_POST['companyid1']       : 0;

if ($parent <= 0 || $comp <= 0 || $parent === $comp) {
  echo json_encode(['ok' => false, 'error' => 'IDs invalides']); exit;
}

$chk = mysql2_query("SELECT 1 FROM tbl_Competitor WHERE Fld_Company_ID=$parent AND Fld_Competitor_ID=$comp LIMIT 1");
if ($chk && mysqli_num_rows($chk)) { echo json_encode(['ok' => true, 'message' => 'already-exists']); exit; }

$ins = mysql2_query("INSERT INTO tbl_Competitor (Fld_Company_ID, Fld_Competitor_ID) VALUES ($parent,$comp)");
echo json_encode(['ok' => (bool)$ins, 'error' => $ins ? null : mysqli_error($conn)]);
