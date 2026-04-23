<?php
$fop = fopen('csv/Sabena_Technics_Capa_MAI_2018.csv', 'r');
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
	$existpasvar=0;//var roy
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

//PN1;P/N;D‚signation;Intervention BOD;Intervention DNR;Intervention SIN;OEM   Approved Center;Conformit‚ NFL 00015;EASA Form 1 ;CAA  (THAIL.);FAA;TCCA;GACA ; Form  INDONES.;Form CAAC-038 (CHINA);JCAB Form (JAPAN);FRA 145;HELICO;ALOUETTE;DAUPHIN/PANTHER;ECUREUIL/FENNEC;PUMA;SUPER PUMA;TIGRE;AIRBUS;A300;A310;A300-600;A318;A319;A320;A321;A330;A340;BOEING;B707;B727;B737;B737NG;B747;B757;B767;B777;DC10;MD80;KC 135;ATR;ATR 42;ATR 42-500;ATR 42-300/400;ATR 72;ATR 600;EMBRAER;EMBRAER 110;EMBRAER  120;XINGU/EMBRAER 121;EMBRAER RJ135;EMBRAER  RJ145;EMBRAER RJ170/190;DASSAULT;ALPHA-JET;ATLANTIQUE 2;FALCON 10;FALCON 20;FALCON 50;FALCON 200;FALCON 900;FALCON 900EX;FALCON 7X;RAYTHEON;BEECH 90;BEECH 99;BEECH 100;BEECH 200;KING 200;BOMBARDIER;BD700;DH8;CHALLENGER;CRJ;CL415;CASA;CN235;SAAB;SAAB340;DORNIER;DO328JET;DO328;VARIOUS;CESSNA;C130;C160;N262;BAE/RJ;DEHAVILLAND;TRACKER;FOKKER;FOKKER 100;FOKKER 27;FOKKER 28;FOKKER 70;BOD;DNR;SIN;STRUCTURE;


		if ($varoy=='1') $alt_pn=$val;
		if ($varoy=='2') $Fld_Part_Nbr=$val;
		if ($varoy=='3') $FLD_PART_DESC=addslashes($val);
		// if ($varoy=='4') $nha=$val;
		// if ($varoy=='5') $Fld_Part_MFG=$val;
		// if ($varoy=='6') $Fld_Part_MFG_Old=$val;
		// if ($varoy=='7') $Fld_AC_ID=$val;

		// if ($varoy=='9') $Fld_Old_LP=$val;
		// if ($varoy=='10') $Fld_Part_List_Price=$val;
		// if ($varoy=='11') $Fld_Part_Price_Currency_ID=$val;
		// if ($varoy=='12') $Fld_Part_LP_Date=$val;
		// if ($varoy=='13') $Fld_Remark=$val;
		// if ($varoy=='14') $status=$val;
		// if ($varoy=='15') $alt_pn=$val;
		// if ($varoy=='16') $Fld_Add_PN_Date=$val;
		// if ($varoy=='17') $aci_contact_entry=$val;
		// if ($varoy=='18') $ata_chapter=$val;
		// if ($varoy=='19') $cage_code=$val;
		// if ($varoy=='20') $essentiality_category_id=$val;
		// if ($varoy=='21') $=$val;
		// if ($varoy=='22') $moq=$val;

      }
					if(!empty($Fld_Part_Nbr)){

											//Verification si le pn se trouve dans la table tbl_Parts
					                        $sqlemp="SELECT * FROM tbl_Parts where Fld_Part_Nbr='".$Fld_Part_Nbr."'";
											echo $sqlemp;
											
											$result = mysql2_query($sqlemp);
											// $dataemp = mysqli_fetch_array($reqemp);
											$nb_resultats = mysqli_num_rows($result);
					                        //Fin Verification si le pn se trouve dans la table tbl_Parts
											
								if (0<$nb_resultats) {	
								// if(!empty($cage_code)){
								$existvar++;
								echo $existvar;
								echo "exist<br><br>";		
// $req="update tbl_Parts set cage_code='".$cage_code."' where Fld_Part_Nbr='".$Fld_Part_Nbr."'";
// echo $req."<br><br>"; 
// $requete = mysql2_query($req);								
								// }
								}
								else {
									$existpasvar++;
								echo "<br>New:".$existpasvar."<br>";
									
// $req="INSERT INTO `tbl_Parts` (`Fld_Part_ID`, `Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`, `cage_code`, `essentiality_category_id`, `nha`, `moq`) VALUES (NULL, '".$Fld_Part_Nbr."', '".$FLD_PART_DESC."', '', '".$Fld_Part_MFG_Old."', '".$Fld_AC_ID."', '".$Fld_Old_LP."', '".$Fld_Part_List_Price."', '".$Fld_Part_Price_Currency_ID."', '".$Fld_Part_LP_Date."', '".$Fld_Remark."', 'Available', '".$alt_pn."', '".$Fld_Add_PN_Date."', '".$aci_contact_entry."', '".$ata_chapter."', '".$cage_code."', '".$essentiality_category_id."', '".$nha."', '".$moq."');"; 
					$req="INSERT INTO `tbl_Parts` (`Fld_Part_ID`, `Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`, `cage_code`, `essentiality_category_id`, `nha`, `moq`) VALUES (NULL, '".$Fld_Part_Nbr."', '".$FLD_PART_DESC."', '', '', '', '', '', '', '', '', 'Available', '".$alt_pn."', '', '', '', '', '', '', '');";				
					echo $req."<br><br>"; 
					$requete = mysql2_query($req);
								}
								
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