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
                    <h1 class="page-header">Company</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            DataTables Advanced Tables
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>Logo Company</th>
                                        <th>ID Company</th>
                                        <th>COMPANY</th>
                                        <th>ACI 770 Contact</th>
										<th>RATING</th>
										<th></th>
                                    </tr>
                                </thead>
                                <tbody>
								<?php
			//recuperation des quantites et id produit 
			//Table tb_company :::: Fld_Company_ID      Company_Old_Id        Fld_Company_Name        Fld_Company_Rating_ID         delete    companyrating    aci_contact  logocompany
					$sql="SELECT * from tb_company where status='Available' order by Fld_Company_Name";
					//echo $sql;
					
					$req = mysql2_query($sql);
					while ($data = mysqli_fetch_array($req))
					{ 
				
								//recuperation nom employee
								$sqlemp="SELECT Employee_Name FROM tbl_Employee where Employee_ID=".$data['aci_contact'];
								
								$reqemp = mysql2_query($sqlemp);
								$dataemp = mysqli_fetch_array($reqemp);
								//Fin recuperation nom employee
					echo "<tr>";
					echo "<td>";
					if (!empty($data['logocompany'])) echo "<img src='../logo_company/".$data['logocompany']."' width='200'>";
					//else echo "<img src='images/No-Logo-Available.png' width='100'>";
					else echo "<div align='center'><span style='color:#be0831;font-family: \"Times New Roman\", Times, serif;font-style: oblique;font-weight: bold;'>No Logo Available</span></div>";
					echo "</td>";
					echo "<td>".$data['Fld_Company_ID']."</td>";
					echo "<td><a href='javascript:detailcompany(".$data['Fld_Company_ID'].")'>".$data['Fld_Company_Name']."</a></td>";
					echo "<td><a href='javascript:detailcompany(".$data['Fld_Company_ID'].")'>".$dataemp['Employee_Name']."</a></td>";
					//echo "<td><img src='images/".$data['companyrating'].".png' width='120'></td>";
					echo "<td>";
					if ($data['companyrating']=='5') echo "<span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span>";
					elseif ($data['companyrating']=='4') echo "<span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span>";
					
					elseif ($data['companyrating']=='3') echo "<span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span>";
					
					elseif ($data['companyrating']=='2') echo "<span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span>";
					
					elseif ($data['companyrating']=='1') echo "<span style='font-size: 45px;color:#FFA500;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span>";
					else echo "<span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span><span style='font-size: 45px;color:#aaa;'>★</span>";
					echo "</td>";
					
					echo "<td><a href='javascript:detailcompany(".$data['Fld_Company_ID'].")' style='decoration:none;' title='company details'><i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-plus-square'></i></a><a href='modif_company.php?Fld_Company_ID=".$data['Fld_Company_ID']."' style='decoration:none;' title='Modification Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-pencil-square-o'></i>
					    </a>
						<a href='ajout_contact_company.php?Fld_Company_ID=".$data['Fld_Company_ID']."' style='decoration:none;' title='Add Contact Company'>
						<img src='images/add_contact2.png' width='35'>
					    </a>
						<a href='archive_company.php?Fld_Company_ID=".$data['Fld_Company_ID']."' onClick=\"return(confirm('Etes vous sur ?'));\" style='decoration:none;'  title='Archive Company'>
						<i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa  fa-archive'></i>
					    </a></td>";
					echo "</tr>";
					//echo "<div style='display:none' id='bloc".$data['Fld_Company_ID']."'><div id='div".$data['Fld_Company_ID']."' align='center'></div></Div>";
					//echo "<tr style='display:none' id='bloc".$data['Fld_Company_ID']."'><td colspan='3'><div id='div".$data['Fld_Company_ID']."' align='center'></div></td></tr>";
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
   <script language="JavaScript" type="text/javascript">
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

<!--Statut contact company-->

function statutcontact(id){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': '#be0831',
						'color': '#ffffff',
                    });
            $.get('archiver_contact.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
			document.getElementById("case"+ id).innerHTML=" <a href=javascript:desarchivercontact("+id+")>annuler</a>";

        }
    }

	function desarchivercontact(id){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': '#ffffff',
						'color': '#333333',
                    });
            $.get('desarchivercontact.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
			document.getElementById("case"+ id).innerHTML=" <a href=javascript:statutcontact("+id+")><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-archive\"></i></a>";

        }
    }
function majtarea(id){
 
    var selection = document.getElementById("recupmessageremark").value;
    $.get('majremarkcontact.php', { // lien de la page qui permet la suppression
                id_company_contact:id,Fld_Contact_Remark:selection //variable de type GET (on recuperera la variable avec $_GET['idsup'])
            }, function(data){
              
			  
            });
/*	alert('select :'+selection);*/
 
				}  
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>