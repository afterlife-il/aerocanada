<?php
	include_once "conf.php";
include_once "page_titles.php";

					                        $sql="SELECT * FROM tbl_RFQ_1";
											
											$result = mysql2_query($sql);
											while($row = mysqli_fetch_array($result))
											{
													 $sqlemp="SELECT * FROM tbl_Parts where Fld_Part_Nbr='".$row['pn_rfq']."'";
													 $resultemp = mysql2_query($sqlemp);
													 $nb_resultats = mysqli_num_rows($resultemp);
													 
													 if (0<$nb_resultats) echo "ok";
													
													else
														{
																
																$reqapn="INSERT INTO tbl_Parts (`Fld_Part_ID`,`Fld_Part_Nbr`, `Fld_Part_Desc`, `Fld_Part_MFG`, `Fld_Part_MFG_Old`, `Fld_AC_ID`, `Fld_Old_LP`, `Fld_Part_List_Price`, `Fld_Part_Price_Currency_ID`, `Fld_Part_LP_Date`, `Fld_Remark`, `status`, `alt_pn`, `Fld_Add_PN_Date`, `aci_contact_entry`, `ata_chapter`)
																 VALUES ('".$row['Fld_Part_ID']."','".$row['pn_rfq']."','".$row['description_rfq']."','','','','','','','".date("Y")."','','Available','','".date("Y-m-d")."','','');";
																 $requete = mysql2_query($reqapn);
														}
											}
	
											
											

					                       

							


											
?>
