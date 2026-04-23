<?php
// page_titles.php
// Map des fichiers vers titres "propres"
$pageTitles = [
    'company.php'          => 'COMPANY',
    'company2.php'         => 'COMPANY',
    'suppliers_quote.php'  => 'SUPPLIERS QUOTE',
    'add_suppliers_quote.php' => 'SUPPLIERS QUOTE',
    'rfq-list.php'         => 'RFQ',
    'add_rfq.php'          => 'RFQ',
    'parts.php'            => 'PARTS',
    'ajout_parts.php'      => 'PARTS',
    'stock.php'            => 'STOCK ACI770',
    'stock_external.php'   => 'EXTERNAL STOCK',
    // 👉 ajoute ici toutes les autres pages
];

// Détecter le fichier en cours
$currentFile = basename($_SERVER['PHP_SELF']);

// Si trouvé dans la liste, sinon fallback au nom brut
$pageTitle = isset($pageTitles[$currentFile])
  ? $pageTitles[$currentFile]
  : strtoupper(pathinfo($currentFile, PATHINFO_FILENAME));
