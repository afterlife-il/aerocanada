<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";


require('../classes/users.class.php');
$objet=new users();
$donnee = $objet->add_user();

echo "<META http-equiv=\"refresh\" content=\"0;URL=users.php\">";

?>