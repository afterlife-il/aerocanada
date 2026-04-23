<?php
include_once "conf.php";
include_once "page_titles.php";

 mysql2_query("insert into tbl_ouverturemail values('','".date("Y-m-d H:i:s")."','".addslashes($_SERVER['HTTP_REFERER'])."','".addslashes($_SERVER["REMOTE_ADDR"])."','".addslashes($_GET['Fld_Contact_Name'])."','".addslashes($_GET['email'])."','".addslashes($_GET['RFQID'])."')");
 mysql_close();
$image = imagecreate(1,1);
header("Content-type: image/gif"); 
imagegif($image);
exit();

?>