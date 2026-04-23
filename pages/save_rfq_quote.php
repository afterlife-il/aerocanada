<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

//Mise a jour RFQ et enregistrement ou mise a jour de la quotation dans la BDD
require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->modif_rfq_quote();			
//Fin Mise a jour RFQ et enregistrement ou mise a jour de la quotation dans la BDD	
		
echo "<META http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?part_id=".$_POST['part_id']."\">";

?>