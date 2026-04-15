<?php

/**
 * Kopieren nach mail-config.php und Werte eintragen.
 * mail-config.php nicht öffentlich teilen (Passwort).
 *
 * Strato: Postausgang smtp.strato.de, Port 587, STARTTLS.
 * SMTP-Benutzer = vollständige E-Mail-Adresse der Mailbox, mit der Sie sich authentifizieren.
 */

return [
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 587,
    'smtp_user' => 'info@radsport-ziller.com',
    'smtp_pass' => 'HIER_MAILBOX_PASSWORT_EINTRAGEN',
    'from_email' => 'info@radsport-ziller.com',
    'from_name' => 'Radsport Ziller Website',
];
