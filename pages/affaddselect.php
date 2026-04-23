<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){

// echo $_GET['id'];


		
								/*
								Table tbl_Company_Details
								*************************************
								id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
								*/
								// getting total number records without any search
								$sql="SELECT * FROM tbl_Company_Details where id_tbl_company_Details='".$_GET['id']."'";	
								
								$req = mysql2_query($sql);
								$data = mysqli_fetch_array($req);
								
											//recuperation address type
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqltypec="SELECT Fld_Division_Text FROM tbl_Division where Fld_Division_ID='".$data['Fld_Company_Address_Type']."'";
											
					                        $reqtypec = mysql2_query($sqltypec);
					                        $datatypec= mysqli_fetch_array($reqtypec);
											//End recuperation address type
								echo "
								 <table class=\"table table-striped table-bordered table-hover\" id=\"tableaddressecompany\">
                                <thead>
                                    <tr>
                                        <td><b>Address Type</b></td><td>".$datatypec['Fld_Division_Text']."</td></tr>
                                        <tr><td><b>Address Title</b></td><td>".$data['title_address']."</td></tr>
                                        <tr><td><b>Street</b></td><td>".$data['Fld_Company_Street']."</td></tr>
                                        <tr><td><b>City</b></td><td>".$data['Fld_Company_City']."</td></tr>
                                        <tr><td><b>Zip Code</b></td><td>".$data['Fld_Company_ZipCode']."</td></tr>
                                        <tr><td><b>State</b></td><td>".$data['Fld_Company_State']."</td></tr>
                                        <tr><td><b>Country</b></td><td>".$data['Fld_Company_Country']."</td></tr>
                                        <tr><td><b>PHONE</b></td><td>".$data['Fld_Company_Phone']."</td></tr>
                                        <tr><td><b>E-MAIL</b></td><td>".$data['Fld_Company_Email']."</td></tr>
										<tr><td><b>VAT Nbr</b></td><td>".$data['Fld_VAT_Nbr']."</td></tr>
								</table>		
								";
							

}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>