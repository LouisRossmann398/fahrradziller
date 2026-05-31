<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Lädt und validiert die SMTP-Konfiguration aus php/mail-config.php.
 *
 * @return array<string, mixed>
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

    // Unsichtbare Zeichen / Zeilenumbrüche aus Copy-Paste entfernen
    $c['smtp_user'] = trim((string) $c['smtp_user']);
    $c['smtp_pass'] = trim((string) $c['smtp_pass']);
    $c['from_email'] = trim((string) $c['from_email']);

    if (str_contains((string) $c['smtp_pass'], 'HIER_MAILBOX_PASSWORT')) {
        throw new RuntimeException('mail-config.php: Platzhalter-Passwort noch nicht ersetzt.');
    }

    return $c;
}

/**
 * PHPMailer für Strato SMTP konfigurieren.
 */
function rz_phpmailer_configure(PHPMailer $mail, array $config): void
{
    $mail->isSMTP();
    $mail->Host = (string) $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->AuthType = 'LOGIN';
    $mail->Username = (string) $config['smtp_user'];
    $mail->Password = (string) $config['smtp_pass'];

    $port = (int) $config['smtp_port'];
    $mail->Port = $port;
    $mail->Hostname = (string) ($config['smtp_hostname'] ?? 'radsport-ziller.com');
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->SMTPTimeout = 20;

    if ($port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAutoTLS = false;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAutoTLS = true;
    }

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ];
}

function rz_send_mail(array $config, string $to, string $subject, string $bodyText, ?string $replyTo = null): void
{
    $mail = new PHPMailer(true);
    rz_phpmailer_configure($mail, $config);

    $mail->setFrom((string) $config['from_email'], (string) $config['from_name']);
    $mail->addAddress($to);
    if ($replyTo !== null && $replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyTo);
    }
    $mail->Subject = $subject;
    $mail->Body = $bodyText;

    try {
        $mail->send();
    } catch (MailerException $e) {
        error_log('rz_send_mail PHPMailer: ' . $mail->ErrorInfo);
        throw new RuntimeException($mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage(), 0, $e);
    }
}

/**
 * Nur SMTP-Verbindung testen (ohne E-Mail zu senden).
 */
function rz_smtp_connect_test(array $config): array
{
    $mail = new PHPMailer(true);
    rz_phpmailer_configure($mail, $config);

    $ok = $mail->smtpConnect();
    $info = $mail->ErrorInfo;
    if ($ok) {
        $mail->smtpClose();
    }

    return ['ok' => $ok, 'error' => $info];
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
    $s = trim(strip_tags($s));
    if (function_exists('mb_strlen') && mb_strlen($s) > $maxLen) {
        return mb_substr($s, 0, $maxLen);
    }
    if (!function_exists('mb_strlen') && strlen($s) > $maxLen) {
        return substr($s, 0, $maxLen);
    }
    return $s;
}
