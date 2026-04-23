<?php
// add_part_simple.php (ou le fichier actuel qui reçoit ?pn=...&desc=...)
include_once "conf.php";
include_once "page_titles.php";

// Accepte GET (on conserve ton contrat)
if (isset($_GET['pn'], $_GET['desc'])) {
    $pn   = trim($_GET['pn']);
    $desc = trim($_GET['desc']);

    // garde-fous simples
    if ($pn !== '' && $desc !== '') {
        // Optionnel: borne les tailles si tu as des limites en base
        // $pn   = mb_substr($pn,   0, 128);
        // $desc = mb_substr($desc, 0, 255);

        // INSERT sécurisé - on NE met PAS Fld_Part_ID (AUTO_INCREMENT)
        if ($stmt = mysqli_prepare($link,
            "INSERT INTO tbl_Parts (Fld_Part_Nbr, Fld_Part_Desc) VALUES (?, ?)")
        ) {
            mysqli_stmt_bind_param($stmt, "ss", $pn, $desc);
            mysqli_stmt_execute($stmt);
            // Si tu veux détecter un doublon (si index unique sur Fld_Part_Nbr) :
            // if (mysqli_errno($link) == 1062) { /* PN déjà présent */ }
            mysqli_stmt_close($stmt);
        }
        // pas d'echo volontairement -> même comportement qu'avant
    }
}
