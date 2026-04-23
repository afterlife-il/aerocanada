<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/release.class.php');
$objet=new release();

$donnee = $objet->modif_release();

//echo "<META http-equiv=\"refresh\" content=\"0;URL=release.php\">";

?>