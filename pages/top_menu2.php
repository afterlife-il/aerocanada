<!--menu horizontal roy -->
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#" style="color:#A7142A;">AEROCANADA</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
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
      
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">STOCK ACI770 <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="stock.php">Stock</a></li>
			<li><a href="ajout_stock.php">Add Stock</a></li>
          </ul>
        </li>
      
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">EXTERNAL STOCK <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="stock_external.php">Stock</a></li>
			<li><a href="add_external_stock.php">Add Stock</a></li>
          </ul>
        </li>
      
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

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SUPPLIERS QUOTE <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="suppliers_quote.php">List of SQ</a></li>
			<li><a href="add_suppliers_quote.php">Add SQ</a></li>
          </ul>
        </li>
		
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">RFQ <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="rfq-list.php">List of RFQ</a></li>
			<li><a href="add_rfq.php">Add RFQ</a></li>
			<?php if($_SESSION['statut']=="SuperAdmin"){ ?>
			<li><a href="graphe_rfqs_quotations.php">GRAPHE RFQ OF THE DAY</a></li>
			<?php }?>	
          </ul>
        </li>
			
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">QUOTATIONS <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="quotations.php">List of QUOTE</a></li>
			<?php 
						if($_SESSION['statut']=="SuperAdmin")
						{
			?>
			<li><a href="graphe_rfqs_quotations.php">GRAPHE QUOTATIONS OF THE DAY</a></li>
			<li><a href="quotations_of_the_month.php">GRAPHE QUOTATIONS OF THE MONTH/ACI770</a></li>
			<li><a href="quotations_of_the_month_dd.php">GRAPHE NB OF QUOTATIONS OF THE MONTH BY DAY</a></li>
				<?php }?>	
          </ul>
        </li>
	  
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">CAPA LIST <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="capa-list.php">List</a></li>
          </ul>
        </li>
	  <!--
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
	  -->
	
		<li><a href="Contacts_JPFleet.php"><i class="fa fa-user "></i> CONTACTS / JPFLEET</a> </li>
	  </ul>
      <ul class="nav navbar-nav navbar-right">
		
		<?php 
						if($_SESSION['statut']=="SuperAdmin")
						{
							?>
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SETTINGS <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="address_type.php"><i class="fa fa-th-list "></i> Address Type</a></li>
			<li><a href="aircrafts.php"><i class="fa fa-th-list "></i> Aircrafts</a></li>
			<li><a href="company_type.php"><i class="fa fa-th-list "></i> Company Type</a></li>
			<li><a href="currency.php"><i class="fa fa-dollar "></i> Currency</a></li>
			<li><a href="release.php"><i class="fa fa-th-list "></i> Release</a></li>
			<li><a href="rfq_conditions.php"><i class="fa fa-th-list "></i> RFQ Conditions</a></li>
			<li><a href="rfq_priority.php"><i class="fa fa-th-list "></i> RFQ PRIORITY</a></li>
			<li><a href="rfq_type.php"><i class="fa fa-th-list "></i> RFQ Type</a></li>
			<li><a href="rfq_terms.php"><i class="fa fa-th-list "></i> RFQ Terms</a></li>
			<li><a href="Shippers.php"><i class="fa fa-th-list "></i> Shippers</a></li>
          </ul>
        </li>
		
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SUSERS <span class="caret"></span></a>
          <ul class="dropdown-menu">
			<li><a href="users.php">Users</a></li>
			<li><a href="graphe_users_day.php">Software Visitor of the Day</a></li>
			<li><a href="graphe_users_week.php">Software Visitor weekly</a></li>
			<li><a href="graphe_users.php">Software Visitor Monthly</a></li>
		  </ul>
		</li>  
		<?php }?>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i></a>
          <ul class="dropdown-menu">
             <li><a href="user_profile.php"><i class="fa fa-user fa-fw"></i> User Profile</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
<!--Fin menu horizontal roy -->