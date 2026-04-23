<?php
include "conf.php";
//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code

/************************************
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/

											$sql="SELECT * FROM tb_company";
											mysql_query("SET NAMES 'utf8'");
											//echo $sqldiv;
											$req = mysql_query($sql);
											while($data = mysql_fetch_array($req))
											{
												$sql2="SELECT * FROM tbl_Company_Details where Fld_Company_ID='".$data['Fld_Company_ID']."'";	
												$req2 = mysql_query($sql2);
												$nbrows = mysql_num_rows($req2);
												if($nbrows==FALSE){
													$req3="INSERT INTO tbl_Company_Details (`id_tbl_company_Details`,`Fld_Linked_ID`, `Fld_Company_ID`, `Company_Old_Id`, `Fld_Company_Type_ID`, `Fld_Company_Country`, `Fld_Company_City`, `Fld_Company_State`, `Fld_Company_Street`, `Fld_Company_ZipCode`, `Fld_Company_Fax`, `Fld_Company_Phone`, `Fld_Company_Email`, `Fld_Company_Score`, `Fld_Company_BAX_Contact`, `Fld_Remark`, `Fld_VAT_Nbr`, `Fld_Date_Of_First_Contact`, `Fld_Company_Address_Type`, `UTC_timezone`, `title_address`)
													VALUES ('','','".$data['Fld_Company_ID']."','','','','NO CITY','','NO ADDRESS','','','','','','','','','','','','')";
													$requete = mysql_query($req3);
													
												}
											}
?>