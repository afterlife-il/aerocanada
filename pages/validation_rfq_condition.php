<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->add_rfq_conditions();

echo "<META http-equiv=\"refresh\" content=\"0;URL=rfq_conditions.php\">";

?>