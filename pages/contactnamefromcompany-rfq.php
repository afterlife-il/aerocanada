<?php
include_once "conf.php";
include_once "page_titles.php";
$id_company=$_GET['id'];
//echo "id_company:".$id_company;
$companyid = explode(",", $id_company);
									$Fld_Company_ID=$companyid[0]; 
?>
<div class="form-group" id='divcontactname'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact" >
											<?php
											//recuperation des contacts de compagnie
											// **tb_company_contact** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											
					                        $sqlcc="SELECT * FROM tb_company_contact where Fld_Company_ID='".$Fld_Company_ID."' AND Fld_Contact_Name!='' AND status='available' ORDER BY Fld_Contact_Name";
											
											$reqcc = mysql2_query($sqlcc);
											while($datacc = mysqli_fetch_array($reqcc)){
												echo "<option value='".$datacc['id_company_contact']."'>".$datacc['Fld_Contact_Name']."</option>";
											}
					                        //Fin recuperation des contacts de compagnie
											?>
                                                
                                            </select>
											

                                        </div>