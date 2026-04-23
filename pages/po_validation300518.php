<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Aerocanada-industries.com</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

    <!-- DataTables Responsive CSS -->
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">

    <!-- Custom CSS -->
     <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

 
         <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
						
						
						
					<?php
					//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID pn_rfq description_rfq 
					
					// **tb_company** Fld_Company_ID Company_Old_Id  Fld_Company_Name  Fld_Company_Rating_ID  delete  companyrating  aci_contact  logocompany  status  internet  cage_code
					
					//**tbl_Company_Details**   id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark Fld_VAT_Nbr Fld_Date_Of_First_Contact Fld_Company_Address_Type  UTC_timezone   title_address
					
					$sqlrfq="SELECT tbl_RFQ_1.*,tb_company.* from tbl_RFQ_1,tb_company where tbl_RFQ_1.Fld_Customer_ID=tb_company.Fld_Company_ID AND tbl_RFQ_1.Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID Desc";
					
					$reqrfq = mysql2_query($sqlrfq);
					$datarfq = mysqli_fetch_array($reqrfq);
					
					$Fld_Company_ID=$datarfq['Fld_Company_ID'];
					$pn=$datarfq['pn_rfq'];
					$description=$datarfq['description_rfq'];
					$Fld_Priority_ID=$datarfq['Fld_Priority_ID'];
					$date_rfq=$datarfq['date'];
					$Fld_Customer_ID=$datarfq['Fld_Customer_ID'];
					$Fld_Payment_Term_ID=$datarfq['Fld_Payment_Term_ID'];
											
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type where Fld_RFQ_Type_ID='".$datarfq['Fld_RFQ_Type_ID']."'";
											$reqrfqt = mysql2_query($sqlrfqt);
											$datarfqt = mysqli_fetch_array($reqrfqt);
											$Fld_RFQ_Type_Text=$datarfqt['Fld_RFQ_Type_Text'];
											//End recuperation RFQ Type
				
					echo "<h1 class=\"page-header\">PO VALIDATION - <b>RFQID# ".$_GET['Fld_RFQ_ID']."</b></h1>";
					
											
											//recuperation des infos du contact dans la societe ********************
											
											// id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											$sqlls="SELECT * FROM tb_company_contact WHERE id_company_contact=".$datarfq['id_company_contact'];
											
											$reqls = mysql2_query($sqlls);
											
											$datals = mysqli_fetch_array($reqls);
											//Fin recuperation des infos du contact dans la societe ********************
											
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlptid="SELECT * FROM tbl_Payment where Fld_Payment_Term_ID=".$datarfq['Fld_Payment_Term_ID'];
											
											$reqptid = mysql2_query($sqlptid);
											$dataptid = mysqli_fetch_array($reqptid);
					                        //Fin recuperation des TERMS
											
											//recuperation Employee_Name
					                        $sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$datarfq['Employee_ID'];
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation Employee_Name
											
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT Fld_Priority_Text FROM tbl_Priority where Fld_Priority_ID=".$datarfq['Fld_Priority_ID'];
											
											$reqPriority = mysql2_query($sqlPriority);
											$dataPriority = mysqli_fetch_array($reqPriority);
					                        //Fin recuperation Priority
					?>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<div class="row">
				<div class="col-lg-1">
				<span style="color:#A7142A;font-weight: bold;">PURCHASE</span>
                    <div class="well">
                        <h4><b>ACI 770</b></h4>
                        <p><?php 
				echo $dataemp['Employee_Name'];?></p>
                    </div>

                    <div class="well">
                        <h4><b>PRIORITY</b><br><?php 
				echo strtoupper($dataPriority['Fld_Priority_Text']);?></h4>
                        <p></p>
                    </div>
					
					<div class="well">
                        <h4><b>RFQ TYPE</b></h4>
                        <p><?php 
				echo strtoupper($Fld_RFQ_Type_Text);?></p>
                    </div>
                </div>
                <!-- /.col-lg-1 -->
				<div class="col-lg-2">
				<span style="color:#A7142A;font-weight: bold;">CUSTOMER</span>
                    <div class="well">
                <p>
						<?php
					echo "<b>COMPANY NAME</b> : ".$datarfq['Fld_Company_Name'];
					echo "<br>";
					echo "<b>CONTACT NAME</b> : ".$datals['Fld_Contact_Name'];
					echo "<br>";
					echo "<b>E-MAIL</b> : ".$datals['Fld_Contact_Email'];
					echo "<br>";
					echo "<b>TERMS</b> : ".$dataptid['Fld_Payment_Text'];
					
				?>
				
				</p>
                    </div>
					
                </div>
                <!-- /.col-lg-2 -->
				<div class="col-lg-1">
				
				<span style="color:#A7142A;font-weight: bold;">SUPPLIER</span>
                    
                </div>
				<div class="col-lg-7">
				
				</div>
			</div>	
			<!-- /.row -->
			<!--Je verifie dans quelle phase de la PO on se trouve-->
			<?php 
			
			//je verifie si le RFQ ID se trouve dans la table tbl_RFQ 
			$result = mysql2_query("SELECT Fld_Phase FROM Tbl_Customer_PO_Follow_UP where Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."'");
			$num_rows = mysqli_num_rows($result);
			if(0<$num_rows){
			$dataphaserfqid = mysqli_fetch_array($result);	
				$Fld_Phase=$dataphaserfqid['Fld_Phase'];
			}
			else $Fld_Phase="0";
				?>
			<!--Fin Je verifie dans quelle phase de la PO on se trouve-->
			<div class="row">
					<div class="col-lg-2">
					<span style="font-size:45px;<?php if($Fld_Phase=='0') echo "color:#A7142A;";else echo "color:#333;";?>">1</span>CUSTOMER<br> PO ACKNOWLEDGMENT
					</div>
					
					<div class="col-lg-2" style="color: #e3e3e3;">
					<span style="font-size:50px;<?php if($Fld_Phase=='12') echo "color:#A7142A;";else if($Fld_Phase>0) echo "color:#333;";?>">2</span><span style="<?php if($Fld_Phase=='12') echo "color:#A7142A;";else if($Fld_Phase>0) echo "color:#333;";?>">SUPPLIER<BR> PURCHASE ORDER</span>
					</div>
					
					<div class="col-lg-2" style="color: #e3e3e3;">
					<span style="font-size:50px;">3</span>STEP 3
					</div>
					
					<div class="col-lg-2" style="color: #e3e3e3;">
					<span style="font-size:50px;">4</span>STEP 4
					</div>
					
					<div class="col-lg-2" style="color: #e3e3e3;">
					<span style="font-size:50px;">5</span>STEP 5
					</div>
					
			</div>	
			<!-- /.row -->
			
            <div class="row" id='blocpoacknowl'>
                <div class="col-lg-12">
                    <div class="panel panel-default" style="background-color: #ddd;">
                        <div class="panel-heading" style="background-color:#A7142A">
                           PO ACKNOWLEDGMENT
                        </div>
						
						<!--<form id="formajoutpart" role="form" method="post" action="pdf_generate.php" class="needs-validation" novalidate>-->
						<form id="formajoutpart" role="form" method="post" action="pdf_generate.php">
						<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $_GET['Fld_RFQ_ID'];?>">		
						<input type="hidden" name="Fld_Priority_ID" value="<?php echo $Fld_Priority_ID;?>">		
						<input type="hidden" name="act" value="po_acknowledgment">		
						<input type="hidden" name="date_rfq" value="<?php echo $date_rfq;?>">		
						<input type="hidden" name="Fld_Customer_ID" value="<?php echo $Fld_Customer_ID;?>">		
						<input type="hidden" name="Fld_Payment_Term_ID" value="<?php echo $Fld_Payment_Term_ID;?>">	
					
									<?php
								//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID
								$sqlrfq3="SELECT * from tbl_RFQ_3 where Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID DESC";
								//echo $sqlrfq3;
								
								$reqrfq3 = mysql2_query($sqlrfq3);
								$datarfq3 = mysqli_fetch_array($reqrfq3);
								?>
						<input type="hidden" name="Fld_RFQ3_ID" value="<?php echo $datarfq3['ID'];?>">	
						<input type="hidden" name="Fld_Part_Id" value="<?php echo $datarfq3['Fld_Part_Id'];?>">	
						<div class="panel-body" <?php if($Fld_Phase=='12') echo "style='display:none'";?>>
							<div class="row">
							 <div class="form-row">
								<div class="col-lg-3">
								QUOTE VALIDATION
								
								<div class="form-group">
                                            <label>CUSTOMER PO #</label>
								<input class="form-control" name="customer_po_number" placeholder="PO #" required>
								</div>
									<table style="width: 100%;">
									<?php
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID='".$datarfq3['Fld_Condition']."'";
											
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT Fld_Currency_Text FROM tbl_Currency where Fld_Currency_ID=".$datarfq3["Fld_Currency_ID"];
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
					                        //End recuperation of the currency
											
											//recuperation Payment_Term
											$sqlRID="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$datarfq3["Fld_Release_ID"];
											
											$reqRID = mysql2_query($sqlRID);
											$dataRID = mysqli_fetch_array($reqRID);
											//Fin ecuperation Payment_Term
											
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq3['Fld_Tag_Info_ID'];
											
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq3['Fld_Traceability_ID'];
											
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											

                                            echo "
											<tr><td><b>DATE</b></td><td><input class=\"form-control\" name=\"po_date\" value='".date('d-m-Y')."'></td></tr>
											<tr><td><b>PN</b></td><td>".$pn."</td></tr>
											<tr><td><b>DESCRIPTION</b></td><td>".$description."</td></tr>
											<tr><td><b>DATE</b></td><td>".$datarfq3['Fld_Quote_Date']."</td></tr>
											<tr><td><b>QTY</b></td><td>".$datarfq3['Fld_Qty']."</td></tr>
											<tr><td><b>CONDITION</b></td><td>".$datact['Fld_Condition_Text']."</td></tr>
											
											<tr><td><b>LEAD TIME</b></td><td>".$datarfq3['lead_time']."</td></tr>
											<tr><td><b>RELEASE</b></td><td>".$dataRID['Fld_Release_Text']."</td></tr>
											<tr><td><b>SN</b></td><td><input class=\"form-control\" name=\"Fld_Part_SN\" value='".$datarfq3['Fld_Part_SN']."'></td></tr>
											<tr><td><b>TAG INFO</b></td><td>".$datatiid['Fld_Company_Name']."</td></tr>
											<tr><td><b>TAG DATE</b></td><td>".$datarfq3['Fld_Tag_Date']."</td></tr>
											<tr><td><b>TRACED TO</b></td><td>".$datatrac['Fld_Company_Name']."</td></tr>
											<tr><td><b>REMARK</b></td><td><textarea class=\"form-control\" name=\"Fld_Remark\">".$datarfq3['Fld_Remark']."</textarea></td></tr>";
											
											//recuperation du core value dans la table part ********************
											//*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter   cage_code    essentiality_category_id    nha   moq   oem_lead_time  core_value  id_currency_core_value
											$sqlpncv="SELECT Fld_Condition_Text FROM  tbl_Parts WHERE Fld_Part_ID=".$datarfq3['Fld_Part_Id'];
											
											$reqpncv = mysql2_query($sqlpncv);
											$datapncv = mysqli_fetch_array($reqpncv);
											//Fin recuperation du core value dans la table part ********************
											
											echo "<tr><td><b>CORE VALUE</b></td><td><input class=\"form-control\" name=\"core_value\" value='".$datapncv['core_value']."'></td></tr>
											<tr><td><b>CORE VALUE CURRENCY</b></td><td>
											
											<select class=\"form-control\" name=\"id_currency_core_value\" id=\"id_currency_core_value\" >";
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcidcv = mysql2_query($sqlcid);
											while($datacidcv = mysqli_fetch_array($reqcidcv)){
												echo "<option value='".$datacidcv['Fld_Currency_ID']."'";
												if ($datapncv["id_currency_core_value"]==$datacidcv['Fld_Currency_ID']) echo "selected";
												echo ">".$datacidcv['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
										
                                                
                                            echo "</select>
											
											</td></tr>
											
											<tr><td><b>PRICE</b></td><td><input class=\"form-control\" name=\"Fld_Price\" value='".$datarfq3['Fld_Price']."' required></td></tr>
											<tr><td><b>$/€</b></td><td>
											   <select class=\"form-control\" name=\"FldCurrencyID\" id=\"FldCurrencyID\" >";
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid2="SELECT * FROM tbl_Currency";
											
											$reqcid2 = mysql2_query($sqlcid2);
											while($datacid2 = mysqli_fetch_array($reqcid2)){
												echo "<option value='".$datacid2['Fld_Currency_ID']."'";
												if ($datarfq3["Fld_Currency_ID"]==$datacid2['Fld_Currency_ID']) echo "selected";
												echo ">".$datacid2['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
										
                                                
                                            echo "</select>
											
											</td></tr>";
											?>
											</table>
											<br>
											<!--PO match Quote-->
									 <div class="form-group">
										<div class="form-check">
										<input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
										<label class="form-check-label" for="invalidCheck">
											PO match Quote
										</label>
										<div class="invalid-feedback">
											You must check before submitting.
										</div>
										</div>
									</div>
									<!--end PO match Quote-->
									
								</div>
								<div class="col-lg-3">
								ACCOUNTING ADDRESS
								
									<div class="form-group">
                                            <label>ADDRESS</label>
											<select class="form-control" name="id_company_address_accounting" id="idcompanyaddressaccounting" onchange="javascript:affaddselect();">
											<option>Choose address</option>
											<?php
								/*
								Table tbl_Company_Details
								*************************************
								id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
								*/
								// getting total number records without any search
								$sqladdc="SELECT * FROM tbl_Company_Details where Fld_Company_ID='".$Fld_Company_ID."'";	
								
								$reqaddc = mysql2_query($sqladdc);
								while($dataaddc = mysqli_fetch_array($reqaddc))
								{
											//recuperation address type
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqltypec="SELECT Fld_Division_Text FROM tbl_Division where Fld_Division_ID='".$dataaddc['Fld_Company_Address_Type']."'";
											
					                        $reqtypec = mysql2_query($sqltypec);
					                        $datatypec= mysqli_fetch_array($reqtypec);
											//End recuperation address type
									echo "<option value='".$dataaddc['id_tbl_company_Details']."'>".$datatypec['Fld_Division_Text']."</option>";
								}
								?>
                                            </select>
                                        </div>
								<div id="blocaccountadd" width="100%"><div id="divaccountadd" align="center" width="100%"></div></div>   		
								   		
								</div>
								<div class="col-lg-3">
								DELIVERY ADDRESS
											<div class="form-group">
                                            <label>ADDRESS</label>
											<select class="form-control" name="id_company_address_delivery" id="idcompanyaddressdelivery" onchange="javascript:functiondelivadd();">
											<option>Choose address</option>
											<?php
											/*
											Table tbl_Company_Details
											*************************************
											id_tbl_company_Details Fld_Linked_ID Fld_Company_ID Company_Old_Id Fld_Company_Type_ID Fld_Company_Country Fld_Company_City Fld_Company_State Fld_Company_Street Fld_Company_ZipCode Fld_Company_Fax Fld_Company_Phone Fld_Company_Email Fld_Company_Score Fld_Company_BAX_Contact Fld_Remark   Fld_VAT_Nbr   Fld_Date_Of_First_Contact   Fld_Company_Address_Type   UTC_timezone  title_address
											*/
											// getting total number records without any search
											$sqladdc="SELECT * FROM tbl_Company_Details where Fld_Company_ID='".$Fld_Company_ID."'";	
											
											$reqaddc = mysql2_query($sqladdc);
											while($dataaddc = mysqli_fetch_array($reqaddc))
											{
														//recuperation address type
														//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
														$sqltypec="SELECT Fld_Division_Text FROM tbl_Division where Fld_Division_ID='".$dataaddc['Fld_Company_Address_Type']."'";
														$reqtypec = mysql2_query($sqltypec);
														$datatypec= mysqli_fetch_array($reqtypec);
														//End recuperation address type
												echo "<option value='".$dataaddc['id_tbl_company_Details']."'>".$datatypec['Fld_Division_Text']."</option>";
											}
								?>
                                            </select>
                                        </div>
										<div id="blocdelivadd" width="100%"><div id="divdelivadd" align="center" width="100%"></div></div>
								</div>
								<div class="col-lg-3">
								DELIVERY METHOD
									<div class="form-group">
                                            <label>Forwarder</label>
								<?php
								//**tbl_Forwarder**   Fld_Linked_ID  Company_Old_Id  Fld_Company_ID  Fld_Shipper_ID  Fld_Account_Nbr Fld_Remark Fld_Shipper_Contact_Name_Forw  Fld_Shipper_Contact_Phone_Forw
								//*** tbl_Shipper ***  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
								
								$sqlforwarder="SELECT tbl_Forwarder.*,tbl_Shipper.* FROM tbl_Forwarder,tbl_Shipper where tbl_Forwarder.Fld_Shipper_ID=tbl_Shipper.Fld_Shipper_ID AND tbl_Forwarder.Fld_Company_ID='".$Fld_Company_ID."'";
								// echo $sqlforwarder;
								$reqforwarder = mysql2_query($sqlforwarder);
								?>
											<select class="form-control" name="id_Forwarder" id="idForwarder" onchange="javascript:funcselectforwarder();">
											<option>Choose Forwarder</option>
								<?php
								
								while($dataforwarder= mysqli_fetch_array($reqforwarder))
								{
														//End recuperation address type
												echo "<option value='".$dataforwarder['Fld_Linked_ID']."'>".$dataforwarder['Fld_Shipper_Text']."</option>";
								}
								?>
											</select>
									</div>
									<div id="blocselectforwarder" width="100%"><div id="divselectforwarder" align="center" width="100%"></div></div>
									
									
									
								</div>
								</div>
							</div>
									
						
							
							<?php $today = date("Y-m-d"); ?>
							<?php $yeartoday = date("Y"); ?>
							<input type="hidden" name="Fld_Part_LP_Date" value="<?php echo $yeartoday;?>">
							<input type="hidden" name="Fld_Add_PN_Date" value="<?php echo $today;?>">
							<input type="hidden" name="aci_contact_entry" value="<?php echo $_SESSION['id_utilisateur'];?>">
								</form>		
								

									<div class="row">
										<div class="col-lg-12">
										<button class="btn btn-primary" type="submit">Submit form</button>
										</div>
									</div>
                        </div>
                        <!-- /.panel-body -->
									
						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--*************************************************************************************************************-->
			<!--****************************************PURCHASING***********************************************************-->
			<!--*************************************************************************************************************-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default" style="background-color: #ddd;">
                        <div class="panel-heading" style="background-color:#A7142A">
                          PURCHASE ORDER
                        </div>
						
						<form id="formpurchasing" role="form" method="post" action="valid_purchasing.php">
									
									
						<div class="panel-body" <?php if($Fld_Phase=='0') echo "style='display:none'";?>>
							<div class="row">
							 <div class="form-row">
								
								<div class="col-lg-1">
									<div class="form-group">
                                            <label>ACI PO#</label>
									<input class="form-control" name="Fld_ACI_PO_NBR" placeholder="PO #" required>
									</div>
								</div>
							 
							 </div>
							</div>
							<!--afficher quotatios supplier pour rfqid-->
							<!--************************************************-->
						<!--Verif si il y a des Supplier quote pour ce pn-->
						<?php
							
							$sqlrfq2="SELECT * from tbl_RFQ_2 where Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID DESC";
							//echo $sqlrfq2;
							$reqrfq2 = mysql2_query($sqlrfq2);
							$numrows_SQ = mysqli_num_rows($reqrfq2);
						?>
						<!--Fin Verif si il y a des supliers quote pour ce pn-->
						<!--************************************************-->
							 <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>RFQ ID</th>
                                            <th>SUPPLIER NAME</th>
											<th>CONTACT NAME</th>
											<th>QTY</th>
											<th>CONDITION</th>
                                            <th>PRICE</th>
                                            <th>$/€</th>
                                            <th>PAYMENT TERMS</th>
                                            <th>LEAD TIME</th>
                                            <th>RELEASE</th>
                                            <th>SN</th>
                                            <th>TAG INFO</th>
                                            <th>TAG DATE</th>
                                            <th>Traced To</th>
                                            <th>SALES REMARKS</th>
                                            <th>ACI770</th>
										
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
											
											//recuperation du nom du contact dans la societe ********************
											$sqlls="SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact=".$datarfq2['Fld_Supplier_Contact_ID'];
											$reqls = mysql2_query($sqlls);
											$datals = mysqli_fetch_array($reqls);
											//Fin recuperation du nom du contact dans la societe ********************
											
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID=".$datarfq2['Fld_Condition_ID'];
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Tag_Info_ID'];
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Traceability_ID'];
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT Fld_Currency_Text FROM tbl_Currency where Fld_Currency_ID=".$datarfq2["Fld_Currency_ID"];
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
					                        //End recuperation of the currency
											
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
											$sqlpt="SELECT Fld_Payment_Text FROM tbl_Payment where Fld_Payment_Term_ID=".$datarfq2["Fld_Payment_Term_ID"];
											
											$reqpt = mysql2_query($sqlpt);
											$datapt = mysqli_fetch_array($reqpt);
											//Fin ecuperation Payment_Term
											
											//recuperation Payment_Term
											$sqlRID="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$datarfq2["Fld_Release_ID"];
											
											$reqRID = mysql2_query($sqlRID);
											$dataRID = mysqli_fetch_array($reqRID);
											//Fin ecuperation Payment_Term
											
												
											//recuperation Employee_Name
											$sqlrempid="SELECT Employee_ID FROM tbl_RFQ_1 where Fld_RFQ_ID='".$datarfq2['Fld_RFQ_ID']."'";
											$reqrempid = mysql2_query($sqlrempid);
											$datarempid = mysqli_fetch_array($reqrempid);
					                        $sqlemp="SELECT Employee_Name FROM tbl_Employee where tbl_Employee.Employee_ID=".$datarempid['Employee_ID'];
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation Employee_Name
											
                                            echo "<tr>
											<td><input type=\"radio\" name=\"suppliers_choice_id\" value='".$datarfq2['ID']."' onchange=\"quote_the_customer_sq('".$datarfq2['ID']."')\"></td>
											<td><button type=\"button\" class=\"btn btn-primary\" data-toggle=\"modal\" data-target=\"#myModal".$datarfq2['ID']."\">".$datarfq2['Fld_RFQ_ID']."</button></td>
											<td>".$datarn['Fld_Company_Name']."</td>
											<td>".$datals['Fld_Contact_Name']."</td>
											<td>".$datarfq2['Fld_Qty']."</td>
											<td>".$datact['Fld_Condition_Text']."</td>
											<td>".$datarfq2['Fld_Price']."</td>
											<td>".$datacid['Fld_Currency_Text']."</td>
											<td>".$datapt['Fld_Payment_Text']."</td>
											<td>".$datarfq2['lead_time']."</td>
											<td>".$dataRID['Fld_Release_Text']."</td>
											
											<td>".$datarfq2['Fld_Part_SN']."</td>
											<td>".$datatiid['Fld_Company_Name']."</td>
											<td>".$datarfq2['Fld_Tag_Date']."</td>
											<td>".$datatrac['Fld_Company_Name']."</td>
											<td>".$datarfq2['Fld_Remark']."</td>
											<td>".$dataemp['Employee_Name']."</td></tr>";
					}
?>					
                                    </tbody>
                                </table>
                            </div>
							<!--END afficher quotatios supplier pour rfqid-->
							
							<div class="row">
							 <div class="form-row">
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>PN</label>
										<input class="form-control" name="Fld_ACI_PO_NBR" placeholder="PN" required>
										</div>
										<div class="form-group">
                                            <label>SN</label>
										<input class="form-control" name="Fld_ACI_PO_NBR" placeholder="PN" required>
										</div>
										<div class="form-group">
                                            <label>QTY</label>
										<input class="form-control" name="Fld_ACI_PO_NBR" placeholder="PN" required>
										</div>
								</div>
							 </div>
							</div>
							<div class="row">
										<div class="col-lg-12">
										<button class="btn btn-primary" type="submit">Submit form</button>
										</div>
							</div>
							 
						</div>
                        <!-- /.panel-body -->
									
						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->				 
			<!--*************************************************************************************************************-->
			<!--****************************************END PURCHASING*******************************************************-->
			<!--*************************************************************************************************************-->
			
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
	<!--*************************************************************************************************************-->
	<!--POPUP MODIF SUPPLIERS-->
	<?php 
	//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  
							//Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
	$sqlrfq2="SELECT * from tbl_RFQ_2 where Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID DESC";
							//echo $sqlrfq2;
							$reqrfq2 = mysql2_query($sqlrfq2);
	while($datarfq2 = mysqli_fetch_array($reqrfq2))
					{
											//recuperation du nom de compagnie ********************
											$sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Supplier_ID'];
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation du nom de compagnie ********************
											
											//recuperation du nom du contact dans la societe ********************
											$sqlls="SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact=".$datarfq2['Fld_Supplier_Contact_ID'];
											$reqls = mysql2_query($sqlls);
											$datals = mysqli_fetch_array($reqls);
											//Fin recuperation du nom du contact dans la societe ********************
											
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID=".$datarfq2['Fld_Condition_ID'];
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Tag_Info_ID'];
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$datarfq2['Fld_Traceability_ID'];
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT Fld_Currency_Text FROM tbl_Currency where Fld_Currency_ID=".$datarfq2["Fld_Currency_ID"];
											
											$reqcid = mysql2_query($sqlcid);
											$datacid = mysqli_fetch_array($reqcid);
					                        //End recuperation of the currency
											
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
											$sqlpt="SELECT Fld_Payment_Text FROM tbl_Payment where Fld_Payment_Term_ID=".$datarfq2["Fld_Payment_Term_ID"];
											
											$reqpt = mysql2_query($sqlpt);
											$datapt = mysqli_fetch_array($reqpt);
											//Fin ecuperation Payment_Term
											
											//recuperation Payment_Term
											$sqlRID="SELECT Fld_Release_Text FROM tbl_Release where Fld_Release_ID=".$datarfq2["Fld_Release_ID"];
											
											$reqRID = mysql2_query($sqlRID);
											$dataRID = mysqli_fetch_array($reqRID);
											//Fin ecuperation Payment_Term
											
												
											//recuperation Employee_Name
											$sqlrempid="SELECT Employee_ID FROM tbl_RFQ_1 where Fld_RFQ_ID='".$datarfq2['Fld_RFQ_ID']."'";
											$reqrempid = mysql2_query($sqlrempid);
											$datarempid = mysqli_fetch_array($reqrempid);
					                        $sqlemp="SELECT Employee_Name FROM tbl_Employee where tbl_Employee.Employee_ID=".$datarempid['Employee_ID'];
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation Employee_Name
	?>
	<div class="modal fade" id="myModal<?php echo $datarfq2['ID'];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="loginForm" method="post" action="testroy.php">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">SUPPLIER NAME:</label>
            <input type="text" class="form-control" id="Fld_Company_Name" value="<?php echo $datarn['Fld_Company_Name'];?>">
          </div>
		  <div class="form-group">
            <label for="recipient-name" class="col-form-label">CONTACT NAME:</label>
            <input type="text" class="form-control" id="Fld_Contact_Name" value="<?php echo $datals['Fld_Contact_Name'];?>">
          </div>
		  <div class="form-group">
            <label for="qty" class="col-form-label">Qty2:</label>
            <input type="text" class="form-control" id="qty" name="qty" value="<?php echo $datarfq2['Fld_Qty'];?>">
          </div>
		  
		  <div class="form-group">
            <label for="Fld_Condition_ID" class="col-form-label">CONDITION:</label>
            <select class="form-control" name="Fld_Condition_ID" id="Fld_Condition_ID">
											<option></option>
			<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
											if($datac['Fld_Condition_ID']==$datarfq2['Fld_Condition_ID'])echo "selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
			</select>								
          </div>
		  <div class="form-group">
            <label for="Fld_Price" class="col-form-label">PRICE:</label>
            <input type="text" class="form-control" id="Fld_Price" name="Fld_Price" value="<?php echo $datarfq2['Fld_Price'];?>">
          </div>
		  
          <div class="form-group">
            <label for="Fld_Currency_ID" class="col-form-label">$/€	:</label>
             <select class="form-control" name="Fld_Currency_ID" id="Fld_Currency_ID" >
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcidcv = mysql2_query($sqlcid);
											while($datacidcv = mysqli_fetch_array($reqcidcv)){
												echo "<option value='".$datacidcv['Fld_Currency_ID']."'";
												if ($datarfq2["Fld_Currency_ID"]==$datacidcv['Fld_Currency_ID']) echo "selected";
												echo ">".$datacidcv['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
			</select>								
          </div>
		  
		   <div class="form-group">
            <label for="Fld_Payment_Term_ID" class="col-form-label">PAYMENT TERMS:</label>
             <select class="form-control" name="Fld_Payment_Term_ID" id="Fld_Payment_Term_ID" >
												<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'";
												if($dataptid['Fld_Payment_Term_ID']==$datarfq2['Fld_Payment_Term_ID']) echo "selected";
												echo ">".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
			</select>								
          </div>
		  <div class="form-group">
            <label for="lead_time" class="col-form-label">LEAD TIME:</label>
            <input type="text" class="form-control" id="lead_time" name="lead_time" value="<?php echo $datarfq2['lead_time'];?>">
          </div>
		  <div class="form-group">
            <label for="Fld_Payment_Term_ID" class="col-form-label">RELEASE:</label>
             <select class="form-control" name="Fld_Payment_Term_ID" id="Fld_Payment_Term_ID" >
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'>".$datar['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
			</select>								
          </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary odom-submit">Send message</button>
      </div>
	  </form>
    </div>
  </div>
</div>
					<?php }?>
	<!--END POPUP MODIF SUPPLIERS-->
	<!--*************************************************************************************************************-->
	<!--*************************************************************************************************************-->
	<!--*************************************************************************************************************-->
	
	
    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
		
    });
		
		
<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Affichage adresse selectionne ACCOUNTING ADDRESS-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function affaddselect(id)
{
var blocaccountadd=document.getElementById('blocaccountadd');
//if(blocquotecustomer.style.display=='inline') blocquotecustomer.style.display='none';
//else{
blocaccountadd.style.display='inline';

//document.getElementById("divquotecustomer").innerHTML='<div id="divquotecustomer" align="center"><img src="../images_design/Spin.gif" border="0"></div>';

var idcompanyaddressaccounting = document.getElementById('idcompanyaddressaccounting'),
    myElementValueaddaccount = idcompanyaddressaccounting.value;

var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "affaddselect.php?id="+myElementValueaddaccount, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_affaddselect(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function up_affaddselect(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divaccountadd').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divaccountadd').innerHTML+=resp2;
    document.getElementById('divaccountadd').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Affichage adresse selectionne ACCOUNTING ADDRESS-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Affichage adresse selectionne DELIVERY ADDRESS-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function functiondelivadd(id)
{
var blocdelivadd=document.getElementById('blocdelivadd');
//if(blocdelivadd.style.display=='inline') blocdelivadd.style.display='none';
//else{
blocdelivadd.style.display='inline';
           
	var idcompanyaddressdelivery = document.getElementById('idcompanyaddressdelivery'),
    myElementValueadddelivery = idcompanyaddressdelivery.value;
		   
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "functiondelivadd.php?id="+myElementValueadddelivery, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_affdelivadd(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function up_affdelivadd(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divdelivadd').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divdelivadd').innerHTML+=resp2;
    document.getElementById('divdelivadd').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Affichage adresse selectionne DELIVERY ADDRESS-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->


<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--DELIVERY METHOD -FORWARDER -->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function funcselectforwarder(id)
{
var blocselectforwarder=document.getElementById('blocselectforwarder');
//if(blocselectforwarder.style.display=='inline') blocselectforwarder.style.display='none';
//else{
blocselectforwarder.style.display='inline';
           
	var idForwarder = document.getElementById('idForwarder'),
    myElementValueadddelivery = idForwarder.value;
		   
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "funcselectforwarder.php?id="+myElementValueadddelivery, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_selectforwarder(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function up_selectforwarder(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divselectforwarder').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divselectforwarder').innerHTML+=resp2;
    document.getElementById('divselectforwarder').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--END DELIVERY METHOD -FORWARDER -->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

<!--*******************************************************************************-->
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
<!--*******************************************************************************-->
</script>
<!--*******************************************************************************-->
<?php
$sqlrfq3="SELECT * from tbl_RFQ_2 where Fld_RFQ_ID='".$_GET['Fld_RFQ_ID']."' ORDER BY ID DESC";
$reqrfq3 = mysql2_query($sqlrfq3);
	while($datarfq3 = mysqli_fetch_array($reqrfq3)){
		?>
<script language="JavaScript" type="text/javascript">
$('#myModal<?PHP echo $datarfq3['ID'];?>').on('click', '.btn-primary', function(){
    var value = $('#myPopupInput').val();
    var value2 = $('#myPopupInput2').val();
    $('#myMainPageInput').val(value);
    $('#myMainPageInput2').val(value2);
    $('#myModal<?PHP echo $datarfq3['ID'];?>').modal('hide');
});
</script>
	<?php }?>
<!--*******************************************************************************-->
    
	
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>