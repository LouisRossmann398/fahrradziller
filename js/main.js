/**
 * Radsport Ziller - Main JavaScript
 * Funktionen: Mobile Navigation, Accessibility Features
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // ============================================
  // Mobile Navigation Toggle
  // ============================================
  
  const menuToggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.nav');
  const navLinks = document.querySelectorAll('.nav-link');
  
  if (menuToggle && nav) {
    menuToggle.addEventListener('click', function() {
      nav.classList.toggle('active');
      
      // Accessibility: Update aria-expanded
      const isExpanded = nav.classList.contains('active');
      menuToggle.setAttribute('aria-expanded', isExpanded);
      
      // Change icon
      menuToggle.textContent = isExpanded ? '✕' : '☰';
    });
    
    // Close menu when clicking on a link (mobile)
    navLinks.forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth < 768) {
          nav.classList.remove('active');
          menuToggle.setAttribute('aria-expanded', 'false');
          menuToggle.textContent = '☰';
        }
      });
    });
  }
  
  // ============================================
  // Active Navigation Link
  // ============================================
  
  function setActiveNavLink() {
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop() || 'index.html';
    
    navLinks.forEach(link => {
      link.classList.remove('active');
      const linkHref = link.getAttribute('href');
      
      if (linkHref === currentPage || 
          (currentPage === '' && linkHref === 'index.html') ||
          (currentPage === 'index.html' && linkHref === 'index.html')) {
        link.classList.add('active');
      }
    });
  }
  
  setActiveNavLink();
  
  // ============================================
  // Accessibility: Text Size Control
  // ============================================
  
  const textSizeBtn = document.getElementById('textSizeBtn');
  let currentTextSize = 0; // 0 = normal, 1 = large, 2 = xlarge
  
  // Load saved text size preference
  const savedTextSize = localStorage.getItem('textSize');
  if (savedTextSize) {
    currentTextSize = parseInt(savedTextSize);
    applyTextSize(currentTextSize);
  }
  
  if (textSizeBtn) {
    textSizeBtn.addEventListener('click', function() {
      currentTextSize = (currentTextSize + 1) % 3;
      applyTextSize(currentTextSize);
      localStorage.setItem('textSize', currentTextSize);
    });
  }
  
  function applyTextSize(size) {
    const html = document.documentElement;
    html.classList.remove('text-size-normal', 'text-size-large', 'text-size-xlarge');
    
    switch(size) {
      case 0:
        html.classList.add('text-size-normal');
        if (textSizeBtn) textSizeBtn.title = 'Textgröße: Normal (klicken für Groß)';
        break;
      case 1:
        html.classList.add('text-size-large');
        if (textSizeBtn) textSizeBtn.title = 'Textgröße: Groß (klicken für Sehr Groß)';
        break;
      case 2:
        html.classList.add('text-size-xlarge');
        if (textSizeBtn) textSizeBtn.title = 'Textgröße: Sehr Groß (klicken für Normal)';
        break;
    }
  }
  
  // ============================================
  // Smooth Scroll for Anchor Links
  // ============================================
  
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href !== '#') {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          const headerOffset = 80;
          const elementPosition = target.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
          
          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      }
    });
  });
  
  // ============================================
  // Form Validation (if form exists)
  // ============================================
  
  const contactForm = document.getElementById('contactForm');
  
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Simple validation
      const name = document.getElementById('name');
      const email = document.getElementById('email');
      const message = document.getElementById('message');
      
      let isValid = true;
      
      // Clear previous error messages
      document.querySelectorAll('.error-message').forEach(el => el.remove());
      
      if (!name.value.trim()) {
        showError(name, 'Bitte geben Sie Ihren Namen ein');
        isValid = false;
      }
      
      if (!email.value.trim() || !isValidEmail(email.value)) {
        showError(email, 'Bitte geben Sie eine gültige E-Mail-Adresse ein');
        isValid = false;
      }
      
      if (!message.value.trim()) {
        showError(message, 'Bitte geben Sie eine Nachricht ein');
        isValid = false;
      }
      
      if (isValid) {
        // Show success message (since we don't have backend yet)
        showSuccessMessage();
        contactForm.reset();
      }
    });
  }
  
  function showError(input, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'var(--color-primary)';
    errorDiv.style.fontSize = 'var(--font-size-small)';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    input.parentElement.appendChild(errorDiv);
    input.style.borderColor = 'var(--color-primary)';
    
    // Reset border color on input
    input.addEventListener('input', function() {
      input.style.borderColor = '';
      const error = input.parentElement.querySelector('.error-message');
      if (error) error.remove();
    }, { once: true });
  }
  
  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
  
  function showSuccessMessage() {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.style.backgroundColor = '#28a745';
    successDiv.style.color = 'white';
    successDiv.style.padding = 'var(--spacing-md)';
    successDiv.style.borderRadius = '4px';
    successDiv.style.marginTop = 'var(--spacing-md)';
    successDiv.style.textAlign = 'center';
    successDiv.textContent = 'Vielen Dank! Ihre Nachricht wurde gesendet. Wir melden uns bald bei Ihnen.';
    
    contactForm.appendChild(successDiv);
    
    setTimeout(() => {
      successDiv.remove();
    }, 5000);
  }
  
  // ============================================
  // Keyboard Navigation Enhancement
  // ============================================
  
  // Add skip to main content link
  const skipLink = document.createElement('a');
  skipLink.href = '#main-content';
  skipLink.textContent = 'Zum Hauptinhalt springen';
  skipLink.className = 'skip-link';
  skipLink.style.cssText = `
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--color-primary);
    color: white;
    padding: 8px;
    text-decoration: none;
    z-index: 9999;
  `;
  
  skipLink.addEventListener('focus', function() {
    this.style.top = '0';
  });
  
  skipLink.addEventListener('blur', function() {
    this.style.top = '-40px';
  });
  
  document.body.insertBefore(skipLink, document.body.firstChild);
  
});

// ============================================
// Window Resize Handler
// ============================================

let resizeTimer;
window.addEventListener('resize', function() {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(function() {
    // Reset mobile menu on desktop
    if (window.innerWidth >= 768) {
      const nav = document.querySelector('.nav');
      const menuToggle = document.querySelector('.menu-toggle');
      if (nav && nav.classList.contains('active')) {
        nav.classList.remove('active');
        if (menuToggle) {
          menuToggle.setAttribute('aria-expanded', 'false');
          menuToggle.textContent = '☰';
        }
      }
    }
  }, 250);
});
