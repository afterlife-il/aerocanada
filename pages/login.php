<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once "confphp7.php";


// Redirection immédiate si l'utilisateur est déjà connecté
if (isset($_SESSION['conectroy']) && $_SESSION['conectroy'] === "parfait") {
    header("Location: parts.php");
    exit;
}

// Initialisation des variables à partir du formulaire
$email = $_POST["email"] ?? '';
$password = $_POST["password"] ?? '';
$conectroy = $_POST["conectroy"] ?? '';

// Si le formulaire a été soumis
if ($conectroy === "parfait" && !empty($email) && !empty($password)) {

    // Préparation de la requête sécurisée
    $stmt = mysqli_prepare($link, "SELECT * FROM tbl_Employee WHERE email = ? AND pw = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Connexion réussie : création des variables de session
        $_SESSION["conectroy"] = "parfait";
        $_SESSION["nom_utilisateur"] = $user["Employee_Name"];
        $_SESSION["id_utilisateur"] = $user["Employee_ID"];
        $_SESSION["statut"] = $user["statut"];
        $_SESSION["leftmenu"] = "open";

        header("Location: parts.php");
        exit;
    } else {
        $erreur = "❌ Login ou mot de passe incorrect.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - AeroCanada</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f2f2f2;">
    <div class="container" style="max-width: 400px; margin-top: 80px;">
        <div class="panel panel-default">
            <div class="panel-heading text-center" style="background-color: #A7142A; color: white;">
                <h3>Login</h3>
            </div>
            <div class="panel-body">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger"><?= $erreur ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="conectroy" value="parfait">
                    <div class="form-group">
                        <label for="email">Email / Username</label>
                        <input type="text" name="email" id="email" class="form-control" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
