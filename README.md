# Radsport Ziller - Website

Moderne, responsive Website für das Fahrradfachgeschäft Radsport Ziller.

## 📁 Projektstruktur

```
Ziller/
├── index.html              # Startseite
├── css/
│   └── styles.css          # Haupt-Stylesheet (Mobile-First, responsive)
├── js/
│   └── main.js             # JavaScript für Navigation & Accessibility
├── pages/
│   ├── fahrraeder.html     # Fahrrad-Kategorien Übersicht
│   ├── leasing.html        # Fahrrad-Leasing Informationen
│   ├── service.html        # Service & Reparatur (in Vorbereitung)
│   ├── neuigkeiten.html    # Neuigkeiten & Angebote (in Vorbereitung)
│   ├── kontakt.html        # Über uns & Kontaktformular
│   ├── impressum.html      # Impressum
│   └── datenschutz.html    # Datenschutzerklärung
└── assets/
    └── images/             # Platzhalter für Bilder
```

## 🎨 Design

- **Farbschema**: Rot (#dc0000), Weiß (#ffffff), Schwarz (#000000)
- **Typografie**: System-Schriften für optimale Performance
- **Layout**: Mobile-First, vollständig responsive
- **Accessibility**: Textgrößen-Anpassung und Kontrast-Modus integriert

## ✨ Features

### Barrierefreiheit (Accessibility)
- **Textgröße anpassen**: 3 Stufen (Normal, Groß, Sehr Groß)
- **Kontrast anpassen**: Umschaltung zwischen normalem und hohem Kontrast
- Einstellungen werden im Browser gespeichert (localStorage)
- Semantisches HTML für Screen-Reader
- Keyboard-Navigation optimiert
- Skip-to-Content Link

### Navigation
- Responsive Menü mit Mobile-Toggle
- Aktive Seite wird automatisch hervorgehoben
- Smooth Scrolling für Anker-Links

### Kontaktformular
- Formular-Validierung (Frontend)
- E-Mail-Format-Prüfung
- Erfolgs- und Fehlermeldungen
- DSGVO-Checkbox
- Backend-Integration vorbereitet (noch nicht implementiert)

## 🚀 Verwendung

### Lokal öffnen
Öffnen Sie die `index.html` direkt im Browser oder nutzen Sie einen lokalen Webserver:

```bash
# Python 3
python -m http.server 8000

# Dann im Browser öffnen: http://localhost:8000
```

### Deployment
Die Website besteht aus reinen HTML/CSS/JavaScript-Dateien und kann auf jedem Webserver gehostet werden:
- Alle Dateien auf den Server hochladen
- Keine Server-seitige Konfiguration erforderlich
- SSL/HTTPS wird empfohlen

## 🔧 Anpassungen

### Farben ändern
Bearbeiten Sie die CSS Custom Properties in `css/styles.css`:

```css
:root {
  --color-primary: #dc0000;      /* Hauptfarbe (Rot) */
  --color-primary-dark: #a50000; /* Dunkleres Rot */
  --color-secondary: #000000;    /* Schwarz */
  --color-white: #ffffff;        /* Weiß */
}
```

### Kontaktdaten aktualisieren
Ersetzen Sie Platzhalter-Kontaktdaten in:
- Footer (in allen HTML-Dateien)
- `pages/kontakt.html`
- `pages/impressum.html`

### Bilder hinzufügen
Fügen Sie Bilder im Ordner `assets/images/` hinzu und ersetzen Sie die Platzhalter:
- Hero-Bereiche
- Produkt-Karten
- Über-uns-Sektion

## 📋 To-Do / Geplante Erweiterungen

- [ ] Echte Bilder einfügen (derzeit Platzhalter)
- [ ] Backend für Kontaktformular implementieren
- [ ] Google Maps / OpenStreetMap einbinden
- [ ] Newsletter-Anmeldung
- [ ] Service-Seite mit Preisen ausbauen
- [ ] Neuigkeiten/Blog-System
- [ ] Produkt-Katalog mit Detailseiten
- [ ] Online-Terminbuchung
- [ ] Cookie-Banner (falls externe Services eingebunden werden)

## 🌐 Browser-Unterstützung

- Chrome/Edge (aktuelle Versionen)
- Firefox (aktuelle Versionen)
- Safari (aktuelle Versionen)
- Mobile Browser (iOS Safari, Chrome Mobile)

## 📱 Responsive Breakpoints

- Mobile: < 768px
- Tablet: 768px - 1023px
- Desktop: ≥ 1024px

## 🛠️ Technologien

- HTML5 (Semantisch)
- CSS3 (Custom Properties, Flexbox, Grid)
- Vanilla JavaScript (ES6+)
- Keine externen Dependencies

## 📄 Lizenz & Hinweise

- Impressum und Datenschutz enthalten Platzhalter und müssen mit echten Daten gefüllt werden
- Bilder und Inhalte müssen vor Live-Gang vervollständigt werden
- USt-IdNr. und rechtliche Angaben im Impressum prüfen

## 👤 Kontakt

Bei Fragen zur Website-Entwicklung oder Anpassungen wenden Sie sich an den Website-Administrator.

---

**Stand**: Februar 2026  
**Version**: 1.0.0 (Grundgerüst)
