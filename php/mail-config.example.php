<?php

/**
 * Kopieren nach mail-config.php und Werte eintragen.
 * mail-config.php nicht öffentlich teilen (Passwort).
 *
 * Strato: smtp.strato.de
 *   Port 465 = SSL/TLS (oft zuverlässiger)
 *   Port 587 = STARTTLS
 * smtp_user + from_email = dieselbe existierende Mailbox (echtes Postfach, kein reiner Weiterleitungs-Alias).
 * Formular-E-Mails gehen an radsport@ (send-contact.php / send-service.php).
 */

return [
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 465,
    'smtp_hostname' => 'radsport-ziller.com',
    'smtp_user' => 'radsport@radsport-ziller.com',
    'smtp_pass' => 'HIER_MAILBOX_PASSWORT_EINTRAGEN',
    'from_email' => 'radsport@radsport-ziller.com',
    'from_name' => 'Radsport Ziller Website',

    // Nur für mail-smtp-diagnose.php – nach Test wieder entfernen:
    // 'diagnose_key' => 'geheimes-testwort',
];
