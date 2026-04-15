@php
    $formUrl = route('form-consigne');
    $currentLang = session('app_language', 'fr');
    $pageTitle = $currentLang === 'en'
        ? 'Hello Passenger — Luggage & travel services at Paris CDG & Orly'
        : 'Hello Passenger — Transport et consigne de bagages à Paris CDG & Orly';
    $metaDescription = $currentLang === 'en'
        ? 'Hello Passenger — Luggage transport and left luggage at Paris CDG & Orly. Book online.'
        : 'Hello Passenger — Transport de bagages et consigne à Paris CDG & Orly. Réservez en ligne.';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', $metaDescription)">
    <link rel="icon" type="image/png" href="{{ asset('favicon-hellopassenger.png') }}">
    <title>@yield('title', $pageTitle)</title>

    <script>
        (function () {
            // Redirect to account page instead of opening modals
            var ACCOUNT_URL = @json(route('account'));
            if (!window.openLoginModal) window.openLoginModal = function () { window.location.href = ACCOUNT_URL; };
            if (!window.openRegisterModal) window.openRegisterModal = function () { window.location.href = ACCOUNT_URL + '#register-panel'; };
        })();
    </script>
    <script>
        (function () {
            function onClick(e) {
                var a = e.target && e.target.closest ? e.target.closest('a') : null;
                if (!a) return;
                var href = (a.getAttribute('href') || '');
                var isLogin = a.classList && a.classList.contains('login-link');
                var isRegister = a.classList && a.classList.contains('register-link');
                if (isLogin || href.indexOf('#login') !== -1) {
                    e.preventDefault(); e.stopImmediatePropagation(); e.stopPropagation();
                    window.location.href = @json(route('account'));
                    return;
                }
                if (isRegister || href.indexOf('#signup') !== -1) {
                    e.preventDefault(); e.stopImmediatePropagation(); e.stopPropagation();
                    window.location.href = @json(route('account')) + '#register-panel';
                    return;
                }
            }
            document.addEventListener('click', onClick, true);
        })();

        // Redirect to account page if ?login=1 or #login in URL
        (function() {
            const alreadyOpened = sessionStorage.getItem('hp_login_modal_opened');
            const urlParams = new URLSearchParams(window.location.search);
            const hash = window.location.hash;

            if (!alreadyOpened && (urlParams.get('login') === '1' || hash === '#login')) {
                sessionStorage.setItem('hp_login_modal_opened', '1');
                window.location.href = @json(route('account'));
                
                // Supprimer les paramètres login de l'URL sans recharger la page
                if (urlParams.get('login') === '1') {
                    urlParams.delete('login');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '') + window.location.hash;
                    window.history.replaceState({}, '', newUrl);
                }
            }
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('css/acceuil-luxe.css') }}?v={{ file_exists(public_path('css/acceuil-luxe.css')) ? filemtime(public_path('css/acceuil-luxe.css')) : '1' }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}?v={{ file_exists(public_path('css/animations.css')) ? filemtime(public_path('css/animations.css')) : '1' }}">
    <script>
        // Detect device language on first visit, then always sync from server session
        (function() {
            var sessionLang = '{{ session("app_language", "fr") }}';
            var savedLang = localStorage.getItem('app_language');
            
            if (!savedLang) {
                // First visit: detect from device
                var deviceLang = navigator.language || navigator.userLanguage || '';
                var langCode = (deviceLang || '').toLowerCase().split('-')[0];
                var detected = (langCode === 'en') ? 'en' : 'fr';
                localStorage.setItem('app_language', detected);
                console.log('[LangDetect] First visit - device:', deviceLang, '=>', detected);
            } else if (sessionLang && sessionLang !== savedLang) {
                // Server session changed (user clicked language switcher): sync localStorage
                localStorage.setItem('app_language', sessionLang);
                console.log('[LangDetect] Synced from server session:', sessionLang);
            } else {
                console.log('[LangDetect] Already set:', savedLang);
            }
        })();
    </script>
    <script>
        (function() {
            function hpHideLoader() {
                var loader = document.getElementById('loader');
                if (loader) {
                    loader.classList.add('hidden');
                    loader.style.display = 'none';
                }
            }
            if (document.readyState === 'complete') {
                hpHideLoader();
            } else {
                window.addEventListener('load', hpHideLoader);
            }
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) hpHideLoader();
            });
        })();
    </script>
    <script src="{{ asset('js/translations-simple.js') }}?v={{ file_exists(public_path('js/translations-simple.js')) ? filemtime(public_path('js/translations-simple.js')) : '1' }}"></script>
    <style>
        /* Override body padding for new nav */
        body.luxe-home {
            padding: 0 !important;
            background-color: #000000;
        }
    </style>
    @stack('styles')
    @stack('head_scripts')
</head>
<body class="luxe-home">

    {{-- Nouvelle Navigation Style Hello Passenger --}}
    @include('Front.nav-front')

    <main class="luxe-main">
        @yield('content')
    </main>

    <footer class="custom-footer">
@php
    $currentLang = session('app_language', 'fr');
    $langPrefix = $currentLang === 'en' ? '/en' : '';
@endphp
        <div class="footer-grid">
            <div class="footer-col">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}" class="footer-logo-link">
                    <img src="{{ asset('logo footer.webp') }}" alt="Logo" class="footer-logo">
                </a>
                <div class="since-text" data-i18n="footer_since">depuis 1998</div>
                <p class="footer-copyright" data-i18n="footer_credits"></p>
                <p class="footer-created-by" data-i18n="footer_created_by"></p>
            </div>

            <div class="footer-col">
                <h3 data-i18n="footer_access">Plan d'accès</h3>
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" class="footer-airport-link" data-i18n="footer_cdg">Aéroport de Roissy Charles de Gaulle</a>
                <p data-i18n="footer_cdg_address">Terminal 2 – Gare TGV<br>Niveau 4</p>
                <br>
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/nous-localiser/" class="footer-airport-link" data-i18n="footer_orly">Aéroport de Paris Orly</a>
                <p data-i18n="footer_orly_address">Orly 3<br>Niveau Arrivées<br>Porte 33a</p>
            </div>

            <div class="footer-col">
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/contact/" class="footer-contact-link" data-i18n="footer_contact">Contactez-nous</a>
                <a href="tel:+33134385898" class="yellow-text">+33 (0)1 34 38 58 98</a>
                <h3 data-i18n="footer_email">Email</h3>
                <a href="mailto:contact@hellopassenger.com" class="yellow-text">contact@hellopassenger.com</a>
                <h3 style="margin-top:20px;" data-i18n="footer_follow">Suivez-nous</h3>
                <div class="socials" style="justify-content: flex-start; gap: 15px;">
                    <a href="https://www.facebook.com/hello.passenger.officiel/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" style="fill:#ffc439; width:20px; height:20px;">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/hellopassenger_officiel/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" style="fill:#ffc439; width:20px; height:20px;">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h3 data-i18n="footer_links">Liens Rapides</h3>
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/faq/" data-i18n="footer_faq">FAQ</a>
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/services/" data-i18n="footer_services">Services</a>
                <a href="https://darkseagreen-mongoose-687346.hostingersite.com{{ $langPrefix }}/mentions-legales/" data-i18n="footer_legal">Mentions Légales</a>
                <br>
                <a href="{{ $formUrl }}" class="footer-reserve"><span class="reserve-text" data-i18n="footer_book">Réservez</span></a>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var ACCOUNT_URL = @json(route('account'));
            var DASHBOARD_URL = @json(route('client.dashboard'));
            var LOGOUT_URL = @json(route('client.logout'));
            var FORM_URL = @json(route('form-consigne'));

            function csrfToken() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }
            function clearInject() {
                var el = document.querySelector('.luxe-auth-inject');
                if (!el) return;
                el.querySelectorAll('[data-hp-auth-item="1"]').forEach(function (n) { n.remove(); });
            }
            function getTranslation(key, defaultVal) {
                if (window.translateKey && typeof window.translateKey === 'function') {
                    return window.translateKey(key, defaultVal);
                }
                if (window.translations && window.currentLang) {
                    var lang = window.currentLang || 'fr';
                    if (window.translations[lang] && window.translations[lang][key]) {
                        return window.translations[lang][key];
                    }
                }
                return defaultVal;
            }
            function setLoggedOut() {
                // Show login/register links
                var inject = document.querySelector('.luxe-auth-inject');
                if (!inject) return;
                clearInject();

                // Reset Réserver button
                var btnReserve = document.getElementById('btn-reserve');
                if (btnReserve) {
                    btnReserve.href = FORM_URL;
                    btnReserve.textContent = getTranslation('btn_book', 'Réserver');
                    btnReserve.style.backgroundColor = '#FAC12E';
                    btnReserve.style.color = '#000000';
                }

                var loginText = getTranslation('login_btn', 'Se connecter');
                var registerText = getTranslation('create_account_short', "S'inscrire");

                // Create Login link
                var spanLogin = document.createElement('span');
                spanLogin.setAttribute('data-hp-auth-item', '1');
                var aLogin = document.createElement('a');
                aLogin.href = ACCOUNT_URL;
                aLogin.className = 'login-link';
                aLogin.textContent = loginText;
                aLogin.style.color = '#1a1a1a';
                spanLogin.appendChild(aLogin);
                inject.appendChild(spanLogin);

                // Create Register link
                var spanRegister = document.createElement('span');
                spanRegister.setAttribute('data-hp-auth-item', '1');
                var aRegister = document.createElement('a');
                aRegister.href = ACCOUNT_URL;
                aRegister.className = 'register-link';
                aRegister.textContent = registerText;
                aRegister.style.color = '#1a1a1a';
                spanRegister.appendChild(aRegister);
                inject.appendChild(spanRegister);
            }
            function setLoggedIn() {
                // Hide login/register links
                document.querySelectorAll('a.login-link, a.register-link').forEach(function (a) { a.style.display = 'none'; });

                var inject = document.querySelector('.luxe-auth-inject');
                if (!inject) return;
                clearInject();

                // Change Réserver button to Déconnexion (with POST form)
                var btnReserve = document.getElementById('btn-reserve');
                if (btnReserve) {
                    btnReserve.href = 'javascript:void(0)';
                    btnReserve.textContent = getTranslation('logout_btn', 'Déconnexion');
                    btnReserve.style.backgroundColor = '#1F1F1F';
                    btnReserve.style.color = '#FFFFFF';
                    btnReserve.onclick = function(e) {
                        e.preventDefault();
                        performLogout();
                    };
                }

                var myAccountText = getTranslation('header_my_dashboard', 'Mon compte');
                var logoutText = getTranslation('logout_btn', 'Déconnexion');

                // Create My Account link
                var spanAccount = document.createElement('span');
                spanAccount.setAttribute('data-hp-auth-item', '1');
                var aAccount = document.createElement('a');
                aAccount.href = DASHBOARD_URL;
                aAccount.textContent = myAccountText;
                aAccount.style.color = '#1a1a1a';
                aAccount.style.fontWeight = '600';
                spanAccount.appendChild(aAccount);
                inject.appendChild(spanAccount);

                // Create Logout button
                var spanLogout = document.createElement('span');
                spanLogout.setAttribute('data-hp-auth-item', '1');
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = LOGOUT_URL;
                form.style.display = 'inline';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrfToken();
                form.appendChild(input);
                var btn = document.createElement('button');
                btn.type = 'submit';
                btn.style.background = 'none';
                btn.style.border = 'none';
                btn.style.padding = '0';
                btn.style.cursor = 'pointer';
                btn.style.color = '#1a1a1a';
                btn.style.fontSize = '15px';
                btn.style.fontWeight = '600';
                btn.textContent = logoutText;
                form.appendChild(btn);
                spanLogout.appendChild(form);
                inject.appendChild(spanLogout);
            }
            function update() {
                fetch(@json(url('/check-auth-status')), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) { (j && j.authenticated) ? setLoggedIn() : setLoggedOut(); })
                    .catch(function () { setLoggedOut(); });
            }

            function performLogout() {
                fetch(LOGOUT_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    }
                }).then(function(response) {
                    if (response.ok) {
                        window.location.reload();
                    }
                }).catch(function(err) {
                    console.error('Logout error:', err);
                });
            }

            document.addEventListener('DOMContentLoaded', update);
            window.addEventListener('load', update);
            
            // Re-apply translations when language changes
            document.addEventListener('hp-language-changed', function() {
                update();
            });
        })();
    </script>
    {{-- Scroll reveal & counter animations (all pages) --}}
    <script>
        (function() {
            var reveal = document.querySelectorAll('.luxe-reveal, .luxe-img-reveal');
            if (reveal.length) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) entry.target.classList.add('luxe-visible');
                    });
                }, { rootMargin: '0px 0px -60px 0px', threshold: 0.1 });
                reveal.forEach(function(el) { observer.observe(el); });
            }
        })();
        (function() {
            var counters = document.querySelectorAll('.luxe-counter[data-target]');
            if (!counters.length) return;
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (!e.isIntersecting) return;
                    var el = e.target;
                    var target = parseInt(el.getAttribute('data-target'), 10);
                    var suffix = el.getAttribute('data-suffix') || '';
                    var duration = 1600;
                    var start = 0;
                    var startTime = null;
                    function step(t) {
                        if (!startTime) startTime = t;
                        var p = Math.min((t - startTime) / duration, 1);
                        p = 1 - Math.pow(1 - p, 2);
                        el.textContent = Math.round(start + (target - start) * p) + suffix;
                        if (p < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                    obs.unobserve(el);
                });
            }, { threshold: 0.3 });
            counters.forEach(function(c) { obs.observe(c); });
        })();
    </script>
    @stack('scripts')

    {{-- Positionner les widgets dans la zone visible (sans scroll) — Visual Viewport API --}}
    <script>
    (function() {
        var OFFSET = 95;
        function position() {
            var h = (window.visualViewport && window.visualViewport.height) || window.innerHeight;
            var top = Math.max(50, h - OFFSET);
            var book = document.getElementById('hp-book-widget-btn');
            var isMobile = window.innerWidth < 768;
            var pad = isMobile ? '12px' : '24px';
            if (book) { book.style.top = top + 'px'; book.style.bottom = 'auto'; book.style.left = pad; }
        }
        window.hpPositionWidgets = position;
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', position);
            window.visualViewport.addEventListener('scroll', position);
        }
        window.addEventListener('load', position);
        window.addEventListener('resize', position);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { position(); setTimeout(position, 50); setTimeout(position, 300); });
        } else {
            position();
            setTimeout(position, 50);
            setTimeout(position, 300);
        }
    })();
    </script>
</body>
</html>
