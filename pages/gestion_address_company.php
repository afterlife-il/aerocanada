<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
if ($_POST['act']=='addaddresscompany') $donnee = $objet->add_address_company();
else $donnee = $objet->gestion_address_company();

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_POST['Fld_Company_ID']."\">";

?>