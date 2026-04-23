<?php 
session_start();
include_once "conf.php";
include_once "page_titles.php";
//echo "test".$_GET['id'];

/*
			//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP
*/
					$sql="SELECT * from tbl_RFQ_2 where ID='".$_GET['id']."'";
					
					//echo $sql;
					$req = mysql2_query($sql); 
					$data = mysqli_fetch_array($req);
					
					/*Recuperation du pn et de la description pn*/
					/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql2="SELECT * from tbl_Parts where Fld_Part_ID='".$data["Fld_Part_ID"]."' and status='Available'";
					$req2 = mysql2_query($sql2);
					$data2 = mysqli_fetch_array($req2);
					/*Fin Recuperation du pn et de la description pn*/
					
					/*recuperation info de la table rfq*/
					/*
					//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
					*/
					$sql3="SELECT * from tbl_RFQ_1 where Fld_RFQ_ID='".$data["Fld_RFQ_ID"]."'";
					$req3 = mysql2_query($sql3);
					$data3 = mysqli_fetch_array($req3);
					/*END recuperation info de la table rfq*/
?>
						<!---new--->
						<div class="panel-body" id='blocrecuprfqquote'>  
						  <div id='divrecuprfqquote'>
						<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="RFQ_ID" value="<?php echo $data["Fld_RFQ_ID"];?>">
                                        </div>
								</div>
								
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo $data["Fld_Current_Date"];?>">
                                    </div>
								</div>
								<div class="col-lg-8">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>CUSTOMER'S NAME</label><br>
											<?php
											//recuperation du nom de compagnie ********************
											$sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$data3["Fld_Customer_ID"]."'";
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation du nom de compagnie ********************
											?>
											<input type="text" name="companyid" id="companyid" class="companyid" value="<?php echo $datarn['Fld_Company_Name'].",".$data3["Fld_Customer_ID"];?>">
											<input type="hidden" name="Fld_Customer_ID" value="<?php echo $data3["Fld_Customer_ID"];?>">
                                        </div>
								</div>
								<div class="col-lg-2" id='bloccontactname'>
										<div class="form-group has-warning" id='divcontactname'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact" onclick="javascript:majtarea();">
											<?php
											//recuperation des contacts de compagnie
											// **tb_company_contact** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											
					                        $sqlcc="SELECT * FROM tb_company_contact where Fld_Company_ID='".$data3["Fld_Customer_ID"]."' AND Fld_Contact_Name!='' AND status='available' ORDER BY Fld_Contact_Name";
											
											$reqcc = mysql2_query($sqlcc);
											while($datacc = mysqli_fetch_array($reqcc)){
												echo "<option value='".$datacc['id_company_contact']."'";
												if($datacc['id_company_contact']==$data3["id_company_contact"]) echo " selected";
												echo ">".$datacc['Fld_Contact_Name']."</option>";
											}
					                        //Fin recuperation des contacts de compagnie
											?>

                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID">
											<option></option>
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'";
												if ($datarfqt['Fld_RFQ_Type_ID']==$data3["Fld_RFQ_Type_ID"]) echo " selected";
												echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>TERMS</label>
											<select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'";
												if ($dataptid['Fld_Payment_Term_ID']==$data3["Fld_Payment_Term_ID"]) echo " selected";
												echo ">".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
												if ($dataPriority['Fld_Priority_ID']==$data3["Fld_Priority_ID"]) echo " selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$_SESSION['id_utilisateur']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4">
										<div class="form-group">
                                            <label style="color:#a7142a;">INTERNAL REMARK</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
											box-shadow: 0 0 10px #a7142a;"><?php echo $data3["Fld_Observation"];?></textarea>
                                        </div>
                                </div>
							</div>
							<div class="row">
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>PN</label>
                                            <input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $data3["pn_rfq"];?>"> 
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description_rfq" id="description_rfq" value="<?php echo $data3['description_rfq'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']==$data['Fld_Condition_ID']) echo " selected";
												echo ">".$datac['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
							</div>
							<hr>
							<div class="row">
									
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'";
												if($datar['Fld_Release_ID']==$data['Fld_Release_ID']) echo "selected";
												echo ">".$datar['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
											<?php    
											//recuperation du nom de compagnie pour TAG INFO********************
											$sqlrnti="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$data["Fld_Tag_Info_ID"]."'";
											$reqrnti = mysql2_query($sqlrnti);
											$datarnti = mysqli_fetch_array($reqrnti);
											//Fin recuperation du nom de compagnie pour TAG INFO********************
											?>
                                            <input type="text" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" value="<?php echo $datarnti['Fld_Company_Name'].",".$data["Fld_Tag_Info_ID"];?>" style="width: 335px;">
											<input type="hidden" name="Fld_Tag_Info_ID" value="<?php echo $datarnti['Fld_Company_Name'].",".$data["Fld_Tag_Info_ID"];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" id="Fld_Tag_Date" value="<?php echo $data['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TRACED TO</label><br>
											<?php
											//recuperation du nom de compagnie pour TRACED TO********************
											$sqlrntt="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$data["Fld_Traceability_ID"]."'";
											$reqrntt = mysql2_query($sqlrntt);
											$datarntt = mysqli_fetch_array($reqrntt);
											//Fin recuperation du nom de compagnie pour TRACED TO********************
											?>
											<input type="text" name="Fld_Traceability_ID" id="Fld_Traceability_ID" class="Fld_Traceability_ID" value="<?php echo $datarntt['Fld_Company_Name'].",".$data["Fld_Traceability_ID"];?>" >
											<input type="hidden" name="Fld_Traceability_ID" value="<?php echo $data["Fld_Traceability_ID"];?>">
                                        </div>
									</div>
						   </div>
						   <div class="row">
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>STOCK LOC / LEAD TIME</label>
                                            <input class="form-control" name="lead_time" id="lead_time" value="">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PRICE <?php if (!empty($data['Fld_Price'])){?>(SUPPLIER PRICE : <?php echo $data['Fld_Price'];?>)<?php }?></label>
                                            <input class="form-control" name="Fld_Price" id="Fld_Price" value="">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="FldCurrencyID" id="FldCurrencyID" onmouseleave='javascript:majpn(<?php echo $data['Fld_Part_ID'];?>)'>
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'>".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>COMMENTS FOR THE CLIENT</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"></textarea>
                                        </div>
                                    </div>
							</div>	
							<div class="row">
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" id="Fld_Part_SN" value="<?php echo $data['Fld_Part_SN'];?>">
                                        </div>
                                    </div>
									
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>MOQ (Minimum Qty)</label>
                                            <input class="form-control" name="moq" id="moq" value="">
                                        </div>
                                    </div>
									
						   </div>
						<input type="hidden" name="quote_type" value="suppliers_quote">
						<input type="hidden" name="part_id" value="<?php echo $_GET['part_id'];?>">
						<input type="hidden" name="Fld_Part_Nbr" value="<?php echo $data2['Fld_Part_Nbr'];?>">
						<input type="hidden" name="Fld_Part_Desc" value="<?php echo $data2['Fld_Part_Desc'];?>">
						<input type="hidden" class="form-control" name="qtc" value="valid">
						<input type="hidden" class="form-control" name="quotethecustomer" value="">
						
						<input type="hidden" name="quote_type" value="suppliers_quote">
						<input type="hidden" name="Fld_Part_ID" value="<?php echo $data["Fld_Part_ID"];?>">
						
						<div class="row">
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="ADD RFQ" name=button1 onclick="return OnButton1();">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SEND QUOTATION" name=button2 onclick="return OnButton2();">
										</div>
								</div>									
						</div>	
						</div>	
						</div>	