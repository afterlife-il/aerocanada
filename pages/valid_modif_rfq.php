<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

//validation des modification de la rfq
require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->valid_modif_rfq();
//End validation des modification de la rfq

//Enregistrement de la quotation ou des modification de la quotation dans la BDD

if (0<$_POST['num_rows_rfq3']){
	$objet=new rfq();
	$donnee = $objet->modif_multi_quote();
}
else
{
$objet=new rfq();
$donnee = $objet->add_quote_RFQ3_multi();						
}
//Fin Enregistrement de la quotation ou des modification de la quotation dans la BDD	

//affichage de tout les resultats envoyoer par un formulaire en POST
// foreach($_POST as $key => $val) echo '$_POST["'.$key.'"]='.$val.'<br />';


echo "<META http-equiv=\"refresh\" content=\"0;URL=details_rfq.php?Fld_RFQ_ID=".$_POST['Fld_RFQ_ID']."\">";

?>