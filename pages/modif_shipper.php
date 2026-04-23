<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

/**
 * Page: modif_shipper.php
 * Reçoit: GET id
 * Sécurise: ID numérique, messages propres
 */

$Fld_Shipper_ID = isset($_GET['id']) ? (int)$_GET['id']
               : (isset($_POST['Fld_Shipper_ID']) ? (int)$_POST['Fld_Shipper_ID'] : 0);

if ($Fld_Shipper_ID <= 0) {
    echo "<div class='alert alert-warning'>Shipper ID manquant ou invalide.</div>";
    echo "<p><a class='btn btn-default' href='Shippers.php'>&larr; Retour à la liste</a></p>";
    exit;
}

$sql = "SELECT * FROM tbl_Shipper WHERE Fld_Shipper_ID = " . $Fld_Shipper_ID;
$req = mysql2_query($sql);
if ($req === false) {
    echo "<div class='alert alert-danger'>Erreur SQL lors du chargement.</div>";
    echo "<p><a class='btn btn-default' href='Shippers.php'>&larr; Retour à la liste</a></p>";
    exit;
}

if (mysqli_num_rows($req) < 1) {
    echo "Pas de réponse pour l’ID demandé.<br><a href='Shippers.php'><img src='images/add_contact.png' alt='Retour'></a>";
    exit;
}

$data = mysqli_fetch_assoc($req);
$id   = (int)$data['Fld_Shipper_ID'];
$name = isset($data['Fld_Shipper_Text']) ? htmlspecialchars($data['Fld_Shipper_Text'], ENT_QUOTES, 'UTF-8') : '';
?>
<form action="validation_modif_shipper.php" method="post">
  <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
    <thead>
      <tr>
        <th>#</th>
        <th>Shipper Name</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <input type="hidden" name="Fld_Shipper_ID" value="<?php echo $id; ?>">
          <?php echo $id; ?>
        </td>
        <td>
          <input class="form-control" name="Fld_Shipper_Text" id="Fld_Shipper_Text"
                 placeholder="Shipper Name" value="<?php echo $name; ?>">
        </td>
        <td>
          <button type="submit" class="btn btn-primary">Submit</button>
        </td>
      </tr>
    </tbody>
  </table>
</form>
<p><a class="btn btn-default" href="Shippers.php">&larr; Retour à la liste</a></p>
