<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/users.class.php');

$objet=new users();
$donnee = $objet->del_user($_GET['idsup']);

echo "<META http-equiv=\"refresh\" content=\"0;URL=users.php\">";

?>