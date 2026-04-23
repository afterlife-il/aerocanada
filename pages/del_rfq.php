<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/rfq.class.php');

$objet = new rfq();
$donnee = $objet->del_rfq($_GET['ID']);

if ($_GET['part_id'] === "rfqlist") {
    echo '<meta http-equiv="refresh" content="0;URL=rfq-list.php">';
} else {
    // On récupère le PN officiel à partir du Part_ID
    $part_id = intval($_GET['part_id']);
    $sql = "SELECT Fld_Part_Nbr FROM tbl_Parts WHERE Fld_Part_ID = $part_id LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if ($res && $row = mysqli_fetch_assoc($res)) {
        $pn = urlencode($row['Fld_Part_Nbr']);
        echo "<meta http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?pn=$pn\">";
    } else {
        echo "Erreur : PN introuvable.";
    }
}
?>
