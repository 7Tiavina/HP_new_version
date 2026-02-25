@php
    $clientGuard = Auth::guard('client');
    $isClientLoggedIn = $clientGuard->check();
@endphp

<!-- Translation system (FR/EN buttons use this) -->
<script src="{{ asset('js/translations-simple.js') }}"></script>

<!-- Alpine.js for dropdown/menu -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Promo bar (as on Hostinger header) -->
<div class="bg-gray-900 text-white text-sm">
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
                    <button type="button" class="text-gray-900 hover:text-gray-700 font-semibold" onclick="window.openLoginModal?.()">
                        Sign in
                    </button>
                    <button type="button" class="text-gray-900 hover:text-gray-700 font-semibold" onclick="window.openRegisterModal?.()">
                        Register
                    </button>
                </div>
            @endif

            <a href="{{ route('form-consigne') }}"
               class="inline-flex items-center justify-center bg-[#FFC107] hover:bg-[#FFB300] text-black font-extrabold px-6 py-3 rounded-full transition-colors">
                BOOK NOW
            </a>
        </div>

        <!-- Mobile menu button -->
        <button class="lg:hidden inline-flex items-center gap-3 font-bold text-gray-900"
                x-data
                @click="$dispatch('hp-toggle-mobile')"
                aria-label="Open menu">
            <span class="uppercase">Menu</span>
            <span class="w-8 h-8 rounded-full bg-gray-900 text-white inline-flex items-center justify-center">≡</span>
        </button>
    </div>
</header>

<!-- Mobile menu -->
<div class="lg:hidden" x-data="{ open: false }" @hp-toggle-mobile.window="open = true" @keydown.escape.window="open = false">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50 z-50" @click="open = false"></div>

    <div x-show="open" x-transition
         class="fixed top-0 right-0 h-full w-[85%] max-w-sm bg-white z-50 shadow-2xl p-6 overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <img src="{{ asset('images/HP-logo-290x91.png') }}" alt="Hello Passenger" class="h-20 w-auto">
            <button class="text-3xl font-bold" @click="open = false" aria-label="Close menu">&times;</button>
        </div>

        <div class="flex items-center justify-between mb-6">
            <button data-lang="fr" class="text-sm font-semibold text-gray-900 hover:text-gray-700">FR</button>
            <div class="text-sm font-semibold text-gray-900">
                <span class="inline-flex items-center justify-center text-xs font-bold w-6 h-6 rounded-full bg-gray-900 text-white mr-2">0</span>
                Your Cart
                <div class="text-xs font-medium text-gray-600 mt-1">No products in the cart.</div>
            </div>
        </div>

        <nav class="space-y-2 font-semibold text-gray-900">
            <a href="{{ route('form-consigne') }}" class="block py-2">Home</a>

            <details class="py-2">
                <summary class="cursor-pointer">About Us</summary>
                <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                    <a href="#" class="block">What to do during a stopover in Paris</a>
                    <a href="#" class="block">Useful Information Before Flying</a>
                </div>
            </details>

            <details class="py-2">
                <summary class="cursor-pointer">Locate us</summary>
                <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                    <a href="#cdg" class="block">Charles De Gaulle</a>
                    <a href="#orly" class="block">Orly</a>
                </div>
            </details>

            <details class="py-2">
                <summary class="cursor-pointer">Services</summary>
                <div class="mt-2 ml-3 space-y-2 text-sm font-medium text-gray-700">
                    <a href="#" class="block">Transport of Luggage</a>
                    <a href="{{ route('form-consigne') }}" class="block">Left-Luggage Facilities</a>
                    <a href="#" class="block">Lost Item</a>
                    <a href="#" class="block">Children’s</a>
                    <a href="#" class="block">High-Tech</a>
                </div>
            </details>

            <a href="#contact" class="block py-2">Contact</a>
        </nav>

        <div class="mt-8 space-y-3">
            @if($isClientLoggedIn)
                <a href="{{ route('client.dashboard') }}" class="block w-full text-center border border-gray-300 rounded-full py-3 font-bold">
                    My account
                </a>
            @else
                <button type="button" class="block w-full text-center border border-gray-300 rounded-full py-3 font-bold" @click="open = false; window.openLoginModal?.()">
                    Sign in
                </button>
                <button type="button" class="block w-full text-center border border-gray-300 rounded-full py-3 font-bold" @click="open = false; window.openRegisterModal?.()">
                    Register
                </button>
            @endif

            <a href="{{ route('form-consigne') }}"
               class="block w-full text-center bg-[#FFC107] hover:bg-[#FFB300] text-black rounded-full py-3 font-extrabold">
                BOOK NOW
            </a>
        </div>

        <div class="mt-8 flex items-center gap-4 text-sm font-semibold text-gray-700">
            <a href="https://www.facebook.com/hello.passenger.officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://www.instagram.com/hellopassenger_officiel/" class="hover:text-gray-900" target="_blank" rel="noopener noreferrer">Instagram</a>
        </div>
    </div>
</div>

@include('Front.auth-modals')
