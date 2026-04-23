<?php 		
include_once "conf.php";
include_once "page_titles.php";		
//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
$sql="SELECT * from tbl_RFQ_2 where Fld_Supplier_ID='".$_GET['id']."' AND Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."'";
// echo $sql;
$req = mysql2_query($sql);
$data = mysqli_fetch_array($req);

//*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter   cage_code    essentiality_category_id    nha   moq   oem_lead_time  core_value  id_currency_core_value
											$sqlpncv="SELECT Fld_Part_Nbr FROM tbl_Parts WHERE Fld_Part_ID='".$data['Fld_Part_ID']."'";
											
											$reqpncv = mysql2_query($sqlpncv);
											$datapncv = mysqli_fetch_array($reqpncv);

			?>					
									
										<div class="form-group">
                                            <label>PN</label>
										<input class="form-control" name="Fld_Part_Nbr" placeholder="PN" value="<?php echo $datapncv['Fld_Part_Nbr'];?>">
										</div>
										<div class="form-group">
                                            <label>SN</label>
										<input class="form-control" name="Fld_Part_SN" placeholder="SN" value="<?php echo $data['Fld_Part_SN'];?>">
										</div>
										<div class="form-group">
                                            <label>QTY</label>
										<input class="form-control" name="Fld_Qty" placeholder="QTY" value="<?php echo $data['Fld_Qty'];?>">
										</div>
							

	
