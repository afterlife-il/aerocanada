<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->archive_part($_GET['Fld_Part_ID']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=Parts/index.php\">";

?>