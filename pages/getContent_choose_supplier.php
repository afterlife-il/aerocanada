<?php
session_start();

include_once "conf.php";
include_once "page_titles.php";
    
	

?>
						<!--************************************************-->
						<!--Verif si il y a des Supplier quote pour ce pn-->
						<?php
							//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  
							//Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
							$sqlrfq2="SELECT * from tbl_RFQ_2 where Fld_Part_ID='".$_GET['Fld_Part_ID']."' and Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID DESC";
							//echo $sqlrfq2;
							$reqrfq2 = mysql2_query($sqlrfq2);
							$numrows_SQ = mysqli_num_rows($reqrfq2);
						?>
						<!--Fin Verif si il y a des supliers quote pour ce pn-->
						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_SQ=='0'){ ?>style="display:none;"<?php } ?>>
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>SUPPLIER NAME</th>
											<th>QTY</th>
											<th>CONDITION</th>
                                            <th>PRICE</th>
                                            <th>LEAD TIME</th>
                                            <th>RELEASE</th>
                                            
										
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					
					while($datarfq2 = mysqli_fetch_array($reqrfq2))
					{
											//recuperation du nom de compagnie ********************
											$sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Supplier_ID'];
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation du nom de compagnie ********************
											
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID=".$datarfq2['Fld_Condition_ID'];
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT Fld_Currency_Text FROM tbl_Currency where Fld_Currency_ID=".$datarfq2["Fld_Currency_ID"];
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
					                        //End recuperation of the currency
											
											
											//recuperation Payment_Term
											$sqlRID="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$datarfq2["Fld_Release_ID"];
											
											$reqRID = mysql2_query($sqlRID);
											$dataRID = mysqli_fetch_array($reqRID);
											//Fin ecuperation Payment_Term				
  
                                            echo "<tr>";
											echo "<td style='border: 1px solid ".$prioritescss.";'><a href=\"details_rfq.php?Fld_RFQ_ID=".$_GET['Fld_RFQ_ID']."&Fld_Part_ID=".$_GET['Fld_Part_ID']."&idsupplier=".$datarfq2['ID']."\"><i class=\"fa  fa-plane\"></i></a></td>";
											// echo "<td><input type=\"radio\" name=\"suppliers_choice_id\" value='".$datarfq2['ID']."' onchange=\"quote_the_customer_sq('".$datarfq2['ID']."')\"></td>";
											echo "<td>".$datarn['Fld_Company_Name']."</td>
											<td>".$datarfq2['Fld_Qty']."</td>
											<td>".$datact['Fld_Condition_Text']."</td>
											<td>".$datarfq2['Fld_Price']." ".$datacid['Fld_Currency_Text']."</td>
											<td>".$datarfq2['lead_time']."</td>
											<td>".$dataRID['Fld_Release_Text']."</td>
											
											</tr>";
					}
?>					
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
					