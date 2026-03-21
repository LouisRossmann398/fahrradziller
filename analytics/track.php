<?php
/**
 * Radsport Ziller Analytics - Tracking Endpoint
 * DSGVO-konform: IP-Anonymisierung, keine Cookies
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// JSON-Daten empfangen
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Erforderliche Daten validieren
    $sessionId = $data['sessionId'] ?? null;
    $pageUrl = $data['pageUrl'] ?? null;
    $pageTitle = $data['pageTitle'] ?? '';
    $referrer = $data['referrer'] ?? '';
    $timeOnPage = intval($data['timeOnPage'] ?? 0);
    $eventType = $data['eventType'] ?? 'pageview';
    $eventCategory = $data['eventCategory'] ?? null;
    $eventValue = $data['eventValue'] ?? null;
    
    if (!$sessionId || !$pageUrl) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // IP-Adresse anonymisieren
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $anonymizedIp = anonymizeIp($ip);
    
    // User Agent analysieren
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $deviceType = getDeviceType($userAgent);
    $browser = getBrowser($userAgent);
    
    // Session prüfen/erstellen
    $stmt = $pdo->prepare("
        SELECT id, last_activity 
        FROM analytics_sessions 
        WHERE session_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();
    
    $isNewSession = false;
    $isEntryPage = false;
    
    if (!$session) {
        // Neue Session erstellen
        $isNewSession = true;
        $isEntryPage = true;
        
        $stmt = $pdo->prepare("
            INSERT INTO analytics_sessions 
            (session_id, anonymized_ip, device_type, browser, referrer, entry_page, started_at, last_activity) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $sessionId,
            $anonymizedIp,
            $deviceType,
            $browser,
            $referrer,
            $pageUrl
        ]);
    } else {
        // Session-Timeout prüfen (30 Minuten)
        $lastActivity = strtotime($session['last_activity']);
        $now = time();
        
        if (($now - $lastActivity) > SESSION_TIMEOUT) {
            // Session abgelaufen, neue Entry Page
            $isEntryPage = true;
            
            $stmt = $pdo->prepare("
                UPDATE analytics_sessions 
                SET entry_page = ?, referrer = ?, last_activity = NOW() 
                WHERE session_id = ?
            ");
            $stmt->execute([$pageUrl, $referrer, $sessionId]);
        } else {
            // Session fortsetzen
            $stmt = $pdo->prepare("
                UPDATE analytics_sessions 
                SET total_pageviews = total_pageviews + 1,
                    total_duration = total_duration + ?,
                    exit_page = ?,
                    last_activity = NOW()
                WHERE session_id = ?
            ");
            $stmt->execute([$timeOnPage, $pageUrl, $sessionId]);
        }
    }
    
    // PageView speichern
    if ($eventType === 'pageview') {
        $stmt = $pdo->prepare("
            INSERT INTO analytics_pageviews 
            (session_id, page_url, page_title, referrer, anonymized_ip, device_type, browser, entry_page, time_on_page, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $sessionId,
            $pageUrl,
            $pageTitle,
            $referrer,
            $anonymizedIp,
            $deviceType,
            $browser,
            $isEntryPage ? 1 : 0,
            $timeOnPage
        ]);
    }
    
    // Event speichern (Conversion-Tracking)
    if ($eventType === 'event' && $eventCategory) {
        $stmt = $pdo->prepare("
            INSERT INTO analytics_events 
            (session_id, event_type, event_category, event_value, page_url, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $sessionId,
            'event',
            $eventCategory,
            $eventValue,
            $pageUrl
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'isNewSession' => $isNewSession
    ]);
    
} catch (Exception $e) {
    error_log('Analytics tracking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
