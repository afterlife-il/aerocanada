<?php
class company
{
	
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//*****************************  COMPANY *********************************************************************/
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								public function affichage_company($id_company)
								{
									$res=array();
									//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code  customer_payment_term_id   customer_payment_term_amount   customer_payment_term_currencyid  aci_payment_term_id  aci_payment_term_amount   aci_payment_term_currencyid
									$req="SELECT * from tb_company where Fld_Company_ID='".$id_company."'";
									
									$requete=mysql2_query($req);
									while($reponse=mysqli_fetch_array($requete)){
									$res[]=$reponse;
																				}
								return $res;
								}
								public function affichage_add_company($id_company)
								{
									$res=array();
									$req="SELECT * from tbl_Company_Details where Fld_Company_ID=".$id_company;
									$requete=mysql2_query($req);
									
									while($reponse=mysqli_fetch_array($requete)){
									$res[]=$reponse;
																				}
								return $res;
								}
								
								public function affichage_aci_contact($id_company)
								{
									$res=array();
									$req="SELECT Fld_Company_BAX_Contact FROM tbl_Company_Details where Fld_Company_ID='".$id_company."'";
									//echo $req;
									
									$requete=mysql2_query($req);
									$reponse=mysqli_fetch_array($requete);
								
								return $reponse['Fld_Company_BAX_Contact'];
								}
								
								public function affichage_type_company($id_company)
								{
									$res=array();
									$req="SELECT Fld_Company_Type_ID FROM tbl_Company_Details where Fld_Company_ID='".$id_company."'";
									//echo $req;
									
									$requete=mysql2_query($req);
									$reponse=mysqli_fetch_array($requete);
								
								return $reponse['Fld_Company_Type_ID'];
								}
								
								public function affichage_vat_company($id_company)
								{
									$res=array();
									$req="SELECT Fld_VAT_Nbr FROM tbl_Company_Details where Fld_Company_ID='".$id_company."'";
									//echo $req;
									
									$requete=mysql2_query($req);
									$reponse=mysqli_fetch_array($requete);
								
								return $reponse['Fld_VAT_Nbr'];
								}
							
				
								public function ajout_company()
								{
									
									//********************************telechargement du logo
									//******************************************************
									
									//je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									if(!empty($_FILES["logocompany"]["name"]))
									{
										$imagelogo=$_FILES["logocompany"]["name"];
										
									}
									else $imagelogo="";
									//Fin je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									$fichelogo="";
									$target_dir = "../logo_company/";
									$target_file = $target_dir . basename($_FILES["logocompany"]["name"]);
									$uploadOk = 1;
									$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
									// Check if image file is a actual image or fake image
									if(isset($_POST["submit"])) {
										$check = getimagesize($_FILES["logocompany"]["tmp_name"]);
										if($check !== false) {
											echo "Le fichier est une image - " . $check["mime"] . ".";
											$uploadOk = 1;
										} else {
											echo "Ce fichier n'est pas une image.";
											$uploadOk = 0;
										}
									}
									// Check if file already exists
									if (file_exists($target_file)) {
										echo "Désolé, ce fichier existe déjà.";
										$uploadOk = 0;
									}
									// Check file size
									if ($_FILES["logocompany"]["size"] > 500000) {
										echo "Désolé, votre fichier est trop volumineux.";
										$uploadOk = 0;
									}
									// Allow certain file formats
									if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
									&& $imageFileType != "gif" ) {
										echo "Désolé, seulement les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
										$uploadOk = 0;
									}
									// Check if $uploadOk is set to 0 by an error
									if ($uploadOk == 0) {
										echo "Désolé, votre fichier n'a pas été téléchargé.";
									// if everything is ok, try to upload file
									} else {
										if (move_uploaded_file($_FILES["logocompany"]["tmp_name"], $target_file)) {
											echo "Le fichier ". basename( $_FILES["logocompany"]["name"]). " a été téléchargé.";
											$fichelogo="ok";
										} 
										else {
											echo "Désolé, il y a une erreur de chargement dans votre fichier.";
											$fichelogo="no";
										}
									}
									//********************************Fin telechargement du logo
									//**********************************************************
									
								//**tb_company**  Fld_Company_ID        Company_Old_Id       Fld_Company_Name      Fld_Company_Rating_ID    delete   companyrating	aci_contact   logocompany status internet cage_code  customer_payment_term_id   customer_payment_term_amount   customer_payment_term_currencyid  aci_payment_term_id  aci_payment_term_amount   aci_payment_term_currencyid
								$reqac="INSERT INTO tb_company (`Fld_Company_ID`,`Company_Old_Id`, `Fld_Company_Name`, `Fld_Company_Rating_ID`, `delete`, `companyrating`, `aci_contact`, `logocompany`, `status`, `internet`, `cage_code`, `customer_payment_term_id`, `customer_payment_term_amount`, `customer_payment_term_currencyid`, `aci_payment_term_id`, `aci_payment_term_amount`, `aci_payment_term_currencyid`)
								VALUES ('','','".addslashes($_POST['Fld_Company_Name'])."','','','".$_POST['companyrating']."','".$_POST['Employee_ID']."','".$imagelogo."','Available','".addslashes($_POST['internet'])."','".$_POST['cage_code']."','".$_POST['customer_payment_term_id']."','".$_POST['customer_payment_term_amount']."','".$_POST['customer_payment_term_currencyid']."','".$_POST['aci_payment_term_id']."','".$_POST['aci_payment_term_amount']."','".$_POST['aci_payment_term_currencyid']."');";
								// echo $reqac;
								$requete = mysql2_query($reqac);
								
								$lastid=mysql2_insert_id();
								
								//Ajout Adresse Company
								for($i=1;$i<=$_POST['nbaddcompany'];$i++)
								{
								//**tbl_Company_Details**   id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark Fld_VAT_Nbr Fld_Date_Of_First_Contact Fld_Company_Address_Type  UTC_timezone   title_address
								$req="INSERT INTO tbl_Company_Details (`id_tbl_company_Details`,`Fld_Linked_ID`, `Fld_Company_ID`, `Company_Old_Id`, `Fld_Company_Type_ID`, `Fld_Company_Country`, `Fld_Company_City`, `Fld_Company_State`, `Fld_Company_Street`, `Fld_Company_ZipCode`, `Fld_Company_Fax`, `Fld_Company_Phone`, `Fld_Company_Email`, `Fld_Company_Score`, `Fld_Company_BAX_Contact`, `Fld_Remark`, `Fld_VAT_Nbr`, `Fld_Date_Of_First_Contact`, `Fld_Company_Address_Type`, `UTC_timezone`, `title_address`)
								VALUES ('','','".$lastid."','','".$_POST['Fld_Company_Type_ID']."','".addslashes($_POST['Fld_Company_Country'.$i])."','".addslashes($_POST['Fld_Company_City'.$i])."','".addslashes($_POST['Fld_Company_State'.$i])."','".addslashes($_POST['Fld_Company_Street'.$i])."','".$_POST['Fld_Company_ZipCode'.$i]."','".$_POST['Fld_Company_Fax'.$i]."','".$_POST['Fld_Company_Phone'.$i]."','".$_POST['Fld_Company_Email'.$i]."','','".$_POST['Employee_ID']."','".$_POST['Fld_Remark'.$i]."','".$_POST['Fld_VAT_Nbr']."','".$_POST['Fld_Date_Of_First_Contact'.$i]."','".$_POST['Fld_Company_Address_Type'.$i]."','".$_POST['UTC_timezone'.$i]."','".$_POST['title_address'.$i]."')";
								
								// echo $req;
								$requete = mysql2_query($req);
								}
								//Fin Ajout Adresse Company

								}
								
								public function modif_company()
								{
									//********************************telechargement du logo
									//******************************************************
									
									//je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									if(!empty($_FILES["logocompany"]["name"]))
									{
										$imagelogo=$_FILES["logocompany"]["name"];
										unlink ("../logo_company/".$_POST['logocompany2']);
									}
									else $imagelogo=$_POST['logocompany2'];
									//Fin je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									$fichelogo="";
									$target_dir = "../logo_company/";
									$target_file = $target_dir . basename($_FILES["logocompany"]["name"]);
									$uploadOk = 1;
									$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
									// Check if image file is a actual image or fake image
									if(isset($_POST["submit"])) {
										$check = getimagesize($_FILES["logocompany"]["tmp_name"]);
										if($check !== false) {
											echo "Le fichier est une image - " . $check["mime"] . ".";
											$uploadOk = 1;
										} else {
											echo "Ce fichier n'est pas une image.";
											$uploadOk = 0;
										}
									}
									// Check if file already exists
									if (file_exists($target_file)) {
										echo "Désolé, ce fichier existe déjà.";
										$uploadOk = 0;
									}
									// Check file size
									if ($_FILES["logocompany"]["size"] > 500000) {
										echo "Désolé, votre fichier est trop volumineux.";
										$uploadOk = 0;
									}
									// Allow certain file formats
									if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
									&& $imageFileType != "gif" ) {
										echo "Désolé, seulement les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
										$uploadOk = 0;
									}
									// Check if $uploadOk is set to 0 by an error
									if ($uploadOk == 0) {
										echo "Désolé, votre fichier n'a pas été téléchargé.";
									// if everything is ok, try to upload file
									} else {
										if (move_uploaded_file($_FILES["logocompany"]["tmp_name"], $target_file)) {
											echo "Le fichier ". basename( $_FILES["logocompany"]["name"]). " a été téléchargé.";
											$fichelogo="ok";
										} 
										else {
											echo "Désolé, il y a une erreur de chargement dans votre fichier.";
											$fichelogo="no";
										}
									}
									//********************************Fin telechargement du logo
									//**********************************************************
									
									//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code  customer_payment_term_id   customer_payment_term_amount   customer_payment_term_currencyid  aci_payment_term_id  aci_payment_term_amount   aci_payment_term_currencyid
									$sql="update tb_company set Fld_Company_Name='".addslashes($_POST['Fld_Company_Name'])."', companyrating='".$_POST['companyrating']."', aci_contact='".$_POST['Employee_ID']."',logocompany='".$imagelogo."',internet='".$_POST['internet']."',cage_code='".$_POST['cage_code']."',customer_payment_term_id='".$_POST['customer_payment_term_id']."',customer_payment_term_amount='".$_POST['customer_payment_term_amount']."',customer_payment_term_currencyid='".$_POST['customer_payment_term_currencyid']."',aci_payment_term_id='".$_POST['aci_payment_term_id']."',aci_payment_term_amount='".$_POST['aci_payment_term_amount']."',aci_payment_term_currencyid='".$_POST['aci_payment_term_currencyid']."' where Fld_Company_ID='".$_POST['Fld_Company_ID']."'";
									//echo $sql;
									
									$query=mysql2_query($sql);
									
									company::modif_details_company();
									company::modif_add_company();
								}
								
								public function modif_details_company()
								{	
									//  tbl_Company_Details  ::::  id_tbl_company_Details Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Company_Type_ID  Fld_Company_Country  Fld_Company_City  Fld_Company_State  Fld_Company_Street  Fld_Company_ZipCode  Fld_Company_Fax  Fld_Company_Phone  Fld_Company_Email  Fld_Company_Score  Fld_Company_BAX_Contact  Fld_Remark  Fld_VAT_Nbr  Fld_Date_Of_First_Contact  Fld_Company_Address_Type  UTC_timezone  title_address
									$sql="update tbl_Company_Details set Fld_Company_Type_ID='".$_POST['Fld_Company_Type_ID']."',Fld_VAT_Nbr='".$_POST['Fld_VAT_Nbr']."' where Fld_Company_ID='".$_POST['Fld_Company_ID']."'";
									//echo $sql;
									
									$query=mysql2_query($sql);
								}
								
								public function modif_info_general_company()
								{	
									//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete   	companyrating 	aci_contact  logocompany status internet cage_code  customer_payment_term_id   customer_payment_term_amount   customer_payment_term_currencyid  aci_payment_term_id  aci_payment_term_amount   aci_payment_term_currencyid
									$sql="update tb_company set Fld_Company_Name='".addslashes($_POST['Fld_Company_Name'])."', aci_contact='".$_POST['Employee_ID']."',internet='".$_POST['internet']."',cage_code='".$_POST['cage_code']."',customer_payment_term_id='".$_POST['customer_payment_term_id']."',customer_payment_term_amount='".$_POST['customer_payment_term_amount']."',customer_payment_term_currencyid='".$_POST['customer_payment_term_currencyid']."',aci_payment_term_id='".$_POST['aci_payment_term_id']."',aci_payment_term_amount='".$_POST['aci_payment_term_amount']."',aci_payment_term_currencyid='".$_POST['aci_payment_term_currencyid']."' where Fld_Company_ID='".$_POST['Fld_Company_ID']."'";
									//echo $sql;
									
									$query=mysql2_query($sql);
									//  tbl_Company_Details  ::::  id_tbl_company_Details Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Company_Type_ID  Fld_Company_Country  Fld_Company_City  Fld_Company_State  Fld_Company_Street  Fld_Company_ZipCode  Fld_Company_Fax  Fld_Company_Phone  Fld_Company_Email  Fld_Company_Score  Fld_Company_BAX_Contact  Fld_Remark  Fld_VAT_Nbr  Fld_Date_Of_First_Contact  Fld_Company_Address_Type  UTC_timezone  title_address
									$sql2="UPDATE tbl_Company_Details 
       SET Fld_Company_Type_ID='" . escape_data($_POST['Fld_Company_Type_ID']) . "',
           Fld_VAT_Nbr='" . escape_data($_POST['Fld_VAT_Nbr']) . "',
           Fld_Remark='" . escape_data($_POST['Fld_Remark']) . "' 
       WHERE Fld_Company_ID='" . escape_data($_POST['Fld_Company_ID']) . "'";

									//echo $sql;
									
									$query2=mysql2_query($sql2);
								}
								
								public function modif_add_company()
								{
									for($i=1;$i<=$_POST['nbaddcompany'];$i++)
									{
									$requete="SELECT * FROM tbl_Company_Details where id_tbl_company_Details=".$_POST['id_tbl_company_Details'.$i];
									$result=mysql2_query($requete);
									 $nb_resultats = mysqli_num_rows($result);
									//echo $requete;
									
									if (0<$nb_resultats) {
										//echo "-modif-";
								   $sql="UPDATE tbl_Company_Details 
      SET Fld_Company_ID='" . escape_data($_POST['Fld_Company_ID']) . "',
          Fld_Company_Country='" . escape_data($_POST['Fld_Company_Country'.$i]) . "',
          Fld_Company_City='" . escape_data($_POST['Fld_Company_City'.$i]) . "',
          Fld_Company_State='" . escape_data($_POST['Fld_Company_State'.$i]) . "',
          Fld_Company_Street='" . escape_data($_POST['Fld_Company_Street'.$i]) . "',
          Fld_Company_ZipCode='" . escape_data($_POST['Fld_Company_ZipCode'.$i]) . "',
          Fld_Company_Fax='" . escape_data($_POST['Fld_Company_Fax'.$i]) . "',
          Fld_Company_Phone='" . escape_data($_POST['Fld_Company_Phone'.$i]) . "',
          Fld_Company_Email='" . escape_data($_POST['Fld_Company_Email'.$i]) . "',
          Fld_Company_BAX_Contact='" . escape_data($_POST['Employee_ID']) . "',
          Fld_Remark='" . escape_data($_POST['Fld_Remark'.$i]) . "',
          Fld_Company_Address_Type='" . escape_data($_POST['Fld_Company_Address_Type'.$i]) . "',
          UTC_timezone='" . escape_data($_POST['UTC_timezone'.$i]) . "',
          title_address='" . escape_data($_POST['title_address'.$i]) . "' 
      WHERE id_tbl_company_Details='" . escape_data($_POST['id_tbl_company_Details'.$i]) . "'";

									// echo $sql;
									
									$query=mysql2_query($sql);
												}
									else {
										//echo "-ajout-";
									$req="INSERT INTO tbl_Company_Details (`id_tbl_company_Details`,`Fld_Linked_ID`, `Fld_Company_ID`, `Company_Old_Id`, `Fld_Company_Type_ID`, `Fld_Company_Country`, `Fld_Company_City`, `Fld_Company_State`, `Fld_Company_Street`, `Fld_Company_ZipCode`, `Fld_Company_Fax`, `Fld_Company_Phone`, `Fld_Company_Email`, `Fld_Company_Score`, `Fld_Company_BAX_Contact`, `Fld_Remark`, `Fld_VAT_Nbr`, `Fld_Date_Of_First_Contact`, `Fld_Company_Address_Type`, `UTC_timezone`, `title_address`)
									VALUES ('','','".$_POST['Fld_Company_ID']."','','".$_POST['Fld_Company_Type_ID']."','".addslashes($_POST['Fld_Company_Country'.$i])."','".addslashes($_POST['Fld_Company_City'.$i])."','".addslashes($_POST['Fld_Company_State'.$i])."','".addslashes($_POST['Fld_Company_Street'.$i])."','".$_POST['Fld_Company_ZipCode'.$i]."','".addslashes($_POST['Fld_Company_Fax'.$i])."','".addslashes($_POST['Fld_Company_Phone'.$i])."','".$_POST['Fld_Company_Email'.$i]."','','".$_POST['Employee_ID']."','".addslashes($_POST['Fld_Remark'.$i])."','".$_POST['Fld_VAT_Nbr'.$i]."','".$_POST['Fld_Date_Of_First_Contact'.$i]."','".$_POST['Fld_Company_Address_Type'.$i]."','".addslashes($_POST['UTC_timezone'.$i])."','".addslashes($_POST['title_address'.$i])."')";
									//echo $req;
									
									$requete = mysql2_query($req);
									}
									}
								}
								public function add_address_company()
								{
									$req="INSERT INTO tbl_Company_Details (`id_tbl_company_Details`,`Fld_Linked_ID`, `Fld_Company_ID`, `Company_Old_Id`, `Fld_Company_Type_ID`, `Fld_Company_Country`, `Fld_Company_City`, `Fld_Company_State`, `Fld_Company_Street`, `Fld_Company_ZipCode`, `Fld_Company_Fax`, `Fld_Company_Phone`, `Fld_Company_Email`, `Fld_Company_Score`, `Fld_Company_BAX_Contact`, `Fld_Remark`, `Fld_VAT_Nbr`, `Fld_Date_Of_First_Contact`, `Fld_Company_Address_Type`, `UTC_timezone`, `title_address`)
									VALUES ('','','".$_POST['Fld_Company_ID']."','','".$_POST['Fld_Company_Type_ID']."','".addslashes($_POST['Fld_Company_Country'])."','".addslashes($_POST['Fld_Company_City'])."','".addslashes($_POST['Fld_Company_State'])."','".addslashes($_POST['Fld_Company_Street'])."','".$_POST['Fld_Company_ZipCode']."','".addslashes($_POST['Fld_Company_Fax'])."','".addslashes($_POST['Fld_Company_Phone'])."','".$_POST['Fld_Company_Email']."','','".$_POST['Employee_ID']."','".addslashes($_POST['Fld_Remark'])."','".$_POST['Fld_VAT_Nbr']."','".$_POST['Fld_Date_Of_First_Contact']."','".$_POST['Fld_Company_Address_Type']."','".addslashes($_POST['UTC_timezone'])."','".addslashes($_POST['title_address'])."')";
									//echo $req;
									
									$requete = mysql2_query($req);
								}
								public function gestion_address_company()
								{
									for($i=1;$i<=$_POST['nbaddcompany'];$i++)
									{	
										//  tbl_Company_Details  ::::  id_tbl_company_Details Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Company_Type_ID  Fld_Company_Country  Fld_Company_City  Fld_Company_State  Fld_Company_Street  Fld_Company_ZipCode  Fld_Company_Fax  Fld_Company_Phone  Fld_Company_Email  Fld_Company_Score  Fld_Company_BAX_Contact  Fld_Remark  Fld_VAT_Nbr  Fld_Date_Of_First_Contact  Fld_Company_Address_Type  UTC_timezone  title_address
										
										$sql="update tbl_Company_Details set title_address='".addslashes($_POST['title_address'.$i])."',Fld_Company_Address_Type='".addslashes($_POST['Fld_Company_Address_Type'.$i])."',Fld_Company_Street='".addslashes($_POST['Fld_Company_Street'.$i])."',Fld_Company_City='".addslashes($_POST['Fld_Company_City'.$i])."',Fld_Company_ZipCode='".addslashes($_POST['Fld_Company_ZipCode'.$i])."',Fld_Company_State='".addslashes($_POST['Fld_Company_State'.$i])."',Fld_Company_Country='".$_POST['Fld_Company_Country'.$i]."',UTC_timezone='".addslashes($_POST['UTC_timezone'.$i])."',Fld_Company_Phone='".addslashes($_POST['Fld_Company_Phone'.$i])."',Fld_Company_Email='".$_POST['Fld_Company_Email'.$i]."',Fld_Company_Score='".$_POST['Fld_Company_Score']."',Fld_Remark='".$_POST['Fld_Remark'.$i]."',Fld_VAT_Nbr='".$_POST['Fld_VAT_Nbr'.$i]."' where id_tbl_company_Details='".$_POST['id_tbl_company_Details'.$i]."'";
										// echo $sql;
										
										$query=mysql2_query($sql);
									}
								}
								public function archive_company($Fld_Company_ID)
								{
									
									$sql="update tb_company set status='archive' where Fld_Company_ID='".$Fld_Company_ID."'";
									$query=mysql2_query($sql);
								}
								public function sup_add_company($id_tbl_company_Details)
								{
									$result = mysql2_query("DELETE FROM tbl_Company_Details where id_tbl_company_Details='".$id_tbl_company_Details."'"); 
								}
									/*******************************************************************************************************************
									*******************************************GESTION COMPANY TYPE****************************************************/
									
									// ** tbl_Company_Type ** Fld_Company_Type_ID  Fld_Company_Type_Text
																			
									public function affichage_company_type()
									{
										$res=array();
										$req="SELECT * FROM tbl_Company_Type";
										$requete=mysql2_query($req);
										
										while($reponse=mysqli_fetch_array($requete)){
										$res[]=$reponse;
																					}
									return $res;
									}
									public function add_company_type()
									{	
										
										$requete = mysql2_query("INSERT INTO tbl_Company_Type (`Fld_Company_Type_ID`,`Fld_Company_Type_Text`) VALUES ('','".addslashes($_POST['Fld_Company_Type_Text'])."');");
									}
									public function modif_company_type()
									{
										$sql="update tbl_Company_Type set Fld_Company_Type_Text='".addslashes($_GET['Fld_Company_Type_Text'])."' where Fld_Company_Type_ID='".$_GET['Fld_Company_Type_ID']."'";
										$query=mysql2_query($sql);
									}
									public function del_company_type($Fld_Company_Type_ID)
									{
										$result = mysql2_query("DELETE FROM tbl_Company_Type where Fld_Company_Type_ID='".$Fld_Company_Type_ID."'"); 
									}
									
									
									
									/*******************************************************************************************************************
									*******************************************GESTION ADDRESS TYPE****************************************************/
									
									//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
																			
									public function affichage_address_type()
									{
										$res=array();
										$req="SELECT * FROM tbl_Division";
										$requete=mysql2_query($req);
										
										while($reponse=mysqli_fetch_array($requete)){
										$res[]=$reponse;
																					}
									return $res;
									}
									public function add_address_type()
									{	
										
										$req="INSERT INTO tbl_Division (`Fld_Division_ID`,`Fld_Division_Text`) VALUES ('','".addslashes($_POST['Fld_Division_Text'])."');";
										// echo $req;
										$requete = mysql2_query($req);
									}
									public function modif_address_type()
									{
										$sql="update tbl_Division set Fld_Division_Text='".addslashes($_GET['Fld_Division_Text'])."' where Fld_Division_ID='".$_GET['Fld_Division_ID']."'";
										$query=mysql2_query($sql);
									}
									public function del_address_type($Fld_Division_ID)
									{
										$result = mysql2_query("DELETE FROM tbl_Division where Fld_Division_ID='".$Fld_Division_ID."'"); 
									}
									
									
									
									
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//*****************************  Gestion des contacts societe
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//****tb_company_contact*****id_company_contact Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Contact_Name Fld_Contact_Phone Fld_Contact_Phone2 Fld_Contact_Fax Fld_Company_Mobile Fld_Contact_Division_ID Fld_Contact_Email Fld_Contact_Title Fld_Contact_Remark status aci_contact entry_date
								
								public function valid_modif_contact_company()
								{
									$sql="update tb_company_contact set Fld_Contact_Name='".addslashes($_POST['Fld_Contact_Name'])."',Fld_Contact_Phone='".$_POST['Fld_Contact_Phone']."',Fld_Contact_Phone2='".$_POST['Fld_Contact_Phone2']."',Fld_Contact_Fax='".$_POST['Fld_Contact_Fax']."',Fld_Company_Mobile='".$_POST['Fld_Company_Mobile']."',Fld_Contact_Division_ID='".$_POST['Fld_Contact_Division_ID']."',Fld_Contact_Email='".$_POST['Fld_Contact_Email']."',Fld_Contact_Title='".$_POST['Fld_Contact_Title']."',Fld_Contact_Remark='".addslashes($_POST['Fld_Contact_Remark'])."',whatsapp_number='".addslashes($_POST['whatsapp_number'])."',linkedin_url='".addslashes($_POST['linkedin_url'])."',facebook_url='".addslashes($_POST['facebook_url'])."',instagram_url='".addslashes($_POST['instagram_url'])."',social_network_notes='".addslashes($_POST['social_network_notes'])."',modified_date=NOW() where id_company_contact='".$_POST['id_company_contact']."'";
									//echo $sql;
									
									$query=mysql2_query($sql);
								}
								public function valid_modif_contact_company_multi()
								{
									for($i=1;$i<=$_POST['nbcontact'];$i++)
									{
										$idcontact=$_POST['id_company_contact'.$i];
									$sql="update tb_company_contact set Fld_Contact_Name='".addslashes($_POST['Fld_Contact_Name'.$i])."',Fld_Contact_Phone='".$_POST['Fld_Contact_Phone'.$i]."',Fld_Contact_Phone2='".$_POST['Fld_Contact_Phone2'.$i]."',Fld_Contact_Fax='".$_POST['Fld_Contact_Fax'.$i]."',Fld_Company_Mobile='".$_POST['Fld_Company_Mobile'.$i]."',Fld_Contact_Division_ID='".$_POST['Fld_Contact_Division_ID'.$i]."',Fld_Contact_Email='".$_POST['Fld_Contact_Email'.$i]."',Fld_Contact_Title='".$_POST['Fld_Contact_Title'.$i]."',Fld_Contact_Remark='".addslashes($_POST['Fld_Contact_Remark'.$idcontact])."',whatsapp_number='".addslashes($_POST['whatsapp_number'.$i])."',linkedin_url='".addslashes($_POST['linkedin_url'.$i])."',facebook_url='".addslashes($_POST['facebook_url'.$i])."',instagram_url='".addslashes($_POST['instagram_url'.$i])."',social_network_notes='".addslashes($_POST['social_network_notes'.$i])."',modified_date=NOW() where id_company_contact='".$idcontact."'";
									//echo $sql;
									
									$query=mysql2_query($sql);
									}
								}
								public function ajout_contact_company()
								{
									
									//Ajout Contact Company
									if (!empty($_POST['Fld_Company_ID'])) $Fld_Company_ID=$_POST['Fld_Company_ID'];
									else {
									$companyid = explode(",", $_POST['companyid']);
									$Fld_Company_ID=$companyid[1]; 
										}
										$today = date("Y-m-d");
								for($i=1;$i<=$_POST['nbcontactcompanyajout'];$i++)
								{
								$req="INSERT INTO tb_company_contact (`id_company_contact`,`Fld_Linked_ID`,`Fld_Company_ID`,`Company_Old_Id`,`Fld_Contact_Name`,`Fld_Contact_Phone`,`Fld_Contact_Phone2`,`Fld_Contact_Fax`,`Fld_Company_Mobile`,`Fld_Contact_Division_ID`,`Fld_Contact_Email`,`Fld_Contact_Title`,`Fld_Contact_Remark`,`status`,`aci_contact`,`entry_date`,`modified_date`,`whatsapp_number`,`linkedin_url`,`facebook_url`,`instagram_url`,`social_network_notes`)
								VALUES ('','','".$Fld_Company_ID."','','".addslashes($_POST['Fld_Contact_Name'.$i])."','".addslashes($_POST['Fld_Contact_Phone'.$i])."','".$_POST['Fld_Contact_Phone2'.$i]."','".$_POST['Fld_Contact_Fax'.$i]."','".$_POST['Fld_Company_Mobile'.$i]."','".$_POST['Fld_Contact_Division_ID'.$i]."','".$_POST['Fld_Contact_Email'.$i]."','".addslashes($_POST['Fld_Contact_Title'.$i])."','".addslashes($_POST['Fld_Contact_Remark'.$i])."','Available','".$_SESSION['id_utilisateur']."','".$today."',NOW(),'".addslashes($_POST['whatsapp_number'.$i])."','".addslashes($_POST['linkedin_url'.$i])."','".addslashes($_POST['facebook_url'.$i])."','".addslashes($_POST['instagram_url'.$i])."','".addslashes($_POST['social_network_notes'.$i])."')";
								
								$requete = mysql2_query($req);
								}
								//Fin Ajout Contact Company
								}
								public function ajout_contact_company_unique()
{
    // 1) Sécurisation de l'ID compagnie
    $Fld_Company_ID = isset($_POST['Fld_Company_ID'])
        ? (int)$_POST['Fld_Company_ID']
        : 0;

    $today = date("Y-m-d");

    // 2) RÉCUPÉRATION ROBUSTE DU NOM
    //    On essaie plusieurs noms de champs possibles.
    $contact_name = '';
    if (!empty($_POST['Fld_Contact_Name'])) {
        $contact_name = $_POST['Fld_Contact_Name'];
    } elseif (!empty($_POST['Fld_Contact_Name1'])) {
        // au cas où le JS crée Fld_Contact_Name1
        $contact_name = $_POST['Fld_Contact_Name1'];
    } elseif (!empty($_POST['contact_name'])) {
        // au cas où le champ s'appelle simplement "contact_name"
        $contact_name = $_POST['contact_name'];
    }

    // 3) (OPTIONNEL MAIS TRÈS UTILE) – petit log pour vérifier
    //    Tu pourras le commenter plus tard.
    @file_put_contents(
        __DIR__ . '/../logs_debug_contact.txt',
        "----- ".date('Y-m-d H:i:s')." -----\n".
        print_r($_POST, true)."\n\n",
        FILE_APPEND
    );

    // 4) Récupération des autres champs comme avant
    $phone      = isset($_POST['Fld_Contact_Phone'])     ? $_POST['Fld_Contact_Phone']     : '';
    $phone2     = isset($_POST['Fld_Contact_Phone2'])    ? $_POST['Fld_Contact_Phone2']    : '';
    $fax        = isset($_POST['Fld_Contact_Fax'])       ? $_POST['Fld_Contact_Fax']       : '';
    $mobile     = isset($_POST['Fld_Company_Mobile'])    ? $_POST['Fld_Company_Mobile']    : '';
    $division   = isset($_POST['Fld_Contact_Division_ID']) ? (int)$_POST['Fld_Contact_Division_ID'] : 0;
    $email      = isset($_POST['Fld_Contact_Email'])     ? $_POST['Fld_Contact_Email']     : '';
    $title      = isset($_POST['Fld_Contact_Title'])     ? $_POST['Fld_Contact_Title']     : '';
    $remark     = isset($_POST['Fld_Contact_Remark'])    ? $_POST['Fld_Contact_Remark']    : '';
    $whatsapp   = isset($_POST['whatsapp_number'])       ? $_POST['whatsapp_number']       : '';
    $linkedin   = isset($_POST['linkedin_url'])          ? $_POST['linkedin_url']          : '';
    $facebook   = isset($_POST['facebook_url'])          ? $_POST['facebook_url']          : '';
    $instagram  = isset($_POST['instagram_url'])         ? $_POST['instagram_url']         : '';
    $socialNotes = isset($_POST['social_network_notes']) ? $_POST['social_network_notes']  : '';

    // 5) Requête d'insert
    $req = "
        INSERT INTO tb_company_contact (
            `id_company_contact`,
            `Fld_Linked_ID`,
            `Fld_Company_ID`,
            `Company_Old_Id`,
            `Fld_Contact_Name`,
            `Fld_Contact_Phone`,
            `Fld_Contact_Phone2`,
            `Fld_Contact_Fax`,
            `Fld_Company_Mobile`,
            `Fld_Contact_Division_ID`,
            `Fld_Contact_Email`,
            `Fld_Contact_Title`,
            `Fld_Contact_Remark`,
            `status`,
            `aci_contact`,
            `entry_date`,
            `modified_date`,
            `whatsapp_number`,
            `linkedin_url`,
            `facebook_url`,
            `instagram_url`,
            `social_network_notes`
        ) VALUES (
            '',
            '',
            '".$Fld_Company_ID."',
            '',
            '".addslashes($contact_name)."',
            '".addslashes($phone)."',
            '".addslashes($phone2)."',
            '".addslashes($fax)."',
            '".addslashes($mobile)."',
            '".$division."',
            '".addslashes($email)."',
            '".addslashes($title)."',
            '".addslashes($remark)."',
            'Available',
            '".$_SESSION['id_utilisateur']."',
            '".$today."',
            NOW(),
            '".addslashes($whatsapp)."',
            '".addslashes($linkedin)."',
            '".addslashes($facebook)."',
            '".addslashes($instagram)."',
            '".addslashes($socialNotes)."'
        )
    ";

    $requete = mysql2_query($req);

								//Fin Ajout Contact Company
								}
								/***********************************************FLEET****************************************/
								//tbl_Fleet **** id_Fleet  Fld_Link_Id  Fld_Company_ID Company_Old_Id  Fld_Region  Fld_Engine  Fld_Unit  Fld_AC_ID   msn   immat						
								public function add_aircraft_fleet()
								{
								$req="INSERT INTO `tbl_Fleet` (`id_Fleet`, `Fld_Link_Id`, `Fld_Company_ID`, `Company_Old_Id`, `Fld_Region`, `Fld_Engine`, `Fld_Unit`, `Fld_AC_ID`, `msn`, `immat`) VALUES ('', '', '".$_POST['Fld_Company_ID']."', '', '".$_POST['Fld_Region']."', '".$_POST['Fld_Engine']."', '".$_POST['Fld_Unit']."', '".$_POST['Fld_AC_ID']."', '".$_POST['msn']."', '".$_POST['immat']."');";
								
								// echo $req;
								$requete = mysql2_query($req);
								}
								public function valid_modif_aircraft_fleet()
								{
									for($i=1;$i<=$_POST['nbaircraft'];$i++)
								{
								$req="update tbl_Fleet set Fld_Region='".$_POST['Fld_Region'.$i]."',Fld_Engine='".$_POST['Fld_Engine'.$i]."',Fld_Unit='".$_POST['Fld_Unit'.$i]."',Fld_AC_ID='".$_POST['Fld_AC_ID'.$i]."',msn='".$_POST['msn'.$i]."',immat='".$_POST['immat'.$i]."' where id_Fleet='".$_POST['id_Fleet'.$i]."'";
								
								$requete = mysql2_query($req);
								}
									
								}
								/***********************************************END FLEET************************************/
								
								/***********************************************BANK ACCOUNT****************************************/
								//**tbl_Company_Bank_Account**   Fld_Linked_ID  Fld_Company_ID  Fld_Bank_Acct_Nbr  Fld_Bank_Name  Fld_Bank_Address  Fld_ABA_Routing_Nbr  Fld_Swift_Nbr  Fld_Reference  branch_nbr  bank_nbr  comments							
								public function add_bank_account()
								{
								$req="INSERT INTO `tbl_Company_Bank_Account` (`Fld_Linked_ID`, `Fld_Company_ID`, `Fld_Bank_Acct_Nbr`, `Fld_Bank_Name`, `Fld_Bank_Address`, `Fld_ABA_Routing_Nbr`, `Fld_Swift_Nbr`, `Fld_Reference`, `branch_nbr`, `bank_nbr`, `comments`) VALUES ('', '".$_POST['Fld_Company_ID']."', '".$_POST['Fld_Bank_Acct_Nbr']."', '".$_POST['Fld_Bank_Name']."', '".addslashes($_POST['Fld_Bank_Address'])."', '".$_POST['Fld_ABA_Routing_Nbr']."', '".$_POST['Fld_Swift_Nbr']."', '".$_POST['Fld_Reference']."', '".$_POST['branch_nbr']."', '".$_POST['bank_nbr']."', '".addslashes($_POST['comments'])."');";
								
								// echo $req;
								$requete = mysql2_query($req);
								}
								public function valid_modif_bank_account()
								{
									for($i=1;$i<=$_POST['nbbankaccount'];$i++)
								{
								$req="update tbl_Company_Bank_Account set Fld_Bank_Acct_Nbr='".$_POST['Fld_Bank_Acct_Nbr'.$i]."',Fld_Bank_Name='".$_POST['Fld_Bank_Name'.$i]."',Fld_Bank_Address='".$_POST['Fld_Bank_Address'.$i]."',Fld_ABA_Routing_Nbr='".$_POST['Fld_ABA_Routing_Nbr'.$i]."',Fld_Swift_Nbr='".$_POST['Fld_Swift_Nbr'.$i]."',Fld_Reference='".$_POST['Fld_Reference'.$i]."',branch_nbr='".$_POST['branch_nbr'.$i]."',bank_nbr='".$_POST['bank_nbr'.$i]."',comments='".addslashes($_POST['comments'.$i])."' where Fld_Linked_ID='".$_POST['Fld_Linked_ID'.$i]."'";
								
								$requete = mysql2_query($req);
								}
									
								}
								/***********************************************END BANK ACCOUNT**********************************/
								
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//*****************************  SHIPPERS  *******************************************************************
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								// ** tbl_Shipper **  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
								public function affichage_shippers()
								{
									$res=array();
									$req="SELECT * FROM tbl_Shipper order by Fld_Shipper_Text";
									$requete=mysql2_query($req);
									
									while($reponse=mysqli_fetch_array($requete))
										{
									$res[]=$reponse;
										}
								return $res;
								}
								public function add_shipper()
								{
									$requete = mysql2_query("INSERT INTO tbl_Shipper (`Fld_Shipper_ID`,`Fld_Shipper_Text`, `Fld_Shipper_Contact_Name`, `Fld_Shipper_Contact_Phone`)
									VALUES ('','".addslashes($_POST['Fld_Shipper_Text'])."','".$_POST['Fld_Shipper_Contact_Name']."','".$_POST['Fld_Shipper_Contact_Phone']."');");
								}
								
								public function modif_shipper()
								{
									$sql="update tbl_Shipper set Fld_Shipper_Text='".addslashes($_POST['Fld_Shipper_Text'])."',Fld_Shipper_Contact_Name='".addslashes($_POST['Fld_Shipper_Contact_Name'])."',Fld_Shipper_Contact_Phone='".$_POST['Fld_Shipper_Contact_Phone']."' where Fld_Shipper_ID='".$_POST['Fld_Shipper_ID']."'";
									$query=mysql2_query($sql);
								}
								public function del_shipper($Fld_Shipper_ID)
								{
									$result = mysql2_query("DELETE FROM tbl_Shipper where Fld_Shipper_ID='".$Fld_Shipper_ID."'"); 
								}
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								/*****************************  END SHIPPERS  ****************************************************************/
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								
								
								
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//*****************************  FORWARDER  *******************************************************************
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								//**tbl_Forwarder**   Fld_Linked_ID  Company_Old_Id  Fld_Company_ID  Fld_Shipper_ID  Fld_Account_Nbr Fld_Remark Fld_Shipper_Contact_Name_Forw  Fld_Shipper_Contact_Phone_Forw
								public function affichage_forwarders()
								{
									$res=array();
									$req="SELECT * FROM tbl_Forwarder order by Fld_Shipper_Text";
									$requete=mysql2_query($req);
									
									while($reponse=mysqli_fetch_array($requete)){
									$res[]=$reponse;
																				}
								return $res;
								}
								public function add_forwarder()
								{
									$requete = mysql2_query("INSERT INTO tbl_Forwarder (`Fld_Linked_ID`,`Company_Old_Id`, `Fld_Company_ID`, `Fld_Shipper_ID`, `Fld_Account_Nbr`, `Fld_Remark`, `Fld_Shipper_Contact_Name_Forw`, `Fld_Shipper_Contact_Phone_Forw`)
									VALUES ('','','".$_POST['Fld_Company_ID']."','".$_POST['Fld_Shipper_ID']."','".$_POST['Fld_Account_Nbr']."','".addslashes($_POST['Fld_Remark'])."','".$_POST['Fld_Shipper_Contact_Name_Forw']."','".$_POST['Fld_Shipper_Contact_Phone_Forw']."');");
								}
								
								public function modif_forwarder()
								{	
									for($i=1;$i<=$_POST['nbforwarder'];$i++)
									{
									$sql="update tbl_Forwarder set Fld_Shipper_ID='".$_POST['Fld_Shipper_ID'.$i]."',Fld_Shipper_Contact_Name_Forw='".$_POST['Fld_Shipper_Contact_Name_Forw'.$i]."',Fld_Shipper_Contact_Phone_Forw='".$_POST['Fld_Shipper_Contact_Phone_Forw'.$i]."',Fld_Account_Nbr='".$_POST['Fld_Account_Nbr'.$i]."',Fld_Remark='".addslashes($_POST['Fld_Remark'.$i])."' where Fld_Linked_ID='".$_POST['Fld_Linked_ID'.$i]."'";
									$query=mysql2_query($sql);
									}
								}
								public function del_forwarder($Fld_Linked_ID)
								{
									$result = mysql2_query("DELETE FROM tbl_Forwarder where Fld_Linked_ID='".$Fld_Linked_ID."'"); 
								}
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								/*****************************  END FORWARDER  ****************************************************************/
								/*************************************************************************************************************/
								/*************************************************************************************************************/
								
								//***********************************************************************************************************/
								//******************************************************COMPETITOR*******************************************/
								//**tbl_Competitor**  Fld_Linked_ID Fld_Company_ID  Fld_Competitor_ID
								public function del_competitor($Fld_Linked_ID)
								{
									$result = mysql2_query("DELETE FROM tbl_Competitor where Fld_Linked_ID='".$Fld_Linked_ID."'"); 
								}
								public function add_competitor()
								{
									if (!empty($_POST['companyid'])) {
									$companyid = explode(",", $_POST['companyid']);
									$companyidrecup=$companyid[1]; 
										}
									$requete = mysql2_query("INSERT INTO tbl_Competitor (`Fld_Linked_ID`,`Fld_Company_ID`, `Fld_Competitor_ID`)
									VALUES ('','".$_POST['Fld_Company_ID']."','".$companyidrecup."');");
								}
								//******************************************************END COMPETITOR***************************************/	
								//***********************************************************************************************************/
								
									/*************************************************************************************************************************************/
									/*******************************************************DOCS**************************************************************************/
									/*************************************************************************************************************************************/
									public function add_docs_company()
									{
									//********************************telechargement du document
									//******************************************************
									
									//je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									if(!empty($_FILES["docsattachmentcompany"]["name"]))
									{
										$nom_docs_attachment=$_FILES["docsattachmentcompany"]["name"];
										$nom_docs_attachment=str_replace(' ', '_', $nom_docs_attachment); // on remplace les espaces par des _
										
									
									
									//Fin je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
									$fichelogo="";
									$target_dir = "../docsattachmentcompany/";
									// $target_file = $target_dir . basename($_FILES["docsattachmentcompany"]["name"]);
									$target_file = $target_dir . $nom_docs_attachment;
									$uploadOk = 1;
									$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
									
									// Check if file already exists
									if (file_exists($target_file)) {
										echo "Désolé, ce fichier existe déjà.";
										$uploadOk = 0;
									}
									// Check file size
									if ($_FILES["docsattachmentcompany"]["size"] > 10000000) {
										echo "Désolé, votre fichier est trop volumineux.";
										$uploadOk = 0;
									}
									// Allow certain file formats
									if($imageFileType == "exe" ) {
										echo "Désolé, les fichiers EXE sont pas autorisés.";
										$uploadOk = 0;
									}
									// Check if $uploadOk is set to 0 by an error
									if ($uploadOk == 0) {
										echo "Désolé, votre fichier n'a pas été téléchargé.";
									// if everything is ok, try to upload file
									} else {
										if (move_uploaded_file($_FILES["docsattachmentcompany"]["tmp_name"], $target_file)) {
											echo "Le fichier ". basename( $_FILES["docsattachmentcompany"]["name"]). " a été téléchargé.";
											$fichelogo="ok";
											
										} 
										else {
											echo "Désolé, il y a une erreur de chargement dans votre fichier.";
											$fichelogo="no";
										}
									}
									
									}
									
									//********************************Fin telechargement du document
									//**********************************************************
									//**tbl_docs_attachment_company** id_docs_attachment_company	name	docs_name	id_company
									 $req="INSERT INTO tbl_docs_attachment_company (`id_docs_attachment_company`,`name`,`docs_name`, `id_company`)
									VALUES ('','".$_POST['docs_name']."','".$nom_docs_attachment."','".$_POST['id_company']."');";
									// echo $req;
									$requete = mysql2_query($req);
		
									}
									public function del_doc_company($id_docs_attachment_company)
									{	
										$sqldapn="SELECT * FROM tbl_docs_attachment_company where id_docs_attachment_company='".$id_docs_attachment_company."'";
																			$reqdapn = mysql2_query($sqldapn);
																			$datadapn = mysqli_fetch_array($reqdapn);
																			$docs_name=$datadapn['docs_name'];
										$result = mysql2_query("DELETE FROM tbl_docs_attachment_company where id_docs_attachment_company='".$id_docs_attachment_company."'"); 
										unlink ("../docsattachmentcompany/".$docs_name); 
									}
									
									/*************************************************************************************************************************************/
									/*******************************************************END DOCS**********************************************************************/
									/*************************************************************************************************************************************/
								
								
}
?>
