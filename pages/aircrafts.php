<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";
if($_SESSION['conectroy']=="parfait"){
require('../classes/aircraft.class.php');
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
    <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
</nav>
<?php include "after_nav.php"; ?>

  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">
    <!-- ICI ton contenu de page (le panel ADDRESS TYPE, etc.) -->
    <div class="row"> 
  </div><!-- /page-wrapper|2 -->
</div><!-- /wrapper -->

    <!-- ton contenu -->
  </div>
</div>
          <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

            <div class="row">
                <div class="col-lg-10">
                </div>
                <!-- /.col-lg-10 -->
            </div>
            <!-- /.row -->
			<?php 
			if($_SESSION['statut']=="SuperAdmin")
						{
			?>
            <div class="row">
                <div class="col-lg-10">
                   <div class="panel panel-default">
                        <div class="panel-heading">
                            AIRCRAFT <a href="javascript:addaircraft()"><img src="images/Airplane_logo.png" width="50"> + Add a aircraft</a>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
							<form action='validation_aircraft.php' method="post">
                                <table class="table table-striped table-bordered table-hover" id="mytable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Model</th>
                                            <th>Series</th>
                                            <th>Manufacturer</th>
                                            <th>Engine Model</th>
                                            <th>Engine Series</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
									$z=0;
									//************** tbl_Aircraft ************ Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
									$objet=new aircraft();
									$donnee = $objet->affichage_aircrafts();
									foreach($donnee as $dataemp)
									{
									$z++;
									$varsuivante=$z+1;
								
                                       echo "<tr class=\"odd gradeX\" id=\"row_".$dataemp["Fld_AC_ID"]."\"><td>".$dataemp["Fld_AC_ID"]."</td><td>".$dataemp['Fld_AC_Model']."</td><td>".$dataemp['Fld_AC_Series']."</td>
									   <td>".$dataemp['Fld_AC_Manufacturer']."</td><td>".$dataemp['Fld_AC_Engine_Model']."</td><td>".$dataemp['Fld_AC_Engine_Series']."<input type=\"hidden\" name=\"nbusers\" id=\"nbusers\" value=\"".$z."\"></td>";
									   echo "<td><a href=\"javascript:modif_aircraft(".$dataemp["Fld_AC_ID"].")\"><i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa  fa-pencil-square-o\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:supp_aircraft(".$dataemp["Fld_AC_ID"].",".$z.")' onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td>";
									   echo "</tr>";
									}?>       
                                    </tbody>
                                </table>
								</form>
                            </div>
                            <!-- /.table-responsive -->
							<div style="display:none" id="blocaircraft"><div id="divaircraft" align="center"></div></div>  
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-10 -->
            </div>
						<?php }?>
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

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            responsive: true
        });
    });
<!--Ajout utilisateur-->
	//fonction qui ajoute une ligne en fin de tableau pour ajouter une adresse
	function addaircraft(){
    var cell, ligne;
 
    // on recupere l'identifiant (id) de la table qui sera modifiee
    var tableau = document.getElementById("mytable");
    // nombre de lignes dans la table (avant ajout de la ligne)
    var nbLignes = tableau.rows.length;
 
    ligne = tableau.insertRow(-1); // creation d'une ligne pour ajout en fin de table
                                   // le parametre est dans ce cas (-1)
    ligne.id='row_'+eval(nbLignes+1);
    // creation et insertion des cellules dans la nouvelle ligne creee
    cell = ligne.insertCell(0);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 0";
    cell.innerHTML = eval(nbLignes+1);
	
	cell = ligne.insertCell(1);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 0";
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_AC_Model\" id=\"Fld_AC_Model\" placeholder=\"Aircraft Name\">";
	
	cell = ligne.insertCell(2);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_AC_Series\" id=\"Fld_AC_Series\" placeholder=\"Series\">";
	
	cell = ligne.insertCell(3);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_AC_Manufacturer\" id=\"Fld_AC_Manufacturer\" placeholder=\"Manufacturer\">";
	
	cell = ligne.insertCell(4);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_AC_Engine_Model\" id=\"Fld_AC_Engine_Model\" placeholder=\"Model\">";
	
	cell = ligne.insertCell(5);
    //cell.innerHTML = "Ligne " + nbLignes + " Cellule 3";
    cell.innerHTML = "<input class=\"form-control\" name=\"Fld_AC_Engine_Series\" id=\"Fld_AC_Engine_Series\" placeholder=\"Series\"><input type='hidden' name='nbusers' value='"+nbLignes+"'>";
	
	cell = ligne.insertCell(6);
    cell.innerHTML = "<input type='submit' value='submit' class=\"form-control\">";
	
}
<!--Fin Ajout utilisateur-->
<!--Supp user-->
function supp_aircraft(id,nbligne){
        if (id > 0) {
            //Execution du script PHP avec Ajax
            $('#mytable tr[id="row_' + id + '"] td').css({
                        'backgroundImage': 'none',
                        'backgroundColor': 'white',
                    });
                    $('#mytable tr[id="row_' + id + '"] td').animate({
                        'backgroundColor': '#ff8888',
                        'color': '#941010'
                    }, 1000);
            $.get('del_aircraft.php', { // lien de la page qui permet la suppression
                idsup:id //variable de type GET (on recuperera la variable avec $_GET'idsup'])
            }, function(data){
                //si la requete s'est bien deroulee
             /*   if (data == '1') {*/
                    $('#mytable tr[id="row_' + id + '"] td').fadeTo("slow", 0, function(){
                        $(this).hide();
                    });
			
                /*} else{
                    alert('Probleme de connexion a la base de donnee');
                }*/
            });

			document.getElementById("Employee_Name"+nbligne).value = '0';
        }
    }
<!--Fin Supp user-->
<!--Modification company-->
function modif_aircraft(id)
{
var bloc=document.getElementById('blocaircraft');
//if(bloc.style.display=='table-row') bloc.style.display='none';
//else
    {
bloc.style.display='table-row';

document.getElementById("divaircraft").innerHTML='<div id="divaircraft" align="center"><img src="../images/loader.gif" border="0"></div>';
           
var xhr=null;
         
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
                   
            xhr.open("POST", "modif_aircraft.php?id="+id, true);/*si jamais je veux recuperer les infos sous form de get je met les infos dans le lien cad ajax.php?variable=...*/
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() { up_donnee_courrier(xhr,id); };
            xhr.send("id="+id);/*si je veux mettre la variable sous forme de post je la met la*/
    }
}
function up_donnee_courrier(xhr,id)
{
if (xhr.readyState==4)
    {
    document.getElementById('divaircraft').innerHTML='<div id="'+id+'" align="center">';
         var resp;
        resp = xhr.responseText;
        document.getElementById('divaircraft').innerHTML+=resp;
    document.getElementById('divaircraft').innerHTML+='</div>';
	document.location.href="#blocaircraft";//je redirige le lien vers le haut de la banniere (l'ancre haut)
    }
}

<!--Fin Modification company-->
    </script>

</body>

</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
?>