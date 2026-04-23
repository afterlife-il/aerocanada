<?php
class rfq
{
	// Attributs
	////****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
    public $ID;
    public $Fld_RFQ_ID;
    public $Fld_Qty;
    public $Fld_Part_ID;
    public $Fld_Observation;
    public $Fld_Customer_ID;
    public $date;
    public $Fld_RFQ_Type_ID;
    public $Fld_Priority_ID;
    public $Employee_ID;
    public $id_company_contact;
    public $terms;
    public $Fld_Condition_ID;
    public $pn_rfq;
    public $description_rfq;
		
	public function add_rfq()
	{
								if (!empty($_POST['companyid'])) 
										{
									$companyid = explode(",", $_POST['companyid']);
									$companyidrecup=$companyid[0]; 
										}
										
								if (!empty($_POST['pnid'])) 
										{
									$pnid = explode(",", $_POST['pnid']);
									$pnidres=$pnid[1]; 
									$pn_rfq=$pnid[0]; 
									$description=$_POST['description'];
										}
								else {
									$pnidres=$_POST['Fld_Part_ID'];
									$description=$_POST['description_rfq'];
									$pn_rfq=$_POST['pn_rfq'];
									}
										
		$req="INSERT INTO `tbl_RFQ_1` (`ID`, `Fld_RFQ_ID`, `Fld_Qty`, `Fld_Part_ID`, `Fld_Observation`, `Fld_Customer_ID`, `date`, `Fld_RFQ_Type_ID`, `Fld_Priority_ID`, `Employee_ID`, `id_company_contact`, `Fld_Payment_Term_ID`, `Fld_Condition_ID`, `pn_rfq`, `description_rfq`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$_POST['Fld_Qty']."', '".$pnidres."', '".$_POST['Fld_Remark_rfq']."', '".$companyidrecup."', '".$_POST['RFQ_DATE']."', '".$_POST['Fld_RFQ_Type_ID']."', '".$_POST['Fld_Priority_ID']."', '".$_POST['Employee_ID']."', '".$_POST['id_company_contact']."', '".$_POST['Fld_Payment_Term_ID']."', '".$_POST['Fld_Condition_ID']."', '".$pn_rfq."', '".$description."');";
		// echo $req;
		$requete = mysql2_query($req);
		$lastidrfq=mysql2_insert_id();//reuperation de l'id qu'on vient de saisir
		//ajout de ce RFQ ID dans la table tbl_RFQ si elle n'existe pas deja
		
		$resultrfq1 = mysql2_query("SELECT * FROM tbl_RFQ where Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."'");
		$num_rows_rfq1 = mysqli_num_rows($resultrfq1);
		if($num_rows_rfq1==0){
		
		$req5="INSERT INTO `tbl_RFQ` (`ID`, `Fld_RFQ_ID`, `Fld_Date`, `Fld_Step_1`, `Fld_Step_2`, `Fld_Step_3`, `Fld_Priority_ID`, `Fld_Observation`, `Fld_RFQ_Type_ID`, `Fld_RFQ_ACI_Employee_Id`, `Fld_Customer_ID`, `Fld_Contact_ID`, `Fld_Customer_Detail_ID`, `Fld_PO`, `Fld_PO_Date`, `Fld_Requested_Date`, `Fld_Payment_Term_ID`, `Fld_Customer_Forwarder_ID`, `Fld_ShipTo_Company`, `Fld_Customer_ShippingDetail_ID`, `Fld_Customer_ShippingContact_ID`, `Fld_PO_IsOpen`, `RowIndex`, `Fld_Order_Rating`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$_POST['RFQ_DATE']."', 'TRUE', 'FALSE', 'FALSE', '".$_POST['Fld_Priority_ID']."', NULL, NULL, '".$_POST['id_utilisateur']."', '".$companyidrecup."', NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
		$requete5 = mysql2_query($req5);
							}
		//Fin ajout de ce RFQ ID dans la table tbl_RFQ si elle n'existe pas deja
		
					//*************************************************************************************
					//je verifie si il y a des elements a rentrer dans la dable quote
					
					if((!empty($_POST['actonrfq']))&&($_POST['actonrfq']=='addrfqft')&&((!empty($_POST['Fld_Release_ID']))||(!empty($_POST['Fld_Tag_Info_ID']))||(!empty($_POST['Fld_Tag_Date']))||(!empty($_POST['Fld_Traceability_ID']))||(!empty($_POST['lead_time']))||(!empty($_POST['Fld_Price']))||(!empty($_POST['Fld_Remark']))||(!empty($_POST['Fld_Part_SN']))||(!empty($_POST['moq']))))
					{
										
										$datequote=date("d-m-y");
					
											if (!empty($_POST['Fld_Traceability_ID']))
													{
												$companyid = explode(",", $_POST['Fld_Traceability_ID']);
												$companyidrecuptrac=$companyid[0]; 
													}
													
											if (!empty($_POST['Fld_Tag_Info_ID'])) 
													{
												$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID']);
												$companytaginforecup=$companyidtaginfo[0]; 
													}
											$sql="INSERT INTO `tbl_RFQ_3` (`ID`, `Fld_RFQ_ID`, `Fld_Quote_Date`, `Fld_Part_Id`, `Fld_Part_SN`, `Fld_Qty`, `Fld_Condition`, `Fld_Price`, `Fld_Price_Min`, `Fld_Price_Max`, `Fld_Currency_ID`, `Fld_Remark`, `Fld_Supply_Date`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Linked_ID`, `Fld_Exch_Core_Value`, `Fld_Exch_Core_Value_Currency_ID`, `Fld_Exch_Cond`, `Fld_IsBeen_Chosen`, `Fld_Send_Mail`, `Fld_Exch_Core_RCVD`, `moq`, `lead_time`, `Fld_Priority_ID`, `id_tbl_rfq1`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$datequote."', '".$_POST['part_id']."', '".$_POST['Fld_Part_SN']."', '".$_POST['Fld_Qty']."', '".$_POST['Fld_Condition_ID']."', '".$_POST['Fld_Price']."', '', '', '".$_POST['FldCurrencyID']."', '".$_POST['Fld_Remark']."', '', '".$companyidrecuptrac."', '".$companytaginforecup."', '".$_POST['Fld_Tag_Date']."', '".$_POST['Fld_Release_ID']."', '', '', '', '', '', '', '', '".$_POST['moq']."', '".$_POST['lead_time']."', '".$_POST['Fld_Priority_ID']."', '".$lastidrfq."');";
											// echo $sql;
											$requete = mysql2_query($sql);
					}
					//Fin je verifie si il y a des elements a rentrer dans la dable quote
					//*************************************************************************************
	}
	
	public function add_pn_rfq()
	{
								if (!empty($_POST['pnidnew'])) 
										{
									$pnidnew = explode(",", $_POST['pnidnew']);
									$pnidres=$pnidnew[1]; 
									$pn_rfq=$pnidnew[0]; 
									$description=$_POST['descriptionnew'];
										
										
		$req="INSERT INTO `tbl_RFQ_1` (`ID`, `Fld_RFQ_ID`, `Fld_Qty`, `Fld_Part_ID`, `Fld_Observation`, `Fld_Customer_ID`, `date`, `Fld_RFQ_Type_ID`, `Fld_Priority_ID`, `Employee_ID`, `id_company_contact`, `Fld_Payment_Term_ID`, `Fld_Condition_ID`, `pn_rfq`, `description_rfq`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$_POST['Fld_Qty']."', '".$pnidres."', '".$_POST['Fld_Remark']."', '".$_POST['Fld_Customer_ID']."', '".$_POST['RFQ_DATE']."', '".$_POST['Fld_RFQ_Type_ID']."', '".$_POST['Fld_Priority_ID']."', '".$_POST['Employee_ID']."', '".$_POST['id_company_contact']."', '".$_POST['Fld_Payment_Term_ID']."', '".$_POST['Fld_Condition_ID']."', '".$pn_rfq."', '".$description."');";
		// echo $req;
		$requete = mysql2_query($req);
		
										}
	}
	
	public function del_rfq($ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_RFQ_1 where ID='".$ID."'"); 
		 if (!empty($_GET['idrfq3']))
		 {
			 $result2 = mysql2_query("DELETE FROM tbl_RFQ_3 where ID='".$_GET['idrfq3']."'"); 
		 }
	}
	
	public function modif_rfq_quote()
	{	  
										
									if (!empty($_POST['companyidpopup'])) 
										{
									$companyid = explode(",", $_POST['companyidpopup']);
									$companyidrecup=$companyid[1]; 
										}
		////****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID  pn_rfq description_rfq
		
		 $sql="update tbl_RFQ_1 set Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."',Fld_Qty='".$_POST['Fld_Qty']."',Fld_Part_ID='".$_POST['part_id']."',Fld_Observation='".$_POST['Fld_Remark_rfq']."',Fld_Customer_ID='".$companyidrecup."',date='".$_POST['RFQ_DATE']."',Fld_RFQ_Type_ID='".$_POST['Fld_RFQ_Type_ID']."',Fld_Priority_ID='".$_POST['Fld_Priority_ID']."',Employee_ID='".$_POST['Employee_ID']."',id_company_contact='".$_POST['id_company_contact']."',Fld_Payment_Term_ID='".$_POST['Fld_Payment_Term_ID']."',Fld_Condition_ID='".$_POST['Fld_Condition_ID']."',pn_rfq='".$_POST['pn_rfq']."',description_rfq='".$_POST['description_rfq']."' where ID='".$_POST['idrfq1']."'";
		// echo $sql."<br>";
		$query=mysql2_query($sql);
		
		//gestion des informations de quotation
		if((!empty($_POST['quoteok']))&&($_POST['quoteok']=='ok'))
		{
								if (!empty($_POST['Fld_Traceability_ID'])) 
										{
									$companyid = explode(",", $_POST['Fld_Traceability_ID']);
									$companyidrecuptrac=$companyid[1]; 
										}
								
								if (!empty($_POST['Fld_Tag_Info_ID'])) 
										{
									$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID']);
									$companytaginforecup=$companyidtaginfo[1]; 
										}	
								
		 $sql2="update tbl_RFQ_3 set Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."',Fld_Part_Id='".$_POST['Fld_Part_ID']."',Fld_Part_SN='".$_POST['Fld_Part_SN']."',Fld_Qty='".$_POST['Fld_Qty']."',Fld_Condition='".$_POST['Fld_Condition_ID']."',Fld_Price='".$_POST['Fld_Price']."',Fld_Currency_ID='".$_POST['FldCurrencyID']."',Fld_Remark='".$_POST['Fld_Remark']."',Fld_Traceability_ID='".$companyidrecuptrac."',Fld_Tag_Info_ID='".$companytaginforecup."',Fld_Tag_Date='".$_POST['Fld_Tag_Date']."',Fld_Release_ID='".$_POST['Fld_Release_ID']."',moq='".$_POST['moq']."',lead_time='".$_POST['lead_time']."',Fld_Priority_ID='".$_POST['Fld_Priority_ID']."',rfqvalid='".$_POST['rfqvalid']."' where ID='".$_POST['idrfq3']."'";
		 // echo $sql2;
		$query2=mysql2_query($sql2);
		}
		else
		{
			//ajout d'une quotation
			$datequote=date("d-m-y");
			if (!empty($_POST['Fld_Traceability_ID'])) 
										{
									$companyid = explode(",", $_POST['Fld_Traceability_ID']);
									$companyidrecuptrac=$companyid[1]; 
										}
										
								if (!empty($_POST['Fld_Tag_Info_ID'])) 
										{
									$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID']);
									$companytaginforecup=$companyidtaginfo[1]; 
										}
		$sql="INSERT INTO `tbl_RFQ_3` (`ID`, `Fld_RFQ_ID`, `Fld_Quote_Date`, `Fld_Part_Id`, `Fld_Part_SN`, `Fld_Qty`, `Fld_Condition`, `Fld_Price`, `Fld_Price_Min`, `Fld_Price_Max`, `Fld_Currency_ID`, `Fld_Remark`, `Fld_Supply_Date`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Linked_ID`, `Fld_Exch_Core_Value`, `Fld_Exch_Core_Value_Currency_ID`, `Fld_Exch_Cond`, `Fld_IsBeen_Chosen`, `Fld_Send_Mail`, `Fld_Exch_Core_RCVD`, `moq`, `lead_time`, `Fld_Priority_ID`, `id_tbl_rfq1`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$datequote."', '".$_POST['part_id']."', '".$_POST['Fld_Part_SN']."', '".$_POST['Fld_Qty']."', '".$_POST['Fld_Condition_ID']."', '".$_POST['Fld_Price']."', '', '', '".$_POST['FldCurrencyID']."', '".$_POST['Fld_Remark']."', '', '".$companyidrecuptrac."', '".$companytaginforecup."', '".$_POST['Fld_Tag_Date']."', '".$_POST['Fld_Release_ID']."', '', '', '', '', '', '', '', '".$_POST['moq']."', '".$_POST['lead_time']."', '".$_POST['Fld_Priority_ID']."', '".$_POST['idrfq1']."');";
		// echo $sql;
		 $requete = mysql2_query($sql);
			//Fin ajout d'une quotation
		}
		//Fin gestion des informations de quotation
		  
	}
	
	public function valid_modif_rfq()
	{
		  for($i=1;$i<=$_POST['nbline'];$i++)  
		  {			  
										
									if(!empty($_POST['pnid'.$i])) 
									{
									$pnid = explode(",", $_POST['pnid'.$i]);
									$pnidres=$pnid[1]; 
									$pn_rfq=$pnid[0]; 
									$description=$_POST['description'];
									}
									else {
									$pnidres=$_POST['Fld_Part_ID'.$i];
									$description=$_POST['description'.$i];
									$pn_rfq=$_POST['pn_rfq'.$i];
									}
									
		 $sql="update tbl_RFQ_1 set Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."',Fld_Qty='".$_POST['Fld_Qty'.$i]."',Fld_Part_ID='".$pnidres."',Fld_Observation='".$_POST['Fld_Observation'.$i]."',Fld_Customer_ID='".$_POST['Fld_Customer_ID']."',date='".$_POST['RFQ_DATE']."',Fld_RFQ_Type_ID='".$_POST['Fld_RFQ_Type_ID'.$i]."',Fld_Priority_ID='".$_POST['Fld_Priority_ID']."',Employee_ID='".$_POST['Employee_ID']."',id_company_contact='".$_POST['id_company_contact']."',Fld_Payment_Term_ID='".$_POST['Fld_Payment_Term_ID']."',Fld_Condition_ID='".$_POST['Fld_Condition_ID'.$i]."',pn_rfq='".$pn_rfq."',description_rfq='".$_POST['description'.$i]."' where ID='".$_POST['ID'.$i]."'";
		// echo $sql."<br>";
		$query=mysql2_query($sql);
		  }
	}
	
	/******************************************************************************************************************
	*******************************************************************************************************************
	******************************************************************************************************************
	*******************************************RFQ TYPE***************************************************************/
	
	// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
											
	public function affichage_rfq_type()
	{
		$res=array();
		$req="SELECT * FROM tbl_RFQ_Type";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	public function add_rfq_type()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_RFQ_Type (`Fld_RFQ_Type_ID`,`Fld_RFQ_Type_Text`) VALUES ('','".$_POST['Fld_RFQ_Type_Text']."');");
	}
	public function modif_rfq_type()
	{
		 $sql="update tbl_RFQ_Type set Fld_RFQ_Type_Text='".$_GET['Fld_RFQ_Type_Text']."' where Fld_RFQ_Type_ID='".$_GET['Fld_RFQ_Type_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_rfq_type($Fld_RFQ_Type_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_RFQ_Type where Fld_RFQ_Type_ID='".$Fld_RFQ_Type_ID."'"); 
	}
	
	
	
	/******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************RFQ PRIORITY***********************************************************/
	
	// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
											
	public function affichage_rfq_priority()
	{
		$res=array();
		$req="SELECT * FROM tbl_Priority";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	public function add_rfq_priority()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_Priority (`Fld_Priority_ID`,`Fld_Priority_Text`) VALUES ('','".$_POST['Fld_Priority_Text']."');");
	}
	public function modif_rfq_priority()
	{
		 $sql="update tbl_Priority set Fld_Priority_Text='".$_GET['Fld_Priority_Text']."' where Fld_Priority_ID='".$_GET['Fld_Priority_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_rfq_priority($Fld_Priority_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Priority where Fld_Priority_ID='".$Fld_Priority_ID."'"); 
	}
	
	
	/******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************RFQ TERMS**************************************************************/
	
	// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
	public function affichage_rfq_terms()
	{
		$res=array();
		$req="SELECT * FROM tbl_Payment";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	public function add_rfq_terms()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_Payment (`Fld_Payment_Term_ID`,`Fld_Payment_Text`) VALUES ('','".$_POST['Fld_Payment_Text']."');");
	}
	public function modif_rfq_terms()
	{
		 $sql="update tbl_Payment set Fld_Payment_Text='".$_GET['Fld_Payment_Text']."' where Fld_Payment_Term_ID='".$_GET['Fld_Payment_Term_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_rfq_terms($Fld_Payment_Term_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Payment where Fld_Payment_Term_ID='".$Fld_Payment_Term_ID."'"); 
	}
	
		/******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************RFQ CONDITIONS**********************************************************/
	
	// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
											
	public function affichage_rfq_conditions()
	{
		$res=array();
		$req="SELECT * FROM tbl_Condition";
		$requete=mysql2_query($req);
		
		while($reponse=mysqli_fetch_array($requete)){
		$res[]=$reponse;
													}
	return $res;
	}
	public function add_rfq_conditions()
	{	
		 
		 $requete = mysql2_query("INSERT INTO tbl_Condition (`Fld_Condition_ID`,`Fld_Condition_Text`) VALUES ('','".$_POST['Fld_Condition_Text']."');");
	}
	public function modif_rfq_condition()
	{
		 $sql="update tbl_Condition set Fld_Condition_Text='".$_GET['Fld_Condition_Text']."' where Fld_Condition_ID='".$_GET['Fld_Condition_ID']."'";
		$query=mysql2_query($sql);
	}
	public function del_rfq_condition($Fld_Condition_ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_Condition where Fld_Condition_ID='".$Fld_Condition_ID."'"); 
	}
	
	/******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************************************************************************************
	*******************************************GESTION QUOTATIONS tbl_RFQ_3********************************************/
	
	//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID  id_tbl_rfq1 rfqvalid
	
	public function add_quote_RFQ3()
	{	
		 
		 $datequote=date("d-m-y");
		 
								if (!empty($_POST['Fld_Traceability_ID'])) 
										{
									$companyid = explode(",", $_POST['Fld_Traceability_ID']);
									$companyidrecuptrac=$companyid[1]; 
										}
										
								if (!empty($_POST['Fld_Tag_Info_ID'])) 
										{
									$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID']);
									$companytaginforecup=$companyidtaginfo[1]; 
										}
		$sql="INSERT INTO `tbl_RFQ_3` (`ID`, `Fld_RFQ_ID`, `Fld_Quote_Date`, `Fld_Part_Id`, `Fld_Part_SN`, `Fld_Qty`, `Fld_Condition`, `Fld_Price`, `Fld_Price_Min`, `Fld_Price_Max`, `Fld_Currency_ID`, `Fld_Remark`, `Fld_Supply_Date`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Linked_ID`, `Fld_Exch_Core_Value`, `Fld_Exch_Core_Value_Currency_ID`, `Fld_Exch_Cond`, `Fld_IsBeen_Chosen`, `Fld_Send_Mail`, `Fld_Exch_Core_RCVD`, `moq`, `lead_time`, `Fld_Priority_ID`, `id_tbl_rfq1`) VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$datequote."', '".$_POST['part_id']."', '".$_POST['Fld_Part_SN']."', '".$_POST['Fld_Qty']."', '".$_POST['Fld_Condition_ID']."', '".$_POST['Fld_Price']."', '', '', '".$_POST['FldCurrencyID']."', '".$_POST['Fld_Remark']."', '', '".$companyidrecuptrac."', '".$companytaginforecup."', '".$_POST['Fld_Tag_Date']."', '".$_POST['Fld_Release_ID']."', '', '', '', '', '', '', '', '".$_POST['moq']."', '".$_POST['lead_time']."', '".$_POST['Fld_Priority_ID']."', '".$_POST['id_tbl_rfq1']."');";
		// echo $sql;
		 $requete = mysql2_query($sql);
		 
	}
		public function add_quote_RFQ3_multi()
	{	
		 
		 $datequote=date("d-m-y");
		 for($i=1;$i<=$_POST['nbline'];$i++)
		 {
			 
				if($_POST['rfqvalid'.$i]=='ok')
					{
								if (!empty($_POST['Fld_Traceability_ID'.$i])) 
										{
									$companyid = explode(",", $_POST['Fld_Traceability_ID'.$i]);
									$companyidrecuptrac=$companyid[1]; 
										}
										
								if (!empty($_POST['Fld_Tag_Info_ID'.$i])) 
										{
									$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID'.$i]);
									$companytaginforecup=$companyidtaginfo[1]; 
										}
		$sql="INSERT INTO `tbl_RFQ_3` (`ID`, `Fld_RFQ_ID`, `Fld_Quote_Date`, `Fld_Part_Id`, `Fld_Part_SN`, `Fld_Qty`, `Fld_Condition`, `Fld_Price`, `Fld_Price_Min`, `Fld_Price_Max`, `Fld_Currency_ID`, `Fld_Remark`, `Fld_Supply_Date`, `Fld_Traceability_ID`, `Fld_Tag_Info_ID`, `Fld_Tag_Date`, `Fld_Release_ID`, `Fld_Linked_ID`, `Fld_Exch_Core_Value`, `Fld_Exch_Core_Value_Currency_ID`, `Fld_Exch_Cond`, `Fld_IsBeen_Chosen`, `Fld_Send_Mail`, `Fld_Exch_Core_RCVD`, `moq`, `lead_time`, `Fld_Priority_ID`, `id_tbl_rfq1`) 
		
		VALUES (NULL, '".$_POST['Fld_RFQ_ID']."', '".$datequote."', '".$_POST['Fld_Part_ID'.$i]."', '".$_POST['Fld_Part_SN'.$i]."', '".$_POST['Fld_Qty'.$i]."', '".$_POST['Fld_Condition_ID'.$i]."', '".$_POST['Fld_Price'.$i]."', '', '', '".$_POST['FldCurrencyID'.$i]."', '".$_POST['Fld_Remark'.$i]."', '', '".$companyidrecuptrac."', '".$companytaginforecup."', '".$_POST['Fld_Tag_Date'.$i]."', '".$_POST['Fld_Release_ID'.$i]."', '', '', '', '', '', '', '', '".$_POST['moq'.$i]."', '".$_POST['lead_time'.$i]."', '".$_POST['Fld_Priority_ID']."', '".$_POST['id_tbl_rfq1'.$i]."');";
		// echo $sql;
		 $requete = mysql2_query($sql);
					}
		 }
		 
	}
	public function modif_quote()
	{
								if (!empty($_POST['companyidtreacability'])) 
										{
									$companyid = explode(",", $_POST['companyidtreacability']);
									$companyidrecuptrac=$companyid[1]; 
										}
								else $companyidrecuptrac=$_POST['Fld_Traceability_ID'];		
										
								if (!empty($_POST['companyidtaginfo'])) 
										{
									$companyidtaginfo = explode(",", $_POST['companyidtaginfo']);
									$companytaginforecup=$companyidtaginfo[1]; 
										}
								else $companytaginforecup=$_POST['Fld_Tag_Info_ID'];
										
										if (!empty($_POST['pnid'])) 
										{
									$pnid = explode(",", $_POST['pnid']);
									$pnidres=$pnid[1]; 
										}
										else $pnidres=$_POST['Fld_Part_Id'];
									
		 $sql="update tbl_RFQ_3 set Fld_RFQ_ID='".$_POST['Fld_RFQ_ID']."',Fld_Quote_Date='".$_POST['Fld_Quote_Date']."',Fld_Part_Id='".$pnidres."',Fld_Part_SN='".$_POST['Fld_Part_SN']."',Fld_Qty='".$_POST['Fld_Qty']."',Fld_Condition='".$_POST['Fld_Condition']."',Fld_Price='".$_POST['Fld_Price']."',Fld_Currency_ID='".$_POST['Fld_Currency_ID']."',Fld_Remark='".$_POST['Fld_Remark']."',Fld_Traceability_ID='".$companyidrecuptrac."',Fld_Tag_Info_ID='".$companytaginforecup."',Fld_Tag_Date='".$_POST['Fld_Tag_Date']."',Fld_Release_ID='".$_POST['Fld_Release_ID']."',moq='".$_POST['moq']."',lead_time='".$_POST['lead_time']."',Fld_Priority_ID='".$_POST['Fld_Priority_ID']."' where ID='".$_POST['ID']."'";
		$query=mysql2_query($sql);
	}
	
	public function modif_multi_quote()
	{
		for($i=1;$i<=$_POST['nbline'];$i++)
		{
								if (!empty($_POST['Fld_Traceability_ID'.$i])) 
										{
									$companyid = explode(",", $_POST['Fld_Traceability_ID'.$i]);
									$companyidrecuptrac=$companyid[1]; 
										}
								
								if (!empty($_POST['Fld_Tag_Info_ID'.$i])) 
										{
									$companyidtaginfo = explode(",", $_POST['Fld_Tag_Info_ID'.$i]);
									$companytaginforecup=$companyidtaginfo[1]; 
										}	
								
		 $sql="update tbl_RFQ_3 set Fld_Part_Id='".$_POST['Fld_Part_ID'.$i]."',Fld_Part_SN='".$_POST['Fld_Part_SN'.$i]."',Fld_Qty='".$_POST['Fld_Qty'.$i]."',Fld_Condition='".$_POST['Fld_Condition_ID'.$i]."',Fld_Price='".$_POST['Fld_Price'.$i]."',Fld_Currency_ID='".$_POST['FldCurrencyID'.$i]."',Fld_Remark='".$_POST['Fld_Remark'.$i]."',Fld_Traceability_ID='".$companyidrecuptrac."',Fld_Tag_Info_ID='".$companytaginforecup."',Fld_Tag_Date='".$_POST['Fld_Tag_Date'.$i]."',Fld_Release_ID='".$_POST['Fld_Release_ID'.$i]."',moq='".$_POST['moq'.$i]."',lead_time='".$_POST['lead_time'.$i]."',Fld_Priority_ID='".$_POST['Fld_Priority_ID']."',rfqvalid='".$_POST['rfqvalid'.$i]."' where ID='".$_POST['RFQ3_ID'.$i]."'";
		 // echo $sql."<br>";
		 $companyidrecuptrac="";
		 $companytaginforecup="";
		$query=mysql2_query($sql);
		}	
	}
	
	public function del_quote($ID)
	{
		 $result = mysql2_query("DELETE FROM tbl_RFQ_3 where ID='".$ID."'"); 
	}
}

?>