  <?php 
include_once "conf.php";
//echo "test".$_GET['id'];

/*
			//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
*/
					$sql="SELECT * from tbl_RFQ_1 where ID='".$_GET['id']."'";
					
					//echo $sql;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
					
?>
  
  <div class="panel panel-default" id='divrecuprfq'>
                        <div class="panel-heading">
                            REQUEST FOR QUOTATION
                        </div>
                        <!-- /.panel-heading -->
						<form action="valid_add_rfq.php" method="post">
						<input type="hidden" name="Fld_Part_ID" value="<?php echo $data['Fld_Part_ID'];?>">
                        <div class="panel-body">
                           <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo $data['date'];?>">
                                    </div>
								</div>
								<div class="col-lg-6">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID">
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'";
												if($data['Fld_RFQ_Type_ID']==$datarfqt['Fld_RFQ_Type_ID']) echo "selected";
												echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
												if($data['Fld_Priority_ID']==$dataPriority['Fld_Priority_ID']) echo "selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$data['Employee_ID']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
							</div>

						   <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>CUSTOMER'S NAME</label>
											<?php
											//recuperation du nom de la societe ********************
											//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete    companyrating    aci_contact  logocompany status internet  cage_code
											$sqlls="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Customer_ID'];
											$reqls = mysql2_query($sqlls);
											$datals = mysqli_fetch_array($reqls);
											//Fin recuperation du nom de la societe ********************
											?>
											<input type="text" name="companyid" id="companyid" class="companyid"  value="<?php echo $datals['Fld_Company_Name'];?>"  onchange='javascript:alert('+this+')'>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>CONTACT NAME</label>
                                            <div id='bloccontactname'><div id='divcontactname' >
											<select class="form-control" name="id_company_contact">
											<?php
											//recuperation des contacts de compagnie
											// **tb_company_contact** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											
					                        $sqlcc="SELECT * FROM tb_company_contact where Fld_Contact_Name!=''";
											
											$reqcc = mysql2_query($sqlcc);
											while($datacc = mysqli_fetch_array($reqcc)){
												echo "<option value='".$datacc['id_company_contact']."'";
												if ($datacc['id_company_contact']==$data['id_company_contact']) echo "selected";
												echo ">".$datacc['Fld_Contact_Name']."</option>";
											}
					                        //Fin recuperation des contacts de compagnie
											?>
                                                
                                            </select>
											</div></div>

                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>TERMS</label>
											<select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'";
												if ($dataptid['Fld_Payment_Term_ID']==$data['Fld_Payment_Term_ID']) echo "selected";
												echo ">".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
								</div>
							</div>
							 <div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $data["pn_rfq"];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description_rfq" id="description_rfq" value="<?php echo $data['description_rfq'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']==$data['Fld_Condition_ID']) echo "selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
							</div>
						   <div class="row">
									<div class="col-lg-6">
										<div class="form-group">
                                            <label style="color:#a7142a;">INTERNAL REMARK</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Observation" id="Fld_Observation" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
    box-shadow: 0 0 10px #a7142a;"><?php echo $data['Fld_Observation'];?></textarea>
                                        </div>
                                    </div>
									<div class="col-lg-6">
                                    </div>
						   </div>
						   <div class="row">
						   <div class="col-lg-4">
										<div class="form-group" align="right">
										<button  class="btn btn-default">OPEN RFQs</button>
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<button type="submit" class="btn btn-default">ADD RFQ</button>
										</div>
								</div>	
						   </div>						   
                        </div>
                        <!-- /.panel-body -->
						</form>
                    </div>
                    <!-- /.panel -->