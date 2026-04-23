<?php
//valid_modif_contact_company_multi.php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
if($_POST['act']=='addcontact') $donnee = $objet->ajout_contact_company_unique();
else $donnee = $objet->valid_modif_contact_company_multi();

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_POST['Fld_Company_ID']."\">";

?>