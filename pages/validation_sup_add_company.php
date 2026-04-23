<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->sup_add_company($_GET['id_tbl_company_Details']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?Fld_Company_ID=".$_GET['Fld_Company_ID']."\">";

?>