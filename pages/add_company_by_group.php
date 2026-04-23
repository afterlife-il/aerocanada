<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
	
		// ** tbl_company_group_newsletter ** id_company_group_newsletter  	id_company	  id_groupe_newsletter    valid
		//** tbl_groupe_newsletter ** id_groupe_newsletter group_name
		if(!empty($_GET['companyid']))
		{
			$companyid = explode(",", $_GET['companyid']);
			$id_company=$companyid[0];
			$req="INSERT INTO tbl_company_group_newsletter (`id_company_group_newsletter`,`id_company`, `id_groupe_newsletter`, `valid`)VALUES ('','".$id_company."','".$_GET['id_groupe_newsletter']."','1');";
		 // echo $req;
		 $requete = mysql2_query($req);
		}


?>

