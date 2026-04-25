# Radsport Ziller - Website

Responsive Firmenwebsite fuer Radsport Ziller mit statischen Seiten und serverseitigem Formularversand (PHP + PHPMailer).

## Projektstruktur

```
Ziller/
|- index.html
|- pages/
|  |- fahrraeder.html
|  |- leasing.html
|  |- service.html
|  |- neuigkeiten.html
|  |- karriere.html
|  |- kontakt.html
|  |- impressum.html
|  `- datenschutz.html
|- css/styles.css
|- js/main.js
|- php/
|  |- send-contact.php
|  |- send-service.php
|  |- mail-common.php
|  |- mail-config.example.php
|  `- .htaccess
|- assets/images/
|- vendor/                # Composer-Abhaengigkeiten (PHPMailer)
|- composer.json
`- HOSTING_STRATO.md
```

## Was bereits fertig ist

- Responsive Navigation und einheitliche Seitenlayouts
- Startseite mit Bild-Slider und Inhaltskacheln
- Kontaktformular und Service-/Reparaturformular mit Frontend-Validierung
- Serverseitiger E-Mail-Versand ueber Strato SMTP
- Rechtsseiten (Impressum, Datenschutz) und einheitlicher Footer

## Formularversand (wichtig)

- Kontaktformular: Versand ueber `php/send-contact.php`
- Serviceformular: Versand ueber `php/send-service.php`
- Gemeinsame SMTP-Logik in `php/mail-common.php`
- Empfaengeradressen und SMTP-Daten werden in `php/mail-config.php` gepflegt

`mail-config.php` liegt absichtlich **nicht** im Repository (siehe `.gitignore`).
Als Vorlage dient `php/mail-config.example.php`.

## Lokale Entwicklung

```bash
cd /Pfad/zum/Ziller
python3 -m http.server 8080
```

Dann im Browser:

- Startseite: `http://localhost:8080/`
- Unterseiten: `http://localhost:8080/pages/...`

Wenn Formulare lokal getestet werden sollen, einen PHP-Server verwenden:

```bash
php -S localhost:8080
```

## Deployment / Strato

Die komplette Schritt-fuer-Schritt-Anleitung steht in:

- `HOSTING_STRATO.md`

Kurzfassung:

1. Dateien in den richtigen Webspace-Ordner hochladen
2. PHP 8.x aktivieren
3. `php/mail-config.php` aus der Vorlage erstellen und SMTP eintragen
4. Formulare auf Live-Domain testen

## Wartungshinweise fuer den Kunden

- Inhalte/texte: direkt in den entsprechenden HTML-Dateien
- Design/Abstaende/Farben: `css/styles.css`
- Interaktionen/Formular-Frontend: `js/main.js`
- Formular-Empfaenger oder SMTP: `php/send-*.php` und `php/mail-config.php`

## Technischer Stand

- HTML5, CSS3, Vanilla JavaScript
- PHP 8+ fuer Mail-Endpunkte
- PHPMailer via Composer
