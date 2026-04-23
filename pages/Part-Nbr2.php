<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
	
//maakav pn
$today=date("Y-m-d");
$heuretoday=date("g:i a");
$requete = mysql2_query("INSERT INTO `tbl_maakav_pn` (`id_maakav_pn`, `id_part`, `datepn`, `heurevisitepn`, `id_Employee`) VALUES (NULL, '".$_GET['part_id']."', '".$today."', '".$heuretoday."', '".$_SESSION['id_utilisateur']."');");
//Fin maakav pn
?>
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

 

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"></a>
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
        <div id="page-wrapper">
            
            <!-- /.row -->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            PART NUMBER
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body" style="padding-bottom: 1px;">                           
						   <?php
/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$_GET['part_id']."' and status='Available'";
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);

							//recuperation du nom du aircraft	
							if (!empty($data["Fld_AC_ID"])){
							// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
						    $sqlac="SELECT Fld_AC_Model FROM tbl_Aircraft where Fld_AC_ID=".$data["Fld_AC_ID"];
							
							$reqac=mysql2_query($sqlac);
						    $dataac = mysqli_fetch_array($reqac);
							$Aircraft_model=$dataac['Fld_AC_Model'];
							}
							else $Aircraft_model="";
							//Fin recuperation du nom du aircraft
						
					
			?>
			<form action="majpnauto.php" method="post">
			<input type="hidden" name="Fld_Part_ID" value="<?php echo $data['Fld_Part_ID'];?>">
			<input type="hidden" name="Fld_Part_MFG" value="<?php echo $data["Fld_Part_MFG"];?>">
							<div class="row">
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" id="Fld_Part_Nbr" value="<?php echo $data["Fld_Part_Nbr"];?>" disabled>
											<!--onmouseleave='javascript:majpn(<?php //echo $data['Fld_Part_ID'];?>)'-->
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
											<a href="#" onclick="disable()"></a><a href="#" onclick="enable()"><img src="images/Edit-validated-icon.png" border="0"></a>
                                            <input class="form-control" name="Fld_Part_Desc" id="Fld_Part_Desc" value="<?php echo $data['Fld_Part_Desc'];?>" disabled >
											
                                        </div>
                                    </div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>ALT PN</label>
											<input class="form-control" name="alt_pn" id="alt_pn" value="<?php echo htmlspecialchars($data['alt_pn']);?>" >

                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>AIRCRAFT</label>
                                            <select class="form-control" name="Fld_AC_ID" id="Fld_AC_ID">
											<option></option>
											<?php
											//recuperation des types de compagnie
											// **tbl_Aircraft** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					                        $sqlair="SELECT * FROM tbl_Aircraft order by Fld_AC_Model";
											
											$reqair = mysql2_query($sqlair);
											while($dataair = mysqli_fetch_array($reqair)){
												echo "<option value='".$dataair['Fld_AC_ID']."'";
												if ($data["Fld_AC_ID"]==$dataair['Fld_AC_ID']) echo "selected";
												echo ">".$dataair ['Fld_AC_Model']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
                                        </div>
									</div>	
					<div class="col-lg-1">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-9 text-right">
								<?php
									$sqlsqcount="SELECT count(*) as crfq from tbl_RFQ_1 where 	Fld_Part_ID='".$_GET['part_id']."'";
									$reqsqcoun = mysql2_query($sqlsqcount);
									$datasqcoun = mysqli_fetch_array($reqsqcoun);
									
									?>
                                    <div > RFQ : <?php echo $datasqcoun['crfq'];?></div>
                                    
                                </div>
                            </div>
                        </div>
                       
                    </div>
					</div>
					<div class="col-lg-1">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-9 text-right">
								<?php
									$sqlsqcount="SELECT count(*) as sqc from tbl_RFQ_2 where Fld_Part_ID='".$_GET['part_id']."'";
									$reqsqcoun = mysql2_query($sqlsqcount);
									$datasqcoun = mysqli_fetch_array($reqsqcoun);
									
									?>
                                    <div > SQ : <?php echo $datasqcoun['sqc'];?></div>
                                    
                                </div>
                            </div>
                        </div>
                       
                    </div>
					</div>
					
					<div class="col-lg-1">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-9 text-right">
								<?php
									$sqlsqcount="SELECT count(*) as qc from tbl_RFQ_3 where Fld_Part_Id='".$_GET['part_id']."'";
									$reqsqcoun = mysql2_query($sqlsqcount);
									$datasqcoun = mysqli_fetch_array($reqsqcoun);
									
									?>
                                    <div > Q : <?php echo $datasqcoun['qc'];?></div>
                                    
                                </div>
                            </div>
                        </div>
                       
                    </div>
					</div>	
					
					<div class="col-lg-1">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-9 text-right">
								<?php
									$sqlsqcount="SELECT count(*) as sc from tbl_Stock where Fld_Part_ID='".$_GET['part_id']."'";
									$reqsqcoun = mysql2_query($sqlsqcount);
									$datasqcoun = mysqli_fetch_array($reqsqcoun);
									
									?>
                                    <div > S : <?php echo $datasqcoun['sc'];?></div>
                                    
                                </div>
                            </div>
                        </div>
                       
                    </div>
					</div>
									
									
							</div>	
							<div class="row">
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>OEM</label><br>
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID=".$data["Fld_Part_MFG"];
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation des type de compagnie
											?>
											<input type="text" name="companyidforoem" id="companyidforoem" class="companyidforoem" placeholder="<?php echo $dataemp['Fld_Company_Name'];?>" >
                                        </div>
										<div class="form-group">
                                            <label>CAGE CODE #</label>
											<?php
											if (!empty($data["cage_code"])) echo "<input class=\"form-control\" name=\"cage_code\" id=\"cage_code\" value=\"".$data['cage_code']."\" >";
											else {
											//recuperation du cage code 
					                        $sqlccn="SELECT cage_code FROM tb_company where Fld_Company_ID=".$data["Fld_Part_MFG"];
											
											$reqccn = mysql2_query($sqlccn);
											$dataccn = mysqli_fetch_array($reqccn);
					                        //Fin recuperation du cage code
											echo "<input class=\"form-control\" name=\"cage_code\" id=\"cage_code\" value=\"".$dataccn['cage_code']."\" >";
											}
											?>
                                            
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>LIST PRICE</label>
                                            <input class="form-control" name="Fld_Part_List_Price" id="Fld_Part_List_Price" value="<?php echo $data['Fld_Part_List_Price'];?>" >
                                        </div>
										<div class="form-group">
                                            <label>ATA CHAPTER</label>
                                            <input class="form-control" name="ata_chapter" id="ata_chapter" value="<?php echo $data['ata_chapter'];?>" >
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="FldCurrencyID" id="FldCurrencyID" >
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'";
												if ($data["Fld_Part_Price_Currency_ID"]==$datacid['Fld_Currency_ID']) echo "selected";
												echo ">".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>LP DATE</label>
                                            <input class="form-control" name="Fld_Part_LP_Date" id="Fld_Part_LP_Date" value="<?php echo $data['Fld_Part_LP_Date'];?>" >
                                        </div>
									</div>
									
									
									
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>REMARK</label>
											<textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark" ><?php echo $data['Fld_Remark'];?></textarea>
                                        </div>
										<div class="form-group" align="right">
										<button type="submit" class="btn btn-default">SUBMIT</button>
										</div>
									</div>
									
					
									
							</div>
						</form>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
						<!--RFQ'S-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            REQUEST FOR QUOTATION  <a href="add_rfq.php" style="color:white;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD MULTIPLE RFQ</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
										    <th></th>
                                            <th>RFQ ID</th>
											<th>DATE</th>
                                            <th>QTY</th>
                                            <th>PN</th>
                                            <th>DESCRIPTION</th>
                                            <th>OBSERVATION</th>
                                            <th>CUSTOMER</th>
                                            <th>CUSTOMER CONTACT</th>
                                            <th>RFQ TYPE</th>
                                            <th>PRIORITY</th>
                                            <th>TERMS</th>
                                            <th>CONDITION</th>
                                            <th>SALES CONTACT</th>
                                            <th></th>
										
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					//****tbl_RFQ_1****ID Fld_RFQ_ID  Fld_Qty  Fld_Part_ID  Fld_Observation  Fld_Customer_ID date  Fld_RFQ_Type_ID  Fld_Priority_ID  Employee_ID  id_company_contact  Fld_Payment_Term_ID  Fld_Condition_ID pn_rfq description_rfq
					$sqlrfq3="SELECT tbl_RFQ_1.*,tb_company.Fld_Company_Name from tbl_RFQ_1,tb_company where tbl_RFQ_1.Fld_Customer_ID=tb_company.Fld_Company_ID AND tbl_RFQ_1.Fld_Part_ID='".$_GET['part_id']."' ORDER BY ID Desc";
					//echo $sqlrfq3;
					$reqrfq3 = mysql2_query($sqlrfq3);
					while($datarfq3 = mysqli_fetch_array($reqrfq3))
					{
											
											//recuperation du nom du contact dans la societe ********************
											$sqlls="SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact=".$datarfq3['id_company_contact'];
											$reqls = mysql2_query($sqlls);
											$datals = mysqli_fetch_array($reqls);
											//Fin recuperation du nom du contact dans la societe ********************
											
											//recuperation de conditions ********************
											//tbl_Condition****Fld_Condition_ID  Fld_Condition_Text
											$sqlct="SELECT Fld_Condition_Text FROM  tbl_Condition WHERE Fld_Condition_ID=".$datarfq3['Fld_Condition_ID'];
											$reqct = mysql2_query($sqlct);
											$datact = mysqli_fetch_array($reqct);
											//Fin recuperation de conditions ********************
											
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT Fld_RFQ_Type_Text FROM tbl_RFQ_Type where Fld_RFQ_Type_ID=".$datarfq3['Fld_RFQ_Type_ID'];
											
											$reqrfqt = mysql2_query($sqlrfqt);
											$datarfqt = mysqli_fetch_array($reqrfqt);
					                        //Fin recuperation RFQ Type
											
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT Fld_Priority_Text FROM tbl_Priority where Fld_Priority_ID=".$datarfq3['Fld_Priority_ID'];
											
											$reqPriority = mysql2_query($sqlPriority);
											$dataPriority = mysqli_fetch_array($reqPriority);
					                        //Fin recuperation Priority
											
											//recuperation Employee_Name
					                        $sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$datarfq3['Employee_ID'];
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
											
					                        //Fin recuperation Employee_Name
											
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlptid="SELECT * FROM tbl_Payment where Fld_Payment_Term_ID=".$datarfq3['Fld_Payment_Term_ID'];
											
											$reqptid = mysql2_query($sqlptid);
											$dataptid = mysqli_fetch_array($reqptid);
					                        //Fin recuperation des TERMS
											
											
                                            echo "<tr><td><input type=\"radio\" name=\"rfq_choice_id\" value='".$datarfq3['ID']."' onchange=\"view_rfq('".$datarfq3['ID']."');recup_info_rfq('".$datarfq3['ID']."');\"></td><td>".$datarfq3['Fld_RFQ_ID']."</td><td>".$datarfq3['date']."</td><td>".$datarfq3['Fld_Qty']."</td><td>".$datarfq3['pn_rfq']."</td><td>".$datarfq3['description_rfq']."</td><td>".$datarfq3['Fld_Observation']."</td><td>".$datarfq3['Fld_Company_Name']."</td><td>".$datals['Fld_Contact_Name']."</td><td>".$datarfqt['Fld_RFQ_Type_Text']."</td><td>".$dataPriority['Fld_Priority_Text']."</td><td>".$dataptid['Fld_Payment_Text']."</td><td>".$datact['Fld_Condition_Text']."</td><td>".$dataemp ['Employee_Name']."</td><td>";
											if($_SESSION['statut']=="SuperAdmin") echo "<a href='del_rfq.php?ID=".$datarfq3['ID']."&part_id=".$_GET['part_id']."'  onClick=\"return(confirm('Etes vous sur ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a>";
											echo "</td></tr>";
					}
?>					
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--END RFQ'S-->
			
			<!--Accordion-->
			 <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            QUOTATION STEPS
                        </div>
                        <!-- .panel-heading -->
                        <div class="panel-body">
                            <div class="panel-group" id="accordion">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">REQUEST FOR QUOTATION</a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse in">
                                        <div class="panel-body">
                                           <!--RFQ STEPS-->
							<div class="row">
							<form action="valid_add_rfq.php" method="post"><!-- valid_add_rfq-->
						<input type="hidden" name="Fld_Part_ID" value="<?php echo $data['Fld_Part_ID'];?>">
						<input type="hidden" name="part_id" value="<?php echo $_GET['part_id'];?>">
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>RFQ ID</label>
                                            <input class="form-control" name="RFQ_ID" value="<?php echo date("Y-m-d-His");?>">
                                        </div>
								</div>
								<div class="col-lg-1" style="width: 160px;">
										<div class="form-group" style="width: 160px;">
                                            <label>CUSTOMER'S NAME</label><br>
											<input type="text" name="companyid" id="companyid" class="companyid" placeholder="Please Enter company" style="width: 160px;">
											<BR><BR>
											<button type="button" class="btn btn-primary btn-xs" onclick="window.open('ajoutcompanypopup.php', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,top=20,left=500,width=400,height=400');">ADD COMPANY</button>
                                        </div>
								</div>
								
								<div class="col-lg-10">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo date("d/m/Y");?>">
                                    </div>
								</div>
								<div class="col-lg-1" id='bloccontactname'>
										<div class="form-group" id='divcontactname'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact" onclick="javascript:majtarea();">
											<option>CHOOSE</option>

                                            </select>
											

                                        </div>
								</div>
							</div>
							<div class="row">	
								<div class="col-lg-1">
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
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>TERMS</label>
											<select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation des TERMS
											// tbl_Payment****** Fld_Payment_Term_ID  Fld_Payment_Text
											
					                        $sqlptid="SELECT * FROM tbl_Payment";
											
											$reqptid = mysql2_query($sqlptid);
											while($dataptid = mysqli_fetch_array($reqptid)){
												echo "<option value='".$dataptid['Fld_Payment_Term_ID']."'>".$dataptid['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation des TERMS
											?>
                                                
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">	
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRIORITY</label>
											<select class="form-control" name="Fld_Priority_ID">
											<?php
											//recuperation Priority
											// ** tbl_Priority ** Fld_Priority_ID  Fld_Priority_Text
					                        $sqlPriority="SELECT * FROM tbl_Priority";
											
											$reqPriority = mysql2_query($sqlPriority);
											while($dataPriority = mysqli_fetch_array($reqPriority)){
												echo "<option value='".$dataPriority['Fld_Priority_ID']."'>".$dataPriority['Fld_Priority_Text']."</option>";
											}
					                        //Fin recuperation Priority
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>CREDIT LIMITS</label>
                                            <input class="form-control" name="credit_limits">
                                    </div>
								</div>
							</div>
							<div class="row">									
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>SALES CONTACT</label>
											<select class="form-control" name="Employee_ID">
											<?php
											//recuperation des types de compagnie
					                        $sqlemp="SELECT distinct(Employee_Name),Employee_ID FROM tbl_Employee";
											
											$reqemp = mysql2_query($sqlemp);
											while($dataemp = mysqli_fetch_array($reqemp)){
												echo "<option value='".$dataemp['Employee_ID']."'";
												if ($dataemp['Employee_ID']==$_SESSION['id_utilisateur']) echo "selected";
												echo ">".$dataemp ['Employee_Name']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
										</div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label style="color:#a7142a;">INTERNAL REMARK</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
											box-shadow: 0 0 10px #a7142a;"></textarea>
                                        </div>
                                    </div>
							</div>
							<div class="row">
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $data["Fld_Part_Nbr"];?>">
											<br><button type="button" class="btn btn-primary btn-xs" onclick="window.open('add_multi_parts_popup.php', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,top=20,left=500,width=400,height=400');">CREATE PN</button>
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description_rfq" id="description_rfq" value="<?php echo $data['Fld_Part_Desc'];?>">
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
							</div>
							<div class="row">
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
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
									
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>TAG INFO</label>
                                            <input type="text" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" placeholder="Please Enter company" >
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" id="Fld_Tag_Date" value="">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>STK LOC/LEAD TIME</label>
                                            <input class="form-control" name="lead_time" id="lead_time" value="">
                                        </div>
									</div>
							</div>
							<div class="row">
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" id="Fld_Price" value="">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CURRENCY</label>
                                            <select class="form-control" name="FldCurrencyID" id="FldCurrencyID">
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcid = mysql2_query($sqlcid);
											while($datacid = mysqli_fetch_array($reqcid)){
												echo "<option value='".$datacid['Fld_Currency_ID']."'>".$datacid['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>COMMENTS FOR THE CLIENT</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"></textarea>
                                        </div>
                                    </div>
							</div>
							<div class="row">		
									
									
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CUSTOMER'S PO#</label>
                                            <input class="form-control" name="customer_po" id="customer_po">
                                        </div>
                                    </div>
									<div class="col-lg-3">
									<BR>
									<button type="button" class="btn btn-primary">PO TRANSFO</button>
									<button type="button" class="btn btn-primary">ADD RFQ</button>
									<button type="button" class="btn btn-primary">SEND QUOTE</button>
									</div>
									
							</div>
							
		
						   <div class="row">
						   <div class="col-lg-4">
										<!--
										<div class="form-group" align="right">
										<button type="submit" class="btn btn-default">OPEN RFQs</button>
										</div>
										-->
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<button type="submit" class="btn btn-default">ADD RFQ</button>
										</div>
								</div>	
						   </div>	
						   </form>
                                           <!--END RFQ STEPS-->
                                        </div>
                                    </div>
                                </div>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">Collapsible Group Item #2</a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Collapsible Group Item #3</a>
                                        </h4>
                                    </div>
                                    <div id="collapseThree" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- .panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--End Accordion-->
			
			<!--STOCK select to quote-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            STOCK <a href="ajout_stock.php" style="color:white;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD STOCK</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>ID</th>
                                            <th>ACI 770 PO#</th>
                                            <th>CONDITION</th>
                                            <th>SN</th>
                                            <th>PURCHASE PRICE</th>
                                            <th>TAG INFO</th>
                                            <th>TAG DATE</th>
                                            <th>Traced To</th>                                            
											<th>STOCK LOCATION</th>
											<th>SALES REMARKS</th>
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					$sqlst="SELECT * from tbl_Stock where Fld_Part_ID='".$data['Fld_Part_ID']."'";
					//echo $sqlst;
					$reqst = mysql2_query($sqlst);
					while($datast = mysqli_fetch_array($reqst))
					{
					//recuperation condition 
					// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					$sqlc="SELECT * FROM tbl_Condition where Fld_Condition_ID=".$datast['Fld_Condition_ID'];
					
					$reqc = mysql2_query($sqlc);
					$datac = mysqli_fetch_array($reqc);
					// echo $datac['Fld_Condition_Text'];
					//Fin recuperation condition 

					//recuperation stock location
					// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					$sqlsl="SELECT * from tbl_Stock_Location where Fld_Stock_Location_ID=".$datast['Fld_Stock_Location_ID'];
					
					$reqsl = mysql2_query($sqlsl);
					$datasl = mysqli_fetch_array($reqsl);
					// echo $datasl['Fld_Stock_Location_Text'];
					//Fin recuperation stock location
					//recuperation du nom de la compagnie
					$sqlcomn="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID=".$datast['Fld_Tag_Info_ID'];
					
					$reqcomn = mysql2_query($sqlcomn);
					$datacn = mysqli_fetch_array($reqcomn);
					//Fin recuperation du nom de la compagnie
                                            echo "<tr><td><input type=\"radio\" name=\"stock_choice\" value='Fld_Stock_ID' onchange=\"quote_the_customer(".$datast['Fld_Stock_ID'].")\"></td><td><a href='javascript:addstock(".$datast['Fld_Stock_ID'].")'>".$datast['Fld_Stock_ID']."</a></td><td>".$datast['Fld_BAX_PO_Nbr']."</td><td>".$datac['Fld_Condition_Text']."</td><td>".$datast['Fld_Part_SN']."</td><td>".$datast['Fld_Part_Price']."</td><td>".$datacn['Fld_Company_Name']."</td><td>".$datast['Fld_Tag_Date']."</td><td>".$datast['Fld_Traceability_ID']."</td><td>".$datasl['Fld_Stock_Location_Text']."</td><td>".$datast['Fld_Sales_Remark']."</td></tr>";
					}
?>					

                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--Fin STOCK select to quote-->
			  
			 <div class="row" id='blocstock' style='display:none'>
                <div class="col-lg-12" id='divstock'>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            PN DETAILS
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                           
						   <?php
/*
			Table tbl_Stock ::::   Fld_Stock_ID  Fld_Part_ID  Fld_Part_SN  Fld_Supplier_ID  Fld_Entry_Date  Fld_Part_Price  Fld_Price_Currency_ID  Fld_BAX_PO_Nbr  Fld_Supplier_order_Date  Fld_Supplier_Payment_Date  Fld_Qty  Fld_Condition_ID  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  Fld_Warehouse_Location  Fld_Physical_Stock  Fld_Owner_ID  Fld_Stock_Location_ID  Fld_Status_ID  Fld_Status_Ind  Fld_Status_Date  Fld_Stock_Remark  Fld_Shelf_Life_Limit  Fld_Valeur_Comptable  Fld_Valeur_Comptable_currency_Id  Fld_Sales_Remark  Fld_External_Location  Fld_Sales_Remark_ID  Fld_Warehouse_Location_ID  Fld_OriginalUnit_Stock_ID  Fld_Min_Qty  Fld_Publish
*/
					$sql2="SELECT * from tbl_Stock where Fld_Part_ID='".$data['Fld_Part_ID']."'";
					
					
					$req2 = mysql2_query($sql2);
					$data2 = mysqli_fetch_array($req2);
			?>
						<div class="row">
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" value="<?php echo $data["Fld_Part_Nbr"];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="Fld_Part_Desc" value="<?php echo $data['Fld_Part_Desc'];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">			
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" value="<?php echo $data2['Fld_Part_SN'];?>">
                                        </div>
						   </div>
						</div>
						<div class="row">
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" value="<?php echo $data2['Fld_Qty'];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>MOQ (Minimum Qty)</label>
                                            <input class="form-control" name="Fld_Min_Qty" value="<?php echo $data2['Fld_Min_Qty'];?>">
                                        </div>
                            </div>
							<div class="col-lg-3">
										<div class="form-group">
                                            <label>CONDITION</label>
											<select class="form-control" name="Fld_Condition_ID">
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($data2['Fld_Condition_ID']==$datac['Fld_Condition_ID']) echo "selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
						   </div>
						</div>
						<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											//recuperation release
											// ** tbl_Release ** Fld_Release_ID  Fld_Release_Text
					                        $sqlr="SELECT * from tbl_Release";
											
											$reqr = mysql2_query($sqlr);
											while($datar = mysqli_fetch_array($reqr)){
												echo "<option value='".$datar['Fld_Release_ID']."'";
												if ($data2['Fld_Release_ID']==$datar['Fld_Release_ID']) echo "selected";
												echo ">".$datar['Fld_Release_Text']."</option>";
											}
					                        //Fin recuperation release 
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG INFO ?????</label>
                                            <input class="form-control" name="Fld_Tag_Info_ID" value="<?php echo $data2['Fld_Tag_Info_ID'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" value="<?php echo $data2['Fld_Tag_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>Traced To</label>
                                            <input class="form-control" name="Fld_Traceability_ID" value="<?php echo $data2['Fld_Traceability_ID'];?>">
                                        </div>
									</div>
                        </div>
						<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>Entry Date</label>
                                            <input class="form-control" name="Fld_Entry_Date" value="<?php echo $data2['Fld_Entry_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>No Days ????</label>
                                            <input class="form-control" name="" value="">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SHELF LIFE</label>
                                            <input class="form-control" name="Fld_Shelf_Life_Limit" value="<?php echo $data2['Fld_Shelf_Life_Limit'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>SALES REMARKS</label>
                                            <input class="form-control" name="Fld_Sales_Remark" value="<?php echo $data2['Fld_Sales_Remark'];?>">
                                        </div>
									</div>
                        </div>
						<div class="row">
									<div class="col-lg-12">
										<div class="form-group">
                                            <label>REMARKS ??? (Fld_Stock_Remark ??)</label>
											<textarea class="form-control" rows="3" name="Fld_Stock_Remark"><?php echo htmlspecialchars($data2['Fld_Stock_Remark']);?></textarea>
                                        </div>
									</div>
                        </div>
						
						<!--Suppliers-->
						<div class="row">
							<div class="col-lg-3">
							SUPPLIER
							</div>
						</div>
						<div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Suppliers</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>ACI 770 PO#</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Supplier order date</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $_GET['part_id'];?>">
                                        </div>
								</div>
							</div>
						<!--Fin Suppliers-->
						<!--STOCK DETAILS-->
						    <div class="row">
								<div class="col-lg-3">
								STOCK DETAILS
								<div>
							<div>
						    <div class="row">
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Location</label>
											<select class="form-control" name="Fld_Stock_Location_ID">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_Stock_Location_ID']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Warehouse Loc.</label>
											<select class="form-control" name="Fld_Warehouse_Location_ID">
											<?php
											//recuperation Warehouse location
											// ** tbl_Warehouse_Location ** Id  Fld_Location
					                        $sqlsl="SELECT * from tbl_Warehouse_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Id']."'";
												if ($data2['Fld_Warehouse_Location_ID']==$datasl['Id']) echo "selected";
												echo ">".$datasl['Fld_Location']."</option>";
											}
					                        //Fin recuperation Warehouse location
											?>
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Outside Loc. Fld_External_Location?? (tbl_Stock_Location??)</label>
											<select class="form-control" name="Fld_External_Location">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_External_Location']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
								</div>
							</div>	
							<div class="row">
										<div class="col-lg-3">
										<div class="form-group">
                                            <label>PHYSICAL STK (tbl_Stock_Location??)</label>
											<select class="form-control" name="Fld_Physical_Stock">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_Physical_Stock']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
										</div>
										<div class="col-lg-3">
										<div class="form-group">
                                            <label>VIRTUAL STK (tbl_Stock_Location??)</label>
											<select class="form-control" name="Fld_Physical_Stock">
											<?php
											//recuperation stock location
											// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
					                        $sqlsl="SELECT * from tbl_Stock_Location";
											
											$reqsl = mysql2_query($sqlsl);
											while($datasl = mysqli_fetch_array($reqsl)){
												echo "<option value='".$datasl['Fld_Stock_Location_ID']."'";
												if ($data2['Fld_Physical_Stock']==$datasl['Fld_Stock_Location_ID']) echo "selected";
												echo ">".$datasl['Fld_Stock_Location_Text']."</option>";
											}
					                        //Fin recuperation stock location
											?>
                                            </select>
                                        </div>
										</div>
										<div class="col-lg-3">
										<div class="form-group">
                                            <label>SHOW ON WEBSITE ????</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
										</div>
										<div class="col-lg-3">
										<div class="form-group">
                                            <label>Owner  ???</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
										</div>
							</div>	
						<!--Fin STOCK DETAILS-->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
				</div>
				
				<!--STATUS-->
				<div class="row">
					<div class="col-lg-12">
					STATUS
					</div>
				</div>
				<div class="row">
								
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>STATUS</label>
											
                                            <select class="form-control" name="Fld_Status_ID">
											<?php
											//recuperation STATUS
											// ** tbl_Status ** Fld_Status_ID  Fld_Status_Text
					                        $sqlsid="SELECT * from tbl_Status";
											
											$reqsid = mysql2_query($sqlsid);
											while($datasid = mysqli_fetch_array($reqsid)){
												echo "<option value='".$datasid['Fld_Status_ID']."'";
												if ($data2['Fld_Status_ID']==$datasid['Fld_Status_ID']) echo "selected";
												echo ">".$datasid['Fld_Status_Text']."</option>";
											}
					                        //Fin recuperation STATUS
											?>
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>BASED UPON</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>STATUS DATE</label>
                                            <input class="form-control" name="Fld_Status_Date" value="<?php echo $data2['Fld_Status_Date'];?>">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>ACCOUNTING VALUE</label>
                                            <input class="form-control" name="" value="<?php ?>">
                                        </div>
									</div>
								
							</div>	
				<!--Fin STATUS-->
				
				
            </div>
            </div>
            </div>
            </div>
            <!-- /.row -->
			
			<!--SUPPLIERS QUOTE-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            SUPPLIERS QUOTE <a href="add_suppliers_quote.php" style="color:white;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD A SQ</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
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
                                            <th>DELIVERY</th>
                                            <th>RELEASE</th>
                                            <th>SN</th>
                                            <th>TAG INFO</th>
                                            <th>TAG DATE</th>
                                            <th>Traced To</th>
                                            <th>SALES REMARKS</th>
										
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  
					//Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
					$sqlrfq2="SELECT * from tbl_RFQ_2 where Fld_Part_ID='".$data['Fld_Part_ID']."'";
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
                                            echo "<tr>
											<td><input type=\"radio\" name=\"suppliers_choice_id\" value='".$datarfq2['ID']."' onchange=\"quote_the_customer_sq('".$datarfq2['ID']."')\"></td>
											<td><a href='modif_suppliers_quote.php?ID=".$datarfq2['ID']."&part_id=".$_GET['part_id']."'>".$datarfq2['Fld_RFQ_ID']."</a></td>
											<td>".$datarn['Fld_Company_Name']."</td>
											<td>".$datals['Fld_Contact_Name']."</td>
											<td>".$datarfq2['Fld_Qty']."</td>
											<td>".$datact['Fld_Condition_Text']."</td>
											<td>".$datarfq2['Fld_Price']."</td>
											<td>".$datacid['Fld_Currency_Text']."</td>
											<td>".$datapt['Fld_Payment_Text']."</td>
											<td>".$datarfq2['Fld_Delivery']."</td>
											<td>".$dataRID['Fld_Release_Text']."</td>
											<td>".$datarfq2['lead_time']."</td>
											<td>".$datarfq2['Fld_Part_SN']."</td>
											<td>".$datatiid['Fld_Company_Name']."</td>
											<td>".$datarfq2['Fld_Tag_Date']."</td>
											<td>".$datatrac['Fld_Company_Name']."</td>
											<td>".$datarfq2['Fld_Remark']."</td></tr>";
					}
?>					
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--Fin SUPPLIERS QUOTE-->
			<!--QUOTATIONS-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            QUOTATIONS 
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            
                                            <th>RFQ ID</th>
                                            <th>Price</th>
                                            
										
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					//****tbl_RFQ_3**** ID  Fld_RFQ_ID  Fld_Quote_Date  Fld_Part_Id  Fld_Part_SN  Fld_Qty  Fld_Condition  Fld_Price  Fld_Price_Min  Fld_Price_Max  Fld_Currency_ID  Fld_Remark  Fld_Supply_Date  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  Fld_Linked_ID  Fld_Exch_Core_Value  Fld_Exch_Core_Value_Currency_ID  Fld_Exch_Cond  Fld_IsBeen_Chosen  Fld_Send_Mail  Fld_Exch_Core_RCVD  moq  lead_time  Fld_Priority_ID
					$sqlrfq3="SELECT * from tbl_RFQ_3 where Fld_Part_Id='".$data['Fld_Part_ID']."'";
					//echo $sqlrfq2;
					$reqrfq3 = mysql2_query($sqlrfq3);
					while($datarfq3 = mysqli_fetch_array($reqrfq3))
					{
											
                                            echo "<tr>
											
											<td>".$datarfq3['Fld_RFQ_ID']."</td>
											<td>".$datarfq3['Fld_Price']."</td>
											
											</tr>";
					}
?>					
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--Fin QUOTATIONS-->
			<div class="row">
									<div class="col-lg-8">
										<div class="form-group">
                                            <button type="button" class="btn btn-outline btn-primary btn-lg" onClick="document.location='email_broadcast.php?part_id=<?php echo $_GET['part_id'];?>&company_id=<?php echo $data["Fld_Part_MFG"];?>'">EMAIL BROADCAST</button>
											
                                        </div>
									</div>
			
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
<script src="../URBA/bower_components/jquery/dist/jquery.min.js"></script><!--Roy-->
    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });

    </script>
<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">-->
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="js/typeahead.js"></script>
    <style>
	<!--
        h1 {
            font-size: 20px;
            color: #111;
        }

        .content {
            width: 80%;
            margin: 0 auto;
            margin-top: 50px;
        }
		 .tt-hint,
        .companyid {
            border: 2px solid #CCCCCC;
            border-radius: 8px 8px 8px 8px;
            font-size: 24px;
            height: 45px;
            line-height: 30px;
            outline: medium none;
            padding: 8px 12px;
            width: 400px;
        }
-->
       
		.tt-hint,
        .companyid,.companyidforoem,.Fld_Tag_Info_ID,.Fld_Traceability_ID {
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
			$('input.companyidforoem').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
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
                   
            xhr.open("POST", "contactnamefromcompany.php?id="+companyidval, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
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


<!--Consulter et modifier Stock-->
	function addstock(id)
{
var blocstock=document.getElementById('blocstock');
//if(blocstock.style.display=='inline') blocstock.style.display='none';
//else{
blocstock.style.display='inline';

//document.getElementById("divstock").innerHTML='<div id="divstock" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "change_stock.php?Fld_Stock_ID="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_stock(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function up_donnee_stock(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divstock').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divstock').innerHTML+=resp3;
    document.getElementById('divstock').innerHTML+='</div>';
    }
}
<!--Fin Consulter et modifier Stock-->
<!--Fermer fenetre details stock-->
	function close_stock_details(id)
{
var blocstock=document.getElementById('blocstock');
if(blocstock.style.display=='inline') blocstock.style.display='none';
}
<!--Fin Fermer fenetre details stock-->
function majpnOLD(id){
 
    var selection = document.getElementById("Fld_Part_Nbr").value;
    var FldPartDesc = document.getElementById("Fld_Part_Desc").value;
    var altpn = document.getElementById("alt_pn").value;
    var FldPartMFG = document.getElementById("Fld_Part_MFG").value;
    var FldPartListPrice = document.getElementById("Fld_Part_List_Price").value;
    var FldACID = document.getElementById("Fld_AC_ID").value;
	var FldCurrencyID = document.getElementById("FldCurrencyID").value;
    $.get('majpnauto.php', { // lien de la page qui permet la suppression
                Fld_Part_ID:id,Fld_Part_Nbr:selection,Fld_Part_Desc:FldPartDesc,alt_pn:altpn,Fld_Part_List_Price:FldPartListPrice,Fld_Part_MFG:FldPartMFG,Fld_AC_ID:FldACID,FldCurrencyID:FldCurrencyID//// //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
/*	alert('select :'+selection);*/
					}  
					
function majpnstockOLD(id){
    var FldRemark = document.getElementById("Fld_Remark").value;
    $.get('majpnauto.php', { // lien de la page qui permet la suppression
                Fld_Part_ID:id,Fld_Remark:FldRemark //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
/*	alert('select :'+selection);*/
						
					  }
					
<!--************************************Function quote the customer from the stock ******************************-->
function quote_the_customer(id)
{
var blocquotecustomer=document.getElementById('blocquotecustomer');
//if(blocquotecustomer.style.display=='inline') blocquotecustomer.style.display='none';
//else{
blocquotecustomer.style.display='inline';

//document.getElementById("divquotecustomer").innerHTML='<div id="divquotecustomer" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "quote_the_customer.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { open_quote_customer(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function open_quote_customer(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divquotecustomer').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divquotecustomer').innerHTML+=resp3;
    document.getElementById('divquotecustomer').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
<!--End Function quote the customer from the stock-->

<!--************************************Function quote the customer from SUPPLIERS QUOTE ******************************-->
function quote_the_customer_sq(id)
{
var blocquotecustomersq=document.getElementById('blocquotecustomersq');

blocquotecustomersq.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "quote_the_customer_sq.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { open_quote_customer(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   
}
function open_quote_customer(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divquotecustomersq').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divquotecustomersq').innerHTML+=resp3;
    document.getElementById('divquotecustomersq').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
<!--End Function quote the customer from SUPPLIERS QUOTE-->

<!--**********************************************************************************************-->
<!--**********************************************************************************************-->

<!--Recuperation des infos rfq pour affichage dans fenetre gauche-->
function recup_info_rfq(id)
{
var blocrecuprfq=document.getElementById('blocrecuprfq');
//if(blocrecuprfq.style.display=='inline') blocrecuprfq.style.display='none';
//else{
blocrecuprfq.style.display='inline';

//document.getElementById("divrecuprfq").innerHTML='<div id="divrecuprfq" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "recup_info_rfq.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { recup_rfq(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function recup_rfq(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divrecuprfq').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divrecuprfq').innerHTML+=resp3;
    document.getElementById('divrecuprfq').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
<!--Fin Recuperation des infos rfq pour affichage dans fenetre gauche-->

<!--**********************************************************************************************-->
<!--**********************************************************************************************-->

<!--Affichage du rfq pour quotation-->
function view_rfq(id)
{
var blocquotecustomer=document.getElementById('blocquotecustomer');
//if(blocquotecustomer.style.display=='inline') blocquotecustomer.style.display='none';
//else{
blocquotecustomer.style.display='inline';

//document.getElementById("divquotecustomer").innerHTML='<div id="divquotecustomer" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "rfq_for_quote.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { open_rfq(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function open_rfq(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divquotecustomer').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divquotecustomer').innerHTML+=resp3;
    document.getElementById('divquotecustomer').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
<!--Fin Affichage du rfq pour quotation-->

<!--**********************************************************************************************-->
<!--**********************************************************************************************-->

<!--Griser/degriser champs input pn et description-->
function disable() {
    document.getElementById("Fld_Part_Nbr").disabled = true;
    document.getElementById("Fld_Part_Desc").disabled = true;
}
function enable() {
    document.getElementById("Fld_Part_Nbr").disabled = false;
    document.getElementById("Fld_Part_Desc").disabled = false;
}
<!--Fin Griser/degriser champs input pn et description-->
    </script>
	
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>
