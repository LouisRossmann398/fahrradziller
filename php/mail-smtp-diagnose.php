<?php

declare(strict_types=1);

/**
 * SMTP-Fehler sichtbar machen (nur mit diagnose_key in mail-config.php).
 *
 * In mail-config.php ergänzen:
 *   'diagnose_key' => 'ein-geheimes-wort',
 *
 * Dann aufrufen:
 *   https://www.radsport-ziller.com/php/mail-smtp-diagnose.php?key=ein-geheimes-wort
 *
 * Nach dem Test: diagnose_key entfernen oder Datei löschen.
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/mail-common.php';

header('Content-Type: application/json; charset=UTF-8');

$key = (string) ($_GET['key'] ?? '');

try {
    $config = rz_mail_load_config();
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$expected = (string) ($config['diagnose_key'] ?? '');
if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
    exit;
}

$results = [
    'smtp_user' => $config['smtp_user'],
    'smtp_port' => (int) $config['smtp_port'],
    'smtp_host' => $config['smtp_host'],
    'from_email' => $config['from_email'],
    'openssl' => extension_loaded('openssl'),
    'tests' => [],
];

foreach ([465, 587] as $port) {
    $testConfig = $config;
    $testConfig['smtp_port'] = $port;
    $connect = rz_smtp_connect_test($testConfig);
    $results['tests'][(string) $port] = $connect;
}

$anyOk = false;
foreach ($results['tests'] as $t) {
    if (!empty($t['ok'])) {
        $anyOk = true;
        break;
    }
}

$results['ok'] = $anyOk;
$results['hint'] = $anyOk
    ? 'Mindestens ein Port funktioniert – diesen Port in mail-config.php eintragen und Formular testen.'
    : 'SMTP-Login schlägt fehl. Prüfen: echtes Postfach (kein reiner Alias), Webmail-Login, ggf. info@ für smtp_user nutzen.';

http_response_code($anyOk ? 200 : 503);
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
