<!-- Previous (original) auth modals found in `_public_html/resources/views/Front/header-front.blade.php` -->

<!-- intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css"/>
<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>

<style>
    /* Fallback styles for the loader animation even if Tailwind isn't ready yet */
    @keyframes hpProgress {
        0% { background-position: 0% 50%; width: 0%; }
        50% { width: 100%; background-position: 100% 50%; }
        100% { background-position: 0% 50%; width: 0%; }
    }
    .hp-progress {
        animation: hpProgress 2s infinite linear;
    }

    /* Ensure modal labels are always readable on black background */
    #loginModal label,
    #registerModal label,
    #forgotPasswordModal label,
    #loginErrorModal label {
        color: #ffffff !important;
    }
    
    /* intl-tel-input dark theme */
    #registerModal .iti__country-list {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
    }
    #registerModal .iti__country {
        color: #ffffff !important;
    }
    #registerModal .iti__country:hover {
        background-color: #374151 !important;
    }
    #registerModal .iti__highlight {
        background-color: #f9c52d !important;
    }
</style>

<!-- Loading animation (used by previous modals) -->
<div id="loader"
     class="fixed inset-0 z-[10050] hidden flex flex-col items-center justify-center"
     style="background-color: rgba(0,0,0,0.92);"
     role="status" aria-live="polite" aria-label="Loading">
    <div class="mb-8">
        <img src="{{ asset('HP-Logo-White.png') }}" alt="HelloPassenger" class="luxe-logo-img w-auto mx-auto">
    </div>
    <div class="w-80 h-3 bg-gray-700 rounded-full overflow-hidden mb-6">
        <div class="h-full hp-progress"
             style="background: linear-gradient(90deg, #f9c52d, #f9c52d, #f9c52d); background-size: 200% 200%;"></div>
    </div>
    <div class="text-[#f9c52d] font-bold tracking-widest text-xl flex items-center">
        <span class="animate-pulse">CHARGEMENT</span>
        <span class="ml-2 animate-pulse">.</span>
        <span class="animate-pulse">.</span>
        <span class="animate-pulse">.</span>
    </div>
</div>

<!-- Modal LOGIN (previous) -->
<div id="loginModal"
     class="fixed inset-0 z-[10000] hidden items-center justify-center p-4"
     style="background-color: rgba(0,0,0,0.92);"
     role="dialog" aria-modal="true" aria-labelledby="loginModalTitle" tabindex="-1">
    <div class="w-full max-w-md p-8 rounded-2xl shadow-2xl relative border-2 border-[#f9c52d]"
         style="background-color: #000;">
        <button id="closeModal" class="absolute top-4 right-4 text-gray-400 text-2xl font-bold hover:text-white transition-colors" aria-label="Close login modal">&times;</button>

        <div class="text-center mb-6">
            <img src="{{ asset('HP-Logo-White.png') }}" alt="HelloPassenger" class="luxe-logo-img w-auto mx-auto mb-4">
            <h2 id="loginModalTitle" class="text-3xl font-bold mb-2" style="color: #ffffff !important;" data-i18n="login_title">Se connecter</h2>
            <p class="text-gray-400" data-i18n="login_subtitle">Accédez à votre compte</p>
        </div>

        @if(session('guest_login_attempt'))
            <div class="bg-blue-950/30 border border-blue-400/30 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-100 mb-3">
                    <strong>Vous avez déjà passé une commande avec cet email en tant qu'invité.</strong><br>
                    Pour vous connecter, vous devez d'abord créer un compte ou recevoir un mot de passe.
                </p>
                <div class="space-y-2">
                    <form method="POST" action="{{ route('client.send-generated-password') }}" class="inline-block w-full" id="guestPasswordForm">
                        @csrf
                        <input type="hidden" name="email" id="guestEmailInput" value="{{ session('guest_email') ?? old('email') }}">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition-all text-sm mb-2">
                            Recevoir un mot de passe par email
                        </button>
                    </form>
                    <button type="button" id="switchToRegisterFromGuest" class="w-full bg-gray-600 text-white py-2 rounded-lg font-semibold hover:bg-gray-700 transition-all text-sm">
                        Créer un compte avec mon propre mot de passe
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.login.submit') }}" class="space-y-5" novalidate id="loginForm">
            @csrf
            <div>
                <label for="loginEmail" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="your_email">VOTRE ADRESSE EMAIL : <span class="text-red-500">*</span></label>
                <input id="loginEmail" name="email" type="email" value="{{ session('guest_email') ?? old('email') }}"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                       required autocomplete="email" />
            </div>
            <div>
                <label for="loginPassword" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="your_password">VOTRE MOT DE PASSE : <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input id="loginPassword" name="password" type="password"
                           class="w-full px-4 py-3 pr-12 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                           required autocomplete="current-password" />
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-[#f9c52d] transition-colors" onclick="togglePasswordVisibility('loginPassword', this)" aria-label="Voir le mot de passe">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <button type="button" id="forgotPasswordBtn" class="text-gray-300 font-bold hover:text-[#f9c52d] transition-colors underline bg-transparent border-none cursor-pointer" data-i18n="forgot_password">
                    Mot de passe oublié ?
                </button>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" style="accent-color:#f9c52d;" />
                    <span class="text-gray-400" data-i18n="remember_me">Rester connecté(e)</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-[#f9c52d] text-white py-4 rounded-full font-bold hover:bg-[#f9c52d] transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3 text-lg" data-i18n="login_btn">
                SE CONNECTER
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-400 mb-3" data-i18n="no_account">Pas encore de compte ?</p>
            <button id="openRegister" class="w-full bg-gray-700 text-white py-3 rounded-full font-bold hover:bg-gray-600 transition-all duration-200 border-2 border-gray-700" type="button" data-i18n="create_account">
                CRÉER UN COMPTE →
            </button>
        </div>
    </div>
</div>

<!-- Modal REGISTER (previous) -->
<div id="registerModal"
     class="fixed inset-0 z-[10000] hidden items-center justify-center p-4"
     style="background-color: rgba(0,0,0,0.92);"
     role="dialog" aria-modal="true" aria-labelledby="registerModalTitle" tabindex="-1">
    <div class="w-full max-w-md p-8 rounded-2xl shadow-2xl relative border-2 border-[#f9c52d] max-h-[90vh] overflow-y-auto"
         style="background-color: #000;">
        <button id="closeRegisterModal" class="absolute top-4 right-4 text-gray-400 text-2xl font-bold hover:text-white transition-colors" aria-label="Close register modal">&times;</button>

        <div class="text-center mb-6">
            <img src="{{ asset('HP-Logo-White.png') }}" alt="HelloPassenger" class="luxe-logo-img w-auto mx-auto mb-4">
            <h2 id="registerModalTitle" class="text-3xl font-bold text-white mb-2" data-i18n="register_title">Créer un compte</h2>
            <p class="text-gray-400" data-i18n="register_subtitle">Rejoignez HelloPassenger</p>
        </div>

        <!-- Error messages section - must be at the top -->
        @if(session('from_register') && $errors->any())
            <div class="bg-red-900/30 border-2 border-red-500 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-red-300 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('client.register') }}" class="space-y-4" novalidate id="registerForm">
            @csrf

            <div>
                <label for="registerNom" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="nom">NOM : <span class="text-red-500">*</span></label>
                <input id="registerNom" name="nom" type="text" value="{{ old('nom') }}"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                       required />
            </div>

            <div>
                <label for="registerPrenom" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="prenom">PRÉNOM : <span class="text-red-500">*</span></label>
                <input id="registerPrenom" name="prenom" type="text" value="{{ old('prenom') }}"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                       required />
            </div>

            <div>
                <label for="registerEmail" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="email">ADRESSE EMAIL : <span class="text-red-500">*</span></label>
                <input id="registerEmail" name="email" type="email" value="{{ old('email') }}"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                       required />
            </div>

            <div>
                <label for="registerTelephone" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="telephone">TÉLÉPHONE :</label>
                <input id="registerTelephone" name="telephone" type="text" value="{{ old('telephone') }}"
                       placeholder="+33 6 12 34 56 78"
                       autocomplete="off"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all" />
                <p class="text-xs text-gray-400 mt-1" data-i18n="phone_country_code_hint">⚠️ Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France, +230 pour Maurice)</p>
            </div>

            <div>
                <label for="registerPassword" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="password">MOT DE PASSE : <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input id="registerPassword" name="password" type="password"
                           class="w-full px-4 py-3 pr-12 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                           required autocomplete="new-password" />
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-[#f9c52d] transition-colors" onclick="togglePasswordVisibility('registerPassword', this)" aria-label="Voir le mot de passe">
                        <svg class="w-5 h-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="registerPasswordConfirm" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="confirm_password">CONFIRMER MOT DE PASSE : <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input id="registerPasswordConfirm" name="password_confirmation" type="password"
                           class="w-full px-4 py-3 pr-12 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                           required />
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-[#f9c52d] transition-colors" onclick="togglePasswordVisibility('registerPasswordConfirm', this)" aria-label="Voir le mot de passe">
                        <svg class="w-5 h-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#f9c52d] text-[#212121] py-4 rounded-full font-bold hover:bg-[#f9c52d] transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3 text-lg mt-6" data-i18n="create_my_account">
                CRÉER MON COMPTE
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-400 mb-3" data-i18n="already_account">Déjà un compte ?</p>
            <button id="goToLoginBtn" class="w-full bg-gray-700 text-white py-3 rounded-full font-bold hover:bg-gray-600 transition-all duration-200 border-2 border-gray-700" type="button" data-i18n="login_btn">
                SE CONNECTER
            </button>
        </div>
    </div>
</div>

<!-- Modal Erreur connexion (previous) -->
<div id="loginErrorModal"
     class="fixed inset-0 z-[10060] hidden items-center justify-center"
     style="background-color: rgba(0,0,0,0.92);"
     role="dialog" aria-modal="true" aria-labelledby="loginErrorTitle" tabindex="-1">
    <div class="bg-white w-full max-w-sm p-8 rounded-2xl shadow-2xl relative text-center">
        <div class="text-red-500 text-5xl mb-4">⚠️</div>
        <h3 id="loginErrorTitle" class="text-2xl font-bold text-[#212121] mb-4" data-i18n="login_error_title">Erreur de connexion</h3>
        <div class="mb-6 text-left">
            @if($errors->has('email'))
                <p class="mb-2 text-red-600 font-semibold text-sm">Email ou mot de passe incorrect</p>
                <p class="text-gray-600 text-sm">{{ $errors->first('email') }}</p>
            @elseif($errors->has('password'))
                <p class="mb-2 text-red-600 font-semibold text-sm">Mot de passe incorrect</p>
                <p class="text-gray-600 text-sm">{{ $errors->first('password') }}</p>
            @else
                <p class="mb-2 text-red-600 font-semibold text-sm" data-i18n="login_error_message">Identifiants invalides — veuillez réessayer.</p>
            @endif
        </div>
        <div class="mb-4 p-3 bg-yellow-50 rounded-lg text-left">
            <p class="text-xs text-gray-600 mb-1"><strong>Conseils :</strong></p>
            <ul class="text-xs text-gray-600 list-disc list-inside space-y-1">
                <li>Vérifiez que vous copiez bien le mot de passe complet</li>
                <li>Assurez-vous qu'il n'y a pas d'espaces avant ou après</li>
                <li>Le mot de passe est sensible à la casse (majuscules/minuscules)</li>
            </ul>
        </div>
        <button id="closeLoginError" class="bg-[#f9c52d] text-[#212121] px-8 py-3 rounded-full font-bold hover:bg-[#f9c52d] transition-all duration-200 shadow-lg" data-i18n="close_btn">
            Fermer
        </button>
    </div>
</div>

<!-- Modal Mot de passe oublié (previous) -->
<div id="forgotPasswordModal"
     class="fixed inset-0 z-[10000] hidden items-center justify-center p-4"
     style="background-color: rgba(0,0,0,0.92);"
     role="dialog" aria-modal="true" aria-labelledby="forgotPasswordTitle" tabindex="-1">
    <div class="w-full max-w-md p-8 rounded-2xl shadow-2xl relative border-2 border-[#f9c52d]"
         style="background-color: #000;">
        <button id="closeForgotPasswordModal" class="absolute top-4 right-4 text-gray-400 text-2xl font-bold hover:text-white transition-colors" aria-label="Close forgot password modal">&times;</button>

        <div class="text-center mb-6">
            <img src="{{ asset('HP-Logo-White.png') }}" alt="HelloPassenger" class="luxe-logo-img w-auto mx-auto mb-4">
            <h2 id="forgotPasswordTitle" class="text-3xl font-bold text-white mb-2" data-i18n="forgot_password_title">Mot de passe oublié</h2>
            <p class="text-gray-400" data-i18n="forgot_password_subtitle">Entrez votre email pour recevoir un nouveau mot de passe</p>
        </div>

        <form id="forgotPasswordForm" method="POST" action="{{ route('client.forgot-password') }}" class="space-y-4" novalidate>
            @csrf
            <div id="forgotPasswordMessage" class="hidden mb-4"></div>

            <div>
                <label for="forgotPasswordEmail" class="block text-sm font-bold text-gray-300 mb-2" data-i18n="your_email">ADRESSE EMAIL : <span class="text-red-500">*</span></label>
                <input id="forgotPasswordEmail" name="email" type="email" value="{{ old('email') }}"
                       class="w-full px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg focus:border-[#f9c52d] focus:ring-2 focus:ring-[#f9c52d] focus:outline-none transition-all"
                       required autocomplete="email" />
                <div id="forgotPasswordError" class="text-red-600 text-sm mt-2 font-medium hidden"></div>
            </div>

            <button type="submit" id="forgotPasswordSubmitBtn" class="w-full bg-[#f9c52d] text-[#212121] py-4 rounded-full font-bold hover:bg-[#f9c52d] transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3 text-lg mt-6" data-i18n="send_password">
                <span id="forgotPasswordBtnText">ENVOYER LE MOT DE PASSE</span>
                <svg id="forgotPasswordBtnSpinner" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg id="forgotPasswordBtnIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
            </button>
        </form>

        <div class="mt-6 text-center">
            <button type="button" id="backToLoginFromForgot" class="text-gray-400 hover:text-[#f9c52d] transition-colors underline" data-i18n="back_to_login">
                Retour à la connexion
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        function ensureTailwindLoaded() {
            if (window.__hpTailwindLoaded) return Promise.resolve();
            if (window.__hpTailwindLoading) return window.__hpTailwindLoading;

            window.__hpTailwindLoading = new Promise((resolve) => {
                window.tailwind = window.tailwind || {};
                window.tailwind.config = {
                    corePlugins: { preflight: false }
                };

                const s = document.createElement('script');
                s.src = 'https://cdn.tailwindcss.com';
                s.onload = function () {
                    window.__hpTailwindLoaded = true;
                    resolve();
                };
                s.onerror = function () { resolve(); };
                document.head.appendChild(s);
            });

            return window.__hpTailwindLoading;
        }

        const loader = document.getElementById('loader');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const loginErrorModal = document.getElementById('loginErrorModal');
        const closeLoginBtn = document.getElementById('closeModal');
        const closeRegisterBtn = document.getElementById('closeRegisterModal');
        const openRegisterBtn = document.getElementById('openRegister');
        const goToLoginBtn = document.getElementById('goToLoginBtn');
        const switchToRegisterFromGuest = document.getElementById('switchToRegisterFromGuest');
        const forgotPasswordModal = document.getElementById('forgotPasswordModal');
        const forgotPasswordBtn = document.getElementById('forgotPasswordBtn');
        const closeForgotPasswordModal = document.getElementById('closeForgotPasswordModal');
        const backToLoginFromForgot = document.getElementById('backToLoginFromForgot');
        const closeLoginError = document.getElementById('closeLoginError');

        function show(el, display) {
            if (!el) return;
            el.classList.remove('hidden');
            if (display) el.style.display = display;
        }
        function hide(el) {
            if (!el) return;
            el.classList.add('hidden');
            el.style.display = 'none';
        }
        function lockScroll(lock) {
            document.body.style.overflow = lock ? 'hidden' : '';
        }

        function openLoginModal() {
            ensureTailwindLoaded().then(() => {
                hide(registerModal);
                hide(forgotPasswordModal);
                if (loader) show(loader, 'flex');
                lockScroll(true);
                var safety = setTimeout(function() {
                    if (loader && !loader.classList.contains('hidden')) {
                        hide(loader);
                        show(loginModal, 'flex');
                    }
                }, 3000);
                setTimeout(() => {
                    clearTimeout(safety);
                    hide(loader);
                    show(loginModal, 'flex');
                    const first = loginModal?.querySelector('input');
                    if (first) first.focus();
                }, 80);
            }).catch(function() {
                if (loader) hide(loader);
                show(loginModal, 'flex');
            });
        }

        function openRegisterModal() {
            ensureTailwindLoaded().then(() => {
                hide(loginModal);
                hide(forgotPasswordModal);
                if (loader) show(loader, 'flex');
                lockScroll(true);
                var safety = setTimeout(function() {
                    if (loader && !loader.classList.contains('hidden')) {
                        hide(loader);
                        show(registerModal, 'flex');
                    }
                }, 3000);
                setTimeout(() => {
                    clearTimeout(safety);
                    hide(loader);
                    show(registerModal, 'flex');
                    const first = registerModal?.querySelector('input');
                    if (first) first.focus();
                }, 120);
            }).catch(function() {
                if (loader) hide(loader);
                show(registerModal, 'flex');
            });
        }

        function closeAuthModals() {
            hide(loader);
            hide(loginModal);
            hide(registerModal);
            hide(forgotPasswordModal);
            hide(loginErrorModal);
            lockScroll(false);
        }

        // Expose expected global functions for Hostinger header + booking flow
        window.openLoginModal = openLoginModal;
        window.openRegisterModal = openRegisterModal;
        window.closeAuthModals = closeAuthModals;

        // Toggle password visibility
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            // Change icon
            if (isPassword) {
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>';
            } else {
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
            }
        }
        window.togglePasswordVisibility = togglePasswordVisibility;

        if (closeLoginBtn) closeLoginBtn.addEventListener('click', closeAuthModals);
        if (closeRegisterBtn) closeRegisterBtn.addEventListener('click', closeAuthModals);
        if (closeForgotPasswordModal) closeForgotPasswordModal.addEventListener('click', closeAuthModals);
        if (closeLoginError) closeLoginError.addEventListener('click', function () {
            hide(loginErrorModal);
        });

        if (openRegisterBtn) openRegisterBtn.addEventListener('click', openRegisterModal);
        if (goToLoginBtn) goToLoginBtn.addEventListener('click', openLoginModal);
        if (switchToRegisterFromGuest) switchToRegisterFromGuest.addEventListener('click', openRegisterModal);
        if (forgotPasswordBtn) {
            forgotPasswordBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const email = document.getElementById('loginEmail')?.value || '';
                ensureTailwindLoaded().then(() => {
                    hide(loginModal);
                    hide(registerModal);
                    if (loader) show(loader, 'flex');
                    lockScroll(true);
                    var safety = setTimeout(function() {
                        if (loader && !loader.classList.contains('hidden')) {
                            hide(loader);
                            show(forgotPasswordModal, 'flex');
                        }
                    }, 3000);
                    setTimeout(() => {
                        clearTimeout(safety);
                        hide(loader);
                        show(forgotPasswordModal, 'flex');
                        const forgotEmailInput = document.getElementById('forgotPasswordEmail');
                        if (forgotEmailInput && email) forgotEmailInput.value = email;
                        forgotEmailInput?.focus?.();
                    }, 180);
                }).catch(function() {
                    if (loader) hide(loader);
                    show(forgotPasswordModal, 'flex');
                });
            });
        }
        if (backToLoginFromForgot) backToLoginFromForgot.addEventListener('click', openLoginModal);

        // Forgot password AJAX submit (as before)
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
            forgotPasswordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('forgotPasswordSubmitBtn');
                const btnText = document.getElementById('forgotPasswordBtnText');
                const btnSpinner = document.getElementById('forgotPasswordBtnSpinner');
                const btnIcon = document.getElementById('forgotPasswordBtnIcon');
                const messageDiv = document.getElementById('forgotPasswordMessage');
                const errorDiv = document.getElementById('forgotPasswordError');
                const emailInput = document.getElementById('forgotPasswordEmail');

                if (messageDiv) messageDiv.classList.add('hidden');
                if (errorDiv) { errorDiv.classList.add('hidden'); errorDiv.textContent = ''; }

                if (submitBtn) submitBtn.disabled = true;
                if (btnText) btnText.textContent = 'ENVOI EN COURS...';
                if (btnIcon) btnIcon.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');

                try {
                    const formData = new FormData(forgotPasswordForm);
                    const response = await fetch(forgotPasswordForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        if (messageDiv) {
                            messageDiv.className = 'bg-green-50 border-2 border-green-500 rounded-lg p-4 mb-4';
                            messageDiv.innerHTML = '<p class="text-sm text-green-800 font-semibold">' + (data.message || 'Email envoyé avec succès !') + '</p>';
                            messageDiv.classList.remove('hidden');
                        }
                        if (emailInput) emailInput.value = '';
                    } else {
                        let errorMessage = (data && data.message) ? data.message : 'Une erreur est survenue. Veuillez réessayer.';
                        if (data && data.errors && data.errors.email) {
                            errorMessage = Array.isArray(data.errors.email) ? data.errors.email[0] : data.errors.email;
                        }
                        if (errorDiv) { errorDiv.textContent = errorMessage; errorDiv.classList.remove('hidden'); }
                    }
                } catch (error) {
                    if (errorDiv) { errorDiv.textContent = 'Une erreur réseau est survenue. Veuillez réessayer.'; errorDiv.classList.remove('hidden'); }
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnText) btnText.textContent = 'ENVOYER LE MOT DE PASSE';
                    if (btnIcon) btnIcon.classList.remove('hidden');
                    if (btnSpinner) btnSpinner.classList.add('hidden');
                }
            });
        }

        // Click outside closes
        [loginModal, registerModal, forgotPasswordModal].forEach((m) => {
            if (!m) return;
            m.addEventListener('click', (e) => {
                if (e.target === m) closeAuthModals();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeAuthModals();
        });

        // Auto-open on validation errors (same intent as previous header)
        @if(session('from_register'))
            openRegisterModal();
        @elseif(session('login_error') && $errors->any())
            openLoginModal();
            if (loginErrorModal) {
                loginErrorModal.classList.remove('hidden');
                loginErrorModal.style.display = 'flex';
            }
        @endif

        // Replay any queued click that happened before this script loaded
        const q = window.__hpAuthQueue || [];
        window.__hpAuthQueue = [];
        q.forEach((t) => { if (t === 'register') openRegisterModal(); else openLoginModal(); });

        // Initialize intl-tel-input for register phone field (like in payment.blade.php)
        const phoneInput = document.getElementById('registerTelephone');
        let itiInstance = null;

        if (phoneInput && window.intlTelInput) {
            itiInstance = window.intlTelInput(phoneInput, {
                initialCountry: 'auto',
                geoIpLookup: function(callback) {
                    fetch('https://ipapi.co/json')
                        .then(function(res) { return res.json(); })
                        .then(function(data) { callback(data.country_code); })
                        .catch(function() { callback('fr'); });
                },
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
                preferredCountries: ['fr', 'mu', 'be', 'ch', 'ca'],
                autoPlaceholder: 'aggressive',
                separateDialCode: false
            });
        }
    })();
</script>

