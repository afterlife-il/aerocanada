<?php
session_start();
if(!empty($_GET['id'])){
include_once "conf.php";
include_once "page_titles.php";
    // echo $_GET['id'];
	
	
	
}

//echo "test".$_GET['id'];

/*
			//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
*/
					$sql="SELECT * from tbl_RFQ_1 where ID='".$_GET['id']."'";
					
					//echo $sql;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
					
?>
						<!---new--->
					<input type="hidden" name="rfqok" value="ok">
					<input type="hidden" name="idrfq1" value="<?php echo $data["ID"];?>">
					<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $data["Fld_RFQ_ID"];?>">
						<div class="row">
								<div class="col-lg-5">
										<div class="form-group has-warning">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="Fld_RFQ_ID" value="<?php echo $data["Fld_RFQ_ID"];?>">
                                        </div>
								</div>
								
								<div class="col-lg-5">
										<div class="form-group has-warning">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo $data["date"];?>">
                                    </div>
								</div>
								
							</div>
							<div class="row">
								<div class="col-lg-5">
										<div class="form-group has-warning">
                                            <label>CUSTOMER'S NAME</label><br>
											<?php
											//recuperation du nom de compagnie ********************
											$sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$data["Fld_Customer_ID"]."'";
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation du nom de compagnie ********************
											?>
											<input type="text" name="companyidpopup" id="companyidpopup" class="companyidpopup" value="<?php echo $datarn['Fld_Company_Name'].",".$data["Fld_Customer_ID"];?>">
                                        </div>
								</div>
								<div class="col-lg-5" id='bloccontactnamepopup'>
										<div class="form-group has-warning" id='divcontactnamepopup'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact" onclick="javascript:majtareapopup();">
											<?php
											//recuperation des contacts de compagnie
											// **tb_company_contact** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											
					                        $sqlcc="SELECT * FROM tb_company_contact where Fld_Company_ID='".$data["Fld_Customer_ID"]."' AND Fld_Contact_Name!='' AND status='available' ORDER BY Fld_Contact_Name";
											
											$reqcc = mysql2_query($sqlcc);
											while($datacc = mysqli_fetch_array($reqcc)){
												echo "<option value='".$datacc['id_company_contact']."'";
												if($datacc['id_company_contact']==$data["id_company_contact"]) echo " selected";
												echo ">".$datacc['Fld_Contact_Name']."</option>";
											}
					                        //Fin recuperation des contacts de compagnie
											?>

                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-5">
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
												if ($datarfqt['Fld_RFQ_Type_ID']==$data["Fld_RFQ_Type_ID"]) echo " selected";
												echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-5">
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
												if ($dataptid['Fld_Payment_Term_ID']==$data["Fld_Payment_Term_ID"]) echo " selected";
												echo ">".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-5">
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
												if ($dataPriority['Fld_Priority_ID']==$data["Fld_Priority_ID"]) echo " selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-5">
										<div class="form-group has-warning">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$data["Employee_ID"]) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-10">
										<div class="form-group">
                                            <label style="color:#a7142a;">INTERNAL REMARK</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
											box-shadow: 0 0 10px #a7142a;"><?php echo $data["Fld_Observation"];?></textarea>
                                        </div>
                                </div>
							</div>
							<div class="row">
									<div class="col-lg-3">
										<div class="form-group has-warning">
                                            <label>PN</label>
                                            <input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $data["pn_rfq"];?>"> 
                                        </div>
									</div>
									<div class="col-lg-4">
										<div class="form-group has-warning">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description_rfq" id="description_rfq" value="<?php echo $data['description_rfq'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="<?php echo $data['Fld_Qty'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-3">
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
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
							</div>
							<hr>
							<!--Je verifie si une quotation a ete saisie pour ce rfq-->
							<?php
							//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID  id_tbl_rfq1
							
							$resultrfq3 = mysql2_query("SELECT * FROM tbl_RFQ_3 where Fld_RFQ_ID='".$data["Fld_RFQ_ID"]."'");											
							$num_rows_rfq3 = mysqli_num_rows($resultrfq3);
							if(0<$num_rows_rfq3){
							$datarfq3 = mysqli_fetch_array($resultrfq3);
							echo "<input type='hidden' name='quoteok' value='ok'><input type='hidden' name='idrfq3' value='".$datarfq3["ID"]."'>";
							
							}
								?>
							<!--Je verifie si une quotation a ete saisie pour ce rfq-->
							<div class="row">

									<div class="col-lg-4">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
											<?php
											//recuperation du nom de compagnie TAG INFO********************
											$sqltirecup="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$datarfq3['Fld_Tag_Info_ID']."'";
											$reqtirecup = mysql2_query($sqltirecup);
											$datatirecup = mysqli_fetch_array($reqtirecup);
											//Fin recuperation du nom de compagnie TAG INFO********************
											?>
                                            <input type="text" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" value="<?php if (!empty($datarfq3['Fld_Tag_Info_ID'])) echo $datatirecup['Fld_Company_Name'].",".$datarfq3['Fld_Tag_Info_ID'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" id="Fld_Tag_Date" value="<?php echo $datarfq3['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>TRACED TO</label><br>
											<?php
											//recuperation du nom de compagnie TRACED TO********************
											$sqlttrecup="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$datarfq3['Fld_Traceability_ID']."'";
											$reqttrecup = mysql2_query($sqlttrecup);
											$datattrecup = mysqli_fetch_array($reqttrecup);
											//Fin recuperation du nom de compagnie TRACED TO********************
											?>
											<input type="text" name="Fld_Traceability_ID" id="Fld_Traceability_ID" class="Fld_Traceability_ID" value="<?php if (!empty($datarfq3['Fld_Traceability_ID'])) echo $datattrecup['Fld_Company_Name'].",".$datarfq3['Fld_Traceability_ID'];?>">
                                        </div>
									</div>
									
						   </div>
						   <div class="row">
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<option></option>
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'";
												if($datar['Fld_Release_ID']==$datarfq3['Fld_Release_ID']) echo " selected";
												echo ">".$datar['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" id="Fld_Part_SN" value="<?php echo $datarfq3['Fld_Part_SN'];?>">
                                        </div>
                                    </div>
									
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>MOQ</label>
                                            <input class="form-control" name="moq" id="moq" value="<?php echo $datarfq3['moq'];?>">
                                        </div>
                                    </div>
									
						   </div>
						   <div class="row">
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>LEAD TIME</label>
                                            <input class="form-control" name="lead_time" id="lead_time" value="<?php echo $datarfq3['lead_time'];?>">
                                        </div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" id="Fld_Price" value="<?php echo $datarfq3['Fld_Price'];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="FldCurrencyID" id="FldCurrencyID">
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'";
												if($datacid['Fld_Currency_ID']==$datarfq3['Fld_Currency_ID']) echo " selected";
												echo ">".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
							</div>	
							<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>COMMENTS FOR THE CLIENT</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"><?php echo $datarfq3['Fld_Remark'];?></textarea>
                                        </div>
                                    </div>
									
							</div>
							
						<input type="hidden" name="quote_type" value="suppliers_quote">
						<input type="hidden" name="part_id" value="<?php echo $data["Fld_Part_ID"];?>">
						<input type="hidden" name="Fld_Part_Nbr" value="<?php echo $data["pn_rfq"];?>">
						<input type="hidden" name="Fld_Part_Desc" value="<?php echo $data['description_rfq'];?>">
						<input type="hidden" name="rfqvalid" value="<?php echo $datarfq3['rfqvalid'];?>">
					
						<div  id='blocquotecustomer'>
                           <div class="row" id='divquotecustomer'>
								<div class="col-lg-12">
										<div class="form-group" style="text-align:center;">
                                        </div>
								</div>
								<input type="hidden" class="form-control" name="qtc" value="valid">
						   </div>
						</div>
						<input type="hidden" class="form-control" name="quotethecustomer" value="">
						<div class="row">
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SAVE" name=button1 onclick="return OnButton3();">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SEND QUOTATION" name=button2 onclick="return OnButton4();">
										</div>
								</div>									
						</div>	
					
						
						
						<!--Ajout pour autocompression Roy-->
						 <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
	
	    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
	

	
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
    <script src="js/typeahead.js"></script>
    <style>

		.tt-hint,
        .companyidpopup,.companyidforoem,.Fld_Tag_Info_ID,.Fld_Traceability_ID,.companyidtaginfo,.companyidtreacability {
            display: block;
    width: 100%;
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
        }

        .tt-dropdown-menu {
            width: 400px;
            margin-top: 5px;
            padding: 8px 12px;
            background-color: #fff;
            border: 1px solid #ccc;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px 8px 8px 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
        $(document).ready(function() {

            $('input.companyidpopup').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidforoem').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });

			$('input.Fld_Tag_Info_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtaginfo').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtreacability').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Traceability_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->


 </script>