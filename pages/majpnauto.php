<?php
session_start();
include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") { header("Location: login.php"); exit; }
include_once "page_titles.php";

// Gestion Fld_Part_MFG
if (!empty($_POST['companyidforoem'])) {
    $companyidforoem = explode(",", $_POST['companyidforoem']);
    $Fld_Part_MFG = $companyidforoem[0];
} else {
    $Fld_Part_MFG = $_POST['Fld_Part_MFG'];
}

// Préparation de la requête d'UPDATE
if (!empty($_POST['Fld_Part_Nbr']) || !empty($_POST['Fld_Part_Desc'])) {
    $sql = "UPDATE tbl_Parts SET 
        Fld_Part_Nbr = '" . $_POST['Fld_Part_Nbr'] . "',
        Fld_Part_Desc = '" . addslashes($_POST['Fld_Part_Desc']) . "',
        alt_pn = '" . $_POST['alt_pn'] . "',
        Fld_Part_List_Price = '" . $_POST['Fld_Part_List_Price'] . "',
        Fld_Part_MFG = '" . $Fld_Part_MFG . "',
        oem_lead_time = '" . $_POST['oem_lead_time'] . "',
        Fld_AC_ID = '" . $_POST['Fld_AC_ID'] . "',
        Fld_Part_Price_Currency_ID = '" . $_POST['FldCurrencyID'] . "',
        Fld_Remark = '" . addslashes($_POST['Fld_Remark']) . "',
        ata_chapter = '" . $_POST['ata_chapter'] . "',
        Fld_Part_LP_Date = '" . $_POST['Fld_Part_LP_Date'] . "',
        core_value = '" . $_POST['core_value'] . "',
        id_currency_core_value = '" . $_POST['id_currency_core_value'] . "',
        wanted = '" . $_POST['wanted'] . "'
    WHERE Fld_Part_ID = '" . $_POST['Fld_Part_ID'] . "'";
} else {
    $sql = "UPDATE tbl_Parts SET 
        alt_pn = '" . $_POST['alt_pn'] . "',
        Fld_Part_List_Price = '" . $_POST['Fld_Part_List_Price'] . "',
        Fld_Part_MFG = '" . $Fld_Part_MFG . "',
        oem_lead_time = '" . $_POST['oem_lead_time'] . "',
        Fld_AC_ID = '" . $_POST['Fld_AC_ID'] . "',
        Fld_Part_Price_Currency_ID = '" . $_POST['FldCurrencyID'] . "',
        Fld_Remark = '" . addslashes($_POST['Fld_Remark']) . "',
        ata_chapter = '" . $_POST['ata_chapter'] . "',
        Fld_Part_LP_Date = '" . $_POST['Fld_Part_LP_Date'] . "',
        core_value = '" . $_POST['core_value'] . "',
        id_currency_core_value = '" . $_POST['id_currency_core_value'] . "',
        wanted = '" . $_POST['wanted'] . "'
    WHERE Fld_Part_ID = '" . $_POST['Fld_Part_ID'] . "'";
}

// Exécution de la requête
$query = mysql2_query($sql);

// Cage code update
if (!empty($_POST['cage_code'])) {
    $sql2 = "UPDATE tb_company SET cage_code = '" . $_POST['cage_code'] . "' 
             WHERE Fld_Company_ID = '" . $_POST['Fld_Part_MFG'] . "'";
    $query2 = mysql2_query($sql2);
}

// Redirection vers la page avec ?pn=xxxxx
$part_id = intval($_POST['Fld_Part_ID']);
$sql = "SELECT Fld_Part_Nbr FROM tbl_Parts WHERE Fld_Part_ID = $part_id LIMIT 1";
$res = mysqli_query($conn, $sql); // Attention ici : on utilise bien $conn

if ($res && $row = mysqli_fetch_assoc($res)) {
    $pn = urlencode($row['Fld_Part_Nbr']);
    echo "<meta http-equiv='refresh' content='0;url=Part-Nbr.php?pn=$pn'>";
} else {
    echo "<meta http-equiv='refresh' content='0;url=parts.php'>";
}
?>
