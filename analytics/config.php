<?php
/**
 * Radsport Ziller Analytics - Konfiguration
 * DSGVO-konform, keine Cookies, IP-Anonymisierung
 */

// Datenbank-Konfiguration (für Strato anpassen)
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name'); // Bei Strato anpassen
define('DB_USER', 'your_database_user'); // Bei Strato anpassen
define('DB_PASS', 'your_database_password'); // Bei Strato anpassen
define('DB_CHARSET', 'utf8mb4');

// Admin-Login (UNBEDINGT ÄNDERN!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('ChangeMeNow123!', PASSWORD_DEFAULT)); // UNBEDINGT ÄNDERN!

// Session-Dauer (wie lange ist eine Session aktiv ohne neue Aktivität?)
define('SESSION_TIMEOUT', 1800); // 30 Minuten

// IP-Anonymisierung aktiviert (DSGVO-Pflicht)
define('ANONYMIZE_IP', true);

// Daten-Retention (wie lange Daten speichern?)
define('DATA_RETENTION_DAYS', 365); // 1 Jahr

// Timezone
date_default_timezone_set('Europe/Berlin');

// Datenbank-Verbindung
function getDbConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}

// IP-Anonymisierung (DSGVO-konform)
function anonymizeIp($ip) {
    if (!ANONYMIZE_IP) {
        return $ip;
    }
    
    // IPv4
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        $parts[3] = '0'; // Letztes Oktett auf 0 setzen
        return implode('.', $parts);
    }
    
    // IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        // Letzte 4 Segmente auf 0 setzen
        for ($i = count($parts) - 4; $i < count($parts); $i++) {
            if (isset($parts[$i])) {
                $parts[$i] = '0';
            }
        }
        return implode(':', $parts);
    }
    
    return 'unknown';
}

// User Agent parsen (Device Type)
function getDeviceType($userAgent) {
    if (preg_match('/mobile|android|iphone|ipad|ipod/i', $userAgent)) {
        return 'mobile';
    } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
        return 'tablet';
    }
    return 'desktop';
}

// Browser erkennen
function getBrowser($userAgent) {
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
    return 'Other';
}
?>
