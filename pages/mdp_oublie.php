<!DOCTYPE html>
<?php
$messageres="";
if(!empty($_POST['email'])) {
	//echo "enter";
$email = htmlentities($_POST['email']);
include_once "conf.php";
include_once "page_titles.php";
 $req="SELECT * FROM tbl_Employee where email='".$email."'";
 //echo $req;
 $requete=mysql2_query($req);
 $num_rows = mysqli_num_rows($requete);
 if ($num_rows>'0'){
	 //echo "enter";
$reponse=mysqli_fetch_array($requete);		 

$Employee_Name_bdd=$reponse['Employee_Name'];
$email_bdd=$reponse['email'];
$pw_bdd=$reponse['pw'];


//Envoi email avec login et mdp
	$subject = 'Aerocanada : Email Recovery of Your Connection Information';
     $message = 'Your login information
	 Email: '.$email_bdd.'
	 Mot de passe : '.$pw_bdd;
    $headers = 'From: Aerocanada <lamalol@gmail.com>' . "\r\n" .
     'Reply-To: lamalol@gmail.com' . "\r\n" .
     'X-Mailer: PHP/' . phpversion();

     mail($email_bdd, $subject, $message,$headers);
	//Envoi email avec login et mdp
	$messageres="<div align='center' style='color:green;'><b>You will receive an email with your connection information.Thanks</b></div>";
	}
	else $messageres="<div align='center' style='color:red;'><b>Your Email doesn't exist in our database.</b></div>";
	}
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Bootstrap Admin Theme</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
     <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
<link href="../dist/css/aci-overrides.css" rel="stylesheet"> <!-- <= impératif, et APRÈS sb-admin-2.css -->

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Forgot your username?</h3>
                    </div>
                    <div class="panel-body">
					<?php if(!empty($messageres)) echo $messageres;?>
                        <form role="form" id="myform" method="post" action="mdp_oublie.php">
                            <fieldset>
                                <div class="form-group">
                                    Please enter your email address to receive your username and password.<br><br>
                                    <input class="form-control" placeholder="Your email address" name="email" id="email" type="email" autofocus>
                                </div>
								<input type="submit" value="Confirm" class="btn btn-lg btn-success btn-block"/>
                            </fieldset>
                        </form>
						<a href="login.php">Back to login page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

</body>

</html>
