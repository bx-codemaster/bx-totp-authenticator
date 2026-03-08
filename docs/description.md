# BX TOTP Authenticator - Dateistruktur

Übersicht über alle Dateien des BX TOTP Authenticator Moduls für modified eCommerce.

**Erstellt am:** 4. Februar 2026

---

## 📁 Verzeichnisstruktur

### Admin-Bereich

#### Hauptdatei
```
admin/
└── bx_totp_authenticator.php                          # Hauptverwaltung der TOTP-Einstellungen
```

#### Klassen
```
admin/includes/classes/
└── bx_totp_helper.php                                 # TOTP-Helferklasse für Code-Generierung/-Verifizierung
```

#### System-Module
```
admin/includes/modules/system/
└── bx_totp_authenticator.php                          # System-Modul für TOTP Authenticator
```

#### Extra-Dateien

**Menü:**
```
admin/includes/extra/menu/
└── bx_totp_authenticator.php                          # Menüeintrag im Admin
```

**Dateinamen:**
```
admin/includes/extra/filenames/
└── bx_totp_authenticator.php                          # Filename-Konstanten
```

**CSS:**
```
admin/includes/extra/css/
└── bx_totp_authenticator.php                          # CSS-Einbindung für Admin
```

**JavaScript:**
```
admin/includes/extra/javascript/
└── bx_totp_authenticator.php                          # JavaScript-Einbindung für Admin
```

**Icons:**
```
admin/images/icons/heading/
└── bx_2fa.png                                         # Icon für 2FA-Seiten im Admin
```

---

### Sprachdateien

#### Deutsch
```
lang/german/modules/system/
└── bx_totp_authenticator.php                          # Deutsche Systemmodul-Texte

lang/german/extra/admin/
└── bx_totp_authenticator.php                          # Deutsche Admin-Texte
```

#### Englisch
```
lang/english/modules/system/
└── bx_totp_authenticator.php                          # Englische Systemmodul-Texte

lang/english/extra/admin/
└── bx_totp_authenticator.php                          # Englische Admin-Texte
```

---

## 🔗 Verwandte Komponenten

Das BX TOTP Authenticator Modul ist Teil des größeren **BX Two-Factor Authentication** Systems und arbeitet zusammen mit:

### Frontend Two-Factor Authentication
```
includes/classes/
├── bx_two_factor_auth.php                             # Haupt-2FA-Handler
└── bx_two_factor_email_handler.php                    # E-Mail-Code-Handler

includes/extra/filenames/
└── bx_two_factor_filenames.php                        # Filename-Konstanten für Frontend

includes/extra/header/header_head/
└── bx_two_factor_css.php                              # CSS-Einbindung für Frontend

includes/extra/login/
└── bx_two_factor_check.php                            # Login-Hook für 2FA-Prüfung

bx_two_factor_account.php                              # Kunden-2FA-Verwaltung

bx_two_factor_verify.php                               # 2FA-Verifizierung beim Login
```

### Templates (tpl_modified_nova)
```
templates/tpl_modified_nova/
├── module/
│   ├── bx_two_factor_account.html                     # 2FA-Account-Management
│   └── bx_two_factor_verify.html                      # 2FA-Verifizierung
├── lang/
│   ├── lang_bx_two_factor_german.custom               # Deutsche Frontend-Texte
│   └── lang_bx_two_factor_english.custom              # Englische Frontend-Texte
├── css/
│   └── bx_two_factor.css                              # Frontend-Styles
├── javascript/extra/
│   └── bx_two_factor_authenticator.js.php             # Frontend-JavaScript
└── admin/mail/
    ├── german/
    │   ├── bx_two_factor_code_email.html              # E-Mail-Template (DE, HTML)
    │   └── bx_two_factor_code_email.txt               # E-Mail-Template (DE, Text)
    └── english/
        ├── bx_two_factor_code_email.html              # E-Mail-Template (EN, HTML)
        └── bx_two_factor_code_email.txt               # E-Mail-Template (EN, Text)
```

**Hinweise für tpl_modified_nova:**

1. **Login-Box Navigation:** Die Datei `templates/tpl_modified_nova/boxes/box_login.html` wurde mit dem 2FA-Link ergänzt:
   ```html
   {if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
   <!-- Start BX TWO FACTOR AUTH  -->
     <li>
       <a href="{$smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT|xtc_href_link}" title="{#title_nav_two_factor_auth#}">
         <span class="icon"><i class="fa-solid fa-shield-halved"></i></span><span class="title">{#title_nav_two_factor_auth#}</span>
       </a>
     </li>
   <!-- End BX TWO FACTOR AUTH  -->
   {/if}
   ```
   
   Position: Nach dem "Bestellungen"-Link, vor dem "Account löschen"-Link (nur für account_type == 0 sichtbar).

2. **Account-Navigation:** Die Datei `templates/tpl_modified_nova/module/account_navigation.html` wurde mit dem 2FA-Link ergänzt:
   ```html
   {if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
   <!-- Start BX TWO FACTOR AUTH  -->
   <div class="navigation_item{if (strpos($smarty.server.PHP_SELF, $smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT) !== false) || (strpos($smarty.server.PHP_SELF, $smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT) !== false)} selected{/if}">
     <a href="{$smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT|xtc_href_link}" title="{#title_nav_two_factor_auth#}">
       <span class="icon"><i class="fa-solid fa-shield-halved"></i></span><span class="title">{#title_nav_two_factor_auth#}</span>
     </a>
   </div>
   <!-- End BX TWO FACTOR AUTH  -->
   {/if}
   ```
   
   Position: Nach dem "Bestellungen"-Link, vor dem "Account löschen"-Link (innerhalb des `account_type == 0` Blocks).
   
   **Hinweis:** Die Datei `templates/tpl_modified_nova/module/account.html` inkludiert `account_navigation.html` für die linke Seitennavigation.

3. **Sprachvariablen:** Die Variable `title_nav_two_factor_auth` wird über die Custom-Language-Dateien geladen:
   - `templates/tpl_modified_nova/lang/lang_bx_two_factor_german.custom`
   - `templates/tpl_modified_nova/lang/lang_bx_two_factor_english.custom`

4. **Icon:** Verwendet Font Awesome Icon `fa-solid fa-shield-halved` für die visuelle Darstellung.

### Templates (xtc5)
```
templates/xtc5/
├── module/
│   ├── bx_two_factor_account.html                     # 2FA-Account-Management
│   └── bx_two_factor_verify.html                      # 2FA-Verifizierung
├── css/
│   └── bx_two_factor.css                              # Frontend-Styles
├── buttons/
│   ├── german/
│   │   ├── button_setup_authenticator.gif             # Button: Authenticator einrichten
│   │   └── button_setup_emailcodes.gif                # Button: E-Mail-Codes einrichten
│   └── english/
│       ├── button_setup_authenticator.gif             # Button: Authenticator einrichten
│       └── button_setup_emailcodes.gif                # Button: E-Mail-Codes einrichten
└── mail/ FEHLEN
    ├── german/
    │   ├── bx_two_factor_code_email.html              # E-Mail-Template (DE, HTML)
    │   └── bx_two_factor_code_email.txt               # E-Mail-Template (DE, Text)
    └── english/
        ├── bx_two_factor_code_email.html              # E-Mail-Template (EN, HTML)
        └── bx_two_factor_code_email.txt               # E-Mail-Template (EN, Text)
```

**Hinweise für xtc5:**

1. **Sprachdateien:** Die Datei `templates/xtc5/module/account.html` lädt die Sprachdatei:
   ```smarty
   {if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
   {config_load file="$language/lang_$language.custom" section="bx_two_factor_account"}
   {/if}
   ```

2. **Account-Navigation:** Die Datei `templates/xtc5/module/account.html` wurde mit dem 2FA-Link ergänzt:
   ```html
   {if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
   <!-- Start BX TWO FACTOR AUTH  -->
     <li>
       <a href="{$smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT|xtc_href_link}" title="{#title_nav_two_factor_auth#}">
         <strong>{#title_nav_two_factor_auth#}</strong>
       </a>
     </li>
   <!-- End BX TWO FACTOR AUTH  -->
   {/if}
   ```

3. **Smarty PHP Plugins:** Die Datei `templates/xtc5/smarty/register_php_plugins.php` muss ergänzt werden:
   ```php
   $register_php_plugins = array(
     // ... existing entries ...
     'xtc_image_button', // Benötigt für Button-Grafiken
   );
   ```

### Templates (tpl_modified)
```
templates/tpl_modified/
├── module/
│   ├── bx_two_factor_account.html                     # 2FA-Account-Management
│   └── bx_two_factor_verify.html                      # 2FA-Verifizierung
├── lang/
│   ├── lang_bx_two_factor_german.custom               # Deutsche Frontend-Texte
│   └── lang_bx_two_factor_english.custom              # Englische Frontend-Texte
├── css/
│   └── bx_two_factor.css                              # Frontend-Styles
└── buttons/
    ├── german/
    │   ├── button_setup_authenticator.gif             # Button: Authenticator einrichten
    │   └── button_setup_emailcodes.gif                # Button: E-Mail-Codes einrichten
    └── english/
        ├── button_setup_authenticator.gif             # Button: Authenticator einrichten
        └── button_setup_emailcodes.gif                # Button: E-Mail-Codes einrichten
```

**Hinweise für tpl_modified:**

1. **boxes.php Ergänzung:** `templates/tpl_modified/source/boxes.php` muss ergänzt werden, um die 2FA-Seiten in voller Breite ohne linkes Menü anzuzeigen:
   ```php
   // Im $fullcontent Array hinzufügen:
   FILENAME_BX_TWO_FACTOR_ACCOUNT, // BX TWO FACTOR AUTH 
   FILENAME_BX_TWO_FACTOR_VERIFY, // BX TWO FACTOR AUTH
   ```

2. **Sprachvariablen:** Folgende Dateien müssen mit der Variable `title_nav_two_factor_auth` ergänzt werden:
   - `templates/tpl_modified/lang/lang_german.custom` (title_nav_two_factor_auth = 'Zwei-Faktor-Authentisierung')
   - `templates/tpl_modified/lang/lang_english.custom` (title_nav_two_factor_auth = 'Two-factor authentication')

3. **Account-Navigation:** Die Datei `templates/tpl_modified/module/account.html` muss mit dem 2FA-Link ergänzt werden:
   ```html
   {if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
   <!-- Start BX TWO FACTOR AUTH  -->
     <li>
       <a class="black" href="{$smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT|xtc_href_link}" title="{#title_nav_two_factor_auth#}">
         <strong>{#title_nav_two_factor_auth#}</strong>
       </a>
     </li>
   <!-- End BX TWO FACTOR AUTH  -->
   {/if}
   ```

4. **Smarty PHP Plugins:** Die Datei `templates/tpl_modified/smarty/register_php_plugins.php` muss ggf. ergänzt werden, falls benötigte PHP-Funktionen nicht im Template erkannt werden (`xtc_image`, `xtc_image_button` müssen registriert werden).

### Templates (tpl_modified_responsive)
```
templates/tpl_modified_responsive/
├── module/
│   ├── bx_two_factor_account.html                     # 2FA-Account-Management
│   └── bx_two_factor_verify.html                      # 2FA-Verifizierung
├── lang/
│   ├── lang_bx_two_factor_german.custom               # Deutsche Frontend-Texte
│   └── lang_bx_two_factor_english.custom              # Englische Frontend-Texte
├── css/
│   └── bx_two_factor.css                              # Frontend-Styles
├── javascript/extra/
│   └── bx_two_factor_authenticator.js.php             # Frontend-JavaScript
└── admin/mail/
    ├── german/
    │   ├── bx_two_factor_code_email.html              # E-Mail-Template (DE, HTML)
    │   └── bx_two_factor_code_email.txt               # E-Mail-Template (DE, Text)
    └── english/
        ├── bx_two_factor_code_email.html              # E-Mail-Template (EN, HTML)
        └── bx_two_factor_code_email.txt               # E-Mail-Template (EN, Text)
```
**Sprachvariablen:** Folgende Dateien müssen mit der Variable `title_nav_two_factor_auth` ergänzt werden:
   - `templates/tpl_modified_responsive/lang/lang_german.custom` (title_nav_two_factor_auth = 'Zwei-Faktor-Authentisierung')
   - `templates/tpl_modified_responsive/lang/lang_english.custom` (title_nav_two_factor_auth = 'Two-factor authentication')
**Account-Navigation:** Die Datei `templates/tpl_modified_responsive/module/account.html` muss mit dem 2FA-Link ergänzt werden:
```html
{if isset($LINK_EXPRESS)}
  <li><a class="black" href="{$LINK_EXPRESS}"><strong>{#text_express_checkout#}</strong></a></li>
{/if}

{if $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS && $smarty.const.MODULE_BX_TOTP_AUTHENTICATOR_STATUS == 'True'}
<!-- Start BX TWO FACTOR AUTH  -->
  <li>
    <a class="black" href="{$smarty.const.FILENAME_BX_TWO_FACTOR_ACCOUNT|xtc_href_link}" title="{#title_nav_two_factor_auth#}">
      <strong>{#title_nav_two_factor_auth#}</strong>
    </a>
  </li>
<!-- End BX TWO FACTOR AUTH  -->
{/if}

{if $LINK_PASSWORD}
  <li><a class="black" href="{$LINK_PASSWORD}"><strong>{#text_password#}</strong></a></li>
{/if}
```
**boxes.php Ergänzung:** `templates/tpl_modified_responsive/source/boxes.php` muss ergänzt werden, um die 2FA-Seiten in voller Breite ohne linkes Menü anzuzeigen:
```php
  // Im $fullcontent Array hinzufügen:
  FILENAME_BX_TWO_FACTOR_ACCOUNT, // BX TWO FACTOR AUTH 
  FILENAME_BX_TWO_FACTOR_VERIFY, // BX TWO FACTOR AUTH
```

## 🔧 Funktionsübersicht

### Admin-Komponenten
- **bx_totp_authenticator.php** - Hauptverwaltungsseite für Admins zur Konfiguration ihrer TOTP-2FA
- **bx_totp_helper.php** - Core-Klasse für TOTP-Funktionalität (Shared zwischen Admin & Frontend)
- **System-Modul** - Installation/Deinstallation und Konfiguration

### Frontend-Komponenten
- **bx_two_factor_auth.php** - Orchestriert TOTP und E-Mail-2FA
- **bx_two_factor_account.php** - Kundenseite zur 2FA-Verwaltung
- **bx_two_factor_verify.php** - Login-Verifizierung

### Unterstützte Templates
- tpl_modified_nova (Haupt-Template)
- tpl_modified_responsive
- tpl_modified
- xtc5

---

## 📊 Statistik

- **Kern-Dateien:** 10 Dateien
- **Sprachdateien:** 3 Dateien (DE, EN)
- **Templates:** 3 verschiedene Template-Sets
- **Template-Dateien pro Set:** ~10 Dateien
- **Gesamt:** ~40+ Dateien im gesamten 2FA-System

---

## 🔐 Sicherheitsfeatures

- TOTP (Time-based One-Time Password) gemäß RFC 6238
- E-Mail-basierte 2FA als Alternative
- Backup-Codes für Notfallzugriff
- QR-Code-Generierung für einfaches Setup
- PDF-Export von Backup-Codes und TOTP-Secret
- Session-basierte Verifizierung
- Automatisches Timeout bei E-Mail-Codes

---

## 📝 Hinweise

- Das Modul nutzt den **bx_dependency_resolver** für optionale Abhängigkeiten (QR-Code, TCPDF)
- TOTP-Codes sind 30 Sekunden gültig (Standard)
- E-Mail-Codes sind 10 Minuten gültig
- Backup-Codes können nur einmal verwendet werden
- Unterstützt sowohl Kunden als auch Admins

---

**Projekt:** BX TOTP Authenticator / BX Two-Factor Authentication  
**Version:** 1.0.0  
**Kompatibilität:** modified eCommerce Shop  
**Autor:** benax
