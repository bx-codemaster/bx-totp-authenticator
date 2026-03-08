<?php
/* -----------------------------------------------------------------------------------------
   $Id: bx_two_factor_account.php 16358 2026-01-20 12:00:00Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   BX Two-Factor Authentication - Account Management
   
   Allows customers to:
   - Enable/disable 2FA
   - Choose method (TOTP or Email)
   - View QR code for TOTP setup
   - Generate/view backup codes
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty
$smarty = new Smarty();

// Customer must be logged in
if (!isset($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
} elseif (isset($_SESSION['customer_id']) 
          && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
          && GUEST_ACCOUNT_EDIT != 'true'
          )
{ 
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

$bx_dependency_resolver = false;
if(file_exists(DIR_FS_CATALOG . 'includes/classes/bx_dependency_resolver.php')) {
    require_once (DIR_FS_CATALOG . 'includes/classes/bx_dependency_resolver.php');
    $bx_dependency_resolver = true;
}

// Defaults setzen
$qrcode_available = false;
$tcpdf_available  = false;

if ($bx_dependency_resolver) {
    try {
        $result = bx_dependency_resolver::requireMultiple([
            'modified_qrcode',
            'modified_tcpdf'
        ]);
        
        // Verfügbarkeit prüfen (assoziatives Array)
        foreach ($result as $project_name => $project_data) {
            if ($project_name === 'modified_qrcode' && $project_data['status'] === 'loaded') {
                $qrcode_available = true;
            }
            if ($project_name === 'modified_tcpdf' && $project_data['status'] === 'loaded') {
                $tcpdf_available = true;
            }
        }
    } catch (Exception $e) {
        // Optional dependencies - log but continue
        error_log('2FA optional dependencies not available: ' . $e->getMessage());
    }
}

define('BX_DEPENDENCY_RESOLVER_AVAILABLE', $bx_dependency_resolver);
define('TOTP_QRCODE_AVAILABLE', $qrcode_available);
define('TOTP_TCPDF_AVAILABLE', $tcpdf_available);

// Load 2FA handler
require_once(DIR_FS_CATALOG . 'includes/classes/bx_two_factor_auth.php');
$twoFactorAuth = new bx_two_factor_auth();

// ========================================
$customerId = (int)$_SESSION['customer_id'];
$action     = isset($_GET['action']) ? $_GET['action'] : '';
$messages   = [];

// Get customer email for display
$customer_query = xtc_db_query("SELECT customers_email_address, customers_firstname 
                                FROM " . TABLE_CUSTOMERS . " 
                                WHERE customers_id = '" . $customerId . "'");
$customer_data = xtc_db_fetch_array($customer_query);

// ========================================
// ACTIONS
// ========================================

// Enable 2FA
if ($action === 'enable' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $method     = isset($_POST['method'])      ? $_POST['method']      : 'totp';
    $totpCode   = isset($_POST['totp_code'])   ? $_POST['totp_code']   : '';
    $totpSecret = isset($_POST['totp_secret']) ? $_POST['totp_secret'] : '';
    
    if ($method === 'totp') {
        // Verify TOTP code before enabling
        if (empty($totpCode) || empty($totpSecret)) {
            $messages[] = ['type' => 'error', 'text' => TEXT_BX_2FA_ERROR_ENTER_CODE];
        } else {
            require_once(DIR_FS_CATALOG . 'admin/includes/classes/bx_totp_helper.php');
            $totp = new bx_totp_helper();
            
            if ($totp->verifyCode($totpSecret, $totpCode, 2)) {
                $result = $twoFactorAuth->enable($customerId, 'totp', $totpSecret);
                
                if ($result['success']) {
                    $_SESSION['bx_2fa_backup_codes'] = $result['backup_codes'];
                    xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=show_backup_codes', 'SSL'));
                } else {
                    $messages[] = ['type' => 'error', 'text' => $result['message']];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => TEXT_BX_2FA_ERROR_INVALID_CODE];
            }
        }
    } elseif ($method === 'email') {
        // For email, send test code first
        $emailResult = $twoFactorAuth->sendEmailCode($customerId);
        
        if ($emailResult['success']) {
            $_SESSION['bx_2fa_email_setup'] = [
                'pending' => true,
                'expires' => time() + $emailResult['expires_in']
            ];
            xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=verify_email', 'SSL'));
        } else {
            $messages[] = ['type' => 'error', 'text' => $emailResult['message']];
        }
    }
}

// Verify email code during setup
if ($action === 'verify_email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailCode = isset($_POST['email_code']) ? $_POST['email_code'] : '';
    
    if (!empty($emailCode)) {
        // Load email handler directly (2FA is not enabled yet during setup)
        require_once(DIR_FS_CATALOG . 'includes/classes/bx_two_factor_email_handler.php');
        $emailHandler = new bx_two_factor_email_handler();
        
        // Verify the code directly via email handler
        $verifyResult = $emailHandler->verifyCode($customerId, $emailCode);
        
        if ($verifyResult['success']) {
            // Enable email 2FA
            $result = $twoFactorAuth->enable($customerId, 'email');
            
            if ($result['success']) {
                unset($_SESSION['bx_2fa_email_setup']);
                $_SESSION['bx_2fa_backup_codes'] = $result['backup_codes'];
                xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=show_backup_codes', 'SSL'));
            } else {
                $messages[] = ['type' => 'error', 'text' => $result['message']];
            }
        } else {
            $messages[] = ['type' => 'error', 'text' => $verifyResult['message']];
        }
    }
}

// Cancel email setup
if ($action === 'cancel_email_setup') {
    unset($_SESSION['bx_2fa_email_setup']);
    xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, '', 'SSL'));
}

// Disable 2FA
if ($action === 'disable' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = isset($_POST['confirm_disable']) ? $_POST['confirm_disable'] : '';
    
    if ($confirm === 'yes') {
        $result = $twoFactorAuth->disable($customerId);
        $messages[] = ['type' => 'success', 'text' => $result['message']];
        unset($_SESSION['bx_2fa_backup_codes']);
    }
}

// Regenerate backup codes
if ($action === 'regenerate_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $twoFactorAuth->regenerateBackupCodes($customerId);
    
    if ($result['success']) {
        $_SESSION['bx_2fa_backup_codes'] = $result['backup_codes'];
        xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=show_backup_codes', 'SSL'));
    } else {
        $messages[] = ['type' => 'error', 'text' => $result['message']];
    }
}

// Change method
if ($action === 'change_method' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $newMethod = isset($_POST['new_method']) ? $_POST['new_method'] : '';
    
    if ($newMethod === 'totp') {
        // Need to setup TOTP first
        xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=setup_totp', 'SSL'));
    } elseif ($newMethod === 'email') {
        $result = $twoFactorAuth->changeMethod($customerId, 'email');
        $messages[] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];
    }
}

// Download backup codes as PDF
if ($action === 'download_backup_codes_pdf') {
    if (!TOTP_TCPDF_AVAILABLE) {
        $messageStack->add_session('bx_two_factor_account', TEXT_BX_2FA_ERROR_TCPDF_NOT_AVAILABLE, 'error');
        xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT));
        exit;
    }
    
    // Get backup codes from session
    if (!isset($_SESSION['bx_2fa_backup_codes']) || empty($_SESSION['bx_2fa_backup_codes'])) {
        $messages[] = ['type' => 'error', 'text' => TEXT_BX_2FA_ERROR_NO_BACKUP_CODES];
    } else {
        $backupCodes = $_SESSION['bx_2fa_backup_codes'];
        
        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Document info
        $shopName = defined('STORE_NAME') ? STORE_NAME : 'Modified Shop';
        $pdf->SetCreator('Modified eCommerce');
        $pdf->SetAuthor($shopName);
        $pdf->SetTitle(TEXT_BX_2FA_PDF_META_TITLE);
        $pdf->SetSubject(TEXT_BX_2FA_PDF_META_SUBJECT);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        
        // Add page
        $pdf->AddPage();
        
        // Logo (if exists)
        $logoPath = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/logo_head.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, 20, 40, 0, 'PNG');
            $pdf->Ln(25);
        }
        
        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 10, TEXT_BX_2FA_PDF_TITLE, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, TEXT_BX_2FA_PDF_SUBTITLE, 0, 1, 'C');
        $pdf->Ln(5);
        
        // Shop name
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, $shopName, 0, 1, 'C');
        $pdf->Ln(10);
        
        // Customer info
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_CUSTOMER . ' ' . $customer_data['customers_firstname'], 0, 1, 'L');
        $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_EMAIL . ' ' . $customer_data['customers_email_address'], 0, 1, 'L');
        $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_DATE . ' ' . date('d.m.Y H:i'), 0, 1, 'L');
        $pdf->Ln(10);
        
        // Warning box
        $pdf->SetFillColor(255, 243, 205);
        $pdf->SetDrawColor(133, 100, 4);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->MultiCell(0, 6, TEXT_BX_2FA_PDF_IMPORTANT_TITLE, 1, 'C', true, 1, '', '', true, 0, false, true, 6, 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5, 
            TEXT_BX_2FA_PDF_IMPORTANT_TEXT, 
            1, 'L', true, 1, '', '', true, 0, false, true, 5, 'T'
        );
        $pdf->Ln(10);
        
        // Backup codes title
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, TEXT_BX_2FA_PDF_YOUR_CODES, 0, 1, 'L');
        $pdf->Ln(5);
        
        // Codes in 2-column grid
        $pdf->SetFont('courier', 'B', 14);
        $colWidth = 85;
        $rowHeight = 12;
        $x = 20;
        $y = $pdf->GetY();
        
        foreach ($backupCodes as $index => $code) {
            $col = $index % 2;
            $row = floor($index / 2);
            
            $xPos = $x + ($col * $colWidth);
            $yPos = $y + ($row * $rowHeight);
            
            $pdf->SetXY($xPos, $yPos);
            
            // Background
            $pdf->SetFillColor(248, 249, 250);
            $pdf->SetDrawColor(224, 224, 224);
            $pdf->SetLineWidth(0.3);
            $pdf->Cell($colWidth - 5, $rowHeight - 2, $code, 1, 0, 'C', true);
        }
        
        $pdf->Ln(($rowHeight * ceil(count($backupCodes) / 2)) + 10);
        
        // Footer info
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(0, 4, 
            sprintf(TEXT_BX_2FA_PDF_FOOTER_TEXT, date('d.m.Y um H:i')), 
            0, 'C', false, 1, '', '', true, 0, false, true, 4, 'T'
        );
        
        // Output PDF
        $filename = sprintf(TEXT_BX_2FA_PDF_FILENAME, date('Y-m-d'));
        $pdf->Output($filename, 'D'); // 'D' = Download
        exit;
    }
}
if ($action === 'download_totp_secret_pdf') {
    if (!TOTP_TCPDF_AVAILABLE) {
        $messageStack->add_session('bx_two_factor_account', TEXT_BX_2FA_ERROR_TCPDF_NOT_AVAILABLE, 'error');
        xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=setup_totp', 'SSL'));
        exit;
    }
    
    $totpSecret = isset($_GET['totp_secret']) ? $_GET['totp_secret'] : '';
    
    if (empty($totpSecret)) {
        $messages[] = ['type' => 'error', 'text' => TEXT_BX_2FA_ERROR_NO_TOTP_SECRET];
        xtc_redirect(xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=setup_totp', 'SSL'));
        exit;
    } else {
        try {
            // Create PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Document info
            $shopName = defined('STORE_NAME') ? STORE_NAME : 'Modified Shop';
            $pdf->SetCreator('Modified eCommerce');
            $pdf->SetAuthor($shopName);
            $pdf->SetTitle(TEXT_BX_2FA_PDF_TOTP_META_TITLE);
            $pdf->SetSubject(TEXT_BX_2FA_PDF_TOTP_META_SUBJECT);
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(20, 20, 20);
            $pdf->SetAutoPageBreak(true, 20);
            
            // Add page
            $pdf->AddPage();
            
            // Logo (if exists)
            $logoPath = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/logo_head.png';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 20, 20, 40, 0, 'PNG');
                $pdf->Ln(25);
            }
            
            // Title
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->Cell(0, 10, TEXT_BX_2FA_PDF_TOTP_TITLE, 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 8, TEXT_BX_2FA_PDF_TOTP_SUBTITLE, 0, 1, 'C');
            $pdf->Ln(10);
            
            // Shop name
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 8, $shopName, 0, 1, 'C');
            $pdf->Ln(10);
            
            // Customer info
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_CUSTOMER . ' ' . $customer_data['customers_firstname'], 0, 1, 'L');
            $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_EMAIL . ' ' . $customer_data['customers_email_address'], 0, 1, 'L');
            $pdf->Cell(0, 6, TEXT_BX_2FA_PDF_DATE . ' ' . date('d.m.Y H:i'), 0, 1, 'L');
            $pdf->Ln(10);
            
            // Warning box
            $pdf->SetFillColor(255, 243, 205);
            $pdf->SetDrawColor(133, 100, 4);
            $pdf->SetLineWidth(0.5);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->MultiCell(0, 6, TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TITLE, 1, 'C', true, 1, '', '', true, 0, false, true, 6, 'M');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5, 
                TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TEXT, 
                1, 'L', true, 1, '', '', true, 0, false, true, 5, 'T'
            );
            $pdf->Ln(10);
            
            // TOTP Secret title
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, TEXT_BX_2FA_PDF_TOTP_SECRET_LABEL, 0, 1, 'L');
            $pdf->Ln(5);
            
            // Secret code in box
            $pdf->SetFont('courier', 'B', 16);
            $pdf->SetFillColor(248, 249, 250);
            $pdf->SetDrawColor(224, 224, 224);
            $pdf->SetLineWidth(0.3);
            $pdf->Cell(0, 15, $totpSecret, 1, 1, 'C', true);
            $pdf->Ln(10);
            
            // Instructions
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 5, TEXT_BX_2FA_PDF_TOTP_INSTRUCTIONS, 0, 'L', false, 1);
            $pdf->Ln(5);
            
            // Footer info
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(0, 4, 
                sprintf(TEXT_BX_2FA_PDF_TOTP_FOOTER_TEXT, date('d.m.Y um H:i')), 
                0, 'C', false, 1, '', '', true, 0, false, true, 4, 'T'
            );
            
            // Output PDF
            $filename = sprintf(TEXT_BX_2FA_PDF_TOTP_FILENAME, date('Y-m-d'));
            $pdf->Output($filename, 'D'); // 'D' = Download
            exit;
        } catch (Exception $e) {
            error_log('2FA PDF Error: ' . $e->getMessage());
            die('Fehler beim Erstellen des PDFs: ' . $e->getMessage());
        }
    }
}
// ========================================
// VIEW DATA
// ========================================

$twoFactorEnabled = $twoFactorAuth->isEnabled($customerId);
$currentMethod    = $twoFactorAuth->getMethod($customerId) ?: 'none';
$twoFactorData    = $twoFactorAuth->getCustomerData($customerId);

// Generate TOTP secret if needed
$totpSecret = '';
$totpQrCode = '';

if (($action === 'setup_totp' || $action === 'enable') && (false === $twoFactorEnabled || $currentMethod === 'email')) {

    $totpSecret = $twoFactorAuth->createTOTPSecret();
    
    if (true === TOTP_QRCODE_AVAILABLE) {
        try {
            $shopName = defined('STORE_NAME') ? STORE_NAME : 'Modified Shop';
            $qrCodeBase64 = $twoFactorAuth->generateTOTPQRCode(
                $totpSecret,
                $customer_data['customers_email_address'],
                $shopName
            );

            if ($qrCodeBase64 !== false && !empty($qrCodeBase64)) {
                // Format as data URI for img src
                $totpQrCode = 'data:image/png;base64,' . $qrCodeBase64;
            } else {
                $messages[] = ['type' => 'warning', 'text' => TEXT_BX_2FA_WARNING_QR_CODE_FAILED];
            }
        } catch (Exception $e) {
            $messages[] = ['type' => 'warning', 'text' => sprintf(TEXT_BX_2FA_WARNING_QR_CODE_ERROR, $e->getMessage())];
        }
    } else {
        $messages[] = ['type' => 'info', 'text' => TEXT_BX_2FA_INFO_QR_CODE_UNAVAILABLE];
    }

    if(false === BX_DEPENDENCY_RESOLVER_AVAILABLE) {
        $messages[] = ['type' => 'warning', 'text' => TEXT_BX_2FA_INFO_DEPENDENCY_RESOLVER_UNAVAILABLE];
    }
}

// Email setup pending?
$emailSetupPending = isset($_SESSION['bx_2fa_email_setup']['pending']) && $_SESSION['bx_2fa_email_setup']['pending'];

// Backup codes to display?
$showBackupCodes = ($action === 'show_backup_codes' && isset($_SESSION['bx_2fa_backup_codes']));
$backupCodesToDisplay = $showBackupCodes ? $_SESSION['bx_2fa_backup_codes'] : [];

// Remaining backup codes count
$remainingBackupCodes = $twoFactorAuth->getRemainingBackupCodesCount($customerId);

// ========================================
// SMARTY TEMPLATE
// ========================================

$breadcrumb->add(TEXT_BX_2FA_BREADCRUMB_ACCOUNT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(TEXT_BX_2FA_BREADCRUMB_2FA, xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, '', 'SSL'));

// Messages
if (!empty($messages)) {
    $smarty->assign('messages', $messages);
}

// Customer info
$smarty->assign('customer_email', $customer_data['customers_email_address']);
$smarty->assign('customer_firstname', $customer_data['customers_firstname']);

// 2FA Status
$smarty->assign('two_factor_enabled', $twoFactorEnabled);
$smarty->assign('current_method', $currentMethod);
$smarty->assign('current_action', $action);

// TOTP Setup
$smarty->assign('totp_secret', $totpSecret);
$smarty->assign('totp_qr_code', $totpQrCode);
$smarty->assign('totp_qr_code_available', TOTP_QRCODE_AVAILABLE);

// Email Setup
$smarty->assign('email_setup_pending', $emailSetupPending);

// Backup Codes
$smarty->assign('show_backup_codes', $showBackupCodes);
$smarty->assign('backup_codes', $backupCodesToDisplay);
$smarty->assign('remaining_backup_codes', $remainingBackupCodes);

// Forms
$smarty->assign('form_action_enable', xtc_draw_form('enable_2fa', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=enable', 'SSL'), 'post'));
$smarty->assign('form_action_disable', xtc_draw_form('disable_2fa', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=disable', 'SSL'), 'post'));
$smarty->assign('form_action_verify_email', xtc_draw_form('verify_email', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=verify_email', 'SSL'), 'post'));
$smarty->assign('form_action_regenerate', xtc_draw_form('regenerate_backup', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=regenerate_backup', 'SSL'), 'post'));
$smarty->assign('form_action_change', xtc_draw_form('change_method', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=change_method', 'SSL'), 'post'));
$smarty->assign('form_end', '</form>');

// Links
$smarty->assign('link_account', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, '', 'SSL'));
$smarty->assign('link_setup_totp', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=setup_totp', 'SSL'));
$smarty->assign('link_setup_email', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=enable&method=email', 'SSL'));
$smarty->assign('link_view_codes', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=view_backup_codes', 'SSL'));
$smarty->assign('link_download_pdf', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=download_backup_codes_pdf', 'SSL'));
$smarty->assign('link_download_totp_secret_pdf', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=download_totp_secret_pdf&totp_secret=' . urlencode($totpSecret), 'SSL'));
$smarty->assign('link_cancel_email_setup', xtc_href_link(FILENAME_BX_TWO_FACTOR_ACCOUNT, 'action=cancel_email_setup', 'SSL'));
$smarty->assign('tcpdf_available', TOTP_TCPDF_AVAILABLE);

// Load custom CSS for 2FA
$smarty->assign('HEAD_TAG_ADDITIONAL_CSS', '<link rel="stylesheet" href="' . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/css/bx_two_factor.css" type="text/css" />');

// include header
require (DIR_WS_INCLUDES.'header.php');

// include boxes
$display_mode = 'account';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/bx_two_factor_account.html');

$smarty->assign('main_content', $main_content);

$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');

$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
