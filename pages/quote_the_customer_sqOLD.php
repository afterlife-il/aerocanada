<?php 
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
					
?>
<form method="post" action="email_broadcast.php">
<input type="hidden" name="quote_type" value="suppliers_quote">
<input type="hidden" name="Fld_Part_ID" value="<?php echo $data["Fld_Part_ID"];?>">
        <div class="panel panel-default" id='divquotecustomer'>
                        <div class="panel-heading">
                            QUOTE THE CUSTOMER
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="RFQ_ID" value="<?php echo $data["Fld_RFQ_ID"];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo $data["Fld_Current_Date"];?>">
                                    </div>
								</div>
								<div class="col-lg-6">
								</Div>
						   </div>
						   <div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" id="Fld_Part_Nbr" value="<?php echo $data2["Fld_Part_Nbr"];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="Fld_Part_Desc" id="Fld_Part_Desc" value="<?php echo $data2['Fld_Part_Desc'];?>">
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
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']==$data['Fld_Condition_ID'])echo "selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?> 
                                            </select>
                                        </div>
									</div>
							</div>
							<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" id="Fld_Part_SN" value="<?php echo $data['Fld_Part_SN'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>MOQ (Minimum Qty)</label>
                                            <input class="form-control" name="moq" id="moq" value="">
                                        </div>
                                    </div>
									
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>REMARKS</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"><?php echo htmlspecialchars($data['Fld_Remark']);?></textarea>
                                        </div>
                                    </div>
						   </div>
						   <div class="row">
									
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sql4="SELECT * from tbl_Release";
											
											$req4 = mysql2_query($sql4);
											while($data4 = mysqli_fetch_array($req4)){
												echo "<option value='".$data4['Fld_Release_ID']."'";
												if ($data['Fld_Release_ID']==$data4['Fld_Release_ID']) echo "selected";
												echo ">".$data4['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG INFO</label>
											<?php
											//recuperation du nom de compagnie
					                        $sqlemp="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID='".$data["Fld_Tag_Info_ID"]."'";
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation du nom de compagnie
											?>
                                            <input class="form-control" name="Fld_Company_Name" id="Fld_Company_Name" value="<?php echo $dataemp["Fld_Company_Name"];?>">
											<input type="hidden" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" value="<?php echo $dataemp["Fld_Company_Name"].",".$data["Fld_Tag_Info_ID"];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" id="Fld_Tag_Date" value="<?php echo $data["Fld_Tag_Date"];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>Traced To</label>
                                            <input class="form-control" name="Fld_Traceability_ID" id="Fld_Traceability_ID" value="<?php echo $data["Fld_Traceability_ID"];?>">
                                        </div>
									</div>
						   </div>
						   <div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>STOCK LOC / LEAD TIME</label>
                                            <input class="form-control" name="lead_time" id="lead_time" value="<?php echo $data["lead_time"];?>">
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
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'>".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
                                           
                                        </div>
									</div>
									
							</div>		
						   <div class="row">
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" >
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
												echo "<option value='".$datacid['Fld_Currency_ID']."'>".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-4">
										<div class="form-group" align="right">
										<input type="hidden" class="form-control" name="qtc" value="valid">
										<button type="submit" class="btn btn-default">SEND QUOTATION</button>
										</div>
								</div>
						   </div>
				
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
					
	</form>