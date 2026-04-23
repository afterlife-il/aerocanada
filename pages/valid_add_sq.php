<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/sq.class.php');
$objet=new sq();
$donnee = $objet->add_sq();
		
echo "<META http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?part_id=".$_POST['Fld_Part_ID']."\">";

?>