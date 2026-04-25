<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Lädt und validiert die SMTP-Konfiguration aus php/mail-config.php.
 *
 * @return array{smtp_host:string,smtp_port:int,smtp_user:string,smtp_pass:string,from_email:string,from_name:string}
 */
function rz_mail_load_config(): array
{
    $path = __DIR__ . '/mail-config.php';
    if (!is_readable($path)) {
        throw new RuntimeException('Mail-Konfiguration fehlt (mail-config.php).');
    }
    /** @var mixed $c */
    $c = require $path;
    if (!is_array($c)) {
        throw new RuntimeException('Mail-Konfiguration ungültig.');
    }
    foreach (['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'from_email', 'from_name'] as $k) {
        if (!isset($c[$k]) || $c[$k] === '' || $c[$k] === null) {
            throw new RuntimeException('Mail-Konfiguration unvollständig: ' . $k);
        }
    }
    return $c;
}

function rz_send_mail(array $config, string $to, string $subject, string $bodyText, ?string $replyTo = null): void
{
    // Zentraler Mailversand: beide Formular-Endpunkte nutzen dieselbe SMTP-Logik.
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = (string) $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = (string) $config['smtp_user'];
    $mail->Password = (string) $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $config['smtp_port'];
    $mail->CharSet = 'UTF-8';
    $mail->setFrom((string) $config['from_email'], (string) $config['from_name']);
    $mail->addAddress($to);
    if ($replyTo !== null && $replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyTo);
    }
    $mail->Subject = $subject;
    $mail->Body = $bodyText;
    $mail->send();
}

function rz_json_response(bool $ok, string $errorMessage = '', int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(
        ['ok' => $ok, 'error' => $ok ? '' : $errorMessage],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function rz_sanitize_line(string $s, int $maxLen = 8000): string
{
    // Basis-Schutz: entfernt HTML und kürzt überlange Eingaben.
    $s = trim(strip_tags($s));
    if (function_exists('mb_strlen') && mb_strlen($s) > $maxLen) {
        return mb_substr($s, 0, $maxLen);
    }
    if (!function_exists('mb_strlen') && strlen($s) > $maxLen) {
        return substr($s, 0, $maxLen);
    }
    return $s;
}
