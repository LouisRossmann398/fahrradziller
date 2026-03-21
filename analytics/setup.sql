-- Radsport Ziller Analytics - Datenbank Setup
-- DSGVO-konform mit IP-Anonymisierung

-- Tabelle für Page Views
CREATE TABLE IF NOT EXISTS analytics_pageviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(200),
    referrer VARCHAR(500),
    anonymized_ip VARCHAR(45),
    device_type ENUM('desktop', 'tablet', 'mobile') NOT NULL,
    browser VARCHAR(50),
    entry_page BOOLEAN DEFAULT 0,
    exit_page BOOLEAN DEFAULT 0,
    time_on_page INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_page (page_url(255)),
    INDEX idx_date (created_at),
    INDEX idx_device (device_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelle für Sessions
CREATE TABLE IF NOT EXISTS analytics_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) UNIQUE NOT NULL,
    anonymized_ip VARCHAR(45),
    device_type ENUM('desktop', 'tablet', 'mobile') NOT NULL,
    browser VARCHAR(50),
    referrer VARCHAR(500),
    entry_page VARCHAR(500),
    exit_page VARCHAR(500),
    total_pageviews INT DEFAULT 1,
    total_duration INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_date (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelle für Conversion-Events (Kontaktformular, Service-Anfragen, etc.)
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_category VARCHAR(100),
    event_value VARCHAR(500),
    page_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_type (event_type),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hinweis für automatische Datenbereinigung
-- Cron-Job einrichten für: DELETE FROM analytics_pageviews WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
-- Cron-Job einrichten für: DELETE FROM analytics_sessions WHERE started_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
-- Cron-Job einrichten für: DELETE FROM analytics_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
