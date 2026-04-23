<?php
// top_menu.php
// 1) Titre invisible basé sur le nom du fichier courant
$pageFile  = basename($_SERVER['PHP_SELF']);            // ex: Part-Nbr.php
$pageTitle = pathinfo($pageFile, PATHINFO_FILENAME);    // ex: Part-Nbr
echo "\n<!-- PAGE TITLE: " . htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . " -->\n";

// 2) Largeur d'écran depuis le cookie (fallback 1920)
$largeur = isset($_COOKIE['largeur']) ? (int)$_COOKIE['largeur'] : 1920;
?>


<div class="container-fluid" style="background-color:#be0831;color:#ffffff;">
  <!-- Brand + Burger -->
  <div class="navbar-header">
    <button id="aci-top-burger"
            type="button"
            class="navbar-toggle collapsed"
            data-toggle="collapse"
            data-target="#aci-topbar-collapse"
            aria-expanded="false"
            aria-controls="aci-topbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>

    <a class="navbar-brand" href="index.html" style="padding:0 20px 0 15px; margin:0;">
      <img src="images/plane-logo3.png" alt="Aero Canada" style="height:45px; margin-top:2px;">
    </a>
  </div>

  <!-- Contenu repliable -->
  <div class="collapse navbar-collapse" id="aci-topbar-collapse">
    <ul class="nav navbar-nav">

      <?php if ($largeur > 1900): ?>


      <!-- ===== MENUS COMPLETS (grands écrans) ===== -->

      <!-- PARTS -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">PARTS <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="parts.php">List of Parts</a></li>
          <li><a href="parts_wanted.php">Parts WANTED</a></li>
          <li><a href="ajout_parts.php">Add Part</a></li>
          <li><a href="add_multi_parts.php">Add Multi Parts</a></li>
          <li><a href="classement_pn.php">Classement des PN</a></li>
        </ul>
      </li>

      <!-- STOCK ACI770 -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">STOCK ACI770 <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="stock.php">Stock</a></li>
          <li><a href="ajout_stock.php">Add Stock</a></li>
        </ul>
      </li>

      <!-- EXTERNAL STOCK -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">EXTERNAL STOCK <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="stock_external.php">Stock</a></li>
          <li><a href="add_external_stock.php">Add Stock</a></li>
        </ul>
      </li>

      <!-- COMPANY -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">COMPANY <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="company.php?companyrating=all">List of Companies</a></li>
          <li><a href="competitor.php">COMPETITOR</a></li>
          <li><a href="ajout_company.php">Add Company</a></li>
          <li><a href="company_contact.php">Contacts</a></li>
          <li><a href="ajout_contact_company.php">Add Contact</a></li>
        </ul>
      </li>

      <!-- SUPPLIERS QUOTE -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SUPPLIERS QUOTE <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="suppliers_quote.php">List of SQ</a></li>
          <li><a href="add_suppliers_quote.php">Add SQ</a></li>
        </ul>
      </li>

      <!-- RFQ -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">RFQ <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="rfq-list.php">List of RFQ</a></li>
          <li><a href="add_rfq.php">Add RFQ</a></li>
          <?php if (isset($_SESSION['statut']) && $_SESSION['statut'] === "SuperAdmin"): ?>
            <li><a href="graphe_rfqs_quotations.php">GRAPHE RFQ OF THE DAY</a></li>
          <?php endif; ?>
        </ul>
      </li>

      <!-- QUOTATIONS -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">QUOTATIONS <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="quotations.php">List of QUOTE</a></li>
          <?php if (isset($_SESSION['statut']) && $_SESSION['statut'] === "SuperAdmin"): ?>
            <li><a href="graphe_rfqs_quotations.php">GRAPHE QUOTATIONS OF THE DAY</a></li>
            <li><a href="quotations_of_the_month.php">GRAPHE QUOTATIONS OF THE MONTH/ACI770</a></li>
            <li><a href="quotations_of_the_month_dd.php">GRAPHE NB OF QUOTATIONS OF THE MONTH BY DAY</a></li>
          <?php endif; ?>
        </ul>
      </li>

      <!-- CAPA -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">CAPA LIST <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="capa-list.php">List</a></li>
        </ul>
      </li>

      <!-- CONTACTS -->
      <li><a href="Contacts_JPFleet.php"><i class="fa fa-user"></i> CONTACTS / JPFLEET</a></li>
      <?php endif; // fin grands écrans ?>

      <!-- Boutons rapides (toujours visibles) -->
      <li><a href="parts.php" class="button-parts" role="button">PARTS</a></li>
      <li>&nbsp;&nbsp;</li>
      <li><a href="company.php?companyrating=all" class="button-company" role="button">COMPANY</a></li>
    </ul>

    <ul class="nav navbar-nav navbar-right">
      <?php if (isset($_SESSION['statut']) && $_SESSION['statut'] === "SuperAdmin"): ?>
      <!-- SETTINGS -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SETTINGS <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="address_type.php"><i class="fa fa-th-list"></i> Address Type</a></li>
          <li><a href="aircrafts.php"><i class="fa fa-th-list"></i> Aircrafts</a></li>
          <li><a href="company_type.php"><i class="fa fa-th-list"></i> Company Type</a></li>
          <li><a href="currency.php"><i class="fa fa-dollar"></i> Currency</a></li>
          <li><a href="release.php"><i class="fa fa-th-list"></i> Release</a></li>
          <li><a href="rfq_conditions.php"><i class="fa fa-th-list"></i> RFQ Conditions</a></li>
          <li><a href="rfq_priority.php"><i class="fa fa-th-list"></i> RFQ PRIORITY</a></li>
          <li><a href="rfq_type.php"><i class="fa fa-th-list"></i> RFQ Type</a></li>
          <li><a href="rfq_terms.php"><i class="fa fa-th-list"></i> RFQ Terms</a></li>
          <li><a href="Shippers.php"><i class="fa fa-th-list"></i> Shippers</a></li>
        </ul>
      </li>

      <!-- USERS -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">USERS <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="users.php">Users</a></li>
          <li><a href="graphe_users_day.php">Software Visitor of the Day</a></li>
          <li><a href="graphe_users_week.php">Software Visitor weekly</a></li>
          <li><a href="graphe_users.php">Software Visitor Monthly</a></li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- PROFIL / LOGOUT -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu">
          <li><a href="user_profile.php"><i class="fa fa-user fa-fw"></i> User Profile</a></li>
          <li role="separator" class="divider"></li>
          <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
        </ul>
      </li>

      <!-- BOUTON MENU GAUCHE -->
      <li style="margin-top:8px;">
        <form method="get" action="gestion_menu.php" style="display:inline;">
          <input type="hidden" name="REQUEST_URI" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
          <button id="toggleLeftMenu" class="btn btn-default">☰ MENU</button>
        </form>
      </li>
    </ul>
  </div><!-- /#aci-topbar-collapse -->
</div><!-- /.container-fluid -->

<!-- Fermer le menu du haut au chargement (vanilla JS, pas besoin de jQuery) -->

