		<nav class="navbar navbar-default  navbar-fixed-top" style="background-color:#fff;">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button id="aci-top-burger" type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Aerocanada</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
	  <!--
        <li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
        <li><a href="#">Link</a></li>
		-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Parts <span class="caret"></span></a>
          <ul class="dropdown-menu">
             <li><a href="parts.php">List of Parts</a></li>
			<li><a href="ajout_parts.php">Add Part</a></li>
			<li><a href="aircrafts.php">Aircrafts</a></li>
          </ul>
        </li>
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Stock <span class="caret"></span></a>
          <ul class="dropdown-menu">
             <li><a href="stock.php">Stock</a></li>
			 <li><a href="ajout_stock.php">Add Stock</a></li>
          </ul>
        </li>
		<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Company <span class="caret"></span></a>
          <ul class="dropdown-menu">
              <li><a href="company.php">List of Companies</a></li>
			  <li><a href="ajout_company.php">Add Company</a></li>
			  <li><a href="ajout_contact_company.php">Add Contact</a></li>
          </ul>
        </li>
		<li><a href="http://aerocanada-industries.com/adminaero/pages/Contacts/"><i class="fa fa-user "></i> Contacts / JPFleet</a></li>
		<?php 
						if($_SESSION['statut']=="SuperAdmin")
						{
							?>
						<li>
                            <a href="users.php"><i class="fa fa-user "></i> Users</a>
                        </li>
						<li>
                            <a href="currency.php"><i class="fa fa-dollar "></i> Currency</a>
                        </li>
						<?php }?>
      </ul>
	  <!--
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
	  -->
      <ul class="nav navbar-nav navbar-right">
        <li><a href="user_profile.php"><i class="fa fa-user fa-fw"></i> User Profile</a></li>
        <li class="divider"></li>
        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<script>
  // When the top burger is clicked, slide the left sidebar in/out
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#aci-top-burger');
    if(!btn) return;
    document.body.classList.toggle('aci-side-open');
  });
</script>
