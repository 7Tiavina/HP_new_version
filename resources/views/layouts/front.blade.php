@php
    $formUrl = route('form-consigne');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Hello Passenger — Luggage transport and left luggage at Paris CDG & Orly. Book online.')">
    <link rel="icon" type="image/png" href="{{ asset('favicon-hellopassenger.png') }}">
    <title>@yield('title', 'Hello Passenger — Luggage & travel services at Paris CDG & Orly')</title>

    <script>
        (function () {
            window.__hpAuthQueue = window.__hpAuthQueue || [];
            if (!window.openLoginModal) window.openLoginModal = function () { window.__hpAuthQueue.push('login'); };
            if (!window.openRegisterModal) window.openRegisterModal = function () { window.__hpAuthQueue.push('register'); };
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
                    if (window.openLoginModal) window.openLoginModal();
                    return;
                }
                if (isRegister || href.indexOf('#signup') !== -1) {
                    e.preventDefault(); e.stopImmediatePropagation(); e.stopPropagation();
                    if (window.openRegisterModal) window.openRegisterModal();
                    return;
                }
            }
            document.addEventListener('click', onClick, true);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('css/acceuil-luxe.css') }}?v={{ file_exists(public_path('css/acceuil-luxe.css')) ? filemtime(public_path('css/acceuil-luxe.css')) : '1' }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}?v={{ file_exists(public_path('css/animations.css')) ? filemtime(public_path('css/animations.css')) : '1' }}">
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
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}?v={{ file_exists(public_path('css/chatbot.css')) ? filemtime(public_path('css/chatbot.css')) : '1' }}">
    <script src="{{ asset('js/translations-simple.js') }}?v={{ file_exists(public_path('js/translations-simple.js')) ? filemtime(public_path('js/translations-simple.js')) : '1' }}"></script>
    @stack('styles')
    @stack('head_scripts')
</head>
<body class="luxe-home">

    {{-- Widget flottant Chatbot --}}
    @include('components.chatbot')

    <header class="luxe-header">
        <div class="luxe-header-inner">
            <button type="button" class="luxe-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="luxe-nav-menu">
                <span class="luxe-nav-toggle-bar"></span>
                <span class="luxe-nav-toggle-bar"></span>
                <span class="luxe-nav-toggle-bar"></span>
                <span class="luxe-nav-toggle-bar"></span>
            </button>
            <a href="{{ route('form-consigne') }}" class="luxe-logo" aria-label="Hello Passenger Home" data-i18n-label="home">
                @if(file_exists(public_path('HP-Logo.png')))
                    <img src="{{ asset('HP-Logo.png') }}" alt="Hello Passenger" class="luxe-logo-img">
                @else
                    Hello Passenger
                @endif
            </a>
            @include('components.translation-widget')
            <nav class="luxe-nav" id="luxe-nav-menu" role="navigation">
                <button type="button" class="luxe-nav-close" aria-label="Fermer le menu" title="Fermer">&times;</button>
                <a href="{{ $formUrl }}" class="btn-cta" data-i18n="nav_book">Book now</a>
                <a href="#login" class="login-link" data-i18n="login_btn">Login</a>
                <a href="#signup" class="register-link" data-i18n="create_account_short">Register</a>
                <span class="luxe-auth-inject"></span>
            </nav>
        </div>
    </header>

    <main class="luxe-main">
        @yield('content')
    </main>

    <div class="footer-wrapper">
        <footer class="custom-footer">
            <div class="footer-grid">

                <!-- Colonne 1: Logo & Since 2001 -->
                <div class="brand-col">
                    @if(file_exists(public_path('HP-Logo.png')))
                        <img src="{{ asset('HP-Logo.png') }}" alt="Hello Passenger" class="footer-logo">
                    @endif
                    <div class="since-text" data-i18n="footer_since">depuis 2001</div>
                    <p class="credits" data-i18n="footer_credits">© <span>Hello Passenger</span> {{ date('Y') }}. Tous droits réservés.</p>
                </div>

                <!-- Colonne 2: Plan d'accès -->
                <div class="footer-col">
                    <h3 data-i18n="footer_access">Plan d'accès</h3>
                    <a href="#" class="yellow-text" data-i18n="footer_cdg">Aéroport de Paris CDG</a>
                    <p data-i18n="footer_cdg_address">Terminal 2 - Gare TGV - Niveau 4<br>Opposition Hôtel Sheraton</p>
                    <br>
                    <a href="#" class="yellow-text" data-i18n="footer_orly">Aéroport de Paris ORLY</a>
                    <p data-i18n="footer_orly_address">Terminal 3 - Niveau d'arrivée</p>
                </div>

                <!-- Colonne 3: Contactez-nous -->
                <div class="footer-col">
                    <h3 data-i18n="footer_contact">Contactez-nous</h3>
                    <a href="tel:+33134385898" class="yellow-text">+33 (0)1 34 38 58 98</a>
                    <br>
                    <h3 data-i18n="footer_email">Email</h3>
                    <a href="mailto:contact@hellopassenger.com" class="yellow-text">contact@hellopassenger.com</a>
                    <br>
                    <h3 data-i18n="footer_follow">Suivez-nous</h3>
                    <div class="socials">
                        <a href="#" aria-label="Facebook">
                            <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Colonne 4: Liens Rapides -->
                <div class="footer-col">
                    <h3 data-i18n="footer_links">Liens Rapides</h3>
                    <a href="{{ $formUrl }}" data-i18n="footer_services">Services</a>
                    <a href="#faq" data-i18n="footer_faq">FAQ</a>
                    <a href="#contact" data-i18n="footer_contact_link">Contact</a>
                    <br>
                    <a href="{{ $formUrl }}" class="reserve-btn" data-i18n="footer_book">Réservez maintenant ↗</a>
                </div>
            </div>

            <!-- Bouton Retour en Haut -->
            <a href="#" class="go-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <path d="M18 15l-6-6-6 6"/>
                </svg>
            </a>
        </footer>
    </div>

    @include('Front.auth-modals')

    <script>
        (function () {
            var DASHBOARD_URL = @json(route('client.dashboard'));
            var LOGOUT_URL = @json(route('client.logout'));

            function csrfToken() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }
            function clearInject() {
                var el = document.querySelector('.luxe-auth-inject');
                if (!el) return;
                el.querySelectorAll('[data-hp-auth-item="1"]').forEach(function (n) { n.remove(); });
            }
            function setLoggedOut() {
                document.querySelectorAll('a.login-link, a.register-link').forEach(function (a) { a.style.display = ''; });
                clearInject();
            }
            function setLoggedIn() {
                document.querySelectorAll('a.login-link, a.register-link').forEach(function (a) { a.style.display = 'none'; });
                var inject = document.querySelector('.luxe-auth-inject');
                if (!inject) return;
                clearInject();

                var spanAccount = document.createElement('span');
                spanAccount.setAttribute('data-hp-auth-item', '1');
                spanAccount.style.marginRight = '1rem';
                var aAccount = document.createElement('a');
                aAccount.href = DASHBOARD_URL;
                aAccount.textContent = 'My account';
                aAccount.style.color = 'var(--luxe-gold)';
                spanAccount.appendChild(aAccount);
                inject.appendChild(spanAccount);

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
                btn.style.color = 'var(--luxe-cream-muted)';
                btn.style.fontSize = '0.9rem';
                btn.style.fontWeight = '500';
                btn.textContent = 'Logout';
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
            document.addEventListener('DOMContentLoaded', update);
            window.addEventListener('load', update);
        })();
    </script>
    <script>
        (function () {
            var toggle = document.querySelector('.luxe-nav-toggle');
            var nav = document.querySelector('.luxe-nav');
            if (!toggle || !nav) return;
            toggle.addEventListener('click', function () {
                var open = nav.classList.toggle('luxe-nav-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
                document.body.style.overflow = open ? 'hidden' : '';
            });
            function closeNav() {
                nav.classList.remove('luxe-nav-open');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.setAttribute('aria-label', 'Open menu');
                }
                document.body.style.overflow = '';
            }
            nav.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', closeNav);
            });
            var closeBtn = nav.querySelector('.luxe-nav-close');
            if (closeBtn) closeBtn.addEventListener('click', closeNav);
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
            var chat = document.getElementById('chatbot-widget');
            var isMobile = window.innerWidth < 768;
            var pad = isMobile ? '12px' : '24px';
            if (book) { book.style.top = top + 'px'; book.style.bottom = 'auto'; book.style.left = pad; }
            if (chat) { chat.style.top = top + 'px'; chat.style.bottom = 'auto'; chat.style.right = (window.innerWidth < 768 ? '12px' : '20px'); }
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
