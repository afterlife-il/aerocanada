<?php
include_once "conf.php";
include_once "page_titles.php";

$id_company = isset($_GET['id']) ? $_GET['id'] : '';
$companyid  = explode(",", $id_company);           // on reçoit "ID,Nom"
$Fld_Company_ID = trim($companyid[0]);

?>
<div class="form-group" id="divcontactname">
  <label>CONTACT NAME</label>

  <?php if (!empty($Fld_Company_ID)) { ?>
    <div class="input-group">
      <select class="form-control" name="Fld_Supplier_Contact_ID" id="Fld_Supplier_Contact_ID">
        <option value="">-- Select contact --</option>
        <?php
        // **tb_company_contact**
        // id_company_contact, Fld_Company_ID, Fld_Contact_Name, Fld_Contact_Email, status, ...
        // Corrige le bug de casse sur "status"
        $sqlcc = "
          SELECT id_company_contact, Fld_Contact_Name
          FROM tb_company_contact
          WHERE Fld_Company_ID = '".mysqli_real_escape_string($connection, $Fld_Company_ID)."'
            AND Fld_Contact_Name <> ''
            AND LOWER(status) = 'available'
          ORDER BY Fld_Contact_Name
        ";
        $reqcc = mysql2_query($sqlcc);
        while ($datacc = mysqli_fetch_array($reqcc)) {
          echo "<option value='".$datacc['id_company_contact']."'>".htmlspecialchars($datacc['Fld_Contact_Name'])."</option>";
        }
        ?>
      </select>
      <span class="input-group-btn">
        <button class="btn btn-default" type="button"
                id="btnAddContact"
                data-company-id="<?php echo htmlspecialchars($Fld_Company_ID); ?>">
          + Add contact
        </button>
      </span>
    </div>

    <!-- Modal d'ajout de contact -->
    <div class="modal fade" id="modalAddContact" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#A7142A;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff;font-weight:bold;">×</button>
            <h4 class="modal-title" style="color:#fff;font-weight:bold;">ADD SUPPLIER CONTACT</h4>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-6">
                  <label>Contact Name</label>
                  <input type="text" class="form-control" id="new_contact_name" placeholder="John Doe">
                </div>
                <div class="col-md-6">
                  <label>Contact Email</label>
                  <input type="email" class="form-control" id="new_contact_email" placeholder="john@company.com">
                </div>
              </div>
              <div class="row" style="margin-top:10px;">
                <div class="col-md-6">
                  <label>Contact Phone</label>
                  <input type="text" class="form-control" id="new_contact_phone" placeholder="+1 555 0000">
                </div>
              </div>
              <div class="row" id="add_contact_alert" style="display:none;margin-top:10px;">
                <div class="col-md-12">
                  <div class="alert alert-danger"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="saveNewContact">Save</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      (function(){
        // Ouvrir le modal
        $('#btnAddContact').off('click').on('click', function(){
          $('#add_contact_alert').hide();
          $('#new_contact_name').val('');
          $('#new_contact_email').val('');
          $('#new_contact_phone').val('');
          $('#modalAddContact').modal('show');
        });

        // Enregistrer le contact
        $('#saveNewContact').off('click').on('click', function(){
          var companyId = $('#btnAddContact').data('company-id');
          var name  = $('#new_contact_name').val().trim();
          var email = $('#new_contact_email').val().trim();
          var phone = $('#new_contact_phone').val().trim();

          if(!name){
            $('#add_contact_alert .alert').text('Contact name is required.');
            $('#add_contact_alert').show();
            return;
          }

          $.ajax({
            url: 'add_contact_from_popup.php',
            type: 'POST',
            data: {
              company_id: companyId,
              contact_name: name,
              contact_email: email,
              contact_phone: phone
            },
            success: function(resp){
              // Ferme le modal
              $('#modalAddContact').modal('hide');
              // Recharge la liste des contacts pour cette company
              if (typeof majtarea === 'function') {
                majtarea(); // recharge divcontactname en rappelant ce fichier
              }
            },
            error: function(){
              $('#add_contact_alert .alert').text('Server error while saving contact.');
              $('#add_contact_alert').show();
            }
          });
        });
      })();
    </script>

  <?php } else { ?>

    <br>You must choose a company

  <?php } ?>
</div>
