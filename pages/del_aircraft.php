<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/aircraft.class.php');

$objet=new aircraft();
$donnee = $objet->del_aircraft($_GET['idsup']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=aircrafts.php\">";

?>