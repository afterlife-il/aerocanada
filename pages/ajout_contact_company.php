<?php
//ajout_contact_company.php
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
    <link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->
    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div id="wrapper">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
    <?php include "top_menu.php"; ?>  <!-- barre rouge avec SON burger -->
    <?php if(isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') include "left_menu.php"; ?>
  </nav>
  <?php include "after_nav.php"; ?>

  <!-- === UNIQUE wrapper de contenu === -->
  <div id="<?php echo (isset($_SESSION['leftmenu']) && $_SESSION['leftmenu']=='open') ? 'page-wrapper' : 'page-wrapper2'; ?>">

    <div class="row">
      <div class="col-lg-10">
        <h1 class="page-header">ADD CONTACT COMPANY</h1>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-10">
        <div class="panel panel-default">
          <div class="panel-heading">
            <a href="javascript:ajoutcontactcompany(2)">+Add Contact</a>
          </div>

          <form id="formajoutcontactcompany" role="form" method="post" action="valid_ajout_contact_company.php">
            <div class="panel-body">

              <div class="row">
                <div class="col-lg-12">
                  <!-- Gestion du nom company -->
                  <?php if (!empty($_GET['Fld_Company_ID'])){

                    $id_company = (int)$_GET['Fld_Company_ID'];

                    // détails company
                    $sql = "SELECT * FROM tbl_Company_Details WHERE Fld_Company_ID=".$id_company;
                    $req = mysql2_query($sql);
                    $nbrows = mysqli_num_rows($req);

                    if ($nbrows > 0){
                      // nom + logo
                      $sqlcomn = "SELECT Fld_Company_Name, logocompany FROM tb_company WHERE Fld_Company_ID=".$id_company;
                      $reqcomn = mysql2_query($sqlcomn);
                      $datacn  = mysqli_fetch_array($reqcomn);
                      echo "<div align='center'>";
                      if (!empty($datacn['logocompany'])) echo "<img src='../logo_company/".$datacn['logocompany']."' width='200'>";
                      else echo "<img src='images/No-Logo-Available.png' width='100'>";
                      echo "<h2>".$datacn['Fld_Company_Name']."</h2></div>";
                  ?>
                      <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                          <tr>
                            <th>Company Type</th>
                            <th>Country</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Street</th>
                            <th>Zip Code</th>
                            <th>Fax</th>
                            <th>Phone</th>
                            <th>E-mail</th>
                            <th>Score</th>
                            <th>ACI 770 Contact</th>
                            <th>Remark</th>
                            <th>AT Nbr</th>
                            <th>Date 1st Contact</th>
                            <th>Address Type</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                          while ($data = mysqli_fetch_array($req)) {
                            // employee
                            $sqlemp  = "SELECT Employee_Name FROM tbl_Employee WHERE Employee_ID=".$data['Fld_Company_BAX_Contact'];
                            $reqemp  = mysql2_query($sqlemp);
                            $dataemp = mysqli_fetch_array($reqemp);
                            // type
                            $sqlctt  = "SELECT Fld_Company_Type_Text FROM tbl_Company_Type WHERE Fld_Company_Type_ID=".$data['Fld_Company_Type_ID'];
                            $reqctt  = mysql2_query($sqlctt);
                            $datactt = mysqli_fetch_array($reqctt);
                            // division (address type)
                            $sqlatdiv  = "SELECT Fld_Division_Text FROM tbl_Division WHERE Fld_Division_ID=".$data['Fld_Company_Address_Type'];
                            $reqatdiv  = mysql2_query($sqlatdiv);
                            $dataatdiv = mysqli_fetch_array($reqatdiv);

                            echo "<tr>";
                            echo "<td>".$datactt['Fld_Company_Type_Text']."</td>";
                            echo "<td>".$data['Fld_Company_Country']."</td>";
                            echo "<td>".$data['Fld_Company_City']."</td>";
                            echo "<td>".$data['Fld_Company_State']."</td>";
                            echo "<td>".$data['Fld_Company_Street']."</td>";
                            echo "<td>".$data['Fld_Company_ZipCode']."</td>";
                            echo "<td>".$data['Fld_Company_Fax']."</td>";
                            echo "<td>".$data['Fld_Company_Phone']."</td>";
                            echo "<td>".$data['Fld_Company_Email']."</td>";
                            echo "<td>".$data['Fld_Company_Score']."</td>";
                            echo "<td>".$dataemp['Employee_Name']."</td>";
                            echo "<td>".$data['Fld_Remark']."</td>";
                            echo "<td>".$data['Fld_VAT_Nbr']."</td>";
                            echo "<td>".$data['Fld_Date_Of_First_Contact']."</td>";
                            echo "<td>".$dataatdiv['Fld_Division_Text']."</td>";
                            echo "<td><a href='modif_company.php?Fld_Company_ID=".$id_company."' style='decoration:none;'>
                                    <i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa fa-pencil-square-o'></i>
                                  </a></td>";
                            echo "</tr>";
                          }
                        ?>
                        </tbody>
                      </table>
                  <?php
                    } else {
                      echo "Pas de reponse";
                    }
                  ?>
                    <input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company; ?>">
                  <?php } else { ?>
                    <label>COMPANY NAME</label><br>
                    <input type="text" name="companyid" size="30" class="companyid" placeholder="Please Enter company name" required>
                  <?php } ?>
                  <!-- /Gestion du nom company -->
                </div>
              </div>

              <div class="row">
                <div class="col-lg-5">
                  <div class="form-group">
                    <label>Name</label>
                    <input class="form-control" name="Fld_Contact_Name1">
                  </div>
                  <div class="form-group">
                    <label>Phone</label>
                    <input class="form-control" name="Fld_Contact_Phone1">
                  </div>
                  <div class="form-group">
                    <label>Phone 2</label>
                    <input class="form-control" name="Fld_Contact_Phone21">
                  </div>
                  <div class="form-group">
                    <label>Fax</label>
                    <input class="form-control" name="Fld_Contact_Fax1">
                  </div>
                  <div class="form-group">
                    <label>Mobile</label>
                    <input class="form-control" name="Fld_Company_Mobile1">
                  </div>
                </div>

                <div class="col-lg-5">
                  <div class="form-group">
                    <label>Division</label>
                    <select class="form-control" name="Fld_Contact_Division_ID1">
                      <?php
                        $sqldiv="SELECT * FROM tbl_Division";
                        $reqemp = mysql2_query($sqldiv);
                        while($datadiv = mysqli_fetch_array($reqemp)){
                          echo "<option value='".$datadiv['Fld_Division_ID']."'>".$datadiv['Fld_Division_Text']."</option>";
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>E-mail</label>
                    <input class="form-control" name="Fld_Contact_Email1">
                  </div>
                  <div class="form-group">
                    <label>Title</label>
                    <input class="form-control" name="Fld_Contact_Title1">
                  </div>
                  <div class="form-group">
                    <label>Remark</label>
                    <textarea class="form-control" rows="3" name="Fld_Contact_Remark1"></textarea>
                  </div>
                </div>
              </div>

              <input type="hidden" name="nbcontactcompanyajout" value="1">
              <div style="display:none" id="bloc2"><div id="div2" align="left" style="background:#F8F8F8;padding:10px;"></div></div>

              <button type="submit" class="btn btn-default">Validate</button>
            </div><!-- /.panel-body -->
          </form>
        </div><!-- /.panel -->
      </div><!-- /.col -->
    </div><!-- /.row -->

  </div><!-- /#page-wrapper|2 -->
</div><!-- /#wrapper -->

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

<script type="text/javascript">
$(document).ready(function() {
  if ($('#dataTables-example').length) {
    $('#dataTables-example').DataTable({ responsive: true });
  }
});

// Ajout Contact Company (AJAX)
function ajoutcontactcompany(id){
  var bloc=document.getElementById('bloc'+id);
  if(bloc.style.display=='inline') bloc.style.display='none';
  else{
    bloc.style.display='inline';
    document.getElementById("div"+id).innerHTML='<div id="div'+id+'" align="center"><img src="../images_design/Spin.gif" border="0"></div>';
    var xhr=null;
    if (window.XMLHttpRequest) xhr = new XMLHttpRequest();
    else if (window.ActiveXObject) xhr = new ActiveXObject("Microsoft.XMLHTTP");
    xhr.open("POST", "addcontactcompany.php?id="+id, true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() { up_donnee_company(xhr,id); };
    xhr.send("id="+id);
  }
}
function up_donnee_company(xhr,id){
  if (xhr.readyState==4){
    document.getElementById('div'+id).innerHTML='<div id="'+id+'" align="center">';
    var resp = xhr.responseText;
    document.getElementById('div'+id).innerHTML+=resp;
    document.getElementById('div'+id).innerHTML+='</div>';
  }
}
</script>

<!-- Typeahead -->
<script src="js/typeahead.js"></script>
<style>
  .tt-hint, .companyid{
    border: 2px solid #CCCCCC; border-radius: 8px;
    font-size: 24px; height: 45px; line-height: 30px;
    padding: 8px 12px; width: 400px;
  }
  .tt-dropdown-menu{
    width:400px; margin-top:5px; padding:8px 12px;
    background-color:#F1F1F1; border:1px solid rgba(0,0,0,.2); border-radius:8px;
    font-size:18px; color:#111;
  }
</style>
<script>
$(function(){
  $('input.companyid').typeahead({
    name: 'Fld_Company_Name',
    id: 'Fld_Company_ID',
    remote: 'list-company.php?query=%QUERY'
  });
});
</script>

</body>
</html>
<?php
} else {
  echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php?url=".$_SERVER['REQUEST_URI']."\">";
}
?>
