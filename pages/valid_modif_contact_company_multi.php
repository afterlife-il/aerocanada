<?php
//valid_modif_contact_company_multi.php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
if(isset($_POST['act']) && $_POST['act']=='addcontact') $donnee = $objet->ajout_contact_company_unique();
else $donnee = $objet->valid_modif_contact_company_multi();

$companyId = isset($_POST['Fld_Company_ID']) ? (int)$_POST['Fld_Company_ID'] : 0;
$page = isset($_POST['return_page']) ? (int)$_POST['return_page'] : 1;
$anchor = !empty($_POST['return_anchor']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['return_anchor']) : 'bloccontactcompany';

header("Location: company.php?companyrating=all&page=".$page."&Fld_Company_ID=".$companyId."#".$anchor);
exit;

?>
