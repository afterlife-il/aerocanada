<?php
session_start();
include_once "conf.php";


require('../classes/company.class.php');
$objet=new company();
$donnee = $objet->modif_company();

echo "<META http-equiv=\"refresh\" content=\"0;URL=modif_company.php?Fld_Company_ID=".$_POST['Fld_Company_ID']."\">";

?>