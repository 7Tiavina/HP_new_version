@extends('layouts.front')

@php
    $currentLang = session('app_language', 'fr');
    $isClientLoggedIn = Auth::guard('client')->check();
@endphp

@section('title', $currentLang === 'en' ? 'My Account — Hello Passenger' : 'Mon Compte — Hello Passenger')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
<style>
    .auth-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px 24px 40px;
        background: #f5f5f5;
    }

    .auth-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        max-width: 960px;
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        overflow: hidden;
        border: 1px solid #f0f0f0;
    }

    /* Left side */
    .auth-card__info {
        background: linear-gradient(135deg, #111111, #f7b500);
        color: #fff;
        padding: 32px 28px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .auth-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
    }

    .auth-logo img {
        height: 36px;
    }

    .auth-logo span {
        font-weight: 600;
        font-size: 18px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .auth-card__info h1 {
        margin: 0 0 12px;
        font-size: 28px;
        line-height: 1.2;
    }

    .auth-card__info p {
        margin: 0 0 20px;
        font-size: 14px;
        opacity: 0.95;
    }

    .auth-card__info ul {
        padding-left: 20px;
        margin: 0 0 14px;
        font-size: 14px;
        opacity: 0.98;
        list-style: disc;
    }

    .auth-card__info li {
        margin-bottom: 8px;
    }

    .auth-card__info-footer {
        font-size: 11px;
        opacity: 0.9;
    }

    /* Right side */
    .auth-card__form {
        padding: 24px 22px 26px;
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .top-bar {
        margin-bottom: 16px;
    }

    .tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 4px;
        border-bottom: 1px solid #dcdfe5;
        overflow-x: auto;
        padding-bottom: 6px;
    }

    .tab {
        padding: 8px 12px;
        font-size: 13px;
        border-radius: 999px;
        border: none;
        background: transparent;
        color: #555;
        cursor: pointer;
        white-space: nowrap;
        margin-bottom: 6px;
        transition: all 0.2s;
    }

    .tab:hover {
        background: rgba(17, 17, 17, 0.05);
    }

    .tab--active {
        background: rgba(17, 17, 17, 0.08);
        color: #111;
        font-weight: 600;
        border: 1px solid rgba(17, 17, 17, 0.15);
    }

    .panel {
        display: none;
        animation: fadeIn 0.18s ease-out;
    }

    .panel--active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .panel-title {
        margin: 4px 0 4px;
        font-size: 19px;
        font-weight: 600;
        color: #111;
    }

    .panel-subtitle {
        margin: 0 0 16px;
        font-size: 13px;
        color: #666;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        margin-bottom: 4px;
        color: #111;
        font-weight: 500;
    }

    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group input[type="text"],
    .form-group input[type="tel"] {
        width: 100%;
        padding: 9px 10px;
        border-radius: 6px;
        border: 1px solid #dcdfe5;
        font-size: 14px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        color: #111;
    }

    .form-group input:focus {
        border-color: #f7b500;
        box-shadow: 0 0 0 1px rgba(247, 181, 0, 0.25);
    }

    /* intl-tel-input fix */
    .iti { width: 100%; }
    .iti__flag-container { z-index: 2; }
    .iti input { width: 100%; padding-left: 52px; }

    .inline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 6px;
        flex-wrap: wrap;
    }

    .remember-me {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #555;
    }

    .remember-me input {
        margin: 0;
    }

    .privacy-check {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
        margin-top: 8px;
        color: #555;
    }

    .privacy-check input {
        margin: 0;
    }

    .privacy-link {
        font-size: 12px;
        color: #111;
        text-decoration: underline;
    }

    .link-button {
        background: none;
        border: none;
        color: #111;
        font-size: 12px;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
        transition: color 0.2s;
    }

    .link-button:hover {
        color: #f7b500;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 999px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        background: #f7b500;
        color: #111;
        width: 100%;
        margin-top: 4px;
        transition: background 0.15s ease, transform 0.05s ease, box-shadow 0.15s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .btn:hover {
        background: #ffcd33;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.18);
    }

    .btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
    }

    .btn--outline {
        background: #fff;
        color: #111;
        border: 1px solid #111;
        box-shadow: none;
    }

    .btn--outline:hover {
        background: #f5f5f5;
    }

    .status {
        font-size: 13px;
        margin: 8px 0 4px;
        min-height: 18px;
    }

    .status--error {
        color: #c0392b;
    }

    .status--success {
        color: #27ae60;
    }

    .small {
        font-size: 12px;
        color: #777;
        margin-top: 6px;
    }

    .lang-switch {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
    }

    .lang-label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #777;
    }

    .lang-btn {
        border-radius: 999px;
        border: 1px solid #dcdfe5;
        padding: 4px 10px;
        font-size: 11px;
        cursor: pointer;
        background: #fff;
        color: #555;
        transition: all 0.2s;
    }

    .lang-btn:hover {
        border-color: #111;
    }

    .lang-btn--active {
        background: #111;
        color: #fff;
        border-color: #111;
        font-weight: 600;
    }

    .lang-switch--bottom {
        margin-top: 24px;
        padding-top: 12px;
        border-top: 1px solid #dcdfe5;
        align-self: center;
    }

    .iti { width: 100% !important; }

    @media (max-width: 820px) {
        .auth-card {
            grid-template-columns: minmax(0, 1fr);
        }

        .auth-card__info {
            display: none;
        }

        .auth-card__form {
            padding: 20px 16px 22px;
        }

        .auth-page {
            padding: 60px 16px 30px;
        }
    }
</style>
@endpush

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <!-- LEFT -->
        <aside class="auth-card__info">
            <div>
                <div class="auth-logo">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('HP-Logo-White.png') }}" alt="Hello Passenger" />
                    </a>
                </div>
                <h1 data-i18n="info.title">Vos bagages. Notre expertise.</h1>
                <p data-i18n="info.subtitle">
                    Connectez-vous ou créez votre compte pour gérer vos réservations, vos services bagages et vos informations personnelles.
                </p>
                <ul>
                    <li data-i18n="info.item1">Consigne & livraison de bagages</li>
                    <li data-i18n="info.item2">Suivi de vos réservations</li>
                    <li data-i18n="info.item3">Historique des services</li>
                </ul>
            </div>
            <div class="auth-card__info-footer" data-i18n="info.footer">
                Aéroport de Paris CDG & Orly – Support : contact@hellopassenger.com
            </div>
        </aside>

        <!-- RIGHT -->
        <section class="auth-card__form">
            @if($isClientLoggedIn)
                <!-- LOGGED IN: Dashboard preview -->
                <div class="panel panel--active">
                    <h2 class="panel-title">Bonjour, {{ Auth::guard('client')->user()->prenom }}</h2>
                    <p class="panel-subtitle">
                        Retrouvez vos réservations et gérez votre espace personnel.
                    </p>

                    <a href="{{ route('client.dashboard') }}" class="btn" style="display:inline-block; text-decoration:none; text-align:center;">
                        Gérer mon compte →
                    </a>

                    <form id="logout-form" method="POST" action="{{ route('client.logout') }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="btn btn--outline" data-i18n="logout.submit">Se déconnecter</button>
                        <div id="logout-status" class="status"></div>
                    </form>

                    <p class="small" style="margin-top: 16px;" data-i18n="logout.footer">
                        Après votre déconnexion, vous pourrez vous reconnecter à tout moment avec votre email et votre mot de passe.
                    </p>
                </div>
            @else
            <div class="top-bar">
                <nav class="tabs">
                    <button class="tab tab--active" data-target="login-panel" data-i18n="tab.login">Connexion</button>
                    <button class="tab" data-target="register-panel" data-i18n="tab.register">Inscription</button>
                    <button class="tab" data-target="forgot-panel" data-i18n="tab.forgot">Mot de passe oublié</button>
                </nav>
            </div>

            <!-- LOGIN -->
            <div id="login-panel" class="panel panel--active">
                <h2 class="panel-title" data-i18n="login.title">Connexion</h2>
                <p class="panel-subtitle" data-i18n="login.subtitle">
                    Accédez à vos réservations et services.
                </p>

                <form id="login-form" method="POST" action="{{ route('auth.login.submit') }}">
                    @csrf
                    <div class="form-group">
                        <label for="login-email" data-i18n="login.emailLabel">Email</label>
                        <input type="email" id="login-email" name="email" required autocomplete="email" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="login-password" data-i18n="login.passwordLabel">Mot de passe</label>
                        <input type="password" id="login-password" name="password" required autocomplete="current-password">
                    </div>

                    <div class="form-group">
                        <label class="privacy-check">
                            <input type="checkbox" id="login-privacy" name="privacy" required>
                            <span data-i18n="login.privacy">J'accepte la politique de confidentialité</span>
                            <a href="/privacy-policy" class="privacy-link" target="_blank" rel="noopener noreferrer" data-i18n="login.privacyLink">En savoir plus</a>
                        </label>
                    </div>

                    <button type="submit" class="btn" data-i18n="login.submit">Se connecter</button>
                </form>
            </div>

            <!-- REGISTER -->
            <div id="register-panel" class="panel">
                <h2 class="panel-title" data-i18n="register.title">Créer un compte</h2>
                <p class="panel-subtitle" data-i18n="register.subtitle">
                    Réservez plus vite et suivez vos services bagages.
                </p>

                <form id="register-form" method="POST" action="{{ route('client.register') }}">
                    @csrf
                    <div class="form-group">
                        <label for="register-firstname" data-i18n="register.firstNameLabel">Prénom</label>
                        <input type="text" id="register-firstname" name="prenom" required value="{{ old('prenom') }}">
                    </div>

                    <div class="form-group">
                        <label for="register-lastname" data-i18n="register.lastNameLabel">Nom</label>
                        <input type="text" id="register-lastname" name="nom" required value="{{ old('nom') }}">
                    </div>

                    <div class="form-group">
                        <label for="register-email" data-i18n="register.emailLabel">Email</label>
                        <input type="email" id="register-email" name="email" required autocomplete="email" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="register-telephone" data-i18n="register.phoneLabel">Téléphone</label>
                        <input type="tel" id="register-telephone" name="telephone" autocomplete="tel">
                        <input type="hidden" id="register-telephone-full" name="telephone_complete">
                    </div>

                    <div class="form-group">
                        <label for="register-password" data-i18n="register.passwordLabel">Mot de passe</label>
                        <input type="password" id="register-password" name="password" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="register-password-confirm" data-i18n="register.passwordConfirmLabel">Confirmer le mot de passe</label>
                        <input type="password" id="register-password-confirm" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn" data-i18n="register.submit">Créer mon compte</button>
                    <div id="register-status" class="status"></div>
                    @if(session('register_error'))
                        <div id="register-status" class="status status--error">{{ session('register_error') }}</div>
                    @endif
                    <p class="small">
                        <span data-i18n="register.already">Déjà inscrit ?</span>
                        <button type="button" class="link-button" data-target="login-panel" data-i18n="register.toLogin">
                            Se connecter
                        </button>
                    </p>
                </form>
            </div>

            <!-- FORGOT PASSWORD -->
            <div id="forgot-panel" class="panel">
                <h2 class="panel-title" data-i18n="forgot.title">Réinitialiser votre mot de passe</h2>
                <p class="panel-subtitle" data-i18n="forgot.subtitle">
                    Saisissez votre email pour recevoir un lien de réinitialisation.
                </p>

                <form id="forgot-form">
                    @csrf
                    <div class="form-group">
                        <label for="forgot-email" data-i18n="forgot.emailLabel">Email</label>
                        <input type="email" id="forgot-email" name="email" required autocomplete="email">
                    </div>

                    <button type="submit" class="btn" data-i18n="forgot.submit">Envoyer le lien</button>
                    <div id="forgot-status" class="status"></div>
                    <p class="small">
                        <span data-i18n="forgot.remember">Vous vous souvenez de votre mot de passe ?</span>
                        <button type="button" class="link-button" data-target="login-panel" data-i18n="forgot.toLogin">
                            Retour à la connexion
                        </button>
                    </p>
                </form>
            </div>
            @endif

            <!-- LANGUAGE SWITCH -->
            <div class="lang-switch lang-switch--bottom">
                <span class="lang-label">LANG</span>
                <a href="{{ route('set-language', ['lang' => 'fr']) }}" class="lang-btn {{ $currentLang === 'fr' ? 'lang-btn--active' : '' }}">FR</a>
                <a href="{{ route('set-language', ['lang' => 'en']) }}" class="lang-btn {{ $currentLang === 'en' ? 'lang-btn--active' : '' }}">EN</a>
            </div>

        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script>
(function() {
    'use strict';

    // Language handling
    let currentLang = '{{ $currentLang }}';

    function t(key, fallback) {
        if (window.translateKey && typeof window.translateKey === 'function') {
            return window.translateKey(key, fallback);
        }
        if (window.translations && window.translations[currentLang] && window.translations[currentLang][key]) {
            return window.translations[currentLang][key];
        }
        return fallback || key;
    }

    function applyTranslations() {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            el.textContent = t(key, el.textContent);
        });
    }

    applyTranslations();

    // Tab switching
    const tabs = document.querySelectorAll('.tab, .link-button[data-target]');
    const panels = document.querySelectorAll('.panel');

    function showPanel(targetId) {
        panels.forEach(p => p.classList.toggle('panel--active', p.id === targetId));
        document.querySelectorAll('.tab').forEach(tab =>
            tab.classList.toggle('tab--active', tab.dataset.target === targetId)
        );
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.target;
            if (target) showPanel(target);
        });
    });

    // Phone input
    const phoneInput = document.getElementById('register-telephone');
    if (phoneInput && typeof intlTelInput !== 'undefined') {
        window.itiInstance = intlTelInput(phoneInput, {
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
            preferredCountries: ['fr', 'us', 'gb'],
            separateDialCode: true
        });

        // Inject full phone number with country code on form submit
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', function() {
                const fullNumber = window.itiInstance.getNumber();
                document.getElementById('register-telephone-full').value = fullNumber;
            });
        }
    }

    // Forgot password form AJAX
    const forgotForm = document.getElementById('forgot-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('forgot-status');
            statusEl.textContent = t('login.status.loading', 'Envoi en cours...');
            statusEl.className = 'status';

            const formData = new FormData(forgotForm);
            const email = formData.get('email');

            try {
                const response = await fetch('{{ route('client.forgot-password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (response.ok) {
                    statusEl.textContent = t('forgot.status.success', 'Si cet email existe, un lien a été envoyé.');
                    statusEl.className = 'status status--success';
                } else {
                    statusEl.textContent = data.message || t('forgot.status.error', 'Erreur lors de l\'envoi.');
                    statusEl.className = 'status status--error';
                }
            } catch (err) {
                statusEl.textContent = t('forgot.status.error', 'Erreur réseau.');
                statusEl.className = 'status status--error';
            }
        });
    }

    // Auto-show panels based on session/errors
    @if(session('from_register'))
        showPanel('register-panel');
    @elseif(session('login_error') && $errors->any())
        showPanel('login-panel');
    @elseif(session('forgot_password_sent'))
        showPanel('forgot-panel');
    @endif

    // Handle URL hash for panel
    if (window.location.hash === '#register-panel') {
        showPanel('register-panel');
    }

})();
</script>
@endpush
