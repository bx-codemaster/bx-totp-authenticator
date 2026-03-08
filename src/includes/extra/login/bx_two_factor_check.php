<?php
/**
 * BX Two-Factor Authentication - Login Integration
 * 
 * This file is auto-included after successful login in login.php
 * It checks if customer has 2FA enabled and redirects to verification page.
 * 
 * Location: includes/extra/login/bx_two_factor_check.php
 */

// Only execute if module is installed and active
if (!defined('MODULE_BX_TOTP_AUTHENTICATOR_STATUS')
    || MODULE_BX_TOTP_AUTHENTICATOR_STATUS != 'True'
    )
{
    return;
}

// Only execute if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    return;
}

$customerId = (int)$_SESSION['customer_id'];

// Load 2FA handler
if (!class_exists('bx_two_factor_auth')) {
    require_once(DIR_FS_CATALOG . 'includes/classes/bx_two_factor_auth.php');
}

$twoFactorAuth = new bx_two_factor_auth();

// Check if 2FA is enabled for this customer
if (!$twoFactorAuth->isEnabled($customerId)) {
    // 2FA not enabled, continue normal login
    return;
}

// Check if 2FA is already verified in this session
if (isset($_SESSION['bx_2fa_verified']) && $_SESSION['bx_2fa_verified'] === true) {
    // Already verified, continue
    return;
}

// 2FA is enabled but not yet verified
// Store customer_id in temporary session and clear the main one
$_SESSION['bx_2fa_pending_customer_id'] = $customerId;
$_SESSION['bx_2fa_pending_time'] = time();

// Clear main customer session (partial authentication)
unset($_SESSION['customer_id']);
unset($_SESSION['customer_time']);

// Get method for display
$method = $twoFactorAuth->getMethod($customerId);

// For email method, send code immediately
if ($method === 'email') {
    $emailResult = $twoFactorAuth->sendEmailCode($customerId);
    
    if ($emailResult['success']) {
        $_SESSION['bx_2fa_email_sent'] = true;
        $_SESSION['bx_2fa_email_expires'] = time() + $emailResult['expires_in'];
    } else {
        $_SESSION['bx_2fa_email_error'] = $emailResult['message'];
    }
}

// Redirect to 2FA verification page
xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_VERIFY, '', 'SSL'));
exit; // Stop execution to prevent further redirects in login.php
