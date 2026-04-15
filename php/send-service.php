<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/mail-common.php';

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

if (!empty($data['website'] ?? null)) {
    rz_json_response(true);
}

$appointmentDate = rz_sanitize_line((string) ($data['appointmentDate'] ?? ''), 32);
$appointmentTime = rz_sanitize_line((string) ($data['appointmentTime'] ?? ''), 16);
$name = rz_sanitize_line((string) ($data['serviceName'] ?? ''), 200);
$email = trim((string) ($data['serviceEmail'] ?? ''));
$phone = rz_sanitize_line((string) ($data['servicePhone'] ?? ''), 80);
$msg = rz_sanitize_line((string) ($data['serviceMessage'] ?? ''), 8000);
$privacy = !empty($data['privacy']);

if ($appointmentDate === '' || $appointmentTime === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$privacy) {
    rz_json_response(false, 'Bitte füllen Sie alle Pflichtfelder korrekt aus.', 422);
}

try {
    $config = rz_mail_load_config();
} catch (Throwable $e) {
    error_log('send-service config: ' . $e->getMessage());
    rz_json_response(false, 'Der Versand ist momentan nicht möglich. Bitte versuchen Sie es später oder rufen Sie uns an.', 500);
}

$mailSubject = '[Service/Reparatur Website] Terminanfrage von ' . $name;

$body = "Neue Terminanfrage (Service / Reparatur):\r\n\r\n";
$body .= "Wunschtermin Datum: {$appointmentDate}\r\n";
$body .= "Wunschtermin Uhrzeit: {$appointmentTime} Uhr\r\n\r\n";
$body .= "Name: {$name}\r\n";
$body .= "E-Mail: {$email}\r\n";
if ($phone !== '') {
    $body .= "Telefon: {$phone}\r\n";
}
if ($msg !== '') {
    $body .= "\r\nAnliegen:\r\n{$msg}\r\n";
}

try {
    rz_send_mail($config, 'radsport@radsport-ziller.com', $mailSubject, $body, $email);
} catch (Throwable $e) {
    error_log('send-service mail: ' . $e->getMessage());
    rz_json_response(false, 'E-Mail konnte nicht gesendet werden. Bitte später erneut versuchen.', 500);
}

rz_json_response(true);
