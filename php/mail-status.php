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
$autoloadOk = is_readable($root . '/vendor/autoload.php');
$configOk = is_readable(__DIR__ . '/mail-config.php');

// Prüfen, ob die PHPMailer-Quellen wirklich vorhanden sind (nicht nur autoload.php)
$phpmailerSrc = $root . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
$phpmailerFileOk = is_readable($phpmailerSrc);
$phpmailerClassOk = false;
if ($autoloadOk) {
    require_once $root . '/vendor/autoload.php';
    $phpmailerClassOk = class_exists(\PHPMailer\PHPMailer\PHPMailer::class);
}

$vendorOk = $autoloadOk && $phpmailerFileOk && $phpmailerClassOk;

$status = [
    'php_version' => PHP_VERSION,
    'php_min_8' => $phpOk,
    'vendor_autoload' => $autoloadOk,
    'phpmailer_files' => $phpmailerFileOk,
    'phpmailer_class' => $phpmailerClassOk,
    'mail_config' => $configOk,
    'ready' => $phpOk && $vendorOk && $configOk,
    'hint' => '',
];

if (!$phpOk) {
    $status['hint'] = 'Im Strato-Kundenbereich PHP 8.0 oder höher für die Domain aktivieren.';
} elseif (!$autoloadOk) {
    $status['hint'] = 'Ordner vendor/ fehlt im Web-Root – per FTP hochladen oder composer install.';
} elseif (!$phpmailerFileOk || !$phpmailerClassOk) {
    $status['hint'] = 'vendor/phpmailer/phpmailer/src/ fehlt oder ist unvollständig – kompletten Ordner vendor/ erneut per FTP hochladen (PHPMailer.php, SMTP.php, Exception.php).';
} elseif (!$configOk) {
    $status['hint'] = 'Datei php/mail-config.php fehlt – aus mail-config.example.php anlegen und SMTP-Daten eintragen.';
} else {
    $status['hint'] = 'Grundvoraussetzungen erfüllt. Formular testen; bei Fehlern Strato-Fehlerprotokoll prüfen.';
}

http_response_code($status['ready'] ? 200 : 503);
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
