<?php
session_start();
include_once "conf.php";

require('../classes/parts.class.php');

$objet = new parts();
$objet->add_part();

header("Location: parts.php");
exit();
?>
