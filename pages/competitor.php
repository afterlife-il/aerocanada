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

 

       

          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-8">
                    <h1 class="page-header">COMPETITOR</h1>
                </div>
                <!-- /.col-lg-8 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="panel panel-default" style="background-color: #ddd;">
                        <div class="panel-heading" style="background-color:#A7142A">
                            
                        </div>
						
						<form id="formcompetitor" role="form" method="post" action="competitor.php">
						<!--
						//*****tbl_Parts*************  Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn Fld_Add_PN_Date aci_contact_entry ata_chapter
						-->
						<input type="hidden" name="act" value="okcompet">
									
						<div class="panel-body">
							
							
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
                                            <label>COMPANY 1</label><br>
											<input type="text" name="companyid1" id="companyid1" size="30" class="companyid1" placeholder="Please Enter company">
                                    </div>
                                </div>
								
								<div class="col-lg-2">
									<div class="form-group">
                                            <label>COMPANY 2</label><br>
											<input type="text" name="companyid2" id="companyid2" size="30" class="companyid2" placeholder="Please Enter company">
                                    </div>
                                </div>
								<div class="col-lg-2">
									<div class="form-group">
                                            <label>COMPANY 3</label><br>
											<input type="text" name="companyid3" id="companyid3" size="30" class="companyid3" placeholder="Please Enter company">
                                    </div>
                                </div>
								<div class="col-lg-2">
									<div class="form-group">
                                            <label>COMPANY 4</label><br>
											<input type="text" name="companyid4" id="companyid4" size="30" class="companyid4" placeholder="Please Enter company">
                                    </div>
                                </div>
								<div class="col-lg-2">
									<div class="form-group">
									<br>
                                     		<button type="button" id="submit_competitors" class="btn btn-default">Validate</button>


                                    </div>
                                </div>
								
							</div>
							
							<!--  --><?php 
							// if($_POST['act']=='okcompet')
							// {
							// 	$nbcomp=0;
							// 		if (!empty($_POST['companyid1'])) 
							// 		{
							// 		$companyid1 = explode(",", $_POST['companyid1']);
							// 		$companyidrecup1=$companyid1[0]; 
							// 		$nbcomp++;
							// 		}
							// 		if (!empty($_POST['companyid2'])) 
							// 		{
							// 		$companyid2 = explode(",", $_POST['companyid2']);
							// 		$companyidrecup2=$companyid2[0]; 
							// 		$nbcomp++;
							// 		}
							// 		if (!empty($_POST['companyid3'])) 
							// 		{
							// 		$companyid3 = explode(",", $_POST['companyid3']);
							// 		$companyidrecup3=$companyid3[0]; 
							// 		$nbcomp++;
							// 		}
							// 		if (!empty($_POST['companyid4'])) 
							// 		{
							// 		$companyid4 = explode(",", $_POST['companyid4']);
							// 		$companyidrecup4=$companyid4[0]; 
							// 		$nbcomp++;
							// 		}
							// 		echo "<b>COMPET. EN COMMUN:</b><br>";
							// 		//**tbl_Competitor**  Fld_Linked_ID Fld_Company_ID  Fld_Competitor_ID
							// 		$sqlcopmpet="SELECT Fld_Competitor_ID FROM tbl_Competitor where Fld_Company_ID='".$companyidrecup1."'";
							// 		$reqcopmpet = mysql2_query($sqlcopmpet);
							// 		while ($datacopmpet = mysqli_fetch_array($reqcopmpet))
							// 		{
							// 				$sqlcopmpet2="SELECT * FROM tbl_Competitor where Fld_Company_ID='".$companyidrecup2."' AND Fld_Competitor_ID='".$datacopmpet['Fld_Competitor_ID']."'";
							// 				$reqcopmpet2 = mysql2_query($sqlcopmpet2);
							// 				$num_rows2 = mysqli_num_rows($reqcopmpet2);
											
							// 				if(!empty($companyidrecup3))
							// 				{
							// 				$sqlcopmpet3="SELECT * FROM tbl_Competitor where Fld_Company_ID='".$companyidrecup3."' AND Fld_Competitor_ID='".$datacopmpet['Fld_Competitor_ID']."'";
							// 				$reqcopmpet3 = mysql2_query($sqlcopmpet3);
							// 				$num_rows3 = mysqli_num_rows($reqcopmpet3);
							// 				}
							// 				if(!empty($companyidrecup4))
							// 				{
							// 				$sqlcopmpet4="SELECT * FROM tbl_Competitor where Fld_Company_ID='".$companyidrecup4."' AND Fld_Competitor_ID='".$datacopmpet['Fld_Competitor_ID']."'";
							// 				$reqcopmpet4 = mysql2_query($sqlcopmpet4);
							// 				$num_rows4 = mysqli_num_rows($reqcopmpet4);
							// 				}
											
							// 				if(($nbcomp=='4')&&(0<$num_rows2)&&(0<$num_rows3)&&(0<$num_rows4))
							// 				{
												
							// 					$sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$datacopmpet['Fld_Competitor_ID']."'";
												
							// 					$reqcomn = mysql2_query($sqlcomn);
							// 					$datacn = mysqli_fetch_array($reqcomn);
							// 					$companynamecom = strtoupper($datacn['Fld_Company_Name']);
							// 					echo $companynamecom."<br>";
							// 				}
							// 				else if(($nbcomp=='3')&&(0<$num_rows2)&&(0<$num_rows3))
							// 				{
												
							// 					$sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$datacopmpet['Fld_Competitor_ID']."'";
												
							// 					$reqcomn = mysql2_query($sqlcomn);
							// 					$datacn = mysqli_fetch_array($reqcomn);
							// 					$companynamecom = strtoupper($datacn['Fld_Company_Name']);
							// 					echo $companynamecom."<br>";
							// 				}
							// 				else if(($nbcomp=='2')&&(0<$num_rows2))
							// 				{
												
							// 					$sqlcomn="SELECT * FROM tb_company where Fld_Company_ID='".$datacopmpet['Fld_Competitor_ID']."'";
												
							// 					$reqcomn = mysql2_query($sqlcomn);
							// 					$datacn = mysqli_fetch_array($reqcomn);
							// 					$companynamecom = strtoupper($datacn['Fld_Company_Name']);
							// 					echo $companynamecom."<br>";
							// 				}
											
							// 		} 
							// 		$nbcomp=0;
							// }		
							// ?>

										
									
                            </div>

									
									</div>

                        <!-- /.panel-body -->
									
						</form>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-8 -->
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
	<script>
    $(document).ready(function() {
        // Gestion de la soumission via AJAX pour ajouter des competitors
        $("#submit_competitors").click(function() {
            // Récupérer les valeurs des champs de companyid1 à companyid4
            var companyid1 = $('#companyid1').val();
            var companyid2 = $('#companyid2').val();
            var companyid3 = $('#companyid3').val();
            var companyid4 = $('#companyid4').val();

            // Envoi de la requête AJAX pour ajouter des competitors
            $.ajax({
                url: 'add_competitor.php',  // Fichier qui traite l'ajout en base
                type: 'POST',
                data: {
                    companyid1: companyid1,
                    companyid2: companyid2,
                    companyid3: companyid3,
                    companyid4: companyid4
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Competitors added successfully!');
                        location.reload();  // Rechargement de la page pour afficher les changements
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function() {
                    alert('An error occurred while adding competitors.');
                }
            });
        });
    });
</script>

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
        .companyid1,.companyid2,.companyid3,.companyid4 {
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

            $('input.companyid1').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'

            });
			$('input.companyid2').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyid3').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyid4').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });

        })
    </script>
	<!--Fin Ajout pour autocompression Roy-->
</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>