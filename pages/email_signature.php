<?php
// ====================================================================
// Signature & logo utilisés dans les emails de cotation / newsletter
// Pour modifier : il suffit de changer ce bloc HTML.
// ====================================================================

$ACI_EMAIL_LOGO_URL = 'https://www.aerocanada-industries.com/images/logo-email-aci.png';
// Mets ici le bon chemin réel de ton logo sur le site (PNG de préférence)

$ACI_EMAIL_SIGNATURE_HTML = '
<table cellpadding="0" cellspacing="0" border="0" style="font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.4;">
  <tr>
    <td valign="top" style="padding-right:10px;">
      <img src="' . $ACI_EMAIL_LOGO_URL . '" alt="AeroCanada Industries" width="120" style="display:block;border:0;"/>
    </td>
    <td valign="top">
      <strong>Yohan Amsellem</strong><br/>
      Director, International Sales | AeroCanada Industries 770 Inc.<br/>
      <span style="font-size:10px;color:#666;">Your Perfect Choice For Aviation Solutions</span><br/><br/>

      dir. +33 1 84 16 0749 | mob. +33 6 52 54 36 80<br/>
      tel. +1 514 80 60 223 | fax. +1 514 80 60 224<br/>
      <a href="mailto:yohan@aerocanada.aero">yohan@aerocanada.aero</a> |
      <a href="https://www.aerocanada.aero" target="_blank">www.aerocanada.aero</a><br/><br/>

      OUR ADDRESS CHANGED:<br/>
      99, Prince Street, 7th Floor, Suite#701<br/>
      Montreal QC H3C 2M7, Canada<br/><br/>

      FAA AC00-56A | UNGM 256670 | NATO CAGE L0674<br/>
    </td>
  </tr>
</table>
';
