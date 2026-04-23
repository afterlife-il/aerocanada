<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
require('../classes/users.class.php');

$objet=new users();
$donnee = $objet->modif_user();

if((!empty($_POST['act']))&&($_POST['act']=='modifinfoperso')) echo "<META http-equiv=\"refresh\" content=\"0;URL=user_profile.php\">";	

else echo "<META http-equiv=\"refresh\" content=\"0;URL=users.php\">";

?>