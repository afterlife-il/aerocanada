<?php
session_start();
include_once "conf.php";
if($_SESSION['conectroy']=="parfait"){
?>
<!DOCTYPE html>
<html>
	<title>Contacts / JPFleet</title>
	<head>
		<link rel="stylesheet" type="text/css" href="Contacts/css/jquery.dataTables.css">
		<script type="text/javascript" language="javascript" src="Contacts/js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="Contacts/js/jquery.dataTables.js"></script>
		
		<script type="text/javascript" language="javascript" >
			$(document).ready(function() {
				var dataTable = $('#employee-grid').DataTable( {
					"processing": true,
					"serverSide": true,
					"ajax":{
						url :"partsdata.php", // json datasource
						type: "post",  // method  , by default get
						error: function(){  // error handling
							$(".employee-grid-error").html("");
							$("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
							$("#employee-grid_processing").css("display","none");
							
						}
					}
				} );
			} );
		</script>
		<style>
			div.container {
			    margin: 0 auto;
			    <!--max-width:760px;-->
			}
			div.header {
			    margin: 30px auto;
			    line-height:30px;
			    text-align: center;
			}
			body {
			    background: #f7f7f7;
			    color: #333;
			    font: 90%/1.45em "Helvetica Neue",HelveticaNeue,Verdana,Arial,Helvetica,sans-serif;
			}
			
		<!--Menu horizontal -->
HTML  CSS   Result
Edit on 
body {
  margin: 0;
  padding: 0;
  background: #ccc;
}
 
.nav ul {
  list-style: none;
  background-color: #F8F8F8;
  text-align: center;
  padding: 0;
  margin: 0;
}
.nav li {
  font-family: 'Oswald', sans-serif;
  font-size: 1.2em;
  line-height: 40px;
  height: 40px;
  border-bottom: 1px solid #888;
}
 
.nav a {
  text-decoration: none;
  color: #444;
  display: block;
  transition: .3s background-color;
}
 
.nav a:hover {
  background-color: #be0831;
  color: #fff;
}
 
.nav a.active {
  background-color: #fff;
  color: #444;
  cursor: default;
}
 
@media screen and (min-width: 600px) {
  .nav li {
    width: 120px;
    border-bottom: none;
    height: 50px;
    line-height: 50px;
    font-size: 1.4em;
  }
 
  /* Option 1 - Display Inline */
  .nav li {
    display: inline-block;
    margin-right: -4px;
  }
 
  /* Options 2 - Float
  .nav li {
    float: left;
  }
  .nav ul {
    overflow: auto;
    width: 600px;
    margin: 0 auto;
  }
  .nav {
    background-color: #444;
  }
  */
}
		<!--Fin Menu horizontal -->
		</style>
	</head>
	<body>
	<img src="http://aerocanada-industries.com/img/new-store-logo-1500278150.jpg" style="float:left;">
	 <div class="nav">
      <ul>
        <li class="home"><a href="http://aerocanada-industries.com/adminaero/pages/list_pn.php">Home</a></li>
        <li class="news"><a href="http://aerocanada-industries.com/adminaero/pages/company.php">Company</a></li>
        <li class="contact" style="width: 180px;"><a class="active" href="http://aerocanada-industries.com/adminaero/pages/Contacts/">Contact / JPFleet</a></li>
      </ul>
    </div>
		<div class="header"><h1>Parts </h1></div>
		<div class="container">
			<table id="employee-grid"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
					<thead>
						<tr>

										<th>Part-ID</th>
										<th>PN</th>
										<!---<th>Description</th>-->
										<th>MFG-OIM</th>
										<th>Old-MFG</th>
										<th>Aircraft</th>
										<th>Part-List-Price</th>
										<th>Currency</th>
										<!--<th>Part-LP-Date</th>
										<th>Remark</th>-->
										<th>status</th>

						</tr>
					</thead>
			</table>
		</div>
	</body>
</html>
<?php
}
else echo "<meta http-equiv=\"refresh\" content=\"0; url=login.php\">";
?>