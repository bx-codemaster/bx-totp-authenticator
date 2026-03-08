<?php
/* -----------------------------------------------------------------------------------------
	$Id: /lang/german/extra/admin/bx_totp_authenticator.php 1000 2026-01-22 12:00:00Z benax $
	
	modified eCommerce Shopsoftware
	http://www.modified-shop.org
	
	Copyright (c) 2009 - 2013 [www.modified-shop.org]
	-----------------------------------------------------------------------------------------
	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/
	
// Globale Titel & Beschreibungen
define('MODULE_BX_TOTP_TITLE', 'BX TOTP Authenticator');
define('MODULE_BX_TOTP_DESC', '2-Faktor-Authentifizierung für Ihren Shop');
define('MODULE_BX_TOTP_VERSION', '1.5.0');

// Nachrichten
define('TEXT_BX_TOTP_SUCCESS_DISABLED', '2FA erfolgreich deaktiviert für: %s (%s)');
define('TEXT_BX_TOTP_NOT_ACTIVATED', 'Kunde hat 2FA nicht aktiviert.');
define('TEXT_BX_TOTP_CUSTOMER_NOT_FOUND', 'Kunde nicht gefunden.');
define('TEXT_BX_TOTP_INVALID_CUSTOMER_ID', 'Ungültige Kunden-ID.');

// Tab-Bezeichnungen
define('TEXT_BX_TOTP_TAB_DASHBOARD', 'Dashboard');
define('TEXT_BX_TOTP_TAB_CUSTOMERS', 'Kunden-Liste');
define('TEXT_BX_TOTP_TAB_SUPPORT', 'Support-Aktionen');

// Dashboard - Übersicht
define('TEXT_BX_TOTP_OVERVIEW_TITLE', '2FA-Übersicht');
define('TEXT_BX_TOTP_ACTIVATION_RATE', 'Aktivierungsrate');
define('TEXT_BX_TOTP_CUSTOMERS_USE_2FA', 'Kunden nutzen 2FA');
define('TEXT_BX_TOTP_OF', 'von');
define('TEXT_BX_TOTP_METHOD_DISTRIBUTION', 'Methoden-Verteilung');
define('TEXT_BX_TOTP_METHOD_TOTP', 'TOTP (Authenticator)');
define('TEXT_BX_TOTP_METHOD_EMAIL', 'E-Mail-Codes');
define('TEXT_BX_TOTP_RECOMMENDED_METHOD', 'Empfohlene Methode');
define('TEXT_BX_TOTP_ALTERNATIVE_METHOD', 'Alternative Methode');

// Dashboard - Systemstatus
define('TEXT_BX_TOTP_SYSTEM_STATUS', 'Systemstatus');
define('TEXT_BX_TOTP_QRCODE_LIBRARY', 'QR-Code Library');
define('TEXT_BX_TOTP_QRCODE_FOR_SETUP', 'endroid/qr-code für TOTP-Setup (benötigt BX Dependency Resolver)');
define('TEXT_BX_TOTP_AVAILABLE', 'Verfügbar');
define('TEXT_BX_TOTP_NOT_INSTALLED', 'Nicht installiert');
define('TEXT_BX_TOTP_TOTP_CLASS', 'TOTP-Klasse');
define('TEXT_BX_TOTP_CLASS_FILE', 'bx_totp_helper.php');
define('TEXT_BX_TOTP_LOADED', 'Geladen');
define('TEXT_BX_TOTP_MISSING', 'Fehlt');
define('TEXT_BX_TOTP_DATABASE_TABLES', 'Datenbank-Tabellen');
define('TEXT_BX_TOTP_TABLES_DESC', 'two_factor_email_codes & customers');
define('TEXT_BX_TOTP_TABLES_OK', 'Tabellen OK');
define('TEXT_BX_TOTP_TABLES_MISSING', 'Tabellen fehlen');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_CLASS', 'BX Dependency Resolver');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_FOR_SETUP', 'Für automatische Installation der QR-Code Library (empfohlen)');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_AVAILABLE', 'Verfügbar');
define('TEXT_BX_TOTP_DEPENDENCY_RESOLVER_NOT_AVAILABLE', 'Nicht verfügbar');

// Dashboard - Schnellstart
define('TEXT_BX_TOTP_QUICKSTART', 'Schnellstart');
define('TEXT_BX_TOTP_QUICKSTART_CUSTOMERS', '<strong>Kunden:</strong> Aktivieren 2FA selbstständig unter "Mein Konto → Zwei-Faktor-Authentifizierung"');
define('TEXT_BX_TOTP_QUICKSTART_SUPPORT', '<strong>Admin-Support:</strong> Tab "Support-Aktionen" für Hilfe bei Problemen');
define('TEXT_BX_TOTP_QUICKSTART_LIST', '<strong>Kundenliste:</strong> Alle 2FA-Nutzer im Tab "Kunden-Liste" verwalten');

// Kundenliste
define('TEXT_BX_TOTP_CUSTOMERS_WITH_2FA', 'Kunden mit aktivierter 2FA');
define('TEXT_BX_TOTP_CUSTOMERS_FOUND', 'Kunden gefunden');
define('TEXT_BX_TOTP_FILTER', 'Filter:');
define('TEXT_BX_TOTP_ALL_METHODS', 'Alle Methoden');
define('TEXT_BX_TOTP_ONLY_TOTP', 'Nur TOTP');
define('TEXT_BX_TOTP_ONLY_EMAIL', 'Nur E-Mail');
define('TEXT_BX_TOTP_SEARCH_PLACEHOLDER', 'Suche nach Name oder E-Mail...');
define('TEXT_BX_TOTP_SEARCH_BUTTON', 'Suchen');
define('TEXT_BX_TOTP_RESET_FILTER', 'Filter zurücksetzen');

// Kundenliste - Tabellenspalten
define('TEXT_BX_TOTP_TABLE_ID', 'ID');
define('TEXT_BX_TOTP_TABLE_CUSTOMER', 'Kunde');
define('TEXT_BX_TOTP_TABLE_EMAIL', 'E-Mail');
define('TEXT_BX_TOTP_TABLE_METHOD', 'Methode');
define('TEXT_BX_TOTP_TABLE_ACTIVATED', 'Aktiviert am');
define('TEXT_BX_TOTP_TABLE_ACTIONS', 'Aktionen');
define('TEXT_BX_TOTP_EDIT_CUSTOMER', 'Kunde bearbeiten');
define('TEXT_BX_TOTP_DISABLE_2FA', '2FA deaktivieren');
define('TEXT_BX_TOTP_DISABLE_BUTTON', '✗ 2FA AUS');

// Kundenliste - Meldungen
define('TEXT_BX_TOTP_NO_CUSTOMERS_CRITERIA', 'Keine Kunden gefunden, die den Suchkriterien entsprechen.');
define('TEXT_BX_TOTP_NO_CUSTOMERS_YET', 'Noch keine Kunden haben 2FA aktiviert.');
define('TEXT_BX_TOTP_CUSTOMERS_HINT', '<strong>💡 Hinweis:</strong> Nutzen Sie den <strong>roten Button "✗ 2FA AUS"</strong> in der Tabelle, um 2FA für einen Kunden im Notfall zu deaktivieren.');

// Bestätigungs-Dialog
define('TEXT_BX_TOTP_CONFIRM_DISABLE', '⚠️ 2FA deaktivieren für:\n\n%s\n%s\n\nSind Sie sicher?');

// Support - Häufige Fälle Titel
define('TEXT_BX_TOTP_SUPPORT_CASES', 'Häufige Support-Fälle');

// Support - Fall 1: Neues Handy
define('TEXT_BX_TOTP_CASE1_TITLE', '🔄 "Ich habe ein neues Handy"');
define('TEXT_BX_TOTP_CASE1_SOLUTION', 'Lösung:');
define('TEXT_BX_TOTP_CASE1_POINT1', 'Wenn der Kunde noch Zugriff auf altes Gerät hat: TOTP-Secret in neue App übertragen');
define('TEXT_BX_TOTP_CASE1_POINT2', 'Wenn kein Zugriff mehr: Backup-Codes nutzen oder Notfall-Deaktivierung');
define('TEXT_BX_TOTP_CASE1_POINT3', 'Nach Deaktivierung kann Kunde 2FA neu einrichten');

// Support - Fall 2: Code nicht akzeptiert
define('TEXT_BX_TOTP_CASE2_TITLE', '⏰ "Der Code wird nicht akzeptiert"');
define('TEXT_BX_TOTP_CASE2_CAUSES', 'Mögliche Ursachen:');
define('TEXT_BX_TOTP_CASE2_POINT1', '<strong>Zeitabweichung:</strong> Kunde soll Uhrzeit auf Gerät prüfen (automatische Zeit aktivieren)');
define('TEXT_BX_TOTP_CASE2_POINT2', '<strong>Falscher Code:</strong> Neuen Code generieren lassen (alle 30 Sekunden)');
define('TEXT_BX_TOTP_CASE2_POINT3', '<strong>Falsche App:</strong> Sicherstellen, dass der richtige Account ausgewählt ist');

// Support - Fall 3: Keine E-Mail-Codes
define('TEXT_BX_TOTP_CASE3_TITLE', '📧 "Ich erhalte keine E-Mail-Codes"');
define('TEXT_BX_TOTP_CASE3_CHECKS', 'Prüfpunkte:');
define('TEXT_BX_TOTP_CASE3_POINT1', 'Spam-Ordner überprüfen lassen');
define('TEXT_BX_TOTP_CASE3_POINT2', 'E-Mail-Adresse im Account korrekt?');
define('TEXT_BX_TOTP_CASE3_POINT3', 'Mail-Server-Log prüfen');
define('TEXT_BX_TOTP_CASE3_POINT4', 'Ggf. auf TOTP-Methode umstellen (sicherer)');

// Support - Fall 4: Backup-Codes verloren
define('TEXT_BX_TOTP_CASE4_TITLE', '🔐 "Ich habe meine Backup-Codes verloren"');
define('TEXT_BX_TOTP_CASE4_SOLUTION', 'Lösung:');
define('TEXT_BX_TOTP_CASE4_POINT1', 'Kunde kann sich mit TOTP-App oder E-Mail-Code anmelden');
define('TEXT_BX_TOTP_CASE4_POINT2', 'Nach Anmeldung neue Backup-Codes generieren lassen (unter "Mein Konto → Sicherheit")');
define('TEXT_BX_TOTP_CASE4_POINT3', 'Wenn keine Anmeldung mehr möglich: Notfall-Deaktivierung durch Admin erforderlich');
define('TEXT_BX_TOTP_CASE4_POINT4', 'Wichtig: Kunde sollte neue Codes sicher aufbewahren (ausdrucken oder in Passwort-Manager)');

// Support - Fall 5: QR-Code kann nicht gescannt werden
define('TEXT_BX_TOTP_CASE5_TITLE', '📱 "QR-Code kann nicht gescannt werden"');
define('TEXT_BX_TOTP_CASE5_SOLUTIONS', 'Lösungsansätze:');
define('TEXT_BX_TOTP_CASE5_POINT1', '<strong>Bildschirmhelligkeit erhöhen:</strong> QR-Code muss gut lesbar sein');
define('TEXT_BX_TOTP_CASE5_POINT2', '<strong>Manueller Eintrag:</strong> Secret-Code unter dem QR-Code anzeigen und händisch in App eingeben');
define('TEXT_BX_TOTP_CASE5_POINT3', '<strong>Andere Authenticator-App:</strong> Google Authenticator, Microsoft Authenticator, Authy testen');
define('TEXT_BX_TOTP_CASE5_POINT4', '<strong>Kamera-Berechtigungen:</strong> App muss auf Kamera zugreifen dürfen');
define('TEXT_BX_TOTP_CASE5_POINT5', '<strong>Screenshot vermeiden:</strong> QR-Code direkt vom Bildschirm scannen');

// Sidebar - Quick Actions
define('TEXT_BX_TOTP_QUICK_ACTIONS', 'Quick Actions');
define('TEXT_BX_TOTP_MODULE_SETTINGS', 'Modul-Einstellungen');
define('TEXT_BX_TOTP_CONFIGURATION', 'Konfiguration');
define('TEXT_BX_TOTP_CUSTOMER_MANAGEMENT', 'Kundenverwaltung');
define('TEXT_BX_TOTP_ALL_CUSTOMERS', 'Alle Kunden');

// Sidebar - Schnell-Übersicht
define('TEXT_BX_TOTP_QUICK_OVERVIEW', 'Schnell-Übersicht');
define('TEXT_BX_TOTP_ACTIVE_USERS', 'Aktive Nutzer');
define('TEXT_BX_TOTP_QRCODE_LIBRARY_MISSING', 'QR-Code-Library fehlt');

// Sidebar - Hinweise
define('TEXT_BX_TOTP_HINTS', 'Hinweise');
define('TEXT_BX_TOTP_HINT1', 'Kunden aktivieren 2FA selbst in ihrem Account-Bereich.');
define('TEXT_BX_TOTP_HINT2', 'Bei Problemen nutzen Sie den Tab <strong>"Support-Aktionen"</strong>.');
define('TEXT_BX_TOTP_HINT3', 'TOTP ist sicherer als E-Mail-Codes.');

