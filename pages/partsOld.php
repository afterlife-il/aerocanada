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
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Parts</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href="http://aerocanada-industries.com/adminaero/pages/ajout_parts.php">  <img src="images/add.png" width="30"> Add a PN</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
									<?php
									/*******requete d'affichage de toutes les colonnes d'une table
			                        $sqlcol = "SHOW COLUMNS from tbl_Parts";
									
			                        $querycol=mysql2_query($sqlcol);
			                        while( $rowcol=mysqli_fetch_array($querycol) ) {
			                        echo "<th>".$rowcol["Field"]."</th>";
			                        											}
			                        //******requete d'affichage de toutes les colonnes d'une table
									*/
									?>
                                        
                                        <th>Part-ID</th>
										<th>Part-Nbr</th>
										<th>Part-Desc</th>
										<th>MFG/OIM</th>
										<!--<th>Part-MFG-Old</th>-->
										<th>Aircraft</th>
										<th>Old-LP</th>
										<th>Part-List-Price</th>
										<th>Currency</th>
										<th>Part-LP-Date</th>
										<th>Remark</th>
										<th></th>
                                    </tr>
                                </thead>
                                <tbody>
								<?php
			/*
			Table tbl_Parts :::: 
Fld_Part_ID
Fld_Part_Nbr
Fld_Part_Desc
Fld_Part_MFG
Fld_Part_MFG_Old
Fld_AC_ID
Fld_Old_LP
Fld_Part_List_Price
Fld_Part_Price_Currency_ID
Fld_Part_LP_Date
Fld_Remark
*/
					$sql="SELECT * from tbl_Parts where status='Available'";
					
					//echo $sql;
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
				
							//recuperation du nom de la currency	
							// Fld_Currency_ID    Fld_Currency_Text
							if (!empty($data['Fld_Part_Price_Currency_ID'])){
						    $sqlCurrency="SELECT * FROM tbl_Currency where Fld_Currency_ID=".$data['Fld_Part_Price_Currency_ID'];
							
						    $reqCurrency = mysql2_query($sqlCurrency);
						    $dataCurrency = mysqli_fetch_array($reqCurrency);
							$currency=$dataCurrency['Fld_Currency_Text'];
							}
							else $currency="";
							//Fin recuperation du nom de la currency
							
							//recuperation du nom du aircraft	
							if (!empty($data['Fld_AC_ID'])){
							// Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
						    $sqlac="SELECT * FROM tbl_Aircraft where Fld_AC_ID=".$data['Fld_AC_ID'];
							
						    $reqac = mysql2_query($sqlac);
						    $dataac = mysqli_fetch_array($reqac);
							$Aircraft_model=$dataac['Fld_AC_Model'];
							}
							else $Aircraft_model="";
							//Fin recuperation du nom du aircraft	
							
							//recuperation du nom de la compagnie
							if (!empty($data['Fld_Part_MFG'])){
						    $sqlcn="SELECT Fld_Company_Name FROM tb_company where Fld_Company_ID=".$data['Fld_Part_MFG'];
							
						    $reqcn = mysql2_query($sqlcn);
						    $datacn = mysqli_fetch_array($reqcn);
							$companyname=$datacn['Fld_Company_Name'];
							}
							else $companyname="";
							//Fin recuperation du nom de la compagnie
				
					echo "<tr>";
					echo "<td>".$data['Fld_Part_ID']."</td>";
					echo "<td><a href='Part-Nbr.php?part_id=".$data['Fld_Part_ID']."'>".$data['Fld_Part_Nbr']."</a></td>";
					echo "<td>".$data['Fld_Part_Desc']."</td>";
					echo "<td>".$companyname."</td>";
					//echo "<td>".$data['Fld_Part_MFG_Old']."</td>";
					echo "<td>".$Aircraft_model."</td>";
					echo "<td>".$data['Fld_Old_LP']."</td>";
					echo "<td>".$data['Fld_Part_List_Price']."</td>";
					echo "<td>".$currency."</td>";
					echo "<td>".$data['Fld_Part_LP_Date']."</td>";
					echo "<td>".$data['Fld_Remark']."</td>";
					echo "<td><a href='modif_part.php?Fld_Part_ID=".$data['Fld_Part_ID']."' style='decoration:none;' title='Modification Part'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-pencil-square-o'></i>
					    </a>
						<a href='archive_part.php?Fld_Part_ID=".$data['Fld_Part_ID']."' onClick=\"return(confirm('Etes vous sur ?'));\" style='decoration:none;'  title='Archive Part'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i>
					    </a>
						</td>";
					//echo "<td><a href='javascript:detailstock(".$data['Fld_Stock_ID'].")'>Details</a></td>";
					echo "</tr>";
					}
			?>
                                </tbody>
                            </table>
							       <div style="display:none" id="bloccompany"><div id="divcompany" align="center"></div></div>   
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
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
    <script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });

<!--Details company-->	
function detailcompany(id)
{
var bloc=document.getElementById('bloccompany');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='table-row';

document.getElementById("divcompany").innerHTML='<div id="divcompany" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "detailcompany.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_courrier(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_courrier(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divcompany').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divcompany').innerHTML+=resp;
    document.getElementById('divcompany').innerHTML+='</div>';
	document.location.href="#bloccompany";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}


function fermeturedetailcompany()
{
var bloc=document.getElementById('bloccompany');
if(bloc.style.display=='table-row') bloc.style.display='none';
}
<!--Fin Details company-->


    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>