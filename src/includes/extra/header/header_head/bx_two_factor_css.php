<?php
/** --------------------------------------------------------------
 * BX Two Factor Authentication - Frontend CSS Integration
 * 
 * Bindet das Stylesheet für die 2FA-Seiten im Frontend ein.
 * Wird automatisch in den <head> geladen wenn 2FA aktiviert ist.
 * 
 * Styles für:
 * - 2FA Account Management Seite
 * - 2FA Verification Seite
 * - QR-Code Darstellung
 * - Code-Eingabefelder
 * - Backup-Codes Anzeige
 * - Status-Boxen und Benachrichtigungen
 * - Responsive Design für mobile Geräte
 * 
 * Template: bx_two_factor.css 
 * 
 * $Id: includes\extra\header\header_head\bx_two_factor_css.php 100 2026-01-21 12:00:00Z Benax $
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 * 
 * Copyright (c) 2009 - 2026 [www.modified-shop.org]
 * --------------------------------------------------------------
 * Released under the GNU General Public License
 * --------------------------------------------------------------
 */
if ( defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS') && 'True' == MODULE_BX_TOTP_AUTHENTICATOR_STATUS) {
  // echo CURRENT_TEMPLATE;
  echo '<link rel="stylesheet" type="text/css" href="'.DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/css/bx_two_factor.css">'.PHP_EOL;
}
