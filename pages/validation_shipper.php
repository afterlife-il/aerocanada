<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->add_shipper();

echo "<META http-equiv=\"refresh\" content=\"0;URL=Shippers.php\">";

?>