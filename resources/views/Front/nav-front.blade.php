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
        font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        padding: 12px 0;
        width: 100%;
    }

    /* --- TOP BAR (98% / 1920px) --- */
    .contact-info-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1920px;
        width: 98%;
        margin: 0 auto 10px auto;
        padding: 0 40px;
        color: #ffffff;
        font-size: 13px;
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

    /* Style spécifique pour les icônes sociales blanches */
    .social-icon {
        width: 18px;
        height: 18px;
        fill: #ffffff;
    }

    /* --- NAVBAR BLANCHE --- */
    .navbar {
        background-color: #ffffff;
        max-width: 1920px;
        width: 98%;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        border-radius: 20px;
        margin: 0 auto;
    }

    .logo-img {
        height: 65px;
        width: auto;
    }

    /* --- MENU --- */
    .nav-center-menu {
        display: flex;
        list-style: none;
        gap: 30px;
    }

    .nav-center-menu li a {
        text-decoration: none;
        color: #1a1a1a;
        font-weight: 500;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
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
        gap: 20px;
    }

    .lang-box {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
        font-size: 14px;
    }

    .icon-action {
        color: #333;
        display: flex;
        align-items: center;
    }

    .grid-dots {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 3px;
    }
    .grid-dots span {
        width: 6px;
        height: 6px;
        background-color: #ffc439;
        border-radius: 1px;
    }

    /* BOUTON RÉSERVER (Moins arrondi pour coller à la photo) */
    .btn-reserve {
        background-color: #ffc439;
        color: #1a1a1a;
        text-decoration: none;
        padding: 16px 45px;
        border-radius: 12px; /* Arrondi plus faible, plus "carré" */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 16px;
        letter-spacing: 0.5px;
        transition: background 0.2s;
    }

    .btn-reserve:hover {
        background-color: #f0b420;
    }

    @media (max-width: 1150px) {
        .nav-center-menu, .contact-left { display: none; }
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
            <a href="#"><svg class="social-icon" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
            <a href="#"><svg class="social-icon" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849s-.011 3.585-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849s.012-3.584.07-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.337 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.337-.2 6.78-2.618 6.98-6.98.058-1.281.072-1.689.072-4.948s-.014-3.667-.072-4.947c-.2-4.338-2.617-6.78-6.98-6.98-1.28-.058-1.689-.072-4.948-.072zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
        </div>
    </div>

    <nav class="navbar">
        <div class="nav-left">
            <a href="{{ $formUrl }}">
                <img src="{{ asset('HP-logo-290x91-1.webp') }}" alt="Hello Passenger" class="logo-img">
            </a>
        </div>

        <ul class="nav-center-menu">
            <li><a href="#">Nos Services Bagages <span class="chevron-down"></span></a></li>
            <li><a href="#">A Propos</a></li>
            <li><a href="#">Nous Localiser</a></li>
            <li><a href="#">Contact</a></li>
        </ul>

        <div class="nav-right-group">
            <div class="lang-box">
                <img src="https://flagcdn.com/w20/us.png" width="20" alt="EN"> EN
            </div>
            
            <div class="icon-action">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>

            <div class="icon-action grid-dots">
                <span></span><span></span>
                <span></span><span></span>
            </div>

            <a href="{{ $formUrl }}" class="btn-reserve">Réserver</a>
        </div>
    </nav>
</div>