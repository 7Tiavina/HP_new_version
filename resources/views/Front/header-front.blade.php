@php
    $clientGuard = Auth::guard('client');
    $isClientLoggedIn = $clientGuard->check();
@endphp

<!-- Translation system (FR/EN buttons use this) -->
<script>
    // Detect device language on first visit, then always sync from server session
    (function() {
        var sessionLang = '{{ session("app_language", "fr") }}';
        var savedLang = localStorage.getItem('app_language');
        
        if (!savedLang) {
            var deviceLang = navigator.language || navigator.userLanguage || '';
            var langCode = (deviceLang || '').toLowerCase().split('-')[0];
            var detected = (langCode === 'en') ? 'en' : 'fr';
            localStorage.setItem('app_language', detected);
            console.log('[LangDetect] First visit - device:', deviceLang, '=>', detected);
        } else if (sessionLang && sessionLang !== savedLang) {
            localStorage.setItem('app_language', sessionLang);
            console.log('[LangDetect] Synced from server:', sessionLang);
        } else {
            console.log('[LangDetect] Already set:', savedLang);
        }
    })();
</script>
<script src="{{ asset('js/translations-simple.js') }}"></script>

<!-- Alpine.js for dropdown/menu -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Promo bar (as on Hostinger header) -->
<div class="bg-[#212121] text-white text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center justify-between">
        <div class="truncate">
            Enjoy €10 off your booking with the code <strong>PROMOHIVER</strong> –
            <a href="{{ route('form-consigne') }}" class="underline font-semibold">Book now</a>
        </div>

        <div class="hidden sm:flex items-center gap-3">
            <a href="https://www.facebook.com/hello.passenger.officiel/" class="opacity-90 hover:opacity-100 transition-opacity" aria-label="Facebook" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://www.instagram.com/hellopassenger_officiel/" class="opacity-90 hover:opacity-100 transition-opacity" aria-label="Instagram" target="_blank" rel="noopener noreferrer">Instagram</a>
        </div>
    </div>
</div>

<!-- Main header -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="{{ route('form-consigne') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/HP-logo-290x91.png') }}" alt="Hello Passenger" class="h-20 sm:h-24 w-auto">
        </a>

        <!-- Desktop nav -->
        <nav class="hidden lg:flex items-center gap-6 text-gray-900 font-medium" x-data>
            <a href="{{ route('form-consigne') }}" class="hover:text-gray-700">Home</a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @keydown.escape="open = false" class="hover:text-gray-700">
                    About Us
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute left-0 mt-3 w-72 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">What to do during a stopover in Paris</a>
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">Useful Information Before Flying</a>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @keydown.escape="open = false" class="hover:text-gray-700">
                    Locate us
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute left-0 mt-3 w-64 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                    <div class="px-4 py-2 text-xs uppercase tracking-wide text-gray-500">Directions to the airport</div>
                    <a href="#cdg" class="block px-4 py-3 hover:bg-gray-50">Charles De Gaulle</a>
                    <a href="#orly" class="block px-4 py-3 hover:bg-gray-50">Orly</a>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @keydown.escape="open = false" class="hover:text-gray-700">
                    Services
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute left-0 mt-3 w-72 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">Transport of Luggage</a>
                    <a href="{{ route('form-consigne') }}" class="block px-4 py-3 hover:bg-gray-50">Left-Luggage Facilities</a>
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">Lost Item</a>
                    <div class="px-4 py-2 text-xs uppercase tracking-wide text-gray-500">Rental Equipment</div>
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">Children’s</a>
                    <a href="#" class="block px-4 py-3 hover:bg-gray-50">High-Tech</a>
                </div>
            </div>

            <a href="#contact" class="hover:text-gray-700">Contact</a>
        </nav>

        <!-- Right actions -->
        <div class="hidden lg:flex items-center gap-6">
            <!-- Social -->
            <div class="hidden xl:flex items-center gap-4 text-gray-700 font-semibold">
                <a href="https://www.facebook.com/hello.passenger.officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a href="https://www.instagram.com/hellopassenger_officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Instagram</a>
            </div>

            <!-- Cart -->
            <div class="relative" x-data="{ open: false }">
                <button type="button"
                        class="flex items-center gap-3 text-gray-900 font-semibold hover:text-gray-700"
                        @click="open = !open"
                        @keydown.escape="open = false"
                        aria-haspopup="dialog"
                        :aria-expanded="open.toString()">
                    <span class="inline-flex items-center justify-center text-xs font-bold w-6 h-6 rounded-full bg-gray-900 text-white">0</span>
                    <span>Your Cart</span>
                </button>

                <div x-show="open" x-transition @click.away="open = false"
                     class="absolute right-0 mt-3 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
                    <p class="text-sm text-gray-600">No products in the cart.</p>
                </div>
            </div>

            <!-- Language (Hostinger shows FR link) -->
            <button data-lang="fr" class="text-gray-900 font-semibold hover:text-gray-700">
                FR
            </button>

            @if($isClientLoggedIn)
                <a href="{{ route('client.dashboard') }}" class="text-gray-900 hover:text-gray-700 font-semibold">My account</a>
            @else
                <div class="flex items-center gap-3">
                    <a href="{{ route('account') }}" class="text-gray-900 hover:text-gray-700 font-semibold">
                        Sign in
                    </a>
                    <a href="{{ route('account') }}#register-panel" class="text-gray-900 hover:text-gray-700 font-semibold">
                        Register
                    </a>
                </div>
            @endif

            <a href="{{ route('form-consigne') }}"
               class="inline-flex items-center justify-center bg-[#FFC107] hover:bg-[#FFB300] text-black font-extrabold px-6 py-3 rounded-full transition-colors">
                BOOK NOW
            </a>
        </div>

        <!-- Mobile actions -->
        <div class="lg:hidden flex items-center gap-3">
            @if($isClientLoggedIn)
                <a href="{{ route('client.dashboard') }}" 
                   class="w-9 h-9 rounded-full bg-gray-900 text-white flex items-center justify-center hover:bg-gray-800 transition-colors"
                   aria-label="Mon compte">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('account') }}" 
                   class="w-9 h-9 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center hover:bg-gray-200 transition-colors"
                   aria-label="Se connecter">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif

            <!-- Mobile menu button -->
            <button class="inline-flex items-center gap-2 font-bold text-gray-900"
                    x-data
                    @click="$dispatch('hp-toggle-mobile')"
                    aria-label="Open menu">
                <span class="uppercase text-sm">Menu</span>
                <span class="w-9 h-9 rounded-full bg-gray-900 text-white inline-flex items-center justify-center text-xl">≡</span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile menu -->
<div class="lg:hidden" x-data="{ open: false }" 
     @hp-toggle-mobile.window="open = true; document.body.classList.add('drawer-chatbot-hidden');" 
     @keydown.escape.window="open = false; document.body.classList.remove('drawer-chatbot-hidden');">
    <div x-show="open" 
         x-transition.opacity 
         class="fixed inset-0 bg-black/50 z-50" 
         @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');"></div>

    <div x-show="open" 
         x-transition
         class="fixed top-0 right-0 h-full w-[85%] max-w-sm bg-white z-50 shadow-2xl overflow-y-auto">
        <!-- Drawer Header with Logo and Close button -->
        <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <!-- Logo (same as header) -->
                <a href="{{ route('form-consigne') }}" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');">
                    <img src="{{ asset('images/HP-logo-290x91.png') }}" alt="Hello Passenger" class="h-16 w-auto">
                </a>
                <!-- Close button -->
                <button class="text-3xl font-bold text-gray-900 hover:text-gray-700" 
                        @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" 
                        aria-label="Close menu">
                    &times;
                </button>
            </div>
        </div>

        <!-- Navigation section -->
        <div class="p-4">
            <!-- Cart summary -->
            <div class="flex items-center justify-between mb-6 p-3 bg-gray-50 rounded-lg">
                <div class="text-sm font-semibold text-gray-900">
                    <span class="inline-flex items-center justify-center text-xs font-bold w-6 h-6 rounded-full bg-gray-900 text-white mr-2">0</span>
                    Your Cart
                </div>
                <div class="text-xs font-medium text-gray-600">No products in the cart.</div>
            </div>

            <!-- Nav links -->
            <nav class="space-y-2 font-semibold text-gray-900">
                <a href="{{ route('form-consigne') }}" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-3 hover:bg-gray-50 rounded-lg px-2">Home</a>

                <details class="py-2">
                    <summary class="cursor-pointer py-2 hover:bg-gray-50 rounded-lg px-2">About Us</summary>
                    <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">What to do during a stopover in Paris</a>
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Useful Information Before Flying</a>
                    </div>
                </details>

                <details class="py-2">
                    <summary class="cursor-pointer py-2 hover:bg-gray-50 rounded-lg px-2">Locate us</summary>
                    <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                        <a href="#cdg" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Charles De Gaulle</a>
                        <a href="#orly" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Orly</a>
                    </div>
                </details>

                <details class="py-2">
                    <summary class="cursor-pointer py-2 hover:bg-gray-50 rounded-lg px-2">Services</summary>
                    <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Transport of Luggage</a>
                        <a href="{{ route('form-consigne') }}" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Left-Luggage Facilities</a>
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Lost Item</a>
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">Children's</a>
                        <a href="#" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-1">High-Tech</a>
                    </div>
                </details>

                <a href="#contact" @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');" class="block py-3 hover:bg-gray-50 rounded-lg px-2">Contact</a>
            </nav>

            <!-- BOOK NOW button (same as header) -->
            <div class="mt-6">
                <a href="{{ route('form-consigne') }}"
                   @click="open = false; document.body.classList.remove('drawer-chatbot-hidden');"
                   class="block w-full text-center bg-[#FFC107] hover:bg-[#FFB300] text-black font-extrabold px-6 py-3 rounded-full transition-colors shadow-lg hover:shadow-xl">
                    BOOK NOW
                </a>
            </div>

            <!-- Language selector -->
            <div class="mt-4 flex items-center justify-center">
                <button data-lang="fr" class="text-sm font-semibold text-gray-900 hover:text-gray-700 px-4 py-2">
                    FR
                </button>
            </div>

            <!-- Social links -->
            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-center gap-4 text-sm font-semibold text-gray-700">
                <a href="https://www.facebook.com/hello.passenger.officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a href="https://www.instagram.com/hellopassenger_officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Instagram</a>
            </div>
        </div>
    </div>
</div>
