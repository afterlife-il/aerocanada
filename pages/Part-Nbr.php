<?php
//Part-Nbr.php
session_start();
include_once "conf.php";
if($_SESSION['conectroy']=="parfait"){

// ========================================================
// RESOLUTION UNIQUE DU PART_ID (pn ou part_id)
// ========================================================
$part_id = 0;

// 1) Si on a pn => on récupère l'ID
if (!empty($_GET['pn'])) {
    $pn = mysqli_real_escape_string($conn, $_GET['pn']);
    $sql = "SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr = '$pn' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $part_id = (int)$row['Fld_Part_ID'];
    } else {
        die("Erreur : P/N '$pn' introuvable.");
    }
}

// 2) Sinon si on a part_id (ancienne URL) => on redirige vers pn
elseif (!empty($_GET['part_id'])) {
    $part_id = (int)$_GET['part_id'];

    $sql = "SELECT Fld_Part_Nbr FROM tbl_Parts WHERE Fld_Part_ID = $part_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $pn = urlencode($row['Fld_Part_Nbr']);
        header("Location: Part-Nbr.php?pn=$pn");
        exit();
    } else {
        die("Erreur : Part ID introuvable.");
    }
}

else {
    die("Erreur : Aucun identifiant (pn ou part_id) fourni.");
}
if (empty($_GET['part_id']) && !empty($_GET['pn'])) {
    $pn = addslashes($_GET['pn']);   // pn tel qu'affiché en haut (ex: 1712507C)

    $sqlPartId = "
        SELECT Fld_Part_ID
        FROM tbl_Parts
        WHERE Fld_Part_Nbr = '".$pn."'
        LIMIT 1
    ";
    $resPartId = mysql2_query($sqlPartId);

    if ($resPartId && mysqli_num_rows($resPartId) > 0) {
        $rowPartId       = mysqli_fetch_assoc($resPartId);
        $_GET['part_id'] = $rowPartId['Fld_Part_ID'];
    } else {
        // Sécurité : si rien trouvé, on force 0 (aucune RFQ, mais pas d'erreur SQL)
        $_GET['part_id'] = 0;
    }
}

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
    <script
  src="https://code.jquery.com/jquery-3.7.0.min.js"
  integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
  crossorigin="anonymous"></script>
</head>

<body>

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <!--
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  -->
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
		if($_SESSION['leftmenu']=='open') include "left_menu.php";
	   ?>
            <!-- /.navbar-static-side -->
        </nav>
         <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <!--<h1 class="page-header">P/N</h1>-->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--<div  class="row" style="position: fixed;z-index: 2;font-size: 11px;width:1800px;height:200px;margin-right: -15px;margin-left: -15px;" >-->
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
					$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$part_id."' and status='Available'";
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
									<div class="col-lg-2">
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
											//recuperation des model d'avion
											// **tbl_Aircraft** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					                        $sqlair="SELECT * FROM tbl_Aircraft order by Fld_AC_Model";
											
											$reqair = mysql2_query($sqlair);
											while($dataair = mysqli_fetch_array($reqair)){
												echo "<option value='".$dataair['Fld_AC_ID']."'";
												if ($data["Fld_AC_ID"]==$dataair['Fld_AC_ID']) echo "selected";
												echo ">".$dataair ['Fld_AC_Model']."</option>";
											}
					                        //Fin recuperation des model d'avion
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
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
                                            <label>ATA CHAPTER</label>
                                            <input class="form-control" name="ata_chapter" id="ata_chapter" value="<?php echo $data['ata_chapter'];?>" >
                                        </div>
									</div>	
									
							</div>	
							<div class="row">
									<div class="col-lg-1">
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
										
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>OEM LEAD TIME</label>
                                            <input class="form-control" name="oem_lead_time" id="oem_lead_time" value="<?php echo $data['oem_lead_time'];?>" >
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>LP DATE</label>
                                            <input class="form-control" name="Fld_Part_LP_Date" id="Fld_Part_LP_Date" value="<?php echo $data['Fld_Part_LP_Date'];?>" >
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>LIST PRICE</label>
                                            <input class="form-control" name="Fld_Part_List_Price" id="Fld_Part_List_Price" value="<?php echo $data['Fld_Part_List_Price'];?>" >
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
                                            <label>CORE VALUE</label>
                                            <input class="form-control" name="core_value" id="core_value" value="<?php echo $data['core_value'];?>" >
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>CORE VALUE $/€</label>
                                            <select class="form-control" name="id_currency_core_value" id="id_currency_core_value" >
											<?php
											//recuperation des currency
											//tbl_Currency---- Fld_Currency_ID Fld_Currency_Text
					                        $sqlcid="SELECT * FROM tbl_Currency";
											
											$reqcidcv = mysql2_query($sqlcid);
											while($datacidcv = mysqli_fetch_array($reqcidcv)){
												echo "<option value='".$datacidcv['Fld_Currency_ID']."'";
												if ($data["id_currency_core_value"]==$datacidcv['Fld_Currency_ID']) echo "selected";
												echo ">".$datacidcv['Fld_Currency_Text']."</option>";
											}
					                        //End recuperation of the currency
											?>
                                                
                                            </select>
                                        </div>
									</div>
									
									
									
									
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>REMARK</label>
											<textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark" ><?php echo $data['Fld_Remark'];?></textarea>
                                        </div>
										
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>WANTED</label>
											<div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="wanted" value="1" <?php if($data['wanted']=='1') echo "checked";?>>
                                                </label>
                                            </div>
                                        </div>
										
									</div>
									<div class="col-lg-1">
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
			
			<!--*************************************************************************************************************-->
			<!--***********************************************DOCS ATTACHMENT***********************************************-->
			<!--*************************************************************************************************************-->
			
				<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href='javascript:docattachopen();' style="top: 4px;color:#fff;text-decoration: none;"><i style="top: 4px;color:#fff;" class="fa  fa-plus-square"></i> DOCS ATTACHMENT</a>
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
						<!--Verif si il y a des produits en stock pour ce pn-->
						<?php
							//affichage info docs part
					        $sqldapn="SELECT * FROM tbl_docs_attachment_pn where pn_id='".$part_id."'";
							
							$reqdapn = mysql2_query($sqldapn);
							$numrows_docattach = mysqli_num_rows($reqdapn);
						?>
						<!--Fin Verif si il y a des produits en stock pour ce pn-->
						
						<!--************************************************-->
                        <div class="panel-body" id="docattach" style="padding-bottom: 1px;<?php if ($numrows_docattach=='0'){ ?>display:none;<?php } ?>">                           
						
			<form action="add_docs_pn.php" method="post" enctype="multipart/form-data">
			 <!--  **tbl_docs_attachment_pn** id_docs_attachment_pn   name docs_name   pn    pn_id  -->
			<input type="hidden" name="pn_id" value="<?php echo $data['Fld_Part_ID'];?>">
			<input type="hidden" name="pn" value="<?php echo $data["Fld_Part_Nbr"];?>">
							<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>DOCS NAME</label>
											<input class="form-control" name="docs_name" id="docs_name">

                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>DOCS</label><br><br>
										<input type="file" name="docsattachment" id="docsattachment">

                                        </div>
									</div>	
									<div class="col-lg-1">
										<div class="form-group">
                                            <label></label><br><br>
										<button type="submit" class="btn btn-default">SUBMIT</button>

                                        </div>
									</div>
									
									
							</div>	
						</form>
						<table>
						<?php
											while($datadapn = mysqli_fetch_array($reqdapn)){
												echo "<tr><td><a href='../docsattachment/".$datadapn['docs_name']."' target='_blank'>".$datadapn['name']."</a></td><td><a href='del_doc_pn.php?id_docs_attachment_pn=".$datadapn['id_docs_attachment_pn']."&part_id=".$part_id."'  onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td></tr>";
											}
						?>
						</table>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--*************************************************************************************************************-->
			<!--***********************************************END DOCS ATTACHMENT*******************************************-->
			<!--*************************************************************************************************************-->
			

		<!--***********************************************************************************************-->
		<!--***********************************************************************************************-->
		<!--***********************************************************************************************-->
		<!--RFQ + QUOTE THE CUSTOMER-->
		<div class="row">
			<!--Request for quotation-->
                <div class="col-lg-12">
                    <div class="panel panel-danger">
  <div class="panel-heading" style="background:#A7142A;color:#fff;font-weight:bold;">
    <a data-toggle="collapse" href="#rfq_collapse" style="color:#fff;">RFQ / QUOTE</a>
  </div>
  <div id="rfq_collapse" class="panel-collapse collapse in">
    <div class="panel-body">
                        <!-- /.panel-heading -->
						<form method="post" name="Form1">
						<input type="hidden" name="Fld_Customer_ID" id="Fld_Customer_ID">
                        <input type="hidden" name="Fld_Part_ID_hidden" id="Fld_Part_ID_hidden">
                        <input type="hidden" name="Fld_Part_ID" value="<?php echo $data['Fld_Part_ID'];?>">
						<input type="hidden" name="Fld_Part_Nbr" value="<?php echo $data["Fld_Part_Nbr"];?>">
						<input type="hidden" name="Fld_Part_Desc" value="<?php echo $data["Fld_Part_Desc"];?>">
						<input type="hidden" name="part_id" value="<?php echo $part_id;?>">
						<input type="hidden" name="id_utilisateur" value="<?php echo $_SESSION['id_utilisateur'];?>">
						<input type="hidden" name="actonrfq" value="addrfqft">
                        <div class="panel-body" id='blocrecuprfqquote'>  
						  <div id='divrecuprfqquote'>
                          <div class="form-group has-warning">
    <label>RFQ ID</label>
    <?php
    // Gestion RFQ_ID : on garde exactement la même logique qu’avant
    if (isset($_GET['RFQ_ID']) && $_GET['RFQ_ID'] !== '') {
        $RFQ_ID = $_GET['RFQ_ID'];
    } elseif (!empty($_POST['RFQ_ID'])) {
        $RFQ_ID = $_POST['RFQ_ID'];
    } elseif (!empty($_POST['Fld_RFQ_ID'])) {
        $RFQ_ID = $_POST['Fld_RFQ_ID'];
    } else {
        // Nouvelle RFQ : génération automatique
        $RFQ_ID = date("Y-m-d-His");
    }
    $rfq_id_safe = htmlspecialchars($RFQ_ID, ENT_QUOTES, 'UTF-8');
    ?>

    <!-- Champ visible NON modifiable (readonly, sans name) -->
    <input class="form-control"
           value="<?php echo $rfq_id_safe; ?>"
           readonly="readonly"
           style="background-color:#eee; cursor:not-allowed;">

    <!-- Champs cachés envoyés au serveur (comme avant) -->
    <input type="hidden" name="RFQ_ID"
           value="<?php echo $rfq_id_safe; ?>">
    <input type="hidden" name="Fld_RFQ_ID"
           value="<?php echo $rfq_id_safe; ?>">
</div>

								
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>DATE</label>
                                            <input class="form-control" name="RFQ_DATE" value="<?php echo date("d/m/Y");?>">
                                    </div>
								</div>
								<div class="col-lg-8">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>CUSTOMER'S NAME</label><br>
											<input type="text" name="companyid" id="companyid" class="companyid" placeholder="Please Enter company" onclick="javascript:majtareaback();">
											<a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
                                        </div>
								</div>
								<div class="col-lg-2" id='bloccontactname'>
										<div class="form-group has-warning" id='divcontactname'>
                                            <label>CONTACT NAME</label>
											<select class="form-control" name="id_company_contact" onclick="javascript:majtarea();">
											<option>CHOOSE CONTACT</option>

                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>RFQ TYPE</label>
											<select class="form-control" name="Fld_RFQ_Type_ID">
											<option></option>
											<?php
											//recuperation RFQ Type 
											// ** tbl_RFQ_Type ** Fld_RFQ_Type_ID  Fld_RFQ_Type_Text
					                        $sqlrfqt="SELECT * FROM tbl_RFQ_Type";
											
											$reqrfqt = mysql2_query($sqlrfqt);
											while($datarfqt = mysqli_fetch_array($reqrfqt)){
												echo "<option value='".$datarfqt['Fld_RFQ_Type_ID']."'";
												if ($datarfqt['Fld_RFQ_Type_ID']=='2') echo " selected";
												echo ">".$datarfqt['Fld_RFQ_Type_Text']."</option>";
											}
					                        //Fin recuperation RFQ Type
											?>
                                                
                                            </select>
                                        </div>
								</div>
								<div class="col-lg-2">
										<div class="form-group has-warning">
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
								<div class="col-lg-2">
										<div class="form-group has-warning">
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
								<div class="col-lg-2">
										<div class="form-group has-warning">
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
							</div>
							<div class="row">
								<div class="col-lg-4">
										<div class="form-group">
                                            <label style="color:#a7142a;">INTERNAL REMARK</label>
											<!--!!!!!!! Surtout ne pas mettre les informations de ramarque de la table stock par ce que ce sont des infos interne de la boite qui ne doivent pas arriver au client!!!!!!!-->
                                            <textarea class="form-control" rows="3" name="Fld_Remark_rfq" id="Fld_Remark_rfq" style="background-color:#DDDDDD;color:#a7142a;border-color: #a7142a;
											box-shadow: 0 0 10px #a7142a;"></textarea>
                                        </div>
                                </div>
							</div>
							<div class="row">
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>PN</label>
                                            <input class="form-control" name="pn_rfq" id="pn_rfq" value="<?php echo $data["Fld_Part_Nbr"];?>">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description_rfq" id="description_rfq" value="<?php echo $data['Fld_Part_Desc'];?>">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty" id="Fld_Qty" value="1">
                                        </div>
                                    </div>
									<div class="col-lg-2">
										<div class="form-group has-warning">
                                            <label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php
											//recuperation condition 
											// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
					                        $sqlc="SELECT * FROM tbl_Condition order by Fld_Condition_Text";
											
											$reqc = mysql2_query($sqlc);
											while($datac = mysqli_fetch_array($reqc)){
												echo "<option value='".$datac['Fld_Condition_ID']."'";
												if ($datac['Fld_Condition_ID']=='1') echo " selected";
												echo ">".$datac ['Fld_Condition_Text']."</option>";
											}
					                        //Fin recuperation condition 
											?>
                                                
                                            </select>
                                        </div>
                                    </div>
							</div>
							<hr>
							<!--=============================================-->
							<!--les champs ci dessous concerne les quotations-->
							<div class="row">
									
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<option value="">SELECT THE RELEASE</option>
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
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><br>
                                            <input type="text" name="Fld_Tag_Info_ID" id="Fld_Tag_Info_ID" class="Fld_Tag_Info_ID" placeholder="Please Enter company" style="width: 335px;">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG DATE</label>
                                            <input class="form-control" name="Fld_Tag_Date" id="Fld_Tag_Date" >
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>Traced To</label><br>
											<input type="text" name="Fld_Traceability_ID" id="Fld_Traceability_ID" class="Fld_Traceability_ID" placeholder="Please Enter company" >
                                        </div>
									</div>
						   </div>
						   <div class="row">
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>STOCK LOC / LEAD TIME</label>
                                            <input class="form-control" name="lead_time" id="lead_time">
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price" id="Fld_Price">
                                        </div>
									</div>
									<div class="col-lg-2">
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
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>COMMENTS FOR THE CLIENT</label>
                                            <textarea class="form-control" rows="3" name="Fld_Remark" id="Fld_Remark"></textarea>
                                        </div>
                                    </div>
							</div>	
							<div class="row">
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>SN</label>
                                            <input class="form-control" name="Fld_Part_SN" id="Fld_Part_SN">
                                        </div>
                                    </div>
									
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>MOQ (Minimum Qty)</label>
                                            <input class="form-control" name="moq" id="moq">
                                        </div>
                                    </div>
									
						   </div>
						<input type="hidden" name="quote_type" value="suppliers_quote">
						<input type="hidden" name="part_id" value="<?php echo $part_id;?>">
					
						<div  id='blocquotecustomer'>
                           <div class="row" id='divquotecustomer'>
								<div class="col-lg-12">
										<div class="form-group" style="text-align:center;">
                                        </div>
								</div>
								<input type="hidden" name="qtc" value="no">
						   </div>
						</div>
						<input type="hidden" class="form-control" name="quotethecustomer" value="">
						<div class="row">
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="ADD RFQ / QUOTATION" name=button1 onclick="return OnButton1();">
										</div>
								</div>	
								<div class="col-lg-4">
										<div class="form-group" align="right">
										<INPUT type="button" value="SEND QUOTATION" name=button2 onclick="return OnButton2();">
										</div>
								</div>									
						</div>	
                      </div>
                    </div>
                    <!-- /.panel-body -->
						</form>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
		</div>
			<!-- /.row -->
			<!--END RFQ + QUOTE THE CUSTOMER-->
			<!--***********************************************************************************************-->
			<!--***********************************************************************************************-->
			<!--***********************************************************************************************-->
				<!--RFQ'S-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            RFQ / QUOTATION  <a href="add_rfq.php" style="color:white;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD MULTIPLE RFQ</a> 
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
<!--Verif si il y a des RFQ pour ce pn-->
<?php
// ID interne de la pièce (vient maintenant soit de l'URL, soit de la fiche, soit de notre adaptateur pn→part_id)
$partIdForRfq = 0;

// priorité à la fiche $data si elle est définie
if (!empty($data['Fld_Part_ID'])) {
    $partIdForRfq = (int)$data['Fld_Part_ID'];
} elseif (!empty($_GET['part_id'])) {
    $partIdForRfq = (int)$_GET['part_id'];
}

// Si on n'a rien -> requête impossible (0 RFQ mais pas d'erreur)
if ($partIdForRfq === 0) {
    $sqlrfq1 = "
        SELECT r.*, c.Fld_Company_Name
        FROM tbl_RFQ_1 AS r
        JOIN tb_company AS c ON r.Fld_Customer_ID = c.Fld_Company_ID
        WHERE 1 = 0
    ";
} else {
    // Requête historique : tbl_RFQ_1 pour CE Part_ID uniquement
    $sqlrfq1 = "
        SELECT r.*, c.Fld_Company_Name
        FROM tbl_RFQ_1 AS r
        JOIN tb_company AS c ON r.Fld_Customer_ID = c.Fld_Company_ID
        WHERE r.Fld_Part_ID = '".$partIdForRfq."'
        ORDER BY r.ID DESC
    ";
}

$reqrfq1     = mysql2_query($sqlrfq1);
$numrows_rfq = $reqrfq1 ? mysqli_num_rows($reqrfq1) : 0;

// Petit debug discret dans le HTML (tu peux l'enlever après)
//echo "<!-- RFQ_DEBUG part_id=".(int)$partIdForRfq." rows=".(int)$numrows_rfq." -->";

// Petit debug visible (temporaire) - SuperAdmin uniquement
if (!empty($_SESSION['statut']) && $_SESSION['statut'] === 'SuperAdmin') {
    echo "<div style='font-size:10px;color:#999;margin:3px 0;'>
            RFQ_DEBUG part_id=".(int)$partIdForRfq." rows=".(int)$numrows_rfq."
          </div>";
}
?>
<!--Fin Verif si il y a des RFQ pour ce pn-->

						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_rfq=='0'){ ?>style="display:none;"<?php } ?>>
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
										    <th></th>
                                            <th>RFQ ID</th>
											<th>CUSTOMER</th>
                                            <th>QTY</th>
											<th>CONDITION</th>
											<th>PRICE</th>
											<th>$/€</th>
											<th>L/T</th>
                                            <th>RELEASE</th>
                                            <th>TAG INFO</th>
                                            <th>TAG DATE</th>
                                            <th>CORE VALUE</th>
                                            <th>REMARKS</th>
                                            <th></th>
										
                                        </tr>
                                    </thead>
                                                                        <tbody>
<?php
if ($numrows_rfq > 0) {

    while ($datarfq1 = mysqli_fetch_assoc($reqrfq1)) {

        // ---------------------------------------------------------------------------------
        // 1) COULEUR + PO (tbl_RFQ)
        // ---------------------------------------------------------------------------------
        $prioritescss = "";
        $ponumber     = "";

        $sqlPo    = "SELECT Fld_PO FROM tbl_RFQ 
                     WHERE Fld_RFQ_ID='".$datarfq1['Fld_RFQ_ID']."' 
                       AND Fld_PO!=''";
        $resultPo = mysql2_query($sqlPo);

        if ($resultPo && mysqli_num_rows($resultPo) > 0) {
            $datavnumpo  = mysqli_fetch_assoc($resultPo);
            $prioritescss = "background-color:#2E64FE;color:#FFFFFF;"; // bleu = PO
            $ponumber     = "<a href='po_validation.php?Fld_RFQ_ID=".$datarfq1['Fld_RFQ_ID']."' style='color:#FFFFFF;'>PO# : ".$datavnumpo['Fld_PO']."</a>";
        } else {
            // pas encore de PO : couleur selon priorité
            if ($datarfq1['Fld_Priority_ID'] == '1') {
                $prioritescss = "background-color:#01DF01;"; // Routine
            } elseif ($datarfq1['Fld_Priority_ID'] == '2') {
                $prioritescss = "background-color:#FF0000;"; // AOG
            } else {
                $prioritescss = "background-color:#FF8000;"; // Critical
            }
            $ponumber = "<a href='po_validation.php?Fld_RFQ_ID=".$datarfq1['Fld_RFQ_ID']."'>PO VALIDATION</a>";
        }

        // ---------------------------------------------------------------------------------
        // 2) INFOS DE QUOTATION (tbl_RFQ_3) – une seule ligne par RFQ_ID
        // ---------------------------------------------------------------------------------
        $datarfq3 = null;
        $sqlrfq3  = "SELECT * 
                     FROM tbl_RFQ_3 
                     WHERE Fld_RFQ_ID='".$datarfq1['Fld_RFQ_ID']."'
                       AND Fld_Part_Id=".(int)$partIdForRfq."
                       AND id_tbl_rfq1=".(int)$datarfq1['ID']."
                     ORDER BY ID DESC
                     LIMIT 1";
        $reqrfq3  = mysql2_query($sqlrfq3);
        if ($reqrfq3 && mysqli_num_rows($reqrfq3) > 0) {
            $datarfq3 = mysqli_fetch_assoc($reqrfq3);
        }

        // Valeurs par défaut (au cas où pas de RFQ_3)
        $price       = $datarfq3 ? $datarfq3['Fld_Price']           : '';
        $lead_time   = $datarfq3 ? $datarfq3['lead_time']           : '';
        $tag_date    = $datarfq3 ? $datarfq3['Fld_Tag_Date']        : '';
        $core_value  = $datarfq3 ? $datarfq3['Fld_Exch_Core_Value'] : '';
        $remark      = $datarfq3 ? $datarfq3['Fld_Remark']          : '';
        $rfq3_id     = $datarfq3 ? $datarfq3['ID']                  : 0;
        $release_id  = $datarfq3 ? (int)$datarfq3['Fld_Release_ID'] : 0;
        $currency_id = $datarfq3 ? (int)$datarfq3['Fld_Currency_ID']: 0;
        $tag_info_id = $datarfq3 ? (int)$datarfq3['Fld_Tag_Info_ID']: 0;

        // ---------------------------------------------------------------------------------
        // 3) CONDITION TEXT (tbl_Condition)
        // ---------------------------------------------------------------------------------
        $condition_text = '';
        if (!empty($datarfq1['Fld_Condition_ID'])) {
            $sqlct = "SELECT Fld_Condition_Text 
                      FROM tbl_Condition 
                      WHERE Fld_Condition_ID=".(int)$datarfq1['Fld_Condition_ID'];
            $reqct = mysql2_query($sqlct);
            if ($reqct && mysqli_num_rows($reqct) > 0) {
                $datact = mysqli_fetch_assoc($reqct);
                $condition_text = $datact['Fld_Condition_Text'];
            }
        }

        // ---------------------------------------------------------------------------------
        // 4) CURRENCY TEXT (tbl_Currency)
        // ---------------------------------------------------------------------------------
        $currency_text = '';
        if ($currency_id > 0) {
            $sqlcid = "SELECT Fld_Currency_Text 
                       FROM tbl_Currency 
                       WHERE Fld_Currency_ID=".$currency_id;
            $reqcid = mysql2_query($sqlcid);
            if ($reqcid && mysqli_num_rows($reqcid) > 0) {
                $datacid = mysqli_fetch_assoc($reqcid);
                $currency_text = $datacid['Fld_Currency_Text'];
            }
        }

        // ---------------------------------------------------------------------------------
        // 5) RELEASE TEXT (tbl_Release)
        // ---------------------------------------------------------------------------------
        $release_text = '';
        if ($release_id > 0) {
            $sqlRID = "SELECT Fld_Release_Text 
                       FROM tbl_Release 
                       WHERE Fld_Release_ID=".$release_id;
            $reqRID = mysql2_query($sqlRID);
            if ($reqRID && mysqli_num_rows($reqRID) > 0) {
                $dataRID = mysqli_fetch_assoc($reqRID);
                $release_text = $dataRID['Fld_Release_Text'];
            }
        }

        // ---------------------------------------------------------------------------------
        // 6) TAG INFO COMPANY (tb_company)
        // ---------------------------------------------------------------------------------
        $tag_company = '';
        if ($tag_info_id > 0) {
            $sqltiid = "SELECT Fld_Company_Name 
                        FROM tb_company 
                        WHERE Fld_Company_ID=".$tag_info_id;
            $reqtiid = mysql2_query($sqltiid);
            if ($reqtiid && mysqli_num_rows($reqtiid) > 0) {
                $datatiid = mysqli_fetch_assoc($reqtiid);
                $tag_company = $datatiid['Fld_Company_Name'];
            }
        }

        // ---------------------------------------------------------------------------------
        // 7) AFFICHAGE DE LA LIGNE
        // ---------------------------------------------------------------------------------
        echo "<tr>
            <td style='border:1px solid ".$prioritescss.";'>
                <a href=\"".($rfq3_id > 0 ? "modif_quotations.php?ID=".$rfq3_id."&mode=clean" : "javascript:void(0);")."\"
                   ".($rfq3_id > 0 ? "" : "data-href=\"getContent.php?id=".$datarfq1['ID']."\" class=\"openPopup\"").">
                    <i class=\"fa fa-plane\"></i>
                </a>
                ".($rfq3_id > 0 ? "<br><a href=\"download_quote_pdf.php?ID=".$rfq3_id."\" target=\"_blank\" title=\"Download Quote PDF\"><i class=\"fa fa-file-pdf-o\"></i></a> <a href=\"create_po_from_quote.php?quote_id=".$rfq3_id."\" title=\"Create PO\"><i class=\"fa fa-shopping-cart\"></i></a>" : "")."
            </td>

            <td style='".$prioritescss."text-align:center;'>
                <b>".$datarfq1['Fld_RFQ_ID']."</b><br>".$ponumber."
            </td>

            <td>".$datarfq1['Fld_Company_Name']."</td>
            <td>".$datarfq1['Fld_Qty']."</td>
            <td>".$condition_text."</td>
            <td>".$price."</td>
            <td>".$currency_text."</td>
            <td>".$lead_time."</td>
            <td>".$release_text."</td>
            <td>".$tag_company."</td>
            <td>".$tag_date."</td>
            <td>".$core_value."</td>
            <td>".$remark."</td>
            <td>";

        if (!empty($_SESSION['statut']) && $_SESSION['statut'] == "SuperAdmin") {
            echo "<a href='del_rfq.php?ID=".$datarfq1['ID'].
                 "&part_id=".$partIdForRfq.
                 "&idrfq3=".$rfq3_id."' 
                 onClick=\"return(confirm('Are you sure ?'));\">
                    <img src='images/bin-blue-full-icon.png' border='0' width='27'>
                  </a>";
        }

        echo "</td></tr>";
    }

} else {
    // aucune RFQ
    ?>
    <tr>
        <td colspan="14" style="text-align:center;color:#999;">
            No RFQ found for this Part.
        </td>
    </tr>
    <?php
}
?>
                                    </tbody>

                                </table>
								  
                            </div>
							<span style="color:#01DF01">&#11044; Routine</span> <span style="color:#FF8000">&#11044; Critical</span> <span style="color:#FF0000">&#11044; AOG</span> <span style="color:#2E64FE">&#11044; PO</span>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			<!--END RFQ'S-->
			
			
			<!--*******************************************************************************************-->
			<!--*******************************************************************************************-->
			<!--STOCK select to quote-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            STOCK <a href="ajout_stock.php" style="color:white;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD STOCK</a>
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
						<!--Verif si il y a des produits en stock pour ce pn-->
						<?php
							$sqlst="SELECT * from tbl_Stock where Fld_Part_ID='".$data['Fld_Part_ID']."'";
							//echo $sqlst;
							$reqst = mysql2_query($sqlst);
							$numrows_stock = mysqli_num_rows($reqst);
						?>
						<!--Fin Verif si il y a des produits en stock pour ce pn-->
						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_stock==0 ){ ?>style="display:none;"<?php } ?>>
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
					
						$aciStockPayload = htmlspecialchars(json_encode(array(
							'id' => $datast['Fld_Stock_ID'],
							'qty' => $datast['Fld_Qty'],
							'condition_id' => $datast['Fld_Condition_ID'],
							'price' => $datast['Fld_Part_Price'],
							'currency_id' => $datast['Fld_Price_Currency_ID'],
							'release_id' => $datast['Fld_Release_ID'],
							'tag_info' => trim($datast['Fld_Tag_Info_ID'] . ',' . $datacn['Fld_Company_Name'], ','),
							'tag_date' => $datast['Fld_Tag_Date'],
							'traceability' => $datast['Fld_Traceability_ID'],
							'lead_time' => $datasl['Fld_Stock_Location_Text'],
							'remark' => $datast['Fld_Sales_Remark']
						)), ENT_QUOTES, 'UTF-8');

	                    echo "<tr>
					  <td>
					    <input type=\"radio\" name=\"stock_choice\" value=\"".$datast['Fld_Stock_ID']."\"
					           onchange=\"quote_the_customer_aci(this.getAttribute('data-stock'))\" data-stock=\"".$aciStockPayload."\">
					  </td>

					  <td><a href='javascript:addstock(".$datast['Fld_Stock_ID'].")'>".$datast['Fld_Stock_ID']."</a>
					  </td>

					  <td>".$datast['Fld_BAX_PO_Nbr']."
					  </td>
					  <td>".$datac['Fld_Condition_Text']."
					  </td>
					  <td>".$datast['Fld_Part_SN']."
					  </td>
					  <td>".$datast['Fld_Part_Price']."
					  </td>
					  <td>".$datacn['Fld_Company_Name']."
					  </td>
					  <td>".$datast['Fld_Tag_Date']."</td>
					  <td>".$datast['Fld_Traceability_ID']."
					  </td>
					  <td>".$datasl['Fld_Stock_Location_Text']."
					  </td>
					  <td>".$datast['Fld_Sales_Remark']."</td>
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
			<!--Fin STOCK select to quote-->
			<!--*******************************************************************************************-->
			<!--*******************************************************************************************-->
			  
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
					$sql2="SELECT * from tbl_Stock where Fld_Part_ID='".$data['Fld_Part_ID']."' ORDER BY Fld_Stock_ID DESC";
					
					
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
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $part_id;?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>ACI 770 PO#</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $part_id;?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>Supplier order date</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $part_id;?>">
                                        </div>
								</div>
								<div class="col-lg-3">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Part_ID" value="<?php echo $part_id;?>">
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
			
			<!--*******************************************************************************************-->
			<!--*******************************************************************************************-->
			<!--EXTERNAL STOCK select to quote-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            EXTERNAL STOCK 
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
						<!--Verif si il y a des produits en stock pour ce pn-->
						
						<?php
						/*****tbl_Stock_external*************  Fld_Stock_externe_ID  Fld_Part_ID  Fld_Part_SN  Fld_Supplier_ID  Fld_Entry_Date  Fld_Part_Price  Fld_Price_Currency_ID  Fld_BAX_PO_Nbr  Fld_Supplier_order_Date  Fld_Supplier_Payment_Date  Fld_Qty  Fld_Condition_ID  Fld_Release_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Traceability_ID  Fld_Warehouse_Location  Fld_Physical_Stock  Fld_Owner_ID  Fld_Stock_Location_ID  Fld_Status_ID  Fld_Status_Ind  Fld_Status_Date  Fld_Stock_Remark  Fld_Shelf_Life_Limit  Fld_Valeur_Comptable  Fld_Valeur_Comptable_currency_Id  Fld_Sales_Remark  Fld_External_Location  Fld_Sales_Remark_ID  Fld_Warehouse_Location_ID  Fld_OriginalUnit_Stock_ID  Fld_Min_Qty  Fld_Publish  status  Fld_AC_ID  Fld_Company_ID
*/
							$sqlstex="SELECT * from tbl_Stock_external where Fld_Part_ID='".$data['Fld_Part_ID']."'";
							//echo $sqlst;
							$reqstex = mysql2_query($sqlstex);
							$numrows_stex= mysqli_num_rows($reqstex);
						?>
						<!--Fin Verif si il y a des produits en stock pour ce pn-->
						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_stex=='0'){ ?>style="display:none;"<?php } ?>>
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>ID</th>
                                            <th>PART NUMBER</th>
                                            <th>DESCRIPTION</th>
											<th>AIRCRAFT</th>
											<th>QTY</th>
											<th>CONDITION</th>
											<th>COMPANY</th>
											<th>ENTRY DATE</th>
											<th>COMMENTS</th>
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					while($datastex = mysqli_fetch_array($reqstex))
					{
					//recuperation condition 
					// ** tbl_Condition ** Fld_Condition_ID  Fld_Condition_Text
						$externalConditionId = (int)$datastex['Fld_Condition_ID'];
						$datac = array('Fld_Condition_Text' => '');
						if ($externalConditionId > 0) {
							$sqlc="SELECT * FROM tbl_Condition where Fld_Condition_ID=".$externalConditionId;
							$reqc = mysql2_query($sqlc);
							$datac = mysqli_fetch_array($reqc);
						}
					// echo $datac['Fld_Condition_Text'];
					//Fin recuperation condition 

					//recuperation stock location
					// ** tbl_Stock_Location ** Fld_Stock_Location_ID  Fld_Stock_Location_Text
						$externalLocationId = (int)$datastex['Fld_Stock_Location_ID'];
						$datasl = array('Fld_Stock_Location_Text' => '');
						if ($externalLocationId > 0) {
							$sqlsl="SELECT * from tbl_Stock_Location where Fld_Stock_Location_ID=".$externalLocationId;
							$reqsl = mysql2_query($sqlsl);
							$datasl = mysqli_fetch_array($reqsl);
						}
					// echo $datasl['Fld_Stock_Location_Text'];
					//Fin recuperation stock location
					
					//recuperation du nom de la compagnie
						$externalCompanyId = (int)$datastex['Fld_Company_ID'];
						$datacn = array('Fld_Company_Name' => '');
						if ($externalCompanyId > 0) {
							$sqlcomn="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID=".$externalCompanyId;
							$reqcomn = mysql2_query($sqlcomn);
							$datacn = mysqli_fetch_array($reqcomn);
						}
					//Fin recuperation du nom de la compagnie
					
					//recuperation du nom du aircraft	
						if (!empty($datastex["Fld_AC_ID"])){
					// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
						$sqlacse="SELECT Fld_AC_Model FROM tbl_Aircraft where Fld_AC_ID=".(int)$datastex["Fld_AC_ID"];
					
					$reqacse=mysql2_query($sqlacse);
					$dataacse = mysqli_fetch_array($reqacse);
					$Aircraft_modelse=$dataacse['Fld_AC_Model'];
					}
					else $Aircraft_modelse="";
					//Fin recuperation du nom du aircraft
					
					
											$externalStockPayload = htmlspecialchars(json_encode(array(
												'id' => $datastex['Fld_Stock_externe_ID'],
												'qty' => $datastex['Fld_Qty'],
												'condition_id' => $datastex['Fld_Condition_ID'],
												'price' => $datastex['Fld_Part_Price'],
												'currency_id' => $datastex['Fld_Price_Currency_ID'],
												'release_id' => $datastex['Fld_Release_ID'],
												'tag_info' => trim($datastex['Fld_Tag_Info_ID'] . ',' . $datacn['Fld_Company_Name'], ','),
												'tag_date' => $datastex['Fld_Tag_Date'],
												'traceability' => $datastex['Fld_Traceability_ID'],
												'lead_time' => $datastex['Fld_External_Location'],
												'remark' => $datastex['Fld_Stock_Remark']
											)), ENT_QUOTES, 'UTF-8');
	                                            echo "<tr><td><input type=\"radio\" name=\"external_stock_choice\" value=\"".$datastex['Fld_Stock_externe_ID']."\" onchange=\"quote_the_customer_external(this.getAttribute('data-stock'))\" data-stock=\"".$externalStockPayload."\"></td><td>".$datastex['Fld_Stock_externe_ID']."</td><td>".$data["Fld_Part_Nbr"]."</td><td>".$data['Fld_Part_Desc']."</td><td>".$Aircraft_modelse."</td><td>".$datastex['Fld_Qty']."</td><td>".$datac['Fld_Condition_Text']."</td><td>".$datacn['Fld_Company_Name']."</td><td>".$datastex['Fld_Entry_Date']."</td><td>".$datastex['Fld_Stock_Remark']."</td></tr>";
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
			<!--Fin EXTERNAL STOCK select to quote-->
			<!--*******************************************************************************************-->
			<!--*******************************************************************************************-->
			
			<!--SUPPLIERS QUOTE-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            SUPPLIERS QUOTE 
							<a href='javascript:windowaddsqpn();' style="top: 4px;color:#fff;text-decoration: none;"><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-circle'></i> ADD A SQ</a>
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
						<!--Verif si il y a des Supplier quote pour ce pn-->
						<?php
							//****tbl_RFQ_2******ID  Fld_RFQ_ID  Fld_Supplier_ID  Fld_Qty  Fld_Condition_ID  Fld_Payment_Term_ID  Fld_Delivery  Fld_Price  Fld_Price_Max  Fld_Price_Min  Fld_Currency_ID  Fld_Traceability_ID  Fld_Tag_Info_ID  Fld_Tag_Date  Fld_Release_ID  
							//Fld_Part_ID  Fld_Remark  Fld_IsBeen_Chosen  Fld_Current_Date  Fld_Qty_Received  Fld_Part_SN  Fld_Supplier_Contact_ID  Fld_Date_RecevdEnd_REP lead_time
							$sqlrfq2="SELECT * from tbl_RFQ_2 where Fld_Part_ID='".$data['Fld_Part_ID']."' ORDER BY ID DESC";
							//echo $sqlrfq2;
							$reqrfq2 = mysql2_query($sqlrfq2);
							$numrows_SQ = mysqli_num_rows($reqrfq2);
						?>
						<!--Fin Verif si il y a des supliers quote pour ce pn-->
						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_SQ=='0'){ ?>style="display:none;"<?php } ?>>
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
						try {
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
					                        $sqlemp="SELECT Employee_Name FROM tbl_Employee where tbl_Employee.Employee_ID='".$datarfq2['aci_contact']."'";
											
											$reqemp = mysql2_query($sqlemp);
											$dataemp = mysqli_fetch_array($reqemp);
					                        //Fin recuperation Employee_Name
											
											
                                            echo "<tr>";
											echo "<td style='border: 1px solid ".$prioritescss.";'><a href=\"javascript:void(0);\" data-href=\"getContentSupplier.php?id=".$datarfq2['ID']."&Fld_RFQ_ID=".$datarfq2['Fld_RFQ_ID']."\" class=\"openPopup\"><i class=\"fa  fa-plane\"></i></a></td>";
											// echo "<td><input type=\"radio\" name=\"suppliers_choice_id\" value='".$datarfq2['ID']."' onchange=\"quote_the_customer_sq('".$datarfq2['ID']."')\"></td>";
											echo "<td><a href='modif_suppliers_quote.php?ID=".$datarfq2['ID']."&part_id=".$part_id."'>".$datarfq2['Fld_RFQ_ID']."</a></td>
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
										} catch(Throwable $t) {

										}
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
			<!---------------------------------------------------------------------------------------------->
			<!---------------------------------------------------------------------------------------------->
			<!---------------------------------------------------------------------------------------------->
			<!--***************************************ADD SQ*********************************************-->
			 <div class="row" id="addpnsq" style='display:none'>
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            ADD SUPPLIERS QUOTE
                        </div>
						<form id="formajoutsq" role="form" method="post" action="valid_add_sq.php" class="needs-validation" novalidate>
						<?php $today = date("Y-m-d");?>
						<input type="hidden" name="Fld_Current_Date" value="<?php echo $today;?>">
						<input type="hidden" name="aci_contact" value="<?php echo $_SESSION['id_utilisateur'];?>">
                        <div class="panel-body">
                            <div class="row">
							
							<div class="col-lg-2">
										<div class="form-group">
                                            <label>RFQ ID</label>&nbsp;&nbsp;<i style='font-size:18px;color:#FE2E2E;' class='fa fa-warning '></i> If you don't select it will be generated
											<select class="form-control" name="Fld_RFQ_ID" id="Fld_RFQ_ID" required>
											<option value="">SELECT RFQ - Otherwise it will be generated </option>
											<?php 
											$sqlrfqid2="SELECT Fld_RFQ_ID from tbl_RFQ_1 where Fld_Part_ID='".$data['Fld_Part_ID']."' ORDER BY ID DESC";
											$reqrfqid2 = mysql2_query($sqlrfqid2);
											while($datarfqid2 = mysqli_fetch_array($reqrfqid2))
												{
													echo "<option value='".$datarfqid2['Fld_RFQ_ID']."'>".$datarfqid2['Fld_RFQ_ID']."</option>";
												}
											?>
											</select>
                                        </div>
	
							</div>
							<div class="col-lg-2">
									<div class="form-group">
                                            <label>PN</label><br><?php echo $data["Fld_Part_Nbr"];?>
											<input type="hidden" name="pn_rfq" id="pn_rfq" value="<?php echo $data["Fld_Part_Nbr"];?>">
											<input type="hidden" name="Fld_Part_ID" id="Fld_Part_ID" value="<?php echo $part_id;?>">
       
                                        </div>
							</div>
							<div class="col-lg-2">
									<div class="form-group">
                                            <label>DESCRIPTION</label><br><?php echo $data['Fld_Part_Desc'];?>
											<input type="hidden" name="Fld_Part_Desc" id="Fld_Part_Desc" value="<?php echo $data['Fld_Part_Desc'];?>">
       
                                        </div>
							</div>
								
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>SUPPLIERS</label><a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<!--Les suppliers sont aussi les compagnie par ce qu'une compagnie peut etre vendeuse et acheteuse-->
											<input  type="text" name="companyid" id="companyid2" class="companyid" placeholder="Please Enter company" >
											
                                        </div>
								</div>
								<div class="col-lg-2" id='bloccontactname2'>
										<div class="form-group" id='divcontactname2'>
                                            <label>SUPPLIERS CONTACT NAME</label><a data-toggle="modal" data-target="#myModalcontactname" data-whatever="TEST" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<select class="form-control" name="Fld_Supplier_Contact_ID"  onclick="javascript:majtarea2();">
											<option></option>
                                                
                                            </select>
                                        </div>
								</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PART SN</label>
											<input class="form-control" name="Fld_Part_SN">
                                        </div>
								</div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty">
                                        </div>
								</div>
								<div class="col-lg-2">
										<label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<?php

											$sqldiv="SELECT distinct(Fld_Condition_Text),Fld_Condition_ID FROM tbl_Condition order by Fld_Condition_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Condition_ID']."'>".$datadiv ['Fld_Condition_Text']."</option>";
											}
											?>
                                                
                                            </select>
								</div>	
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>RELEASE</label>
                                            <select class="form-control" name="Fld_Release_ID">
											<?php
											$sqldiv="SELECT * FROM tbl_Release order by Fld_Release_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Release_ID']."'>".$datadiv ['Fld_Release_Text']."</option>";
											}
											?>
                                            </select>
                                        </div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG INFO</label><a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<input  type="text" name="companyidtaginfo" id="companyidtaginfo" class="companyidtaginfo" placeholder="Please Enter company" >
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>TAG DATE (JJ/MM/AAAA)</label>
                                            <input class="form-control" name="Fld_Tag_Date">
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>TRACEABILITY</label><a data-toggle="modal" data-target="#myModal" style="cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i></a><br>
											<!--Traceability sont les noms de compagnie-->
                                            <input  type="text" name="companyidtreacability" id="companyidtreacability" class="companyidtreacability" placeholder="Please Enter company" >
                                        </div>
									</div>
							</div>
						
							<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>LEAD TIME</label>
											<input class="form-control" name="lead_time">
                                    </div>
                                </div>
								<div class="col-lg-1">
										<div class="form-group">
                                            <label>DELIVERY </label><!--(number of days)-->
                                            <input class="form-control" name="Fld_Delivery">
                                        </div>
								</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PRICE</label>
                                            <input class="form-control" name="Fld_Price">
                                        </div>
									</div>
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>$/€</label>
                                            <select class="form-control" name="Fld_Price_Currency_ID">
											<?php
											//recuperation du nom de la currency	
											// Fld_Currency_ID    Fld_Currency_Text
											$sqldiv="SELECT * FROM tbl_Currency";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Currency_ID']."'>".$datadiv ['Fld_Currency_Text']."</option>";
											}
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>PAYMENT TERM</label>
                                            <select class="form-control" name="Fld_Payment_Term_ID">
											<?php
											//recuperation Payment_Term
											// ** tbl_Payment ** Fld_Payment_Term_ID  Fld_Payment_Text
					                        $sqlpt="SELECT * FROM tbl_Payment order by Fld_Payment_Text";
											
											$reqpt = mysql2_query($sqlpt);
											while($datapt = mysqli_fetch_array($reqpt)){
												echo "<option value='".$datapt['Fld_Payment_Term_ID']."'>".$datapt['Fld_Payment_Text']."</option>";
											}
					                        //Fin recuperation Payment_Term
											?>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
                                            <label>REMARK</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Remark"></textarea>
                                        </div>
									</div>
							</div>
							
						</div>
							          

                        <!-- /.panel-body -->
									<button type="submit" class="btn btn-default">Validate</button>
						</form>

						
						
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
			<!--**************************************END ADD SQ******************************************-->
			<!---------------------------------------------------------------------------------------------->
			<!---------------------------------------------------------------------------------------------->
			<!---------------------------------------------------------------------------------------------->
			<!--Fin SUPPLIERS QUOTE-->
			
			<!--**********************************************************************-->
			<!--**********************CAPA LIST***************************************-->
			<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            CAPA LIST <a data-toggle="modal" data-target="#myModalcl" data-test="<?php echo $part_id;?>" class="identifyingClass" style="top: 4px;color:#fff;text-decoration: none;cursor: pointer;"><i style='margin-left:10px;top: 4px;font-size:23px;' class='fa fa-plus-circle '></i> ADD A PN </a>
                        </div>
                        <!-- /.panel-heading -->
						<!--************************************************-->
						<!--Verif si il y a des RFQ pour ce pn-->
						<?php
							//****tbl_capa_list****id_capa_list  Fld_Part_ID  pn  descriptioin  aircraft  manufacturer  ata  capability  pma  doa  der  code_oem  design_oem  id_company  status  entry_date  comments
							$sqlcapal="SELECT * from tbl_capa_list where Fld_Part_ID='".$part_id."'";
							//echo $sqlcapal;
							$reqcapal = mysql2_query($sqlcapal);
							$numrows_capal = mysqli_num_rows($reqcapal);
						?>
						<!--Fin Verif si il y a des RFQ pour ce pn-->
						 
						<!--************************************************-->
                        <div class="panel-body" <?php if ($numrows_capal=='0'){ ?>style="display:none;"<?php } ?>>
						    <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
							SELECT TO QUOTE
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
										
                                            <th>ID</th>
                                            <th>PN</th>
                                            <th>DESCRIPTION</th>
                                            <th>AIRCRAFT</th>
                                            <th>ATA</th>
                                            <th>CAPABILITY</th>
                                            <th>PMA</th>
                                            <th>DOA</th>
                                            <th>DER</th>
                                            <th>CODE OEM</th>
                                            <th>DESIGN OEM</th>
                                            <th>ENTRY DATE</th>
                                            <th>COMPANY</th>
										
                                        </tr>
                                    </thead>
                                    <tbody>							
					<?php
					
					while($datacapal = mysqli_fetch_array($reqcapal))
					{
											
											                      
											//recuperation du nom de compagnie ********************
											$sqlrncapal="SELECT Fld_Company_Name FROM tb_company WHERE Fld_Company_ID='".$datacapal['id_company']."'";
											$reqrncapal = mysql2_query($sqlrncapal);
											$datarncapal = mysqli_fetch_array($reqrncapal);
											//Fin recuperation du nom de compagnie ********************
											
                                            echo "<tr>
											<td>".$datacapal['id_capa_list']."</td>
											<td>".$datacapal['pn']."</td>
											<td>".$datacapal['descriptioin']."</td>
											<td>".$datacapal['aircraft']."</td>
											<td>".$datacapal['ata']."</td>
											<td>".$datacapal['capability']."</td>
											<td>".$datacapal['pma']."</td>
											<td>".$datacapal['doa']."</td>
											<td>".$datacapal['der']."</td>
											<td>".$datacapal['code_oem']."</td>
											<td>".$datacapal['design_oem']."</td>
											<td>".$datacapal['entry_date']."</td>
											<td>".$datarncapal['Fld_Company_Name']."</td>";
											
											echo "</tr>";
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
			<!--**********************END CAPA LIST***********************************-->
			<!--**********************************************************************-->
			
			
			
			
			<!--**********************************************************************-->
			<!--Envoie email RFQ-->
				<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                         <a href='javascript:sendrfqopen();' style="top: 4px;color:#fff;text-decoration: none;"><i style="top: 4px;color:#fff;" class="fa  fa-plus-square"></i>   SEND RFQ</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body" id="sendrfq" style="padding-bottom: 1px;display:none;">                           
						   <?php
					/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$part_id."' and status='Available'";
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
			<form action="sendemailrfq.php" method="post">
			<input type="hidden" name="Fld_Part_ID" value="<?php echo $data['Fld_Part_ID'];?>">
							<div class="row">
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>PN</label>
                                            <input class="form-control" name="Fld_Part_Nbr" id="Fld_Part_Nbr" value="<?php echo $data["Fld_Part_Nbr"];?>" >
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="Fld_Part_Desc" id="Fld_Part_Desc" value="<?php echo $data['Fld_Part_Desc'];?>" >
											
                                        </div>
                                    </div>
									<div class="col-lg-1">
										<label>CONDITION</label>
                                            <select class="form-control" name="Fld_Condition_ID">
											<option></option>
											<?php

											$sqldiv="SELECT distinct(Fld_Condition_Text),Fld_Condition_ID FROM tbl_Condition order by Fld_Condition_Text";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Condition_ID']."'>".$datadiv ['Fld_Condition_Text']."</option>";
											}
											?>
                                                
                                            </select>
									</div>	
									<div class="col-lg-1">
										<div class="form-group">
                                            <label>QTY</label>
                                            <input class="form-control" name="Fld_Qty_RFQ" value="1">
                                        </div>
									</div>
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
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>COMMENTS</label>
											<input type="text" name="commentsrfq" class="form-control" placeholder="COMMENTS" >
                                        </div>
									</div>
									<div class="col-lg-2">
										<div class="form-group">
                                            <label>E-mail</label> 
											<input type="text" name="emailrfq" class="form-control" placeholder="E-mail for RFQ" ><br>
											(Multiple emails separated by a comma ",")
                                        </div>
									</div>
									<!--<div class="col-lg-2">
										<div class="form-group">
                                            <label>COMPANY</label>
											<input type="text" name="companyiderq" id="companyiderq" class="companyid" placeholder="Please Enter company" >
                                        </div>
									</div>
									<div class="col-lg-2" id='bloccontactnameemailrfq'>
										<div class="form-group" id='divcontactnameemailrfq'>
                                            <label>CONTACT NAME</label><br>
											<select class="form-control" name="id_company_contact" onclick="javascript:majtareaemailrfq();">
											<option>CHOOSE CONTACT</option>

                                            </select>
											

                                        </div>
									</div>-->
									<div class="col-lg-1">
									<br>
										<div class="form-group">
										
										<label class="checkbox-inline">
                                            <INPUT type="checkbox" name="fast_send" value="ok">Fast Send
										</label>
                                        </div>
									</div>
									<div class="col-lg-1">
									<br>
										<div class="form-group">
                                            <input type="submit" class="form-control">
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
			<!--End Envoie email RFQ-->
			<div class="row">
									<div class="col-lg-8">
										<div class="form-group">
                                            <button type="button" class="btn btn-outline btn-primary btn-lg" onClick="document.location='email_broadcast.php?part_id=<?php echo $part_id;?>&company_id=<?php echo $data["Fld_Part_MFG"];?>'">EMAIL BROADCAST</button>
											<button type="button" class="btn btn-outline btn-primary btn-lg">OPEN REPAIR STATUS</button>
											<button type="button" class="btn btn-outline btn-primary btn-lg">DUPLICATE STK ITEM</button>
											<button type="button" class="btn btn-outline btn-primary btn-lg">ADD CERT INFO TO QUOTE</button>
                                        </div>
									</div>
			
			</div>
            <!-- /.row -->
			

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--POPUP ADD A COMPANY-->
	
	<script type="text/javascript">
    $('#myModal').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
    })
</script>
	
	
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" STYLE="background-color: #A7142A;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;font-weight: bold;">ADD A COMPANY</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="myPopupInputcompanyname" class="col-form-label">COMPANY NAME:</label>
							<input type="text" class="form-control" id="myPopupInputcompanyname" />
						</div>
						<div class="col-md-4">
							
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="myPopupInputcompanyname" class="col-form-label">CONTACT NAME:</label>
							<input type="text" class="form-control" id="myPopupInputcompanycontactname" />
						</div>
						<div class="col-md-4">
							<label for="myPopupInputdescription" class="col-form-label">E-MAIL:</label>
							<input type="text" class="form-control" id="myPopupInputcompanycontactemail" />
						</div>
					</div>	
				</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
	<!--END POPUP ADD A COMPANY-->

<script>
// ===============================
// POPUP ADD COMPANY - HANDLER
// ===============================

(function() {

  // On mémorise quel input doit recevoir la company (ex: #companyid, #companyid2, etc.)
  var lastTargetInput = null;

  // Quand on clique sur un + qui ouvre #myModal,
  // on récupère l'input texte juste avant (dans le même bloc)
  $(document).on('click', 'a[data-target="#myModal"]', function() {
    // input text juste avant le <a>
    var $input = $(this).prevAll('input[type="text"]').first();

    // fallback : si structure différente, on prend l'input dans le même form-group
    if (!$input.length) {
      $input = $(this).closest('.form-group').find('input[type="text"]').first();
    }

    lastTargetInput = $input.length ? $input : null;

    // Nettoyage champs popup à chaque ouverture
    $('#myPopupInputcompanyname').val('');
    $('#myPopupInputcompanycontactname').val('');
    $('#myPopupInputcompanycontactemail').val('');
  });

  // Click sur Save du popup
  $(document).on('click', '#myModal .btn-primary', function() {
    var companyName  = $.trim($('#myPopupInputcompanyname').val());
    var contactName  = $.trim($('#myPopupInputcompanycontactname').val());
    var contactEmail = $.trim($('#myPopupInputcompanycontactemail').val());

    if (!companyName) {
      alert('Please enter a COMPANY NAME.');
      return;
    }

    // UX : désactive bouton pendant l'envoi
    var $btn = $(this);
    $btn.prop('disabled', true).text('Saving...');

$.ajax({
  url: 'add_company_from_popup.php',
  method: 'POST',
  dataType: 'json',
  data: {
    companyname: companyName,
    contactname: contactName,
    contactemail: contactEmail
  },
  success: function(resp) {

    // si jamais ton serveur renvoie du texte, on tente JSON
    if (typeof resp === 'string') {
      try { resp = JSON.parse(resp); } catch(e) {}
    }

    if (!resp || !resp.ok) {
      alert('Add Company failed: ' + (resp && resp.error ? resp.error : 'unknown'));
      return;
    }

    // 1) Remplir le champ qui a déclenché le popup
    if (lastTargetInput && lastTargetInput.length) {
      lastTargetInput.val(resp.company_name || companyName).trigger('change');
    }

    // 2) CAS IMPORTANT : si on est sur RFQ customer (champ #companyid),
    //    on remplit aussi le hidden #Fld_Customer_ID s'il existe
    if (lastTargetInput && lastTargetInput.attr('id') === 'companyid') {
      if ($('#Fld_Customer_ID').length) {
        $('#Fld_Customer_ID').val(resp.company_id);
      }
    }

    // 3) CAS SUPPLIER QUOTE : si on est sur supplier quote (champ #companyid2),
    //    on crée/remplit un hidden #Fld_Supplier_ID dans le form #formajoutsq
    if (lastTargetInput && lastTargetInput.attr('id') === 'companyid2') {
      if ($('#formajoutsq').length) {
        if (!$('#Fld_Supplier_ID').length) {
          $('#formajoutsq').append('<input type="hidden" name="Fld_Supplier_ID" id="Fld_Supplier_ID" value="">');
        }
        $('#Fld_Supplier_ID').val(resp.company_id);
      }
    }

    // Debug utile (tu peux enlever après)
    console.log('ADD_COMPANY resp =', resp);

    // Fermeture modal
    $('#myModal').modal('hide');
  },
  error: function(xhr) {
    if (xhr.status === 401) {
      alert('Session expired. Please login again.');
    } else {
      alert('Server error: ' + xhr.status);
    }
  },
  complete: function() {
    $btn.prop('disabled', false).text('Save');
  }
});
  });
})();

</script>


	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	
	
	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--POPUP ADD A COMPANY CONTACT-->
	
	<script type="text/javascript">
    $('#myModalcontactname').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
		var recipient = button.data('whatever') // Extract info from data-* attributes
		modal.find('.modal-body input').val(recipient)
    })
</script>
	
	
	<div class="modal fade" id="myModalcontactname" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" STYLE="background-color: #A7142A;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel" style="color:#FFFFFF;font-weight: bold;">ADD A COMPANY</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="myPopupInputcompanyname" class="col-form-label">COMPANY NAME:</label>
							<input type="text" class="form-control" id="myPopupInputcompanyname" />
						</div>
						<div class="col-md-4">
							
						</div>
					</div>	
				</div>
            </div>
			
			
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
	<!--END POPUP ADD A COMPANY CONTACT-->
	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	

<!--*************************************************************************************************************************************-->
	<!--POPUP ADD A PN TO CAPA LIST-->
	
	<script type="text/javascript">
    $('#myModalcl').on('hidden.bs.modal', function (e) {
        //get value from #myPopupInput and set the value to #myMainPageInput
		//test roy
			
	
		//Fin test roy
    })
</script>
	
	
	<div class="modal fade" id="myModalcl" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ADD A PN TO CAPA LIST</h4>
            </div>
			<?php
					/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/
					$sql="SELECT * from tbl_Parts where Fld_Part_ID='".$part_id."' and status='Available'";
					$req = mysql2_query($sql);
					$data = mysqli_fetch_array($req);	
			?>
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="pnidcl" class="col-form-label">PN:</label><br>
							<input type="text" name="pnidcl" id="pnidcl" value="<?php echo $data["Fld_Part_Nbr"].", ".$data["Fld_Part_ID"];?>" class="form-control">
							<input type="hidden" name="partidrecup" id="partidrecup">
						</div>
						<div class="col-md-2">
						&nbsp;
						</div>
						<div class="col-md-4">
							<label for="descriptioncl" class="col-form-label">DESCRIPTION:</label><br>
							<input type="text" name="descriptioncl" id="descriptioncl" value="<?php echo $data["Fld_Part_Desc"];?>" class="form-control">
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="aircraftcl" class="col-form-label">AIRCRAFT:</label>
							<input type="text" name="aircraftcl" id="aircraftcl" class="form-control">
						</div>
						<div class="col-md-2">
						&nbsp;
						</div>
						<div class="col-md-4">
							<label for="atacl" class="col-form-label">ATA:</label>
							<input class="form-control" name="atacl" id="atacl">
						</div>
					</div>	
				</div>
            </div>
			
			<div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="code_oemcl" class="col-form-label">CODE OEM:</label>
							<input type="text" name="code_oemcl" id="code_oemcl" class="form-control">
						</div>
						<div class="col-md-2">
						&nbsp;
						</div>
						<div class="col-md-4">
							<label for="design_oemcl" class="col-form-label">DESIGN OEM:</label>
							<input class="form-control" name="design_oemcl" id="design_oemcl">
						</div>
					</div>	
				</div>
            </div>
			
            <div class="modal-body">
                <div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<label for="companyidcl" class="col-form-label">COMPANY NAME:</label><br>
							<input type="text" name="companyidcl" id="companyidcl" class="companyidcl" placeholder="Please Enter company">
						</div>
						<div class="col-md-2">
							
						</div>
						<div class="col-md-4">
							<label for="capabilitycl" class="col-form-label">CAPABILITY:</label>
							<input type="text" name="capabilitycl" id="capabilitycl" class="form-control">
						</div>
						
					</div>	
				</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
	<!--END ADD A PN TO CAPA LIST-->
	<!--*************************************************************************************************************************************-->
	<!--*****************************************************************************************************-->
	
    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>
    <script>
    if (window.jQuery && !jQuery.fn.metisMenu) {
        jQuery.fn.metisMenu = function() { return this; };
    }
    </script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
<!--<script src="../URBA/bower_components/jquery/dist/jquery.min.js"></script>Roy-->

	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--**************************************POPUP SEND QUOTQTION AND SUPPLIERS-->

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
  $('.openPopup').on('click', function(){
    var dataURL = $(this).attr('data-href');
    $('#myModalSendQuote .modal-body').load(dataURL, function(){
      $('#myModalSendQuote').modal('show');
    });
  });
});
</script>
	
	<!--**************************************END POPUP SEND QUOTQTION AND SUPPLIERS*********************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->


	
	
    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });	
	
    </script>
<!--Ajout pour autocompression Roy-->
 <!--<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
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
        .companyid,
        .companyidcl,.companyidforoem,.Fld_Tag_Info_ID,.Fld_Traceability_ID,.companyidtaginfo,.companyidtreacability {
            display: block;
    width: 100%;
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
		function aciCompanyId(value) {
			return String(value || '').split(',')[0].replace(/^\s+|\s+$/g, '');
		}

		function aciSetFormValue(formName, fieldName, value) {
			var form = document.forms[formName];
			if (form && form.elements[fieldName]) {
				$(form.elements[fieldName]).val(value || '');
			}
		}

		function aciSelectStockSource(payload, sourceLabel) {
			var stock = {};
			try {
				stock = JSON.parse(payload || '{}');
			} catch (e) {
				return;
			}

			aciSetFormValue('Form1', 'Fld_Qty', stock.qty);
			aciSetFormValue('Form1', 'Fld_Condition_ID', stock.condition_id);
			aciSetFormValue('Form1', 'Fld_Release_ID', stock.release_id);
			aciSetFormValue('Form1', 'Fld_Tag_Info_ID', stock.tag_info);
			aciSetFormValue('Form1', 'Fld_Tag_Date', stock.tag_date);
			aciSetFormValue('Form1', 'Fld_Traceability_ID', stock.traceability);
			aciSetFormValue('Form1', 'lead_time', stock.lead_time);
			aciSetFormValue('Form1', 'Fld_Price', stock.price);
			aciSetFormValue('Form1', 'FldCurrencyID', stock.currency_id);
			aciSetFormValue('Form1', 'Fld_Remark', stock.remark);

			$('#blocquotecustomer, #blocrecuprfqquote').hide();
			$('#divquotecustomer, #divrecuprfqquote').empty();

			if ($('#stock-selected-source').length === 0) {
				$('#rfq_collapse .panel-body').first().prepend('<div id="stock-selected-source" class="alert alert-success" style="margin-bottom:10px;"></div>');
			}
			$('#stock-selected-source').text('Selected source: ' + sourceLabel + ' #' + (stock.id || ''));
			if (window.location.hash !== '#rfq_collapse') {
				window.location.hash = 'rfq_collapse';
			}
		}

		function quote_the_customer_aci(payload) {
			aciSelectStockSource(payload, 'ACI770 Stock');
		}

		function quote_the_customer_external(payload) {
			aciSelectStockSource(payload, 'External Stock');
		}

        $(document).ready(function() {

            $('input.companyid').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidcl').typeahead({
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
			$('input.Fld_Traceability_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });

			if (window.location.hash === '#rfq_collapse' && $('#rfq_collapse').length) {
				$('#rfq_collapse').collapse('show');
				setTimeout(function() {
					document.getElementById('rfq_collapse').scrollIntoView();
				}, 100);
			}
        });
//Fin Ajout pour autocompression Roy

//<!--*****************************************************************************************************-->	
//<!--****************************************ADD PN POPUP*************************************************-->
	$('#myModal').on('click', '.btn-primary', function(){
    var value = $('#myPopupInputcompanyname').val();
    var value2 = $('#myPopupInputcompanycontactname').val();  
    var value3 = $('#myPopupInputcompanycontactemail').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_company_from_popup.php',
         data: {companyname: value, contactname:value2, contactemail:value3},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModal').modal('hide');
});
//<!--************************************END ADD PN POPOUP************************************************-->
//<!--*****************************************************************************************************-->
//<!--*****************************************************************************************************-->

//<!--*****************************************************************************************************-->	
//<!--****************************************SEND QUOTQTION POPUP*************************************************-->
	$('#myModalSendQuote').on('click', '.btn-primary', function(){
    var value = $('#myPopupInputcompanyname').val();
    var value2 = $('#myPopupInputcompanycontactname').val();  
    var value3 = $('#myPopupInputcompanycontactemail').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_company_from_popup.php',
         data: {companyname: value, contactname:value2, contactemail:value3},
         type: 'get',
         success: function(output) {
                      // alert(output);
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModalSendQuote').modal('hide');
});
//<!--************************************END SEND QUOTQTION POPOUP************************************************-->
//<!--*****************************************************************************************************-->
//<!--*****************************************************************************************************-->

//<!--*****************************************************************************************************-->	
//<!--****************************************ADD PN TO CAPALIST POPUP*************************************-->
	$('#myModalcl').on('click', '.btn-primary', function(){
    var value = $('#pnidcl').val();
    var value2 = $('#descriptioncl').val();
    var value3 = $('#aircraftcl').val();
    var value4 = $('#atacl').val();
    var value5 = $('#capabilitycl').val();
    var value6 = $('#code_oemcl').val();
    var value7 = $('#design_oemcl').val();
    var value8 = $('#companyidcl').val();
	//enregistrer le nouveau pn dans la base 
		 $.ajax({ url: 'add_capalist_popup.php',
         data: {pnid: value, description:value2, aircraft:value3, ata:value4, capability:value5, code_oem:value6, design_oem:value7, companyid:value8},
         type: 'get',
         success: function(output) {
                      // alert('test');
         }
});
         //FIN enregistrer le nouvea pn dans la base 
    $('#myModalcl').modal('hide');
	document.location.reload();
});
//<!--************************************END ADD PN TO CAPALIST POPUP*************************************-->
//<!--*****************************************************************************************************-->
//<!--*****************************************************************************************************-->

//<!--code pour envoyer une valeur du lien vers le popup capalist-->
$(function () {
        $(".identifyingClass").click(function () {
            var my_id_value = $(this).data('test');
            $(".modal-body #partidrecup").val(my_id_value);
        })
    });
//<!--Fin code pour envoyer une valeur du lien vers le popup capalist-->
	
//<!--*******************************************************************************-->
//<!--*******************************************************************************-->
//<!--Ajout nom contact a partir du nom de la societe-->
//<!--*******************************************************************************-->
//<!--*******************************************************************************-->
	function majtarea(id)
{
var bloccontactname=document.getElementById('bloccontactname');
var companyidval=aciCompanyId(document.getElementById('companyid').value);

bloccontactname.style.display='inline';

//document.getElementById("divcontactname").innerHTML='<div id="divcontactname" align="center"><img src="../images/Spin.gif" border="0"></div>';
           
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
            xhr.send("id2="+companyidval);/*si je veux mettre la variable sous forme de post je la met la*/
    
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
//<!--*******************************************************************************-->
//<!--*******************************************************************************-->
//<!--Ajout nom contact a partir du nom de la societe popup-->
//<!--*******************************************************************************-->
//<!--*******************************************************************************-->
	function majtareapopup(id)
{
var bloccontactnamepopup=document.getElementById('bloccontactnamepopup');
var companyidvalpopup=aciCompanyId(document.getElementById('companyidpopup').value);

bloccontactnamepopup.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "contactnamefromcompany.php?id="+companyidvalpopup, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_contact_name_popup(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name_popup(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactnamepopup').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactnamepopup').innerHTML+=resp2;
    document.getElementById('divcontactnamepopup').innerHTML+='</div>';
    }
}
//*******************************************************************************
//*******************************************************************************
//<!--Fin Ajout nom contact a partir du nom de la societe popup-->
//*******************************************************************************
//*******************************************************************************
//<!--fonction qui remet le div contact name comme avant le changement*-->
	function majtareaback(id) 
{
var bloccontactname=document.getElementById('bloccontactname');

bloccontactname.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "contactnamebackrfq.php", true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_contact_name_back(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name_back(xhr,id)
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
<!--Fin fonction qui remet le div contact name comme avant le changement-->
	function majtarea2(id)
{
var bloccontactname2=document.getElementById('bloccontactname2');
var companyidval=aciCompanyId(document.getElementById('companyid2').value);

bloccontactname2.style.display='inline';
           
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
            xhr.onreadystatechange = function() { up_contact_name2(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name2(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactname2').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactname2').innerHTML+=resp2;
    document.getElementById('divcontactname2').innerHTML+='</div>';
    }
}
//*******************************************************************************
//*******************************************************************************
// Fin Ajout nom contact a partir du nom de la societe-->
//*******************************************************************************
//*******************************************************************************

//*******************************************************************************
//*******************************************************************************
//Ajout nom contact a partir du nom de la societe  email rfq-->
//*******************************************************************************
//*******************************************************************************

	function majtareaemailrfq(id)    
{
var bloccontactnameemailrfq=document.getElementById('bloccontactnameemailrfq');
var companyidval=aciCompanyId(document.getElementById('companyiderq').value);

bloccontactnameemailrfq.style.display='inline';
           
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
            xhr.onreadystatechange = function() { up_contact_name_erfq(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    
}
function up_contact_name_erfq(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcontactnameemailrfq').innerHTML='<div id="'+id+'" align="center">';
         var resp2;
        resp2 = xhr.responseText;
        document.getElementById('divcontactnameemailrfq').innerHTML+=resp2;
    document.getElementById('divcontactnameemailrfq').innerHTML+='</div>';
    }
}

//*******************************************************************************
//*******************************************************************************
// Fin Ajout nom contact a partir du nom de la societe email rfq
//*******************************************************************************
//*******************************************************************************

//Consulter et modifier Stock
	function addstock(id)
{
var blocstock=document.getElementById('blocstock');
//if(blocstock.style.display=='inline') blocstock.style.display='none';
//else{
blocstock.style.display='inline';

//document.getElementById("divstock").innerHTML='<div id="divstock" align="center"><img src="../images/Spin.gif" border="0"></div>';
           
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
					
//************************************Function quote the customer from the stock ******************************
function quote_the_customer(id)
{
var blocquotecustomer=document.getElementById('blocquotecustomer');
//if(blocquotecustomer.style.display=='inline') blocquotecustomer.style.display='none';
//else{
blocquotecustomer.style.display='inline';

//document.getElementById("divquotecustomer").innerHTML='<div id="divquotecustomer" align="center"><img src="../images/Spin.gif" border="0"></div>';
           
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
            xhr.onreadystatechange = function() { open_quote_customer_stock(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function open_quote_customer_stock(xhr,id)
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
//End Function quote the customer from the stock-->

//************************************Function quote the customer from SUPPLIERS QUOTE ******************************
function quote_the_customer_sq(id)
{
var blocrecuprfqquote=document.getElementById('blocrecuprfqquote');

blocrecuprfqquote.style.display='inline';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "sq_for_quote.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { open_quote_customer_sq(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   
}
function open_quote_customer_sq(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divrecuprfqquote').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divrecuprfqquote').innerHTML+=resp3;
    document.getElementById('divrecuprfqquote').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
//End Function quote the customer from SUPPLIERS QUOTE//

//*******************************************************************************
//*******************************************************************************

//Recuperation des infos rfq pour affichage dans fenetre gauche
function recup_info_rfq(id)
{
var blocrecuprfq=document.getElementById('blocrecuprfq');
//if(blocrecuprfq.style.display=='inline') blocrecuprfq.style.display='none';
//else{
blocrecuprfq.style.display='inline';

//document.getElementById("divrecuprfq").innerHTML='<div id="divrecuprfq" align="center"><img src="../images/Spin.gif" border="0"></div>';
           
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
//Fin Recuperation des infos rfq pour affichage dans fenetre gauche

//*******************************************************************************
//*******************************************************************************

//Affichage du rfq pour quotation
function view_rfq(id)
{
var blocrecuprfqquote=document.getElementById('blocrecuprfqquote');
//if(blocrecuprfqquote.style.display=='inline') blocrecuprfqquote.style.display='none';
//else{
blocrecuprfqquote.style.display='inline';

//document.getElementById("divrecuprfqquote").innerHTML='<div id="divrecuprfqquote" align="center"><img src="../images/Spin.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "rfq_for_quote2.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { open_rfq(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
   // }
}
function open_rfq(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divrecuprfqquote').innerHTML='<div id="'+id+'" align="center">';
         var resp3;
        resp3 = xhr.responseText;
        document.getElementById('divrecuprfqquote').innerHTML+=resp3;
    document.getElementById('divrecuprfqquote').innerHTML+='</div>';
	document.location.href="#wrapper";//je redirige le lien vers le haut de la banniere (l'ancre haut wrapper)
    }
}
//Fin Affichage du rfq pour quotation

//*******************************************************************************
//*******************************************************************************

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


<!--Ouverture fenetre/fermeture DOCS ATTACHMENT-->
function docattachopen()
{
var docattach=document.getElementById('docattach');
if(docattach.style.display=='inline') docattach.style.display='none';
else{
docattach.style.display='inline';
}
}
<!--END Ouverture/fermeture fenetre DOCS ATTACHMENT-->

<!--Ouverture fenetre/fermeture DOCS ATTACHMENT-->
function sendrfqopen()
{
var sendrfq=document.getElementById('sendrfq');
if(sendrfq.style.display=='inline') sendrfq.style.display='none';
else{
sendrfq.style.display='inline';
}
}
<!--END Ouverture/fermeture fenetre DOCS ATTACHMENT-->


<!--Ouverture fenetre/fermeture ADD SUPPLIER QUOTE-->
function windowaddsqpn()
{
var addpnsq=document.getElementById('addpnsq');
if(addpnsq.style.display=='inline') addpnsq.style.display='none';
else{
addpnsq.style.display='inline';
}
}
<!--END Ouverture/fermeture fenetre ADD SUPPLIER QUOTE-->

//*******************************************************************************
//*******************************************************************************
//****************** DIFFERENTS ACTION SUR BOUTON SUBMIT*************************
//*******************************************************************************
//*******************************************************************************
function OnButton1()
{
    document.Form1.action = "valid_add_rfq.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton2()
{
    document.Form1.action = "email_broadcast.php"
    document.Form1.target = "_self";    // Open in a new window
    document.Form1.submit();             // Submit the page
    return true;
}

function OnButton3()
{
    document.Form2.action = "save_rfq_quote.php"
    document.Form2.target = "_self";    // Open in a new window
    document.Form2.submit();             // Submit the page
    return true;
}

function OnButton4()
{
    document.Form2.action = "email_broadcast.php"
    document.Form2.target = "_self";    // Open in a new window
    document.Form2.submit();             // Submit the page
    return true;
}

function OnButton5()
{
    document.Form3.action = "save_rfq_quote.php"
    document.Form3.target = "_self";    // Open in a new window
    document.Form3.submit();             // Submit the page
    return true;
}

function OnButton6()
{
    document.Form3.action = "email_broadcast.php"
    document.Form3.target = "_self";    // Open in a new window
    document.Form3.submit();             // Submit the page
    return true;
}

//*******************************************************************************
//*******************************************************************************
//****************** FIN DIFFERENTS ACTION SUR BOUTON SUBMIT*********************
//*******************************************************************************
//*******************************************************************************
</script>
		

<!--2025.11.13 15.13 j'insere ca pour essayer de retrouver le collapse-->

<script>
$(function() {
  // OEM company — update hidden Fld_Part_MFG on selection
  // typeahead 0.9.3 fires 'typeahead:selected' (not 'typeahead:select')
  // datum.value = "ID,CompanyName" from list-company.php
  $('input.companyidforoem').bind('typeahead:selected typeahead:autocompleted', function(ev, datum) {
    var companyId = datum.value.split(',')[0];
    $('input[name="Fld_Part_MFG"]').val(companyId);
  });

  // company (other sections)
  $('input.companyid').bind('typeahead:selected typeahead:autocompleted', function(ev, datum) {
    var companyId = aciCompanyId(datum.value);
	var inputId = $(this).attr('id');

	if (inputId === 'companyid') {
		$('#Fld_Customer_ID').val(companyId);
		setTimeout(function() { majtarea('divcontactname'); }, 50);
	}
	if (inputId === 'companyid2') {
		setTimeout(function() { majtarea2('divcontactname2'); }, 50);
	}
	if (inputId === 'companyiderq') {
		setTimeout(function() { majtareaemailrfq('divcontactnameemailrfq'); }, 50);
	}
  });

  // PN
  $('input.pnid').bind('typeahead:selected typeahead:autocompleted', function(ev, datum) {
    $('#Fld_Part_ID_hidden').val(datum.value.split(',')[0]);
  });
});
</script>



</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>
