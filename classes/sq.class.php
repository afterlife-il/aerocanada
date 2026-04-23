<?php
class sq
{
	// Attributs
	//**tbl_RFQ_2**    ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time aci_contact

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
		
		$companyid = explode(",", $_POST['companyid']);
		$Fld_Supplier_ID=$companyid[0];
		
		$companyidtaginfo = explode(",", $_POST['companyidtaginfo']);
		$Fld_Tag_Info_ID=$companyidtaginfo[0]; 
		
		$companyidtreacability = explode(",", $_POST['companyidtreacability']);
		$Fld_Traceability_ID=$companyidtreacability[0]; 
		 
		 
		 if(empty($_POST['Fld_RFQ_ID'])) $Fld_RFQ_ID=date("Y-m-d-His");
		 else $Fld_RFQ_ID=$_POST['Fld_RFQ_ID'];
		 
		 $req="INSERT INTO `tbl_RFQ_2` (`ID`, `Fld_RFQ_ID`, `Fld_Supplier_ID`, `Fld_Qty`, `Fld_Condition_ID`, `Fld_Payment_Term_ID`, `Fld_Delivery`, `Fld_Price`, `Fld_Price_Max`, `Fld_Price_Min`, `Fld_Currency_ID`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Part_ID`, `Fld_Remark`, `Fld_IsBeen_Chosen`, `Fld_Current_Date`, `Fld_Qty_Received`, `Fld_Part_SN`, `Fld_Supplier_Contact_ID`, `Fld_Date_RecevdEnd_REP`, `lead_time`, `aci_contact`) VALUES ('', '".$Fld_RFQ_ID."', '".$Fld_Supplier_ID."', '".$_POST['Fld_Qty']."', '".$_POST['Fld_Condition_ID']."', '".$_POST['Fld_Payment_Term_ID']."', '".$_POST['Fld_Delivery']."', '".$_POST['Fld_Price']."', '".$_POST['Fld_Price_Max']."', '".$_POST['Fld_Price_Min']."', '".$_POST['Fld_Price_Currency_ID']."', '".$Fld_Traceability_ID."', '".$Fld_Tag_Info_ID."', '".$_POST['Fld_Tag_Date']."', '".$_POST['Fld_Release_ID']."', '".$_POST['Fld_Part_ID']."', '".$_POST['Fld_Remark']."', '', '".$_POST['Fld_Current_Date']."', '".$_POST['Fld_Qty_Received']."', '".$_POST['Fld_Part_SN']."', '".$_POST['id_company_contact']."', '".$_POST['Fld_Date_RecevdEnd_REP']."', '".$_POST['lead_time']."', '".$_POST['aci_contact']."');";
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