<?php
/* -----------------------------------------------------------------------------------------
	$Id: /lang/english/extra/admin/bx_totp_authenticator.php 1000 2026-01-22 12:00:00Z benax $
	
	modified eCommerce Shopsoftware
	http://www.modified-shop.org
	
	Copyright (c) 2009 - 2013 [www.modified-shop.org]
	-----------------------------------------------------------------------------------------
	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/
	
// Global Titles & Descriptions
define('MODULE_BX_TOTP_TITLE', 'BX TOTP Authenticator');
define('MODULE_BX_TOTP_DESC', '2-Factor Authentication for Your Shop');
define('MODULE_BX_TOTP_VERSION', '1.5.0');

// Messages
define('TEXT_BX_TOTP_SUCCESS_DISABLED', '2FA successfully disabled for: %s (%s)');
define('TEXT_BX_TOTP_NOT_ACTIVATED', 'Customer has not activated 2FA.');
define('TEXT_BX_TOTP_CUSTOMER_NOT_FOUND', 'Customer not found.');
define('TEXT_BX_TOTP_INVALID_CUSTOMER_ID', 'Invalid customer ID.');

// Tab Labels
define('TEXT_BX_TOTP_TAB_DASHBOARD', 'Dashboard');
define('TEXT_BX_TOTP_TAB_CUSTOMERS', 'Customer List');
define('TEXT_BX_TOTP_TAB_SUPPORT', 'Support Actions');

// Dashboard - Overview
define('TEXT_BX_TOTP_OVERVIEW_TITLE', '2FA Overview');
define('TEXT_BX_TOTP_ACTIVATION_RATE', 'Activation Rate');
define('TEXT_BX_TOTP_CUSTOMERS_USE_2FA', 'Customers use 2FA');
define('TEXT_BX_TOTP_OF', 'of');
define('TEXT_BX_TOTP_METHOD_DISTRIBUTION', 'Method Distribution');
define('TEXT_BX_TOTP_METHOD_TOTP', 'TOTP (Authenticator)');
define('TEXT_BX_TOTP_METHOD_EMAIL', 'Email Codes');
define('TEXT_BX_TOTP_RECOMMENDED_METHOD', 'Recommended Method');
define('TEXT_BX_TOTP_ALTERNATIVE_METHOD', 'Alternative Method'); 

// Dashboard - System Status
define('TEXT_BX_TOTP_SYSTEM_STATUS', 'System Status');
define('TEXT_BX_TOTP_QRCODE_LIBRARY', 'QR Code Library');
define('TEXT_BX_TOTP_QRCODE_FOR_SETUP', 'endroid/qr-code for TOTP Setup (requires BX Dependency Resolver)');
define('TEXT_BX_TOTP_AVAILABLE', 'Available');
define('TEXT_BX_TOTP_NOT_INSTALLED', 'Not Installed');
define('TEXT_BX_TOTP_TOTP_CLASS', 'TOTP Class');
define('TEXT_BX_TOTP_CLASS_FILE', 'bx_totp_helper.php');
define('TEXT_BX_TOTP_LOADED', 'Loaded');
define('TEXT_BX_TOTP_MISSING', 'Missing');
define('TEXT_BX_TOTP_DATABASE_TABLES', 'Database Tables');
define('TEXT_BX_TOTP_TABLES_DESC', 'two_factor_email_codes & customers');
define('TEXT_BX_TOTP_TABLES_OK', 'Tables OK');
define('TEXT_BX_TOTP_TABLES_MISSING', 'Tables Missing');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_CLASS', 'BX Dependency Resolver');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_FOR_SETUP', 'For automatic installation of dependencies (e.g., QR Code Library)');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_AVAILABLE', 'Available');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_NOT_AVAILABLE', 'Not Available');

// Dashboard - Quick Start
define('TEXT_BX_TOTP_QUICKSTART', 'Quick Start');
define('TEXT_BX_TOTP_QUICKSTART_CUSTOMERS', '<strong>Customers:</strong> Activate 2FA independently under "My Account → Two-Factor Authentication"');
define('TEXT_BX_TOTP_QUICKSTART_SUPPORT', '<strong>Admin Support:</strong> Use "Support Actions" tab for help with issues');
define('TEXT_BX_TOTP_QUICKSTART_LIST', '<strong>Customer List:</strong> Manage all 2FA users in "Customer List" tab');

// Customer List
define('TEXT_BX_TOTP_CUSTOMERS_WITH_2FA', 'Customers with activated 2FA');
define('TEXT_BX_TOTP_CUSTOMERS_FOUND', 'Customers found');
define('TEXT_BX_TOTP_FILTER', 'Filter:');
define('TEXT_BX_TOTP_ALL_METHODS', 'All Methods');
define('TEXT_BX_TOTP_ONLY_TOTP', 'TOTP Only');
define('TEXT_BX_TOTP_ONLY_EMAIL', 'Email Only');
define('TEXT_BX_TOTP_SEARCH_PLACEHOLDER', 'Search by name or email...');
define('TEXT_BX_TOTP_SEARCH_BUTTON', 'Search');
define('TEXT_BX_TOTP_RESET_FILTER', 'Reset Filter');

// Customer List - Table Columns
define('TEXT_BX_TOTP_TABLE_ID', 'ID');
define('TEXT_BX_TOTP_TABLE_CUSTOMER', 'Customer');
define('TEXT_BX_TOTP_TABLE_EMAIL', 'Email');
define('TEXT_BX_TOTP_TABLE_METHOD', 'Method');
define('TEXT_BX_TOTP_TABLE_ACTIVATED', 'Activated on');
define('TEXT_BX_TOTP_TABLE_ACTIONS', 'Actions');
define('TEXT_BX_TOTP_EDIT_CUSTOMER', 'Edit Customer');
define('TEXT_BX_TOTP_DISABLE_2FA', 'Disable 2FA');
define('TEXT_BX_TOTP_DISABLE_BUTTON', '✗ 2FA OFF');

// Customer List - Messages
define('TEXT_BX_TOTP_NO_CUSTOMERS_CRITERIA', 'No customers found matching the search criteria.');
define('TEXT_BX_TOTP_NO_CUSTOMERS_YET', 'No customers have activated 2FA yet.');
define('TEXT_BX_TOTP_CUSTOMERS_HINT', '<strong>💡 Note:</strong> Use the <strong>red button "✗ 2FA OFF"</strong> in the table to disable 2FA for a customer in emergency situations.');

// Confirmation Dialog
define('TEXT_BX_TOTP_CONFIRM_DISABLE', '⚠️ Disable 2FA for:\n\n%s\n%s\n\nAre you sure?');

// Support - Common Cases Title
define('TEXT_BX_TOTP_SUPPORT_CASES', 'Common Support Cases');

// Support - Case 1: New Phone
define('TEXT_BX_TOTP_CASE1_TITLE', '🔄 "I have a new phone"');
define('TEXT_BX_TOTP_CASE1_SOLUTION', 'Solution:');
define('TEXT_BX_TOTP_CASE1_POINT1', 'If customer still has access to old device: Transfer TOTP secret to new app');
define('TEXT_BX_TOTP_CASE1_POINT2', 'If no access anymore: Use backup codes or emergency disable');
define('TEXT_BX_TOTP_CASE1_POINT3', 'After disabling, customer can set up 2FA again');

// Support - Case 2: Code Not Accepted
define('TEXT_BX_TOTP_CASE2_TITLE', '⏰ "The code is not accepted"');
define('TEXT_BX_TOTP_CASE2_CAUSES', 'Possible Causes:');
define('TEXT_BX_TOTP_CASE2_POINT1', '<strong>Time Drift:</strong> Customer should check device time (enable automatic time)');
define('TEXT_BX_TOTP_CASE2_POINT2', '<strong>Wrong Code:</strong> Generate new code (every 30 seconds)');
define('TEXT_BX_TOTP_CASE2_POINT3', '<strong>Wrong App:</strong> Ensure the correct account is selected');

// Support - Case 3: No Email Codes
define('TEXT_BX_TOTP_CASE3_TITLE', '📧 "I don\'t receive email codes"');
define('TEXT_BX_TOTP_CASE3_CHECKS', 'Checkpoints:');
define('TEXT_BX_TOTP_CASE3_POINT1', 'Check spam folder');
define('TEXT_BX_TOTP_CASE3_POINT2', 'Is the email address in account correct?');
define('TEXT_BX_TOTP_CASE3_POINT3', 'Check mail server log');
define('TEXT_BX_TOTP_CASE3_POINT4', 'If necessary, switch to TOTP method (more secure)');

// Support - Case 4: Lost Backup Codes
define('TEXT_BX_TOTP_CASE4_TITLE', '🔐 "I lost my backup codes"');
define('TEXT_BX_TOTP_CASE4_SOLUTION', 'Solution:');
define('TEXT_BX_TOTP_CASE4_POINT1', 'Customer can log in with TOTP app or email code');
define('TEXT_BX_TOTP_CASE4_POINT2', 'After login, generate new backup codes (under "My Account → Security")');
define('TEXT_BX_TOTP_CASE4_POINT3', 'If login no longer possible: Emergency disable by admin required');
define('TEXT_BX_TOTP_CASE4_POINT4', 'Important: Customer should store new codes safely (print or password manager)');

// Support - Case 5: Cannot Scan QR Code
define('TEXT_BX_TOTP_CASE5_TITLE', '📱 "QR code cannot be scanned"');
define('TEXT_BX_TOTP_CASE5_SOLUTIONS', 'Solutions:');
define('TEXT_BX_TOTP_CASE5_POINT1', '<strong>Increase Screen Brightness:</strong> QR code must be clearly readable');
define('TEXT_BX_TOTP_CASE5_POINT2', '<strong>Manual Entry:</strong> Display secret code below QR code and enter manually in app');
define('TEXT_BX_TOTP_CASE5_POINT3', '<strong>Try Another Authenticator App:</strong> Google Authenticator, Microsoft Authenticator, Authy');
define('TEXT_BX_TOTP_CASE5_POINT4', '<strong>Camera Permissions:</strong> App must have camera access');
define('TEXT_BX_TOTP_CASE5_POINT5', '<strong>Avoid Screenshots:</strong> Scan QR code directly from screen');

// Sidebar - Quick Actions
define('TEXT_BX_TOTP_QUICK_ACTIONS', 'Quick Actions');
define('TEXT_BX_TOTP_MODULE_SETTINGS', 'Module Settings');
define('TEXT_BX_TOTP_CONFIGURATION', 'Configuration');
define('TEXT_BX_TOTP_CUSTOMER_MANAGEMENT', 'Customer Management');
define('TEXT_BX_TOTP_ALL_CUSTOMERS', 'All Customers');

// Sidebar - Quick Overview
define('TEXT_BX_TOTP_QUICK_OVERVIEW', 'Quick Overview');
define('TEXT_BX_TOTP_ACTIVE_USERS', 'Active Users');
define('TEXT_BX_TOTP_QRCODE_LIBRARY_MISSING', 'QR Code Library Missing');

// Sidebar - Notes
define('TEXT_BX_TOTP_HINTS', 'Notes');
define('TEXT_BX_TOTP_HINT1', 'Customers activate 2FA themselves in their account area.');
define('TEXT_BX_TOTP_HINT2', 'For issues, use the <strong>"Support Actions"</strong> tab.');
define('TEXT_BX_TOTP_HINT3', 'TOTP is more secure than email codes.');
