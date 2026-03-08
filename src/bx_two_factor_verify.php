<?php
/* -----------------------------------------------------------------------------------------
   $Id: bx_two_factor_verify.php 16358 2026-01-20 12:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   BX Two-Factor Authentication - Verification Page
   
   Shows after login when customer has 2FA enabled.
   User must enter TOTP code, email code, or backup code.
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty
$smarty = new Smarty();

// Check if there's a pending 2FA verification
if (!isset($_SESSION['bx_2fa_pending_customer_id'])) {
    // No pending verification, redirect to login
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// Check timeout (15 minutes)
$timeout = 900; // 15 minutes
if (isset($_SESSION['bx_2fa_pending_time']) && (time() - $_SESSION['bx_2fa_pending_time']) > $timeout) {
    unset($_SESSION['bx_2fa_pending_customer_id']);
    unset($_SESSION['bx_2fa_pending_time']);
    unset($_SESSION['bx_2fa_email_sent']);
    unset($_SESSION['bx_2fa_email_expires']);
    
    $messageStack->add_session('login', TEXT_BX_2FA_VERIFY_ERROR_TIMEOUT);
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$customerId = (int)$_SESSION['bx_2fa_pending_customer_id'];

// Load 2FA handler
require_once(DIR_FS_CATALOG . 'includes/classes/bx_two_factor_auth.php');
$twoFactorAuth = new bx_two_factor_auth();

// Get customer data
$customer_query = xtc_db_query("SELECT customers_gender, customers_firstname, customers_lastname, customers_email_address, customers_password_time FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . (int)$customerId . "'");
$customer_data = xtc_db_fetch_array($customer_query);

if (!$customer_data) {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$method   = $twoFactorAuth->getMethod($customerId);
$messages = [];
$verificationSuccess = false;

// ========================================
// PROCESS VERIFICATION
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $code      = isset($_POST['code'])       ? xtc_db_prepare_input($_POST['code']) : '';
    $useBackup = isset($_POST['use_backup']) ? true : false;
    
    if (empty($code)) {
        $messages[] = ['type' => 'error', 'text' => TEXT_BX_2FA_VERIFY_ERROR_ENTER_CODE];
    } else {
        // Verify code
        if ($useBackup) {
            // Verify backup code
            $result = $twoFactorAuth->verifyBackupCode($customerId, $code);
            
            if ($result['success']) {
                $verificationSuccess = true;
                
                if ($result['remaining_codes'] < 3) {
                    $messages[] = ['type' => 'warning', 'text' => sprintf(TEXT_BX_2FA_VERIFY_WARNING_LOW_BACKUP_CODES, $result['remaining_codes'])];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => $result['message']];
            }
        } else {
            // Verify TOTP or email code
            $result = $twoFactorAuth->verify($customerId, $code);
            
            if ($result['success']) {
                $verificationSuccess = true;
            } else {
                $messages[] = ['type' => 'error', 'text' => $result['message']];
            }
        }
        
        // If verification successful, complete login
        if ($verificationSuccess) {
            // Set session
            $_SESSION['customer_id']     = $customerId;
            $_SESSION['customer_time']   = $customer_data['customers_password_time'];
            $_SESSION['bx_2fa_verified'] = true;
            
            // Clear pending data
            unset($_SESSION['bx_2fa_pending_customer_id']);
            unset($_SESSION['bx_2fa_pending_time']);
            unset($_SESSION['bx_2fa_email_sent']);
            unset($_SESSION['bx_2fa_email_expires']);
            
            // Update last login
            xtc_db_query("UPDATE " . TABLE_CUSTOMERS_INFO . " 
                         SET customers_info_date_of_last_logon = NOW(), 
                             customers_info_number_of_logons = customers_info_number_of_logons + 1 
                         WHERE customers_info_id = '" . $customerId . "'");
            
            // Write customers status session
            require(DIR_WS_INCLUDES . 'write_customers_status.php');
            
            // Write customers session
            require_once(DIR_FS_INC . 'write_customers_session.inc.php');
            write_customers_session($customerId);
            
            // User info
            require_once(DIR_FS_INC . 'xtc_write_user_info.inc.php');
            xtc_write_user_info($customerId);
            
            // Who's online
            xtc_update_whos_online();
            
            // Restore cart contents
            $_SESSION['cart']->restore_contents();
            
            // Restore wishlist
            if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
                $_SESSION['wishlist']->restore_contents();
            }
            
            // Success message
            $messageStack->add_session('global', TEXT_BX_2FA_VERIFY_SUCCESS_LOGIN, 'success');
            
            // Redirect to account or original destination
            if (isset($_SESSION['navigation']) && is_object($_SESSION['navigation'])) {
                $redirect = $_SESSION['navigation']->snapshot;
                if (!empty($redirect)) {
                    $_SESSION['navigation']->clear_snapshot();
                    xtc_redirect($redirect);
                }
            }
            
            xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
            exit;
        }
    }
}

// Resend email code
if (isset($_GET['action']) && $_GET['action'] === 'resend_email' && $method === 'email') {
    $emailResult = $twoFactorAuth->sendEmailCode($customerId);
    
    if ($emailResult['success']) {
        $_SESSION['bx_2fa_email_sent'] = true;
        $_SESSION['bx_2fa_email_expires'] = time() + $emailResult['expires_in'];
        $messages[] = ['type' => 'success', 'text' => $emailResult['message']];
    } else {
        $messages[] = ['type' => 'error', 'text' => $emailResult['message']];
    }
}

// Cancel verification
if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    unset($_SESSION['bx_2fa_pending_customer_id']);
    unset($_SESSION['bx_2fa_pending_time']);
    unset($_SESSION['bx_2fa_email_sent']);
    unset($_SESSION['bx_2fa_email_expires']);
    
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
    exit;
}

// ========================================
// TEMPLATE
// ========================================

$breadcrumb->add(TEXT_BX_2FA_VERIFY_BREADCRUMB_LOGIN, xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
$breadcrumb->add(TEXT_BX_2FA_VERIFY_BREADCRUMB_2FA, xtc_href_link(FILENAME_BX_TWO_FACTOR_VERIFY, '', 'SSL'));

// Messages
if (!empty($messages)) {
    $smarty->assign('messages', $messages);
}

// Error from email sending
if (isset($_SESSION['bx_2fa_email_error'])) {
    $smarty->assign('email_error', $_SESSION['bx_2fa_email_error']);
    unset($_SESSION['bx_2fa_email_error']);
}

// Customer info
$smarty->assign('customer_email', $customer_data['customers_email_address']);
$smarty->assign('customer_firstname', $customer_data['customers_firstname']);
$smarty->assign('customer_lastname', $customer_data['customers_lastname']);

// Gender salutation
switch ($customer_data['customers_gender']) {
    case 'm':
        $smarty->assign('customer_salutation', GENDER_MALE);
        break;
    case 'f':
        $smarty->assign('customer_salutation', GENDER_FEMALE);
        break;
    case 'd':
        $smarty->assign('customer_salutation', defined('GENDER_DIVERSE') ? GENDER_DIVERSE : '');
        break;
    default:
        $smarty->assign('customer_salutation', '');
}

// Method
$smarty->assign('method', $method);

// Email code info
if ($method === 'email') {
    $smarty->assign('email_sent', isset($_SESSION['bx_2fa_email_sent']) && $_SESSION['bx_2fa_email_sent']);
    
    if (isset($_SESSION['bx_2fa_email_expires'])) {
        $remainingTime = max(0, $_SESSION['bx_2fa_email_expires'] - time());
        $smarty->assign('email_expires_in', $remainingTime);
    }
}

// Remaining time for session 
$remainingSessionTime = $timeout - (time() - $_SESSION['bx_2fa_pending_time']);
$smarty->assign('session_expires_in', $remainingSessionTime);

// Forms
$smarty->assign('form_action_verify', xtc_draw_form('verify_2fa', xtc_href_link(FILENAME_BX_TWO_FACTOR_VERIFY, '', 'SSL'), 'post'));
$smarty->assign('form_end', '</form>');

// Links
$smarty->assign('link_resend_email', xtc_href_link(FILENAME_BX_TWO_FACTOR_VERIFY, 'action=resend_email', 'SSL'));
$smarty->assign('link_cancel', xtc_href_link(FILENAME_BX_TWO_FACTOR_VERIFY, 'action=cancel', 'SSL'));

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/bx_two_factor_verify.html');
$smarty->assign('main_content', $main_content);

// include header
require (DIR_WS_INCLUDES.'header.php');

// include boxes
$display_mode = 'account';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/bx_two_factor_verify.html');
$smarty->assign('main_content', $main_content);

if (!defined('RM'))
  $smarty->load_filter('output', 'note');

$smarty->display(CURRENT_TEMPLATE . '/index.html');

require (DIR_WS_INCLUDES.'application_bottom.php');
