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
 
  $description = '
  <details class="bxac-card">
    <summary class="bxac-summary" style="list-style: none;">
      <span class="bxac-arrow">▸</span>
      <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', 'BX TOTP Authenticator', '', '', 'style="max-height: 32px; vertical-align: middle; margin-right: 8px;"') . 'BX TOTP Authenticator</span>
    </summary>
    <div class="bxac-body">
      <h3 style="margin-top: 0;">Two-Factor Authentication</h3>';
  
  if(defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS') && MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True') {
    $description .= '<p>Enables two-factor authentication - TOTP (Google/Microsoft/Authy Authenticator) to secure customer accounts</p>
    <ul>
      <li>✅ Easy integration into customer accounts</li>
      <li>✅ QR-Code for easy setup</li>
      <li>✅ Backup codes for emergencies</li>
      <li>✅ Compatible with all TOTP apps</li>
    </ul>';
  } else {
    if(basename($_SERVER['PHP_SELF']) !== 'start.php') {
      $description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Delete all files?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_totp_authenticator&action=custom&delete=true').'">Delete all module files</a></p>';
    }
  }
  $description .= '</div></details>';
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION', $description);  
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_TITLE', 'sorting order');
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_DESC', 'Display order. The smallest digit is displayed first.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_TITLE', 'Module active?');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_DESC', 'Should the module be displayed?');

  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_TITLE', 'Configuration ID');
  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_DESC', 'The unique ID of the module configuration.');

  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_COULD_NOT_BE_DELETED', ' could not be deleted.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_SUCCSESSFULLY_REMOVED', 'All module files were successfully deleted.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DELETE_FAILED', 'Error deleting module files.');
