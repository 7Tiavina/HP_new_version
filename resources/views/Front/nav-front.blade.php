@php
    $formUrl = route('form-consigne');
    $clientGuard = Auth::guard('client');
    $isClientLoggedIn = $clientGuard->check();
    $currentLang = session('app_language', 'fr');
    $langPrefix = $currentLang === 'en' ? '/en' : '';
    $fromPayment = request()->routeIs('payment*') ? '?from=payment' : '';
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

    .contact-item a {
        color: #ffffff;
        text-decoration: none;
        transition: color 0.3s;
    }

    .contact-item a:hover {
        color: #FAC12E;
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
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        padding: 0 15px;
        border-radius: 15px;
        margin: 0 auto;
    }

    .nav-left-group {
        display: flex;
        align-items: center;
        gap: 0;
        justify-self: start;
    }

    .nav-right-group {
        justify-self: end;
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
        align-items: center;
        justify-content: center;
    }

    .nav-center-menu li {
        margin: 0;
        padding: 0 12px;
        display: flex;
        align-items: center;
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
        transition: color 0.3s;
    }

    .nav-center-menu li a:hover {
        color: #FAC12E;
    }

    /* Services Dropdown - Hover to show */
    #servicesItem {
        position: relative;
    }
    #servicesTrigger {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        padding: 0;
        font-family: inherit;
        transition: color 0.2s ease;
        line-height: 1;
    }
    #servicesItem:hover #servicesTrigger,
    #servicesTrigger.open {
        color: #FAC12E;
    }
    #servicesTrigger .chevron {
        flex-shrink: 0;
        transition: transform 0.2s ease;
        width: 12px;
        height: 12px;
    }
    #servicesItem:hover #servicesTrigger .chevron,
    #servicesTrigger.open .chevron {
        transform: rotate(180deg);
    }
    #servicesDropdown {
        display: none;
        position: absolute;
        top: calc(100% + 28px);
        left: 0;
        background: #111;
        border-radius: 10px;
        min-width: 300px;
        max-width: 380px;
        padding: 10px 0;
        z-index: 1000;
    }
    /* Invisible bridge to keep dropdown open when moving cursor */
    #servicesDropdown::before {
        content: '';
        position: absolute;
        top: -30px;
        left: 0;
        right: 0;
        height: 30px;
    }
    #servicesItem:hover #servicesDropdown,
    #servicesDropdown.open {
        display: block;
    }
    #servicesDropdown a {
        display: flex;
        align-items: center;
        padding: 12px 24px;
        text-decoration: none !important;
        color: #fff !important;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        line-height: 1.5;
        background: transparent !important;
    }
    #servicesDropdown a:hover {
        background: #1a1a1a !important;
    }
    #servicesDropdown a .drop-arrow {
        color: #F5B800;
        font-size: 16px;
        position: absolute;
        left: 10px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    #servicesDropdown a .drop-text {
        transition: transform 0.3s ease, color 0.3s ease;
        display: inline-block;
    }
    #servicesDropdown a:hover .drop-arrow {
        opacity: 1;
        transform: translateX(0);
    }
    #servicesDropdown a:hover .drop-text {
        transform: translateX(15px);
        color: #F5B800;
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
        transition: color 0.3s;
    }

    .icon-action:hover {
        color: #FAC12E;
    }

    .mobile-user-icon {
        display: none;
        width: 38px;
        height: 38px;
        background: #f3f4f6;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        color: #374151;
        text-decoration: none;
    }

    .mobile-user-icon.logged-in {
        background: #FAC12E;
        color: #000;
    }

    /* Desktop user email link */
    .nav-user-email {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(250, 193, 46, 0.1);
        border: 1px solid rgba(250, 193, 46, 0.3);
        border-radius: 20px;
        text-decoration: none;
        color: #1a1a1a;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s;
        max-width: 200px;
    }

    .nav-user-email:hover {
        background: rgba(250, 193, 46, 0.2);
        border-color: #FAC12E;
        color: #FAC12E;
    }

    .nav-user-icon {
        flex-shrink: 0;
        color: #FAC12E;
    }

    .nav-user-email-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 1150px) {
        .nav-user-email {
            display: none;
        }
        .mobile-user-icon.logged-in {
            display: flex;
        }
    }

    .icon-action svg {
        width: 18px;
        height: 18px;
    }

    .grid-dots {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .grid-dots:hover {
        background: transparent;
    }

    .grid-dots span {
        width: 8px;
        height: 8px;
        background-color: transparent;
        border: 2px solid #FAC12E;
        border-radius: 2px;
        transition: all 0.3s;
    }

    .grid-dots:hover span {
        background-color: #FAC12E;
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
        background-color: #FAC12E;
        color: #000000;
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
        color: #FAC12E;
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
        color: #FAC12E;
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
        margin-left: auto;
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
        background: rgba(0,0,0,0.5);
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
        background: #ffffff;
        box-shadow: -10px 0 50px rgba(0,0,0,0.3);
    }

    .hp-drawer-wrapper.open {
        right: 0;
    }

    .hp-drawer-overlay.open {
        visibility: visible;
        opacity: 1;
    }

    /* --- LEFT PHOTO DRAWER (GRID DOTS - DESKTOP) --- */
    .left-photo-drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        visibility: hidden;
        opacity: 0;
        transition: 0.4s;
        z-index: 9998;
        backdrop-filter: blur(4px);
    }

    .left-photo-drawer-overlay.open {
        visibility: visible;
        opacity: 1;
    }

    .left-photo-drawer {
        position: fixed;
        top: 10px;
        left: calc(-100% - 10px);
        width: 100%;
        max-width: 340px;
        height: calc(100% - 20px);
        z-index: 9999;
        transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 10px 0 50px rgba(0,0,0,0.5);
        border-radius: 20px 0 0 20px;
    }

    .left-photo-drawer.open {
        left: 10px;
    }

    /* Main content area (black) */
    .left-photo-drawer-main {
        flex: 1;
        background: #1a1a1a;
        overflow-y: auto;
        border-radius: 20px 0 0 20px;
    }

    /* Yellow close bar - separate from drawer with small gap */
    .left-photo-drawer-close-bar {
        position: fixed;
        top: 10px;
        left: 360px;
        width: 60px;
        height: calc(100% - 20px);
        background: #FAC12E;
        z-index: 9999;
        transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 25px;
        border-radius: 30px;
        opacity: 0;
        visibility: hidden;
    }

    .left-photo-drawer.open ~ .left-photo-drawer-close-bar {
        left: 360px;
        opacity: 1;
        visibility: visible;
    }

    .left-photo-drawer-close-btn {
        font-size: 36px;
        color: #000000;
        background: none;
        width: 40px;
        height: 40px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        font-weight: 300;
    }

    .left-photo-drawer-close-btn:hover {
        transform: rotate(90deg);
    }

    .left-photo-drawer-content {
        flex: 1;
        padding: 30px 30px 40px 30px;
        overflow-y: auto;
        color: #ffffff;
    }

    .left-photo-drawer-logo {
        text-align: center;
        margin-bottom: 40px;
        padding-top: 10px;
    }

    .left-photo-drawer-logo img {
        height: 60px;
        width: auto;
    }

    .left-photo-drawer-section {
        margin-bottom: 35px;
    }

    .left-photo-drawer-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 15px;
    }

    .left-photo-drawer-location {
        margin-bottom: 30px;
    }

    .left-photo-drawer-location-title {
        color: #FAC12E;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .left-photo-drawer-location-title a {
        color: #FAC12E;
        text-decoration: underline;
        transition: opacity 0.3s;
    }

    .left-photo-drawer-location-title a:hover {
        opacity: 0.8;
    }

    .left-photo-drawer-map {
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 15px;
        background: #ffffff;
        padding: 10px;
    }

    .left-photo-drawer-map img {
        width: 100%;
        height: auto;
        display: block;
    }

    .left-photo-drawer-map a {
        cursor: pointer;
        display: block;
        transition: opacity 0.3s ease;
    }

    .left-photo-drawer-map a:hover {
        opacity: 0.8;
    }

    .left-photo-drawer-contact {
        margin-bottom: 20px;
    }

    .left-photo-drawer-contact-label {
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .left-photo-drawer-contact-value {
        color: #FAC12E;
        font-size: 16px;
        font-weight: 600;
        text-decoration: underline;
    }

    .left-photo-drawer-contact-value a {
        color: #FAC12E;
        text-decoration: underline;
        transition: opacity 0.3s;
    }

    .left-photo-drawer-contact-value a:hover {
        opacity: 0.8;
    }

    .left-photo-drawer-reserve-btn {
        display: inline-flex;
        align-items: center;
        background-color: #1a1a1a;
        border: 1px solid #FAC12E;
        border-radius: 12px;
        text-decoration: none;
        padding: 0;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-top: 30px;
    }

    .left-photo-drawer-reserve-text {
        color: #ffffff;
        font-family: 'Manrope', sans-serif;
        font-weight: bold;
        font-size: 14px;
        padding: 12px 20px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .left-photo-drawer-reserve-btn .arrow-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #FAC12E;
        color: #1a1a1a;
        width: 50px;
        height: 100%;
        min-height: 44px;
        border-radius: 0;
        flex-shrink: 0;
    }

    .left-photo-drawer-reserve-btn .arrow-icon svg {
        width: 20px;
        height: 20px;
    }

    .left-photo-drawer-reserve-btn:hover {
        background-color: #333333;
        transform: translateY(-2px);
    }

    .hp-drawer-header {
        display: flex;
        justify-content: flex-end;
        padding: 20px;
        background: #ffffff;
        border-bottom: 2px solid #FAC12E;
    }

    .hp-drawer-close-btn {
        font-size: 35px;
        color: #111827;
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
        color: #111827;
    }

    .drawer-logo {
        width: 150px;
        margin-bottom: 30px;
    }

    .drawer-nav-menu {
        list-style: none;
        margin-bottom: 30px;
        padding: 0;
    }

    .drawer-nav-menu li {
        margin-bottom: 5px;
    }

    .drawer-nav-menu a {
        color: #111827;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        padding: 15px 10px;
        display: block;
        border-radius: 8px;
        transition: background 0.3s, color 0.3s;
    }

    .drawer-nav-menu a:hover {
        background: rgba(250, 193, 46, 0.15);
        color: #FAC12E;
    }

    /* Drawer Submenu */
    .drawer-submenu-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
    }
    .drawer-submenu-trigger::after {
        content: '+';
        font-size: 20px;
        font-weight: 300;
        transition: transform 0.2s ease;
    }
    .drawer-submenu.open .drawer-submenu-trigger::after {
        content: '−';
    }
    .drawer-submenu-list {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: rgba(0,0,0,0.03);
        border-radius: 8px;
        margin: 4px 0;
    }
    .drawer-submenu.open .drawer-submenu-list {
        max-height: 300px;
    }
    .drawer-submenu-list li a {
        padding: 10px 12px 10px 28px;
        font-size: 14px;
        color: #4B5563;
    }
    .drawer-submenu-list li a:hover {
        background: rgba(250, 193, 46, 0.1);
        color: #FAC12E;
    }

    .drawer-section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 25px 0 15px 0;
        color: #6B7280;
        letter-spacing: 1px;
    }

    .drawer-contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        color: #111827;
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
        color: #111827;
        text-decoration: none;
        transition: color 0.3s;
    }

    .drawer-contact-item a:hover {
        color: #FAC12E;
    }

    .drawer-footer {
        padding: 20px 25px;
        background: linear-gradient(135deg, rgba(250, 193, 46, 0.1), rgba(249, 168, 37, 0.05));
        border-top: 2px solid #FAC12E;
    }

    .drawer-lang-selector {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .drawer-lang-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #ffffff;
        border-radius: 8px;
        text-decoration: none;
        color: #1a1a1a;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .drawer-lang-btn:hover {
        background: #FAC12E;
        color: #000000;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(250, 193, 46, 0.3);
    }

    .drawer-lang-flag {
        width: 24px;
        height: 18px;
        border-radius: 2px;
        object-fit: cover;
    }

    .drawer-lang-divider {
        color: #9CA3AF;
        font-size: 18px;
        font-weight: 300;
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
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-img {
            height: 50px;
        }

        .nav-center-menu,
        .nav-right-group .icon-action:not(.mobile-user-icon),
        .nav-right-group .luxe-auth-inject,
        .nav-right-group .btn-reserve {
            display: none;
        }

        .nav-right-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .mobile-user-icon {
            display: flex !important;
        }

        .hamburger-menu {
            display: flex;
            margin-left: 5px;
        }
    }
</style>

<div class="hp-nav-container">
    <div class="contact-info-top">
        <div class="contact-left">
            <div class="contact-item">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" style="display: flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><span data-i18n="header_location_label">Location:</span> <span data-i18n="header_location_value">Aéroport de Paris CDG et Orly</span></span>
                </a>
            </div>
            <div class="contact-item">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.81 12.81 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
                <span><span data-i18n="header_call_label">Appelez-nous:</span> <a href="tel:+33134385898">+33 (0)1 34 38 58 98</a></span>
            </div>
            <div class="contact-item">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span><span data-i18n="header_email_label">Email:</span> <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a></span>
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
            <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}">
                <img src="{{ asset('HP-logo-290x91-1.webp') }}" alt="Hello Passenger" class="logo-img">
            </a>
        </div>

        <ul class="nav-center-menu">
            <li class="nav-dropdown" id="servicesItem">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/services/" class="nav-dropdown-trigger" id="servicesTrigger">
                    <span data-i18n="nav_services">Nos Services Bagages</span>
                    <svg class="chevron" width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </a>
                <div class="nav-dropdown-menu" id="servicesDropdown">
                    <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/consigne-bagages/">
                        <span class="drop-arrow">↗</span>
                        <span class="drop-text" data-i18n="service_consigne">Consigne à Bagages</span>
                    </a>
                    <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/transfert-livraison-bagages/">
                        <span class="drop-arrow">↗</span>
                        <span class="drop-text" data-i18n="service_transfert">Transfert &amp; Livraison Bagages</span>
                    </a>
                    <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/assistance-personnalisee/">
                        <span class="drop-arrow">↗</span>
                        <span class="drop-text" data-i18n="service_assistance">Assistance Personnalisée</span>
                    </a>
                    <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/bdm-travel-store/">
                        <span class="drop-arrow">↗</span>
                        <span class="drop-text" data-i18n="service_bdm">BDM Travel Store</span>
                    </a>
                    <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/services-facilitateurs-de-voyage/">
                        <span class="drop-arrow">↗</span>
                        <span class="drop-text" data-i18n="service_facilitateurs">Services Pratiques</span>
                    </a>
                </div>
            </li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/a-propos/" data-i18n="nav_about">A Propos</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" data-i18n="nav_locate">Nous Localiser</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/contact/" data-i18n="nav_contact">Nous contacter</a></li>
            <li class="lang-selector-item">
                @include('components.translation-widget')
            </li>
        </ul>

        <div class="nav-right-group">
            @if($isClientLoggedIn)
                <a href="{{ route('account') }}{{ $fromPayment }}" class="nav-user-email" title="Mon compte">
                    <svg class="nav-user-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span class="nav-user-email-text">{{ $clientGuard->user()->prenom ?? 'Client' }}</span>
                </a>
                <a href="{{ route('account') }}{{ $fromPayment }}" class="mobile-user-icon logged-in" title="Mon compte">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            @else
                <div class="icon-action" id="user-icon-trigger" style="cursor: pointer;" title="Se connecter">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <a href="{{ route('account') }}{{ $fromPayment }}" class="mobile-user-icon" title="Se connecter">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            @endif

            <div class="luxe-auth-inject" style="display: none;"></div>

            <div class="icon-action grid-dots">
                <span></span><span></span>
                <span></span><span></span>
            </div>

            <div class="hamburger-menu" id="hamburger-menu" onclick="toggleDrawer()">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>
        </div>
    </nav>
</div>

<!-- LEFT PHOTO DRAWER (Grid Dots Button - Desktop) -->
<div class="left-photo-drawer-overlay" id="left-photo-drawer-overlay" onclick="toggleLeftPhotoDrawer()"></div>
<div class="left-photo-drawer" id="left-photo-drawer">
    <!-- Main content (black background) -->
    <div class="left-photo-drawer-main">
        <div class="left-photo-drawer-content">
            <!-- Logo -->
            <div class="left-photo-drawer-logo">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}">
                    <img src="{{ asset('HP-Logo-White.png') }}" alt="Hello Passenger">
                </a>
            </div>

            <!-- Plan d'accès Section -->
            <div class="left-photo-drawer-section">
                <h2 class="left-photo-drawer-section-title" data-i18n="drawer_access_plan">Plan d'accès</h2>

                <!-- CDG Airport -->
                <div class="left-photo-drawer-location">
                    <div class="left-photo-drawer-location-title">
                        <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" data-i18n="drawer_cdg">Aéroport de Paris CDG</a><br>
                        <span data-i18n="drawer_cdg_address">Terminal 2<br>
                        Gare TGV – Niveau 4<br>
                        Opposition Hôtel Sheraton,<br>
                        entre les terminaux 2C et 2E</span>
                    </div>
                    <div class="left-photo-drawer-map">
                        <a href="{{ asset('PA_CDG_FR_JAN_2026_9446a22942.jpg') }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('PA_CDG_FR_JAN_2026_9446a22942.jpg') }}" alt="CDG Airport Map">
                        </a>
                    </div>
                </div>

                <!-- Orly Airport -->
                <div class="left-photo-drawer-location">
                    <div class="left-photo-drawer-location-title">
                        <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" data-i18n="drawer_orly">Aéroport de Paris ORLY</a><br>
                        <span data-i18n="drawer_orly_address">Terminal 3<br>
                        Niveau d'arrivée</span>
                    </div>
                    <div class="left-photo-drawer-map">
                        <a href="{{ asset('PA_ORY_FR_JUN_2025_9ac2300e1c-1020x1020.jpg') }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('PA_ORY_FR_JUN_2025_9ac2300e1c-1020x1020.jpg') }}" alt="Orly Airport Map">
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="left-photo-drawer-section">
                <h2 class="left-photo-drawer-section-title" data-i18n="drawer_contact">Contact</h2>

                <div class="left-photo-drawer-contact">
                    <div class="left-photo-drawer-contact-value">
                        <a href="tel:+33134385898">+33 (0)1 34 38 58 98</a>
                    </div>
                </div>

                <div class="left-photo-drawer-contact">
                    <div class="left-photo-drawer-contact-label" data-i18n="header_email_label">Email</div>
                    <div class="left-photo-drawer-contact-value">
                        <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a>
                    </div>
                </div>
            </div>

            <!-- Reserve Button -->
            <a href="{{ $formUrl }}" class="left-photo-drawer-reserve-btn" onclick="toggleLeftPhotoDrawer()">
                <span class="left-photo-drawer-reserve-text" data-i18n="drawer_book">Réserver</span>
                <span class="arrow-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</div>

<!-- Yellow close bar (separate from drawer) -->
<div class="left-photo-drawer-close-bar">
    <button class="left-photo-drawer-close-btn" onclick="toggleLeftPhotoDrawer()">&times;</button>
</div>

<!-- RIGHT DRAWER (Mobile - Gray Hamburger) -->
<div class="hp-drawer-overlay" id="hp-drawer-overlay" onclick="toggleDrawer()"></div>
<div class="hp-drawer-wrapper" id="hp-drawer">
    <div class="hp-drawer-header">
        <button class="hp-drawer-close-btn" onclick="toggleDrawer()">&times;</button>
    </div>

    <div class="hp-drawer-main">
        <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}">
            <img src="{{ asset('images/HP-logo-290x91.png') }}" class="drawer-logo" alt="Hello Passenger">
        </a>

        <ul class="drawer-nav-menu">
            <li class="drawer-submenu">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/services/" class="drawer-submenu-trigger" data-i18n="nav_services">Nos Services Bagages</a>
                <ul class="drawer-submenu-list">
                    <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/consigne-bagages/" data-i18n="service_consigne">Consigne Bagages</a></li>
                    <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/transfert-livraison-bagages/" data-i18n="service_transfert">Transfert &amp; Livraison Bagages</a></li>
                    <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/assistance-personnalisee/" data-i18n="service_assistance">Assistance Personnalisée</a></li>
                    <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/bdm-travel-store/" data-i18n="service_bdm">BDM Travel Store</a></li>
                    <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/services-facilitateurs-de-voyage/" data-i18n="service_facilitateurs">Services Facilitateurs de Voyage</a></li>
                </ul>
            </li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/a-propos/" data-i18n="nav_about">A Propos</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" data-i18n="nav_locate">Nous Localiser</a></li>
            <li><a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/contact/" data-i18n="nav_contact">Nous contacter</a></li>
            @if($isClientLoggedIn)
                <li>
                    <form method="POST" action="{{ route('client.logout') }}" class="inline">
                        @csrf
                        <button type="submit" onclick="toggleDrawer();" class="text-red-600 hover:text-red-700 w-full text-left" data-i18n="logout_btn">Déconnecter</button>
                    </form>
                </li>
            @else
                <li><a href="{{ $formUrl }}" data-i18n="btn_book">Réserver</a></li>
            @endif
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
        <div class="drawer-lang-selector">
            <a href="{{ route('set-language', ['lang' => 'en']) }}" class="drawer-lang-btn">
                <img src="https://flagcdn.com/w40/us.png" alt="EN" class="drawer-lang-flag">
                <span>English</span>
            </a>
            <span class="drawer-lang-divider">|</span>
            <a href="{{ route('set-language', ['lang' => 'fr']) }}" class="drawer-lang-btn">
                <img src="https://flagcdn.com/w40/fr.png" alt="FR" class="drawer-lang-flag">
                <span>Français</span>
            </a>
        </div>
    </div>
</div>

<script>
    // Toggle LEFT photo drawer (desktop - grid dots button)
    function toggleLeftPhotoDrawer() {
        const drawer = document.getElementById('left-photo-drawer');
        const overlay = document.getElementById('left-photo-drawer-overlay');

        if (!drawer || !overlay) return;

        const isOpen = drawer.classList.contains('open');

        drawer.classList.toggle('open');
        overlay.classList.toggle('open');

        document.body.style.overflow = !isOpen ? 'hidden' : 'auto';
    }

    // Toggle RIGHT drawer (mobile - gray hamburger)
    function toggleDrawer() {
        const drawer = document.getElementById('hp-drawer');
        const overlay = document.getElementById('hp-drawer-overlay');
        const hamburger = document.getElementById('hamburger-menu');

        if (!drawer || !overlay) return;

        const isOpen = drawer.classList.contains('open');

        drawer.classList.toggle('open');
        overlay.classList.toggle('open');

        if (hamburger) {
            hamburger.classList.toggle('open');
        }

        document.body.style.overflow = !isOpen ? 'hidden' : 'auto';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Grid dots opens LEFT photo drawer (desktop)
        const gridDots = document.querySelector('.grid-dots');
        if (gridDots) {
            gridDots.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleLeftPhotoDrawer();
            });
        }

        const userIcon = document.getElementById('user-icon-trigger');
        if (userIcon) {
            userIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                window.location.href = @json(route('account')) + '{{ $fromPayment }}';
            });
        }

        const drawerLinks = document.querySelectorAll('.drawer-nav-menu a, .drawer-lang-btn');
        drawerLinks.forEach(function(link) {
            // Don't close drawer on submenu trigger click
            if (link.classList.contains('drawer-submenu-trigger')) {
                link.addEventListener('click', function(e) {
                    const parent = this.closest('.drawer-submenu');
                    // Si le menu n'est pas encore ouvert, on l'ouvre d'abord
                    if (parent && !parent.classList.contains('open')) {
                        e.preventDefault();
                        e.stopPropagation();
                        parent.classList.add('open');
                    }
                    // Sinon (déjà ouvert), on laisse le lien rediriger normalement
                });
            } else if (link.closest('.drawer-submenu-list')) {
                // Submenu item: close drawer on click
                link.addEventListener('click', function() {
                    toggleDrawer();
                });
            } else {
                link.addEventListener('click', function() {
                    toggleDrawer();
                });
            }
        });
    });

    // Services dropdown toggle (click to open/close)
    function toggleServicesDropdown(event) {
        event.stopPropagation();
        document.getElementById('servicesTrigger').classList.toggle('open');
        document.getElementById('servicesDropdown').classList.toggle('open');
    }

    // Close services dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('servicesItem');
        const trigger = document.getElementById('servicesTrigger');
        const dd = document.getElementById('servicesDropdown');

        if (container && !container.contains(e.target)) {
            trigger.classList.remove('open');
            dd.classList.remove('open');
        }
    });
</script>
