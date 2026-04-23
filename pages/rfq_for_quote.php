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
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="RFQ_ID" value="<?php echo $data["Fld_RFQ_ID"];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo $data["date"];?>">
                                    </div>
								</div>
								<div class="col-lg-3">
								</div>
								<div class="col-lg-3">
										<input type="hidden" class="form-control" name="qtc" value="valid">
								</div>
						  
						