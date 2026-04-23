<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->del_doc_company($_GET['id_docs_attachment_company']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_GET['id']."\">";

?>