<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
include_once "conf.php";
if (!isset($_SESSION["conectroy"]) || $_SESSION["conectroy"] !== "parfait") {
	http_response_code(401);
	echo "Unauthorized";
	exit;
}
include_once "page_titles.php";

$id_company_contact = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/*Table tb_company_contact
*************************************
id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone#2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
*/

$sql="SELECT * FROM tb_company_contact where id_company_contact='".$id_company_contact."'";

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){

?>

<form id="formmodifcontact" name="formmodifcontact" method="post" action="valid_modif_contact_company_multi.php">

 <tbody>
								<?php
					$x=1;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);



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


					echo "<div class='row'>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Name</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Name".$x."' value='".$data['Fld_Contact_Name']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Phone</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Phone".$x."' value='".$data['Fld_Contact_Phone']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Phone 2</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Phone2".$x."' value='".$data['Fld_Contact_Phone2']."'></div></div>";

					echo "</div>";
					echo "<div class='row'>";

					echo "<div class='col-lg-4'><div class='form-group'><label>Fax</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Fax".$x."' value='".$data['Fld_Contact_Fax']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Mobile</label><br><input class=\"form-control\" type='text' name='Fld_Company_Mobile".$x."' value='".$data['Fld_Company_Mobile']."'></div></div>";

					echo "<div class='col-lg-4'><div class='form-group'><label>Division</label><br><select class=\"form-control\" name=\"Fld_Contact_Division_ID".$x."\">";



											//recuperation du nom de la division
											 //*******tbl_Division*********Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division order by Fld_Division_Text";

											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'";
												if ($data['Fld_Contact_Division_ID']==$datadiv['Fld_Division_ID']) echo " selected";
												echo ">".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie


                                            echo "</select></div></div>";

					echo "</div>";
					echo "<div class='row'>";

					echo "<div class='col-lg-4'><div class='form-group'><label>E-mail</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Email".$x."' value='".$data['Fld_Contact_Email']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Title</label><br><input class=\"form-control\" type='text' name='Fld_Contact_Title".$x."' value='".$data['Fld_Contact_Title']."'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><label>Remark</label><br><textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='WIDTH: 100%; height:50px;'  id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".$data['Fld_Contact_Remark']."</textarea></div></div>";

					echo "</div>";
					echo "<div class='row'>";
					echo "<div class='col-lg-3'><div class='form-group'><label>WhatsApp</label><br><input class=\"form-control\" type='text' name='whatsapp_number".$x."' value='".(isset($data['whatsapp_number']) ? $data['whatsapp_number'] : '')."'></div></div>";
					echo "<div class='col-lg-3'><div class='form-group'><label>LinkedIn</label><br><input class=\"form-control\" type='text' name='linkedin_url".$x."' value='".(isset($data['linkedin_url']) ? $data['linkedin_url'] : '')."'></div></div>";
					echo "<div class='col-lg-3'><div class='form-group'><label>Facebook</label><br><input class=\"form-control\" type='text' name='facebook_url".$x."' value='".(isset($data['facebook_url']) ? $data['facebook_url'] : '')."'></div></div>";
					echo "<div class='col-lg-3'><div class='form-group'><label>Instagram</label><br><input class=\"form-control\" type='text' name='instagram_url".$x."' value='".(isset($data['instagram_url']) ? $data['instagram_url'] : '')."'></div></div>";
					echo "</div>";
					echo "<div class='row'>";
					echo "<div class='col-lg-12'><div class='form-group'><label>Social network notes</label><br><textarea class=\"form-control\" name='social_network_notes".$x."' style='WIDTH: 100%; height:50px;'>".(isset($data['social_network_notes']) ? $data['social_network_notes'] : '')."</textarea></div></div>";

					echo "</div>";
					echo "<div class='row'>";

					echo "<div class='col-lg-8'><div class='form-group'></div></div>";
					echo "<div class='col-lg-4'><div class='form-group'><input class=\"form-control\" type='submit' value='ok' name='valform".$x."'></div></div>";

					echo "<input type=\"hidden\" name=\"id_company_contact".$x."\" value=\"".$data['id_company_contact']."\"><input type=\"hidden\" name=\"nbcontact\" id=\"nbcontact\" value=\"".$x."\">";
					echo "</div>";


			?>


                                </tbody>


                           <input type="hidden" name="Fld_Company_ID" value="<?php echo $data['Fld_Company_ID'];?>">
                           <input type="hidden" name="return_page" value="<?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1;?>">
                           <input type="hidden" name="return_anchor" value="bloccontactcompany">
						</form>
<?php
}
			//**************************************************************Fin Details Company****************************
