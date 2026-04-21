<?php
/* ---------------------------------------------------------
   $Id: bx_totp_authenticator.php 00000 2026-01-20 00:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ---------------------------------------------------------

   Released under the GNU General Public License 
   -------------------------------------------------------*/

  class bx_totp_authenticator {
    public string $code;
    public string $version;
    public string $title;
    public string $description;
    public int $sort_order;
    public bool $enabled;
    private bool $_check;

    public function __construct() {
      $this->code        = 'bx_totp_authenticator';
      $this->version     = '1.5.0';
      $this->title       = MODULE_BX_TOTP_AUTHENTICATOR_TEXT_TITLE;
      $this->description = MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DESCRIPTION;
      $this->sort_order  = ((defined('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER')) ? (int)MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER : 0);
      $this->enabled     = ((defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS') && MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True') ? true : false);
    }

    public function process($file): void {
    }

    public function display(): array {
      return array('text' => '<div style="text-align: center;">'.xtc_button(BUTTON_SAVE).xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set='.$_GET['set'].'&module='.$this->code))."</div>");
    }

    public function check(): mixed {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("SELECT configuration_value 
                                      FROM ".TABLE_CONFIGURATION."
                                      WHERE configuration_key = 'MODULE_BX_TOTP_AUTHENTICATOR_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }


    public function install(): void {
      xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD bx_totp_authenticator INTEGER(1)");
      xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET bx_totp_authenticator = 1");

      xtc_db_query("ALTER TABLE " . TABLE_CUSTOMERS . " ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0,
                                                              ADD COLUMN two_factor_method ENUM('totp', 'email') DEFAULT 'totp',
                                                              ADD COLUMN two_factor_totp_secret VARCHAR(32),
                                                              ADD COLUMN two_factor_backup_codes TEXT,
                                                              ADD COLUMN two_factor_secret_created DATETIME DEFAULT '1000-01-01 00:00:00';");

      $freeId_query = xtc_db_query("SELECT (configuration_group_id+1) AS id 
                                FROM " . TABLE_CONFIGURATION_GROUP  . " 
                              WHERE (configuration_group_id+1) NOT IN (SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP  . ") 
                              LIMIT 1;"); 
      $freeId = xtc_db_fetch_array($freeId_query);

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (
                configuration_key, 
                configuration_value, 
                configuration_group_id, 
                sort_order, 
                date_added, 
                use_function, 
                set_function) 
        VALUES ('MODULE_BX_TOTP_AUTHENTICATOR_STATUS', 
                'True', 
                '" . $freeId['id'] . "', 
                '1', 
                now(),
                '', 
                'xtc_cfg_select_option(array(\'True\', \'False\'), '),
               ('MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER', 
                '1', 
                '" . $freeId['id'] . "',
                '2', 
                now(), 
                '', 
                ''), 
               ('MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID', 
                '" . $freeId['id'] . "', 
                '" . $freeId['id'] . "', 
                '3', 
                now(), 
                '', 
                '".self::class."->configurationFieldVersion')");

      xtc_db_query("CREATE TABLE two_factor_email_codes (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                customers_id INT NOT NULL,
                                code VARCHAR(6) NOT NULL,
                                created_at DATETIME NOT NULL,
                                expires_at DATETIME NOT NULL,
                                used TINYINT(1) DEFAULT 0,
                                INDEX idx_customer (customers_id),
                                INDEX idx_expires (expires_at)
                              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function remove(): void {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
      xtc_db_query("ALTER TABLE " . TABLE_ADMIN_ACCESS . " DROP bx_totp_authenticator");
      xtc_db_query("ALTER TABLE " . TABLE_CUSTOMERS . " DROP COLUMN two_factor_enabled,
                                                              DROP COLUMN two_factor_method,
                                                              DROP COLUMN two_factor_totp_secret,
                                                              DROP COLUMN two_factor_backup_codes,
                                                              DROP COLUMN two_factor_secret_created;");
      xtc_db_query("DROP TABLE IF EXISTS two_factor_email_codes;");
    }

    public function keys(): array {
      return array(
        'MODULE_BX_TOTP_AUTHENTICATOR_STATUS',
        'MODULE_BX_TOTP_AUTHENTICATOR_SORT_ORDER',
        'MODULE_BX_TOTP_AUTHENTICATOR_CONFIG_ID',
      );
    }
      /**
      * Action to perform when the configuration key '_VERSION' is being displayed.
      *
      * @param string $value
      * @param string $constant
      *
      * @return string
      */
    public function configurationFieldVersion(string $value, string $constant): string {
      return xtc_draw_input_field( 'configuration['.$constant.']', $value, 'readonly="true" style="opacity: 0.4;"');
    }

    public function custom() {
      global $messageStack;
      $result = true;
      $delete = (string)$_GET['delete'] ?? 'false';
        
      // Dateien definieren
      $dirs_and_files   = array();

      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'images/icons/heading/2fa.png';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.DIR_WS_INCLUDES.'classes/bx_totp_helper.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.DIR_WS_INCLUDES.'extra/css/bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.DIR_WS_INCLUDES.'extra/filenames/bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.DIR_WS_INCLUDES.'extra/javascript/bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.DIR_WS_INCLUDES.'extra/menu/bx_totp_authenticator.php';

      $dirs_and_files[] = DIR_FS_CATALOG.DIR_WS_INCLUDES.'classes/bx_two_factor_auth.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_WS_INCLUDES.'classes/bx_two_factor_email_handler.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_WS_INCLUDES.'extra/filenames/bx_two_factor_filenames.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_WS_INCLUDES.'extra/header/header_head/bx_two_factor_css.php';
      $dirs_and_files[] = DIR_FS_CATALOG.DIR_WS_INCLUDES.'extra/login/bx_two_factor_check.php';
      
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/german/modules/system/bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/english/modules/system/bx_totp_authenticator.php';
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/german/extra/bx_two_factor_account.php';
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/english/extra/bx_two_factor_account.php';
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/german/extra/bx_two_factor_verify.php';
      $dirs_and_files[] = DIR_FS_CATALOG.'lang/english/extra/bx_two_factor_verify.php';
        
      if ($delete === 'true') {
        // Dateien löschen
        foreach ($dirs_and_files as $dir_or_file) {
          if (!$this->rrmdir($dir_or_file)) {
            $messageStack->add_session($dir_or_file.MODULE_BX_TOTP_AUTHENTICATOR_TEXT_COULD_NOT_BE_DELETED, 'error');
            $result = false;
          }
        }
          
        if ($result === true) {
          $messageStack->add_session(MODULE_BX_TOTP_AUTHENTICATOR_TEXT_SUCCSESSFULLY_REMOVED, 'success');
        } else {
          $messageStack->add_session(MODULE_BX_TOTP_AUTHENTICATOR_TEXT_DELETE_FAILED, 'error');
        }
          
        // Datei selbst löschen
        unlink(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/system/bx_totp_authenticator.php');
      }
    }
      
    private function rrmdir($dir) {
      if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
          if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") {
              $this->rrmdir($dir."/".$object);
            } else {
              unlink($dir."/".$object);
            }
          }
        }
        reset($objects);
        rmdir($dir);
        return true;
      } elseif (is_file($dir)) {
        unlink($dir);
        return true;
      }
    }

  }
