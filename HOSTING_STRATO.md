# Website bei Strato live schalten (Schritt für Schritt)

Diese Anleitung setzt voraus, dass Sie **Webhosting bei Strato** haben und die **Domain** dort verwaltet wird.

## Was auf den Server kommt

Laden Sie den **gesamten Projektinhalt** hoch (mindestens):

- `index.html`, `css/`, `js/`, `pages/`, `assets/`
- `php/` (Skripte für Formular-E-Mails)
- `vendor/` (PHPMailer – **Pflicht**, liegt im Git-Repository)

**Nicht** per Git mit hochladen: `php/mail-config.php` (nur **auf dem Server** anlegen, siehe Schritt 3).

---

## Schritt 1: Dateien per FTP/SFTP hochladen

1. Im **Strato-Kundenlogin** unter **SFTP & SSH** (oder FTP) einen Zugang anlegen.
2. Mit **FileZilla** oder **Cyberduck** verbinden.
3. In den **Web-Root** wechseln – oft **`htdocs`** oder der Ordner Ihrer Domain.
4. Dateien so hochladen, dass **`index.html` direkt im Web-Root** liegt.

```text
htdocs/
  index.html
  composer.json
  css/
  js/
  pages/
  assets/
  php/
  vendor/          ← unbedingt mit hochladen!
```

---

## Schritt 2: PHP-Version (Pflicht – vor allem anderen)

**Aktuell bei Ihnen:** PHP 7.2 Extended Support → Formulare funktionieren damit **nicht**.

### In Strato umstellen

1. Einloggen: [Strato Kunden-Login](https://www.strato.de/apps/CustomerLogin)
2. Menü: **Hosting** → Ihr Paket auswählen
3. Domain **radsport-ziller.com** (oder „PHP & Datenbank“ / „Skripte“)
4. Bereich **PHP-Version** / **Skriptsprache**
5. Wählen: **PHP 8.2** oder **PHP 8.3** (nicht 7.2, nicht „Extended Support“)
6. **Speichern** → **5 Minuten warten**

### Kurztest

Im Browser öffnen:

`https://www.radsport-ziller.com/php/send-contact.php`

- **Vorher (PHP 7.2):** Text *„Composer detected issues… PHP >= 8.0“*
- **Nachher (PHP 8.x):** Text *„Nicht erlaubt.“* oder JSON mit `"ok": false` → **das ist gut**, PHP läuft dann

---

## Schritt 3: `mail-config.php` anlegen (auf Ihrem Mac, dann hochladen)

Diese Datei enthält das **SMTP-Passwort** und liegt **nur auf dem Server**, nicht in Git.

### 3a – Welche Mailbox?

Sie brauchen **eine Strato-Mailbox mit Passwort**, mit der sich PHP beim Versand anmeldet.

| Was | Adresse |
|-----|---------|
| **Wohin Formular-Mails gehen** | `radsport@radsport-ziller.com` (muss als Postfach existieren) |
| **Womit PHP sich bei Strato anmeldet** | z. B. `info@radsport-ziller.com` **oder** `radsport@radsport-ziller.com` |

**Empfehlung:** Wenn Sie nur ein Passwort kennen – nehmen Sie **genau diese Mailbox** für `smtp_user` und `from_email`.

Passwort vergessen? Strato → **E-Mail** → Postfach → Passwort zurücksetzen / neu setzen.

### 3b – Datei auf dem Mac erstellen

1. Ordner öffnen: `Documents/Ziller/php/`
2. Datei **`mail-config.example.php`** duplizieren (Rechtsklick → Duplizieren)
3. Umbenennen in: **`mail-config.php`**
4. `mail-config.php` mit **Texteditor** öffnen (Cursor, TextEdit, VS Code)
5. Nur diese Zeilen anpassen:

```php
return [
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 587,
    'smtp_user' => 'info@radsport-ziller.com',     // ← Ihre Mailbox (volle Adresse)
    'smtp_pass' => 'HIER_IHR_ECHTES_PASSWORT',     // ← Passwort dieser Mailbox
    'from_email' => 'info@radsport-ziller.com',    // ← dieselbe Adresse wie smtp_user
    'from_name' => 'Radsport Ziller Website',
];
```

6. **Speichern**
7. **Wichtig:** `mail-config.php` **nicht** in Git committen (steht in `.gitignore`)

**Beispiel**, wenn Sie `radsport@` für SMTP nutzen:

```php
'smtp_user' => 'radsport@radsport-ziller.com',
'smtp_pass' => 'IhrPasswort',
'from_email' => 'radsport@radsport-ziller.com',
```

### 3c – Per FTP/SFTP auf den Server legen

1. **FileZilla** (oder Cyberduck) öffnen, mit Strato verbinden (Zugangsdaten aus Strato → SFTP)
2. Rechts (Server) in den **Web-Root** gehen, wo auch **`index.html`** liegt
3. Ordner **`php`** öffnen
4. Links (Mac) zu `Documents/Ziller/php/` gehen
5. Datei **`mail-config.php`** von links nach rechts in den Server-Ordner **`php/`** ziehen
6. Falls gefragt: **Überschreiben** bestätigen

### 3d – Prüfen auf dem Server

Im Ordner `php/` auf dem Server müssen u. a. liegen:

- `mail-config.php` ← **neu von Ihnen**
- `mail-config.example.php`
- `send-contact.php`
- `send-service.php`
- `mail-status.php`
- `mail-common.php`

**Test im Browser:** `mail-config.php` direkt aufrufen sollte **Fehler 403/Forbidden** zeigen (geschützt) – **nicht** den Dateiinhalt. Das ist in Ordnung.

---

## Schritt 4: Ordner `vendor/` auf dem Server

PHPMailer steckt in **`vendor/`** (Projektroot, **neben** `php/`, nicht darin).

### 4a – Auf dem Mac prüfen

Ordner `Documents/Ziller/vendor/` öffnen. Darin muss u. a. existieren:

- `vendor/autoload.php`
- `vendor/phpmailer/`

Fehlt `vendor/` lokal:

```bash
cd ~/Documents/Ziller
composer install
```

### 4b – Per FTP hochladen

1. FileZilla: links `Documents/Ziller/`, rechts Web-Root (wo `index.html` liegt)
2. Ordner **`vendor`** von links nach rechts ziehen (kann **einige Minuten** dauern – viele kleine Dateien)
3. Warten bis Upload **fertig** ist (keine roten Fehler in FileZilla)

### 4c – Struktur auf dem Server (Kontrolle)

```text
htdocs/   (oder Ihr Web-Root)
  index.html
  php/
    mail-config.php      ← von Ihnen angelegt
    send-contact.php
    mail-status.php
    ...
  vendor/
    autoload.php         ← muss existieren
    phpmailer/
  css/
  js/
  pages/
```

**Häufiger Fehler:** Nur `php/` hochgeladen, **`vendor/` vergessen** → Formular schlägt fehl.

---

## Schritt 5: Diagnose-URL (`mail-status.php`)

### 5a – Datei auf dem Server?

Aus dem letzten Git-Stand muss auf dem Server liegen: **`php/mail-status.php`**

Falls fehlt: aus dem Projekt `php/mail-status.php` per FTP in Server-Ordner `php/` hochladen.

### 5b – Im Browser öffnen

URL: **https://www.radsport-ziller.com/php/mail-status.php**

### 5c – Antwort lesen

**Alles OK** (dann Formulare testen):

```json
{
    "php_version": "8.2.x",
    "php_min_8": true,
    "vendor_autoload": true,
    "mail_config": true,
    "ready": true,
    "hint": "Grundvoraussetzungen erfüllt..."
}
```

**Noch etwas fehlt:**

| Anzeige | Bedeutung | Was tun |
|---------|-----------|---------|
| `"php_min_8": false` | PHP noch 7.2 | Schritt 2: PHP 8.x in Strato |
| `"vendor_autoload": false` | `vendor/` fehlt | Schritt 4: Ordner hochladen |
| `"mail_config": false` | `mail-config.php` fehlt | Schritt 3: Datei anlegen + hochladen |
| `"ready": false` | mindestens einer Punkt oben | `hint` in der JSON lesen |

### 5d – Formulare live testen

1. **Kontakt:** https://www.radsport-ziller.com/pages/kontakt.html → Testnachricht → Postfach **radsport@** (auch Spam)
2. **Reparatur:** https://www.radsport-ziller.com/pages/service.html → Terminanfrage → ebenfalls **radsport@**

Erfolg auf der Website: grüne Meldung *„Vielen Dank! Ihre Nachricht wurde gesendet.“*

Fehler im Formular:

| Meldung | Lösung |
|---------|--------|
| *Der Versand ist momentan nicht möglich* | `mail-config.php` fehlt oder PHP-Fehler → `mail-status.php` prüfen |
| *E-Mail konnte nicht gesendet werden* | Falsches Passwort in `mail-config.php` oder falsche `smtp_user` |
| *Netzwerkfehler* | `php/` nicht erreichbar, Dateien fehlen |

### 5e – Optional aufräumen

Wenn alles läuft: `php/mail-status.php` per FTP vom Server **löschen** (nur Diagnose, nicht zwingend nötig).

---

## Schritt 6: Kurz-Checkliste (zum Abhaken)

- [ ] PHP **8.2/8.3** in Strato (nicht 7.2)
- [ ] Postfach **radsport@radsport-ziller.com** existiert
- [ ] **`php/mail-config.php`** auf Server mit richtigem Passwort
- [ ] Ordner **`vendor/`** im Web-Root neben `php/`
- [ ] **`mail-status.php`** → `"ready": true`
- [ ] Kontaktformular Test-Mail angekommen
- [ ] Reparaturformular Test-Mail angekommen

---

## Schritt 7: HTTPS (SSL)

1. **SSL/TLS** für die Domain aktivieren (Let’s Encrypt).
2. Optional: Weiterleitung **HTTP → HTTPS**.

---

## Schritt 8: Häufige Fehler

| Was Sie sehen | Ursache | Lösung |
|---------------|---------|--------|
| *Composer … PHP >= 8.0* im Browser | PHP zu alt | Schritt 2: PHP 8.x aktivieren |
| *Der Versand ist momentan nicht möglich* im Formular | `mail-config.php` fehlt | Schritt 3 |
| *E-Mail konnte nicht gesendet werden* | Falsches Passwort, falscher Port oder Sonderzeichen im Passwort | Webmail-Login testen; in `mail-config.php` Port **465** probieren (siehe unten) |
| Netzwerkfehler im Formular | `php/` nicht erreichbar (404) | `php/` und Dateien im Web-Root prüfen |
| Mail kommt nicht an | Spam-Ordner / falsches Postfach | **radsport@** prüfen; ggf. Weiterleitung in Strato |
| 404 auf `send-contact.php` | Falscher Upload-Pfad | `index.html` und `php/` auf gleicher Ebene |

**Strato-Fehlerprotokoll:** Kundenlogin → Hosting → Logs / PHP-Error-Log (dort stehen SMTP-Fehler von PHPMailer).

### SMTP schlägt fehl, obwohl `mail-status.php` → `ready: true`

1. **Echtes Postfach?** In Strato muss `radsport@…` ein **eigenes Postfach mit Passwort** sein – **kein reiner Alias**, der nur an `info@` weiterleitet. Alias ohne Login → SMTP schlägt fehl, Webmail oft auch.  
   **Lösung:** In `mail-config.php` testweise **`info@radsport-ziller.com`** für `smtp_user` und `from_email` (Passwort von info@), Empfang der Formulare bleibt bei `radsport@` im PHP-Code.
2. **Webmail testen:** https://webmail.strato.de mit **derselben Adresse wie `smtp_user`** + Passwort.  
   - Geht nicht → Passwort in Strato zurücksetzen, in `mail-config.php` neu speichern (keine Leerzeichen am Ende).
3. **SMTP-Diagnose (zeigt den echten Fehler):**  
   In `mail-config.php` auf dem Server ergänzen:
   ```php
   'diagnose_key' => 'mein-geheimes-testwort',
   ```
   Dateien hochladen: `php/mail-common.php`, `php/mail-smtp-diagnose.php`  
   Im Browser öffnen:  
   `https://www.radsport-ziller.com/php/mail-smtp-diagnose.php?key=mein-geheimes-testwort`  
   → zeigt, ob Port **465** oder **587** verbindet und die **Fehlermeldung** von Strato.  
   Danach **`diagnose_key` wieder löschen** und `mail-smtp-diagnose.php` optional vom Server entfernen.
4. **Port** in `mail-config.php`: den Port wählen, den die Diagnose als `"ok": true` meldet ([Strato-Ports](https://www.strato.de/faq/mail/e-mailserver-adressen-ports-ssl-tls/)).
5. **Passwort in PHP:** nur einfache Anführungszeichen: `'smtp_pass' => 'IhrPasswort'`

---

## Schritt 9: Technik Kurzüberblick

| Formular | PHP-Skript | Ziel-E-Mail |
|----------|------------|-------------|
| Kontakt | `php/send-contact.php` | radsport@radsport-ziller.com |
| Termin / Reparatur | `php/send-service.php` | radsport@radsport-ziller.com |

Versand: **PHPMailer** über **SMTP (Strato)**. Spam-Schutz: verstecktes Honeypot-Feld (nicht ausfüllen).

---

## Lokaler Test (optional)

```bash
cd /Pfad/zum/Projekt
cp php/mail-config.example.php php/mail-config.php
# mail-config.php mit echten SMTP-Daten füllen
php -S localhost:8080
```

Dann `http://localhost:8080/pages/kontakt.html` öffnen.

---

## Unterordner?

Formular-URLs werden relativ aufgelöst (`../php/...`). `pages/` und `php/` müssen wie im Projekt zueinander liegen.
