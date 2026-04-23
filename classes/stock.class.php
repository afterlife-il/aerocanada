<?php
class stock
{
	/********************************************STOCK**********************************************************/
	
	public function add_stock()
	{
			
								if (!empty($_POST['companyid'])) 
										{
									$companyid = explode(",", $_POST['companyid']);
									$companyidrecup=$companyid[0]; 
										}
								
								if (!empty($_POST['Fld_Tag_Info_ID'])) 
										{
									$Fld_Tag_Info_ID = explode(",", $_POST['Fld_Tag_Info_ID']);
									$Fld_Tag_Info_ID=$Fld_Tag_Info_ID[0]; 
										}
								
								if (!empty($_POST['Fld_Supplier_ID'])) 
										{
									$Fld_Supplier_ID = explode(",", $_POST['Fld_Supplier_ID']);
									$Fld_Supplier_ID=$Fld_Supplier_ID[0]; 
										}
								
								if (!empty($_POST['Fld_Part_ID'])) 
										{
									$Fld_Part_ID = explode(",", $_POST['Fld_Part_ID']);
									$Fld_Part_ID=$Fld_Part_ID[1];
										}
										
								if (!empty($_POST['Fld_Traceability_ID'])) 
										{
									$Fld_Traceability_ID = explode(",", $_POST['Fld_Traceability_ID']);
									$Fld_Traceability_ID=$Fld_Traceability_ID[0];
										}
								if (!empty($_POST['Fld_Owner_ID'])) 
										{
									$Fld_Owner_ID = explode(",", $_POST['Fld_Owner_ID']);
									$Fld_Owner_ID=$Fld_Owner_ID[0];
										}

									
		 $requete = mysql2_query("INSERT INTO `tbl_Stock` (`Fld_Stock_ID`, `Fld_Part_ID`, `Fld_Part_SN`, `Fld_Supplier_ID`, `Fld_Entry_Date`, `Fld_Part_Price`, `Fld_Price_Currency_ID`, `Fld_BAX_PO_Nbr`, `Fld_Supplier_order_Date`, `Fld_Supplier_Payment_Date`, `Fld_Qty`, `Fld_Condition_ID`, `Fld_Release_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Traceability_ID`, `Fld_Warehouse_Location`, `Fld_Physical_Stock`, `Fld_Owner_ID`, `Fld_Stock_Location_ID`, `Fld_Status_ID`, `Fld_Status_Ind`, `Fld_Status_Date`, `Fld_Stock_Remark`, `Fld_Shelf_Life_Limit`, `Fld_Valeur_Comptable`, `Fld_Valeur_Comptable_currency_Id`, `Fld_Sales_Remark`, `Fld_External_Location`, `Fld_Sales_Remark_ID`, `Fld_Warehouse_Location_ID`, `Fld_OriginalUnit_Stock_ID`, `Fld_Min_Qty`, `Fld_Publish`, `status`) 
		 VALUES (NULL, '".$Fld_Part_ID."', '".$_POST['Fld_Part_SN']."', '".$Fld_Supplier_ID."', '".date('Y-m-d')."', '".$_POST['Fld_Part_Price']."', '".$_POST['Fld_Price_Currency_ID']."', '".$_POST['Fld_BAX_PO_Nbr']."', '', '', '".$_POST['Fld_Qty']."', '".$_POST['Fld_Condition_ID']."', '".$_POST['Fld_Release_ID']."', '".$Fld_Tag_Info_ID."', '".$_POST['Fld_Tag_Date']."', '".$Fld_Traceability_ID."', '', '', '".$Fld_Owner_ID."', '".$_POST['Fld_Stock_Location_ID']."', '".$_POST['Fld_Status_ID']."', '', '".$_POST['Fld_Status_Date']."', '".$_POST['Fld_Remark']."', '".$_POST['Fld_Shelf_Life_Limit']."', '".$_POST['Fld_Valeur_Comptable']."', '".$_POST['Fld_Valeur_Comptable_currency_Id']."', '".$_POST['Fld_Sales_Remark']."', '', '', '', '', '".$_POST['Fld_Min_Qty']."', '', '');");
	}
	/********************************************END STOCK******************************************************/
	/***********************************************************************************************************/
	
	/***********************************************************************************************************/
	/***********************************************************************************************************/
	
	/********************************************AIRCRAFT**********************************************************/
	
	// Attributs
	//************** tbl_Aircraft ************ Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
    public $Fld_AC_ID;
    public $Fld_AC_Model;
    public $Fld_AC_Series;
    public $Fld_AC_Manufacturer;
    public $Fld_AC_Engine_Model;
    public $Fld_AC_Engine_Series;
	
	public function add_aircraft()
	{
		 $requete = mysql2_query("INSERT INTO tbl_Aircraft (`Fld_AC_ID`,`Fld_AC_Model`, `Fld_AC_Series`, `Fld_AC_Manufacturer`, `Fld_AC_Engine_Model`, `Fld_AC_Engine_Series`)
		 VALUES ('','".$_POST['Fld_AC_Model']."','".$_POST['Fld_AC_Series']."','".$_POST['Fld_AC_Manufacturer']."','".$_POST['Fld_AC_Engine_Model']."','".$_POST['Fld_AC_Engine_Series']."');");
	}
	public function affichage_aircrafts()
	{
		$res=array();
		$req="SELECT * FROM tbl_Aircraft";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	/*
	
	public function verif_login($email,$pw)
	{
		 $resultlogin=array();
		 $requete=mysql2_query("SELECT * FROM tbl_Employee where email='".$email."' and pw='".$pw."'");
		 while($reponse=mysqli_fetch_array($requete)) 
		 {
		 $resultlogin[]=$reponse;
		 }
		 return $resultlogin;
		 
		 //$num_rows = mysqli_num_rows($requete);
		 //return $num_rows;

	}
	*/
	public function modif_aircraft()
	{
		 $sql="update tbl_Aircraft set Fld_AC_Model='".$_POST['Fld_AC_Model']."',Fld_AC_Series='".$_POST['Fld_AC_Series']."',Fld_AC_Manufacturer='".$_POST['Fld_AC_Manufacturer']."',Fld_AC_Engine_Model='".$_POST['Fld_AC_Engine_Model']."',Fld_AC_Engine_Series='".$_POST['Fld_AC_Engine_Series']."' where Fld_AC_ID='".$_POST['Fld_AC_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_aircraft($Fld_AC_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Aircraft where Fld_AC_ID='".$Fld_AC_ID."'"); 
	}
	
}
?>