<?php
// add_pn_from_popup.php (version sécurisée, comportement identique)
session_start();
include_once "conf.php";
include_once "page_titles.php";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") {
  http_response_code(403);
  exit;
}

// Récup / nettoyage des inputs (on garde le contrat: via GET)
$pn        = isset($_GET['Fld_Part_Nbr']) ? trim($_GET['Fld_Part_Nbr']) : '';
$desc      = isset($_GET['Fld_Part_Desc']) ? trim($_GET['Fld_Part_Desc']) : null;
$alt_pn    = isset($_GET['alt_pn']) ? trim($_GET['alt_pn']) : null;
$mfg_raw   = isset($_GET['Fld_Part_MFG']) ? trim($_GET['Fld_Part_MFG']) : '';
$ac_id     = (isset($_GET['Fld_AC_ID']) && $_GET['Fld_AC_ID'] !== '') ? (int)$_GET['Fld_AC_ID'] : null;
$price     = (isset($_GET['Fld_Part_List_Price']) && $_GET['Fld_Part_List_Price'] !== '') ? (float)str_replace(',', '.', $_GET['Fld_Part_List_Price']) : null;
$cur_id    = (isset($_GET['Fld_Part_Price_Currency_ID']) && $_GET['Fld_Part_Price_Currency_ID'] !== '') ? (int)$_GET['Fld_Part_Price_Currency_ID'] : null;
$lp_date   = isset($_GET['Fld_Part_LP_Date']) ? trim($_GET['Fld_Part_LP_Date']) : null;
$remark    = isset($_GET['Fld_Remark']) ? trim($_GET['Fld_Remark']) : null;
$ata       = isset($_GET['ata_chapter']) ? trim($_GET['ata_chapter']) : null;
$oem_lt    = isset($_GET['oem_lead_time']) ? trim($_GET['oem_lead_time']) : null;
$user_id   = isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : null;

// PN obligatoire
if ($pn === '') { http_response_code(400); exit; }

// Essayer d'extraire un ID numérique depuis "label,ID" (on prend ce qu’il y a APRES la DERNIERE virgule)
$mfg_id = null;
if ($mfg_raw !== '') {
  $pos = strrpos($mfg_raw, ',');
  $last = $pos !== false ? trim(substr($mfg_raw, $pos + 1)) : trim($mfg_raw);
  if ($last !== '' && ctype_digit($last)) { $mfg_id = (int)$last; }
}

// Vérifier doublon PN
if ($stmt = mysqli_prepare($link, "SELECT 1 FROM tbl_Parts WHERE Fld_Part_Nbr = ? LIMIT 1")) {
  mysqli_stmt_bind_param($stmt, "s", $pn);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $exists = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_close($stmt);
  if ($exists) { http_response_code(204); exit; } // PN déjà présent → pas d’output, comme avant
}

// Date d’ajout cohérente (DATE). Si ta colonne est un YEAR, remplace par date('Y')
$add_date = date('Y-m-d');

// INSERT sécurisé (NE PAS forcer Fld_Part_ID)
$sql = "INSERT INTO tbl_Parts
  (Fld_Part_Nbr, Fld_Part_Desc, Fld_Part_MFG, Fld_Part_MFG_Old, Fld_AC_ID, Fld_Old_LP,
   Fld_Part_List_Price, Fld_Part_Price_Currency_ID, Fld_Part_LP_Date, Fld_Remark,
   status, alt_pn, Fld_Add_PN_Date, aci_contact_entry, ata_chapter, oem_lead_time,
   core_value, id_currency_core_value)
  VALUES (?, ?, ?, '', ?, '', ?, ?, ?, ?, 'Available', ?, ?, ?, ?, ?, NULL, NULL)";

if ($stmt = mysqli_prepare($link, $sql)) {
  // On bind tout en "s" (string). NULL en PHP restera NULL côté MySQL.
  mysqli_stmt_bind_param(
    $stmt,
    "sssssssssssss", // 13 paramètres
    $pn,
    $desc,
    $mfg_id,     // peut être NULL
    $ac_id,      // peut être NULL
    $price,      // peut être NULL
    $cur_id,     // peut être NULL
    $lp_date,
    $remark,
    $alt_pn,
    $add_date,
    $user_id,    // peut être NULL
    $ata,
    $oem_lt
  );
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

// Aucun echo volontairement (comportement inchangé pour ton $.ajax)
http_response_code(201);

