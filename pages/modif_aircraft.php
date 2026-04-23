<?php

$Fld_AC_ID=$_GET['id'];
include_once "conf.php";
include_once "page_titles.php";
/**************************************************************Aircraft****************************
	//************** tbl_Aircraft ************ Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
*/
// getting total number records without any search
$sql="SELECT * FROM tbl_Aircraft where Fld_AC_ID=".$Fld_AC_ID;	

$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);
//echo $nbrows;
if(0<$nbrows){
?>
<form action="validation_modif_aircraft.php" method="post">
 <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>Aircraft ID</th>
                                        <th>Aircraft Model</th>
                                        <th>Aircraft Series</th>
                                        <th>Aircraft Manufacturer</th>
                                        <th>Engine Model</th>
                                        <th>Engine Series</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
								
								<?php
		
					while ($data = mysqli_fetch_array($req))
					{ 
					
					echo "<tr>";
					echo "<td><input type=\"hidden\" name=\"Fld_AC_ID\" value='".$data['Fld_AC_ID']."'>".$data['Fld_AC_ID']."</td>";
					echo "<td><input class=\"form-control\" name=\"Fld_AC_Model\" id=\"Fld_AC_Model\" placeholder=\"Model\" value=\"".$data['Fld_AC_Model']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"Fld_AC_Series\" id=\"Fld_AC_Series\" placeholder=\"Series\" value=\"".$data['Fld_AC_Series']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"Fld_AC_Manufacturer\" id=\"Fld_AC_Manufacturer\" placeholder=\"Fld_AC_Manufacturer\" value=\"".$data['Fld_AC_Manufacturer']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"Fld_AC_Engine_Model\" id=\"Fld_AC_Engine_Model\" placeholder=\"Fld_AC_Engine_Model\" value=\"".$data['Fld_AC_Engine_Model']."\"></td>";
					echo "<td><input class=\"form-control\" name=\"Fld_AC_Engine_Series\" id=\"Fld_AC_Engine_Series\" placeholder=\"Fld_AC_Engine_Series\" value=\"".$data['Fld_AC_Engine_Series']."\"></td>";

					echo "<td><input type='submit' class=\"form-control\" value='submit'></td>";
					echo "</tr>";
					}
			?>
								
                                </tbody>
                            </table>
							</form>
<?php		
}
else echo "Pas de reponse<br><a href='aircrafts.php'><img src='images/add_contact.png'></a>";
			//**************************************************************Fin Details Company****************************
			
		