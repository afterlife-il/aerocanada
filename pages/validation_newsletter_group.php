<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


//** tbl_groupe_newsletter ** id_groupe_newsletter     group_name
$req="INSERT INTO tbl_groupe_newsletter (`id_groupe_newsletter`,`group_name`) VALUES ('','".addslashes($_POST['group_name'])."');";
// echo $req;
$requete = mysql2_query($req);

echo "<META http-equiv=\"refresh\" content=\"0;URL=newsletter_groups.php\">";

?>