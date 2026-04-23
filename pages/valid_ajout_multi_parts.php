<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/parts.class.php');
$objet=new parts();
$donnee = $objet->add_multi_parts();

if((!empty($_POST['origine']))&&($_POST['origine']=='popup')) echo "<script language='javascript'>window.close()</script>";
else echo "<META http-equiv=\"refresh\" content=\"0;URL=parts.php\">";

?>