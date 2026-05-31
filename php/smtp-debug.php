<?php

declare(strict_types=1);

/**
 * EINMALIGES Debug-Skript für den SMTP-Versand bei Strato.
 *
 * Aufruf im Browser:
 *   https://www.radsport-ziller.com/php/smtp-debug.php?key=ziller-debug-31mai
 *
 * Zeigt den kompletten SMTP-Dialog inkl. Fehlermeldung von Strato.
 * Gibt KEIN Passwort aus. Nach der Analyse diese Datei wieder LÖSCHEN.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('html_errors', '0');
while (ob_get_level() > 0) {
    ob_end_flush();
}

header('Content-Type: text/plain; charset=UTF-8');

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "\n>>> FATALER PHP-FEHLER:\n";
        echo $err['message'] . "\n";
        echo 'in ' . $err['file'] . ':' . $err['line'] . "\n";
    }
});

if (($_GET['key'] ?? '') !== 'ziller-debug-31mai') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

echo "Start Debug.\n";

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
echo 'autoload vorhanden: ' . (is_readable($autoload) ? 'ja' : 'NEIN') . "\n";
require_once $autoload;

echo 'PHPMailer-Klasse: ' . (class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? 'ok' : 'FEHLT') . "\n";

$configPath = __DIR__ . '/mail-config.php';
if (!is_readable($configPath)) {
    echo "FEHLER: mail-config.php nicht lesbar.\n";
    exit;
}
$config = require $configPath;

echo "\n=== Konfiguration (ohne Passwort) ===\n";
echo 'smtp_host:  ' . ($config['smtp_host'] ?? '(fehlt)') . "\n";
echo 'smtp_port:  ' . ($config['smtp_port'] ?? '(fehlt)') . "\n";
echo 'smtp_user:  ' . ($config['smtp_user'] ?? '(fehlt)') . "\n";
echo 'from_email: ' . ($config['from_email'] ?? '(fehlt)') . "\n";
echo 'pass_laenge: ' . strlen((string) ($config['smtp_pass'] ?? '')) . " Zeichen\n";
echo 'openssl geladen: ' . (extension_loaded('openssl') ? 'ja' : 'NEIN') . "\n\n";
flush();

$portsToTry = [
    ['port' => 465, 'secure' => 'ssl'],
    ['port' => 587, 'secure' => 'tls'],
];

foreach ($portsToTry as $variant) {
    echo "================================================\n";
    echo '=== Test Port ' . $variant['port'] . ' (' . $variant['secure'] . ") ===\n";
    echo "================================================\n";
    flush();

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug = 3; // DEBUG_CONNECTION
    $mail->Debugoutput = static function ($str, $level): void {
        echo rtrim((string) $str) . "\n";
        flush();
    };

    try {
        $mail->isSMTP();
        $mail->Host = (string) $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $config['smtp_user'];
        $mail->Password = (string) $config['smtp_pass'];
        $mail->Port = (int) $variant['port'];
        $mail->SMTPSecure = $variant['secure'];
        $mail->SMTPAutoTLS = $variant['port'] !== 465;
        $mail->Hostname = (string) ($config['smtp_hostname'] ?? 'radsport-ziller.com');
        $mail->Timeout = 25;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom((string) $config['from_email'], 'SMTP Debug');
        $mail->addAddress('radsport@radsport-ziller.com');
        $mail->Subject = 'SMTP Debug Test Port ' . $variant['port'];
        $mail->Body = 'Test vom smtp-debug.php (' . date('c') . ').';

        $mail->send();
        echo "\n>>> ERFOLG: Mail ueber Port " . $variant['port'] . " versendet.\n\n";
    } catch (\Throwable $e) {
        echo "\n>>> FEHLGESCHLAGEN Port " . $variant['port'] . "\n";
        echo 'ErrorInfo: ' . $mail->ErrorInfo . "\n";
        echo 'Exception: ' . $e->getMessage() . "\n\n";
    }
    flush();
}

echo "=== Ende ===\n";
echo "WICHTIG: Diese Datei (smtp-debug.php) nach der Analyse wieder loeschen.\n";
