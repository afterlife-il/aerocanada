<?php

$id_tbl_company_Details=$_GET['id'];
include_once "conf.php";
include_once "page_titles.php";

// Table tbl_Company_Details
// *************************************
// id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address

// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where id_tbl_company_Details=".$id_tbl_company_Details;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>

<form id="formmodifcontact" name="formmodifcontact" method="post" action="gestion_address_company.php">


                               
								<?php
					$r=0;
					$data = mysqli_fetch_array($req);
					
					$r++;
					//recuperation nom employee
					$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Fld_Company_BAX_Contact'];
					
					$reqemp = mysql2_query($sqlemp);
					$dataemp = mysqli_fetch_array($reqemp);
					//Fin recuperation nom employee
					
					//recuperation de l'heure utc de l'adresse de la compagnie	
					$triggerOn="";
					if(!empty($data['UTC_timezone'])){
					$triggerOn=date("Y-m-d h:iA");				
					$schedule_date = new DateTime($triggerOn, new DateTimeZone('UTC') );
					$schedule_date->setTimeZone(new DateTimeZone($data['UTC_timezone']));
					$triggerOn =  $schedule_date->format('H:iA d/m/Y');
					$localtimecompany =  $schedule_date->format('H:iA');
					}
					//Fin recuperation de l'heure utc de l'adresse de la compagnie							
					//verification heure ouverture je verifie si l'heure du pays de la compagnie est entre 8h du matin et 20h du soir
					$current_time = $localtimecompany;
					$sunrise = "8:00AM";
					$sunset = "20:00PM";
					$date1 = DateTime::createFromFormat('H:i a', $current_time);
					$date2 = DateTime::createFromFormat('H:i a', $sunrise);
					$date3 = DateTime::createFromFormat('H:i a', $sunset);
					if ($date1 > $date2 && $date1 < $date3)
					{
					$couleur_horaire="green";
					}
					else $couleur_horaire="red";
					//Fin verification heure ouverture


					
					echo "<div class='row'>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Address Type</label><select class=\"form-control\" name=\"Fld_Company_Address_Type".$r."\">";
											
											//recuperation des types de compagnie
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqltypec="SELECT * FROM tbl_Division";	
					                        $reqtypec = mysql2_query($sqltypec);
					                        while($datatypec= mysqli_fetch_array($reqtypec)){
												echo "<option value='".$datatypec['Fld_Division_ID']."'";
												if($datatypec['Fld_Division_ID']==$data['Fld_Company_Address_Type']) echo " selected";
												echo ">".$datatypec['Fld_Division_Text']."</option>";
											}
												
					                        //Fin recuperation des type de compagnie
										
                                                
                    echo "</select></div></div>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Address Title</label><input class=\"form-control\" type='text' name='title_address".$r."' value='".$data['title_address']."'></div></div>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Street</label><input class=\"form-control\" type='text' name='Fld_Company_Street".$r."' value='".$data['Fld_Company_Street']."'></div></div>";
					
					
					echo "</div>";
					echo "<div class='row'>";
					
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>City</label><input class=\"form-control\" type='text' name='Fld_Company_City".$r."' value='".$data['Fld_Company_City']."'></div></div>";
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>Zip Code</label><input class=\"form-control\" type='text' name='Fld_Company_ZipCode".$r."' value='".$data['Fld_Company_ZipCode']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Country</label><input class=\"form-control\" type='text' name='Fld_Company_Country".$r."' value='".$data['Fld_Company_Country']."'></div>";
					echo "</div>";
					
					echo "</div>";
					echo "<div class='row'>";
					
					echo "<div class='col-lg-4'>";
					echo "<div class='form-group'><label>State</label><input class=\"form-control\" type='text' name='Fld_Company_State".$r."' value='".$data['Fld_Company_State']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>PHONE</label><input class=\"form-control\" type='text' name='Fld_Company_Phone".$r."' value='".$data['Fld_Company_Phone']."'></div>";
					echo "</div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>E-MAIL</label><input class=\"form-control\" type='text' name='Fld_Company_Email".$r."' value='".$data['Fld_Company_Email']."'></div>";
					echo "</div>";
					
					echo "</div>";
					echo "<div class='row'>";
					
					echo "<div class='col-lg-4'><div class='form-group'><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr".$r."' value='".$data['Fld_VAT_Nbr']."'></div>";
					echo "</div>";
					echo "<div class='col-lg-8'><div class='form-group'><label>Remark</label><br><textarea name='Fld_Remark".$r."' style='WIDTH: 100%; height:100px;'>".$data['Fld_Remark']."</textarea>
					</div>";
					echo "</div>";
					
					echo "</div>";
					echo "<div class='row'>";
					
					
					echo "<div class='col-lg-8'></div><div class='col-lg-4'><div class='form-group'><input type=\"hidden\" name=\"nbaddcompany\" id=\"nbaddcompany\" value=\"".$r."\"><input type=\"hidden\" name=\"id_tbl_company_Details".$r."\" id=\"id_tbl_company_Details\" value='".$data['id_tbl_company_Details']."'><input type='hidden' name='Fld_Company_Type_ID".$r."' value='".$data['Fld_Company_Type_ID']."'><button type='submit' class='btn btn-default'>Submit Button</button></div>";
					echo "</div>";
					
					echo "</div>";
					
			?> 
                               
                           <input type="hidden" name="Fld_Company_ID" value="<?php echo $data['Fld_Company_ID'];?>">
						</form>
<?php		
}
			//**************************************************************Fin Details Company****************************
			
		