@props([
    'title' => null,
    'page' => null,
    'tailwindRootId' => 'hp-page-root',
    'includeChatbot' => false,
    'forceHeaderBlack' => false,
])

@php
    $downloadedPath = public_path('hostinger/en/darkgreen-pheasant-897942.hostingersite.com/en/index.html');
    $doc = @file_get_contents($downloadedPath) ?: '';

    $baseHref = asset('hostinger/en/darkgreen-pheasant-897942.hostingersite.com/en/') . '/';
    $formUrl = route('form-consigne');

    $headInner = '';
    $bodyAttrs = '';
    $bodyInner = '';

    if ($doc && preg_match('/<head[^>]*>([\\s\\S]*?)<\\/head>/i', $doc, $m)) {
        $headInner = $m[1] ?? '';
    }
    if ($doc && preg_match('/<body([^>]*)>([\\s\\S]*?)<\\/body>/i', $doc, $m)) {
        $bodyAttrs = $m[1] ?? '';
        $bodyInner = $m[2] ?? '';
        if (preg_match('/\bclass="([^"]*)"/', $bodyAttrs)) {
            $bodyAttrs = preg_replace('/\bclass="([^"]*)"/', 'class="$1 hp-hublot"', $bodyAttrs);
        } else {
            $bodyAttrs .= ' class="hp-hublot"';
        }
    }

    // Rewrite Hostinger absolute asset URLs to local downloaded files.
    foreach (['headInner', 'bodyInner'] as $var) {
        if (!empty($$var)) {
            $$var = preg_replace(
                '/(href|src)=(\"|\\\')https?:\\/\\/darkgreen-pheasant-897942\\.hostingersite\\.com\\/(wp-content|wp-includes)\\//i',
                '$1=$2../$3/',
                $$var
            ) ?: $$var;
            $$var = preg_replace(
                '/(href|src)=(\"|\\\')https?:\\/\\/darkgreen-pheasant-897942\\.hostingersite\\.com\\/en\\/(wp-content|wp-includes)\\//i',
                '$1=$2../$3/',
                $$var
            ) ?: $$var;
        }
    }

    // Rewrite "Book now" links anywhere in the body.
    if ($bodyInner) {
        $bodyInner = str_replace(
            [
                'https://indigo-cormorant-820127.hostingersite.com/public/link-form',
                'https://indigo-cormorant-820127.hostingersite.com/link-form',
                'https://indigo-cormorant-820127.hostingersite.com/public/link-form/',
                'https://indigo-cormorant-820127.hostingersite.com/link-form/',
                'https://indigo-cormorant-820127.hostingersite.com/public/link-form',
                'https://indigo-cormorant-820127.hostingersite.com/link-form',
                'https://indigo-cormorant-820127.hostingersite.com/public/link-form/',
                'https://indigo-cormorant-820127.hostingersite.com/link-form/',
            ],
            $formUrl,
            $bodyInner
        );
    }

    // Ensure Hostinger auth links do not navigate away; they should open our modals.
    if ($bodyInner) {
        $bodyInner = preg_replace(
            '/(<a\\b[^>]*class=[\"\\\'][^\"\\\']*\\blogin-link\\b[^\"\\\']*[\"\\\'][^>]*\\bhref=)[\"\\\'][^\"\\\']*[\"\\\']/i',
            '$1\"#login\"',
            $bodyInner
        ) ?: $bodyInner;
        $bodyInner = preg_replace(
            '/(<a\\b[^>]*class=[\"\\\'][^\"\\\']*\\bregister-link\\b[^\"\\\']*[\"\\\'][^>]*\\bhref=)[\"\\\'][^\"\\\']*[\"\\\']/i',
            '$1\"#signup\"',
            $bodyInner
        ) ?: $bodyInner;
    }

    // Split body into prefix/suffix around "page-content" so we can inject Laravel content there.
    $bodyPrefix = '';
    $bodySuffix = '';
    if ($bodyInner) {
        $reStart = '/<div\\s+id=\"page-content\"[^>]*>\\s*<!--\\s*page content\\s*-->/i';
        $reEnd = '/<\\/div>\\s*<!--\\s*end page content\\s*-->/i';

        if (preg_match($reStart, $bodyInner, $ms, PREG_OFFSET_CAPTURE)) {
            $startPos = $ms[0][1];
            $startLen = strlen($ms[0][0]);
            $afterStart = substr($bodyInner, $startPos + $startLen);

            if (preg_match($reEnd, $afterStart, $me, PREG_OFFSET_CAPTURE)) {
                $endPos = $startPos + $startLen + $me[0][1];
                $bodyPrefix = substr($bodyInner, 0, $startPos + $startLen);
                $bodySuffix = substr($bodyInner, $endPos);
            } else {
                $bodyPrefix = $bodyInner;
                $bodySuffix = '';
            }
        } else {
            $bodyPrefix = $bodyInner;
            $bodySuffix = '';
        }
    }
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($title)
        <title>{{ $title }}</title>
    @endif

    <base href="{{ $baseHref }}">

    <script>
        // Auth click queue (in case user clicks before modals JS is ready)
        (function () {
            window.__hpAuthQueue = window.__hpAuthQueue || [];
            if (!window.openLoginModal) window.openLoginModal = function () { window.__hpAuthQueue.push('login'); };
            if (!window.openRegisterModal) window.openRegisterModal = function () { window.__hpAuthQueue.push('register'); };
        })();
    </script>
    <script>
        // Bridge: Hostinger header links open OUR auth modals (capture phase beats theme scripts).
        (function () {
            function onClick(e) {
                var a = e.target && e.target.closest ? e.target.closest('a') : null;
                if (!a) return;

                var href = a.getAttribute('href') || '';
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

    {!! $headInner !!}

    <script>
        // Tailwind: keep Hostinger theme intact + scope utilities to the page root.
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            corePlugins: { preflight: false },
            important: '#{{ $tailwindRootId }}'
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/translations-simple.js') }}"></script>
    <script>
        // Synchronize Laravel session language with localStorage
        (function() {
            var sessionLang = '{{ session("app_language", "fr") }}';
            if (sessionLang && sessionLang !== localStorage.getItem('app_language')) {
                localStorage.setItem('app_language', sessionLang);
            }
        })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/hublot-theme.css') }}?v={{ file_exists(public_path('css/hublot-theme.css')) ? filemtime(public_path('css/hublot-theme.css')) : '1' }}">

    @if($includeChatbot)
        @include('components.chatbot')
    @endif

    <style>
        /* Offset our injected content below the absolute header (measured in JS). */
        #{{ $tailwindRootId }} { padding-top: var(--hp-header-offset, 170px); }

        /* Optional: make header black in normal (non-sticky) state */
        @if($forceHeaderBlack)
        body[data-page="{{ $page }}"] .header-builder-frontend.header-position-absolute,
        body[data-page="{{ $page }}"] .header-builder-frontend.header-position-absolute .header-builder-inner,
        body[data-page="{{ $page }}"] .header-builder-frontend.header-position-absolute .gv-sticky-menu:not(.stuck) {
            background: #000 !important;
            background-color: #000 !important;
        }
        @endif
    </style>
</head>
<body{!! $bodyAttrs !!} @if($page) data-page="{{ $page }}" @endif>

{!! $bodyPrefix !!}

<script>
    // Measure Hostinger header height and offset injected content.
    (function () {
        function setOffset() {
            try {
                var root = document.getElementById(@json($tailwindRootId));
                if (!root) return;
                var header =
                    document.querySelector('.wp-site-header') ||
                    document.querySelector('header.wp-site-header') ||
                    document.querySelector('header.header-builder-frontend');
                if (!header) return;
                var h = Math.ceil(header.getBoundingClientRect().height || 0);
                if (!h) return;
                root.style.setProperty('--hp-header-offset', (h + 20) + 'px');
            } catch (e) {}
        }
        document.addEventListener('DOMContentLoaded', setOffset);
        window.addEventListener('load', setOffset);
        window.addEventListener('resize', function () {
            clearTimeout(window.__hpHeaderOffsetTO);
            window.__hpHeaderOffsetTO = setTimeout(setOffset, 120);
        });
    })();
</script>

<div id="{{ $tailwindRootId }}">
    {{ $slot }}
</div>

{!! $bodySuffix !!}

<script src="{{ asset('js/hublot-theme.js') }}"></script>
@include('Front.auth-modals')

<script>
    // Toggle Hostinger header auth links based on Laravel client auth state.
    (function () {
        var DASHBOARD_URL = @json(route('client.dashboard'));
        var LOGOUT_URL = @json(route('client.logout'));

        function csrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }
        function qsa(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }
        function clearHpItems() { qsa('[data-hp-auth-item="1"]').forEach(function (el) { el.remove(); }); }

        function setLoggedOut() {
            qsa('a.login-link, a.register-link').forEach(function (a) { a.style.display = ''; });
            clearHpItems();
        }

        function setLoggedIn() {
            qsa('a.login-link, a.register-link').forEach(function (a) { a.style.display = 'none'; });

            var menu =
                document.querySelector('.my_account_nav_list.gva-user-menu') ||
                document.querySelector('.gva-user-menu') ||
                document.querySelector('.my_account_nav_list');
            if (!menu) return;

            clearHpItems();

            var liAccount = document.createElement('li');
            liAccount.setAttribute('data-hp-auth-item', '1');
            var aAccount = document.createElement('a');
            aAccount.href = DASHBOARD_URL;
            aAccount.innerHTML = '<i class="fa-regular fa-user"></i> My account';
            liAccount.appendChild(aAccount);
            menu.appendChild(liAccount);

            var liLogout = document.createElement('li');
            liLogout.setAttribute('data-hp-auth-item', '1');
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = LOGOUT_URL;
            form.style.display = 'inline';

            var token = csrfToken();
            if (token) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token;
                form.appendChild(input);
            }

            var btn = document.createElement('button');
            btn.type = 'submit';
            btn.style.background = 'transparent';
            btn.style.border = '0';
            btn.style.padding = '0';
            btn.style.cursor = 'pointer';
            btn.style.color = 'inherit';
            btn.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> Logout';
            form.appendChild(btn);
            liLogout.appendChild(form);
            menu.appendChild(liLogout);
        }

        function update() {
            fetch(@json(url('/check-auth-status')), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) { (j && j.authenticated) ? setLoggedIn() : setLoggedOut(); })
                .catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', update);
        window.addEventListener('load', update);
    })();
</script>

</body>
</html>

