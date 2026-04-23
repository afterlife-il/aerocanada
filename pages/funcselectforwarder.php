<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){

// echo $_GET['id'];


		
								
								$sql="SELECT tbl_Forwarder.*,tbl_Shipper.* FROM tbl_Forwarder,tbl_Shipper where tbl_Forwarder.Fld_Shipper_ID=tbl_Shipper.Fld_Shipper_ID AND tbl_Forwarder.Fld_Linked_ID='".$_GET['id']."'";	
								
								$req = mysql2_query($sql);
								$data = mysqli_fetch_array($req);
								
								
								
								echo "
								 <table class=\"table table-striped table-bordered table-hover\" id=\"tableaddressecompany\">
                                <thead>
                                    <tr>
                                        <td><b>SHIPPER</b></td><td>".$data['Fld_Shipper_Text']."</td></tr>
                                        <tr><td><b>SHIPPER CONTAC NAME</b></td><td>".$data['Fld_Shipper_Contact_Name_Forw']."</td></tr>
                                        <tr><td><b>SHIPPER CONTAC PHONE</b></td><td>".$data['Fld_Shipper_Contact_Phone_Forw']."</td></tr>
                                        <tr><td><b>ACCOUNT #</b></td><td>".$data['Fld_Account_Nbr']."</td></tr>
                                        <tr><td><b>REMARK</b></td><td>".$data['Fld_Remark']."</td></tr>
								</table>		
								";

}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>