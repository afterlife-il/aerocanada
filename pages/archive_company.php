<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->archive_company($_GET['Fld_Company_ID']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php\">";

?>