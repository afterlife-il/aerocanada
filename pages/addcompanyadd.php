 <?php
 session_start();
include_once "conf.php";
include_once "page_titles.php";
$id=$_GET['id'];
$idsuivant=$id+1;
?>
									<b><i><u>Address <?php echo $id;?></u></i></b> <a href="javascript:ajoutaddcompany(<?php echo $idsuivant;?>)">+Add address</a>
										<div class="row">
							
								<div class="col-lg-5">
									<div class="form-group">
                                            <label>Address Title</label>
                                            <input class="form-control" name="title_address<?php echo $id;?>">
                                    </div>
									<div class="form-group">
                                            <label>Street</label>
                                            <input class="form-control" name="Fld_Company_Street<?php echo $id;?>">
                                        </div>
									<div class="form-group">
                                            <label>City</label>
                                            <input class="form-control" name="Fld_Company_City<?php echo $id;?>">
                                        </div>
										<div class="form-group">
                                            <label>Zip Code</label>
                                            <input class="form-control" name="Fld_Company_ZipCode<?php echo $id;?>">
                                    </div>
									<div class="form-group">
                                            <label>State</label>
                                            <input class="form-control" name="Fld_Company_State<?php echo $id;?>">
                                        </div>
									<div class="form-group">
                                            <label>Country</label>
                                            <input class="form-control" name="Fld_Company_Country<?php echo $id;?>">
                                        </div>
										<div class="form-group">
                                            <label>Address Type</label>
                                            <select class="form-control" name="Fld_Company_Address_Type<?php echo $id;?>">
											<option></option>
											<?php
											//recuperation des types de compagnie
											//** tbl_Division ** Fld_Division_ID  Fld_Division_Text
					                        $sqlctt="SELECT * FROM tbl_Division";	
					                        $reqctt = mysql2_query($sqlctt);
					                        while($datactt = mysqli_fetch_array($reqctt)){
												echo "<option value='".$datactt['Fld_Division_ID']."'>".$datactt['Fld_Division_Text']."</option>";
											}
												
					                        //Fin recuperation des type de compagnie
											?>
                                                
                                            </select>
									</div>
									
								</div>
								<div class="col-lg-5">
										<div class="form-group">
                                            <label>E-mail</label>
                                            <input class="form-control" name="Fld_Company_Email<?php echo $id;?>">
                                        </div>
										<div class="form-group">
                                            <label>Phone</label>
                                            <input class="form-control" name="Fld_Company_Phone<?php echo $id;?>">
                                        </div>
									<div class="form-group">
                                            <label>Fax</label>
                                            <input class="form-control" name="Fld_Company_Fax<?php echo $id;?>">
                                    </div>

										<div class="form-group">
                                            <label>Remark</label>
                                            
											<textarea class="form-control" rows="3" name="Fld_Remark<?php echo $id;?>"></textarea>
                                        </div>
										<div class="form-group">
                                            <label>Timezone</label>
                                            <select class="form-control" name="UTC_timezone<?php echo $id;?>">
											<?php
											//recuperation du fuseau horaire

											/**
											* Timezones list with GMT offset
											*
											* @return array
											* @link http://stackoverflow.com/a/9328760
											*/
											function tz_list() {
											$zones_array = array();
											$timestamp = time();
											foreach(timezone_identifiers_list() as $key => $zone) {
												date_default_timezone_set($zone);
												$zones_array[$key]['zone'] = $zone;
												$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
											}
											return $zones_array;
											}
											
					                        //Fin recuperation du fuseau horaire
											?>
    											<option value="0">Please, select timezone</option>
    											<?php foreach(tz_list() as $t) { ?>
    											  <option value="<?php print $t['zone'] ?>">
    											    <?php print $t['diff_from_GMT'] . ' - ' . $t['zone'] ?>
    											  </option>
    											<?php } ?>
                                            </select>
										</div>
										<input type="hidden" name="Fld_Date_Of_First_Contact<?php echo $id;?>" value="<?php echo date('d-M-y');?>" />
										
									
                                </div>
            
                                <!-- /.col-lg-5 (nested) -->
                            </div>
										
										<input type="hidden" name="nbaddcompany" value="<?=$id;?>">
										<div style='display:none;' id='bloc<?=$idsuivant;?>'><div id='div<?=$idsuivant;?>' align='left'></div>
										</div>