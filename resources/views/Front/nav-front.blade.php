@php
    $formUrl = route('form-consigne');
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap');

    /* --- RESET --- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .hp-nav-container {
        background-color: #000000;
        font-family: 'Manrope', sans-serif;
        padding: 12px 0;
        width: 100%;
    }

    /* --- TOP BAR (97% width) --- */
    .contact-info-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 97%;
        max-width: 1600px;
        margin: 0 auto 10px auto;
        padding: 0 15px;
        color: #ffffff;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
    }

    .contact-left {
        display: flex;
        gap: 25px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .contact-item svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: #ffffff;
        stroke-width: 2;
    }

    .social-links {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .social-links a {
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .social-icon {
        width: 18px;
        height: 18px;
        fill: #ffffff;
    }

    /* --- NAVBAR BLANCHE --- */
    .navbar {
        background-color: #ffffff;
        width: 97%;
        max-width: 1600px;
        height: 130px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px;
        border-radius: 15px;
        margin: 0 auto;
    }

    .nav-left-group {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .logo-img {
        height: 80px;
        width: auto;
        display: block;
    }

    /* --- MENU --- */
    .nav-center-menu {
        display: flex;
        list-style: none;
        gap: 0;
        margin: 0;
        padding: 0;
    }

    .nav-center-menu li {
        margin: 0;
        padding: 0 10px;
    }

    .nav-center-menu li a {
        text-decoration: none;
        color: #1a1a1a;
        font-weight: 500;
        font-size: 16px;
        font-family: 'Manrope', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0;
        margin: 0;
    }

    .nav-center-menu .lang-selector-item {
        display: inline-flex;
        align-items: center;
    }
    .nav-center-menu .lang-selector-item #hp-lang-widget {
        display: inline-flex;
        align-items: center;
    }
    .nav-center-menu .lang-selector-item #hp-lang-trigger {
        background: none;
        border: none;
        padding: 0 8px;
        color: #1a1a1a;
        font-size: 16px;
        font-weight: 500;
        font-family: 'Manrope', sans-serif;
        cursor: pointer;
    }
    .nav-center-menu .lang-selector-item #hp-lang-trigger:hover {
        color: #FAC12E;
    }
    .nav-center-menu .lang-selector-item .hp-lang-current-flag {
        width: 20px;
        height: 14px;
    }
    .nav-center-menu .lang-selector-item .hp-lang-chevron {
        width: 12px;
        height: 12px;
    }
    .nav-center-menu .lang-selector-item .hp-lang-dropdown {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        min-width: 100px;
    }
    .nav-center-menu .lang-selector-item .hp-lang-option {
        color: #1a1a1a;
        font-size: 13px;
    }
    .nav-center-menu .lang-selector-item .hp-lang-option:hover {
        background: #f5f5f5;
    }

    .chevron-down {
        width: 6px;
        height: 6px;
        border-bottom: 2px solid #666;
        border-right: 2px solid #666;
        transform: rotate(45deg);
        margin-top: -3px;
    }

    /* --- BLOC DROITE --- */
    .nav-right-group {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .lang-box {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
        font-size: 13px;
    }

    .icon-action {
        color: #333;
        display: flex;
        align-items: center;
    }

    .icon-action svg {
        width: 18px;
        height: 18px;
    }

    .grid-dots {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 3px;
    }
    .grid-dots span {
        width: 5px;
        height: 5px;
        background-color: #FAC12E;
        border-radius: 1px;
    }

    /* BOUTON RÉSERVER */
    .btn-reserve {
        background-color: #FAC12E;
        color: #000000;
        text-decoration: none;
        padding: 12px 24px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 18px;
        font-family: 'Manrope', sans-serif;
        letter-spacing: 0.5px;
        transition: background 0.2s;
    }

    .btn-reserve:hover {
        background-color: #e5ad28;
    }

    @media (max-width: 1150px) {
        .nav-center-menu, .contact-left { display: none; }
    }

    /* Auth injection in nav */
    .nav-right-group .luxe-auth-inject {
        display: inline-flex;
        align-items: center;
        gap: 1rem;
    }
    .nav-right-group .luxe-auth-inject a,
    .nav-right-group .luxe-auth-inject button {
        color: #1a1a1a;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        font-family: inherit;
        transition: color 0.3s;
    }
    .nav-right-group .luxe-auth-inject a:hover,
    .nav-right-group .luxe-auth-inject button:hover {
        color: #ffc439;
    }

    .nav-right-group .login-link,
    .nav-right-group .register-link {
        color: #1a1a1a;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s;
    }
    .nav-right-group .login-link:hover,
    .nav-right-group .register-link:hover {
        color: #ffc439;
    }

    /* --- HAMBURGER MENU (MOBILE ONLY) --- */
    .hamburger-menu {
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 48px;
        height: 48px;
        background: #e5e5e5;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .hamburger-menu:hover {
        background: #d4d4d4;
    }

    .hamburger-line {
        width: 20px;
        height: 2.5px;
        background: #000000;
        margin: 2px 0;
        transition: 0.3s;
        border-radius: 2px;
    }

    .hamburger-menu.open .hamburger-line:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .hamburger-menu.open .hamburger-line:nth-child(2) {
        opacity: 0;
    }

    .hamburger-menu.open .hamburger-line:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
    }

    /* --- DRAWER (RIGHT SIDE) --- */
    .hp-drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        visibility: hidden;
        opacity: 0;
        transition: 0.4s;
        z-index: 9998;
        backdrop-filter: blur(4px);
    }

    .hp-drawer-wrapper {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        max-width: 400px;
        height: 100%;
        z-index: 9999;
        transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        display: flex;
        flex-direction: column;
        background: #1a1a1a;
        box-shadow: -10px 0 50px rgba(0,0,0,0.5);
    }

    .hp-drawer-wrapper.open {
        right: 0;
    }

    .hp-drawer-overlay.open {
        visibility: visible;
        opacity: 1;
    }

    .hp-drawer-header {
        display: flex;
        justify-content: flex-end;
        padding: 20px;
        background: #000000;
    }

    .hp-drawer-close-btn {
        font-size: 35px;
        color: #ffffff;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.3s;
        line-height: 1;
    }

    .hp-drawer-close-btn:hover {
        color: #FAC12E;
    }

    .hp-drawer-main {
        flex: 1;
        padding: 30px 25px;
        overflow-y: auto;
        color: #ffffff;
    }

    .drawer-logo {
        width: 150px;
        margin-bottom: 30px;
    }

    .drawer-nav-menu {
        list-style: none;
        margin-bottom: 30px;
    }

    .drawer-nav-menu li {
        margin-bottom: 5px;
    }

    .drawer-nav-menu a {
        color: #ffffff;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        padding: 15px 10px;
        display: block;
        border-radius: 8px;
        transition: background 0.3s, color 0.3s;
    }

    .drawer-nav-menu a:hover {
        background: rgba(250, 193, 46, 0.1);
        color: #FAC12E;
    }

    .drawer-section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 25px 0 15px 0;
        color: #888;
        letter-spacing: 1px;
    }

    .drawer-contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        color: #ffffff;
        font-size: 15px;
    }

    .drawer-contact-item svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: #FAC12E;
        stroke-width: 2;
        flex-shrink: 0;
    }

    .drawer-contact-item a {
        color: #ffffff;
        text-decoration: none;
        transition: color 0.3s;
    }

    .drawer-contact-item a:hover {
        color: #FAC12E;
    }

    .drawer-footer {
        padding: 20px 25px;
        background: #000000;
        border-top: 1px solid #333;
    }

    .drawer-footer-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #FAC12E;
        color: #000000;
        text-decoration: none;
        padding: 16px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 16px;
        letter-spacing: 0.5px;
        transition: background 0.3s;
    }

    .drawer-footer-btn:hover {
        background: #e5ad28;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 1150px) {
        .nav-right-group .btn-reserve {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .navbar {
            height: 80px;
            padding: 0 20px;
        }

        .logo-img {
            height: 50px;
        }
        
        .nav-center-menu, 
        .nav-right-group .icon-action,
        .nav-right-group .luxe-auth-inject,
        .nav-right-group .btn-reserve {
            display: none;
        }
        
        .nav-right-group {
            display: flex;
        }
        
        .hamburger-menu {
            display: flex;
        }
    }
</style>

<div class="hp-nav-container">
    <div class="contact-info-top">
        <div class="contact-left">
            <div class="contact-item">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>Location: Aéroport de Paris CDG et Orly</span>
            </div>
            <div class="contact-item">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.81 12.81 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
                <span>Appelez-nous: +33 (0)1 34 38 58 98</span>
            </div>
            <div class="contact-item">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span>Email: contact@hellopassenger.com</span>
            </div>
        </div>
        <div class="social-links">
            <a href="https://www.facebook.com/hello.passenger.officiel/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <svg class="social-icon" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
            </a>
            <a href="https://www.instagram.com/hellopassenger_officiel/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg class="social-icon" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849s-.011 3.585-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849s.012-3.584.07-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.337 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.337-.2 6.78-2.618 6.98-6.98.058-1.281.072-1.689.072-4.948s-.014-3.667-.072-4.947c-.2-4.338-2.617-6.78-6.98-6.98-1.28-.058-1.689-.072-4.948-.072zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
        </div>
    </div>

    <nav class="navbar">
        <div class="nav-left-group">
            <a href="{{ $formUrl }}">
                <img src="{{ asset('HP-logo-290x91-1.webp') }}" alt="Hello Passenger" class="logo-img">
            </a>
            <ul class="nav-center-menu">
                <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/services/" data-i18n="nav_services">Nos Services Bagages</a></li>
                <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/a-propos/" data-i18n="nav_about">A Propos</a></li>
                <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/nous-localiser/" data-i18n="nav_locate">Nous Localiser</a></li>
                <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/contact/" data-i18n="nav_contact">Contact</a></li>
                <li class="lang-selector-item">
                    @include('components.translation-widget')
                </li>
            </ul>
        </div>

        <div class="nav-right-group">
            <div class="icon-action" id="user-icon-trigger" style="cursor: pointer;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>

            <div class="luxe-auth-inject" style="display: none;"></div>

            <div class="icon-action grid-dots">
                <span></span><span></span>
                <span></span><span></span>
            </div>

            <a href="{{ $formUrl }}" class="btn-reserve" id="btn-reserve" data-i18n="btn_book">Réserver</a>

            <div class="hamburger-menu" id="hamburger-menu" onclick="toggleDrawer()">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>
        </div>
    </nav>
</div>

<!-- Drawer Overlay & Wrapper -->
<div class="hp-drawer-overlay" id="hp-drawer-overlay" onclick="toggleDrawer()"></div>
<div class="hp-drawer-wrapper" id="hp-drawer">
    <div class="hp-drawer-header">
        <button class="hp-drawer-close-btn" onclick="toggleDrawer()">&times;</button>
    </div>

    <div class="hp-drawer-main">
        <img src="{{ asset('HP-Logo-White.png') }}" class="drawer-logo" alt="Hello Passenger">

        <ul class="drawer-nav-menu">
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/services/" data-i18n="nav_services">Nos Services Bagages</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/a-propos/" data-i18n="nav_about">A Propos</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/nous-localiser/" data-i18n="nav_locate">Nous Localiser</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com/contact/" data-i18n="nav_contact">Contact</a></li>
            <li><a href="{{ $formUrl }}" data-i18n="btn_book">Réserver</a></li>
        </ul>

        <h3 class="drawer-section-title" data-i18n="drawer_contact">Contact</h3>

        <div class="drawer-contact-item">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.81 12.81 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
            <a href="tel:+33134385898">+33 (0)1 34 38 58 98</a>
        </div>

        <div class="drawer-contact-item">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a>
        </div>
    </div>

    <div class="drawer-footer">
        <a href="{{ $formUrl }}" class="drawer-footer-btn" data-i18n="drawer_book">
            Contactez-nous
        </a>
    </div>
</div>

<script>
    function toggleDrawer() {
        const drawer = document.getElementById('hp-drawer');
        const overlay = document.getElementById('hp-drawer-overlay');
        const hamburger = document.getElementById('hamburger-menu');

        if (!drawer || !overlay) return;

        drawer.classList.toggle('open');
        overlay.classList.toggle('open');

        if (hamburger) {
            hamburger.classList.toggle('open');
        }

        document.body.style.overflow = drawer.classList.contains('open') ? 'hidden' : 'auto';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const gridDots = document.querySelector('.grid-dots');
        if (gridDots) {
            gridDots.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDrawer();
            });
        }

        const userIcon = document.getElementById('user-icon-trigger');
        if (userIcon) {
            userIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                if (typeof openLoginModal === 'function') {
                    openLoginModal();
                }
            });
        }

        const drawerLinks = document.querySelectorAll('.drawer-nav-menu a, .drawer-footer-btn');
        drawerLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                toggleDrawer();
            });
        });
    });
</script>
