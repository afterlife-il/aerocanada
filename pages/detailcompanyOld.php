<?php
session_start();
$id_company=$_GET['id'];
include_once "conf.php";
include_once "page_titles.php";
require('../classes/company.class.php');


//recuperation du nom de la compagnie
//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code
	$sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$id_company."'";
	
	$reqcomn = mysql2_query($sqlcomn);
	$datacn = mysqli_fetch_array($reqcomn);
	$companyname = strtoupper($datacn['Fld_Company_Name']);
//Fin recuperation du nom de la compagnie

//recuperation de la date de premier contact
	$sqlrdpc="SELECT Fld_Date_Of_First_Contact FROM tbl_Company_Details where Fld_Company_ID='".$id_company."'";
	
	$reqrdpc = mysql2_query($sqlrdpc);
	$datardpc = mysqli_fetch_array($reqrdpc);
	$FldDateOfFirstContact = $datardpc['Fld_Date_Of_First_Contact'];
//Fin recuperation de la date de premier contact
?>

			
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                             <h1><?php echo $companyname;?></h1>
							 <?php if(!empty($FldDateOfFirstContact)) echo "First contact : ".$FldDateOfFirstContact;
							 echo "<br><a href='modif_company.php?Fld_Company_ID=".$id_company."' style='decoration:none;color:white;' title='Modification Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa  fa-pencil-square-o'></i>
					    </a>
						<a href='ajout_contact_company.php?Fld_Company_ID=".$id_company."' style='decoration:none;color:white;' title='Add Contact Company'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa glyphicon-plus'></i>
					    </a>
						<a href='archive_company.php?Fld_Company_ID=".$id_company."' onClick=\"return(confirm('Etes vous sur ?'));\" style='decoration:none;color:white;'  title='Archive Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa  fa-archive'></i>
					    </a>";
							 
							 
							 ?>
							 <div style='text-align:left;'><a href='javascript:fermeturedetailcompany()'><img src='../images/Fermeture.png' width='30'></a></div>
                        </div>
                        <!-- .panel-heading -->
                        <div class="panel-body">
                            <div class="panel-group" id="accordion">


<?php
//enregistrement maakav sur company
$today=date("Y-m-d");
$heuretoday=date("g:i a");
$requete = mysql2_query("INSERT INTO `tbl_maakav_company` (`id_maakav_company`, `id_company`, `datecomplete`, `heurevisite`, `id_Employee`) VALUES (NULL, '".$id_company."', '".$today."', '".$heuretoday."', '".$_SESSION['id_utilisateur']."');");
//Fin enregistrement maakav sur company
?>
<!--*********************************************************************GENERAL INFORMATION ABOUT THE COMPANY***********************************************-->
<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">GENERAL INFORMATION ABOUT THE COMPANY</a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse in">
									
									
                                        <div class="panel-body">
<?php
/************************************
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>
<div id="improvementsPanel" class="panel-collapse collapse in" aria-expanded="true">
<form id="formgeneralinfo" name="formgeneralinfo" method="post" action="modif_info_general_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
 <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                               
                                <tbody>
								<?php
		
					$data = mysqli_fetch_array($req);
					
				
					//recuperation nom employee
					$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Fld_Company_BAX_Contact'];
					
					$reqemp = mysql2_query($sqlemp);
					$dataemp = mysqli_fetch_array($reqemp);
					//Fin recuperation nom employee

					
					echo "<tr>";
					echo "<td><label>Company Name</label><input class=\"form-control\" type='text' name='Fld_Company_Name' value='".$datacn['Fld_Company_Name']."'></td>";
					echo "<td><label>Company Type</label>
					<select class=\"form-control\" name=\"Fld_Company_Type_ID\">";
											
											//recuperation des types de compagnie
					                        $sqlctt="SELECT distinct(Fld_Company_Type_Text),Fld_Company_Type_ID FROM tbl_Company_Type";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Company_Type_ID']."'";
												if ($datactt['Fld_Company_Type_ID']==$data['Fld_Company_Type_ID']) echo " selected";
												echo ">".$datactt['Fld_Company_Type_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
										
                                                
                    echo "</select></td>";
					echo "<td><label>Website</label><input class=\"form-control\" type='text' name='internet' value='".$datacn['internet']."'></td>";
					echo "<td><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr' value='".$data['Fld_VAT_Nbr']."'></td>";
					echo "<td><label>ACI 770 Contact</label>
					<select class=\"form-control\" name=\"Employee_ID\">";
					//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp ['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$datacn['aci_contact']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
					echo "</select></td>";
					echo "<td><label>CAGE CODE #</label><input class=\"form-control\" type='text' name='cage_code' value='".$datacn['cage_code']."'></td>";
					echo "<td><br><input type='submit' value='ok' name='valform' class=\"btn btn-default\"></td>";
					echo "</tr>";
					
			?> 
                                </tbody>
                            </table>
						</form>
<?php		
}
else echo "Pas de reponse";
			?>
										</div>
								
								</div>
								
                                    </div>
                                </div>
<!--*************************************************END GENERAL INFORMATION ABOUT THE COMPANY*****************************-->

<!--*********************************************************************GENERAL INFORMATION ABOUT THE COMPANY TEST***********************************************-->
<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">GENERAL INFORMATION ABOUT THE COMPANY TEST</a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse in">
									
									
                                        <div class="panel-body">
<?php
/************************************
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>
	
                                
<form id="formgeneralinfo" name="formgeneralinfo" method="post" action="modif_info_general_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
                               
      
								<?php
		
					$data = mysqli_fetch_array($req);
					
				
					//recuperation nom employee
					$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['Fld_Company_BAX_Contact'];
					
					$reqemp = mysql2_query($sqlemp);
					$dataemp = mysqli_fetch_array($reqemp);
					//Fin recuperation nom employee

                    echo "<div class='row'>";
					echo "<div class='col-lg-2'><div class='form-group'><label>Company Name</label><input class=\"form-control\" type='text' name='Fld_Company_Name' value='".$datacn['Fld_Company_Name']."'></div></div>";
					echo "<div class='col-lg-2'><div class='form-group'><label>Company Type</label>
					<select class=\"form-control\" name=\"Fld_Company_Type_ID\">";
											
											//recuperation des types de compagnie
					                        $sqlctt="SELECT distinct(Fld_Company_Type_Text),Fld_Company_Type_ID FROM tbl_Company_Type";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Company_Type_ID']."'";
												if ($datactt['Fld_Company_Type_ID']==$data['Fld_Company_Type_ID']) echo " selected";
												echo ">".$datactt['Fld_Company_Type_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
										
                                                
                    echo "</select></div></div>";
					echo "<div class='col-lg-2'><div class='form-group'><label>Website</label><input class=\"form-control\" type='text' name='internet' value='".$datacn['internet']."'></div></div>";
					echo "<div class='col-lg-2'><div class='form-group'><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr' value='".$data['Fld_VAT_Nbr']."'></div></div>";
					echo "<div class='col-lg-2'><div class='form-group'><label>ACI 770 Contact</label>
					<select class=\"form-control\" name=\"Employee_ID\">";
					//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp ['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$datacn['aci_contact']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
					echo "</select></div></div>";
					echo "<div class='col-lg-2'><div class='form-group'><label>CAGE CODE #</label><input class=\"form-control\" type='text' name='cage_code' value='".$datacn['cage_code']."'></div></div>";
					echo "</div>";
					echo "<div class='row'>";
					echo "<div class='col-lg-11'></div>";
					echo "<div class='col-lg-1'><button type='submit' class='btn btn-default'>Submit Button</button></div>";
					echo "</div>";
					
			?> 
                              
                            
						</form>
<?php		
}
else echo "Pas de reponse";
			?>
										
								
								
								
								</div>
								
                                    </div>
                                </div>
<!--*************************************************END GENERAL INFORMATION ABOUT THE COMPANY TEST*****************************-->


<!--**************************************************************DETAILS COMPANY****************************-->
							<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">COMPANY ADDRESS</a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="panel-collapse collapse">
									
									
                                        <div class="panel-body">
<?php
/*
Table tbl_Company_Details
*************************************
id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
	
?>
<div id="improvementsPanel" class="panel-collapse collapse in" aria-expanded="true">
<a href="javascript:addaddresscompany()"> + Add A ADDRESS</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="gestion_address_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">

 <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                               
                                <tbody>
								<?php
					$r=0;
					while ($data = mysqli_fetch_array($req))
					{ 
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


					
					echo "<tr>";
					echo "<td><label>Address Type</label><select class=\"form-control\" name=\"Fld_Company_Address_Type".$r."\">";
											
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
										
                                                
                    echo "</select></td>";
					echo "<td><label>Address Title</label><input class=\"form-control\" type='text' name='title_address".$r."' value='".$data['title_address']."'></td>";
					echo "<td><label>Street</label><input class=\"form-control\" type='text' name='Fld_Company_Street".$r."' value='".$data['Fld_Company_Street']."'></td>";
					echo "<td><label>City</label><input class=\"form-control\" type='text' name='Fld_Company_City".$r."' value='".$data['Fld_Company_City']."'></td>";
					echo "<td><label>Zip Code</label><input class=\"form-control\" type='text' name='Fld_Company_ZipCode".$r."' value='".$data['Fld_Company_ZipCode']."'></td>";
					echo "<td><label>State</label><input class=\"form-control\" type='text' name='Fld_Company_State".$r."' value='".$data['Fld_Company_State']."'></td>";
					
					
					
					echo "</tr><tr>";
					echo "<td><label>Country</label><input class=\"form-control\" type='text' name='Fld_Company_Country".$r."' value='".$data['Fld_Company_Country']."'></td>";
					
					echo "<td><label>PHONE</label><input class=\"form-control\" type='text' name='Fld_Company_Phone".$r."' value='".$data['Fld_Company_Phone']."'></td>";
					echo "<td><label>E-MAIL</label><input class=\"form-control\" type='text' name='Fld_Company_Email".$r."' value='".$data['Fld_Company_Email']."'></td>";
					echo "<td><label>Remark</label><input class=\"form-control\" type='text' name='Fld_Remark".$r."' value='".$data['Fld_Remark']."'></td>";
					echo "<td><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr".$r."' value='".$data['Fld_VAT_Nbr']."'></td>";
					echo "<td><label>Date 1st Contact</label><br>".$data['Fld_Date_Of_First_Contact']."</td>";
					
					echo "<td><input type=\"hidden\" name=\"nbaddcompany\" id=\"nbaddcompany\" value=\"".$r."\"><input type=\"hidden\" name=\"id_tbl_company_Details".$r."\" id=\"id_tbl_company_Details\" value='".$data['id_tbl_company_Details']."'><input type='hidden' name='Fld_Company_Type_ID".$r."' value='".$data['Fld_Company_Type_ID']."'><input type='submit' value='ok' name='valform".$r."' class=\"btn btn-default\"></td>";
					echo "</tr>";
					}
			?> 
                                </tbody>
                            </table>
							
						</form>
<?php		
}
else echo "Pas de reponse";
//**************************************************************Fin Details Company****************************
			?>
										</div>
								
								</div>
								
                                    </div>
                                </div>
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">CONTACT</a>
                                        </h4>
                                    </div>
                                    <div id="collapseThree" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php
//*****************************************************************CONTACT COMPANY
/*Table tb_company_contact
*************************************
id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone#2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
*/

$sql="SELECT * FROM tb_company_contact where Fld_Company_ID=".$id_company." and status='Available' ORDER BY Fld_Contact_Name";
?>
<a href="javascript:addcontactcompany()"> + Add A COMPANY CONTACT</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_contact_company_multi.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
 <table width="100%" class="table table-striped table-bordered table-hover" id="mytable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Phone 2</th>
                                        <th>Fax</th>
                                        <th>Mobile</th>
                                        <th>Division</th>
                                        <th>E-mail</th>
                                        <th>Title</th>
                                        <th>Remark</th>
										<th></th>
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$x=0;
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
						$x++;
						
						//recuperation des informations utilisateurs / employee
						//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype  numformat
						$sqliue="SELECT numformat FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
						
						//echo $sqldiv;
						$reqiue = mysql2_query($sqliue);
						$dataiue = mysqli_fetch_array($reqiue);
						//Fin recuperation des informations utilisateurs / employee
						//modifiaction du format du numero de tel pour appel automatique
						$tel = str_replace("+","00",$data['Fld_Contact_Phone']);
						$tel = preg_replace('/[^0-9]/', '', $tel); // supression sauf chiffres
						//Fin modifiaction du format du numero de tel pour appel automatique
						
					if ($data['status']!='Available') $statustab="style='background-color:#be0831;color:#00000;'";	
					
					echo "<tr id=\"row_".$data['id_company_contact']."\">";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Name".$x."' value='".$data['Fld_Contact_Name']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone".$x."' value='".$data['Fld_Contact_Phone']."'> <a href='#' onclick='callclient(".$tel.",".$dataiue['numformat'].");'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-phone'></i></a></td>";   
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone2".$x."' value='".$data['Fld_Contact_Phone2']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Fax".$x."' value='".$data['Fld_Contact_Fax']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Company_Mobile".$x."' value='".$data['Fld_Company_Mobile']."'></td>";
					
					echo "<td ".$statustab."><select class=\"form-control\" name=\"Fld_Contact_Division_ID".$x."\">";
										

											
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'";
												if ($data['Fld_Contact_Division_ID']==$datadiv['Fld_Division_ID']) echo " selected";
												echo ">".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											
                                                
                                            echo "</select></td>";
					
					
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Email".$x."' value='".$data['Fld_Contact_Email']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Title".$x."' value='".$data['Fld_Contact_Title']."'></td>";
					echo "<td ".$statustab."><textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='WIDTH: 400px; height:50px;'  id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".$data['Fld_Contact_Remark']."</textarea></td>";
					if ($data['status']=='Available') echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'> <a href='javascript:statutcontact(".$data['id_company_contact'].")' onClick=\"return(confirm('Etes vous sur ?'));\"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i></a></td>";
					else echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."' class=\"btn btn-default\"><a href='javascript:desarchivercontact(".$data['id_company_contact'].")'>Annuler</a></td>";
					echo "<input type=\"hidden\" name=\"id_company_contact".$x."\" value=\"".$data['id_company_contact']."\"><input type=\"hidden\" name=\"nbcontact\" id=\"nbcontact\" value=\"".$x."\">";
					echo "</tr>";
					
					}
			?>
                                    
                      </form>
                                </tbody>
                            </table>
<?php
//**************************************************************END CONTACT COMPANY****************************
			?>
										</div>
                                    </div>
                                </div><div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">CONTACT ARCHIVED</a>
                                        </h4>
                                    </div>
                                    <div id="collapseFour" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php
//*****************************************************************CONTACT COMPANY
/*Table tb_company_contact
*************************************
id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone#2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
*/
// getting total number records without any search

$sql="SELECT * FROM tb_company_contact where Fld_Company_ID=".$id_company." and status='none'";
$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
?>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_contact_company_multi.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
 <table width="100%" class="table table-striped table-bordered table-hover" id="mytable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Phone 2</th>
                                        <th>Fax</th>
                                        <th>Mobile</th>
                                        <th>Division</th>
                                        <th>E-mail</th>
                                        <th>Title</th>
                                        <th>Remark</th>
										<th></th>
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$x=0;
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
						$x++;
						
						//recuperation des informations utilisateurs / employee
						//**tbl_Employee**  Employee_ID   Employee_Name    Fld_Contact_Id  pw  email  statut  position  tel  mobile  skype  numformat
						$sqliue="SELECT numformat FROM tbl_Employee where Employee_ID='".$_SESSION['id_utilisateur']."'";
						
						//echo $sqldiv;
						$reqiue = mysql2_query($sqliue);
						$dataiue = mysqli_fetch_array($reqiue);
						//Fin recuperation des informations utilisateurs / employee
						//modifiaction du format du numero de tel pour appel automatique
						$tel = str_replace("+","00",$data['Fld_Contact_Phone']);
						$tel = preg_replace('/[^0-9]/', '', $tel); // supression sauf chiffres
						//Fin modifiaction du format du numero de tel pour appel automatique
						
					if ($data['status']!='Available') $statustab="style='background-color:#be0831;color:#00000;'";	
					
					echo "<tr id=\"row_".$data['id_company_contact']."\">";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Name".$x."' value='".$data['Fld_Contact_Name']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone".$x."' value='".$data['Fld_Contact_Phone']."'> <a href='#' onclick='callclient(".$tel.",".$dataiue['numformat'].");'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-phone'></i></a></td>";   
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Phone2".$x."' value='".$data['Fld_Contact_Phone2']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Fax".$x."' value='".$data['Fld_Contact_Fax']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Company_Mobile".$x."' value='".$data['Fld_Company_Mobile']."'></td>";
					
					echo "<td ".$statustab."><select class=\"form-control\" name=\"Fld_Contact_Division_ID".$x."\">";
										

											
											//recuperation du nom de la division	
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'";
												if ($data['Fld_Contact_Division_ID']==$datadiv['Fld_Division_ID']) echo " selected";
												echo ">".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											
                                                
                                            echo "</select></td>";
					
					
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Email".$x."' value='".$data['Fld_Contact_Email']."'></td>";
					echo "<td ".$statustab."><input class=\"form-control\" type='text' name='Fld_Contact_Title".$x."' value='".$data['Fld_Contact_Title']."'></td>";
					echo "<td ".$statustab."><textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='WIDTH: 400px; height:50px;'  id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".$data['Fld_Contact_Remark']."</textarea></td>";
					if ($data['status']=='Available') echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'> <a href='javascript:statutcontact(".$data['id_company_contact'].")' onClick=\"return(confirm('Etes vous sur ?'));\"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i></a></td>";
					else echo "<td id='case".$data['id_company_contact']."'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'><a href='javascript:desarchivercontact(".$data['id_company_contact'].")'>Annuler</a></td>";
					echo "<input type=\"hidden\" name=\"id_company_contact".$x."\" value=\"".$data['id_company_contact']."\"><input type=\"hidden\" name=\"nbcontact\" id=\"nbcontact\" value=\"".$x."\">";
					echo "</tr>";
					
					}
			?>
                                    
                      </form>
                                </tbody>
                            </table>
<?php
}
else echo "There is no contact archived for the moment";
			?>
										</div>
                                    </div>
                                </div>
<!--**************************************************************END CONTACT COMPANY*****************************-->								
<!--*****************************************************************COMPANY FLEET********************************-->								
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive">FLEET</a>
                                        </h4>
                                    </div>
                                    <div id="collapseFive" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php


//**tbl_Fleet** id_Fleet  Fld_Link_Id  Fld_Company_ID  Company_Old_Id  Fld_Region  Fld_Engine  Fld_Unit  Fld_AC_ID 

$sqlcomp="SELECT * FROM tbl_Fleet where Fld_Company_ID=".$id_company;
$reqcomp = mysql2_query($sqlcomp);
?>
<a href="javascript:addaircraft()"> + Add A AIRCRAFT</a>
<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_aircraft_fleet.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<table width="100%" class="table table-striped table-bordered table-hover" id="dataTablefleet">
                                <thead>
                                    <tr>
                                        <th>REGION</th>
                                        <th>ENGINE</th>
                                        <th>UNIT</th>
                                        <th>AIRCRAFT</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$z=0;
					while ($datacomp = mysqli_fetch_array($reqcomp))
					{ 
				$z++;

					echo "<tr id=\"row_".$z."\">";
					echo "<td><select class=\"form-control\" name='Fld_Region".$z."'>";
					//recuperation region
					//** tbl_Region **  Region_ID  Region_Texte
					$sqlreg="SELECT distinct(Region_Texte),Region_ID FROM tbl_Region order by Region_Texte";
					
					$reqreg = mysql2_query($sqlreg);
					while($datareg = mysqli_fetch_array($reqreg))
					{
						echo "<option value='".$datareg['Region_ID']."'";
						if ($datareg['Region_ID']==$datacomp['Fld_Region']) echo "selected";
						echo ">".$datareg['Region_Texte']."</option>";
					}
					//Fin recuperation region
					echo "</select></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Engine".$z."' value='".$datacomp['Fld_Engine']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Unit".$z."' value='".$datacomp['Fld_Unit']."'></td>";
					echo "<td><select class=\"form-control\" name='Fld_AC_ID".$z."'>";
					//recuperation Aircraft
					// ** tbl_Aircraft ** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					$sqlrairc="SELECT distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";
					
					$reqrairc = mysql2_query($sqlrairc);
					while($datarairc = mysqli_fetch_array($reqrairc))
					{
						echo "<option value='".$datarairc['Fld_AC_ID']."'";
						if ($datarairc['Fld_AC_ID']==$datacomp['Fld_AC_ID']) echo "selected";
						echo ">".$datarairc['Fld_AC_Model']."</option>";
					}
					//Fin recuperation Aircraft
					echo "</select></td>";
					echo "<input type=\"hidden\" name=\"id_Fleet".$z."\" value=\"".$datacomp['id_Fleet']."\"><input type=\"hidden\" name=\"nbaircraft\" id=\"nbaircraft\" value=\"".$z."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$z."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>
									
										</div>
                                    </div>
                                </div>
<!--*****************************************************************END COMPANY FLEET*************************
************************************************************************************************************-->
<!--****************************************FORWARDER*******************************************************-->
									<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSix">FORWARDER</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSix" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php
//**tbl_Forwarder**   Fld_Linked_ID  Company_Old_Id  Fld_Company_ID  Fld_Shipper_ID  Fld_Account_Nbr Fld_Remark Fld_Shipper_Contact_Name_Forw  Fld_Shipper_Contact_Phone_Forw
//*** tbl_Shipper ***  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone

$sqlforwarder="SELECT tbl_Forwarder.*,tbl_Shipper.* FROM tbl_Forwarder,tbl_Shipper where tbl_Forwarder.Fld_Shipper_ID=tbl_Shipper.Fld_Shipper_ID AND tbl_Forwarder.Fld_Company_ID='".$id_company."'";
// echo $sqlforwarder;
$reqforwarder = mysql2_query($sqlforwarder);
?>
<a href="javascript:addforwarder()"> + Add A FORWARDER</a>
<form id="formForwarder" name="formForwarder" method="post" action="valid_modif_forwarder.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<table width="100%" class="table table-striped table-bordered table-hover" id="dataTableforw">
                                <thead>
                                    <tr>
										<th>SHIPPER</th>
										<th>SHIPPER CONTAC NAME</th>
                                        <th>SHIPPER CONTAC PHONE</th>
										<th>ACCOUNT #</th>
										<th>REMARK</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$w=0;
					while ($dataforwarder = mysqli_fetch_array($reqforwarder))
					{ 
				$w++;

					echo "<tr id=\"row_".$w."\">";
					echo "<td><select class=\"form-control\" name='Fld_Shipper_ID".$w."'>";
									// ** tbl_Shipper **  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
									
									
									$objet=new company();
									$donnee = $objet->affichage_shippers();
									
									foreach($donnee as $dataemp)
									{
										echo "<option value='".$dataemp["Fld_Shipper_ID"]."'";
										if((!empty($dataforwarder['Fld_Shipper_ID']))&&($dataemp["Fld_Shipper_ID"]==$dataforwarder['Fld_Shipper_ID'])) echo "selected";
										echo ">".$dataemp["Fld_Shipper_Text"]."<option>";
									}
					echo "</select></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Name_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Name_Forw']."'><br>OLD CONTACT NAME : ".$dataforwarder['Fld_Shipper_Contact_Name']."</td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Phone_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Phone_Forw']."'><br>OLD CONTACT PHONE : ".$dataforwarder['Fld_Shipper_Contact_Phone']."</td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Account_Nbr".$w."' value='".$dataforwarder['Fld_Account_Nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Remark".$w."' value='".$dataforwarder['Fld_Remark']."'></td>";
					echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$w."\" value=\"".$dataforwarder['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbforwarder\" id=\"nbforwarder\" value=\"".$w."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$w."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>							
										</div>
                                    </div>
                                </div>
<!--****************************************END FORWARDER*******************************************************-->
<!--************************************************************************************************************-->
<!--*****************************************************************BANK ACCOUNT*******************************-->								
									<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSeven">BANK ACCOUNT</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSeven" class="panel-collapse collapse">
                                        <div class="panel-body">
			<?php

//**tbl_Company_Bank_Account**   Fld_Linked_ID  Fld_Company_ID  Fld_Bank_Acct_Nbr  Fld_Bank_Name  Fld_Bank_Address  Fld_ABA_Routing_Nbr  Fld_Swift_Nbr  Fld_Reference  branch_nbr  bank_nbr  comments

$sqlbanka="SELECT * FROM tbl_Company_Bank_Account where Fld_Company_ID=".$id_company;
$reqbanka = mysql2_query($sqlbanka);
?>
<a href="javascript:addaba()"> + Add A BANK ACCOUNT</a>
<form id="formbankaccount" name="formbankaccount" method="post" action="valid_modif_bank_account.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<table width="100%" class="table table-striped table-bordered table-hover" id="dataTableba">
                                <thead>
                                    <tr>
										<th>BANK NAME</th>
										<th>BANK ADDRESS</th>
                                        <th>ACCOUNT #</th>
										<th>BRANCH #</th>
										<th>BANK #</th>
                                        <th>SWIFT #</th>
                                        <th>ABA ROUTING #</th>
                                        <th>REFERENCE</th>
                                        <th>COMMENTS</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
								<?php
					$a=0;
					while ($banka = mysqli_fetch_array($reqbanka))
					{ 
				$a++;

					echo "<tr id=\"row_".$a."\">";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Name".$a."' value='".$banka['Fld_Bank_Name']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Address".$a."' value='".$banka['Fld_Bank_Address']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Acct_Nbr".$a."' value='".$banka['Fld_Bank_Acct_Nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='branch_nbr".$a."' value='".$banka['branch_nbr']."'></td>";
				
					echo "<td><input class=\"form-control\" type='text' name='bank_nbr".$a."' value='".$banka['bank_nbr']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='Fld_Swift_Nbr".$a."' value='".$banka['Fld_Swift_Nbr']."'></td>";
					
					echo "<td><input class=\"form-control\" type='text' name='Fld_ABA_Routing_Nbr".$a."' value='".$banka['Fld_ABA_Routing_Nbr']."'></td>";
					
					echo "<td><input class=\"form-control\" type='text' name='Fld_Reference".$a."' value='".$banka['Fld_Reference']."'></td>";
					echo "<td><input class=\"form-control\" type='text' name='comments".$a."' value='".$banka['comments']."'></td>";
					echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$a."\" value=\"".$banka['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbbankaccount\" id=\"nbbankaccount\" value=\"".$a."\">";
					echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$a."'></td>";
					echo "</tr>";
					
					}
			?>
                                </tbody>
                            </table>
							</form>
									
										</div>
                                    </div>
                                </div>
<!--*****************************************************************END BANK ACCOUNT****************************************-->									
								</div>
							</div>
							<!-- .panel-body -->
                    
						</div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
