<?php
$fop = fopen('csv/2017-12-11-PN-for-DB.csv', 'r');
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
	
   $delimiter = ';'; // Ton séparateur de cellules
   while(($a = fgetcsv($fop, 0, $delimiter)) !== false) // Récupération d'une ligne
   {
	   $existvar=0;//var roy
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

//1 Fld_Part_ID	2 Fld_Part_Nbr	3 FLD_PART_DESC	4 NHA (Next Higher Assy)	5 Fld_Part_MFG	6 Fld_Part_MFG_Old	7 Fld_AC_ID	8 Fld_AC_ID	9 Fld_Old_LP	10 Fld_Part_List_Price	11 Fld_Part_Price_Currency_ID	12 Fld_Part_LP_Date	13 Fld_Remark	14 status	15 alt_pn	16 Fld_Add_PN_Date	17 aci_contact_entry	18 ata_chapter	19 CAGE#	20 Essentiality Category, 1=1; 2= 2 3= 3	21 OEM	22 MOQ


		if ($varoy=='1') $Fld_Part_ID=$val;
		if ($varoy=='2') $Fld_Part_Nbr=$val;
		if ($varoy=='3') $FLD_PART_DESC=$val;
		if ($varoy=='4') $nha=$val;
		if ($varoy=='5') $Fld_Part_MFG=$val;
		if ($varoy=='6') $Fld_Part_MFG_Old=$val;
		if ($varoy=='7') $Fld_AC_ID=$val;

		if ($varoy=='9') $Fld_Old_LP=$val;
		if ($varoy=='10') $Fld_Part_List_Price=$val;
		if ($varoy=='11') $Fld_Part_Price_Currency_ID=$val;
		if ($varoy=='12') $Fld_Part_LP_Date=$val;
		if ($varoy=='13') $Fld_Remark=$val;
		if ($varoy=='14') $status=$val;
		if ($varoy=='15') $alt_pn=$val;
		if ($varoy=='16') $Fld_Add_PN_Date=$val;
		if ($varoy=='17') $aci_contact_entry=$val;
		if ($varoy=='18') $ata_chapter=$val;
		if ($varoy=='19') $cage_code=$val;
		if ($varoy=='20') $essentiality_category_id=$val;
		// if ($varoy=='21') $=$val;
		if ($varoy=='22') $moq=$val;

      }
					if(!empty($Fld_Part_Nbr)){

											//Verification si le pn se trouve dans la table tbl_Parts
					                        $sqlemp="SELECT * FROM tbl_Parts where Fld_Part_Nbr='".$Fld_Part_Nbr."'";
											echo $sqlemp."<br>";
											
											$result = mysql2_query($sqlemp);
											// $dataemp = mysqli_fetch_array($reqemp);
											$nb_resultats = mysqli_num_rows($result);
					                        //Fin Verification si le pn se trouve dans la table tbl_Parts
											
								if (0<$nb_resultats) {									
								$existvar++;
								echo $existvar;
								echo "exist ";	
								if(!empty($cage_code)){								
								$req.$existvar="update tbl_Parts set cage_code='".$cage_code."' where Fld_Part_Nbr='".$Fld_Part_Nbr."'";		
								echo $req.$existvar."<br><br>";	
								$requete = mysql2_query($req.$existvar);									
								}
								}
								else {
									
								$req2="INSERT INTO `tbl_Parts` (`Fld_Part_ID`, `Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`, `cage_code`, `essentiality_category_id`, `nha`, `moq`) VALUES (NULL, '".$Fld_Part_Nbr."', '".$FLD_PART_DESC."', '', '".$Fld_Part_MFG_Old."', '".$Fld_AC_ID."', '".$Fld_Old_LP."', '".$Fld_Part_List_Price."', '".$Fld_Part_Price_Currency_ID."', '".$Fld_Part_LP_Date."', '".$Fld_Remark."', 'Available', '".$alt_pn."', '".$Fld_Add_PN_Date."', '".$aci_contact_entry."', '".$ata_chapter."', '".$cage_code."', '".$essentiality_category_id."', '".$nha."', '".$moq."');"; 
									echo $req2."<br><br>";
									$requete = mysql2_query($req2);	

								}
								 

								}
								$Fld_Part_ID="";
								$Fld_Part_Nbr="";
								$FLD_PART_DESC="";
								$nha="";
								$Fld_Part_MFG="";
								$Fld_Part_MFG_Old="";
								$Fld_AC_ID="";
								$Fld_Old_LP="";
								 $Fld_Part_List_Price="";
								 $Fld_Part_Price_Currency_ID="";
								 $Fld_Part_LP_Date="";
								 $Fld_Remark="";
								 $status="";
								 $alt_pn="";
								 $Fld_Add_PN_Date="";
								 $aci_contact_entry="";
								 $ata_chapter="";
								 $cage_code="";
								 $essentiality_category_id="";
								 $moq="";
								
								
								
								
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