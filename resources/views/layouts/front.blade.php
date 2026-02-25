@php
    $formUrl = route('form-consigne');
    $aboutUrl = route('about-us');
    $faqUrl = route('faq');
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

    {{-- Widgets flottants en premier : Book now + Chatbot --}}
    @include('components.booking-widget')
    @include('components.chatbot')

    <div class="luxe-promo">
        <span data-i18n="promo_intro">Enjoy €10 off your booking with the code</span> <strong>PROMOHIVER</strong> – <a href="{{ $formUrl }}" data-i18n="nav_book">Book now</a>
    </div>

    <header class="luxe-header">
        <div class="luxe-header-inner">
            <a href="{{ route('form-consigne') }}" class="luxe-logo" aria-label="Hello Passenger Home" data-i18n-label="home">
                @if(file_exists(public_path('HP-Logo.png')))
                    <img src="{{ asset('HP-Logo.png') }}" alt="Hello Passenger" class="luxe-logo-img">
                @else
                    Hello Passenger
                @endif
            </a>
            <button type="button" class="luxe-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="luxe-nav-menu">
                <span class="luxe-nav-toggle-bar"></span>
                <span class="luxe-nav-toggle-bar"></span>
                <span class="luxe-nav-toggle-bar"></span>
            </button>
            <nav class="luxe-nav" id="luxe-nav-menu" role="navigation">
                <button type="button" class="luxe-nav-close" aria-label="Fermer le menu" title="Fermer">&times;</button>
                @include('components.translation-widget')
                <a href="{{ $aboutUrl }}" data-i18n="nav_about">About Us</a>
                <a href="{{ $faqUrl }}" data-i18n="nav_faq">FAQ</a>
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

    <footer class="luxe-footer">
        <div class="luxe-footer-inner">
            <h2 class="luxe-section-title" style="margin-bottom: 1rem;" data-i18n="footer_locate">Locate Us</h2>
            <p><strong>Paris CDG</strong> — <span data-i18n="footer_cdg">Terminal 2, TGV station – Level 4, opposite Sheraton Hotel, between 2C and 2E.</span></p>
            <p><strong>Paris Orly</strong> — <span data-i18n="footer_orly">Terminal 3, arrival level.</span></p>
            <p>📞 +33 (0)1 34 38 58 98 · ✉️ <a href="mailto:contact@hellopassenger.com">contact@hellopassenger.com</a></p>
            <div class="luxe-footer-links">
                <a href="{{ $formUrl }}" data-i18n="nav_book">Book</a>
                <a href="{{ $aboutUrl }}" data-i18n="nav_about">About Us</a>
                <a href="{{ $faqUrl }}" data-i18n="nav_faq">FAQ</a>
                <a href="{{ route('form-consigne') }}" data-i18n="nav_home">Home</a>
            </div>
            <p data-i18n="footer_description">Hello Passenger is a platform to book luggage transport to or from the airport and to store your luggage at our counters at Paris CDG and Paris Orly.</p>
            <p>© {{ date('Y') }} <span data-i18n="footer_rights">All Rights Reserved</span>.</p>
        </div>
    </footer>

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
