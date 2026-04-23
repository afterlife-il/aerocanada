<?php
/*
session_start();
include_once "conf.php";
include_once "page_titles.php";
		// pnid  description  aircraft  ata  capability pma  doa  der  code_oem  design_oem  companyid
		
		if((!empty($_GET['pnid']))&&(!empty($_GET['companyid'])))
		{
									//recuperation de du part id et du pn
									$pnid = explode(",", $_GET['pnid']);
									$pn=$pnid[0]; 
									$pnid=$pnid[1]; 
									//Fin recuperation de du part id et du pn
									
									//recuperation de la company id
									$companyid2 = explode(",", $_GET['companyid']);
									$companyidrecup=$companyid2[0]; 
									//Fin recuperation de la company id

											

		$dataemp = mysqli_fetch_array($resultemp);
		$today = date("Y-m-d");
		$req="INSERT INTO `tbl_capa_list` (`id_capa_list`, `Fld_Part_ID`, `pn`, `descriptioin`, `aircraft`, `manufacturer`, `ata`, `capability`, `pma`, `doa`, `der`, `code_oem`, `design_oem`, `id_company`, `status`, `entry_date`, `comments`) VALUES (NULL, '".$pnid."', '".$pn."', '".$_GET['description']."', '".$_GET['aircraft']."', '', '".$_GET['ata']."', '".$_GET['capability']."', '', '', '', '".$_GET['code_oem']."', '".$_GET['design_oem']."', '".$companyidrecup."', '', '".$today."','');";	
		$requete = mysql2_query($req);
				
		}
		*/
		
?>

