<?php
session_start();
include_once "conf.php";
include_once "page_titles.php";

// Sécurité : redirection si session invalide
if ($_SESSION['conectroy'] !== "parfait") {
    header("Location: login.php?url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Inclusion PhpSpreadsheet
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Traitement de l'import
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["excel_file"])) {
    $file = $_FILES["excel_file"]["tmp_name"];
    $idCompany = intval($_POST['id_company'] ?? 5292);

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $imported = 0;
    $results = [];

    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // ligne d'en-tête
        list($partNbr, $description, $condition, $qty) = $row;

        $partNbr = trim($partNbr);
        if (empty($partNbr)) continue;

        // Échappement
        $partNbrSql = mysqli_real_escape_string($conn, $partNbr);
        $descriptionSql = mysqli_real_escape_string($conn, $description);
        $conditionSql = mysqli_real_escape_string($conn, $condition);
        $qtyInt = intval($qty);

        // Recherche de l'ID Part
        $part = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr = '$partNbrSql'"));

        if (!$part) {
            mysqli_query($conn, "INSERT INTO tbl_Parts (Fld_Part_Nbr, Fld_Part_Desc, status, Fld_Part_LP_Date, Fld_Add_PN_Date, aci_contact_entry) 
                VALUES ('$partNbrSql', '$descriptionSql', 'Available', '".date('Y')."', '".date('Y-m-d')."', '6')");
            $partId = mysqli_insert_id($conn);
        } else {
            $partId = $part['Fld_Part_ID'];
        }

        // Ajout / maj dans tbl_surplus_inventory
        $exist = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_surplus_inventory, qty FROM tbl_surplus_inventory WHERE pn = '$partNbrSql' AND id_company = $idCompany"));

        if ($exist) {
            $newQty = $exist['qty'] + $qtyInt;
            mysqli_query($conn, "UPDATE tbl_surplus_inventory SET qty = $newQty WHERE id_surplus_inventory = " . $exist['id_surplus_inventory']);
        } else {
            mysqli_query($conn, "INSERT INTO tbl_surplus_inventory (pn, description, `condition`, qty, date_saisie, id_company) 
                VALUES ('$partNbrSql', '$descriptionSql', '$conditionSql', $qtyInt, '".date('Y-m-d')."', $idCompany)");
        }

        $imported++;
        $results[] = [$partNbr, $description, $condition, $qty];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importation Stock Compagnies</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        table { border-collapse: collapse; margin-top: 20px; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background-color: #eee; }
        .success { padding: 10px; background: #dff0d8; margin-top: 15px; border-left: 5px solid #3c763d; }
    </style>
</head>
<body>
    <h2>📥 Importer un fichier Excel / CSV pour stock externe</h2>

    <form method="post" enctype="multipart/form-data">
        <label for="excel_file">Choisissez le fichier :</label><br>
        <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required><br><br>

        <label for="id_company">ID de la compagnie :</label>
        <input type="number" name="id_company" value="5292" required><br><br>

        <button type="submit">🚀 Importer</button>
    </form>

    <h4>📝 Format attendu :</h4>
    <p>Votre fichier doit comporter une première ligne d'en-tête, et 4 colonnes suivantes dans cet ordre :</p>
    <ul>
        <li><strong>Part Number</strong></li>
        <li><strong>Description</strong></li>
        <li><strong>Condition</strong> (ex: NE, AR, OH...)</li>
        <li><strong>Quantité</strong> (entier)</li>
    </ul>

    <?php if (!empty($imported)): ?>
        <div class="success">
            ✅ <?php echo $imported; ?> lignes importées avec succès !
        </div>

        <h3>Données importées :</h3>
        <table>
            <thead>
                <tr>
                    <th>Part Number</th><th>Description</th><th>Condition</th><th>Quantité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr><td><?= htmlspecialchars($r[0]) ?></td><td><?= htmlspecialchars($r[1]) ?></td><td><?= htmlspecialchars($r[2]) ?></td><td><?= htmlspecialchars($r[3]) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
