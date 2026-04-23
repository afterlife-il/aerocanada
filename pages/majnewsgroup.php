<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
//** tbl_groupe_newsletter ** id_groupe_newsletter     group_name
$sql="update tbl_groupe_newsletter set group_name='".addslashes($_GET['group_name'])."' where id_groupe_newsletter='".$_GET['id_groupe_newsletter']."'";
$query=mysql2_query($sql);

?>