<?php
// top_menu_router.php
// Route le bon gabarit de menu selon la largeur CSS (via cookie) + override URL.
// Objectif : éviter les faux positifs sur Mac/Retina/zoom.

// --- démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- PRIORITÉ 1 : override manuel par URL ?menu=full|compact
if (isset($_GET['menu'])) {
    $forced = ($_GET['menu'] === 'compact') ? 'compact' : 'full';
    $_SESSION['aci_menu_mode'] = $forced;
    // 1 an - chemin racine - SameSite=Lax (ok pour Safari/Chrome/Firefox)
    setcookie('aci_menu_mode', $forced, [
        'expires'  => time() + 31536000,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
}

// --- PRIORITÉ 2 : cookie existant
if (!isset($_SESSION['aci_menu_mode'])) {
    if (!empty($_COOKIE['aci_menu_mode'])) {
        $_SESSION['aci_menu_mode'] = ($_COOKIE['aci_menu_mode'] === 'compact') ? 'compact' : 'full';
    }
}

// --- Si rien n'est encore défini, par défaut "full"
if (!isset($_SESSION['aci_menu_mode'])) {
    $_SESSION['aci_menu_mode'] = 'full';
}

// --- Possibilité de forcer "full" pour les postes administrateurs
// if (isset($_SESSION['statut']) && $_SESSION['statut'] === 'admin') {
//     $_SESSION['aci_menu_mode'] = 'full';
// }

// --- Inclure le bon fichier existant
//   - ton "full" = l’actuel menu complet (ex : top_menu.php)
//   - ton "compact" = l’actuel menu réduit (ex : top_menu2.php)
$mode = $_SESSION['aci_menu_mode'];
if ($mode === 'compact') {
    include_once __DIR__ . '/top_menu2.php';
} else {
    include_once __DIR__ . '/top_menu.php';
}
?>
<!-- Sync de la décision côté navigateur pour les prochaines pages -->
<script>
// Détecte le breakpoint via CSS (fiable) plutôt que des pixels bruts.
// Ici, "full" si >= 1024px (tu peux ajuster à 992, 1200, etc.)
(function() {
  try {
    var full = window.matchMedia('(min-width: 1024px)').matches;
    var wanted = full ? 'full' : 'compact';

    // Lire le cookie aci_menu_mode
    var current = (document.cookie.match(/(?:^|;\s*)aci_menu_mode=([^;]+)/)||[])[1];

    if (current !== wanted) {
      document.cookie = 'aci_menu_mode='+wanted+'; Max-Age=31536000; Path=/; SameSite=Lax';
      // Si on change réellement de mode, on recharge une seule fois pour uniformiser.
      // Mais seulement si le serveur n'a pas déjà inclus l'autre gabarit.
      // On évite la boucle avec un flag JS.
      if (!window.__aci_menu_synced__) {
        window.__aci_menu_synced__ = true;
        location.reload();
      }
    }

    // Recalcule à la volée si l’utilisateur redimensionne/zoome.
    var R;
    window.addEventListener('resize', function(){
      clearTimeout(R);
      R = setTimeout(function(){
        var fullNow = window.matchMedia('(min-width: 1024px)').matches;
        var w = fullNow ? 'full' : 'compact';
        var cur = (document.cookie.match(/(?:^|;\s*)aci_menu_mode=([^;]+)/)||[])[1];
        if (cur !== w) {
          document.cookie = 'aci_menu_mode='+w+'; Max-Age=31536000; Path=/; SameSite=Lax';
          if (!window.__aci_menu_synced__) {
            window.__aci_menu_synced__ = true;
            location.reload();
          }
        }
      }, 200);
    });
  } catch(e) { /* silencieux */ }
})();
</script>
