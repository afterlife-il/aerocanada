<?php
include_once "conf.php";
include_once "page_titles.php";
$id_pn=$_GET['id'];
$idz=$_GET['idz'];
$pnid = explode(",", $id_pn);
$pnidrecup=$pnid[1]; 
/* Table tbl_Parts :::: Fld_Part_ID  Fld_Part_Nbr  Fld_Part_Desc  Fld_Part_MFG  Fld_Part_MFG_Old  Fld_AC_ID  Fld_Old_LP  Fld_Part_List_Price  Fld_Part_Price_Currency_ID  Fld_Part_LP_Date  Fld_Remark status alt_pn*/

					                        $sqlr="SELECT Fld_Part_Desc from tbl_Parts where Fld_Part_ID=".$pnidrecup;
											
											$reqr = mysql2_query($sqlr);
											$datar = mysqli_fetch_array($reqr);
											

?>
											<div class="form-group" id='divdescription<?php echo $idz;?>'>
                                            <label>DESCRIPTION</label>
                                            <input class="form-control" name="description<?php echo $idz;?>" value="<?php echo $datar['Fld_Part_Desc'];?>">
                                        </div>
											

                                        </div>