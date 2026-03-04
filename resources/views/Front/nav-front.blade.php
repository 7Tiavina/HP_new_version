@php
    $formUrl = route('form-consigne');
@endphp

<style>
    /* --- RESET --- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .hp-nav-container {
        background-color: #000000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 15px;
        width: 100%;
    }

    /* --- BLOC BLANC (NAV) --- */
    .navbar {
        background-color: #ffffff;
        width: 100%;
        height: 100px;
        display: flex;
        align-items: center;
        padding: 0 40px;
        border-radius: 12px;
    }

    .nav-left {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-shrink: 0;
    }

    .burger-dots {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
        cursor: pointer;
    }
    .burger-dots span {
        width: 10px;
        height: 10px;
        background-color: #ffc439;
        border-radius: 1px;
    }

    .logo-img {
        height: 55px;
        width: auto;
    }

    /* --- MENU DÉPLACÉ À DROITE --- */
    .nav-center {
        display: flex;
        list-style: none;
        gap: 35px;
        align-items: center;
        margin-left: auto;
        margin-right: 40px;
    }

    .nav-center a {
        text-decoration: none;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 15px;
    }

    .nav-center a:hover {
        color: #ffc439;
    }

    .lang-box {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: bold;
        color: #1a1a1a;
        font-size: 15px;
    }
    .flag-icon { width: 20px; }

    /* Bouton Réserver */
    .btn-reserve {
        background-color: #ffc439;
        color: #000;
        text-decoration: none;
        padding: 16px 45px;
        border-radius: 35px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        flex-shrink: 0;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-reserve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 196, 57, 0.4);
    }

    /* --- BARRE D'INFOS NOIRE --- */
    .info-bar {
        background-color: #1a1a1a;
        width: 100%;
        margin-top: 10px;
        padding: 15px 40px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff;
        font-size: 14px;
    }

    .info-group {
        display: flex;
        gap: 30px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-item svg {
        width: 18px;
        height: 18px;
        stroke: #ffffff;
        stroke-width: 2;
        fill: none;
    }

    .social-links {
        display: flex;
        gap: 20px;
    }

    .social-links a {
        display: flex;
        align-items: center;
        transition: opacity 0.3s;
    }

    .social-links a:hover {
        opacity: 0.7;
    }

    .social-links svg {
        width: 20px;
        height: 20px;
        fill: #ffffff;
    }

    @media (max-width: 1100px) {
        .nav-center, .info-bar { display: none; }
    }

    /* Translation widget dans la nav */
    .nav-center .hp-lang-widget {
        display: inline-flex;
        align-items: center;
    }
    .nav-center .hp-lang-trigger {
        background: transparent;
        border: none;
        padding: 0.4rem;
        gap: 0.5rem;
    }
    .nav-center .hp-lang-trigger:hover {
        background: rgba(255, 196, 57, 0.15);
    }
    .nav-center .hp-lang-current-flag {
        width: 20px;
        height: 14px;
    }
    .nav-center .hp-lang-chevron {
        width: 12px;
        height: 12px;
        color: #1a1a1a;
    }
    .nav-center .hp-lang-dropdown {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .nav-center .hp-lang-option {
        color: #1a1a1a;
    }
    .nav-center .hp-lang-option:hover {
        background: rgba(255, 196, 57, 0.15);
    }
    .nav-center .hp-lang-option.active {
        background: rgba(255, 196, 57, 0.2);
        color: #ffc439;
        font-weight: 700;
    }

    /* Auth injection in nav */
    .nav-center .luxe-auth-inject {
        display: inline-flex;
        align-items: center;
        gap: 1.2rem;
    }
    .nav-center .luxe-auth-inject a,
    .nav-center .luxe-auth-inject button {
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
    .nav-center .luxe-auth-inject a:hover,
    .nav-center .luxe-auth-inject button:hover {
        color: #ffc439;
    }
</style>

<div class="hp-nav-container">
    <nav class="navbar">
        <div class="nav-left">
            <div class="burger-dots" onclick="console.log('Menu clicked')">
                <span></span><span></span>
                <span></span><span></span>
            </div>
            <a href="{{ $formUrl }}">
                <img src="{{ asset('HP-logo-290x91-1.webp') }}" alt="Hello Passenger" class="logo-img">
            </a>
        </div>

        <ul class="nav-center">
            <li><a href="#login" class="login-link" data-i18n="login_btn">Se connecter</a></li>
            <li><a href="#signup" class="register-link" data-i18n="create_account_short">S'inscrire</a></li>
            <li class="luxe-auth-inject"></li>
            <li>
                @include('components.translation-widget')
            </li>
        </ul>

        <div class="nav-right">
            <a href="{{ $formUrl }}" class="btn-reserve" data-i18n="nav_book">Réserver</a>
        </div>
    </nav>

    <div class="info-bar">
        <div class="info-group">
            <div class="info-item">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <span>Location: Aéroport de Paris CDG et Orly</span>
            </div>
            <div class="info-item">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.81 12.81 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
                <span>Appelez-nous: +33 (0)1 34 38 58 98</span>
            </div>
            <div class="info-item">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span>Email: contact@hellopassenger.com</span>
            </div>
        </div>

        <div class="social-links">
            <a href="https://www.facebook.com/votrepage" target="_blank" aria-label="Facebook">
                <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
            </a>
            <a href="https://www.linkedin.com/company/votrepage" target="_blank" aria-label="LinkedIn">
                <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
            </a>
        </div>
    </div>
</div>
