<?php
									include_once "conf.php";
include_once "page_titles.php";
									
									$sql="update tb_company_contact set Fld_Contact_Remark='".$_GET['Fld_Contact_Remark']."' where id_company_contact='".$_GET['id_company_contact']."'";
									//echo $sql;
									$query=mysql2_query($sql);
?>