<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/mail-common.php';

// Erwartet JSON-POST mit:
// name, email, phone (optional), subject (optional), message, privacy, website (Honeypot).
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    rz_json_response(false, 'Nicht erlaubt.', 405);
}

try {
    $raw = (string) file_get_contents('php://input');
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    rz_json_response(false, 'Ungültige Anfrage.', 400);
}

if (!is_array($data)) {
    rz_json_response(false, 'Ungültige Anfrage.', 400);
}

// Honeypot (Spam-Bots)
if (!empty($data['website'] ?? null)) {
    // Für Bots absichtlich "ok", damit kein Feedback für Angreifer entsteht.
    rz_json_response(true);
}

$name = rz_sanitize_line((string) ($data['name'] ?? ''), 200);
$email = trim((string) ($data['email'] ?? ''));
$phone = rz_sanitize_line((string) ($data['phone'] ?? ''), 80);
$subject = rz_sanitize_line((string) ($data['subject'] ?? ''), 200);
$message = rz_sanitize_line((string) ($data['message'] ?? ''), 8000);
$privacy = !empty($data['privacy']);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '' || !$privacy) {
    rz_json_response(false, 'Bitte füllen Sie alle Pflichtfelder korrekt aus.', 422);
}

try {
    $config = rz_mail_load_config();
} catch (Throwable $e) {
    error_log('send-contact config: ' . $e->getMessage());
    rz_json_response(false, 'Der Versand ist momentan nicht möglich. Bitte versuchen Sie es später oder rufen Sie uns an.', 500);
}

$mailSubject = $subject !== '' ? '[Kontakt Website] ' . $subject : '[Kontakt Website] Neue Nachricht';

$body = "Neue Nachricht über das Kontaktformular auf der Website:\r\n\r\n";
$body .= "Name: {$name}\r\n";
$body .= "E-Mail: {$email}\r\n";
if ($phone !== '') {
    $body .= "Telefon: {$phone}\r\n";
}
$body .= "\r\nNachricht:\r\n{$message}\r\n";

try {
    rz_send_mail($config, 'info@radsport-ziller.com', $mailSubject, $body, $email);
} catch (Throwable $e) {
    error_log('send-contact mail: ' . $e->getMessage());
    rz_json_response(false, 'E-Mail konnte nicht gesendet werden. Bitte später erneut versuchen.', 500);
}

rz_json_response(true);
