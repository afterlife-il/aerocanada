<?php
class sq
{
	// Attributs
	//**tbl_RFQ_2**    ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time aci_contact

	private function post($key, $default = '')
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	private function firstCsvValue($value)
	{
		$parts = explode(",", (string)$value);
		return trim($parts[0]);
	}

	private function resolvePartId()
	{
		$partId = (int)$this->post('Fld_Part_ID', 0);
		if ($partId > 0) {
			return $partId;
		}

		$pn = trim((string)$this->post('pn_rfq', ''));
		if ($pn === '') {
			return 0;
		}

		$pnParts = explode(",", $pn);
		if (isset($pnParts[1]) && (int)trim($pnParts[1]) > 0) {
			return (int)trim($pnParts[1]);
		}
		$pn = trim($pnParts[0]);

		$pn = addslashes($pn);
		$req = mysql2_query("SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr='".$pn."' ORDER BY Fld_Part_ID DESC LIMIT 1");
		if ($req && mysqli_num_rows($req) > 0) {
			$row = mysqli_fetch_array($req);
			return (int)$row['Fld_Part_ID'];
		}

		return 0;
	}

	private function hasColumn($table, $column)
	{
		$req = mysql2_query("SHOW COLUMNS FROM `".$table."` LIKE '".addslashes($column)."'");
		return $req && mysqli_num_rows($req) > 0;
	}

	public function affichage_sq()
	{
		$res=array();
		$req="SELECT * FROM tbl_RFQ_2";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}

	public function add_sq()
	{	
		
		$Fld_Supplier_ID=$this->firstCsvValue($this->post('companyid'));
		
		$Fld_Tag_Info_ID=$this->firstCsvValue($this->post('companyidtaginfo'));
		
		$Fld_Traceability_ID=$this->firstCsvValue($this->post('companyidtreacability'));
		 
		 
		 if(empty($_POST['Fld_RFQ_ID'])) $Fld_RFQ_ID=date("Y-m-d-His");
		 else $Fld_RFQ_ID=$_POST['Fld_RFQ_ID'];

		 $Fld_Part_ID = $this->resolvePartId();
		 $Fld_Supplier_Contact_ID = $this->post('Fld_Supplier_Contact_ID', $this->post('id_company_contact'));
		 $id_tbl_rfq1 = (int)$this->post('id_tbl_rfq1', 0);
		 $hasRfqLineColumn = $this->hasColumn('tbl_RFQ_2', 'id_tbl_rfq1');
		 
		 $existingId = 0;
		 if ($Fld_RFQ_ID !== '' && $Fld_Supplier_ID !== '' && $Fld_Part_ID > 0) {
		 	$whereRfqLine = ($hasRfqLineColumn && $id_tbl_rfq1 > 0) ? " AND id_tbl_rfq1='".$id_tbl_rfq1."'" : "";
		 	$check = mysql2_query("SELECT ID FROM tbl_RFQ_2 WHERE Fld_RFQ_ID='".addslashes($Fld_RFQ_ID)."' AND Fld_Supplier_ID='".addslashes($Fld_Supplier_ID)."' AND Fld_Part_ID='".$Fld_Part_ID."'".$whereRfqLine." ORDER BY ID DESC LIMIT 1");
		 	if ($check && mysqli_num_rows($check) > 0) {
		 		$row = mysqli_fetch_array($check);
		 		$existingId = (int)$row['ID'];
		 	}
		 }

		 if ($existingId > 0) {
		 	$req="UPDATE `tbl_RFQ_2` SET Fld_Qty='".$this->post('Fld_Qty')."', Fld_Condition_ID='".$this->post('Fld_Condition_ID')."', Fld_Payment_Term_ID='".$this->post('Fld_Payment_Term_ID')."', Fld_Delivery='".$this->post('Fld_Delivery')."', Fld_Price='".$this->post('Fld_Price')."', Fld_Price_Max='".$this->post('Fld_Price_Max')."', Fld_Price_Min='".$this->post('Fld_Price_Min')."', Fld_Currency_ID='".$this->post('Fld_Price_Currency_ID')."', Fld_Traceability_ID='".$Fld_Traceability_ID."', Fld_Tag_Info_ID='".$Fld_Tag_Info_ID."', Fld_Tag_Date='".$this->post('Fld_Tag_Date')."', Fld_Release_ID='".$this->post('Fld_Release_ID')."', Fld_Remark='".$this->post('Fld_Remark')."', Fld_Current_Date='".$this->post('Fld_Current_Date')."', Fld_Qty_Received='".$this->post('Fld_Qty_Received')."', Fld_Part_SN='".$this->post('Fld_Part_SN')."', Fld_Supplier_Contact_ID='".$Fld_Supplier_Contact_ID."', Fld_Date_RecevdEnd_REP='".$this->post('Fld_Date_RecevdEnd_REP')."', lead_time='".$this->post('lead_time')."', aci_contact='".$this->post('aci_contact')."' WHERE ID='".$existingId."'";
		 	if ($hasRfqLineColumn && $id_tbl_rfq1 > 0) {
		 		$req = str_replace(" WHERE ID=", ", id_tbl_rfq1='".$id_tbl_rfq1."' WHERE ID=", $req);
		 	}
		 } else {
		 	$req="INSERT INTO `tbl_RFQ_2` (`ID`, `Fld_RFQ_ID`, `Fld_Supplier_ID`, `Fld_Qty`, `Fld_Condition_ID`, `Fld_Payment_Term_ID`, `Fld_Delivery`, `Fld_Price`, `Fld_Price_Max`, `Fld_Price_Min`, `Fld_Currency_ID`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Part_ID`, `Fld_Remark`, `Fld_IsBeen_Chosen`, `Fld_Current_Date`, `Fld_Qty_Received`, `Fld_Part_SN`, `Fld_Supplier_Contact_ID`, `Fld_Date_RecevdEnd_REP`, `lead_time`, `aci_contact`) VALUES ('', '".$Fld_RFQ_ID."', '".$Fld_Supplier_ID."', '".$this->post('Fld_Qty')."', '".$this->post('Fld_Condition_ID')."', '".$this->post('Fld_Payment_Term_ID')."', '".$this->post('Fld_Delivery')."', '".$this->post('Fld_Price')."', '".$this->post('Fld_Price_Max')."', '".$this->post('Fld_Price_Min')."', '".$this->post('Fld_Price_Currency_ID')."', '".$Fld_Traceability_ID."', '".$Fld_Tag_Info_ID."', '".$this->post('Fld_Tag_Date')."', '".$this->post('Fld_Release_ID')."', '".$Fld_Part_ID."', '".$this->post('Fld_Remark')."', '', '".$this->post('Fld_Current_Date')."', '".$this->post('Fld_Qty_Received')."', '".$this->post('Fld_Part_SN')."', '".$Fld_Supplier_Contact_ID."', '".$this->post('Fld_Date_RecevdEnd_REP')."', '".$this->post('lead_time')."', '".$this->post('aci_contact')."');";
		 	if ($hasRfqLineColumn && $id_tbl_rfq1 > 0) {
		 		$req="INSERT INTO `tbl_RFQ_2` (`ID`, `Fld_RFQ_ID`, `Fld_Supplier_ID`, `Fld_Qty`, `Fld_Condition_ID`, `Fld_Payment_Term_ID`, `Fld_Delivery`, `Fld_Price`, `Fld_Price_Max`, `Fld_Price_Min`, `Fld_Currency_ID`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Part_ID`, `Fld_Remark`, `Fld_IsBeen_Chosen`, `Fld_Current_Date`, `Fld_Qty_Received`, `Fld_Part_SN`, `Fld_Supplier_Contact_ID`, `Fld_Date_RecevdEnd_REP`, `lead_time`, `aci_contact`, `id_tbl_rfq1`) VALUES ('', '".$Fld_RFQ_ID."', '".$Fld_Supplier_ID."', '".$this->post('Fld_Qty')."', '".$this->post('Fld_Condition_ID')."', '".$this->post('Fld_Payment_Term_ID')."', '".$this->post('Fld_Delivery')."', '".$this->post('Fld_Price')."', '".$this->post('Fld_Price_Max')."', '".$this->post('Fld_Price_Min')."', '".$this->post('Fld_Price_Currency_ID')."', '".$Fld_Traceability_ID."', '".$Fld_Tag_Info_ID."', '".$this->post('Fld_Tag_Date')."', '".$this->post('Fld_Release_ID')."', '".$Fld_Part_ID."', '".$this->post('Fld_Remark')."', '', '".$this->post('Fld_Current_Date')."', '".$this->post('Fld_Qty_Received')."', '".$this->post('Fld_Part_SN')."', '".$Fld_Supplier_Contact_ID."', '".$this->post('Fld_Date_RecevdEnd_REP')."', '".$this->post('lead_time')."', '".$this->post('aci_contact')."', '".$id_tbl_rfq1."');";
		 	}
		 }
		 // echo $req;
		 $requete = mysql2_query($req);
	}
	public function modif_sq()
	{		
		 
		 $sql="update tbl_RFQ_2 set Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'";
		 //gestion Fld_Supplier_ID
		 if (!empty($_POST['companyid']))
		 {
		 $companyid = explode(",", $_POST['companyid']);
		 $Fld_Supplier_ID=$companyid[0];
		 $sql.=",Fld_Supplier_ID='".$Fld_Supplier_ID."'";
		 }
		 //Fin gestion Fld_Supplier_ID
		 
		 //gestion Fld_Part_ID
		 if (!empty($_POST['pnid']))
		 {
		 $pnid = explode(",", $_POST['pnid']);
		 $Fld_Part_ID=$pnid[1];
		 $sql.=",Fld_Part_ID='".$Fld_Part_ID."'";
		 }
		 //Fin gestion Fld_Part_ID
		 
		 //gestion Fld_Tag_Info_ID
		 if (!empty($_POST['companyidtaginfo']))
		 {
		 $companyidtaginfo = explode(",", $_POST['companyidtaginfo']);
		 $Fld_Tag_Info_ID=$companyidtaginfo[0]; 
		 $sql.=",Fld_Tag_Info_ID='".$Fld_Tag_Info_ID."'";
		 }
		 //Fin gestion Fld_Tag_Info_ID
		 
		 //gestion Fld_Traceability_ID
		 if (!empty($_POST['companyidtreacability']))
		 {
		 $companyidtreacability = explode(",", $_POST['companyidtreacability']);
		 $Fld_Traceability_ID=$companyidtreacability[0];  
		 $sql.=",Fld_Traceability_ID='".$Fld_Traceability_ID."'";
		 }
		 //Fin gestion Fld_Traceability_ID
		 
		 $sql.=",Fld_Supplier_Contact_ID='".$_POST['Fld_Supplier_Contact_ID']."',Fld_Part_SN='".$_POST['Fld_Part_SN']."',Fld_Qty='".$_POST['Fld_Qty']."',Fld_Condition_ID='".$_POST['Fld_Condition_ID']."',Fld_Release_ID='".$_POST['Fld_Release_ID']."',Fld_Tag_Date='".$_POST['Fld_Tag_Date']."',lead_time='".$_POST['lead_time']."',Fld_Delivery='".$_POST['Fld_Delivery']."',Fld_Price='".$_POST['Fld_Price']."',Fld_Currency_ID='".$_POST['Fld_Currency_ID']."',Fld_Payment_Term_ID='".$_POST['Fld_Payment_Term_ID']."',Fld_Remark='".$_POST['Fld_Remark']."',Fld_Qty_Received='".$_POST['Fld_Qty_Received']."',Fld_Date_RecevdEnd_REP='".$_POST['Fld_Date_RecevdEnd_REP']."'";
		 
		 $sql.=" where ID='".$_POST['id_table_rfq2']."'";
		 // echo $sql;
		 $query=mysql2_query($sql);
	}
	public function del_sq()
	{
		 $req="DELETE FROM tbl_RFQ_2 where ID='".$_GET['ID']."'";
		 // echo $req;
		 $result = mysql2_query($req); 
	}
}
?>
