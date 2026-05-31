<?php

/**
 * Kopieren nach mail-config.php und Werte eintragen.
 * mail-config.php nicht öffentlich teilen (Passwort).
 *
 * Strato: smtp.strato.de, Port 587, STARTTLS.
 * smtp_user + from_email = dieselbe existierende Mailbox (z. B. info@ oder radsport@).
 * Formular-E-Mails gehen an radsport@ (siehe send-contact.php / send-service.php).
 */

return [
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 587,
    'smtp_user' => 'info@radsport-ziller.com',
    'smtp_pass' => 'HIER_MAILBOX_PASSWORT_EINTRAGEN',
    'from_email' => 'info@radsport-ziller.com',
    'from_name' => 'Radsport Ziller Website',
];
