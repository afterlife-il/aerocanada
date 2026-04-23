<?php 
include_once "conf.php";
//echo "test".$_GET['id'];

/*
			Table tbl_Stock ::::   Fld_Stock_ID  Fld_Part_ID  Fld_Part_SN  Fld_Supplier_ID  Fld_Entry_Date  Fld_Part_Price  Fld_Price_Currency_ID  Fld_BAX_PO_Nbr  Fld_Supplier_order_Date  Fld_Supplier_Payment_Date  Fld_Qty  Fld_Condition_ID  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  Fld_Warehouse_Location  Fld_Physical_Stock  Fld_Owner_ID  Fld_Stock_Location_ID  Fld_Status_ID  Fld_Status_Ind  Fld_Status_Date  Fld_Stock_Remark  Fld_Shelf_Life_Limit  Fld_Valeur_Comptable  Fld_Valeur_Comptable_currency_Id  Fld_Sales_Remark  Fld_External_Location  Fld_Sales_Remark_ID  Fld_Warehouse_Location_ID  Fld_OriginalUnit_Stock_ID  Fld_Min_Qty  Fld_Publish
*/
					$sql="SELECT * from tbl_Stock where Fld_Stock_ID='".$_GET['id']."'";
					
					//echo $sql;
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);
					
					/*Recuperation du pn et de la description pn*/
					/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql2="SELECT * from tbl_Parts where Fld_Part_ID='".$data["Fld_Part_ID"]."' and status='Available'";
					$req2 = mysql2_query($sql2);
					$data2 = mysqli_fetch_array($req2);
					/*Fin Recuperation du pn et de la description pn*/

					//recuperation stock location
					// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					$sqlsl="SELECT * from tbl_Stock_Location where Fld_Stock_Location_ID=".$data['Fld_Stock_Location_ID'];
					
					$reqsl = mysql2_query($sqlsl);
					$datasl = mysqli_fetch_array($reqsl);
					// echo $datasl['Fld_Stock_Location_Text'];
					//Fin recuperation stock location
					//recuperation du nom de la compagnie
					$sqlcomn="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID=".$data['Fld_Tag_Info_ID'];
					
					$reqcomn = mysql2_query($sqlcomn);
					$datacn = mysqli_fetch_array($reqcomn);
					//Fin recuperation du nom de la compagnie
					
?>
<form method="post" action="email_broadcast.php">
<input type="hidden" name="quote_type" value="stock">
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
                                            <input class="form-control" name="RFQ_ID" value="<?php echo date("Y-m-d-His");?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo date("d/m/Y");?>">
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
												if ($data['Fld_Condition_ID']==$datac['Fld_Condition_ID']) echo "selected";
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
                                            <input class="form-control" name="Fld_Min_Qty" id="Fld_Min_Qty" value="<?php echo $data['Fld_Min_Qty'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>REMARKS</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"></textarea>
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
                                            <input class="form-control" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" value="<?php echo $datacn["Fld_Company_Name"];?>">
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
                                            <input class="form-control" name="" id="" value="<?php echo $datasl['Fld_Stock_Location_Text'];?>">
                                        </div>
									</div>
									<div class="col-lg-9">
										<div class="form-group">
                                            
                                        </div>
									</div>
									
									
							</div>		
						   <div class="row">
									<div class="col-lg-4">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="price" id="price" value="">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="Fld_Price_Currency_ID" id="Fld_Price_Currency_ID">
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'";
												if ($data["Fld_Price_Currency_ID"]==$datacid['Fld_Currency_ID']) echo "selected";
												echo ">".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-4">
										<div class="form-group" align="right">
										<button type="submit" class="btn btn-default">ADD RFQ<br>+<br>SEND QUOTATION</button>
										</div>
								</div>
						   </div>
				
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
					
	</form>