<?php
ini_set('display_errors', 1);
// require_once('phpmailer/class.phpmailer.php');
require_once('PHPMailer-FE_v4.11/_lib/class.phpmailer.php');
define('GMailUSER', 'roy@aerocanada.aero'); // utilisateur Gmail
define('GMailPWD', 'Lamalol@770'); // Mot de passe Gmail


function smtpMailer($to, $from, $from_name, $subject, $body) {
	$mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
	$mail->IsSMTP(); // active SMTP
	$mail->SMTPDebug = 2;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
	$mail->SMTPAuth = true;  // Authentification SMTP active
	$mail->SMTPSecure = 'ssl'; // Gmail REQUIERT Le transfert securise
	$mail->Host = 'smtp.gmail.com';
	$mail->Port = 465;
	// $mail->Username = GMailUser;
	// $mail->Password = GMailPWD;
	$mail->Username = 'roy@aerocanada.aero';
	$mail->Password = 'Lamalol@770';
	// $mail->SetFrom($from, $from_name);
	$mail->From = $from;
	$mail->FromName = $from_name;
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddAddress($to);
	if(!$mail->Send()) {
		return 'Mail error: '.$mail->ErrorInfo;
	} else {
		return true;
	}
}




//$result = smtpmailer('destinataire@mail.com', 'votreEmail@mail.com', 'votreNom', 'Votre Message', 'Le sujet de votre message');
$result = smtpMailer('lamalol@gmail.com', 'roy@aerocanada.aero', 'Roy', 'Message test de Roy', 'test sujet message gmail');
if (true !== $result)
{
	//erreur -- traiter erreur
	echo $result;
}

?>