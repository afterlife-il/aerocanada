<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){

/*Import de la photo si possible*/	
//********************************telechargement du logo
//******************************************************
$message= "";
//je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
if(!empty($_FILES["csvcompany"]["name"]))
{
	$csvcompany=$_FILES["csvcompany"]["name"];
}
else $csvcompany="";
//Fin je verifie si il existe deja une photo si oui je l'efface avant de charger la nouvelle
$fichecsv="";
$target_dir = $_SERVER["DOCUMENT_ROOT"].'/adminaero/pages/csvcompanyupload/';

//verification si le dossier existe
if(is_dir($_SERVER["DOCUMENT_ROOT"].'/adminaero/pages/csvcompanyupload/')) {
    // $message.= '<br>Le dossier existe';
} else {
    $message.= '<br>Le dossier n\'existe pas';
}
//Fin verification si le dossier existe

$target_file = $target_dir . basename($_FILES["csvcompany"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
	$message.= "<br>Désolé, ce fichier existe déjà.";
	$uploadOk = 0;
}
// Check file size
if ($_FILES["csvcompany"]["size"] > 5000000) {
	$message.= "<br>Désolé, votre fichier est trop volumineux.";
	$uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "csv") {
	$message.= "<br>Désolé, seulement les fichiers CSV sont autorisés.";
	$uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
	$message.= "<br>Désolé, votre fichier n'a pas été téléchargé.";
// if everything is ok, try to upload file
} else {
	if (move_uploaded_file($_FILES["csvcompany"]["tmp_name"], $target_file)) {
		$message.= "<br>Le fichier ". basename( $_FILES["csvcompany"]["name"]). " a été téléchargé.";
		$fichecsv="ok";
	} 
	else {
		$message.= "<br>Désolé, il y a une erreur de chargement dans votre fichier.";
		$fichecsv="no";
	}
}
//********************************Fin telechargement du logo
//**********************************************************

//********************************Recuperation du id company et du nom company
$companyid = explode(",", $_POST['companyid']);
$companynamerecup=$companyid[1]; 
$companyidrecup=$companyid[0]; 
//********************************Fin Recuperation du id company

//**tbl_docs_attachment_company** id_docs_attachment_company	name	docs_name	id_company
 $req="INSERT INTO tbl_docs_attachment_company (`id_docs_attachment_company`,`name`,`docs_name`, `id_company`)
VALUES ('','".$_POST['docs_name']."','".$nom_docs_attachment."','".$_POST['id_company']."');";
// echo $req;
// $requete = mysql2_query($req);
		
									
/*Fin Import de la photo si possible*/	

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

 

       

          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">IMPORT CSV EXTERNAL STOCK</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default" style="background-color: #ddd;">
                        <div class="panel-heading" style="background-color:#A7142A">
                            
                        </div>
						
						<form id="formajoutpart" role="form" method="post" action="import-csv-external-stock3.php" enctype="multipart/form-data">	
									
						<div class="panel-body">
							
							<div class="row">
								<div class="col-lg-12">
								<?php 
								echo "<b>".$message."</b><br><br>";
								echo "<br>Company  : ".$companynamerecup;
								echo "<br>Company ID : ".$companyidrecup;
								echo "<br>Remark : ".$_POST['Fld_Remark'];
								?>
								<input type="hidden" name="companyname" value="<?php echo $companynamerecup;?>">
								<input type="hidden" name="companyid" value="<?php echo $companyidrecup;?>">
								<input type="hidden" name="Fld_Remark" value="<?php echo $_POST['Fld_Remark'];?>">
								<input type="hidden" name="csvcompany" value="<?php echo $csvcompany;?>">
									<!--Ouveture et importation fichier-->
									<?php
if($fichecsv=="ok")
{				
$fop = fopen('csvcompanyupload/'.$csvcompany, 'r');
if($fop === false)
{
   // Ouverture du fichier échouée
   echo "ERREUR !!";
}
else
{
	include_once "conf.php";
include_once "page_titles.php";
?>
<table style="border: 1px solid black;">
<?php
	$compteurligne=0;
   $delimiter = ';'; // Ton séparateur de cellules
   while(($a = fgetcsv($fop, 0, $delimiter)) !== false) // Récupération d'une ligne
   {
	   $existvar=0;//var roy
	   
	   
	   $compteurligne++;
	   if ($compteurligne == '2') {
        break 1;    /* You could also write 'break 1;' here. */
    }
	$varoy=0;
?>
   <tr>
<?php

      foreach($a as $val) // Parcours en boucle des cellules de la ligne
      {
		  $varoy++;
		  
?>

      <td style="border: 1px solid black;"><?php echo $varoy; ?> <?php echo $val; ?></td>
<?php
							
	}					
?>
   </tr>
<?php
   }
   fclose($fop);
   for($i=1;$i<=$varoy;$i++)
   {
	   echo "<td><select name='var".$i."'><option value='COMMENT'>COMMENT</option><option value='PN'>PN</option><option value='DESCRIPTION'>DESCRIPTION</option><option value='QTY'>QTY</option><option value='COND'>COND</option><option value='APPLICABILITY'>APPLICABILITY</option><option value='SN'>SN</option><option value='Alternate_pn'>ALTERNATE PN</option></select></td>";
	   //*****tbl_Stock_external*************  Fld_Stock_ID	Fld_Part_ID	Fld_Part_SN	Fld_Supplier_ID	Fld_Entry_Date	Fld_Part_Price	Fld_Price_Currency_ID	Fld_BAX_PO_Nbr	Fld_Supplier_order_Date	Fld_Supplier_Payment_Date	Fld_Qty	Fld_Condition_ID	Fld_Release_ID	Fld_Tag_Info_ID	Fld_Tag_Date	Fld_Traceability_ID	Fld_Warehouse_Location	Fld_Physical_Stock	Fld_Owner_ID	Fld_Stock_Location_ID	Fld_Status_ID	Fld_Status_Ind	Fld_Status_Date	Fld_Stock_Remark	Fld_Shelf_Life_Limit	Fld_Valeur_Comptable	Fld_Valeur_Comptable_currency_Id	Fld_Sales_Remark	Fld_External_Location	Fld_Sales_Remark_ID	Fld_Warehouse_Location_ID	Fld_OriginalUnit_Stock_ID	Fld_Min_Qty	Fld_Publish	status  Fld_AC_ID    Fld_Company_ID
   }
?>
</table>
<?php
}
}
?>
									<!--Fin Ouverture et importation fichier-->
									
									
									
                                </div>
					
							</div>
					
						
						<div class="form-group">
                                            
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="optionsimport" id="optionsRadios1" value="efface" checked="">Effacer et recharger les donnees de la societes
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="optionsimport" id="optionsRadios2" value="ajout">Ajouter aux informations existant
                                                </label>
                                            </div>
                                           
                       </div>
                                     
                        <input type="hidden" name="nb_champs_csv" value="<?php echo $varoy;?>">
						<button type="submit" class="btn btn-default">Validate</button>
									
						</div>

                        <!-- /.panel-body -->
					   </form>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

		<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	<!--POPUP ADD A COMPAGNIE-->
	
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
	<!--END POPUP ADD A COMPAGNIE-->
	<!--*************************************************************************************************************************************-->
	<!--*************************************************************************************************************************************-->
	
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
	
    </script>
	<!--Ajout pour autocompression Roy
 <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
    <script src="js/typeahead.js"></script>
    <style>
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
        .Fld_Part_MFG {
            border: 1px solid #CCCCCC;
            <!--border-radius: 8px 8px 8px 8px;-->
            font-size: 24px;
            <!--height: 45px;-->
            line-height: 30px;
            outline: medium none;
            padding: 8px 12px;
            width: 100%;
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

            $('input.Fld_Part_MFG').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'

            });

        })
<!--*********************************Fin Ajout pour autocompression Roy*********************************-->	

<!--*****************************************************************************************************-->	
<!--****************************************ADD A COMPANY POPUP******************************************-->
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
<!--************************************END ADD A COMPANY POPOUP*****************************************-->
<!--*****************************************************************************************************-->
<!--*****************************************************************************************************-->
		
    </script>
	
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>