<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->modif_part();

echo "<META http-equiv=\"refresh\" content=\"0;URL=parts.php\">";

?>