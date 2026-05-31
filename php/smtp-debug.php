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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: text/plain; charset=UTF-8');

if (($_GET['key'] ?? '') !== 'ziller-debug-31mai') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$configPath = __DIR__ . '/mail-config.php';
if (!is_readable($configPath)) {
    echo "FEHLER: mail-config.php nicht lesbar.\n";
    exit;
}
$config = require $configPath;

echo "=== Konfiguration (ohne Passwort) ===\n";
echo 'smtp_host:  ' . ($config['smtp_host'] ?? '(fehlt)') . "\n";
echo 'smtp_port:  ' . ($config['smtp_port'] ?? '(fehlt)') . "\n";
echo 'smtp_user:  ' . ($config['smtp_user'] ?? '(fehlt)') . "\n";
echo 'from_email: ' . ($config['from_email'] ?? '(fehlt)') . "\n";
echo 'pass_laenge: ' . strlen((string) ($config['smtp_pass'] ?? '')) . " Zeichen\n";
echo 'openssl geladen: ' . (extension_loaded('openssl') ? 'ja' : 'NEIN') . "\n\n";

$portsToTry = [
    ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS],
    ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS],
];

foreach ($portsToTry as $variant) {
    echo "================================================\n";
    echo '=== Test Port ' . $variant['port'] . ' (' . $variant['secure'] . ") ===\n";
    echo "================================================\n";

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    $mail->Debugoutput = static function (string $str, int $level): void {
        echo rtrim($str) . "\n";
    };

    try {
        $mail->isSMTP();
        $mail->Host = (string) $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $config['smtp_user'];
        $mail->Password = (string) $config['smtp_pass'];
        $mail->Port = $variant['port'];
        $mail->SMTPSecure = $variant['secure'];
        $mail->SMTPAutoTLS = $variant['port'] !== 465;
        $mail->Hostname = (string) ($config['smtp_hostname'] ?? 'radsport-ziller.com');
        $mail->Timeout = 20;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom((string) $config['from_email'], 'SMTP Debug');
        $mail->addAddress('radsport@radsport-ziller.com');
        $mail->Subject = 'SMTP Debug Test Port ' . $variant['port'];
        $mail->Body = 'Test vom smtp-debug.php (' . date('c') . ').';

        $mail->send();
        echo "\n>>> ERFOLG: Mail über Port " . $variant['port'] . " versendet.\n\n";
    } catch (Throwable $e) {
        echo "\n>>> FEHLGESCHLAGEN Port " . $variant['port'] . "\n";
        echo 'ErrorInfo: ' . $mail->ErrorInfo . "\n";
        echo 'Exception: ' . $e->getMessage() . "\n\n";
    }
}

echo "=== Ende ===\n";
echo "WICHTIG: Diese Datei (smtp-debug.php) nach der Analyse wieder loeschen.\n";
