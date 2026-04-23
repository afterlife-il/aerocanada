<?php
session_start();
include_once "conf.php";

// Récupération sécurisée
$Fld_Shipper_ID   = isset($_POST['Fld_Shipper_ID']) ? (int)$_POST['Fld_Shipper_ID'] : 0;
$Fld_Shipper_Text = isset($_POST['Fld_Shipper_Text']) ? trim($_POST['Fld_Shipper_Text']) : '';

if ($Fld_Shipper_ID <= 0) {
    echo "<div class='alert alert-warning'>ID manquant.</div>";
    echo "<p><a class='btn btn-default' href='Shippers.php'>&larr; Retour à la liste</a></p>";
    exit;
}

if ($Fld_Shipper_Text === '') {
    echo "<div class='alert alert-warning'>Le nom du shipper ne peut pas être vide.</div>";
    echo "<p><a class='btn btn-default' href='modif_shipper.php?id={$Fld_Shipper_ID}'>&larr; Retour à l’édition</a></p>";
    exit;
}

// Echappement pour SQL (on garde ton wrapper + $conn de conf.php)
$Fld_Shipper_Text_sql = mysqli_real_escape_string($conn, $Fld_Shipper_Text);
$sql = "UPDATE tbl_Shipper
        SET Fld_Shipper_Text = '{$Fld_Shipper_Text_sql}'
        WHERE Fld_Shipper_ID = {$Fld_Shipper_ID}";

$ok = mysql2_query($sql);

if ($ok) {
    // Retour à la liste avec un message (optionnel)
    header("Location: Shippers.php?msg=updated&id=".$Fld_Shipper_ID);
    exit;
} else {
    echo "<div class='alert alert-danger'>Erreur SQL lors de la mise à jour.</div>";
    echo "<p><a class='btn btn-default' href='modif_shipper.php?id={$Fld_Shipper_ID}'>&larr; Retour à l’édition</a></p>";
}
