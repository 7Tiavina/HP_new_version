<!DOCTYPE html>
@php
    $embedLang = session('app_language', 'fr');
    $embedTitle = $embedLang === 'en' ? 'Booking' : 'Réserver';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $embedTitle)</title>
    <link rel="stylesheet" href="{{ asset('css/acceuil-luxe.css') }}">
    <script>
        (function() {
            function hpHideLoader() {
                var loader = document.getElementById('loader');
                if (loader) { loader.classList.add('hidden'); loader.style.display = 'none'; }
            }
            if (document.readyState === 'complete') hpHideLoader();
            else window.addEventListener('load', hpHideLoader);
            window.addEventListener('pageshow', function(e) { if (e.persisted) hpHideLoader(); });
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/flatpickr/material_blue.css') }}">
    <link rel="stylesheet" href="{{ asset('css/booking-form.css') }}">
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = { corePlugins: { preflight: false }, important: '#hp-booking-root' };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="hp-embed-mode" id="hp-embed-body" style="margin:0;background:var(--luxe-bg,#0f0f0f);min-height:100vh;">
    @yield('content')
    @stack('scripts')
</body>
</html>
