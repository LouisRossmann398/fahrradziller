# Website bei Strato live schalten (Schritt für Schritt)

Diese Anleitung setzt voraus, dass Sie **Webhosting bei Strato** haben und die **Domain** dort verwaltet wird.

## Was auf den Server kommt

Laden Sie den **gesamten Projektinhalt** hoch (mindestens):

- `index.html`, `css/`, `js/`, `pages/`, `assets/`
- `php/` (Skripte für Formular-E-Mails)
- `vendor/` (von Composer erzeugt – wird für PHPMailer benötigt)

**Nicht** per Git mit hochladen müssen: `php/mail-config.php` (legt man **nur auf dem Server** an, siehe unten).

---

## Schritt 1: Dateien per FTP/SFTP hochladen

1. Im **Strato-Kundenlogin** unter **SFTP & SSH** (oder FTP) einen Zugang anlegen: Wenn noch **0 Konten** angezeigt werden, auf **„+ Neu anlegen“** klicken und **Benutzername, Passwort und Server/Hostname** notieren (ohne eigenes Konto gibt es keine Login-Daten zum Hochladen).
   - **Kommentar:** frei wählbar (z. B. „Website-Upload“), nur Beschriftung in Strato.
   - **Typ:** **SFTP** reicht zum Hochladen; **SFTP + SSH** nur, wenn Sie eine Shell auf dem Server brauchen.
   - **Stammverzeichnis:** Ordner wählen, unter dem Ihr **Web-Root** erreichbar ist (häufig Zugriff auf **`htdocs`** oder den Ordner Ihrer Domain). Nicht versehentlich nur einen Unterordner wählen (z. B. eine fremde WordPress-Installation), wenn die neue Website woanders im Webspace liegt.
2. Mit einem Programm wie **FileZilla** oder **Cyberduck** verbinden.
3. Auf dem Server in den **Web-Root** wechseln – oft heißt der Ordner **`htdocs`** oder **`httpdocs`** (ggf. in der Strato-Hilfe zu Ihrem Paket nachlesen).
4. Alle Website-Dateien so hochladen, dass **`index.html` direkt im Web-Root** liegt (nicht eine Ebene zu tief).

Ergebnis soll z. B. so aussehen:

```text
htdocs/
  index.html
  composer.json
  css/
  js/
  pages/
  assets/
  php/
  vendor/
```

---

## Schritt 2: PHP-Version

1. Im Strato-Menü zum **PHP**- bzw. **Skript**-Bereich Ihres Pakets gehen.
2. **PHP 8.0 oder höher** wählen (empfohlen: aktuelle 8.x).

---

## Schritt 3: E-Mail-Postfach und SMTP

1. Legen Sie bei Strato sicher die Postfächer **`info@radsport-ziller.com`** und **`radsport@radsport-ziller.com`** an (oder prüfen Sie, dass sie existieren und Sie die Passwörter kennen).
2. Für den **Versand aus PHP** nutzt Strato den Postausgangsserver **`smtp.strato.de`**, Port **587**, **STARTTLS**, mit **Anmeldung** (vollständige E-Mail-Adresse + Passwort der Mailbox).  
   Details: [Strato FAQ – E-Mail-Versand aus PHP](https://www.strato.de/faq/mail/e-mail-versand-aus-cgi-und-php-skripten/)

3. Auf Ihrem PC: Datei **`php/mail-config.example.php`** kopieren und als **`php/mail-config.php`** speichern.

4. **`php/mail-config.php`** bearbeiten:
   - `smtp_user` = die **E-Mail-Adresse der Mailbox**, mit der Sie sich am SMTP anmelden (meist **`info@radsport-ziller.com`**).
   - `smtp_pass` = **Passwort dieser Mailbox**.
   - `from_email` = dieselbe Adresse wie `smtp_user` (empfohlen, weniger Probleme mit Absenderprüfung).
   - `from_name` = z. B. `Radsport Ziller Website`.

5. **`mail-config.php` per FTP in den Ordner `php/` auf den Server** hochladen (die Datei ist in `.gitignore` und soll **nicht** ins öffentliche Repository).

Die Skripte senden **Kontaktanfragen** an `info@radsport-ziller.com` und **Termin-/Reparaturanfragen** an `radsport@radsport-ziller.com`. Die SMTP-Anmeldung kann bei **einer** Mailbox erfolgen; beide Ziel-Adressen müssen als Postfach oder Weiterleitung bei Strato existieren.

---

## Schritt 4: HTTPS (SSL)

1. Im Strato-Kundenbereich **SSL/TLS** für die Domain aktivieren (z. B. Let’s Encrypt).
2. Optional: **Automatische Weiterleitung von HTTP auf HTTPS** einschalten.

---

## Schritt 5: Testen

1. **Startseite:** `https://ihre-domain.de/`
2. **Kontaktformular:** eine Testnachricht senden – E-Mail an **info@** prüfen (auch Spam-Ordner).
3. **Service / Terminanfrage:** Testanfrage – E-Mail an **radsport@** prüfen.

Wenn nichts ankommt:

- Strato **Fehlerprotokolle** / PHP-Logs ansehen.
- Prüfen, ob `vendor/` vollständig hochgeladen wurde.
- Prüfen, ob `php/mail-config.php` existiert und SMTP-Daten stimmen.
- Test: Im Browser nur `https://ihre-domain.de/php/send-contact.php` aufrufen – erwartet wird **kein** JSON-Erfolg bei GET (Absicht); ein **500** deutet auf fehlende Konfiguration oder fehlendes `vendor` hin.

---

## Schritt 6: Website nicht in einem Unterordner?

Die Formular-URLs werden im JavaScript relativ zur aktuellen Seite aufgelöst (`../php/...`). Liegt die gesamte Website in einem Unterordner (z. B. `https://domain.de/shop/`), funktioniert das weiterhin, solange **`pages/`** und **`php/`** relativ zueinander wie im Projekt liegen.

---

## Lokaler Test (optional)

PHP und Composer lokal installiert:

```bash
cd /Pfad/zum/Projekt
cp php/mail-config.example.php php/mail-config.php
# mail-config.php mit echten SMTP-Daten füllen
php -S localhost:8080
```

Dann `http://localhost:8080/pages/kontakt.html` öffnen und testen.

---

## Kurzüberblick Technik

| Formular | Ziel-E-Mail |
|----------|-------------|
| Kontakt | info@radsport-ziller.com |
| Termin / Reparatur | radsport@radsport-ziller.com |

Versand über **PHPMailer** und **SMTP (Strato)**. Spam-Schutz: verstecktes **Honeypot-Feld** (nicht ausfüllen).
