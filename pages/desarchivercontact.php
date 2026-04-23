
<?php
include_once "conf.php";
include_once "page_titles.php";
$sql="Update tb_company_contact set status='Available' where id_company_contact=".$_GET['idsup'];
$req = mysql2_query($sql);
?>