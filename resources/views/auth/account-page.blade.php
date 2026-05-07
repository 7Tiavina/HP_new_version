@extends('layouts.front')

@php
    $currentLang = session('app_language', 'fr');
    $isClientLoggedIn = Auth::guard('client')->check();
    $googlePlacesApiKey = config('services.google.places_api_key');
@endphp

@section('title', $currentLang === 'en' ? 'My Account — Hello Passenger' : 'Mon Compte — Hello Passenger')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
<style>
    .auth-page {
        font-family: 'Space Grotesk', sans-serif !important;
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
        height: 80px;
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

    .auth-card__info ul li a:hover {
        text-decoration: underline !important;
        opacity: 1;
    }

    .auth-card__info-footer a:hover {
        opacity: 0.8;
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

    /* intl-tel-input fix - override CDN styles */
    .iti input { padding-left: 40px !important; padding-right: 10px !important; }

    /* Password toggle */
    .password-input-wrapper { position: relative; }
    .password-input-wrapper input { padding-right: 40px; }
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .password-toggle:hover { color: #374151; }
    .password-toggle .eye-closed { display: none; }
    .password-toggle .eye-open { display: block; }
    .password-toggle.active .eye-closed { display: block; }
    .password-toggle.active .eye-open { display: none; }

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

        .tabs {
            gap: 4px;
        }

        .tab {
            padding: 6px 10px;
            font-size: 12px;
        }
    }

    @media (max-width: 380px) {
        .tab {
            padding: 6px 8px;
            font-size: 11px;
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
                    <a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/">
                        <img src="{{ asset('HP-Logo-White.png') }}" alt="Hello Passenger" style="max-height: 160px; width: auto;" />
                    </a>
                </div>
                <h1 data-i18n="info.title">Vos bagages. Notre expertise.</h1>
                <p data-i18n="info.subtitle">
                    Connectez-vous ou créez votre compte pour gérer vos réservations, vos services bagages et vos informations personnelles.
                </p>
                <ul>
                    <li><a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/consigne-bagages/" style="color: inherit; text-decoration: none;" data-i18n="info.item1">Consigne à Bagages</a></li>
                    <li><a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/transfert-livraison-bagages/" style="color: inherit; text-decoration: none;" data-i18n="info.item2">Transfert & Livraison Bagages</a></li>
                    <li><a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/assistance-personnalisee/" style="color: inherit; text-decoration: none;" data-i18n="info.item3">Assistance Personnalisée</a></li>
                    <li><a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/bdm-travel-store/" style="color: inherit; text-decoration: none;" data-i18n="info.item4">BDM Travel Store</a></li>
                    <li><a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/services-facilitateurs-de-voyage/" style="color: inherit; text-decoration: none;" data-i18n="info.item5">Services Pratiques</a></li>
                </ul>
            </div>
            <div class="auth-card__info-footer">
                <a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/nous-localiser/" style="color: inherit; text-decoration: underline;" data-i18n="info.airport">Aéroport de Paris CDG & Orly</a> – <span data-i18n="info.support">Support :</span> <a href="mailto:contact@hellopassenger.com" style="color: inherit; text-decoration: underline;" data-i18n="info.email">contact@hellopassenger.com</a>
            </div>
        </aside>

        <!-- RIGHT -->
        <section class="auth-card__form">
            <div class="back-navigation" style="margin-bottom: 16px;">
                @php
                    $backUrl = session('from_payment') ? route('payment') : route('form-consigne');
                    $backText = $currentLang === 'en' ? 'Back' : 'Retour';
                @endphp
                <a href="{{ $backUrl }}" class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #555; font-weight: 500; font-size: 13px;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7" />
                    </svg>
                    <span>{{ $backText }}</span>
                </a>
            </div>

            @if($isClientLoggedIn)
                <!-- LOGGED IN: Dashboard preview -->
                <div class="panel panel--active">
                    <h2 class="panel-title"><span data-i18n="dashboard.greeting">Bonjour</span>, {{ Auth::guard('client')->user()->prenom }}</h2>
                    <p class="panel-subtitle" data-i18n="dashboard.subtitle">
                        Retrouvez vos réservations et gérez votre espace personnel.
                    </p>

                    <a href="{{ route('client.dashboard') }}" class="btn" style="display:inline-block; text-decoration:none; text-align:center;" data-i18n="dashboard.manage">
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
                    @if(request()->query('from') === 'link-form')
                    <input type="hidden" name="redirect_link_form" value="1">
                    @endif
                    <div class="form-group">
                        <label for="login-email" data-i18n="login.emailLabel">Email</label>
                        <input type="email" id="login-email" name="email" required autocomplete="email" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="login-password" data-i18n="login.passwordLabel">Mot de passe</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="login-password" name="password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="privacy-check">
                            <input type="checkbox" id="login-privacy" name="privacy" required>
                            <span data-i18n="login.privacy">J'accepte la politique de confidentialité</span>
                            <a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/mentions-legales/" class="privacy-link" target="_blank" rel="noopener noreferrer" data-i18n="login.privacyLink">En savoir plus</a>
                        </label>
                    </div>

                    <button type="submit" class="btn" data-i18n="login.submit">Se connecter</button>
                    @if($errors->any() && !session('from_register'))
                        <div class="status status--error">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if(session('guest_login_attempt'))
                        <div class="status status--info">Utilisez votre mot de passe ou demandez une réinitialisation.</div>
                    @endif
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
                    @if(request()->query('from') === 'link-form')
                    <input type="hidden" name="redirect_link_form" value="1">
                    @endif
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
                        <label for="register-adresse" data-i18n="register.addressLabel">Adresse</label>
                        <input type="text" id="register-adresse" name="adresse" autocomplete="street-address" maxlength="255" data-i18n-placeholder="register.addressPlaceholder" placeholder="Commencez à taper votre adresse...">
                        <input type="hidden" id="register-adresse-complete" name="adresse_complete">
                    </div>

                    <div class="form-group">
                        <label for="register-password" data-i18n="register.passwordLabel">Mot de passe</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="register-password" name="password" required autocomplete="new-password">
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-password-confirm" data-i18n="register.passwordConfirmLabel">Confirmer le mot de passe</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="register-password-confirm" name="password_confirmation" required autocomplete="new-password">
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="privacy-check">
                            <input type="checkbox" id="register-privacy" name="privacy" required>
                            <span data-i18n="register.privacy">J'accepte la politique de confidentialité</span>
                            <a href="{{ config('app.front_url') }}{{ $currentLang === 'en' ? '/en' : '' }}/mentions-legales/" class="privacy-link" target="_blank" rel="noopener noreferrer" data-i18n="register.privacyLink">En savoir plus</a>
                        </label>
                    </div>

                    <button type="submit" class="btn" data-i18n="register.submit">Créer mon compte</button>
                    <div id="register-status" class="status"></div>
                    @if($errors->any() && session('from_register'))
                        <div class="status status--error">
                            @foreach($errors->all() as $error)
                                <p style="margin: 0;">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
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

        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script>
(function() {
    'use strict';

    // Password toggle
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const wrapper = this.closest('.password-input-wrapper');
            const input = wrapper.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.add('active');
            } else {
                input.type = 'password';
                this.classList.remove('active');
            }
        });
    });

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
        // Also translate placeholders
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = t(key, el.placeholder);
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
            initialCountry: 'fr',
            autoPlaceholder: 'off',
            separateDialCode: false
        });

        // Set placeholder with country code
        phoneInput.placeholder = '+33 6 12 34 56 78';

        // Update placeholder on country change
        phoneInput.addEventListener('countrychange', function() {
            var selectedCountry = window.itiInstance.getSelectedCountryData();
            var dialCode = selectedCountry.dialCode;
            if (dialCode === '33') {
                phoneInput.placeholder = '+33 6 12 34 56 78';
            } else if (dialCode === '1') {
                phoneInput.placeholder = '+1 555 123 4567';
            } else if (dialCode === '44') {
                phoneInput.placeholder = '+44 7700 900077';
            } else {
                phoneInput.placeholder = '+' + dialCode + ' ';
            }
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

    // Google Places address autocomplete
    function initRegisterAddressAutocomplete() {
        const addressInput = document.getElementById('register-adresse');

        if (!addressInput) {
            console.error('Register address input not found');
            return;
        }

        if (!window.google || !window.google.maps || !window.google.maps.places) {
            console.error('Google Maps API not available for register');
            return;
        }

        try {
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['address'],
                componentRestrictions: { country: [] },
                fields: ['address_components', 'geometry', 'name', 'formatted_address']
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) {
                    console.log("No geometry found for the selected place");
                    return;
                }

                let street_number = '';
                let route = '';
                let city = '';
                let postal_code = '';
                let administrative_area = '';
                let country = '';

                for (let i = 0; i < place.address_components.length; i++) {
                    const component = place.address_components[i];
                    const addressType = component.types[0];

                    if (addressType === 'street_number') {
                        street_number = component.long_name;
                    } else if (addressType === 'route') {
                        route = component.long_name;
                    } else if (
                        addressType === 'locality' ||
                        addressType === 'postal_town' ||
                        addressType === 'administrative_area_level_2' ||
                        addressType === 'administrative_area_level_3' ||
                        addressType === 'sublocality_level_1' ||
                        addressType === 'sublocality'
                    ) {
                        if (!city) {
                            city = component.long_name;
                        }
                    } else if (addressType === 'postal_code') {
                        postal_code = component.long_name;
                    } else if (addressType === 'administrative_area_level_1') {
                        administrative_area = component.long_name;
                    } else if (addressType === 'country') {
                        country = component.long_name;
                    }
                }

                if (!city && administrative_area) {
                    city = administrative_area;
                }

                // Build complete address with all components
                let addressParts = [];

                // Street address
                if (street_number && route) {
                    addressParts.push(street_number + ' ' + route);
                } else if (route) {
                    addressParts.push(route);
                }

                // Postal code and city
                if (postal_code && city) {
                    addressParts.push(postal_code + ' ' + city);
                } else if (city) {
                    addressParts.push(city);
                }

                // State/Province
                if (administrative_area && administrative_area !== city) {
                    addressParts.push(administrative_area);
                }

                // Country
                if (country) {
                    addressParts.push(country);
                }

                let fullAddress = addressParts.join(', ');

                addressInput.value = fullAddress.trim();

                // Store full Google address in hidden field
                document.getElementById('register-adresse-complete').value = place.formatted_address;

                console.log('Complete address filled for register:', fullAddress);
                console.log('Address components:', {
                    street_number,
                    route,
                    postal_code,
                    city,
                    administrative_area,
                    country
                });
            });

            console.log('Google Places Autocomplete initialized for register');
        } catch (error) {
            console.error('Error initializing Google Places Autocomplete for register:', error);
        }
    }

    // Load Google Maps API and init autocomplete
    function loadRegisterGoogleMapsAPI(callback) {
        if (window.google && window.google.maps && window.google.maps.places) {
            if (callback) callback();
            return;
        }

@if($googlePlacesApiKey)
        // Check if script is already loading (prevent duplicate)
        if (document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]')) {
            // Script is loading, wait and retry
            setTimeout(function() { loadRegisterGoogleMapsAPI(callback); }, 500);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googlePlacesApiKey }}&libraries=places&language=fr&v=3.52';
        script.async = true;
        script.defer = true;

        script.onload = function() {
            console.log('Google Places API loaded for register');
            if (callback) callback();
        };

        script.onerror = function() {
            console.error('Failed to load Google Places API for register');
        };

        document.head.appendChild(script);
@else
        console.warn('Google Places API key not configured');
@endif
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.google || !window.google.maps || !window.google.maps.places) {
            loadRegisterGoogleMapsAPI(initRegisterAddressAutocomplete);
        } else {
            setTimeout(initRegisterAddressAutocomplete, 100);
        }
    });

    // Forgot password form AJAX
    const forgotForm = document.getElementById('forgot-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('forgot-status');
            statusEl.textContent = t('forgot.status.loading', 'Envoi en cours...');
            statusEl.className = 'status';

            const formData = new FormData(forgotForm);
            const email = formData.get('email');

            try {
                const response = await fetch('{{ route('client.forgot-password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    statusEl.textContent = data.message || t('forgot.status.success', 'Un nouveau mot de passe a été envoyé à votre adresse email.');
                    statusEl.className = 'status status--success';
                    showPanel('forgot-panel');
                } else {
                    // Handle validation/email not found errors
                    var errorMsg = t('forgot.status.error', 'Erreur lors de l\'envoi.');
                    if (data && data.message) {
                        errorMsg = data.message;
                    } else if (data && data.errors) {
                        var firstKey = Object.keys(data.errors)[0];
                        if (firstKey && Array.isArray(data.errors[firstKey])) {
                            errorMsg = data.errors[firstKey][0];
                        } else if (firstKey) {
                            errorMsg = data.errors[firstKey];
                        }
                    }
                    statusEl.textContent = errorMsg;
                    statusEl.className = 'status status--error';
                }
            } catch (err) {
                // Real network error (no response at all)
                console.error('Forgot password error:', err);
                statusEl.textContent = t('forgot.status.networkError', 'Impossible de contacter le serveur. Veuillez réessayer.');
                statusEl.className = 'status status--error';
            }
        });
    }

    // Auto-show panels based on session/errors
    @if(session('from_register'))
        showPanel('register-panel');
    @elseif($errors->has('email') || session('login_error') || session('guest_login_attempt'))
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
