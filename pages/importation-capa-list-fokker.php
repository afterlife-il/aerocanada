<?php
$fop = fopen('csv/Fokker_Component_Maintenance_In-House_Capability_List_Oct_2018.csv', 'r');
if($fop === false)
{
   // Ouverture du fichier échouée
}
else
{
	include_once "conf.php";
include_once "page_titles.php";
?>
<table style="border: 1px solid black;">
<?php
	$existvar=0;//var roy
   $delimiter = ';'; // Ton séparateur de cellules
   while(($a = fgetcsv($fop, 0, $delimiter)) !== false) // Récupération d'une ligne
   {
	   $varoy=0;
?>
   <tr>
<?php

      foreach($a as $val) // Parcours en boucle des cellules de la ligne
      {
		  $varoy++;
?>
      <td style="border: 1px solid black;"><?php echo $varoy; ?> <?php echo $val; ?></td>
<?php

//*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter   cage_code    essentiality_category_id    nha moq

//MPN;Part Number;Alternate Part Number;Description;Manufacturer;Cage;ATA;Aircraft;Engine;Shop

		if ($varoy=='1') $MPN=$val;
		if ($varoy=='2') $PN=$val;
		if ($varoy=='3') $Alternate=$val;
		if ($varoy=='4') $Description=$val;
		if ($varoy=='5') $Manufacturer=$val;
		if ($varoy=='6') $Cage=$val;
		if ($varoy=='7') $ATA=$val;
		if ($varoy=='8') $Aircraft=$val;
		if ($varoy=='9') $Engine=$val;
		if ($varoy=='10') $Shop=$val;

		$comments ="MPN:".$MPN;
		if (!empty($Alternate)) $comments .=" ** Alternate:".$Alternate;
		if (!empty($Manufacturer)) $comments .=" ** Manufacturer:".$Manufacturer;
		if (!empty($Cage)) $comments .=" ** Cage:".$Cage;
		if (!empty($Engine)) $comments .=" ** Engine:".$Engine;
		if (!empty($Shop)) $comments .=" ** Shop:".$Shop;
		
      }
					if(!empty($PN)){

											//Verification si le pn se trouve dans la table tbl_Parts
					                        $sqlemp="SELECT * FROM tbl_Parts where Fld_Part_Nbr='".$PN."'";
											
											$resultemp = mysql2_query($sqlemp);
											$nb_resultats = mysqli_num_rows($resultemp);
					                        //Fin Verification si le pn se trouve dans la table tbl_Parts
											
				if (0<$nb_resultats) {	
								$dataemp = mysqli_fetch_array($resultemp);
								
								$existvar++;
								echo $existvar;
								echo "exist";		
								$req="INSERT INTO `tbl_capa_list` (`id_capa_list`, `Fld_Part_ID`, `pn`, `descriptioin`, `aircraft`, `ata`, `capability`, `pma`, `doa`, `der`, `code_oem`, `design_oem`, `id_company`,`entry_date`, `comments`) VALUES (NULL, '".$dataemp['Fld_Part_ID']."', '".$PN."', '".$Description."', '".$Aircraft."', '".$ATA."', '".$CAPABILITY."', '".$PMA."', '".$DOA."', '".$DER."', '".$Code_OEM."', '".$Design_OEM."', '2670', '2018-10-23', '".$comments."');";	
							$requete = mysql2_query($req);								
								
								}
				else {	
								$req="INSERT INTO `tbl_Parts` (`Fld_Part_ID`, `Fld_Part_Nbr`, `Fld_Part_Desc`) VALUES (NULL, '".$PN."', '".$Description."');"; 
								$requete = mysql2_query($req);
								$lastid=mysqli_insert_id($connection);
								
								
								$req2="INSERT INTO `tbl_capa_list` (`id_capa_list`, `Fld_Part_ID`, `pn`, `descriptioin`, `aircraft`, `ata`, `capability`, `pma`, `doa`, `der`, `code_oem`, `design_oem`, `id_company, `entry_date`, `comments`) VALUES (NULL, '".$lastid."', '".$PN."', '".$Description."', '".$Aircraft."', '".$ATA."', '".$CAPABILITY."', '".$PMA."', '".$DOA."', '".$DER."', '".$Code_OEM."', '".$Design_OEM."', '2670', '2018-10-23', '".$comments."');";	
							$requete2 = mysql2_query($req2);
					 }
								echo $req; 
								
								}
?>
   </tr>
<?php
   }
   fclose($fop);
?>
</table>
<?php
}
?>