# BX TOTP Authenticator

## 🔐 Beschreibung

Der **BX TOTP Authenticator** ist ein umfassendes Zwei-Faktor-Authentifizierungs-Modul (2FA) für das modified eCommerce System. Es bietet Kunden eine sichere Möglichkeit, ihre Konten mit zeitgesteuerten Einmalpasswörtern (TOTP) oder Email-basierter Authentifizierung zu schützen.

Das Modul wurde mit KI-Unterstützung (CADDY - Computer-Aided Development & Deployment Yield) entwickelt.

## ✨ Features

### 🔑 Authentifizierungsmethoden

- **TOTP (Time-based One-Time Password)**
  - Kompatibel mit gängigen Authenticator-Apps (Google Authenticator, Authy, Microsoft Authenticator, etc.)
  - QR-Code-basierte Einrichtung für einfache Konfiguration
  - Zeitbasierte 6-stellige Codes (30-Sekunden-Fenster)
  - **Empfohlene Methode** für höchste Sicherheit

- **Email-basierte 2FA**
  - Sichere Einmalcodes per E-Mail
  - Alternative für Benutzer ohne Authenticator-App
  - Benutzerfreundliche Fallback-Option

### 📊 Admin-Dashboard

Das Admin-Panel (`admin/bx_totp_authenticator.php`) bietet:

- **Aktivierungsstatistiken**
  - Prozentuale Aktivierungsrate der 2FA
  - Absolute Zahlen aktivierter Kunden
  - Verteilung nach Authentifizierungsmethode

- **Systemstatus-Überwachung**
  - Verfügbarkeit der Dependency Resolver Klasse
  - QR-Code-Bibliothek Status
  - TOTP Helper Klasse Status
  - Datenbanktabellen-Überprüfung

- **Kunden-Management**
  - Liste aller registrierten Kunden mit 2FA-Status
  - Filterung nach Authentifizierungsmethode
  - Suchfunktion
  - Notfall-Deaktivierung (Emergency Disable)

- **Support & Dokumentation**
  - Quick-Start Guide
  - Troubleshooting-Hilfen
  - Kundenanleitungen

## 🚀 Installation & Setup

### Systemanforderungen

- modified eCommerce Shop
- PHP 5.6+ (empfohlen: PHP 7.0+)
- MySQL/MariaDB Datenbank
- Abhängigkeitsauflöser (bx_dependency_resolver.php)
- QR-Code-Bibliothek (modified_qrcode)

### Datenbank-Vorbereitung

Das Modul benötigt folgende Tabelle:

```sql
-- Speichert Einmalcodes für Email-basierte 2FA
CREATE TABLE IF NOT EXISTS `two_factor_email_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customers_id` INT NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Erforderliche Kundenfelder

Folgende Felder müssen optional in der `customers` Tabelle vorhanden sein:

- `two_factor_enabled` (TINYINT, Default: 0)
- `two_factor_method` (VARCHAR, z.B. 'totp', 'email')
- `two_factor_totp_secret` (VARCHAR, für TOTP-Secrets)
- `two_factor_backup_codes` (TEXT, für Backup-Codes)
- `two_factor_secret_created` (DATETIME, Erstellungsdatum)

## 📁 Dateistruktur

```
admin/bx_totp_authenticator.php          # Admin-Interface
admin/includes/classes/bx_totp_helper.php # TOTP-Logik & Hilfsfunktionen
catalog/includes/classes/bx_totp_helper.php # Frontend-TOTP-Klasse (Optional)
```

## 🔧 Verwendung

### Für Administratoren

1. **Zugriff auf Admin-Panel:**
   - Navigieren Sie zu: `admin/bx_totp_authenticator.php`
   - Das Modul wird angezeigt im Admin-Menü unter "Konfiguration" oder "Sicherheit"

2. **Dashboard ansehen:**
   - Tab "📊 Dashboard" zeigt Aktivierungsstatistiken
   - Überwachen Sie die 2FA-Adoption Ihrer Kunden

3. **Kunden verwalten:**
   - Tab "🧑‍💼 Kunden" für vollständige Liste der Kunden mit 2FA
   - Filtern nach Authentifizierungsmethode (TOTP/Email/Alle)
   - Kundennamen oder -Email durchsuchen

4. **Notfall-Support:**
   - Tab "🛠️ Support" für Notfall-Deaktivierung
   - Hilfreiche Dokumentation und Troubleshooting
   - Kunden-Onboarding Tipps

### Für Kunden

Das Frontend-Modul ermöglicht Kunden:

1. **TOTP aktivieren:**
   - QR-Code mit Authenticator-App scannen
   - Oder Secret manuell eingeben
   - Verify-Code zur Bestätigung eingeben

2. **Backup-Codes generieren:**
   - Notfalls ohne Authenticator einloggen

3. **2FA Management:**
   - Seite "Mein Konto" → "Sicherheit" oder ähnlich
   - Aktivieren/Deaktivieren nach Bedarf
   - Authentifizierungsmethode wechseln

## 🔑 Kernklassen & Funktionen

### bx_totp_helper.php

Dies ist die Hauptklasse für TOTP-Verwaltung:

```php
// Beispiel-Methoden (Pseudocode)
bx_totp_helper::generateSecret()        // Neues TOTP-Secret generieren
bx_totp_helper::verifyCode($secret, $code)  // Code-Validierung
bx_totp_helper::generateQRCode($secret, $email) // QR-Code erstellen
bx_totp_helper::generateBackupCodes()   // Backup-Codes generieren
```

## 📊 POST-Handler

### Emergency Disable 2FA

Ermöglicht Administratoren, 2FA für Kunden zu deaktivieren:

```php
POST /admin/bx_totp_authenticator.php
Daten:
  - action: 'emergency_disable_2fa'
  - customer_id: <Kundennummer>
```

**Effekt:**
- Deaktiviert 2FA für den Kunden
- Löscht TOTP-Secret
- Löscht Backup-Codes
- Löscht Email-Codes
- Sendet Bestätigung an Kundenmail

## 🛡️ Sicherheitsfeatures

- ✅ TOTP RFC 6238 Standards-konform
- ✅ AES-256 Verschlüsselung (falls konfiguriert)
- ✅ Zeitfenster-basierte Code-Validierung
- ✅ Backup-Codes für Notfallzugriff
- ✅ Sitzungs-basierte Code-Verifikation
- ✅ Logging of all 2FA events
- ✅ Schutz vor Brute-Force-Angriffen

## ⚙️ Konfiguration

### Sprachdatei & Konstanten

Alle Texte sind über Sprachdefinitionen verfügbar:

```php
MODULE_BX_TOTP_TITLE              // Modul-Titel
MODULE_BX_TOTP_VERSION            // Versionsnummer
MODULE_BX_TOTP_DESC               // Modul-Beschreibung

TEXT_BX_TOTP_TAB_DASHBOARD        // Tab-Namen
TEXT_BX_TOTP_TAB_CUSTOMERS
TEXT_BX_TOTP_TAB_SUPPORT

TEXT_BX_TOTP_ACTIVATION_RATE      // Status-Texte
TEXT_BX_TOTP_CUSTOMERS_USE_2FA
```

## 🐛 Troubleshooting

### Häufige Probleme

**Problem: "QR-Code Library not available"**
- **Lösung:** Installieren Sie das QR-Code-Paket über Composer oder Manual Upload
- Überprüfen Sie bx_dependency_resolver.php Verfügbarkeit

**Problem: "Dependency Resolver nicht verfügbar"**
- **Lösung:** Prüfen Sie `includes/classes/bx_dependency_resolver.php` Existenz
- Aktualisieren Sie das modified Framework

**Problem: Authentifizierung schlägt fehl**
- Überprüfen Sie Systemzeit auf Server & Client
- Verifizieren Sie Zeitzonen-Konfiguration
- Prüfen Sie Datenbanktabellen auf Korrektheit

**Problem: "two_factor_email_codes Tabelle nicht gefunden"**
- Führen Sie das Installations-SQL aus
- Prüfen Sie Datenbankberechtigungen

## 👨‍💻 Entwicklung & Erweiterung

### Hook-Punkte (sofern implementiert)

- `pre_2fa_verification` - Vor Code-Validierung
- `post_2fa_verification` - Nach Code-Validierung
- `on_2fa_enabled` - Wenn 2FA aktiviert wird
- `on_2fa_disabled` - Wenn 2FA deaktiviert wird

### Benutzerdefinierte Authentifizierungsmethoden

Entwickler können neue Methoden hinzufügen, indem sie:

1. Die `bx_totp_helper` Klasse erweitern
2. Neue Verifizierungs-Methoden implementieren
3. Dashboard-Statistiken aktualisieren

### Datenbank-Queries

```php
// Alle aktivierten Kunden
$query = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE two_factor_enabled = 1";

// Nach Methode filtern
$query .= " AND two_factor_method = 'totp'";

// Nach Email
$query .= " WHERE customers_email_address LIKE '%@example.com'";
```

## 📜 Lizenz

GNU General Public License (GPL)

Basierend auf:
- osCommerce (2000-2003)
- nextcommerce
- XT-Commerce
- modified eCommerce

## 🤝 Beiträge & Support

**Entwickelt mit:** CADDY - Computer-Aided Development & Deployment Yield (KI-Unterstützung)

**Ursprung:** modified-shop.org Community

**Support-Kontakt:**
- Offizielle Website: http://www.modified-shop.org
- Community-Forum

## 📝 Changelog

### Version 1.0.0 (2026-01-19)
- Initiale Release
- TOTP & Email 2FA Methoden
- Admin-Dashboard mit Statistiken
- Notfall-Deaktivierung für Support
- QR-Code Integration
- Kunden-Verwaltungs-Interface

## ⚡ Performance-Hinweise

- QR-Code-Generierung wird **gecachet**
- Datenbank-Indexes auf `customers_id` empfohlen
- Bei >10.000 Kunden: Pagination implementieren
- RegelmäßigeMail-Code Cleanup mittels Cron-Job

## 🔒 Sicherheits-Best-Practices

1. **Immer HTTPS verwenden** - Besonders bei 2FA-Setup
2. **Regelmäßige Backups** - Kundeneinstellungen sind wichtig
3. **Alte Codes entfernen** - Implementieren Sie automatisches Cleanup
4. **Logging aktivieren** - Für Audit-Trail
5. **Rate Limiting** - Verhindern Sie Code-Brute-Force
6. **Sichere Secrets** - Speichern Sie verschlüsselt in der DB
7. **HTTPS-only Cookies** - Für Session-Security

---

**Hinweis:** Dieses Modul erfordert korrekte Installation und Konfiguration für optimale Sicherheit. Für eine produktive Umgebung stellen Sie sicher, dass alle Systemanforderungen erfüllt sind.
