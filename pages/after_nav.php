<?php
// after_nav.php — UNIQUEMENT le backdrop + styles/JS liés à l’overlay mobile
$menuOpen = !empty($_SESSION['leftmenu']) && $_SESSION['leftmenu']==='open';
?>
<style>
:root{ --aci-topbar-h:56px; }

/* La topbar passe au-dessus du contenu */
.navbar.navbar-fixed-top{ z-index:1050; }

/* ===== Overlay mobile/tablette pour la sidebar ===== */
@media (max-width:991.98px){
  /* pousser le contenu sous la barre rouge */
  body{ padding-top:var(--aci-topbar-h); }

  /* empêcher le collapse bootstrap d'agir sur la sidebar */
  #aci-sidebar .sidebar-nav.navbar-collapse{
    display:block !important; height:auto !important; overflow:visible !important; padding:0 !important;
  }

  /* sidebar en overlay */
  .navbar-static-side, #aci-sidebar{
    position:fixed !important;
    top:var(--aci-topbar-h); left:-260px; width:260px;
    height:calc(100vh - var(--aci-topbar-h));
    overflow-y:auto; background:#f1f1f1 !important;
    z-index:1045; transition:left .25s ease;
  }
  body.menu-open #aci-sidebar{ left:0; }

  /* backdrop cliquable */
  .aci-backdrop{
    display:none; position:fixed; top:var(--aci-topbar-h); left:0; right:0; bottom:0;
    background:rgba(0,0,0,.35); z-index:1040;
  }
  body.menu-open .aci-backdrop{ display:block; }

  /* contenu plein écran en mobile */
  #page-wrapper, #page-wrapper2{ margin:60px 0 0 0 !important; }
}
</style>

<!-- Backdrop pour fermer la sidebar en mobile -->
<div class="aci-backdrop"
     onclick="(function(){
       document.body.classList.remove('menu-open');
       new Image().src='gestion_menu.php?ajax=1&open=0&REQUEST_URI=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>';
     })();"></div>

<script>
// Ajuster dynamiquement la hauteur de la topbar
(function(){
  function syncTopbarHeight(){
    var nav=document.querySelector('.navbar.navbar-fixed-top');
    var h=nav? nav.offsetHeight:56;
    document.documentElement.style.setProperty('--aci-topbar-h', h+'px');
  }
  syncTopbarHeight();
  window.addEventListener('resize', syncTopbarHeight);

  // Si la session dit "menu ouvert", on reflète en mobile
  <?php if ($menuOpen): ?>
  document.addEventListener('DOMContentLoaded', function(){
    document.body.classList.add('menu-open');
  });
  <?php endif; ?>
})();
</script>
