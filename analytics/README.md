# Radsport Ziller Analytics - Installation & Setup

## 🎯 Was ist das?

Ein vollständig DSGVO-konformes, selbst-gehostetes Analytics-System für die Radsport Ziller Website.

**Features:**
- ✅ 100% DSGVO-konform
- ✅ Keine Cookies
- ✅ IP-Anonymisierung
- ✅ Datenminimierung
- ✅ Kein Cookie-Banner erforderlich
- ✅ Professionelles Admin-Dashboard

## 📋 Installation auf Strato

### Schritt 1: Datenbank erstellen

1. Bei Strato einloggen
2. MySQL-Datenbank erstellen:
   - Gehe zu "Datenbanken" → "MySQL"
   - Neue Datenbank erstellen
   - Notiere dir:
     - Datenbank-Name
     - Benutzername
     - Passwort
     - Hostname (meist: localhost)

3. SQL-Setup ausführen:
   - Öffne phpMyAdmin (bei Strato verfügbar)
   - Wähle deine Datenbank
   - Gehe zu "SQL"
   - Kopiere den Inhalt von `analytics/setup.sql`
   - Führe das SQL-Script aus

### Schritt 2: Konfiguration anpassen

Öffne `analytics/config.php` und passe an:

```php
// Datenbank-Zugangsdaten
define('DB_HOST', 'localhost'); // Bei Strato meist localhost
define('DB_NAME', 'dein_datenbankname'); // Dein DB-Name
define('DB_USER', 'dein_benutzername'); // Dein DB-User
define('DB_PASS', 'dein_passwort'); // Dein DB-Passwort

// Admin-Login UNBEDINGT ÄNDERN!
define('ADMIN_USERNAME', 'admin'); // Ändere auf deinen Username
define('ADMIN_PASSWORD', password_hash('DeIn$iChErE$PaS$w0Rt!', PASSWORD_DEFAULT));
```

**WICHTIG:** Ändere unbedingt Username und Passwort!

### Schritt 3: Dateien hochladen

Via FTP/SFTP alle Dateien auf Strato hochladen:

```
/deine-website/
├── analytics/
│   ├── admin/
│   │   ├── index.php
│   │   └── dashboard.php
│   ├── config.php (ANGEPASST!)
│   ├── track.php
│   └── setup.sql
├── js/
│   ├── main.js
│   └── analytics.js (NEU!)
├── index.html (UPDATED)
└── pages/
    └── *.html (ALLE UPDATED)
```

### Schritt 4: Berechtigungen prüfen

- `analytics/` Ordner: 755
- Alle `.php` Dateien: 644

### Schritt 5: Admin-Login testen

1. Gehe zu: `https://deine-website.de/analytics/admin/`
2. Melde dich mit deinem Username/Passwort an
3. Dashboard sollte erscheinen

## 🔐 Sicherheit

**WICHTIG - Nach Installation:**

1. **Admin-Passwort ändern** in `config.php`
2. **`.htaccess` Schutz** (optional, für extra Sicherheit):

Erstelle `.htaccess` in `/analytics/admin/`:

```apache
AuthType Basic
AuthName "Analytics Admin"
AuthUserFile /pfad/zu/.htpasswd
Require valid-user
```

3. **HTTPS aktivieren** bei Strato (Let's Encrypt Zertifikat)

## 📊 Dashboard-Zugriff

**URL:** `https://deine-website.de/analytics/admin/`

**Login:**
- Username: (siehe config.php)
- Passwort: (siehe config.php)

## 📈 Tracking-Features

Das System trackt automatisch:

**Allgemeine Metriken:**
- ✅ Seitenaufrufe
- ✅ Unique Visitors (Sessions)
- ✅ Verweildauer pro Seite
- ✅ Durchschnittliche Seitenzahl pro Besuch
- ✅ Beliebte Seiten
- ✅ Entry & Exit Pages

**Geräte & Browser:**
- ✅ Desktop / Tablet / Mobile
- ✅ Browser-Typ
- ✅ Referrer (Traffic-Quellen)

**Conversions & Interaktionen:**
- ✅ Kontaktformular-Absendungen
- ✅ Service-Anfragen
- ✅ Telefon-Klicks
- ✅ E-Mail-Klicks
- ✅ Leasing-Rechner Nutzung
- ✅ PDF-Downloads (Angebote)

**Zeitanalysen:**
- ✅ Traffic nach Tageszeit
- ✅ Traffic nach Wochentag
- ✅ Trend über Zeit

## 🔧 Wartung

### Datenbereinigung (nach 1 Jahr)

Richte einen Cron-Job bei Strato ein (täglich ausführen):

```bash
# Daten älter als 365 Tage löschen
mysql -u USER -pPASSWORD DBNAME -e "
DELETE FROM analytics_pageviews WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
DELETE FROM analytics_sessions WHERE started_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
DELETE FROM analytics_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
"
```

### Backup

Sichere regelmäßig deine Datenbank:

```bash
mysqldump -u USER -pPASSWORD DBNAME > backup_$(date +%Y%m%d).sql
```

## 📄 Datenschutzerklärung

Füge folgenden Text zu deiner Datenschutzerklärung hinzu:

**Siehe: `DATENSCHUTZ_ERGAENZUNG.txt`**

## 🆘 Troubleshooting

**Problem: "Database connection failed"**
- Prüfe `config.php` - sind alle DB-Zugangsdaten korrekt?
- Teste DB-Zugriff via phpMyAdmin

**Problem: "Keine Daten im Dashboard"**
- Prüfe ob `track.php` erreichbar ist: `/analytics/track.php`
- Prüfe Browser-Console auf Fehler (F12)
- Prüfe ob `analytics.js` geladen wird

**Problem: "Login funktioniert nicht"**
- Passwort in `config.php` mit `password_hash()` erstellt?
- Session-Support bei Strato aktiviert?

**Problem: "Tracking funktioniert nicht"**
- Prüfe ob JavaScript aktiviert ist
- Prüfe Browser-Console (F12) auf Fehler
- Prüfe ob `/analytics/track.php` 200 zurückgibt

## 📞 Support

Bei Fragen zur Installation:
1. Prüfe diese README
2. Prüfe Browser-Console und Server-Logs
3. Prüfe Strato Dokumentation für PHP/MySQL

## 🎉 Fertig!

Dein DSGVO-konformes Analytics läuft jetzt!

**Nächste Schritte:**
1. Datenschutzerklärung updaten
2. Website auf Strato deployen
3. Analytics-Dashboard regelmäßig checken
4. Insights gewinnen & Website optimieren 🚀
