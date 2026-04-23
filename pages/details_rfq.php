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
	
			<!--CSS rating ajoute par roy-->
			<link href="rating.css" rel="stylesheet">
			<!--Fin CSS rating ajoute par roy-->
</head>

<body>
    <div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>                       <!-- barre rouge -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

 

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html"></a>
            </div>
            <!-- /.navbar-header -->

            <?php
		//ajout le menu du haut
		include "top_menu.php";
	   ?>
            <!-- /.navbar-top-links -->

        <?php
		//ajout le menu de gauche
		include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
		<?php 
	//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID pn_rfq description_rfq
											//recuperation info RFQ ********************
											$sql="SELECT * FROM tbl_RFQ_1 WHERE Fld_RFQ_ID='".$_GET["Fld_RFQ_ID"]."'";
											// echo $sql;
											$req = mysql2_query($sql);
											$data = mysqli_fetch_array($req);
											//Fin recuperation info RFQ ********************
		?>
         <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-10">
                   
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            DETAILS RFQ
                        </div>
						<form method="post" name="Form1"><!-- valid_add_rfq-->
				
						<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $_GET["Fld_RFQ_ID"];?>">
						<input type="hidden" name="RFQ_DATE" value="<?php echo $data['date'];?>">
                        <div class="panel-body">
                           <div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ ID : </label> <b><?php echo $data['Fld_RFQ_ID'];?></b>
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>DATE : </label> <b><?php echo $data['date'];?></b>
                                    </div>
								</div>
								
								<div class="col-lg-8">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
												if($dataPriority['Fld_Priority_ID']==$data['Fld_Priority_ID']) echo "selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$data['Employee_ID']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
							</div>

						   <div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>CUSTOMER'S NAME</label><br>
											<?php
		
											//recuperation du nom de compagnie ********************
											$sqlrn="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID=".$data['Fld_Customer_ID'];
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation du nom de compagnie********************
											
											echo $datarn['Fld_Company_Name'];
											?>
											<input type="hidden" name="Fld_Customer_ID" value="<?php echo $data['Fld_Customer_ID'];?>">
											<!--<input type="text" name="companyid" id="companyid" class="companyid" placeholder="<?php //echo $datarn['Fld_Company_Name'];?>" >-->
                                        </div>
								</div>
								<div class="col-lg-2" id='bloccontactname'>
										<div class="form-group" id='divcontactname'>
										<label>CONTACT NAME</label>
										
											<select class="form-control" name="id_company_contact">
											<?php
											//recuperation des contacts de compagnie
											// **tb_company_contact** id_company_contact Fld_Linked_ID  Fld_Company_ID  Company_Old_Id  Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2  Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID  Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark  status  aci_contact  entry_date
											
					                        $sqlcc="SELECT id_company_contact,Fld_Contact_Name FROM tb_company_contact where Fld_Company_ID='".$data['Fld_Customer_ID']."'";
											
											
											$reqcc = mysql2_query($sqlcc);
											while ($datacc = mysqli_fetch_array($reqcc))
											{
											echo "<option value='".$datacc['id_company_contact']."'";
											if ($datacc['id_company_contact']==$data['id_company_contact']) echo " selected";
											echo ">".$datacc['Fld_Contact_Name']."</option>";
											}
													?>
                                            </select>

                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TERMS</label>
											<select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'";
												if ($dataptid['Fld_Payment_Term_ID']==$data['Fld_Payment_Term_ID']) echo "selected";
												echo ">".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
								</div>
							</div>
							<br><br>
							<?php 
											$colorvar=0;
											$z=0;
											//recuperation des infos RFQ ********************
											$sql2="SELECT * FROM tbl_RFQ_1 WHERE Fld_RFQ_ID='".$data['Fld_RFQ_ID']."'";
											// echo $sql2;
											$req2 = mysql2_query($sql2);
											while($data2 = mysqli_fetch_array($req2))
											{
											$z++;
											$colorvar++;
											 if($colorvar=="2") 
											 {
											 $couleuraff="style='background-color:#E6E6E6;'";
											 $colorvar="0"; 
											 } 
											 else $couleuraff="style='background-color:#6E6E6E;color:#E6E6E6'";
											 
											//Fin recuperation des infos RFQ ********************
											
											//verification si une quotation a ete faite avec rfq id
											//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID  id_tbl_rfq1 rfqvalid
											
											$sqlverifquote="SELECT * FROM tbl_RFQ_3 WHERE Fld_RFQ_ID='".$data['Fld_RFQ_ID']."' AND id_tbl_rfq1='".$data2["ID"]."'";
											// echo $sqlverifquote;
											$resultverifquote = mysql2_query($sqlverifquote);
											$num_rows_rfq3 = mysqli_num_rows($resultverifquote);
											$dataverifquote = mysqli_fetch_array($resultverifquote);
											//Fin verification si une quotation a ete faite avec rfq id
											
							?>
							<input type="hidden" name="RFQ3_ID<?php echo $z;?>" value="<?php echo $dataverifquote["ID"];?>">
							<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $data['Fld_RFQ_ID'];?>">
							<input type="hidden" name="num_rows_rfq3" value="<?php echo $num_rows_rfq3;?>">
							<input type="hidden" name="ID<?php echo $z;?>" value="<?php echo $data2["ID"];?>">
							<input type="hidden" name="id_tbl_rfq1<?php echo $z;?>" value="<?php echo $data2["ID"];?>">
							 <div class="row" <?php echo $couleuraff;?>>
									<div class="col-lg-1"><br>
										<label class="container"><?php echo $z;?>
										<input type="checkbox" checked="checked" name="rfqvalid<?php echo $z;?>" value="ok">
										<span class="checkmark"></span>
										</label>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PN</label><br>
											<?php
											////Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status
											//recuperation PN ***********************
											$sqlrn="SELECT Fld_Part_Nbr,Fld_Part_Desc FROM tbl_Parts WHERE Fld_Part_ID=".$data2['Fld_Part_ID'];
											$reqrn = mysql2_query($sqlrn);
											$datarn = mysqli_fetch_array($reqrn);
											//Fin recuperation PN********************
											?>
											<input class="form-control" name="pnid<?php echo $z;?>" id="pnid<?php echo $z;?>" value="<?php echo $datarn["Fld_Part_Nbr"];?>, <?php echo $data2['Fld_Part_ID'];?>">
											<!--<input type="text" name="pnid<?php //echo $z;?>" id="pnid<?php //echo $z;?>" class="pnid" value="<?php //echo $datarn["Fld_Part_Nbr"];?>, <?php //echo $data2['Fld_Part_ID'];?>">-->
											<input type="hidden" name="Fld_Part_ID<?php echo $z;?>" value="<?php echo $data2["Fld_Part_ID"];?>">
											<input type="hidden" name="pn_rfq<?php echo $z;?>" value="<?php echo $datarn["Fld_Part_Nbr"];?>">
                                        </div>
									</div>
									<div class="col-lg-2" id='blocdescription<?php echo $z;?>'>
										<div class="form-group" id='divdescription<?php echo $z;?>'>
                                            <label>DESCRIPTION</label>
											<input class="form-control" name="description<?php echo $z;?>" id="description<?php echo $z;?>" value="<?php echo $datarn["Fld_Part_Desc"];?>">
											<!--<input class="form-control" name="description<?php //echo $z;?>" id="description<?php //echo $z;?>" onclick="javascript:descfrompn(<?php //echo $z;?>);" value="<?php //echo $datarn["Fld_Part_Desc"];?>">-->
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty<?php echo $z;?>" id="Fld_Qty" value="<?php echo $data2["Fld_Qty"];?>">
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID<?php echo $z;?>">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']==$data2['Fld_Condition_ID']) echo "selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID<?php echo $z;?>">
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'";
												if($datarfqt['Fld_RFQ_Type_ID']==$data2['Fld_RFQ_Type_ID']) echo"selected";
												echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>INTERNAL REMARKS</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="1" name="Fld_Observation<?php echo $z;?>" id="Fld_Observation" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
    box-shadow: 0 0 10px #a7142a;"><?php echo $data2["Fld_Observation"];?></textarea>
                                        </div>
                                    </div>
							</div>
											<?PHP
											//**************************************************************************************************************************
											//recuperation des info supplier
											if(!empty($_GET['idsupplier']))
											{
												//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  
												//Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
												$sqlrfq2="SELECT * from tbl_RFQ_2 where ID='".$_GET['idsupplier']."'";
												//echo $sqlrfq2;
												$reqrfq2 = mysql2_query($sqlrfq2);
												$datarfq2 = mysqli_fetch_array($reqrfq2);
											}
											//Fin recuperation des info supplier
											//**************************************************************************************************************************
											?>
							<div class="row" <?php echo $couleuraff;?>>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN<?php echo $z;?>" id="Fld_Part_SN" value="<?php if(!empty($dataverifquote['Fld_Part_SN'])) echo $dataverifquote['Fld_Part_SN'];?><?php if(!empty($datarfq2['Fld_Part_SN'])) echo $datarfq2['Fld_Part_SN'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>MOQ</label>
                                            <input class="form-control" name="moq<?php echo $z;?>" id="moq" value="<?php if(!empty($dataverifquote['moq'])) echo $dataverifquote['moq'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID<?php echo $z;?>">
											<option></option>
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'";
												if((!empty($dataverifquote['Fld_Release_ID']))&&($dataverifquote['Fld_Release_ID']==$datar['Fld_Release_ID'])) echo " selected";
												echo ">".$datar['Fld_Release_Text']."</option>";
											}
											
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
											<?php if(!empty($dataverifquote['Fld_Tag_Info_ID'])){
											//recuperation du nom de compagnie TAG INFO ********************
											$sqltiid="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$dataverifquote['Fld_Tag_Info_ID']."'";
											$reqtiid = mysql2_query($sqltiid);
											$datatiid = mysqli_fetch_array($reqtiid);
											//Fin recuperation du nom de compagnie TAG INFO ********************
											?>
                                            <input type="text" name="Fld_Tag_Info_ID<?php echo $z;?>" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" value="<?php echo $datatiid['Fld_Company_Name'];?>, <?php echo $dataverifquote['Fld_Tag_Info_ID'];?>" >

											<?php }
											else {
												?>
												<input type="text" name="Fld_Tag_Info_ID<?php echo $z;?>" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" placeholder="Please Enter company" >
											<?php }?>
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date<?php echo $z;?>" id="Fld_Tag_Date" value="<?php if(!empty($dataverifquote['Fld_Tag_Date'])) echo $dataverifquote['Fld_Tag_Date'];?><?php if(!empty($datarfq2['Fld_Tag_Date'])) echo $datarfq2['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>Traced To</label><br>
											
											<?php if(!empty($dataverifquote['Fld_Traceability_ID'])){
											//recuperation du nom de compagnie TRACABILITY ********************
											$sqltrac="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$dataverifquote['Fld_Traceability_ID']."'";
											$reqtrac = mysql2_query($sqltrac);
											$datatrac = mysqli_fetch_array($reqtrac);
											//Fin recuperation du nom de compagnie TRACABILITY ********************
											?>
                                            <input type="text" name="Fld_Traceability_ID<?php echo $z;?>" id="Fld_Traceability_ID" class="Fld_Traceability_ID" value="<?php echo $datatrac['Fld_Company_Name'];?>, <?php echo $dataverifquote['Fld_Traceability_ID'];?>" >
											
											<?php }
											else {
												?>
												<input type="text" name="Fld_Traceability_ID<?php echo $z;?>" id="Fld_Traceability_ID" class="Fld_Traceability_ID" placeholder="Please Enter company" >
											<?php }?>
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>LEAD TIME</label>
                                            <input class="form-control" name="lead_time<?php echo $z;?>" id="lead_time" value="<?php if(!empty($dataverifquote['lead_time'])) echo $dataverifquote['lead_time'];?><?php if(!empty($datarfq2['lead_time'])) echo $datarfq2['lead_time'];?>">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price<?php echo $z;?>" id="Fld_Price" value="<?php if(!empty($dataverifquote['Fld_Price'])) echo $dataverifquote['Fld_Price'];?><?php if(!empty($datarfq2['Fld_Price'])) echo $datarfq2['Fld_Price'];?>">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="FldCurrencyID<?php echo $z;?>" id="FldCurrencyID">
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text htmlcode
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'";
												if ($datacid['Fld_Currency_ID']==$dataverifquote['Fld_Currency_ID']) echo " selected";
												elseif ($datacid['Fld_Currency_ID']==$datarfq2['Fld_Currency_ID']) echo " selected";
												echo ">".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
						   </div>
						   <div class="row" <?php echo $couleuraff;?>>
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>COMMENTS FOR THE CLIENT</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark<?php echo $z;?>" id="Fld_Remark"><?php if(!empty($dataverifquote['Fld_Remark'])) echo $dataverifquote['Fld_Remark'];?><?php if(!empty($datarfq2['Fld_Remark'])) echo $datarfq2['Fld_Remark'];?></textarea>
                                        </div>
                                    </div>
									<div class="col-lg-2">
									<br><br>
									  <a href="copy_rfq.php?ID=<?php echo $data2["ID"];?>&Fld_RFQ_ID=<?php echo $_GET['Fld_RFQ_ID'];?>"><i class="fa fa-copy" width="30px"></i> DUPLICATE THIS PN</a>
									  <br>
									  <a href="remove_rfq.php?ID=<?php echo $data2["ID"];?>" onClick="return(confirm('Etes vous sur ?'));"><span class="glyphicon glyphicon-remove"></span>REMOVE THIS PN </a>
									  <br>
									  <a href="javascript:void(0);" data-href="getContent_choose_supplier.php?Fld_Part_ID=<?php echo $data2["Fld_Part_ID"];?>&Fld_RFQ_ID=<?php echo $_GET["Fld_RFQ_ID"];?>" class="openPopup"><i class="fa  fa-plane"></i></a>
                                    </div>
						   </div>
											
											<br>
								<?php }?>
											<input type="hidden" name="nbline" value="<?php echo $z;?>">
						   <div class="row">
						   <div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="VALID MODIF" name=button1 onclick="return OnButton1();" class="btn btn-success">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SAVE & SEND QUOTE" name=button2 onclick="return OnButton2();" class="btn btn-success">
										</div>
								</div>
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="ADD PN" name=button2 onclick="return OnButton3();" class="btn btn-success">
										</div>
								</div>
						   </div>						   
                        </div>
						</form>
						<!--***********************************************************************************************************-->
						<!--***********************************************************************************************************-->
						<!--ajout pn a la rfq-->
					<div id="addpnrfq" style="display:none;padding:10px;">
					<form id="formajoutcontactcompany" role="form" method="post" action="valid_add_pn_rfq.php">
						<input type="hidden" name="Fld_RFQ_ID" value="<?php echo $_GET['Fld_RFQ_ID'];?>">
						<input type="hidden" name="RFQ_DATE" value="<?php echo $data['date'];?>">
						<input type="hidden" name="Fld_Priority_ID" value="<?php echo $data['Fld_Priority_ID'];?>">
						<input type="hidden" name="Employee_ID" value="<?php echo $data['Employee_ID'];?>">
						<input type="hidden" name="Fld_Customer_ID" value="<?php echo $data['Fld_Customer_ID'];?>">
						<input type="hidden" name="id_company_contact" value="<?php echo $data['id_company_contact'];?>">
						<input type="hidden" name="Fld_Payment_Term_ID" value="<?php echo $data['Fld_Payment_Term_ID'];?>">
						
						          
						<div class="row">
								<div class="col-lg-12" style="background-color:white;">
								<br>
								&nbsp;
								</div>
						</div>
					
						 <div class="row" style="padding:10px;">
							<div class="col-lg-12">
							<b>ADD A PN</b>
							</div>
						 </div>
						 <div class="row" style="padding:10px;">
									
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PN</label><br>
											<input type="text" name="pnidnew" id="pnidnew" class="pnid" value="">
                                        </div>
									</div>
									<div class="col-lg-2" id='blocdescriptionnew'>
										<div class="form-group" id='divdescriptionnew'>
                                            <label>DESCRIPTION</label>
											<input class="form-control" name="descriptionnew" id="descriptionnew" onclick="javascript:descfrompn('new');" value="">
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="">
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'>".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID">
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'>".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'";
												if($dataPriority['Fld_Priority_ID']==$data['Fld_Priority_ID']) echo "selected";
												echo ">".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
									
							</div>

						   <div class="row" style="padding:10px;">
									<div class="col-lg-6">
										<div class="form-group">
                                            <label>INTERNAL REMARKS</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
    box-shadow: 0 0 10px #a7142a;"></textarea>
                                        </div>
                                    </div>
								
						   </div>
						   <div class="row">
						   <div class="col-lg-4">
										<div class="form-group" align="right">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										</div>
								</div>
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="SUBMIT" value="SUBMIT" class="btn btn-success" >
										</div>
								</div>
						   </div>	
					</FORM>
				</div>
						<!--END ajout pn a la rfq-->
						<!--***********************************************************************************************************-->
						<!--***********************************************************************************************************-->

                        <!-- /.panel-body -->
						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

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
	
	<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

	
	<!--*****************************************************************************************************************************************-->
	<!--*****************************************************************************************************************************************-->
	<!--**************************************POPUP CHOICE SUPPLIERS*****************************************************************************-->

	<div class="modal fade" id="myModalSendQuote" role="dialog">
    <div class="modal-dialog">
      <form method="post" name="Form2">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="background-color:#A7142A;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <!--<h4 class="modal-title"></h4>-->
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      </form>
    </div>
</div>

<script>
$(document).ready(function(){
    $('.openPopup').on('click',function(){
        var dataURL = $(this).attr('data-href');
        $('.modal-body').load(dataURL,function(){
            $('#myModalSendQuote').modal({show:true});
        });
    }); 
});
</script>
	
	<!--**************************************END POPUP CHOICE SUPPLIERS*********************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	
	
    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
    </script>
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
    <script src="js/typeahead.js"></script>
    <style>
       
		.tt-hint,
        .companyid,.companyidtaginfo,.companyidtreacability,.pnid,.pnidnew,.Fld_Tag_Info_ID,.Fld_Traceability_ID {
            display: block;
    width: 190px;
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
        }

        .tt-dropdown-menu {
            width: 400px;
            margin-top: 5px;
            padding: 8px 12px;
            background-color: #fff;
            border: 1px solid #ccc;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px 8px 8px 8px;
            font-size: 18px;
            color: #111;
            background-color: #F1F1F1;
        }
    </style>
    <script>
        $(document).ready(function() {

            $('input.companyid').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtaginfo').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtreacability').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.pnid').typeahead({
                name: 'Fld_Part_Nbr',
				id: 'Fld_Part_ID',
                remote: 'list-pn-select.php?query=%QUERY'
            });
			$('input.pnidnew').typeahead({
                name: 'Fld_Part_Nbr',
				id: 'Fld_Part_ID',
                remote: 'list-pn-select.php?query=%QUERY'
            });
			$('input.Fld_Tag_Info_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Traceability_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
	function majtarea(id)
{
var bloccontactname=document.getElementById('bloccontactname');
var companyidval=document.getElementById('companyid').value;

bloccontactname.style.display='inline';

//document.getElementById("divcontactname").innerHTML='<div id="divcontactname" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "contactnamefromcompany-rfq.php?id="+companyidval, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_contact_name(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactname').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactname').innerHTML+=resp2;
    document.getElementById('divcontactname').innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Ajout nom contact a partir du nom de la societe-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!--Recuperation Description a partir du P/N-->
<!--*******************************************************************************--> 
<!--*******************************************************************************-->
	function descfrompn(idz)
{
var blocdescription=document.getElementById('blocdescription'+idz);
var pnid=document.getElementById('pnid'+idz).value;

blocdescription.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "descriptionfrompn.php?id="+pnid+"&idz="+idz, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_descfrompn(xhr,idz); };
            xhr.send("idz="+idz);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_descfrompn(xhr,idz)
{
if (xhr.readyState==4)
    {
    document.getElementById('divdescription'+idz).innerHTML='<div id="'+idz+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divdescription'+idz).innerHTML+=resp2;
    document.getElementById('divdescription'+idz).innerHTML+='</div>';
    }
}

<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- Fin Recuperation Description a partir du P/N-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->


<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** DIFFERENTS ACTION SUR BOUTON SUBMIT************************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
function OnButton1()
{
    document.Form1.action = "valid_modif_rfq.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton2()
{
    document.Form1.action = "email_broadcast_multi.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton3()
{
var addpnrfq=document.getElementById('addpnrfq');
if(addpnrfq.style.display=='inline') addpnrfq.style.display='none';
else addpnrfq.style.display='inline';
return true;
}

-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->
<!-- ****************** FIN DIFFERENTS ACTION SUR BOUTON SUBMIT********************-->
<!--*******************************************************************************-->
<!--*******************************************************************************-->

</script>
<!---->
<style>
/* Customize the label (the container) */
.container {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 25px;
  width: 25px;
  background-color: #eee;
}

/* On mouse-over, add a grey background color */
.container:hover input ~ .checkmark {
  background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark {
  background-color: #A7142A;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.container input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.container .checkmark:after {
  left: 9px;
  top: 5px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>
<!---->
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>