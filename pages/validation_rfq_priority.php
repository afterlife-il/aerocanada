<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/rfq.class.php');
$objet=new rfq();
$donnee = $objet->add_rfq_priority();

echo "<META http-equiv=\"refresh\" content=\"0;URL=rfq_priority.php\">";

?>