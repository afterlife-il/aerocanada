<?php
$fop = fopen('csv/Current_Stock_AELS_20181022_EXTERNAL_STOCK.csv', 'r');
if($fop === false)
{
   // Ouverture du fichier échouée
   echo "ERREUR !!";
}
else
{
	include_once "conf.php";
?>
<table style="border: 1px solid black;">
<?php
	$compteur=0;
   $delimiter = ';'; // Ton séparateur de cellules
   while(($a = fgetcsv($fop, 0, $delimiter)) !== false) // Récupération d'une ligne
   {
	   $existvar=0;//var roy
	   $varoy=0;
	   $compteur++;
?>
   <tr>
<?php

      foreach($a as $val) // Parcours en boucle des cellules de la ligne
      {
		  $varoy++;
		  // $val=str_replace(' ','',$val);
?>

      <td style="border: 1px solid black;"><?php echo $compteur."-->".$varoy; ?> <?php echo $val; ?></td>
<?php

//*****tbl_Stock_external*************  Fld_Stock_ID	Fld_Part_ID	Fld_Part_SN	Fld_Supplier_ID	Fld_Entry_Date	Fld_Part_Price	Fld_Price_Currency_ID	Fld_BAX_PO_Nbr	Fld_Supplier_order_Date	Fld_Supplier_Payment_Date	Fld_Qty	Fld_Condition_ID	Fld_Release_ID	Fld_Tag_Info_ID	Fld_Tag_Date	Fld_Traceability_ID	Fld_Warehouse_Location	Fld_Physical_Stock	Fld_Owner_ID	Fld_Stock_Location_ID	Fld_Status_ID	Fld_Status_Ind	Fld_Status_Date	Fld_Stock_Remark	Fld_Shelf_Life_Limit	Fld_Valeur_Comptable	Fld_Valeur_Comptable_currency_Id	Fld_Sales_Remark	Fld_External_Location	Fld_Sales_Remark_ID	Fld_Warehouse_Location_ID	Fld_OriginalUnit_Stock_ID	Fld_Min_Qty	Fld_Publish	status  Fld_AC_ID    Fld_Company_ID

//Part Number;Alternate Part Number;Description;Condition;Quantity;;;;
		if ($varoy=='1') $Fld_Part_Nbr=$val;
		if ($varoy=='2') $Alternate_pn=$val;
		if ($varoy=='3') $FLD_PART_DESC=$val;
		if ($varoy=='4') $COND=$val;
		if ($varoy=='5') $QTY=$val;

		
		
		if(!empty($Alternate_pn)) $Fld_Stock_Remark ="ALTERNATE PN :".$Alternate_pn;
							
							//COMPANY Fld_Company_ID -- AIRCRAFT END-OF-LIFE SOLUTIONS (AELS)
							$Fld_Company_ID="5535";
							//FIN COMPANY Fld_Company_ID
      }
								//recuperation du part ID
								//****************************************************
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
								//****************************************************
											
								//Recuperation de la ID condition
								if (!empty($COND))
								{
								//tbl_Condition****  Fld_Condition_ID  Fld_Condition_Text
					            $sqlidcon="SELECT Fld_Condition_ID FROM tbl_Condition where Fld_Condition_Text Like '".$COND."'";
								
								$resultidcon = mysql2_query($sqlidcon);
								$nb_resultats_idcon = mysqli_num_rows($resultidcon);
								$dataidcon = mysqli_fetch_array($resultidcon);
									if (0<$nb_resultats_idcon) $Fld_Condition_ID=$dataidcon['Fld_Condition_ID'];
									else
										{
										$reqaddidcon="INSERT INTO `tbl_Condition` (`Fld_Condition_ID`, `Fld_Condition_Text`) VALUES (NULL, '".$COND."');";
										$requetaddidcon = mysql2_query($reqaddidcon);
										$Fld_Condition_ID=mysqli_insert_id($connection);
										}
								}
							
					            //Fin Recuperation de la ID condition
								
								
								
								// $today = date("Y-m-d");
								$today = "2018-10-22";
											
								$req="INSERT INTO `tbl_Stock_external` (`Fld_Stock_externe_ID`, `Fld_Part_ID`, `Fld_Part_SN`, `Fld_Supplier_ID`, `Fld_Entry_Date`, `Fld_Part_Price`, `Fld_Price_Currency_ID`, `Fld_BAX_PO_Nbr`, `Fld_Supplier_order_Date`, `Fld_Supplier_Payment_Date`, `Fld_Qty`, `Fld_Condition_ID`, `Fld_Release_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Traceability_ID`, `Fld_Warehouse_Location`, `Fld_Physical_Stock`, `Fld_Owner_ID`, `Fld_Stock_Location_ID`, `Fld_Status_ID`, `Fld_Status_Ind`, `Fld_Status_Date`, `Fld_Stock_Remark`, `Fld_Shelf_Life_Limit`, `Fld_Valeur_Comptable`, `Fld_Valeur_Comptable_currency_Id`, `Fld_Sales_Remark`, `Fld_External_Location`, `Fld_Sales_Remark_ID`, `Fld_Warehouse_Location_ID`, `Fld_OriginalUnit_Stock_ID`, `Fld_Min_Qty`, `Fld_Publish`, `status`, `Fld_AC_ID`, `Fld_Company_ID`) VALUES ('', '".$Fld_Part_ID."', NULL, NULL, '".$today."', NULL, NULL, NULL, NULL, NULL, '".$QTY."', '".$Fld_Condition_ID."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '".$Fld_Stock_Remark."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '".$Fld_Company_ID."');"; 
								echo $req."<br><br>";
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