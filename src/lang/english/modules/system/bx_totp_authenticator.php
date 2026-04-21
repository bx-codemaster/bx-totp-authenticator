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
    define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION', '<h3 style="margin-top:0; display:flex; align-items:center; gap:8px;">'.xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', 'BX TOTP Authenticator', '', '', 'style="max-height: 32px;"').' BX TOTP Authenticator</h3><p>Enables two-factor authentication - TOTP (Google/Microsoft/Authy Authenticator) to secure customer accounts</p>');
  } else {
    define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION', '<h3 style="margin-top:0; display:flex; align-items:center; gap:8px;">'.xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', 'BX TOTP Authenticator', '', '', 'style="max-height: 32px;"').' BX TOTP Authenticator</h3>
    <p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Permanently delete all module files?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_totp_authenticator&action=custom&delete=true').'">Permanently delete all module files</a></p>');
  }  
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_TITLE', 'sorting order');
  define('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER_DESC', 'Display order. The smallest digit is displayed first.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_TITLE', 'Module active?');
  define('MODULE_BX_TOTP_AUTHENTICATOR_STATUS_DESC', 'Should the module be displayed?');

  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_TITLE', 'Configuration ID');
  define('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID_DESC', 'The unique ID of the module configuration.');

  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_COULD_NOT_BE_DELETED', ' could not be deleted.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_SUCCSESSFULLY_REMOVED', 'All module files were successfully deleted.');
  define('MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DELETE_FAILED', 'Error deleting module files.');
