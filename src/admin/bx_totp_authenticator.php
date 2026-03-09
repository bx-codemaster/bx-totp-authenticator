<?php
/** --------------------------------------------------------------
 * $Id: admin/bx_totp_authenticator.php 16358 2026-01-19 12:00:00Z benax $
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 * 
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 * --------------------------------------------------------------
 * based on:
 * (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
 * (c) 2002-2003 osCommercecoding standards www.oscommerce.com
 * (c) 2003	nextcommerce www.nextcommerce.org
 * (c) 2003 XT-Commerce
 * 
 * Released under the GNU General Public License
 * --------------------------------------------------------------
 * Unter Mitwirkung von CADDY entwickelt
 * CADDY: Computer-Aided Development & Deployment Yield (AI)
 */

require ('includes/application_top.php');

// TOTP Helper Klasse laden
require_once(DIR_FS_ADMIN . DIR_WS_CLASSES . 'bx_totp_helper.php');

if (file_exists(DIR_FS_CATALOG . 'includes/classes/bx_dependency_resolver.php')) {
  require_once(DIR_FS_CATALOG . 'includes/classes/bx_dependency_resolver.php');
  define('TOTP_DEPENDENCY_RESOLVER_AVAILABLE', true);
} else {
  define('TOTP_DEPENDENCY_RESOLVER_AVAILABLE', false);
}

// QR-Code Generator
try {
    if(class_exists('bx_dependency_resolver')) {
      bx_dependency_resolver::require('modified_qrcode');
      define('TOTP_QRCODE_AVAILABLE', true);
    } else {
      define('TOTP_QRCODE_AVAILABLE', false); 
    }
} catch (Exception $e) {
    define('TOTP_QRCODE_AVAILABLE', false); 
    // Optional: Fehler loggen
    // error_log('QR-Code library not available: ' . $e->getMessage());
}



// ========================================
// POST-HANDLER: 2FA Notfall-Deaktivierung
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'emergency_disable_2fa') {
    $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    
    if ($customer_id > 0) {
        // Kundeninfo laden
        $customer_check = xtc_db_query("SELECT customers_id, customers_firstname, customers_lastname, customers_email_address, two_factor_enabled 
                                        FROM " . TABLE_CUSTOMERS . " 
                                        WHERE customers_id = '" . $customer_id . "'");
        
        if (xtc_db_num_rows($customer_check) > 0) {
            $customer = xtc_db_fetch_array($customer_check);
            
            if ($customer['two_factor_enabled'] == 1) {
                // 2FA deaktivieren
                xtc_db_query("UPDATE " . TABLE_CUSTOMERS . " 
                             SET two_factor_enabled = 0,
                                 two_factor_method = NULL,
                                 two_factor_totp_secret = NULL,
                                 two_factor_backup_codes = NULL,
                                 two_factor_secret_created = '1000-01-01 00:00:00'
                             WHERE customers_id = '" . $customer_id . "'");
                
                // Email-Codes löschen
                xtc_db_query("DELETE FROM two_factor_email_codes WHERE customers_id = '" . $customer_id . "'");
                
                $messageStack->add_session(sprintf(TEXT_BX_TOTP_SUCCESS_DISABLED, $customer['customers_firstname'] . ' ' . $customer['customers_lastname'], $customer['customers_email_address']), 'success');
            } else {
                $messageStack->add_session(TEXT_BX_TOTP_NOT_ACTIVATED, 'warning');
            }
        } else {
            $messageStack->add_session(TEXT_BX_TOTP_CUSTOMER_NOT_FOUND, 'error');
        }
    } else {
        $messageStack->add_session(TEXT_BX_TOTP_INVALID_CUSTOMER_ID, 'error');
    }
    
    xtc_redirect(xtc_href_link(FILENAME_BX_TOTP_AUTHENTICATOR, 'tab=support'));
}

// Statistiken abrufen
$stats_query = xtc_db_query("SELECT 
    COUNT(*) as total_customers,
    SUM(CASE WHEN two_factor_enabled = 1 THEN 1 ELSE 0 END) as enabled_count,
    SUM(CASE WHEN two_factor_enabled = 1 AND two_factor_method = 'totp' THEN 1 ELSE 0 END) as totp_count,
    SUM(CASE WHEN two_factor_enabled = 1 AND two_factor_method = 'email' THEN 1 ELSE 0 END) as email_count
FROM " . TABLE_CUSTOMERS);
$stats = xtc_db_fetch_array($stats_query);

$enabled_percentage = $stats['total_customers'] > 0 ? round(($stats['enabled_count'] / $stats['total_customers']) * 100, 1) : 0;

require_once (DIR_WS_INCLUDES.'head.php');

$messageStack->output();
?>
</head>
<!-- header //-->
<?php require(DIR_WS_INCLUDES.'header.php'); ?>

<!-- header_eof //-->
<!-- body //-->
<table class="tableBody">
  <tr>
    <?php //left_navigation
    if (USE_ADMIN_TOP_MENU == 'false') {
      echo '<td class="columnLeft2">'.PHP_EOL;
      echo '<!-- left_navigation //-->'.PHP_EOL;
      require_once(DIR_WS_INCLUDES.'column_left.php');
      echo '<!-- left_navigation eof //-->'.PHP_EOL;
      echo '</td>'.PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter">
      <div class="pageHeadingImage" style="width: 42px;">
        <?php echo xtc_image(DIR_WS_ICONS.'heading/bx_2fa.png', MODULE_BX_TOTP_TITLE, '', '', 'style="height: 32px;"'); ?>
      </div>
      <div class="pageHeading flt-l">
        <?php echo MODULE_BX_TOTP_TITLE; ?>
        <div class="main pdg2">
          <?php echo MODULE_BX_TOTP_DESC; ?>
        </div>
      </div>
      <div class="clear"></div>

      <table class="tableCenter" style="margin-top: 5px;">
        <tr>
          <td class="boxCenterLeft">
            <div class="main" style="display: flex; flex-direction: row; justify-content: left; align-items: center; background: #AF417E; color: #ffffff; border-radius: 4px; margin: 0 0 5px 0; padding: 4px 0 2px 0;">
              <div class="main" style="margin: 5px 10px;"><strong><span style="font-size: 1.5em;">🔑</span> <?php echo MODULE_BX_TOTP_TITLE. ' v'. MODULE_BX_TOTP_VERSION; ?></strong></div>
            </div>
            
            <div class="tabs">
              <ul class="tab-nav">
                <li><a href="#tab-dashboard"><span style="font-size: 14px;">📊</span> <?php echo TEXT_BX_TOTP_TAB_DASHBOARD; ?></a></li>
                <li><a href="#tab-customers"><span style="font-size: 14px;">🧑‍💼</span> <?php echo TEXT_BX_TOTP_TAB_CUSTOMERS; ?></a></li>
                <li><a href="#tab-support"><span style="font-size: 14px;">🛠️</span> <?php echo TEXT_BX_TOTP_TAB_SUPPORT; ?></a></li>
              </ul>

              <div class="tab-content">

                <!-- TAB 1: DASHBOARD //-->
                <div id="tab-dashboard">
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" colspan="2">
                        <strong>📊 <?php echo TEXT_BX_TOTP_OVERVIEW_TITLE; ?></strong>
                      </td>
                    </tr>
                    <tr class="dataTableRow">
                      <td class="dataTableContent" style="width: 50%; vertical-align: top !important;">
                        <div style="padding: 20px; text-align: center;">
                          <div style="font-size: 48px; font-weight: bold; color: #AF417E; line-height: 1;">
                            <?php echo $enabled_percentage; ?>%
                          </div>
                          <div style="font-size: 14px; margin-top: 10px; color: #666;">
                            <?php echo TEXT_BX_TOTP_ACTIVATION_RATE; ?>
                          </div>
                          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                            <strong style="font-size: 18px;"><?php echo $stats['enabled_count']; ?></strong> 
                            <span style="color: #666;"><?php echo TEXT_BX_TOTP_OF; ?></span> 
                            <strong style="font-size: 18px;"><?php echo $stats['total_customers']; ?></strong>
                            <div style="color: #666; margin-top: 5px;"><?php echo TEXT_BX_TOTP_CUSTOMERS_USE_2FA; ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="dataTableContent" style="width: 50%; vertical-align: top !important;">
                        <div style="padding: 20px;">
                          <h3 style="margin-top: 0; margin-bottom: 15px;"><?php echo TEXT_BX_TOTP_METHOD_DISTRIBUTION; ?></h3>
                          
                          <div style="margin-bottom: 15px; padding: 12px; background: #f0f8ff; border-left: 4px solid #4CAF50; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                              <div>
                                <span style="font-size: 20px;">🔑</span>
                                <strong><?php echo TEXT_BX_TOTP_METHOD_TOTP; ?></strong>
                              </div>
                              <div style="font-size: 18px; font-weight: bold; color: #4CAF50;">
                                <?php echo $stats['totp_count']; ?>
                              </div>
                            </div>
                            <div style="margin-top: 5px; color: #666; font-size: 11px;">
                              <?php echo TEXT_BX_TOTP_RECOMMENDED_METHOD; ?>
                            </div>
                          </div>
                          
                          <div style="padding: 12px; background: #fff9e6; border-left: 4px solid #FF9800; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                              <div>
                                <span style="font-size: 20px;">📧</span>
                                <strong><?php echo TEXT_BX_TOTP_METHOD_EMAIL; ?></strong>
                              </div>
                              <div style="font-size: 18px; font-weight: bold; color: #FF9800;">
                                <?php echo $stats['email_count']; ?>
                              </div>
                            </div>
                            <div style="margin-top: 5px; color: #666; font-size: 11px;">
                              <?php echo TEXT_BX_TOTP_ALTERNATIVE_METHOD; ?>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </table>
                  
                  <table class="tableBoxCenter collapse" style="margin-top: 15px;">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent">
                        <strong>🔧 <?php echo TEXT_BX_TOTP_SYSTEM_STATUS; ?></strong>
                      </td>
                    </tr>
                    <tr class="dataTableRow">
                      <td class="dataTableContent">
                        <div style="padding: 15px;">
                          <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #eee;">
                              <td style="padding: 10px 5px; width: 60%;">
                                <strong><?php echo TEXT_BX_TOTP_DEPENDENCY_RESOLVER_CLASS; ?></strong>
                                <div style="font-size: 11px; color: #666;"><?php echo TEXT_BX_TOTP_DEPENDENCY_RESOLVER_FOR_SETUP; ?></div>
                              </td>
                              <td style="padding: 10px 5px; text-align: right;">
                                <?php if (TOTP_DEPENDENCY_RESOLVER_AVAILABLE) { ?>
                                  <span style="color: #4CAF50; font-weight: bold;">✓ <?php echo TEXT_BX_TOTP_DEPENDENCY_RESOLVER_AVAILABLE; ?></span>
                                <?php } else { ?>
                                  <span style="color: #f44336; font-weight: bold;">✗ <?php echo TEXT_BX_TOTP_DEPENDENCY_RESOLVER_NOT_AVAILABLE; ?></span>
                                <?php } ?>
                              </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                              <td style="padding: 10px 5px; width: 60%;">
                                <strong><?php echo TEXT_BX_TOTP_QRCODE_LIBRARY; ?></strong>
                                <div style="font-size: 11px; color: #666;"><?php echo TEXT_BX_TOTP_QRCODE_FOR_SETUP; ?></div>
                              </td>
                              <td style="padding: 10px 5px; text-align: right;">
                                <?php if (TOTP_QRCODE_AVAILABLE): ?>
                                  <span style="color: #4CAF50; font-weight: bold;">✓ <?php echo TEXT_BX_TOTP_AVAILABLE; ?></span>
                                <?php else: ?>
                                  <span style="color: #f44336; font-weight: bold;">✗ <?php echo TEXT_BX_TOTP_NOT_INSTALLED; ?></span>
                                <?php endif; ?>
                              </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                              <td style="padding: 10px 5px;">
                                <strong><?php echo TEXT_BX_TOTP_TOTP_CLASS; ?></strong>
                                <div style="font-size: 11px; color: #666;"><?php echo TEXT_BX_TOTP_CLASS_FILE; ?></div>
                              </td>
                              <td style="padding: 10px 5px; text-align: right;">
                                <?php if (class_exists('bx_totp_helper')): ?> 
                                  <span style="color: #4CAF50; font-weight: bold;">✓ <?php echo TEXT_BX_TOTP_LOADED; ?></span>
                                <?php else: ?>
                                  <span style="color: #f44336; font-weight: bold;">✗ <?php echo TEXT_BX_TOTP_MISSING; ?></span>
                                <?php endif; ?>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding: 10px 5px;">
                                <strong><?php echo TEXT_BX_TOTP_DATABASE_TABLES; ?></strong>
                                <div style="font-size: 11px; color: #666;"><?php echo TEXT_BX_TOTP_TABLES_DESC; ?></div>
                              </td>
                              <td style="padding: 10px 5px; text-align: right;">
                                <?php 
                                $table_check = xtc_db_query("SHOW TABLES LIKE 'two_factor_email_codes'");
                                                                if (xtc_db_num_rows($table_check) > 0): ?>
                                  <span style="color: #4CAF50; font-weight: bold;">✓ <?php echo TEXT_BX_TOTP_TABLES_OK; ?></span>
                                <?php else: ?>
                                  <span style="color: #f44336; font-weight: bold;">✗ <?php echo TEXT_BX_TOTP_TABLES_MISSING; ?></span>
                                <?php endif; ?>
                              </td>
                            </tr>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </table>
                  
                  <div class="info_message" style="display: block; width: auto; margin-top: 15px;">
                    <h4>💡 <?php echo TEXT_BX_TOTP_QUICKSTART; ?></h4>
                    <ul>
                        <li><?php echo TEXT_BX_TOTP_QUICKSTART_CUSTOMERS; ?></li>
                        <li><?php echo TEXT_BX_TOTP_QUICKSTART_SUPPORT; ?></li>
                        <li><?php echo TEXT_BX_TOTP_QUICKSTART_LIST; ?></li>
                    </ul>
                  </div>
                </div>
                <!-- end tab-dashboard //-->

                <!-- TAB 2: KUNDEN-LISTE //-->
                <div id="tab-customers">
                  <?php
                  // Filter-Parameter
                  $filter_method = isset($_GET['filter_method']) ? $_GET['filter_method'] : 'all';
                  $search = isset($_GET['search']) ? xtc_db_prepare_input($_GET['search']) : '';
                  
                  // Query aufbauen
                  $customers_query_raw = "SELECT 
                    c.customers_id,
                    c.customers_firstname,
                    c.customers_lastname,
                    c.customers_email_address,
                    c.two_factor_enabled,
                    c.two_factor_method,
                    c.two_factor_secret_created
                  FROM " . TABLE_CUSTOMERS . " c
                  WHERE c.two_factor_enabled = 1";
                  
                  // Filter nach Methode
                  if ($filter_method != 'all') {
                    $customers_query_raw .= " AND c.two_factor_method = '" . xtc_db_input($filter_method) . "'";
                  }
                  
                  // Suche
                  if (!empty($search)) {
                    $customers_query_raw .= " AND (c.customers_firstname LIKE '%" . xtc_db_input($search) . "%' 
                                              OR c.customers_lastname LIKE '%" . xtc_db_input($search) . "%'
                                              OR c.customers_email_address LIKE '%" . xtc_db_input($search) . "%')";
                  }
                  
                  $customers_query_raw .= " ORDER BY c.two_factor_secret_created DESC";
                  $customers_query = xtc_db_query($customers_query_raw);
                  ?>
                  
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" colspan="6">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                          <strong>👥 <?php echo TEXT_BX_TOTP_CUSTOMERS_WITH_2FA; ?></strong>
                          <div>
                            <?php echo xtc_db_num_rows($customers_query); ?> <?php echo TEXT_BX_TOTP_CUSTOMERS_FOUND; ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="6" style="padding: 10px;">
                        <form method="get" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                          <div style="display: flex; gap: 10px; align-items: center;">
                            <div>
                              <label><strong><?php echo TEXT_BX_TOTP_FILTER; ?></strong></label>
                              <select name="filter_method" onchange="this.form.submit()">
                                <option value="all" <?php echo ($filter_method == 'all' ? 'selected' : ''); ?>><?php echo TEXT_BX_TOTP_ALL_METHODS; ?></option>
                                <option value="totp" <?php echo ($filter_method == 'totp' ? 'selected' : ''); ?>>🔑 <?php echo TEXT_BX_TOTP_ONLY_TOTP; ?></option>
                                <option value="email" <?php echo ($filter_method == 'email' ? 'selected' : ''); ?>>📧 <?php echo TEXT_BX_TOTP_ONLY_EMAIL; ?></option>
                              </select>
                            </div>
                            <div style="flex: 1;">
                              <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                     placeholder="<?php echo TEXT_BX_TOTP_SEARCH_PLACEHOLDER; ?>" style="width: 300px;">
                              <button type="submit" class="button">🔍 <?php echo TEXT_BX_TOTP_SEARCH_BUTTON; ?></button>
                              <?php if (!empty($search) || $filter_method != 'all'): ?>
                                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="button">✕ <?php echo TEXT_BX_TOTP_RESET_FILTER; ?></a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </form>
                      </td>
                    </tr>
                  </table>
                  
                  <table class="tableBoxCenter collapse" style="margin-top: 5px;">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" style="width: 40px;"><?php echo TEXT_BX_TOTP_TABLE_ID; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TEXT_BX_TOTP_TABLE_CUSTOMER; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TEXT_BX_TOTP_TABLE_EMAIL; ?></td>
                      <td class="dataTableHeadingContent" style="width: 120px; text-align: center;"><?php echo TEXT_BX_TOTP_TABLE_METHOD; ?></td>
                      <td class="dataTableHeadingContent" style="width: 150px; text-align: center;"><?php echo TEXT_BX_TOTP_TABLE_ACTIVATED; ?></td>
                      <td class="dataTableHeadingContent" style="width: 180px; text-align: center;"><?php echo TEXT_BX_TOTP_TABLE_ACTIONS; ?></td>
                    </tr>
                    <?php
                    if (xtc_db_num_rows($customers_query) > 0) {
                      $row_count = 0;
                      while ($customer = xtc_db_fetch_array($customers_query)) {
                        $row_count++;
                        $row_class = ($row_count % 2 == 0) ? 'dataTableRow' : 'dataTableRowAlt';
                        ?>
                        <tr class="<?php echo $row_class; ?>" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='<?php echo $row_class; ?>'">
                          <td class="dataTableContent" style="text-align: center;"><?php echo $customer['customers_id']; ?></td>
                          <td class="dataTableContent">
                            <strong><?php echo htmlspecialchars($customer['customers_firstname'] . ' ' . $customer['customers_lastname']); ?></strong>
                          </td>
                          <td class="dataTableContent"><?php echo htmlspecialchars($customer['customers_email_address']); ?></td>
                          <td class="dataTableContent" style="text-align: center;">
                            <?php if ($customer['two_factor_method'] == 'totp'): ?>
                              <span style="background: #4CAF50; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                🔑 TOTP
                              </span>
                            <?php else: ?>
                              <span style="background: #FF9800; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                📧 E-Mail
                              </span>
                            <?php endif; ?>
                          </td>
                          <td class="dataTableContent" style="text-align: center;">
                            <?php 
                            if (!empty($customer['two_factor_secret_created']) && $customer['two_factor_secret_created'] != '1000-01-01 00:00:00') {
                              echo date('d.m.Y H:i', strtotime($customer['two_factor_secret_created']));
                            } else {
                              echo '<span style="color: #999;">-</span>';
                            }
                            ?>
                          </td>
                          <td class="dataTableContent" style="text-align: center;">
                            <!-- <a href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . $customer['customers_id'] . '&action=edit'); ?>" 
                               class="button" title="<?php echo TEXT_BX_TOTP_EDIT_CUSTOMER; ?>" style="margin-right: 5px;">✏️</a> -->
                            
                            <?php 
                              echo xtc_draw_form('emergency_disable_2fa_' . $customer['customers_id'], FILENAME_BX_TOTP_AUTHENTICATOR, '', 'post', 'style="display: inline;"');
                              echo xtc_draw_hidden_field('action', 'emergency_disable_2fa'); 
                              echo xtc_draw_hidden_field('customer_id', $customer['customers_id']);
                            ?>
                              <button type="submit" class="button" 
                                      style="background: #f44336; color: white; border: 1px solid #d32f2f; cursor: pointer; padding: 6px 8px; font-size: 11px; font-weight: bold;" 
                                      title="<?php echo TEXT_BX_TOTP_DISABLE_2FA; ?>"
                                      onclick="return confirm('<?php echo sprintf(TEXT_BX_TOTP_CONFIRM_DISABLE, addslashes($customer['customers_firstname'] . ' ' . $customer['customers_lastname']), addslashes($customer['customers_email_address'])); ?>');">
                                <?php echo TEXT_BX_TOTP_DISABLE_BUTTON; ?>
                              </button>
                            </form>
                          </td>
                        </tr>
                        <?php
                      }
                    } else {
                      ?>
                      <tr class="dataTableRow">
                        <td class="dataTableContent" colspan="6" style="text-align: center; padding: 30px;">
                          <div style="color: #999; font-size: 13px;">
                            <?php if (!empty($search) || $filter_method != 'all'): ?>
                              🔍 <?php echo TEXT_BX_TOTP_NO_CUSTOMERS_CRITERIA; ?>
                            <?php else: ?>
                              ℹ️ <?php echo TEXT_BX_TOTP_NO_CUSTOMERS_YET; ?>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                  </table>
                  
                  <div class="info_message" style="display: block; width: auto; margin-top: 15px;">
                    <?php echo TEXT_BX_TOTP_CUSTOMERS_HINT; ?>
                  </div>
                </div>
                <!-- end tab-customers //-->

                <!-- TAB 3: SUPPORT-AKTIONEN //-->
                <div id="tab-support">
                  <table class="tableBoxCenter collapse" style="margin-top: 15px;">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent">
                        <strong>📝 <?php echo TEXT_BX_TOTP_SUPPORT_CASES; ?></strong>
                      </td>
                    </tr>
                    <tr class="dataTableRow">
                      <td class="dataTableContent">
                        <div style="padding: 15px;">
                          <div style="margin-bottom: 20px; padding: 12px; background: #f5f5f5; border-left: 4px solid #2196F3;">
                            <h4 style="margin-top: 0;"><?php echo TEXT_BX_TOTP_CASE1_TITLE; ?></h4>
                            <p><strong><?php echo TEXT_BX_TOTP_CASE1_SOLUTION; ?></strong></p>
                            <ul>
                              <li><?php echo TEXT_BX_TOTP_CASE1_POINT1; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE1_POINT2; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE1_POINT3; ?></li>
                            </ul>
                          </div>
                          
                          <div style="margin-bottom: 20px; padding: 12px; background: #f5f5f5; border-left: 4px solid #FF9800;">
                            <h4 style="margin-top: 0;"><?php echo TEXT_BX_TOTP_CASE2_TITLE; ?></h4>
                            <p><strong><?php echo TEXT_BX_TOTP_CASE2_CAUSES; ?></strong></p>
                            <ul>
                              <li><?php echo TEXT_BX_TOTP_CASE2_POINT1; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE2_POINT2; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE2_POINT3; ?></li>
                            </ul>
                          </div>
                          
                          <div style="margin-bottom: 20px; padding: 12px; background: #f5f5f5; border-left: 4px solid #4CAF50;">
                            <h4 style="margin-top: 0;"><?php echo TEXT_BX_TOTP_CASE3_TITLE; ?></h4>
                            <p><strong><?php echo TEXT_BX_TOTP_CASE3_CHECKS; ?></strong></p>
                            <ul>
                              <li><?php echo TEXT_BX_TOTP_CASE3_POINT1; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE3_POINT2; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE3_POINT3; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE3_POINT4; ?></li>
                            </ul>
                          </div>
                          
                          <div style="margin-bottom: 20px; padding: 12px; background: #f5f5f5; border-left: 4px solid #9C27B0;">
                            <h4 style="margin-top: 0;"><?php echo TEXT_BX_TOTP_CASE4_TITLE; ?></h4>
                            <p><strong><?php echo TEXT_BX_TOTP_CASE4_SOLUTION; ?></strong></p>
                            <ul>
                              <li><?php echo TEXT_BX_TOTP_CASE4_POINT1; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE4_POINT2; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE4_POINT3; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE4_POINT4; ?></li>
                            </ul>
                          </div>
                          
                          <div style="padding: 12px; background: #f5f5f5; border-left: 4px solid #FF5722;">
                            <h4 style="margin-top: 0;"><?php echo TEXT_BX_TOTP_CASE5_TITLE; ?></h4>
                            <p><strong><?php echo TEXT_BX_TOTP_CASE5_SOLUTIONS; ?></strong></p>
                            <ul>
                              <li><?php echo TEXT_BX_TOTP_CASE5_POINT1; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE5_POINT2; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE5_POINT3; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE5_POINT4; ?></li>
                              <li><?php echo TEXT_BX_TOTP_CASE5_POINT5; ?></li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </table>
                </div>
                <!-- end tab-support //-->

              </div>
            </div>

          </td>
          <td class="boxRight">
<?php

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>⚡ '. TEXT_BX_TOTP_QUICK_ACTIONS. '</strong>');
  $contents[] = array('text' => '<strong>'. TEXT_BX_TOTP_MODULE_SETTINGS. '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_totp_authenticator&action=edit').'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">⚙️</span> '. TEXT_BX_TOTP_CONFIGURATION. '</a>');
  $contents[] = array('text' => '<strong>'. TEXT_BX_TOTP_CUSTOMER_MANAGEMENT. '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_CUSTOMERS).'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">🧑‍💼</span> '. TEXT_BX_TOTP_ALL_CUSTOMERS. '</a>');
  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>📊 '. TEXT_BX_TOTP_QUICK_OVERVIEW. '</strong>');
  $contents[] = array('text' => '<div style="font-size: 24px; font-weight: bold; color: #AF417E; text-align: center;">'.
                                $enabled_percentage.'%</div><div style="text-align: center; color: #666;">'. TEXT_BX_TOTP_ACTIVATION_RATE. '</div>');
  $contents[] = array('text' => '<strong>'. TEXT_BX_TOTP_ACTIVE_USERS. '</strong><br>'.
                                $stats['enabled_count'].' '.TEXT_BX_TOTP_OF.' '.$stats['total_customers'].' '.TEXT_BX_TOTP_TABLE_CUSTOMER);
  $contents[] = array('text' => '🔑 TOTP: <strong>'.$stats['totp_count'].'</strong><br>'.  
                                '📧 E-Mail: <strong>'.$stats['email_count'].'</strong>');
  
  if (!TOTP_QRCODE_AVAILABLE) {
    $contents[] = array('text' => '<div style="color: orange; margin-top: 10px;">⚠️ '. TEXT_BX_TOTP_QRCODE_LIBRARY_MISSING. '</div>');
  }

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }
  
  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>ℹ️ '. TEXT_BX_TOTP_HINTS. '</strong>');
  $contents[] = array('text' => TEXT_BX_TOTP_HINT1);
  $contents[] = array('text' => TEXT_BX_TOTP_HINT2);
  $contents[] = array('text' => TEXT_BX_TOTP_HINT3);

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES.'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>