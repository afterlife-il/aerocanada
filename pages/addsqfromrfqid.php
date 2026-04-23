								
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="Fld_RFQ_ID" value="<?php echo $_GET['Fld_RFQ_ID'];?>">
                                        </div>
								
								
								
										<div class="form-group">
                                            <label>PN</label><br>
											<input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $_GET['pn_rfq'];?>">
											<input type="hidden" name="Fld_Part_ID" id="Fld_Part_ID" value="<?php echo $_GET['id'];?>">
       
                                        </div>
										<?php
										include_once "conf.php";
include_once "page_titles.php";
										/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
										$sqlpns="SELECT Fld_Part_Desc FROM tbl_Parts where Fld_Part_ID='".$_GET['id']."'";
											
											// echo $sqlpns;
											$reqpns = mysql2_query($sqlpns);
											$datapns = mysqli_fetch_array($reqpns);
										?>
										<div class="form-group">
                                            <label>DESCRIPTION</label><br>
											<input class="form-control" name="Fld_Part_Desc" id="Fld_Part_Desc" value="<?php echo $datapns['Fld_Part_Desc'];?>">
       
                                        </div>
								