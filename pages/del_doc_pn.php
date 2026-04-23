<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->del_doc_pn($_GET['id_docs_attachment_pn']);

$pn = urlencode($_GET['pn']);
echo "<META http-equiv=\"refresh\" content=\"0;URL=Part-Nbr.php?pn={$pn}\">";

?>