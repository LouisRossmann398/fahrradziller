# Hinweise-Ordner (Startseite)

## Anleitung für Mitarbeiter

In diesem Ordner liegt immer die Datei **`aktueller-hinweis.docx`**.  
Der Text darin erscheint automatisch auf der **Startseite** – z. B. wenn der Laden an einem Samstag geschlossen ist.

### Wichtig

- **Dateiname:** Immer `aktueller-hinweis.docx` – nicht umbenennen, nicht löschen
- **Format:** Nur `.docx` (Word ab 2007)
- **Inhalt:** Kurzer, klarer Text. Einfache Absätze reichen – keine Bilder oder Tabellen nötig
- **Leer = aus:** Wenn die Datei leer ist (kein Text), wird auf der Webseite **nichts** Extra angezeigt
- **Mit Text = an:** Sobald etwas im Word steht, erscheint der Hinweis auf Desktop und Mobil

### Hinweis eintragen

1. `aktueller-hinweis.docx` herunterladen (oder lokal öffnen)
2. Text eintippen, z. B.:

   ```text
   Am Samstag, 28. März, bleibt unser Geschäft geschlossen.

   Ab Montag sind wir wieder zu den gewohnten Öffnungszeiten für Sie da.
   ```

3. Speichern
4. Per FTP nach `/hinweise/` hochladen und die alte Datei **überschreiben**
5. Startseite im Browser neu laden (ggf. Cache leeren)

### Hinweis wieder entfernen

**Empfohlen:**

1. `aktueller-hinweis.docx` öffnen
2. **Allen Text löschen**, Datei speichern (Datei selbst behalten)
3. Wieder nach `/hinweise/` hochladen und überschreiben

**Falls die Datei gelöscht wird:** Auch dann erscheint auf der Startseite kein Hinweis – die Seite läuft ganz normal weiter.  
Bitte die Datei möglichst **nicht dauerhaft löschen**, sondern leer lassen und wieder hochladen, damit der nächste Hinweis einfach eingetragen werden kann.

### Via Strato FTP

1. In Strato anmelden
2. FTP-Zugang öffnen (z. B. FileZilla / Cyberduck)
3. Zum Ordner `/hinweise/` navigieren
4. `aktueller-hinweis.docx` ersetzen (nicht löschen)

### Tipps

- Dateiname exakt so belassen – sonst wird nichts angezeigt
- Nach dem Hochladen kann es auf dem Handy helfen, die Seite im privaten Tab zu öffnen
- Der Text erscheint oberhalb von „Familienbetrieb mit Tradition“
