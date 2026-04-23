<?php
// contactnamefromcompany.php
include_once "conf.php";
include_once "page_titles.php";

// On reçoit souvent "696,SABENA Technics" -> on extrait l'ID en début de chaîne
$raw = isset($_GET['id']) ? $_GET['id'] : '';
if (preg_match('/^\s*(\d+)/', $raw, $m)) {
    $Fld_Company_ID = $m[1];
} else {
    $Fld_Company_ID = '';
}
?>
<div class="form-group" id="divcontactname">
  <label>CONTACT NAME</label>

  <?php if (!empty($Fld_Company_ID)) { ?>
    <div class="input-group">
      <!-- IMPORTANT : name="id_company_contact" pour rfq.class.php -->
      <select class="form-control" name="id_company_contact" id="id_company_contact">
        <option value="">-- Select contact --</option>
        <?php
        $sql = "
          SELECT id_company_contact,
                 COALESCE(Fld_Contact_Name,'') AS Fld_Contact_Name
          FROM tb_company_contact
          WHERE Fld_Company_ID = '".mysqli_real_escape_string($conn, $Fld_Company_ID)."'
            AND COALESCE(Fld_Contact_Name,'') <> ''
            AND LOWER(status) = 'available'
          ORDER BY Fld_Contact_Name
        ";
        $req = mysql2_query($sql);
        while ($row = mysqli_fetch_array($req)) {
          $name = (string)$row['Fld_Contact_Name'];
          echo '<option value="'.$row['id_company_contact'].'">'.
                 htmlspecialchars($name, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8').
               '</option>';
        }
        ?>
      </select>

      <span class="input-group-btn">
        <button class="btn btn-default" type="button" id="btnAddContact2"
                data-company-id="<?php echo htmlspecialchars($Fld_Company_ID); ?>">
          + Add contact
        </button>
      </span>
    </div>

    <!-- Mini modal pour créer un contact -->
    <div class="modal fade" id="modalAddContact2" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header" style="background:#A7142A;">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"
                  style="color:#fff;font-weight:bold;">×</button>
          <h4 class="modal-title" style="color:#fff;font-weight:bold;">ADD SUPPLIER CONTACT</h4>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6">
                <label>Contact Name</label>
                <input type="text" class="form-control" id="new_contact_name2">
              </div>
              <div class="col-md-6">
                <label>Contact Email</label>
                <input type="email" class="form-control" id="new_contact_email2">
              </div>
            </div>
            <div class="row" style="margin-top:10px;">
              <div class="col-md-6">
                <label>Contact Phone</label>
                <input type="text" class="form-control" id="new_contact_phone2">
              </div>
            </div>
            <div class="row" id="add_contact_alert2" style="display:none;margin-top:10px;">
              <div class="col-md-12">
                <div class="alert alert-danger"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveNewContact2">Save</button>
        </div>
      </div></div>
    </div>

    <script>
      (function(){
        $('#btnAddContact2').off('click').on('click', function(){
          $('#add_contact_alert2').hide();
          $('#new_contact_name2, #new_contact_email2, #new_contact_phone2').val('');
          $('#modalAddContact2').modal('show');
        });

        $('#saveNewContact2').off('click').on('click', function(){
          var companyId = $('#btnAddContact2').data('company-id');
          var name  = $('#new_contact_name2').val().trim();
          var email = $('#new_contact_email2').val().trim();
          var phone = $('#new_contact_phone2').val().trim();

          if (!name){
            $('#add_contact_alert2 .alert').text('Contact name is required.');
            $('#add_contact_alert2').show();
            return;
          }

          $.ajax({
            url: 'add_contact_from_popup.php',
            type: 'POST',
            data: {
              company_id:    companyId,
              contact_name:  name,
              contact_email: email,
              contact_phone: phone
            },
            success: function(){
              $('#modalAddContact2').modal('hide');
              // Recharge la liste pour cette company
              if (typeof majtarea === 'function') { majtarea(); }
            },
            error: function(){
              $('#add_contact_alert2 .alert').text('Server error while saving contact.');
              $('#add_contact_alert2').show();
            }
          });
        });
      })();
    </script>

  <?php } else { ?>
    <br>You must choose a company
  <?php } ?>
</div>
