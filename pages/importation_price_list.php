<?php
$fop = fopen('csv/CORE_INV.csv', 'r');
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
	
   $delimiter = ','; // Ton séparateur de cellules
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
		  // $val=str_replace(' ','',$val);
?>

      <td style="border: 1px solid black;"><?php echo $varoy; ?> <?php echo $val; ?></td>
<?php

//*****tbl_price_list*************  id  CMM_Reference  pn  Description  brand  model  Sales_Unit  Lead_Time_Days  MOQ  Unit_price  Currency  company

//Part Number,Part Description,Cond,Qty
		if ($varoy=='1') $Fld_Part_Nbr=$val;
		if ($varoy=='2') $FLD_PART_DESC=$val;
		if ($varoy=='3') $COND=$val;
		if ($varoy=='4') $QTY=$val;


      }
											//recuperation du part ID
											if(!empty($Fld_Part_Nbr)){

											//Verification si le pn se trouve dans la table tbl_Parts
					                        $sqlemp="SELECT * FROM tbl_Parts where Fld_Part_Nbr='".$Fld_Part_Nbr."'";
											
											$result = mysql2_query($sqlemp);
											$nb_resultats = mysqli_num_rows($result);
					                        //Fin Verification si le pn se trouve dans la table tbl_Parts
											$row = mysqli_fetch_array($result);
											
											if (0<$nb_resultats) $Fld_Part_ID=$row['Fld_Part_ID'];
											else {
												$reqapn="INSERT INTO tbl_Parts (`Fld_Part_ID`,`Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`)
												VALUES ('','".$Fld_Part_Nbr."','".$FLD_PART_DESC."','','','','','','','".date("Y")."','','Available','','".date("Y-m-d")."','6','');";
												$requete = mysql2_query($reqapn);
												$Fld_Part_ID=mysqli_insert_id($connection);
												
											}
											
											}
											//Fin recuperation du part ID
											
											//Recuperation de la ID condition
											//tbl_Condition****  Fld_Condition_ID  Fld_Condition_Text
					                        $sqlidcon="SELECT Fld_Condition_ID FROM tbl_Condition where Fld_Condition_Text Like '".$COND."'";
											
											$resultidcon = mysql2_query($sqlidcon);
											$dataidcon = mysqli_fetch_array($resultidcon);
					                        //Fin Recuperation de la ID condition
											
											
											$today = date("Y-m-d");
											
								$req="INSERT INTO `tbl_price_list` (`id`, `CMM_Reference`, `pn`, `Description`, `brand`, `model`, `Sales_Unit`, `Lead_Time_Days`, `MOQ`, `Unit_price`, `Currency`, `company`) VALUES ('', '', '".$Fld_Part_Nbr."', '".$FLD_PART_DESC."', '', '', NULL, NULL, NULL, NULL, NULL, '');"; 
									// echo $req."<br><br>";
									$requete = mysql2_query($req);							
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