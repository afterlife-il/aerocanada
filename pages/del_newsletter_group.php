<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

//** tbl_groupe_newsletter ** id_groupe_newsletter     group_name
$result = mysql2_query("DELETE FROM tbl_groupe_newsletter where id_groupe_newsletter='".$_GET['idsup']."'"); 
echo "<META http-equiv=\"refresh\" content=\"0;URL=newsletter_groups.php\">";

?>