<?php
//detailcompany.php
session_start();
include_once("conf.php");
if (!headers_sent()) { header('X-ACI-File: '.basename(__FILE__)); }
echo "\n<!-- ACI-FILE: ".basename(__FILE__)." -->\n";

if (!isset($_SESSION['conectroy']) || $_SESSION['conectroy'] !== "parfait") { exit('Unauthorized'); }
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "Erreur : Identifiant de compagnie invalide ou manquant.";
    exit;
}

$id = intval($_POST['id']);
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;

// Utilise l'ID reçu en POST
$id_company = $id;

require('../classes/company.class.php');

// récupération du nom de la compagnie
$sqlcomn = "SELECT * FROM tb_company WHERE Fld_Company_ID = $id_company";
$reqcomn = mysql2_query($sqlcomn);

if (!$reqcomn) {
    die("Erreur SQL sur la requête tb_company : " . mysqli_error($conn));
}

if (mysqli_num_rows($reqcomn) > 0) {
    $datacn = mysqli_fetch_assoc($reqcomn);
    $companyname = strtoupper($datacn['Fld_Company_Name']);
    $customer_payment_term_id           = (int)$datacn['customer_payment_term_id'];
    $customer_payment_term_amount       = trim($datacn['customer_payment_term_amount']); // décimal/texte
    $customer_payment_term_currencyid   =  (int)$datacn['customer_payment_term_currencyid'];

    // ✅ Mise à jour de la date d'activité dès qu'on consulte une fiche
    $now = date('Y-m-d H:i:s');
    mysqli_query($conn, "UPDATE tb_company SET last_activity = '$now' WHERE Fld_Company_ID = $id_company");

    // Validation stricte de aci_payment_term_id
$aci_payment_term_id             = isset($datacn['aci_payment_term_id']) ? (int)$datacn['aci_payment_term_id'] : 0;

    if ($aci_payment_term_id > 0) {
        $sql = "SELECT * FROM tbl_Payment WHERE Fld_Payment_Term_ID = $aci_payment_term_id";
        if (isset($debug_mode) && $debug_mode) {
            echo "Requête SQL pour aci_payment_term_id : $sql<br>";
        }

        $result = mysql2_query($sql);
        if (!$result) {
            die("Erreur SQL sur la requête tbl_Payment : " . mysqli_error($conn));
        }
    } else {
        if (isset($debug_mode) && $debug_mode) {
            echo "Erreur : aci_payment_term_id non valide ou absent.<br>";
        }
    }

    $aci_payment_term_amount         = trim($datacn['aci_payment_term_amount']);
    $aci_payment_term_currencyid     = (int)$datacn['aci_payment_term_currencyid'];

    // récupération de la date de premier contact
    $sqlrdpc = "SELECT Fld_Date_Of_First_Contact FROM tbl_Company_Details WHERE Fld_Company_ID = $id_company";
    $reqrdpc = mysql2_query($sqlrdpc);

    if (!$reqrdpc) {
        die("Erreur SQL sur la requête tbl_Company_Details : " . mysqli_error($conn));
    }

    if (mysqli_num_rows($reqrdpc) > 0) {
        $datardpc = mysqli_fetch_assoc($reqrdpc);
        $FldDateOfFirstContact = $datardpc['Fld_Date_Of_First_Contact'];
    } else {
        $FldDateOfFirstContact = "N/A";
    }
} else {
    die("Erreur : Aucune compagnie trouvée avec cet ID.");
}
?>



<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h1><?php echo $companyname;?></h1>ID : <?php echo $id_company;?><br>
                <?php if(!empty($FldDateOfFirstContact)) echo "First contact : ".$FldDateOfFirstContact; ?>
                <br>
                <a href='modif_company.php?Fld_Company_ID=<?php echo $id_company; ?>'
   style='text-decoration:none;color:white;' title='Modification Company'>
                    <i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa fa-pencil-square-o'></i>
                </a>
                <a href='ajout_contact_company.php?Fld_Company_ID=<?php echo $id_company; ?>' style='text-decoration:none;color:white;' title='Add Contact Company'>
                    <img src='images/add_contact_w.png' width='28'>
                </a>
                <a href='archive_company.php?Fld_Company_ID=<?php echo $id_company; ?>' onClick="return(confirm('Etes vous sur ?'));" style='text-decoration:none;color:white;'  title='Archive Company'>
                    <i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa fa-archive'></i>
                </a>
                <?php if ($id_company == '1182') { ?>
                    <a href='http://aerocanada-industries.com/adminaero/pages/price_list.php?Fld_Company_ID=<?php echo $id_company; ?>' style='text-decoration:none;color:white;'>
                        <i style='margin-left:10px;position: relative;top: 4px;font-size:23px;color:white;' class='fa fa-list-ul'></i>
                    </a>
                <?php } ?>
                <div style='text-align:left;'><a href='javascript:fermeturedetailcompany()'><img src='../images/Fermeture.png' width='30'></a></div>
            </div>
            <!-- .panel-heading -->
            <div class="panel-body">
                <div class="panel-group" id="accordion">

<?php
// enregistrement maakav sur company
$today = date("Y-m-d");
$heuretoday = date("g:i a");
$requete = mysql2_query("INSERT INTO tbl_maakav_company (id_maakav_company, id_company, datecomplete, heurevisite, id_Employee) VALUES (NULL, '".$id_company."', '".$today."', '".$heuretoday."', '".$_SESSION['id_utilisateur']."');");
// Fin enregistrement maakav sur company
?>

<!--*********************************************************************GENERAL INFORMATION ABOUT THE COMPANY***********************************************-->
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">GENERAL INFORMATION ABOUT THE COMPANY</a>
        </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse">
        <div class="panel-body">
<?php
// récupération des informations générales
$sql = "SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company;   
$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);

if ($nbrows > 0) {
    $data = mysqli_fetch_assoc($req);
    
// Récupération du nom de l'employé
$employee_id = isset($data['Fld_Company_BAX_Contact']) ? intval($data['Fld_Company_BAX_Contact']) : 0;
$employee_name = "No Contact2 in ACI 770";  // Valeur par défaut

if ($employee_id > 0) {
    $sqlemp = "SELECT Employee_Name FROM tbl_Employee WHERE Employee_ID = $employee_id";
  //  if ($debug_mode) {
  //      echo "Requête SQL exécutée : $sqlemp<br>";
    // }
    $reqemp = mysql2_query($sqlemp);
    if ($reqemp) {
        $dataemp = mysqli_fetch_assoc($reqemp);
        $employee_name = $dataemp['Employee_Name'];  // Si trouvé, on met à jour le nom de l'employé
    }
}

// Maintenant, le formulaire
echo "<form id='formgeneralinfo' name='formgeneralinfo' method='post' action='modif_info_general_company.php'>";
echo "<input type='hidden' name='Fld_Company_ID' value='".$id_company."'>";
echo "<div class='row'>";
echo "<div class='col-lg-4'>";
echo "<div class='form-group'><label>Company Name</label><input class=\"form-control\" type='text' name='Fld_Company_Name' value='".$datacn['Fld_Company_Name']."'></div>";
echo "<div class='form-group'><label>ACI 770 Contact</label>";
echo "<select class=\"form-control\" name=\"Employee_ID\">";
echo "<option value='0'>" . $employee_name . "</option>";  // Afficher le nom ou l'option par défaut


    
    // récupération des types de compagnie
    $sqlemp = "SELECT distinct(Employee_Name), Employee_ID FROM tbl_Employee";
    $reqemp = mysql2_query($sqlemp);
    while ($dataemp = mysqli_fetch_assoc($reqemp)) {
        echo "<option value='".$dataemp['Employee_ID']."'";
        if ($dataemp['Employee_ID'] == $datacn['aci_contact']) echo " selected";
        echo ">".$dataemp['Employee_Name']."</option>";
    }
    echo "</select></div>";
    echo "</div>";
    echo "<div class='col-lg-4'>";
    echo "<div class='form-group'><label>Company Type</label>";
    echo "<select class=\"form-control\" name=\"Fld_Company_Type_ID\">";
    
    // récupération des types de compagnie
    $sqlctt = "SELECT distinct(Fld_Company_Type_Text), Fld_Company_Type_ID FROM tbl_Company_Type";  
    $reqctt = mysql2_query($sqlctt);
    while ($datactt = mysqli_fetch_assoc($reqctt)) {
        echo "<option value='".$datactt['Fld_Company_Type_ID']."'";
        if ($datactt['Fld_Company_Type_ID'] == $data['Fld_Company_Type_ID']) echo " selected";
        echo ">".$datactt['Fld_Company_Type_Text']."</option>";
    }
    echo "</select></div>";
    echo "<div class='form-group'><label>CAGE CODE #</label><input class=\"form-control\" type='text' name='cage_code' value='".$datacn['cage_code']."'></div>";
    echo "</div>";
    echo "<div class='col-lg-4'>";
    echo "<div class='form-group'><label>Website</label><input class=\"form-control\" type='text' name='internet' value='".$datacn['internet']."'></div>";
    echo "<div class='form-group'><label>VAT Nbr</label><input class=\"form-control\" type='text' name='Fld_VAT_Nbr' value='".$data['Fld_VAT_Nbr']."'></div>";
    echo "</div>";
    echo "</div>";
    echo "<div class='row'>";
    echo "<div class='col-lg-2'><div class='form-group'><label> CUSTOMER PAYMENT TERM</label><select class=\"form-control\" name=\"customer_payment_term_id\">";
    
    // récupération Payment_Term
    $sqlpt = "SELECT * FROM tbl_Payment order by Fld_Payment_Text";
    $reqpt = mysql2_query($sqlpt);
    while ($datapt = mysqli_fetch_assoc($reqpt)) {
        echo "<option value='".$datapt['Fld_Payment_Term_ID']."'";
        if ($datapt['Fld_Payment_Term_ID'] == $customer_payment_term_id) echo " selected";
        elseif ($datapt['Fld_Payment_Text'] == 'In advance') echo " selected";
        echo ">".$datapt['Fld_Payment_Text']."</option>";
    }
    echo "</select></div>";
    echo "<div class='form-group'><label>CUSTOMER PAYMENT TERM AMOUNT</label><input class=\"form-control\" type='text' name='customer_payment_term_amount' value='".$customer_payment_term_amount."'></div>";
    echo "<div class='form-group'><label>CUSTOMER PAYMENT TERM CURRENCY $/€</label><select class=\"form-control\" name=\"customer_payment_term_currencyid\">";
    
    // récupération du nom de la currency   
    $sqldiv = "SELECT * FROM tbl_Currency";
    $reqemp = mysql2_query($sqldiv);
    while ($datadiv = mysqli_fetch_assoc($reqemp)) {
        echo "<option value='".$datadiv['Fld_Currency_ID']."'";
        if ($datadiv['Fld_Currency_ID'] == $customer_payment_term_currencyid) echo " selected";
        echo ">".$datadiv['Fld_Currency_Text']."</option>";
    }
    echo "</select></div>";
    echo "</div>";
    echo "<div class='col-lg-2'><div class='form-group'><label>ACI PAYMENT TERM</label><select class=\"form-control\" name=\"aci_payment_term_id\">";
    
    // récupération Payment_Term
    $sqlpt = "SELECT * FROM tbl_Payment order by Fld_Payment_Text";
    $reqpt = mysql2_query($sqlpt);
    while ($datapt = mysqli_fetch_assoc($reqpt)) {
        echo "<option value='".$datapt['Fld_Payment_Term_ID']."'";
        if ($datapt['Fld_Payment_Term_ID'] == $aci_payment_term_id) echo " selected";
        elseif ($datapt['Fld_Payment_Text'] == 'In advance') echo " selected";
        echo ">".$datapt['Fld_Payment_Text']."</option>";
    }
    echo "</select></div>";
    echo "<div class='form-group'><label>ACI PAYMENT TERM AMOUNT</label><input class=\"form-control\" type='text' name='aci_payment_term_amount' value='".$aci_payment_term_amount."'></div>";
    echo "<div class='form-group'><label>ACI PAYMENT TERM CURRENCY $/€</label><select class=\"form-control\" name=\"aci_payment_term_currencyid\">";
    
    // récupération du nom de la currency   
    $sqldiv = "SELECT * FROM tbl_Currency";
    $reqemp = mysql2_query($sqldiv);
    while ($datadiv = mysqli_fetch_assoc($reqemp)) {
        echo "<option value='".$datadiv['Fld_Currency_ID']."'";
        if ($datadiv['Fld_Currency_ID'] == $aci_payment_term_currencyid) echo " selected";
        echo ">".$datadiv['Fld_Currency_Text']."</option>";
    }
    echo "</select></div>";
    echo "</div>";
    echo "<div class='col-lg-4'><div class='form-group'><label>COMMENT</label><textarea class=\"form-control\" name='Fld_Remark'>".$data['Fld_Remark']."</textarea></div></div>";
    echo "<div class='col-lg-2'></div>";
    echo "<div class='col-lg-1'><br><br><button type='submit' class='btn btn-default'>Submit Button</button></div>";
    echo "<div class='col-lg-1'></div>";
    echo "</div>";
    echo "</form>";
} else {
    echo "Pas de réponse";
}
?>
        </div>
    </div>
</div>
<!--*************************************************END GENERAL INFORMATION ABOUT THE COMPANY*****************************-->


<!--************************************************************DETAILS COMPANY********************************************-->
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">COMPANY ADDRESS</a>
        </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse">
        <div class="panel-body">
<?php
$sql="SELECT * FROM tbl_Company_Details where Fld_Company_ID=".$id_company; 
$req = mysql2_query($sql);
$nbrows = mysqli_num_rows($req);

if($nbrows > 0) {
?>
<a href="javascript:addaddresscompany()"> + Add A ADDRESS</a>
<form id="formAddressCompany" name="formAddressCompany" method="post" action="gestion_address_company.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
  <table class="table table-striped table-bordered table-hover" id="tableaddressecompany">
    <thead>
        <tr>
            <th>Address Type</th>
            <th>Address Title</th>
            <th>Street</th>
            <th>City</th>
            <th>Zip Code</th>
            <th>State</th>
            <th>Country</th>
            <th>PHONE</th>
            <th>E-MAIL</th>
            <th>VAT Nbr</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $r=0;
    while ($data = mysqli_fetch_assoc($req)) { 
        $r++;
        echo "<tr>";
        echo "<td>";
        $sqltypec="SELECT Fld_Division_Text FROM tbl_Division where Fld_Division_ID='".$data['Fld_Company_Address_Type']."'";
        $reqtypec = mysql2_query($sqltypec);
        $datatypec= mysqli_fetch_assoc($reqtypec);
        echo $datatypec['Fld_Division_Text']."</td>";
        echo "<td>".$data['title_address']."</td>";
        echo "<td>".$data['Fld_Company_Street']."</td>";
        echo "<td>".$data['Fld_Company_City']."</td>";
        echo "<td>".$data['Fld_Company_ZipCode']."</td>";
        echo "<td>".$data['Fld_Company_State']."</td>";
        echo "<td>".$data['Fld_Company_Country']."</td>";
        echo "<td>".$data['Fld_Company_Phone']."</td>";
        echo "<td>".$data['Fld_Company_Email']."</td>";
        echo "<td>".$data['Fld_VAT_Nbr']."</td>";
        echo "<td><input type=\"hidden\" name=\"id_tbl_company_Details".$r."\" value='".$data['id_tbl_company_Details']."'><input type='hidden' name='Fld_Company_Type_ID".$r."' value='".$data['Fld_Company_Type_ID']."'><a href=\"javascript:modif_address_company(".$data['id_tbl_company_Details'].")\"><i class=\"fa fa-pencil-square-o\" style=\"font-size:23px;\"></i></a></td>";
        echo "</tr>";
    }
    ?> 
    </tbody>
  </table>
</div>
</form>
<div style="display:none" id="blocdetailscompany"><div id="divdetailscompany"></div></div>
<?php
} else {
    echo "Pas de réponse";
}
?>
        </div>
    </div>
</div>
<!--************************************************************END DETAILS COMPANY********************************************-->

<!--*****************************************************************CONTACT COMPANY--*********************-->                              
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">CONTACT</a>
        </h4>
        <a href="company.php?companyrating=all&details2=ok&Fld_Company_ID=<?php echo $id_company;?>" style="text-decoration:none;color:white;">
            <i style="margin-left:10px;position:relative;top:4px;font-size:23px;color:white;" class="fa fa-space-shuttle"></i>
        </a>
    </div>

    <div id="collapseThree" class="panel-collapse collapse in">
        <div class="panel-body">
        <?php
        /* Table tb_company_contact
           id_company_contact  Fld_Linked_ID  Fld_Company_ID  Company_Old_Id
           Fld_Contact_Name  Fld_Contact_Phone  Fld_Contact_Phone2
           Fld_Contact_Fax  Fld_Company_Mobile  Fld_Contact_Division_ID
           Fld_Contact_Email  Fld_Contact_Title  Fld_Contact_Remark
           status  aci_contact  entry_date
        */

        if (!function_exists('aci_contact_h')) {
            function aci_contact_h($value) {
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }
            function aci_contact_value($row, $key) {
                return isset($row[$key]) ? trim((string)$row[$key]) : '';
            }
            function aci_contact_date_text($value) {
                $value = trim((string)$value);
                if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                    return '';
                }
                return $value;
            }
            function aci_contact_link($url, $label, $iconClass) {
                $url = trim((string)$url);
                if ($url === '') return '';
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }
                return "<a href=\"".aci_contact_h($url)."\" target=\"_blank\" rel=\"noopener\"><i class=\"fa ".aci_contact_h($iconClass)."\"></i> ".aci_contact_h($label)."</a>";
            }
            function aci_contact_social_html($row) {
                $items = array();
                $fax = aci_contact_value($row, 'Fld_Contact_Fax');
                if ($fax !== '') $items[] = "Fax: ".aci_contact_h($fax);

                $whatsapp = aci_contact_value($row, 'whatsapp_number');
                if ($whatsapp !== '') {
                    $waDigits = preg_replace('/[^0-9]/', '', $whatsapp);
                    $items[] = $waDigits !== ''
                        ? "<a href=\"https://wa.me/".aci_contact_h($waDigits)."\" target=\"_blank\" rel=\"noopener\"><i class=\"fa fa-whatsapp\"></i> WhatsApp: ".aci_contact_h($whatsapp)."</a>"
                        : "WhatsApp: ".aci_contact_h($whatsapp);
                }

                $link = aci_contact_link(aci_contact_value($row, 'linkedin_url'), 'LinkedIn', 'fa-linkedin');
                if ($link !== '') $items[] = $link;
                $link = aci_contact_link(aci_contact_value($row, 'facebook_url'), 'Facebook', 'fa-facebook');
                if ($link !== '') $items[] = $link;
                $link = aci_contact_link(aci_contact_value($row, 'instagram_url'), 'Instagram', 'fa-instagram');
                if ($link !== '') $items[] = $link;

                $notes = aci_contact_value($row, 'social_network_notes');
                if ($notes !== '') $items[] = "Social notes: ".aci_contact_h($notes);

                return count($items) ? implode(" &nbsp;|&nbsp; ", $items) : "No social / WhatsApp info";
            }
        }

        // Contacts ACTIFS uniquement
        $sql = "SELECT * FROM tb_company_contact
                WHERE Fld_Company_ID=".$id_company."
                  AND status='Available'
                ORDER BY Fld_Contact_Name";

        // numformat de l’utilisateur connecté (on le récupère UNE fois)
        $sqliue  = "SELECT numformat FROM tbl_Employee WHERE Employee_ID='".$_SESSION['id_utilisateur']."'";
        $reqiue  = mysql2_query($sqliue);
        $dataiue = mysqli_fetch_assoc($reqiue);
        $numformat = (int)$dataiue['numformat'];
        ?>
        <a href="javascript:addcontactcompany()"> + Add A COMPANY CONTACT</a>

        <form id="formContactsActive" name="formContactsActive" method="post" action="valid_modif_contact_company_multi.php">
            <input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
            <input type="hidden" name="return_page" value="<?php echo $page;?>">
            <input type="hidden" name="return_anchor" value="bloccontactcompany">

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="tableaddcontactcompany">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Phone 2</th>
                            <th>Mobile / WhatsApp</th>
                            <th>E-mail</th>
                            <th>Division / Title</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $x  = 0;
                    $req = mysql2_query($sql);
                    while ($data = mysqli_fetch_assoc($req)) { 
                        $x++;

                        // plus de fond rouge ici
                        $statustab = "";

                        // format tel pour callclient
                        $tel = str_replace("+","00",$data['Fld_Contact_Phone']);
                        $tel = preg_replace('/[^0-9]/', '', $tel);
                        
                        echo "<tr id=\"row_".$data['id_company_contact']."\">";

                        echo "<td $statustab>".aci_contact_h($data['Fld_Contact_Name'])."</td>";

                        echo "<td $statustab>";
                        if (!empty($data['Fld_Contact_Phone'])) {
                            echo "<a href='#' onclick='callclient(".$tel.",".$numformat.");return false;'>
                                    <i style=\"margin-left:10px;position:relative;top:4px;font-size:23px;\" class=\"fa fa-phone\"></i>
                                  </a> ";
                        }
                        echo aci_contact_h($data['Fld_Contact_Phone'])."</td>";

                        echo "<td $statustab>".aci_contact_h($data['Fld_Contact_Phone2'])."</td>";
                        echo "<td $statustab>".aci_contact_h($data['Fld_Company_Mobile']);
                        if (aci_contact_value($data, 'whatsapp_number') !== '') {
                            echo "<br><span style='color:#187a2f;'><i class='fa fa-whatsapp'></i> ".aci_contact_h($data['whatsapp_number'])."</span>";
                        }
                        echo "</td>";

                        // Division
                        $divisionText = '';
                        $sqldiv = "SELECT Fld_Division_Text
                                   FROM tbl_Division
                                   WHERE Fld_Division_ID='".$data['Fld_Contact_Division_ID']."'";
                        $reqemp = mysql2_query($sqldiv);
                        if ($datadiv = mysqli_fetch_assoc($reqemp)) {
                            $divisionText = $datadiv['Fld_Division_Text'];
                        }
                        echo "<td $statustab><a href='mailto:".aci_contact_h($data['Fld_Contact_Email'])."'>".aci_contact_h($data['Fld_Contact_Email'])."</a></td>";
                        echo "<td $statustab>".aci_contact_h($divisionText);
                        if (trim((string)$data['Fld_Contact_Title']) !== '') {
                            echo "<br>".aci_contact_h($data['Fld_Contact_Title']);
                        }
                        echo "</td>";

                        // Actions pour un contact ACTIF : archiver + crayon
                        echo "<td id='case".$data['id_company_contact']."'>
                                <a href=\"del_contact_company.php?id_company_contact=".$data['id_company_contact'].
                                   "&Fld_Company_ID=".$id_company.
                                   "&page=".$page."&mode=archive\" 
                                   onclick=\"return confirm('Etes vous sur ?');\">
                                   <i style='margin-left:10px;position: relative;top: 4px;font-size:23px;' class='fa fa-archive'></i>
                                </a>
                                <a href=\"javascript:modif_contact_company(".$data['id_company_contact'].")\">
                                   <i style=\"margin-left:10px;position: relative;top: 4px;font-size:23px;\" class=\"fa fa-pencil-square-o\"></i>
                                </a>
                              </td>";


                        echo "</tr>";
                        $modifiedDate = aci_contact_date_text(aci_contact_value($data, 'modified_date'));
                        echo "<tr class='contact-extra-row' id=\"row_extra_".$data['id_company_contact']."\">";
                        echo "<td colspan='7' style='background:#f7f7f7;'>";
                        echo "<div style='margin-bottom:6px;'><strong>Entry date:</strong> ".aci_contact_h($data['entry_date']);
                        echo " &nbsp; | &nbsp; <strong>Modified date:</strong> ".aci_contact_h($modifiedDate !== '' ? $modifiedDate : 'Not modified yet');
                        echo "</div>";
                        echo "<div style='margin-bottom:6px;'><strong>Social / direct channels:</strong> ".aci_contact_social_html($data)."</div>";
                        echo "<textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='width:100%;height:50px;' id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".aci_contact_h($data['Fld_Contact_Remark'])."</textarea>";
                        echo "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </form>

        <div style="display:none" id="bloccontactcompany"><div id="divcontactcompany"></div></div>
        </div>
    </div>
</div>
<!--**************************************************************END CONTACT COMPANY****************************-->                                

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">CONTACT ARCHIVED</a>
        </h4>
    </div>

    <div id="collapseFour" class="panel-collapse collapse">
        <div class="panel-body">
        <?php
        // CONTACT ARCHIVED
        $sql = "SELECT * FROM tb_company_contact
                WHERE Fld_Company_ID=".$id_company."
                  AND status='none'
                ORDER BY Fld_Contact_Name";

        $req    = mysql2_query($sql);
        $nbrows = mysqli_num_rows($req);

        if ($nbrows > 0) {

            // numformat (on peut réutiliser la même requête que plus haut si tu préfères)
            $sqliue2  = "SELECT numformat FROM tbl_Employee WHERE Employee_ID='".$_SESSION['id_utilisateur']."'";
            $reqiue2  = mysql2_query($sqliue2);
            $dataiue2 = mysqli_fetch_assoc($reqiue2);
            $numformat2 = (int)$dataiue2['numformat'];
        ?>
        <form id="formContactsArchived" name="formContactsArchived" method="post" action="valid_modif_contact_company_multi.php">
            <input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
            <input type="hidden" name="return_page" value="<?php echo $page;?>">
            <input type="hidden" name="return_anchor" value="collapseFour">

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Phone 2</th>
                            <th>Mobile / WhatsApp</th>
                            <th>E-mail</th>
                            <th>Division / Title</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $x = 0;
                    $req = mysql2_query($sql);
                    while ($data = mysqli_fetch_assoc($req)) {
                        $x++;

                        // rouge pour les archivés
                        $statustab = "style='background-color:#be0831;color:#000000;'";

                        // Tel pour callclient
                        $tel = str_replace("+","00",$data['Fld_Contact_Phone']);
                        $tel = preg_replace('/[^0-9]/', '', $tel);

                        echo "<tr id=\"row_".$data['id_company_contact']."\">";

                        // Name
                        echo "<td $statustab>".aci_contact_h($data['Fld_Contact_Name'])."</td>";

                        // Phone
                        echo "<td $statustab>";
                        if (!empty($data['Fld_Contact_Phone'])) {
                            echo "<a href='#' onclick='callclient(".$tel.",".$numformat2.");return false;'>
                                    <i style='margin-left:10px;position:relative;top:4px;font-size:23px;' class='fa fa-phone'></i>
                                  </a> ";
                        }
                        echo aci_contact_h($data['Fld_Contact_Phone'])."</td>";

                        echo "<td $statustab>".aci_contact_h($data['Fld_Contact_Phone2'])."</td>";
                        echo "<td $statustab>".aci_contact_h($data['Fld_Company_Mobile']);
                        if (aci_contact_value($data, 'whatsapp_number') !== '') {
                            echo "<br><span style='color:#187a2f;'><i class='fa fa-whatsapp'></i> ".aci_contact_h($data['whatsapp_number'])."</span>";
                        }
                        echo "</td>";

                        // Division (texte)
                        $divisionText = '';
                        $sqldiv = "SELECT Fld_Division_Text
                                   FROM tbl_Division
                                   WHERE Fld_Division_ID='".$data['Fld_Contact_Division_ID']."'";
                        $reqemp = mysql2_query($sqldiv);
                        if ($datadiv = mysqli_fetch_assoc($reqemp)) {
                            $divisionText = $datadiv['Fld_Division_Text'];
                        }
                        echo "<td $statustab><a href='mailto:".aci_contact_h($data['Fld_Contact_Email'])."'>".aci_contact_h($data['Fld_Contact_Email'])."</a></td>";
                        echo "<td $statustab>".aci_contact_h($divisionText);
                        if (trim((string)$data['Fld_Contact_Title']) !== '') {
                            echo "<br>".aci_contact_h($data['Fld_Contact_Title']);
                        }
                        echo "</td>";

                        // Actions pour un contact ARCHIVÉ : restaurer + supprimer définitivement
                        echo "<td id='case".$data['id_company_contact']."'>";

                        // 1) Désarchiver → redevient Available
                        echo "<a href=\"del_contact_company.php?id_company_contact=".$data['id_company_contact'].
                               "&Fld_Company_ID=".$id_company.
                               "&page=".$page."&mode=restore\" 
                               onclick=\"return confirm('Etes vous sur ?');\">
                               Annuler
                             </a>";

                        // 2) Supprimer définitivement
                        echo " <a href=\"delete_contact_company.php?id=".$data['id_company_contact'].
                               "&Fld_Company_ID=".$id_company.
                               "&page=".$page."\" 
                               onclick=\"return confirm('Are you sure you want to DELETE this contact permanently ?');\">
                               <i class='fa fa-trash' style='margin-left:10px;position:relative;top:4px;font-size:18px;'></i>
                             </a>";

                        echo "</td>";

                        echo "</tr>";
                        $modifiedDate = aci_contact_date_text(aci_contact_value($data, 'modified_date'));
                        echo "<tr class='contact-extra-row' id=\"row_extra_".$data['id_company_contact']."\">";
                        echo "<td colspan='7' style='background:#f2d7dc;color:#000;'>";
                        echo "<div style='margin-bottom:6px;'><strong>Entry date:</strong> ".aci_contact_h($data['entry_date']);
                        echo " &nbsp; | &nbsp; <strong>Modified date:</strong> ".aci_contact_h($modifiedDate !== '' ? $modifiedDate : 'Not modified yet');
                        echo "</div>";
                        echo "<div style='margin-bottom:6px;'><strong>Social / direct channels:</strong> ".aci_contact_social_html($data)."</div>";
                        echo "<textarea class=\"form-control\" name='Fld_Contact_Remark".$data['id_company_contact']."' style='width:100%;height:50px;' id='recupmessageremark".$data['id_company_contact']."' onmouseleave='javascript:majtarea(".$data['id_company_contact'].")'>".aci_contact_h($data['Fld_Contact_Remark'])."</textarea>";
                        echo "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php
        } else {
            echo "There is no contact archived for the moment";
        }
        ?>
        </div>
    </div>
</div>
<!--**************************************************************END CONTACT COMPANY*****************************-->

    
<!--*****************************************************************COMPANY FLEET********************************-->                               
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive">FLEET</a>
                                        </h4>
                                    </div>
                                    <div id="collapseFive" class="panel-collapse collapse">
                                        <div class="panel-body">
            <?php


//**tbl_Fleet** id_Fleet  Fld_Link_Id  Fld_Company_ID  Company_Old_Id  Fld_Region  Fld_Engine  Fld_Unit  Fld_AC_ID   msn   immat

$sqlcomp="SELECT * FROM tbl_Fleet where Fld_Company_ID=".$id_company;
$reqcomp = mysql2_query($sqlcomp);
?>
<a href="javascript:addaircraft()"> + Add A AIRCRAFT</a>
<form id="formFleet" name="formFleet" method="post" action="valid_modif_aircraft_fleet.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTablefleet">
                                <thead>
                                    <tr>
                                        <th>REGION</th>
                                        <th>ENGINE</th>
                                        <th>UNIT</th>
                                        <th>AIRCRAFT</th>                                        
                                        <th>NSB#</th>                                        
                                        <th>IMMAT#</th>                                        
                                        <th></th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                    $z=0;
                    while ($datacomp = mysqli_fetch_assoc($reqcomp))
                    { 
                $z++;

                    echo "<tr id=\"row_".$z."\">";
                    echo "<td><select class=\"form-control\" name='Fld_Region".$z."'>";
                    //recuperation region
                    //** tbl_Region **  Region_ID  Region_Texte
                    $sqlreg="SELECT distinct(Region_Texte),Region_ID FROM tbl_Region order by Region_Texte";
                    
                    $reqreg = mysql2_query($sqlreg);
                    while($datareg = mysqli_fetch_assoc($reqreg))
                    {
                        echo "<option value='".$datareg['Region_ID']."'";
                        if ($datareg['Region_ID']==$datacomp['Fld_Region']) echo "selected";
                        echo ">".$datareg['Region_Texte']."</option>";
                    }
                    //Fin recuperation region
                    echo "</select></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Engine".$z."' value='".$datacomp['Fld_Engine']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Unit".$z."' value='".$datacomp['Fld_Unit']."'></td>";
                    echo "<td><select class=\"form-control\" name='Fld_AC_ID".$z."'>";
                    //recuperation Aircraft
                    // ** tbl_Aircraft ** Fld_AC_ID  Fld_AC_Model  Fld_AC_Series  Fld_AC_Manufacturer  Fld_AC_Engine_Model  Fld_AC_Engine_Series
                    $sqlrairc="SELECT distinct(Fld_AC_Model),Fld_AC_ID FROM tbl_Aircraft order by Fld_AC_Model";
                    
                    $reqrairc = mysql2_query($sqlrairc);
                    while($datarairc = mysqli_fetch_assoc($reqrairc))
                    {
                        echo "<option value='".$datarairc['Fld_AC_ID']."'";
                        if ($datarairc['Fld_AC_ID']==$datacomp['Fld_AC_ID']) echo "selected";
                        echo ">".$datarairc['Fld_AC_Model']."</option>";
                    }
                    //Fin recuperation Aircraft
                    echo "</select></td>";
                    echo "<td><input class=\"form-control\" type='text' name='msn".$z."' value='".$datacomp['msn']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='immat".$z."' value='".$datacomp['immat']."'></td>";
                    echo "<input type=\"hidden\" name=\"id_Fleet".$z."\" value=\"".$datacomp['id_Fleet']."\"><input type=\"hidden\" name=\"nbaircraft\" id=\"nbaircraft\" value=\"".$z."\">";
                    echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$z."'></td>";
                    echo "</tr>";
                    
                    }
            ?>
                                </tbody>
                            </table>
                            </form>
                                    
                                        </div>
                                        </div>
                                    </div>
                                </div>
<!--*****************************************************************END COMPANY FLEET*************************
************************************************************************************************************-->
<!--****************************************FORWARDER*******************************************************-->
                                    <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSix">FORWARDER</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSix" class="panel-collapse collapse">
                                        <div class="panel-body">
            <?php
//**tbl_Forwarder**   Fld_Linked_ID  Company_Old_Id  Fld_Company_ID  Fld_Shipper_ID  Fld_Account_Nbr Fld_Remark Fld_Shipper_Contact_Name_Forw  Fld_Shipper_Contact_Phone_Forw
//*** tbl_Shipper ***  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone

$sqlforwarder="SELECT tbl_Forwarder.*,tbl_Shipper.* FROM tbl_Forwarder,tbl_Shipper where tbl_Forwarder.Fld_Shipper_ID=tbl_Shipper.Fld_Shipper_ID AND tbl_Forwarder.Fld_Company_ID='".$id_company."'";
// echo $sqlforwarder;
$reqforwarder = mysql2_query($sqlforwarder);
?>
<a href="javascript:addforwarder()"> + Add A FORWARDER</a>
<form id="formForwarder" name="formForwarder" method="post" action="valid_modif_forwarder.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTableforw">
                                <thead>
                                    <tr>
                                        <th>SHIPPER</th>
                                        <th>SHIPPER CONTAC NAME</th>
                                        <th>SHIPPER CONTAC PHONE</th>
                                        <th>ACCOUNT #</th>
                                        <th>REMARK</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                    $w=0;
                    while ($dataforwarder = mysqli_fetch_assoc($reqforwarder))
                    { 
                $w++;

                    echo "<tr id=\"row_".$w."\">";
                    echo "<td><select class=\"form-control\" name='Fld_Shipper_ID".$w."'>";
                                    // ** tbl_Shipper **  Fld_Shipper_ID  Fld_Shipper_Text  Fld_Shipper_Contact_Name  Fld_Shipper_Contact_Phone
                                    
                                    
                                    $objet=new company();
                                    $donnee = $objet->affichage_shippers();
                                    
                                    foreach($donnee as $dataemp)
                                    {
                                        echo "<option value='".$dataemp["Fld_Shipper_ID"]."'";
                                        if((!empty($dataforwarder['Fld_Shipper_ID']))&&($dataemp["Fld_Shipper_ID"]==$dataforwarder['Fld_Shipper_ID'])) echo "selected";
                                        echo ">".$dataemp["Fld_Shipper_Text"]."<option>";
                                    }
                    echo "</select></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Name_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Name_Forw']."'><br>OLD CONTACT NAME : ".$dataforwarder['Fld_Shipper_Contact_Name']."</td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Shipper_Contact_Phone_Forw".$w."' value='".$dataforwarder['Fld_Shipper_Contact_Phone_Forw']."'><br>OLD CONTACT PHONE : ".$dataforwarder['Fld_Shipper_Contact_Phone']."</td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Account_Nbr".$w."' value='".$dataforwarder['Fld_Account_Nbr']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Remark".$w."' value='".$dataforwarder['Fld_Remark']."'></td>";
                    echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$w."\" value=\"".$dataforwarder['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbforwarder\" id=\"nbforwarder\" value=\"".$w."\">";
                    echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$w."'></td>";
                    echo "</tr>";
                    
                    }
            ?>
                                </tbody>
                            </table>
                            </form>                         
                                        </div>
                                        </div>
                                    </div>
                                </div>
<!--****************************************END FORWARDER*******************************************************-->
<!--************************************************************************************************************-->
<!--*****************************************************************BANK ACCOUNT*******************************-->                             
                                    <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSeven">BANK ACCOUNT</a>
                                        </h4>
                                    </div>
                                    <div id="collapseSeven" class="panel-collapse collapse">
                                        <div class="panel-body">
            <?php

//**tbl_Company_Bank_Account**   Fld_Linked_ID  Fld_Company_ID  Fld_Bank_Acct_Nbr  Fld_Bank_Name  Fld_Bank_Address  Fld_ABA_Routing_Nbr  Fld_Swift_Nbr  Fld_Reference  branch_nbr  bank_nbr  comments

$sqlbanka="SELECT * FROM tbl_Company_Bank_Account where Fld_Company_ID=".$id_company;
$reqbanka = mysql2_query($sqlbanka);
?>
<a href="javascript:addaba()"> + Add A BANK ACCOUNT</a>
<form id="formbankaccount" name="formbankaccount" method="post" action="valid_modif_bank_account.php">
<input type="hidden" name="Fld_Company_ID" value="<?php echo $id_company;?>">
<div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTableba">
                                <thead>
                                    <tr>
                                        <th>BANK NAME</th>
                                        <th>BANK ADDRESS</th>
                                        <th>ACCOUNT #</th>
                                        <th>BRANCH #</th>
                                        <th>BANK #</th>
                                        <th>SWIFT #</th>
                                        <th>ABA ROUTING #</th>
                                        <th>REFERENCE</th>
                                        <th>COMMENTS</th>
                                        
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                    $a=0;
                    while ($banka = mysqli_fetch_assoc($reqbanka))
                    { 
                $a++;

                    echo "<tr id=\"row_".$a."\">";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Name".$a."' value='".$banka['Fld_Bank_Name']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Address".$a."' value='".$banka['Fld_Bank_Address']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Bank_Acct_Nbr".$a."' value='".$banka['Fld_Bank_Acct_Nbr']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='branch_nbr".$a."' value='".$banka['branch_nbr']."'></td>";
                
                    echo "<td><input class=\"form-control\" type='text' name='bank_nbr".$a."' value='".$banka['bank_nbr']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Swift_Nbr".$a."' value='".$banka['Fld_Swift_Nbr']."'></td>";
                    
                    echo "<td><input class=\"form-control\" type='text' name='Fld_ABA_Routing_Nbr".$a."' value='".$banka['Fld_ABA_Routing_Nbr']."'></td>";
                    
                    echo "<td><input class=\"form-control\" type='text' name='Fld_Reference".$a."' value='".$banka['Fld_Reference']."'></td>";
                    echo "<td><input class=\"form-control\" type='text' name='comments".$a."' value='".$banka['comments']."'></td>";
                    echo "<input type=\"hidden\" name=\"Fld_Linked_ID".$a."\" value=\"".$banka['Fld_Linked_ID']."\"><input type=\"hidden\" name=\"nbbankaccount\" id=\"nbbankaccount\" value=\"".$a."\">";
                    echo "<td><input class=\"form-control\" type='submit' value='ok' name='valform".$a."'></td>";
                    echo "</tr>";
                    
                    }
            ?>
                                </tbody>
                            </table>
                            </form>
                                    </div>
                                        </div>
                                    </div>
                                </div>
<!--****************************************************END BANK ACCOUNT****************************************--> 
<!--************************************************************************************************************-->
<!--******************************************************COMPETITOR******************************************-->                               
                                    <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseEight">COMPETITOR</a>
                                        </h4>
                                    </div>
                                    <div id="collapseEight" class="panel-collapse collapse">
                                        <div class="panel-body">
            <?php

// Vérifier s'il y a une action pour ajouter un competitor (via AJAX ou formulaire POST)
// if (isset($_POST['new_competitor_id']) && intval($_POST['new_competitor_id']) > 0) {
//    $new_competitor_id = intval($_POST['new_competitor_id']);
    
//    if ($new_competitor_id > 0) {
        // Insérer le nouveau competitor dans la base de données
  //      $sql_insert_competitor = "INSERT INTO tbl_Competitor (Fld_Company_ID, Fld_Competitor_ID) VALUES ($id_company, $new_competitor_id)";
    //    $result_insert = mysql2_query($sql_insert_competitor);
      //  if ($result_insert) {
        //    echo "<p style='color: green;'>Nouveau competitor ajouté avec succès.</p>";
       // } else {
   //     }
//    }
//}

// Récupérer les competitors pour la compagnie actuelle
$sqlcompetitor = "SELECT tbl_Competitor.*, tb_company.Fld_Company_Name 
                  FROM tbl_Competitor 
                  INNER JOIN tb_company ON tbl_Competitor.Fld_Competitor_ID = tb_company.Fld_Company_ID 
                  WHERE tbl_Competitor.Fld_Company_ID = '".$id_company."' 
                  ORDER BY tb_company.Fld_Company_Name";
$reqcompetitor = mysql2_query($sqlcompetitor);

?>
<!-- Lien pour ajouter un competitor -->
<a href="#" id="add_competitor_button">+ Add A COMPETITOR</a>
<input type="hidden" id="companyid_parent" value="<?php echo (int)$id_company; ?>">

<div id="add_competitor_form" style="display:none; max-width:460px; margin-top:10px;">
  <label for="new_competitor" style="margin-bottom:4px;">Rechercher une compagnie :</label>
  <input type="text" id="new_competitor" class="form-control" placeholder="Tape le nom de la compagnie">
  <input type="hidden" id="new_competitor_id">
  <button id="submit_new_competitor" type="button" class="btn btn-default" style="margin-top:6px;">Ajouter</button>
</div>

<!-- NEW: saisie + autocomplétion competitor -->
<div class="row" id="competitor_add_row" style="margin-top:8px;">
  <div class="col-lg-4 col-md-6">
     <div class="form-group">
        <label for="competitor_name">Competitor</label>
        <input type="text"
               name="competitor_name"
               id="competitor_name"
               class="form-control companyidforcompetitor"
               placeholder="Type company name…"
               autocomplete="off">
        <input type="hidden" name="competitor_id" id="competitor_id">
     </div>
  </div>
  <div class="col-lg-2 col-md-3">
     <label>&nbsp;</label>
     <button type="button" id="btn_add_competitor" class="btn btn-default btn-block">Add</button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped table-bordered table-hover" id="dataTableCompetitor">
    <tbody>
        <?php
        $a = 0;
        while ($datacompetitor = mysqli_fetch_assoc($reqcompetitor)) { 
            $a++;
            if ($a == 1) echo "<tr>";
            echo "<td width='20%'>".$datacompetitor['Fld_Company_Name']."</td>";
            echo "<td width='5%'><a href='del_competitor.php?Fld_Linked_ID=".$datacompetitor['Fld_Linked_ID']."&Fld_Company_ID=".$id_company."' onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td>";
            if ($a == 4) { echo "</tr>"; $a = 0;
            }
        }
            if ($a > 0) echo "</tr>"; // <-- à ajouter après la boucle
        

        // Si aucun competitor n'est présent, afficher un message
        if (mysqli_num_rows($reqcompetitor) == 0) {
            echo "<tr><td colspan='4'>Aucun competitor pour cette compagnie.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

                                        </div>
                                    </div>
                                </div>
<!--*****************************************************************END COMPETITOR****************************************-->


<!--************************************************************************************************************-->
<!--******************************************************RFQ OF THE COMPANY******************************************-->                               
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseNine">RFQ OF THE COMPANY</a>
        </h4>
    </div>
    <div id="collapseNine" class="panel-collapse collapse">
        <!--************************************************-->
        <!--Verif si il y a des RFQ pour ce pn-->
        <?php
        // Vérification des RFQ pour la société
        $sqlrfq3 = "
            SELECT tbl_RFQ_1.*, tb_company.Fld_Company_Name 
            FROM tbl_RFQ_1 
            INNER JOIN tb_company ON tbl_RFQ_1.Fld_Customer_ID = tb_company.Fld_Company_ID 
            WHERE tbl_RFQ_1.Fld_Customer_ID = '".$id_company."' 
            ORDER BY ID DESC";
        
        $reqrfq3 = mysqli_query($conn, $sqlrfq3);
        if (!$reqrfq3) {
            echo "Error in SQL Query: " . mysqli_error($conn);
            exit;
        }

        $numrows_rfq = mysqli_num_rows($reqrfq3);
        ?>
        <!--Fin Verif si il y a des RFQ pour ce pn-->
        
        <!--************************************************-->
        <div class="panel-body" <?php if ($numrows_rfq == 0) { ?>style="display:none;"<?php } ?>>
            <div class="table-responsive" style="min-height:190px;height:190px;overflow:auto;">
                <strong>SELECT TO QUOTE</strong>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>RFQ ID</th>
                            <th>DATE</th>
                            <th>QTY</th>
                            <th>PN</th>
                            <th>DESCRIPTION</th>
                            <th>OBSERVATION</th>
                            <th>CUSTOMER</th>
                            <th>CUSTOMER CONTACT</th>
                            <th>RFQ TYPE</th>
                            <th>PRIORITY</th>
                            <th>TERMS</th>
                            <th>CONDITION</th>
                            <th>ACI770</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>                         
                    <?php
                    while ($datarfq3 = mysqli_fetch_assoc($reqrfq3)) {
                        // Récupération du nom du contact dans la société
                        if (!empty($datarfq3['id_company_contact'])) {
                            $sqlls = "SELECT Fld_Contact_Name FROM tb_company_contact WHERE id_company_contact = ".$datarfq3['id_company_contact'];
                            $reqls = mysqli_query($conn, $sqlls);
                            if (!$reqls) {
                                echo "Error in Contact Query: " . mysqli_error($conn);
                                continue;
                            }
                            $datals = mysqli_fetch_assoc($reqls);
                        } else {
                            $datals = ['Fld_Contact_Name' => 'N/A'];
                        }

                        // Récupération des conditions
                        if (!empty($datarfq3['Fld_Condition_ID'])) {
                            $sqlct = "SELECT Fld_Condition_Text FROM tbl_Condition WHERE Fld_Condition_ID = ".$datarfq3['Fld_Condition_ID'];
                            $reqct = mysqli_query($conn, $sqlct);
                            if (!$reqct) {
                                echo "Error in Condition Query: " . mysqli_error($conn);
                                continue;
                            }
                            $datact = mysqli_fetch_assoc($reqct);
                        } else {
                            $datact = ['Fld_Condition_Text' => 'N/A'];
                        }

                        // Récupération du type de RFQ
                        if (!empty($datarfq3['Fld_RFQ_Type_ID'])) {
                            $sqlrfqt = "SELECT Fld_RFQ_Type_Text FROM tbl_RFQ_Type WHERE Fld_RFQ_Type_ID = ".$datarfq3['Fld_RFQ_Type_ID'];
                            $reqrfqt = mysqli_query($conn, $sqlrfqt);
                            if (!$reqrfqt) {
                                echo "Error in RFQ Type Query: " . mysqli_error($conn);
                                continue;
                            }
                            $datarfqt = mysqli_fetch_assoc($reqrfqt);
                        } else {
                            $datarfqt = ['Fld_RFQ_Type_Text' => 'N/A'];
                        }

                        // Récupération de la priorité
                        if (!empty($datarfq3['Fld_Priority_ID'])) {
                            $sqlPriority = "SELECT Fld_Priority_Text FROM tbl_Priority WHERE Fld_Priority_ID = ".$datarfq3['Fld_Priority_ID'];
                            $reqPriority = mysqli_query($conn, $sqlPriority);
                            if (!$reqPriority) {
                                echo "Error in Priority Query: " . mysqli_error($conn);
                                continue;
                            }
                            $dataPriority = mysqli_fetch_assoc($reqPriority);
                        } else {
                            $dataPriority = ['Fld_Priority_Text' => 'N/A'];
                        }

                        // Récupération du nom de l'employé
                        if (!empty($datarfq3['Employee_ID'])) {
                            $sqlemp = "SELECT Employee_Name FROM tbl_Employee WHERE Employee_ID = ".$datarfq3['Employee_ID'];
                            $reqemp = mysqli_query($conn, $sqlemp);
                            if (!$reqemp) {
                                echo "Error in Employee Query: " . mysqli_error($conn);
                                continue;
                            }
                            $dataemp = mysqli_fetch_assoc($reqemp);
                        } else {
                            $dataemp = ['Employee_Name' => 'N/A'];
                        }

                        // Récupération des conditions de paiement
                        if (!empty($datarfq3['Fld_Payment_Term_ID'])) {
                            $sqlptid = "SELECT Fld_Payment_Text FROM tbl_Payment WHERE Fld_Payment_Term_ID = ".$datarfq3['Fld_Payment_Term_ID'];
                            $reqptid = mysqli_query($conn, $sqlptid);
                            if (!$reqptid) {
                                echo "Error in Payment Term Query: " . mysqli_error($conn);
                                continue;
                            }
                            $dataptid = mysqli_fetch_assoc($reqptid);
                        } else {
                            $dataptid = ['Fld_Payment_Text' => 'N/A'];
                        }

                        echo "<tr>
                                <td>".$datarfq3['Fld_RFQ_ID']."</td>
                                <td>".$datarfq3['date']."</td>
                                <td>".$datarfq3['Fld_Qty']."</td>
                                <td>".$datarfq3['pn_rfq']."</td>
                                <td>".$datarfq3['description_rfq']."</td>
                                <td>".$datarfq3['Fld_Observation']."</td>
                                <td>".$datarfq3['Fld_Company_Name']."</td>
                                <td>".$datals['Fld_Contact_Name']."</td>
                                <td>".$datarfqt['Fld_RFQ_Type_Text']."</td>
                                <td>".$dataPriority['Fld_Priority_Text']."</td>
                                <td>".$dataptid['Fld_Payment_Text']."</td>
                                <td>".$datact['Fld_Condition_Text']."</td>
                                <td>".$dataemp['Employee_Name']."</td>
                                <td></td>
                            </tr>";
                    }
                    ?>                  
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.panel-body -->
    </div>
</div>
<!--*****************************************************************END RFQ OF THE COMPANY****************************************-->



<!--************************************************************************************************************-->
<!--******************************************************DOCS ATTACHMENT******************************************-->                              
                                    <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTen">DOCS ATTACHMENT</a>
                                        </h4>
                                    </div>
                                    <div id="collapseTen" class="panel-collapse collapse">
                                        <div class="panel-body">

                                        <form action="add_docs_company.php" method="post" enctype="multipart/form-data">
             <!--  **tbl_docs_attachment_company** id_docs_attachment_company   name    docs_name   id_company  -->
            <input type="hidden" name="id_company" value="<?php echo $id_company;?>">
                            <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>DOCS NAME</label>
                                            <input class="form-control" name="docs_name" id="docs_name">

                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>DOCS</label><br><br>
                                        <input type="file" name="docsattachmentcompany" id="docsattachmentcompany">

                                        </div>
                                    </div>  
                                    <div class="col-lg-1">
                                        <div class="form-group">
                                            <label></label><br><br>
                                        <button type="submit" class="btn btn-default">SUBMIT</button>

                                        </div>
                                    </div>
                                    
                                    
                            </div>  
                        </form>
                        <table>
                        <?php
                                            //affichage info docs company
                                            //**tbl_docs_attachment_company** id_docs_attachment_company    name    docs_name   id_company
                                            $sqldacompany="SELECT * FROM tbl_docs_attachment_company where id_company='".$id_company."'";
                                            
                                            $reqdacompany = mysql2_query($sqldacompany);
                                            while($datadacompany = mysqli_fetch_assoc($reqdacompany)){
                                                echo "<tr><td><a href='../docsattachmentcompany/".$datadacompany['docs_name']."' target='_blank'>".$datadacompany['name']."</a></td>
                                            <td><a href='del_doc_company.php?id_docs_attachment_company=".$datadacompany['id_docs_attachment_company']."&id=".$id_company."' onClick=\"return(confirm('Are you sure ?'));\"><img src='images/bin-blue-full-icon.png' border='0' width='27'></a></td></tr>";
                                            }
                        ?>
                        </table>

                                        </div>
                                    </div>
                                </div>
<!--*****************************************************************END DOCS ATTACHMENT****************************************-->

                                
                                </div>
                            </div>
                            <!-- .panel-body -->
                    
                        </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

<script>
function deleteArchivedContact(idContact) {
    if (!confirm('Are you sure you want to DELETE this contact permanently ?')) {
        return false;
    }

    $.post(
        'ajax/delete_contact_company.php',
        { id_company_contact: idContact },
        function (resp) {
            // On s'attend à recevoir du JSON { ok: true } ou { ok:false, error:"..." }
            if (resp && resp.ok) {
                // on enlève visuellement la ligne
                $('#row_' + idContact).fadeOut(200, function () {
                    $(this).remove();
                });
            } else {
                alert((resp && resp.error) ? resp.error : 'Unexpected error while deleting contact.');
            }
        },
        'json'
    );

    return false;
}
</script>

            <script>
  // utilise le même endpoint que pour OEM (le tien) :
  var ENDPOINT_COMPANY = 'ajax/search_company.php'; // adapte si besoin

  // Ajoute .companyidforcompetitor au même init que OEM
  $('.companyidforoem, .companyidforcompetitor').autocomplete({
    source: function(req, res){
      $.getJSON(ENDPOINT_COMPANY, { term: req.term }, function(data){
        res(data); // [{id,label}] comme sur ton OEM
      });
    },
    minLength: 2,
    select: function (e, ui) {
      $(this).val(ui.item.label);
      if ($(this).hasClass('companyidforcompetitor')) {
        $('#competitor_id').val(ui.item.id || '');
      }
      if ($(this).hasClass('companyidforoem')) {
        $('#Fld_Part_MFG').val(ui.item.id || ''); // déjà chez toi pour OEM
      }
      return false;
    }
  });

  // Ajout du competitor (link parent -> competitor)
  $('#btn_add_competitor').on('click', function(){
    var competitorId = $('#competitor_id').val();
    var parentId     = <?php echo (int)$id_company; ?>;

    if(!competitorId){
      alert("Choisis un competitor dans la liste.");
      return;
    }

    $.post('ajax/add_competitor.php',
      { parent: parentId, competitor_id: competitorId },
      function(resp){
        if (resp && resp.ok) location.reload();
        else alert((resp && resp.error) ? resp.error : 'Erreur inconnue');
      },
      'json'
    );
  });
</script>
