
                        <form action="pn.php" method="post"><input type="hidden" name="act" value="stockrfq">
                                <table class="table table-striped table-bordered table-hover">
                                   
                                    <tbody>
<?php
include_once "conf.php";
include_once "page_titles.php";
			//recuperation des quantites et id produit 
					$sql="SELECT * from tb_stock_part where id_stock_part=".$_GET['id_stock_part'];
					//echo $sql;
					$req = mysql2_query($sql);
					while($data = mysqli_fetch_array($req))
					{
                                        
                                            echo "<tr><td><input type='hidden' name='id_stock_part' value='".$data['id_stock_part']."'>";
											echo "<select><option>Choisir client</option>";
											echo "</td><td><input type='hidden' name='Order_reference' value='".date("Y-m-d-His")."'>Order reference :".date("Y-m-d-His")."</td><td><input type='hidden' name='pn' value='".$data['pn']."'>".$data['pn']."</td><td>".$data['part_id']."</td><td>".$data['sn']."</td><td>".$data['condition_part']."</td><td>".$data['release_tag']."</td><td>".$data['release_tag2']."</td><td>".$data['trace']."</td><td>".$data['release_tag_date']."</td><td>".$data['date_manufacture']."</td><td>".$data['location']."</td><td>".$data['aci_po']."</td><td>".$data['moq']."</td><td><input type='submit' value='Email'></td></tr>";
					}
?>					
                                        
                                      
                                    </tbody>
                                </table>
						</Form>