<?php
/* -----------------------------------------------------------------------------------------
   $Id: /lang/german/extra/bx_two_factor_account.php 1000 2026-01-22 12:00:00Z benax $
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org
   
   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Breadcrumb
define('TEXT_BX_2FA_BREADCRUMB_ACCOUNT', 'Mein Konto');
define('TEXT_BX_2FA_BREADCRUMB_2FA', 'Zwei-Faktor-Authentifizierung');

// Fehlermeldungen - TOTP Setup
define('TEXT_BX_2FA_ERROR_ENTER_CODE', 'Bitte geben Sie den Code aus Ihrer Authenticator-App ein.');
define('TEXT_BX_2FA_ERROR_INVALID_CODE', 'Ungültiger Code. Bitte versuchen Sie es erneut.');
define('TEXT_BX_2FA_ERROR_NO_BACKUP_CODES', 'Keine Backup-Codes verfügbar.');
define('TEXT_BX_2FA_ERROR_NO_TOTP_SECRET', 'Kein TOTP Secret vorhanden.');

// Warnungen und Infos
define('TEXT_BX_2FA_WARNING_QR_CODE_FAILED', 'QR-Code konnte nicht generiert werden. Bitte nutzen Sie die manuelle Eingabe.');
define('TEXT_BX_2FA_WARNING_QR_CODE_ERROR', 'QR-Code Fehler: %s - Bitte nutzen Sie die manuelle Eingabe.');
define('TEXT_BX_2FA_INFO_QR_CODE_UNAVAILABLE', 'QR-Code-Bibliothek nicht verfügbar. Bitte geben Sie den Secret-Code manuell ein.');
define('TEXT_BX_2FA_INFO_DEPENDENCY_RESOLVER_UNAVAILABLE', 'BX Dependency Resolver nicht verfügbar. Einige Funktionen sind möglicherweise eingeschränkt oder nicht verfügbar.');

// PDF Backup-Codes
define('TEXT_BX_2FA_PDF_TITLE', 'Backup-Codes');
define('TEXT_BX_2FA_PDF_SUBTITLE', 'Zwei-Faktor-Authentifizierung');
define('TEXT_BX_2FA_PDF_CUSTOMER', 'Kunde:');
define('TEXT_BX_2FA_PDF_EMAIL', 'E-Mail:');
define('TEXT_BX_2FA_PDF_DATE', 'Datum:');

// PDF Warnbox
define('TEXT_BX_2FA_PDF_IMPORTANT_TITLE', 'WICHTIG: Sicher aufbewahren!');
define('TEXT_BX_2FA_PDF_IMPORTANT_TEXT', "Diese Codes ermöglichen den Zugang zu Ihrem Konto, falls Sie keinen Zugriff auf Ihre Authenticator-App oder E-Mails haben.\n\n" .
    "• Jeder Code kann nur EINMAL verwendet werden\n" .
    "• Bewahren Sie diese Codes an einem sicheren Ort auf\n" .
    "• Geben Sie diese Codes niemals an Dritte weiter");

// PDF Codes Sektion
define('TEXT_BX_2FA_PDF_YOUR_CODES', 'Ihre Backup-Codes:');
define('TEXT_BX_2FA_PDF_FOOTER_TEXT', "Diese Codes wurden am %s generiert.\nSie können jederzeit neue Codes in Ihrem Konto generieren.");

// PDF Dateiname Pattern
define('TEXT_BX_2FA_PDF_FILENAME', '2FA_Backup_Codes_%s.pdf'); // %s = date('Y-m-d')

// Meta Daten für PDF
define('TEXT_BX_2FA_PDF_META_TITLE', '2FA Backup-Codes');
define('TEXT_BX_2FA_PDF_META_SUBJECT', 'Zwei-Faktor-Authentifizierung Backup-Codes');

// PDF TOTP Secret
define('TEXT_BX_2FA_PDF_TOTP_META_TITLE', 'TOTP Secret Code');
define('TEXT_BX_2FA_PDF_TOTP_META_SUBJECT', 'Zwei-Faktor-Authentifizierung TOTP Secret');
define('TEXT_BX_2FA_PDF_TOTP_TITLE', 'TOTP Secret Code');
define('TEXT_BX_2FA_PDF_TOTP_SUBTITLE', 'Zwei-Faktor-Authentifizierung Setup');
define('TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TITLE', 'WICHTIG - Bitte sicher aufbewahren!');
define('TEXT_BX_2FA_PDF_TOTP_IMPORTANT_TEXT', 'Dieser Secret Code wird benötigt, um Ihre Authenticator-App einzurichten. Bewahren Sie diesen Code sicher auf!');
define('TEXT_BX_2FA_PDF_TOTP_SECRET_LABEL', 'Ihr TOTP Secret Code:');
define('TEXT_BX_2FA_PDF_TOTP_INSTRUCTIONS', 'Geben Sie diesen Code manuell in Ihre Authenticator-App ein, wenn Sie den QR-Code nicht scannen können.');
define('TEXT_BX_2FA_PDF_TOTP_FOOTER_TEXT', 'Erstellt am %s für Ihre Sicherheit.');
define('TEXT_BX_2FA_PDF_TOTP_FILENAME', '2FA_TOTP_Secret_%s.pdf'); // %s = date('Y-m-d')
