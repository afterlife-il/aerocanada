<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/sq.class.php');
$objet=new sq();
$donnee = $objet->modif_sq();

if(!empty($_POST['part_id'])) echo "<META http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?part_id=".$_POST['part_id']."\">";
else echo "<META http-equiv=\"refresh\" content=\"0;URL=suppliers_quote.php\">";

?>