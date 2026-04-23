<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
if($_POST['act']=='addbankaccount') $donnee = $objet->add_bank_account();
else $donnee = $objet->valid_modif_bank_account();

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_POST['Fld_Company_ID']."\">";

?>