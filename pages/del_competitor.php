<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->del_competitor($_GET['Fld_Linked_ID']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_GET['Fld_Company_ID']."\">";

?>