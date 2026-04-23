<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->add_docs();

$pn = urlencode($_POST['pn_id']);
echo "<meta http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?pn=$pn\">";

?>