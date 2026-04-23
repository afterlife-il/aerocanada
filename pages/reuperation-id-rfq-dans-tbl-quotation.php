<?php
include_once "conf.php";
include_once "page_titles.php";
//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID  id_tbl_rfq1
											
											$sql="SELECT distinct(Fld_RFQ_ID) FROM tbl_RFQ_3 WHERE id_tbl_rfq1='0' limit 10000";
											// echo $sqlverifquote;
											$result = mysql2_query($sql);
											while($data = mysqli_fetch_array($result))
											{
											
											$sql2="SELECT * FROM tbl_RFQ_3 WHERE Fld_RFQ_ID='".$data['Fld_RFQ_ID']."'";
											$result2 = mysql2_query($sql2);
											$num_rows_rfq3 = mysqli_num_rows($result2);
											// $data2 = mysqli_fetch_array($result2);
											
											echo $data['Fld_RFQ_ID']." : ".$num_rows_rfq3."<br>";
											}
?>