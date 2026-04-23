<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/company.class.php');
$objet=new company();
if($_POST['act']=='addaircraft') $donnee = $objet->add_aircraft_fleet();
else $donnee = $objet->valid_modif_aircraft_fleet();

echo "<META http-equiv=\"refresh\" content=\"0;URL=company.php?companyrating=all&Fld_Company_ID=".$_POST['Fld_Company_ID']."\">";

?>