<?php
/* -----------------------------------------------------------------------------------------
   $Id: /lang/english/extra/bx_two_factor_account.php 1000 2026-01-22 12:00:00Z benax $
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org
   
   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Breadcrumb
define('TEXT_BX_2FA_BREADCRUMB_ACCOUNT', 'My Account');
define('TEXT_BX_2FA_BREADCRUMB_2FA', 'Two-Factor Authentication');

// Error messages - TOTP Setup
define('TEXT_BX_2FA_ERROR_ENTER_CODE', 'Please enter the code from your authenticator app.');
define('TEXT_BX_2FA_ERROR_INVALID_CODE', 'Invalid code. Please try again.');
define('TEXT_BX_2FA_ERROR_NO_BACKUP_CODES', 'No backup codes available.');
define('TEXT_BX_2FA_ERROR_NO_TOTP_SECRET', 'No TOTP secret available.');

// Warnings and Info
define('TEXT_BX_2FA_WARNING_QR_CODE_FAILED', 'QR code could not be generated. Please use manual entry.');
define('TEXT_BX_2FA_WARNING_QR_CODE_ERROR', 'QR code error: %s - Please use manual entry.');
define('TEXT_BX_2FA_INFO_QR_CODE_UNAVAILABLE', 'QR code library not available. Please enter the secret code manually.');

// PDF Backup Codes
define('TEXT_BX_2FA_PDF_TITLE', 'Backup Codes');
define('TEXT_BX_2FA_PDF_SUBTITLE', 'Two-Factor Authentication');
define('TEXT_BX_2FA_PDF_CUSTOMER', 'Customer:');
define('TEXT_BX_2FA_PDF_EMAIL', 'Email:');
define('TEXT_BX_2FA_PDF_DATE', 'Date:');

// PDF Warning Box
define('TEXT_BX_2FA_PDF_IMPORTANT_TITLE', 'IMPORTANT: Keep Secure!');
define('TEXT_BX_2FA_PDF_IMPORTANT_TEXT', "These codes allow access to your account if you don't have access to your authenticator app or emails.\n\n" .
    "• Each code can only be used ONCE\n" .
    "• Store these codes in a secure location\n" .
    "• Never share these codes with anyone");

// PDF Codes Section
define('TEXT_BX_2FA_PDF_YOUR_CODES', 'Your Backup Codes:');
define('TEXT_BX_2FA_PDF_FOOTER_TEXT', "These codes were generated on %s.\nYou can generate new codes at any time in your account.");

// PDF Filename Pattern
define('TEXT_BX_2FA_PDF_FILENAME', '2FA_Backup_Codes_%s.pdf'); // %s = date('Y-m-d')

// Meta Data for PDF
define('TEXT_BX_2FA_PDF_META_TITLE', '2FA Backup Codes');
define('TEXT_BX_2FA_PDF_META_SUBJECT', 'Two-Factor Authentication Backup Codes');

// PDF TOTP Secret
define('TEXT_BX_2FA_PDF_TOTP_META_TITLE', 'TOTP Secret Code');
define('TEXT_BX_2FA_PDF_TOTP_META_SUBJECT', 'Two-Factor Authentication TOTP Secret');
define('TEXT_BX_2FA_PDF_TOTP_TITLE', 'TOTP Secret Code');
define('TEXT_BX_2FA_PDF_TOTP_SUBTITLE', 'Two-Factor Authentication Setup');
define('TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TITLE', 'IMPORTANT - Please keep secure!');
define('TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TEXT', 'This secret code is required to set up your authenticator app. Keep this code safe!');
define('TEXT_BX_2FA_PDF_TOTP_SECRET_LABEL', 'Your TOTP Secret Code:');
define('TEXT_BX_2FA_PDF_TOTP_INSTRUCTIONS', 'Enter this code manually in your authenticator app if you cannot scan the QR code.');
define('TEXT_BX_2FA_PDF_TOTP_FOOTER_TEXT', 'Created on %s for your security.');
define('TEXT_BX_2FA_PDF_TOTP_FILENAME', '2FA_TOTP_Secret_%s.pdf'); // %s = date('Y-m-d')
