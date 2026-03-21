<?php
/**
 * Dashboard Content - Analytics Statistiken
 */

// Gesamtstatistiken abrufen
$stats = [];

// 1. Gesamte Seitenaufrufe
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats['totalPageviews'] = $stmt->fetch()['total'];

// 2. Unique Sessions
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT session_id) as total
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats['uniqueVisitors'] = $stmt->fetch()['total'];

// 3. Durchschnittliche Verweildauer
$stmt = $pdo->prepare("
    SELECT AVG(total_duration) as avg_duration
    FROM analytics_sessions
    WHERE started_at BETWEEN ? AND ?
    AND total_duration > 0
");
$stmt->execute([$startDate, $endDate]);
$stats['avgDuration'] = round($stmt->fetch()['avg_duration'] ?? 0);

// 4. Durchschnittliche Seiten pro Session
$stats['avgPagesPerSession'] = $stats['uniqueVisitors'] > 0 
    ? round($stats['totalPageviews'] / $stats['uniqueVisitors'], 1) 
    : 0;

// 5. Beliebte Seiten
$stmt = $pdo->prepare("
    SELECT 
        page_url,
        page_title,
        COUNT(*) as views,
        AVG(time_on_page) as avg_time
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY page_url, page_title
    ORDER BY views DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$popularPages = $stmt->fetchAll();

// 6. Device-Verteilung
$stmt = $pdo->prepare("
    SELECT 
        device_type,
        COUNT(*) as count
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY device_type
");
$stmt->execute([$startDate, $endDate]);
$devices = $stmt->fetchAll();

// 7. Browser-Verteilung
$stmt = $pdo->prepare("
    SELECT 
        browser,
        COUNT(*) as count
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY browser
    ORDER BY count DESC
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$browsers = $stmt->fetchAll();

// 8. Top Referrer
$stmt = $pdo->prepare("
    SELECT 
        referrer,
        COUNT(*) as count
    FROM analytics_sessions
    WHERE started_at BETWEEN ? AND ?
    AND referrer != 'direct'
    AND referrer != ''
    GROUP BY referrer
    ORDER BY count DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$referrers = $stmt->fetchAll();

// 9. Pageviews pro Tag (für Chart)
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as views
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$startDate, $endDate]);
$dailyViews = $stmt->fetchAll();

// 10. Conversion-Events
$stmt = $pdo->prepare("
    SELECT 
        event_category,
        COUNT(*) as count
    FROM analytics_events
    WHERE created_at BETWEEN ? AND ?
    GROUP BY event_category
    ORDER BY count DESC
");
$stmt->execute([$startDate, $endDate]);
$events = $stmt->fetchAll();

// 11. Top Entry Pages
$stmt = $pdo->prepare("
    SELECT 
        entry_page,
        COUNT(*) as count
    FROM analytics_sessions
    WHERE started_at BETWEEN ? AND ?
    AND entry_page IS NOT NULL
    GROUP BY entry_page
    ORDER BY count DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$entryPages = $stmt->fetchAll();

// 12. Stunden-Verteilung (Traffic nach Tageszeit)
$stmt = $pdo->prepare("
    SELECT 
        HOUR(created_at) as hour,
        COUNT(*) as count
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
");
$stmt->execute([$startDate, $endDate]);
$hourlyTraffic = $stmt->fetchAll();

// 13. Wochentag-Verteilung
$stmt = $pdo->prepare("
    SELECT 
        DAYOFWEEK(created_at) as day,
        COUNT(*) as count
    FROM analytics_pageviews
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DAYOFWEEK(created_at)
    ORDER BY day ASC
");
$stmt->execute([$startDate, $endDate]);
$weekdayTraffic = $stmt->fetchAll();

// Wochentag-Namen
$dayNames = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Radsport Ziller</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #2c2c2c;
            line-height: 1.6;
        }
        
        .header {
            background: #dc0000;
            color: white;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .period-selector {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .period-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .period-btn {
            padding: 10px 20px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #2c2c2c;
            font-size: 14px;
            font-weight: 500;
        }
        
        .period-btn:hover {
            background: #e8e8e8;
        }
        
        .period-btn.active {
            background: #dc0000;
            color: white;
            border-color: #dc0000;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #2c2c2c;
        }
        
        .stat-unit {
            font-size: 14px;
            color: #999;
            margin-left: 4px;
        }
        
        .chart-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .chart-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c2c2c;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            text-align: left;
            padding: 12px;
            background: #f5f5f5;
            font-weight: 600;
            font-size: 14px;
            color: #2c2c2c;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .table tr:hover {
            background: #fafafa;
        }
        
        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .bar-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .bar-label {
            min-width: 120px;
            font-size: 14px;
            color: #666;
        }
        
        .bar-track {
            flex: 1;
            height: 24px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc0000, #ff4444);
            transition: width 0.5s ease;
        }
        
        .bar-value {
            min-width: 60px;
            text-align: right;
            font-size: 14px;
            font-weight: 600;
            color: #2c2c2c;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .period-buttons {
                flex-direction: column;
            }
            
            .period-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📊 Analytics Dashboard - Radsport Ziller</h1>
            <a href="?logout" class="logout-btn">Abmelden</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Zeitraum-Auswahl -->
        <div class="period-selector">
            <div class="period-buttons">
                <a href="?period=today" class="period-btn <?php echo $period === 'today' ? 'active' : ''; ?>">Heute</a>
                <a href="?period=7days" class="period-btn <?php echo $period === '7days' ? 'active' : ''; ?>">Letzte 7 Tage</a>
                <a href="?period=30days" class="period-btn <?php echo $period === '30days' ? 'active' : ''; ?>">Letzte 30 Tage</a>
                <a href="?period=90days" class="period-btn <?php echo $period === '90days' ? 'active' : ''; ?>">Letzte 90 Tage</a>
            </div>
        </div>
        
        <!-- Haupt-Statistiken -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Seitenaufrufe</div>
                <div class="stat-value"><?php echo number_format($stats['totalPageviews'], 0, ',', '.'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Besucher</div>
                <div class="stat-value"><?php echo number_format($stats['uniqueVisitors'], 0, ',', '.'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Ø Verweildauer</div>
                <div class="stat-value">
                    <?php 
                    $minutes = floor($stats['avgDuration'] / 60);
                    $seconds = $stats['avgDuration'] % 60;
                    echo $minutes . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);
                    ?>
                    <span class="stat-unit">min</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Ø Seiten/Besuch</div>
                <div class="stat-value"><?php echo $stats['avgPagesPerSession']; ?></div>
            </div>
        </div>
        
        <!-- Beliebte Seiten -->
        <div class="chart-section">
            <h2>📄 Beliebte Seiten</h2>
            <?php if (!empty($popularPages)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Seite</th>
                            <th>Aufrufe</th>
                            <th>Ø Zeit auf Seite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularPages as $page): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($page['page_title'] ?: $page['page_url']); ?></strong><br>
                                    <small style="color: #999;"><?php echo htmlspecialchars($page['page_url']); ?></small>
                                </td>
                                <td><?php echo number_format($page['views'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    $time = round($page['avg_time']);
                                    echo floor($time / 60) . ':' . str_pad($time % 60, 2, '0', STR_PAD_LEFT) . ' min';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">Keine Daten verfügbar</div>
            <?php endif; ?>
        </div>
        
        <!-- Device & Browser -->
        <div class="grid-2">
            <div class="chart-section">
                <h2>📱 Geräte-Typ</h2>
                <?php if (!empty($devices)): 
                    $maxDevice = max(array_column($devices, 'count'));
                    $deviceLabels = ['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobil'];
                ?>
                    <div class="bar-chart">
                        <?php foreach ($devices as $device): ?>
                            <div class="bar-item">
                                <div class="bar-label"><?php echo $deviceLabels[$device['device_type']] ?? $device['device_type']; ?></div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: <?php echo ($device['count'] / $maxDevice * 100); ?>%;"></div>
                                </div>
                                <div class="bar-value"><?php echo number_format($device['count'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">Keine Daten verfügbar</div>
                <?php endif; ?>
            </div>
            
            <div class="chart-section">
                <h2>🌐 Browser</h2>
                <?php if (!empty($browsers)): 
                    $maxBrowser = max(array_column($browsers, 'count'));
                ?>
                    <div class="bar-chart">
                        <?php foreach ($browsers as $browser): ?>
                            <div class="bar-item">
                                <div class="bar-label"><?php echo htmlspecialchars($browser['browser']); ?></div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: <?php echo ($browser['count'] / $maxBrowser * 100); ?>%;"></div>
                                </div>
                                <div class="bar-value"><?php echo number_format($browser['count'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">Keine Daten verfügbar</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Referrer -->
        <?php if (!empty($referrers)): ?>
            <div class="chart-section">
                <h2>🔗 Top Referrer (Traffic-Quellen)</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Quelle</th>
                            <th>Besucher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrers as $referrer): 
                            $url = parse_url($referrer['referrer']);
                            $domain = $url['host'] ?? $referrer['referrer'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($domain); ?></td>
                                <td><?php echo number_format($referrer['count'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Conversion-Events -->
        <?php if (!empty($events)): ?>
            <div class="chart-section">
                <h2>✅ Conversions & Interaktionen</h2>
                <?php
                $eventLabels = [
                    'form_submission_contact' => '📧 Kontaktformular',
                    'form_submission_service' => '🔧 Service-Anfrage',
                    'phone_click' => '📞 Telefon-Klick',
                    'email_click' => '✉️ E-Mail-Klick',
                    'leasing_calculator' => '💰 Leasing-Rechner',
                    'download_pdf' => '📄 PDF-Download'
                ];
                $maxEvent = max(array_column($events, 'count'));
                ?>
                <div class="bar-chart">
                    <?php foreach ($events as $event): 
                        $label = $eventLabels[$event['event_category']] ?? $event['event_category'];
                    ?>
                        <div class="bar-item">
                            <div class="bar-label"><?php echo htmlspecialchars($label); ?></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo ($event['count'] / $maxEvent * 100); ?>%;"></div>
                            </div>
                            <div class="bar-value"><?php echo number_format($event['count'], 0, ',', '.'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Zeitraum-Info -->
        <div class="chart-section">
            <p style="text-align: center; color: #999; font-size: 14px;">
                Zeitraum: <?php echo date('d.m.Y', strtotime($startDate)); ?> - <?php echo date('d.m.Y', strtotime($endDate)); ?>
                <br>
                <small>Alle Daten sind DSGVO-konform anonymisiert (IP-Adressen gekürzt, keine Cookies)</small>
            </p>
        </div>
    </div>
</body>
</html>
