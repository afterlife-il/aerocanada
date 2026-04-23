<?php
/* include autoloader */
require_once 'dompdf/autoload.inc.php';

/* reference the Dompdf namespace */
use Dompdf\Dompdf;

/* instantiate and use the dompdf class */
$dompdf = new Dompdf();
//newsletter html
$html = '<body style="height: 100%;margin: 0;padding: 0;width: 100%;font-size: 12px;">


		<table style="width: 100%;">
		<tr><td style="width: 16%;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/pages/images/logo-aerocanada.png" style="padding:5px;width:100px;"></td>
		<td align="left" style="width: 42%;">
		&nbsp;
		&nbsp;
		</td>
		<td style="width: 42%;">
		<span style="font-size: 13px;">PURCHASE ORDER#<BR>AC180411-26</span>
		
		</td></tr>
		</table>
		<br>
		<table  style="width: 100%;">
		<tr>
		<td width: 50%;>
		FOR<br>
		<hr>
		AEROCANADA INDUSTRIES 770 INC.<br>
		700-407 MCGILL STREET,7TH FLOOR<br>
		QUEBEC<br>
		H2Y 2G3<br>
		CANADAD
		
		</td>
		<td width: 50%;>
		&nbsp;
		</td>
		</tr>
		<tr>
		<td width: 50%;>
		INVOICE TO<br>
		<hr>
		AEROCANADA INDUSTRIES 770 INC.<br>
		700-407 MCGILL STREET,7TH FLOOR<br>
		QUEBEC<br>
		H2Y 2G3<br>
		CANADAD
		
		</td>
		<td width: 50%;>SHIP TO<br>
		<hr>
		AEROCANADA INDUSTRIES 770 INC.<br>
		700-407 MCGILL STREET,7TH FLOOR<br>
		QUEBEC<br>
		H2Y 2G3<br>
		CANADAD
		</td>
		</tr>
		</table>
		<br>
		<table  style="width: 100%;">
			<thead>
		 	 														
                                    <tr>
										<th style="text-align: center;">ORDER DATE</th>
                                        <th style="text-align: center;">SHIPPED DATE</th>
                                        <th style="text-align: center;">SHIP VIA</th>
                                        <th style="text-align: center;">ACCT#</th>
                                        <th style="text-align: center;">YOUR REF#</th>
                                        <th style="text-align: center;">OUR CONTACT</th>
                                        <th style="text-align: center;">TERMS</th>
                                        <th style="text-align: center;">CURRENCY</th>
                                    </tr>
									<tr><td colspan="8"><hr></td></tr>
            </thead>
						<tr>
						<td style="text-align: center;">11-04-18</td>
						<td style="text-align: center;">11-04-18</td>
						<td style="text-align: center;">TBA</td>
						<td style="text-align: center;">TBA</td>
						<td style="text-align: center;">AC180411-26</td>
						<td style="text-align: center;">KARINE</td>
						<td style="text-align: center;">TBA</td>
						<td style="text-align: center;">USD</td>
						</tr>						
            </table>							
		<br>
		<table  style="width: 100%;">
		<thead>
		 	 														
                                    <tr>
										<th style="text-align: center;">QTY</th>
                                        <th style="text-align: center;">P/N</th>
                                        <th style="text-align: center;">S/N</th>
                                        <th style="text-align: center;">Desc</th>
                                        <th style="text-align: center;">Priority</th>
                                        <th style="text-align: center;">Cond.</th>
                                        <th style="text-align: center;">U/Price</th>
                                        <th style="text-align: center;">Total</th>
                                    </tr>
									<tr><td colspan="8"><hr></td></tr>
                                </thead>
		<tr>
		<td style="text-align: center;">1</td>
		<td style="text-align: center;">FE201-6-002<br>OEM 2013</td>
		<td style="text-align: center;">03520</td>
		<td style="text-align: center;">CHECK VALVE</td>
		<td style="text-align: center;">aog</td>
		<td style="text-align: center;">NEW</td>
		<td style="text-align: center;">$3,000.00</td>
		<td style="text-align: center;">$3,000.00</td>
		</tr>
		<tr>
		<td style="text-align: center;">1</td>
		<td style="text-align: center;">FE201-6-002<br>OEM 2013</td>
		<td style="text-align: center;">03530</td>
		<td style="text-align: center;">CHECK VALVE</td>
		<td style="text-align: center;">aog</td>
		<td style="text-align: center;">NEW</td>
		<td style="text-align: center;">$3,000.00</td>
		<td style="text-align: center;">$3,000.00</td>
		</tr>
		
		<tr><td colspan="9"><hr></td></tr>
		<tr>
		<td colspan="6">
		Remarks : <b>FOR CIVIL AIRCRAFT USE ONLY !</b><br>
		</td>
		<td colspan="3">
		Freight $0.00<BR>
		<hr>
		Sub Total $40,000.00<BR>
		<hr>
		VAT 20.0% $0.00<BR>
		<hr>
		<b>Total Due</b> : $40,000.00<BR>
		<hr>
		Currency : Dollars (USD)
		</td>
		</tr>
		</table>
		
		<br>	
		<div style="padding:5px;font-size: 10px;">
		<B>GENERAL  TERMS  AND  CONDITIONS:</b><br>
		-  Please  confirm  receipt  of  this  ORDER.<br>
		-  Please  notify  us  immediately  if  you  are  unable  to  ship  as  specified.<br>
		-  ALL  NEW  material  must  be  supplied  with  OEM  Certificates  (C  of  C  or  FAA  8130-3  or  EASA  FORM  1)<br>
		-  Material  in  SVC  or  OHC  condition  requires  current  FAA  8130-3  /  EASA  FORM  1  /  TC  24-0078  CERTIFICATE<br>
		-  AR  -  AS  REMOVED  -  material  is  purchased  subject  to  shop  inspection.
		</div>
		</body>';
		
		
		$html2='<div style="height: 100%;margin: 0;padding: 0;width: 100%;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #cbd1d6;">
		<span class="mcnPreviewText" style="display:none; font-size:0px; line-height:0px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; visibility:hidden; mso-hide:all;">*|MC_PREVIEW_TEXT|*</span>
        <center>
         <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;height: 100%;margin: 0;padding: 0;width: 100%;background-color: #cbd1d6;">
                <tr>
                    <td align="center" valign="top" id="bodyCell" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;height: 100%;margin: 0;padding: 10px;width: 100%;border-top: 0;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;border: 0;max-width: 600px !important;">
							<tr>
                                <td valign="top" id="templatePreheader" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #ffffff;background-image: none;background-repeat: no-repeat;background-position: center;background-size: cover;border-top: 0;border-bottom: 0;padding-top: 9px;padding-bottom: 9px;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="padding-top: 9px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;line-height: 125%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;font-size: 12px;text-align: left;">
                        
                            <span style="font-size:16px"><strong><span style="color:#a3a3a3">CLOUDPRO</span></strong></span><br>
<br>
Tel : 09.72.46.82.07<br>
<a href="mailto:daniel.essayag@clyosystems.com" target="_blank" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #102fce;font-weight: normal;text-decoration: underline;">daniel.essayag@clyosystems.com</a><br>
60 Avenue de Nice<br>
06800 Cagnes-sur-Mer France
                        </td>
                    </tr>
                </tbody></table>

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;line-height: 125%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;font-size: 12px;text-align: left;">
                        
                            <div style="text-align: right;"><strong><span style="font-size:14px">27/07/2017 13:29</span></strong><br>
<br>
Siret : Fr156464467494<br>
Tva : 1652313131<br>
Naf : 5610a</div>

                        </td>
                    </tr>
                </tbody></table>

            </td>
        </tr>
    </tbody>
</table></td>
                            </tr>
							
							<tr>
                                <td valign="top" id="templateHeader" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #f6f6f6;background-image: none;background-repeat: no-repeat;background-position: center;background-size: cover;border-top: 0;border-bottom: 0;padding-top: 9px;padding-bottom: 0;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="padding-top: 9px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;font-size: 12px;line-height: 125%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;text-align: left;">
                        
                            <img align="left" data-file-id="3075137" height="53" src="https://gallery.mailchimp.com/b686aff88ae47c4524f0934f3/images/bbfac35e-1ad8-4528-ab34-14dc2abd5f9e.jpg" style="border: 0px initial;width: 53px;height: 53px;margin: 15px;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="53"><a href="mailto:dov@serialkolors.com" target="_blank" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #102fce;font-weight: normal;text-decoration: underline;">dov@serialkolors.com</a><br>
Solde: 0,00 b,<br>
Carte Client: 0<br>
Points: 0<br>
Notes:&nbsp;
                        </td>
                    </tr>
                </tbody></table>

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;font-size: 12px;line-height: 125%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;text-align: left;">
                        
                            <div style="text-align: right;"><br>
<img align="right" data-file-id="3075141" height="30" src="https://gallery.mailchimp.com/b686aff88ae47c4524f0934f3/images/7a5982c8-8b85-4691-a34c-9efba61b5aa9.jpg" style="border: 0px;width: 30px;height: 30px;margin: 15px;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="30"><br>
Vendeur:<br>
CYRIL</div>

                        </td>
                    </tr>
                </tbody></table>

            </td>
        </tr>
    </tbody>
</table></td>
                            </tr>
							
							<tr>
                                <td valign="top" id="templateBody" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;background-image: none;background-repeat: no-repeat;background-position: center;background-size: cover;border-top: 0;padding-top: 0;padding-bottom: 9px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
								<tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="padding-top: 9px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
			<table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 100%;min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">

			
			<tbody>
			<tr>
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #434343;font-size: 14px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;line-height: 150%;text-align: left;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
							<tbody>
	
	
								<tr height="40"  style="border-bottom: 2px solid #EAEAEA;">
								
								</tr>
	
	
	
							</tbody>
							</table>
						
						
						</td>
                    </tr>
                </tbody></table>
			
			
			
			
			 </td>
        </tr>
    </tbody>
								</table>
							</td>
                            </tr>
							<tr><td>
							*************
							
							*************
							</td></tr>
							 <tr>
                                <td valign="top" id="templateFooter" style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #ffffff;background-image: none;background-repeat: no-repeat;background-position: center;background-size: cover;border-top: 0;border-bottom: 0;padding-top: 9px;padding-bottom: 9px;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnDividerBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;table-layout: fixed !important;">
    <tbody class="mcnDividerBlockOuter">
        <tr>
            <td class="mcnDividerBlockInner" style="min-width: 100%;padding: 10px 18px 25px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                <table class="mcnDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width: 100%;border-top: 2px none #EEEEEE;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        <td style="mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                            <span></span>
                        </td>
                    </tr>
                </tbody></table>

            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="padding-top: 9px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;line-height: 125%;text-align: left;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;font-size: 12px;">
                        
                            Restitution Signature: B0022aJoD<br>
Version: 5.12.102<br>
Poste: 1 Vente<br>
Nombre de Lignes: 4<br>
Document 1051 (#1)
                        </td>
                    </tr>
                </tbody></table>

                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 300px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;color: #A3A3A3;line-height: 125%;text-align: left;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;font-family: Helvetica;font-size: 12px;">
                        
                            <div style="text-align: right;"><br>
<img data-file-id="3075297" height="46" src="https://gallery.mailchimp.com/b686aff88ae47c4524f0934f3/images/91605438-777b-476d-ac37-e6fe5b92aa23.png" style="border: 0px;width: 71px;height: 46px;margin: 0px;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="71"></div>

                        </td>
                    </tr>
                </tbody></table>
	
            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="padding-top: 9px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
          
                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width: 100%;min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" width="100%" class="mcnTextContentContainer">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 0px 18px 9px;font-size: 16px;font-style: normal;font-weight: bold;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;word-break: break-word;color: #656565;font-family: Helvetica;line-height: 150%;text-align: center;">
                        
                            <div style="text-align: center; border-top: 2px solid #EAEAEA;">&nbsp;</div>

Merci de votre visite
                        </td>
                    </tr>
                </tbody></table>
		
            </td>
        </tr>
    </tbody>
</table>
</td>
                            </tr>
							
							
							</table>
					</td>
                </tr>
            </table>
        </center>
    </div>';
//fin newsletter html
$dompdf->loadHtml($html);

/* Render the HTML as PDF */
$dompdf->render();

/* Output the generated PDF to Browser */
// $dompdf->stream();

$output = $dompdf->output();
file_put_contents('pdf/filename.pdf', $output);


//**********************************************************Envoie Email

$mail = 'lamalol@gmail.com'; // Déclaration de l'adresse de destination.
if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) // On filtre les serveurs qui présentent des bogues.
{
	$passage_ligne = "\r\n";
}
else
{
	$passage_ligne = "\n";
}
//=====Déclaration des messages au format texte et au format HTML.
$message_txt = "Purchase order";
$message_html = "<html><head></head><body><b>Purchase order</b>.</body></html>";
//==========
 
//=====Lecture et mise en forme de la pièce jointe.
$fichier   = fopen("pdf/filename.pdf", "r");
$attachement = fread($fichier, filesize("pdf/filename.pdf"));
$attachement = chunk_split(base64_encode($attachement));
fclose($fichier);
//==========
 
//=====Création de la boundary.
$boundary = "-----=".md5(rand());
$boundary_alt = "-----=".md5(rand());
//==========
 
//=====Définition du sujet.
$sujet = "Purchase order";
//=========
 
//=====Création du header de l'e-mail.
$header = "From: \"Aerocanada\"<roy@aerocanada.aero>".$passage_ligne;
$header.= "Reply-to: \"Aerocanada\" <roy@aerocanada.aero>".$passage_ligne;
$header.= "MIME-Version: 1.0".$passage_ligne;
$header.= "Content-Type: multipart/mixed;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
//==========
 
//=====Création du message.
$message = $passage_ligne."--".$boundary.$passage_ligne;
$message.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary_alt\"".$passage_ligne;
$message.= $passage_ligne."--".$boundary_alt.$passage_ligne;
//=====Ajout du message au format texte.
$message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_txt.$passage_ligne;
//==========
 
$message.= $passage_ligne."--".$boundary_alt.$passage_ligne;
 
//=====Ajout du message au format HTML.
$message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message.= $passage_ligne.$message_html.$passage_ligne;
//==========
 
//=====On ferme la boundary alternative.
$message.= $passage_ligne."--".$boundary_alt."--".$passage_ligne;
//==========
 
 
 
$message.= $passage_ligne."--".$boundary.$passage_ligne;
 
//=====Ajout de la pièce jointe.
// $message.= "Content-Type: image/jpeg; name=\"image.jpg\"".$passage_ligne;
$message.= "Content-Type: application/pdf; name=\"pdf/filename.pdf\"".$passage_ligne;
$message.= "Content-Transfer-Encoding: base64".$passage_ligne;
$message.= "Content-Disposition: attachment; filename=\"pdf/filename.pdf\"".$passage_ligne;
$message.= $passage_ligne.$attachement.$passage_ligne.$passage_ligne;
$message.= $passage_ligne."--".$boundary."--".$passage_ligne; 
//========== 
//=====Envoi de l'e-mail.
mail($mail,$sujet,$message,$header);
// mail("yohan@aerocanada.aero",$sujet,$message,$header);
 
//==========

//**********************************************************Fin Envoie Email
?>