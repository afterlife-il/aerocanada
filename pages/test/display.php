<?php
include "../conf.php";
											//recuperation des model d'avion
											// **tbl_Aircraft** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
					                        $sqlair="SELECT * FROM tbl_Aircraft order by Fld_AC_Model";
											mysql_query("SET NAMES 'utf8'");
											$reqair = mysql_query($sqlair);
											while($dataair = mysql_fetch_array($reqair)){
												echo "<option value='".$dataair['Fld_AC_ID']."'";
												if ($data["Fld_AC_ID"]==$dataair['Fld_AC_ID']) echo "selected";
												echo ">".$dataair ['Fld_AC_Model']."</option>";
											}
					                        //Fin recuperation des model d'avion
											?>