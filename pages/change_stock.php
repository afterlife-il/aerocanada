
<?php
include_once "conf.php";
include_once "page_titles.php";
		
/*
			Table tbl_Stock ::::   Fld_Stock_ID  Fld_Part_ID  Fld_Part_SN  Fld_Supplier_ID  Fld_Entry_Date  Fld_Part_Price  Fld_Price_Currency_ID  Fld_BAX_PO_Nbr  Fld_Supplier_order_Date  Fld_Supplier_Payment_Date  Fld_Qty  Fld_Condition_ID  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  Fld_Warehouse_Location  Fld_Physical_Stock  Fld_Owner_ID  Fld_Stock_Location_ID  Fld_Status_ID  Fld_Status_Ind  Fld_Status_Date  Fld_Stock_Remark  Fld_Shelf_Life_Limit  Fld_Valeur_Comptable  Fld_Valeur_Comptable_currency_Id  Fld_Sales_Remark  Fld_External_Location  Fld_Sales_Remark_ID  Fld_Warehouse_Location_ID  Fld_OriginalUnit_Stock_ID  Fld_Min_Qty  Fld_Publish
*/
					$sql2="SELECT * from tbl_Stock where Fld_Stock_ID='".$_GET['Fld_Stock_ID']."'";
					
					//echo $sql2;
					$req2 = mysql2_query($sql2);
					$data2 = mysqli_fetch_array($req2);
											
/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql3="SELECT * from tbl_Parts where Fld_Part_ID='".$data2["Fld_Part_ID"]."'";
					
					
					$req3 = mysql2_query($sql3);
					$data3 = mysqli_fetch_array($req3);
			?>
			<div class="panel panel-default">
                        <div class="panel-heading">
                            PN DETAILS <div align="right"><a href='javascript:close_stock_details()'><img src='../images/Fermeture.png' width='30'></a></div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
			
						<div class="row">
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" value="<?php echo $data3["Fld_Part_Nbr"];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="Fld_Part_Desc" value="<?php echo $data3['Fld_Part_Desc'];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">			
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" value="<?php echo $data2['Fld_Part_SN'];?>">
                                        </div>
						   </div>
						</div>
						<div class="row">
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" value="<?php echo $data2['Fld_Qty'];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>MOQ (Minimum Qty)</label>
                                            <input class="form-control" name="Fld_Min_Qty" value="<?php echo $data2['Fld_Min_Qty'];?>">
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
												if ($data2['Fld_Condition_ID']==$datac['Fld_Condition_ID']) echo "selected";
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
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'";
												if ($data2['Fld_Release_ID']==$datar['Fld_Release_ID']) echo "selected";
												echo ">".$datar['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG INFO ?????</label>
                                            <input class="form-control" name="Fld_Tag_Info_ID" value="<?php echo $data2['Fld_Tag_Info_ID'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" value="<?php echo $data2['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>Traced To</label>
                                            <input class="form-control" name="Fld_Traceability_ID" value="<?php echo $data2['Fld_Traceability_ID'];?>">
                                        </div>
									</div>
                        </div>
						<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>Entry Date</label>
											<?php 
											$datean = substr($data2['Fld_Entry_Date'], -2);
											$datej = substr($data2['Fld_Entry_Date'], 0,2);									
											$datem = substr($data2['Fld_Entry_Date'], 3,2);											
											$dateaen4= "20".$datean;
											$datett=$datej."-".$datem."-".$dateaen4;
											$modifdate = str_replace("-", "/", $datett);
											
											$date_us=$datem."/".$datej."/".$dateaen4;
											// echo $date_us;
											?>
                                            <input class="form-control" name="Fld_Entry_Date" value="<?php echo $modifdate;?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>No Days</label>
									<?php
								$datejour=date('m/d/Y');
								// echo $datejour;
									//calcule du nombre de jour entre deux dates
									// On transforme les 2 dates en timestamp
									$date1 = strtotime($date_us);
									$date2 = strtotime($datejour);
									
									// On récupère la différence de timestamp entre les 2 précédents
									$nbJoursTimestamp = $date2 - $date1;
									
									// ** Pour convertir le timestamp (exprimé en secondes) en jours **
									// On sait que 1 heure = 60 secondes * 60 minutes et que 1 jour = 24 heures donc :
									$nbJours = $nbJoursTimestamp/86400; // 86 400 = 60*60*24
									
									// echo "Nombre de jours : ".$nbJours;
									//Fin calcule du nombre de jour entre deux dates
									?>
                                            <input class="form-control" name="nbjour" value="<?php echo $nbJours;?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SHELF LIFE</label>
                                            <input class="form-control" name="Fld_Shelf_Life_Limit" value="<?php echo $data2['Fld_Shelf_Life_Limit'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SALES REMARKS</label>
                                            <input class="form-control" name="Fld_Sales_Remark" value="<?php echo $data2['Fld_Sales_Remark'];?>">
                                        </div>
									</div>
                        </div>
						<div class="row">
									<div class="col-lg-12">
										<div class="form-group">
                                            <label>REMARKS ??? (Fld_Stock_Remark ??)</label>
											<textarea class="form-control" rows="3" name="Fld_Stock_Remark"><?php echo htmlspecialchars($data2['Fld_Stock_Remark']);?></textarea>
                                        </div>
									</div>
                        </div>
						<!--Suppliers-->
						<div class="row">
									<div class="col-lg-3" style="background-color:#A71A29;">
									<h1 style="color:#fff;">SUPPLIER</h1>
									</div>
							</div>
						<div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Suppliers</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>ACI 770 PO#</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Supplier order date</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
							</div>
						<!--Fin Suppliers-->
						<!--STOCK DETAILS-->
							<div class="row">
									<div class="col-lg-3" style="background-color:#A71A29;">
									<h1 style="color:#fff;">STOCK DETAILS</h1>
									</div>
							</div>
						    <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Location</label>
											<select class="form-control" name="Fld_Stock_Location_ID">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_Stock_Location_ID']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Warehouse Loc.</label>
											<select class="form-control" name="Fld_Warehouse_Location_ID">
											<?php
											//recuperation Warehouse location
											// ** tbl_Warehouse_Location ** Id  Fld_Location
					                        $sqlsl="SELECT * from tbl_Warehouse_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Id']."'";
												if ($data2['Fld_Warehouse_Location_ID']==$datasl['Id']) echo "selected";
												echo ">".$datasl['Fld_Location']."</option>";
											}
					                        //Fin recuperation Warehouse location
											?>
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Outside Loc. Fld_External_Location?? (tbl_Stock_Location??)</label>
											<select class="form-control" name="Fld_External_Location">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_External_Location']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
								</div>
							</div>	
							<div class="row">
										<div class="col-lg-6">
										<div class="form-group">
											<label>PHYSICAL STK</label><input type="radio" name="stock_type_choice" value='PHYSICAL_STK'>
											<label>VIRTUAL STK</label><input type="radio" name="stock_type_choice" value='VIRTUAL_STK'>
                                        </div>
										</div>
										<div class="col-lg-3">
										<div class="form-group">
                                            <label>Owner  ???</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
										</div>
							</div>	
						<!--Fin STOCK DETAILS-->
											<!--STATUS-->
								<div class="row">
									<div class="col-lg-3" style="background-color:#A71A29;">
									<h1 style="color:#fff;">STATUS</h1>
									</div>
								</div>
								<div class="row">
								
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>STATUS</label>
											
                                            <select class="form-control" name="Fld_Status_ID">
											<?php
											//recuperation STATUS
											// ** tbl_Status ** Fld_Status_ID  Fld_Status_Text
					                        $sqlsid="SELECT * from tbl_Status";
											
											$reqsid = mysql2_query($sqlsid);
											while($datasid = mysqli_fetch_array($reqsid)){
												echo "<option value='".$datasid['Fld_Status_ID']."'";
												if ($data2['Fld_Status_ID']==$datasid['Fld_Status_ID']) echo "selected";
												echo ">".$datasid['Fld_Status_Text']."</option>";
											}
					                        //Fin recuperation STATUS
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>BASED UPON</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>STATUS DATE</label>
                                            <input class="form-control" name="Fld_Status_Date" value="<?php echo $data2['Fld_Status_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>ACCOUNTING VALUE</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
									</div>
								
							</div>	
							<!--Fin STATUS-->
                        </div>
                        </div>
