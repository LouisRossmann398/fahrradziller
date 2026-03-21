/**
 * Radsport Ziller Analytics
 * DSGVO-konform: Keine Cookies, IP-Anonymisierung, Datenminimierung
 * Version: 1.0
 */

(function() {
  'use strict';
  
  // Konfiguration
  const config = {
    endpoint: '/analytics/track.php', // Für Strato anpassen wenn nötig
    sessionTimeout: 30 * 60 * 1000, // 30 Minuten
    heartbeatInterval: 15000 // Alle 15 Sekunden Verweildauer updaten
  };
  
  // Session ID generieren (ohne Cookies, nur SessionStorage)
  function getSessionId() {
    let sessionId = sessionStorage.getItem('rzAnalyticsSession');
    
    if (!sessionId) {
      sessionId = 'rz_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      sessionStorage.setItem('rzAnalyticsSession', sessionId);
      sessionStorage.setItem('rzAnalyticsStart', Date.now().toString());
    }
    
    // Session-Timeout prüfen
    const sessionStart = parseInt(sessionStorage.getItem('rzAnalyticsStart') || '0');
    const now = Date.now();
    
    if ((now - sessionStart) > config.sessionTimeout) {
      // Session abgelaufen, neue erstellen
      sessionId = 'rz_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      sessionStorage.setItem('rzAnalyticsSession', sessionId);
      sessionStorage.setItem('rzAnalyticsStart', Date.now().toString());
    }
    
    return sessionId;
  }
  
  // Tracking-Daten sammeln
  function collectData() {
    return {
      sessionId: getSessionId(),
      pageUrl: window.location.pathname,
      pageTitle: document.title,
      referrer: document.referrer || 'direct',
      timeOnPage: 0,
      eventType: 'pageview'
    };
  }
  
  // Daten senden
  function sendTracking(data) {
    if (navigator.sendBeacon) {
      // sendBeacon für bessere Performance
      const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
      navigator.sendBeacon(config.endpoint, blob);
    } else {
      // Fallback: fetch
      fetch(config.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        keepalive: true
      }).catch(function(error) {
        console.warn('Analytics tracking failed:', error);
      });
    }
  }
  
  // Event tracken
  function trackEvent(category, value) {
    const data = collectData();
    data.eventType = 'event';
    data.eventCategory = category;
    data.eventValue = value || null;
    sendTracking(data);
  }
  
  // Verweildauer tracken
  let pageLoadTime = Date.now();
  let lastHeartbeat = Date.now();
  
  function getTimeOnPage() {
    return Math.floor((Date.now() - pageLoadTime) / 1000);
  }
  
  // Heartbeat für Verweildauer
  function startHeartbeat() {
    setInterval(function() {
      const data = collectData();
      data.timeOnPage = getTimeOnPage();
      sendTracking(data);
      lastHeartbeat = Date.now();
    }, config.heartbeatInterval);
  }
  
  // Page Unload Tracking
  function trackPageUnload() {
    const data = collectData();
    data.timeOnPage = getTimeOnPage();
    sendTracking(data);
  }
  
  // Conversion-Tracking für Fahrradladen
  function setupConversionTracking() {
    // Kontaktformular-Submit tracken
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
      contactForm.addEventListener('submit', function() {
        trackEvent('form_submission', 'contact_form');
      });
    }
    
    // Service-Terminbuchung tracken
    const serviceForm = document.getElementById('serviceAppointmentForm');
    if (serviceForm) {
      serviceForm.addEventListener('submit', function() {
        trackEvent('form_submission', 'service_appointment');
      });
    }
    
    // Telefon-Klicks tracken
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        trackEvent('interaction', 'phone_click');
      });
    });
    
    // E-Mail-Klicks tracken
    const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
    emailLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        trackEvent('interaction', 'email_click');
      });
    });
    
    // Leasing-Rechner Interaktion
    const leasingCalculator = document.querySelector('.jobrad-calculator-wrapper');
    if (leasingCalculator) {
      leasingCalculator.addEventListener('click', function() {
        trackEvent('interaction', 'leasing_calculator');
      }, { once: true });
    }
    
    // PDF-Download (Angebote)
    const pdfLinks = document.querySelectorAll('a[href$=".pdf"]');
    pdfLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        trackEvent('download', 'pdf_offers');
      });
    });
  }
  
  // Initialisierung
  function init() {
    // Initial Pageview
    const data = collectData();
    sendTracking(data);
    
    // Heartbeat starten
    startHeartbeat();
    
    // Conversion-Tracking einrichten
    setupConversionTracking();
    
    // Page Unload tracken
    window.addEventListener('beforeunload', trackPageUnload);
    window.addEventListener('pagehide', trackPageUnload);
    
    // Visibility Change (Tab-Wechsel)
    document.addEventListener('visibilitychange', function() {
      if (document.hidden) {
        trackPageUnload();
      }
    });
  }
  
  // Globale Tracking-Funktion für manuelles Tracking
  window.rzAnalytics = {
    trackEvent: trackEvent
  };
  
  // Starten wenn DOM bereit
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
})();
