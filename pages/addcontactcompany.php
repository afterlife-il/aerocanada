 <?php
 //addcontactcompany.php
 session_start();
include_once "conf.php";
include_once "page_titles.php";
$id=$_GET['id'];
$idsuivant=$id+1;
?>
									<b><i><u>Contact <?php echo $id;?></u></i></b> <a href="javascript:ajoutcontactcompany(<?php echo $idsuivant;?>)">+Add Contact</a>	
									<br><br>
	
	<div class="row">
							
								<div class="col-lg-5">
								
									<div class="form-group">
                                            <label>Contact Name</label>
                                            <input class="form-control" name="Fld_Contact_Name<?php echo $id;?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Phone</label>
                                            <input class="form-control" name="Fld_Contact_Phone<?php echo $id;?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Phone 2</label>
                                            <input class="form-control" name="Fld_Contact_Phone2<?php echo $id;?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Fax</label>
                                            <input class="form-control" name="Fld_Contact_Fax<?php echo $id;?>">
                                        </div>
									
									<div class="form-group">
                                            <label>Company Mobile</label>
                                            <input class="form-control" name="Fld_Company_Mobile<?php echo $id;?>">
                                    </div>
									
								</div>
								<div class="col-lg-5">

										<div class="form-group">
                                            <label>Contact Division</label>
                                            <select class="form-control" name="Fld_Contact_Division_ID<?php echo $id;?>">
											<?php
											//recuperation du nom de la division	
											 //Fld_Division_ID    Fld_Division_Text
											$sqldiv="SELECT * FROM tbl_Division";
											
											//echo $sqldiv;
											$reqemp = mysql2_query($sqldiv);
											while($datadiv = mysqli_fetch_array($reqemp))
											{
												echo "<option value='".$datadiv ['Fld_Division_ID']."'>".$datadiv ['Fld_Division_Text']."</option>";
											}
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
                                        </div>
									
									<div class="form-group">
                                            <label>Contact Email</label>
                                            <input class="form-control" name="Fld_Contact_Email<?php echo $id;?>">
                                        </div>
									<div class="form-group">
                                            <label>Contact Title</label>
                                            <input class="form-control" name="Fld_Contact_Title<?php echo $id;?>">
                                        </div>
										<div class="form-group">
                                            <label>Contact Remark</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Contact_Remark<?php echo $id;?>"></textarea>
                                        </div>
										
									
                                </div>
										
                                <!-- /.col-lg-5 (nested) -->
                            </div>
							
							<input type="hidden" name="nbcontactcompanyajout" value="<?=$id;?>">
										<div style='display:none' id='bloc<?=$idsuivant;?>'><div id='div<?=$idsuivant;?>' align='left' style="background:#F8F8F8;padding:10px;"></div>
										</div>