<?php
/* ---------------------------------------------------------
   $Id: bx_totp_authenticator.php 00000 2026-01-20 00:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ---------------------------------------------------------

   Released under the GNU General Public License 
   -------------------------------------------------------*/
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_TITLE', 'BX TOTP Authenticator');
  if(defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS') && MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True') {
    define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION', '<h3 style="margin-top:0; display:flex; align-items:center; gap:8px;">'.xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', 'BX TOTP Authenticator', '', '', 'style="max-height: 32px;"').' BX TOTP Authenticator</h3><p>Aktiviert die Zwei-Faktor-Authentifizierung - TOTP (Google/Microsoft/Authy Authenticator) zur Absicherung von Kunden-Accounts</p>');
  } else {
    define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION', '<h3 style="margin-top: 0;"><h3 style="margin-top:0; display:flex; align-items:center; gap:8px;">'.xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', 'BX TOTP Authenticator', '', '', 'style="max-height: 32px;"').' BX TOTP Authenticator</h3>
    <p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Alle Dateien löschen?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_totp_authenticator&action=custom&delete=true').'">Alle Moduldateien löschen</a></p>');
  }
  
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_TITLE', 'Sortierreihenfolge');
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_TITLE', 'Modul aktiv?');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_DESC', 'Soll das Modul angezeigt werden?');

  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_TITLE', 'Konfigurations-ID');
  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_DESC', 'Die eindeutige ID der Modul-Konfiguration.');

  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_COULD_NOT_BE_DELETED', ' konnte nicht gelöscht werden.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_SUCCSESSFULLY_REMOVED', 'Alle Moduldateien wurden erfolgreich gelöscht.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DELETE_FAILED', 'Fehler beim Löschen der Moduldateien.');
