<?php
/**
 * Radsport Ziller Analytics - Admin Dashboard
 * Login erforderlich
 */

session_start();
require_once '../config.php';

// Login-Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['analytics_admin'] = true;
        $_SESSION['admin_login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Ungültige Anmeldedaten';
    }
}

// Logout-Handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Login-Prüfung
if (!isset($_SESSION['analytics_admin']) || !$_SESSION['analytics_admin']) {
    // Login-Formular anzeigen
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Analytics Login - Radsport Ziller</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #dc0000 0%, #a50000 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                width: 100%;
                max-width: 400px;
            }
            .login-box h1 {
                margin-bottom: 10px;
                color: #2c2c2c;
                font-size: 24px;
            }
            .login-box p {
                color: #666;
                margin-bottom: 30px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                color: #2c2c2c;
                font-weight: 500;
            }
            input[type="text"],
            input[type="password"] {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 6px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input[type="text"]:focus,
            input[type="password"]:focus {
                outline: none;
                border-color: #dc0000;
            }
            button {
                width: 100%;
                padding: 14px;
                background: #dc0000;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #a50000;
            }
            .error {
                background: #fee;
                color: #c00;
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 20px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔒 Analytics Dashboard</h1>
            <p>Radsport Ziller</p>
            
            <?php if (isset($loginError)): ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit" name="login">Anmelden</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Session-Timeout prüfen (4 Stunden)
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > 14400) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Admin ist eingeloggt - Dashboard anzeigen
$pdo = getDbConnection();
if (!$pdo) {
    die('Datenbankverbindung fehlgeschlagen. Bitte config.php prüfen.');
}

// Zeitraum-Filter
$period = $_GET['period'] ?? '7days';
$customStart = $_GET['start'] ?? null;
$customEnd = $_GET['end'] ?? null;

// Zeitraum berechnen
switch ($period) {
    case 'today':
        $startDate = date('Y-m-d 00:00:00');
        $endDate = date('Y-m-d 23:59:59');
        break;
    case '7days':
        $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $endDate = date('Y-m-d 23:59:59');
        break;
    case '30days':
        $startDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $endDate = date('Y-m-d 23:59:59');
        break;
    case '90days':
        $startDate = date('Y-m-d 00:00:00', strtotime('-90 days'));
        $endDate = date('Y-m-d 23:59:59');
        break;
    case 'custom':
        $startDate = $customStart ? date('Y-m-d 00:00:00', strtotime($customStart)) : date('Y-m-d 00:00:00', strtotime('-30 days'));
        $endDate = $customEnd ? date('Y-m-d 23:59:59', strtotime($customEnd)) : date('Y-m-d 23:59:59');
        break;
    default:
        $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $endDate = date('Y-m-d 23:59:59');
}

// Weiterer Dashboard-Code folgt in Teil 2...
include 'dashboard.php';
?>
