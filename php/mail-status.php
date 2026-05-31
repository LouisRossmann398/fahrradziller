<?php

declare(strict_types=1);

/**
 * Einmal-Check nach dem Upload (im Browser aufrufen, danach optional löschen):
 * https://ihre-domain.de/php/mail-status.php
 */
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$root = dirname(__DIR__);
$phpOk = PHP_VERSION_ID >= 80000;
$vendorOk = is_readable($root . '/vendor/autoload.php');
$configOk = is_readable(__DIR__ . '/mail-config.php');

$status = [
    'php_version' => PHP_VERSION,
    'php_min_8' => $phpOk,
    'vendor_autoload' => $vendorOk,
    'mail_config' => $configOk,
    'ready' => $phpOk && $vendorOk && $configOk,
    'hint' => '',
];

if (!$phpOk) {
    $status['hint'] = 'Im Strato-Kundenbereich PHP 8.0 oder höher für die Domain aktivieren.';
} elseif (!$vendorOk) {
    $status['hint'] = 'Ordner vendor/ fehlt im Web-Root – per FTP hochladen oder composer install.';
} elseif (!$configOk) {
    $status['hint'] = 'Datei php/mail-config.php fehlt – aus mail-config.example.php anlegen und SMTP-Daten eintragen.';
} else {
    $status['hint'] = 'Grundvoraussetzungen erfüllt. Formular testen; bei Fehlern Strato-Fehlerprotokoll prüfen.';
}

http_response_code($status['ready'] ? 200 : 503);
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
