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

## Schritt 2: PHP-Version (wichtig für Formulare)

Die Formular-E-Mails brauchen **PHP 8.0 oder höher**.

1. Strato-Kundenbereich → Ihr Paket / Domain → **PHP-Einstellungen** (oder „Skriptsprache“).
2. **PHP 8.2** oder **8.3** wählen (mindestens **8.0**).
3. Speichern und **2–5 Minuten warten**.

**Symptom bei zu alter PHP-Version:** Aufruf von  
`https://www.radsport-ziller.com/php/send-contact.php`  
zeigt nur: *„Composer detected issues… PHP version >= 8.0.0“* → dann ist Schritt 2 noch nicht erledigt.

---

## Schritt 3: E-Mail-Postfach und `mail-config.php`

### Postfächer bei Strato

| Zweck | Adresse |
|--------|---------|
| **Empfang** (beide Formulare) | `radsport@radsport-ziller.com` |
| **SMTP-Anmeldung** (Versand aus PHP) | Mailbox mit Passwort, z. B. `info@radsport-ziller.com` **oder** `radsport@radsport-ziller.com` |

Strato-Versand: **`smtp.strato.de`**, Port **587**, **STARTTLS**, Login = **volle E-Mail-Adresse** + **Passwort der Mailbox**.

### `mail-config.php` anlegen

1. Auf dem PC: `php/mail-config.example.php` kopieren → `php/mail-config.php`.
2. Eintragen (Beispiel mit Mailbox **info@**):

```php
return [
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 587,
    'smtp_user' => 'info@radsport-ziller.com',      // Mailbox für SMTP-Login
    'smtp_pass' => 'IHR_POSTFACH_PASSWORT',
    'from_email' => 'info@radsport-ziller.com',     // = smtp_user (empfohlen)
    'from_name' => 'Radsport Ziller Website',
];
```

3. **`php/mail-config.php` per FTP** in den Ordner `php/` auf dem Server hochladen (nicht ins öffentliche Git).

`from_email` und `smtp_user` sollten **dieselbe existierende Mailbox** sein – sonst lehnt Strato oft den Versand ab.

---

## Schritt 4: Checkliste Formular-Versand

| Prüfung | So testen | Erwartung |
|----------|-----------|-----------|
| PHP ≥ 8 | `https://www.radsport-ziller.com/php/mail-status.php` | `"php_min_8": true` |
| vendor/ | gleiche URL | `"vendor_autoload": true` |
| mail-config | gleiche URL | `"mail_config": true` |
| Gesamt | gleiche URL | `"ready": true` |
| Kontaktformular | Seite Kontakt → Test senden | Mail an **radsport@** |
| Reparaturformular | Seite Service → Test senden | Mail an **radsport@** |

`mail-status.php` nach erfolgreichem Test **optional per FTP löschen** (nur Diagnose).

---

## Schritt 5: HTTPS (SSL)

1. **SSL/TLS** für die Domain aktivieren (Let’s Encrypt).
2. Optional: Weiterleitung **HTTP → HTTPS**.

---

## Schritt 6: Häufige Fehler

| Was Sie sehen | Ursache | Lösung |
|---------------|---------|--------|
| *Composer … PHP >= 8.0* im Browser | PHP zu alt | Schritt 2: PHP 8.x aktivieren |
| *Der Versand ist momentan nicht möglich* im Formular | `mail-config.php` fehlt | Schritt 3 |
| *E-Mail konnte nicht gesendet werden* | Falsches SMTP-Passwort oder falsche `smtp_user` | Passwort in Strato prüfen, Mailbox existiert? |
| Netzwerkfehler im Formular | `php/` nicht erreichbar (404) | `php/` und Dateien im Web-Root prüfen |
| Mail kommt nicht an | Spam-Ordner / falsches Postfach | **radsport@** prüfen; ggf. Weiterleitung in Strato |
| 404 auf `send-contact.php` | Falscher Upload-Pfad | `index.html` und `php/` auf gleicher Ebene |

**Strato-Fehlerprotokoll:** Kundenlogin → Hosting → Logs / PHP-Error-Log (dort stehen SMTP-Fehler von PHPMailer).

---

## Schritt 7: Technik Kurzüberblick

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
