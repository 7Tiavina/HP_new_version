@php
    $commandeData = Session::get('commande_en_cours');
    $googlePlacesApiKey = config('services.google.places_api_key');
@endphp

@extends('layouts.front')

@section('title', 'Finaliser le Paiement — Hello Passenger')

@push('head_scripts')
    <script
        src="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
        kr-public-key="43559169:testpublickey_TpUnzWl3wta3iKfuUeeYylRCWZ99SwdFKQktpbbxaOdxz"
        kr-post-url-success="{{ route('payment.success') }}"
        kr-post-url-refused="{{ route('payment.error') }}"
        kr-post-url-canceled="{{ route('payment.cancel') }}">
    </script>
    <link rel="stylesheet" href="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/ext/neon-reset.min.css">
    <script src="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/ext/neon.js"></script>
    <script src="{{ asset('js/translations-simple.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
@endpush

@push('styles')
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            corePlugins: { preflight: false },
            important: '#hp-payment-root',
            theme: {
                extend: {
                    colors: {
                        'yellow-custom': '#FFC107',
                        'yellow-hover': '#FFB300',
                        'gray-dark': '#1f2937'
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #hp-payment-root { padding: 1.5rem 0 3rem; }
        #hp-payment-root .input-style {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        #hp-payment-root .input-style:focus {
            border-color: #FFC107;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        }
        #hp-payment-root .input-error { border: 2px solid #ef4444 !important; }
        #hp-payment-root .custom-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #FFC107;
            border-radius: 50%;
            width: 1.5em;
            height: 1.5em;
            animation: hp-payment-spin 1s linear infinite;
        }
        @keyframes hp-payment-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .cache-bust { display: none; }
        /* intl-tel-input inside modal */
#clientProfileModal .iti { width: 100%; }
#clientProfileModal .iti__country-list { z-index: 9999; }
#clientProfileModal .iti__selected-flag { background: transparent; border-radius: 1rem 0 0 1rem; }
#clientProfileModal .iti__flag-container { padding-left: 0.5rem; }
    </style>
@endpush

@push('styles')
<style>
/* Luxe theme overrides — loaded last */
#hp-payment-root { background: var(--luxe-bg) !important; }
#hp-payment-root .bg-gray-50,
#hp-payment-root .bg-white { background: var(--luxe-card) !important; }
#hp-payment-root .input-style,
#hp-payment-root input,
#hp-payment-root select { background: var(--luxe-surface) !important; border-color: var(--luxe-border) !important; color: var(--luxe-cream) !important; }
#hp-payment-root .input-style:focus,
#hp-payment-root input:focus { border-color: var(--luxe-gold) !important; box-shadow: 0 0 0 2px rgba(201, 169, 98, 0.25) !important; }
/* Texte visible sur fond sombre */
#hp-payment-root .text-gray-800,
#hp-payment-root .text-gray-900,
#hp-payment-root h1, #hp-payment-root h2 { color: var(--luxe-cream) !important; }
#hp-payment-root .text-gray-600,
#hp-payment-root .text-gray-700,
#hp-payment-root .text-gray-500,
#hp-payment-root .text-gray-400 { color: var(--luxe-cream-muted) !important; }
#hp-payment-root .border-gray-200 { border-color: var(--luxe-border) !important; }
#hp-payment-root .text-red-600 { color: #e5a0a0 !important; }
#hp-payment-root .bg-green-100,
#hp-payment-root .text-green-700 { background: rgba(201, 169, 98, 0.15) !important; color: var(--luxe-cream) !important; border-color: var(--luxe-gold) !important; }
#hp-payment-root #custom-modal .text-gray-800,
#hp-payment-root #custom-modal .text-gray-700,
#hp-payment-root #custom-modal .text-gray-600 { color: var(--luxe-cream) !important; }
#hp-payment-root #custom-modal .text-gray-400 { color: var(--luxe-cream-muted) !important; }
#hp-payment-root #custom-modal .bg-gray-200 { background: var(--luxe-surface) !important; color: var(--luxe-cream) !important; }
</style>
@endpush

@section('content')
    <div id="hp-payment-root" class="bg-gray-50">
    <!-- Cache busting parameter -->
    <div class="cache-bust" data-version="{{ config('app.version', '1.0.0') }}"></div>

    <!-- Loader Overlay -->
    <div id="loader" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center">
        <div class="custom-spinner !w-12 !h-12 !border-4" style="margin-left: 0;"></div>
    </div>
    
    <!-- Custom Modal -->
    <div id="custom-modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center px-4">
        <div id="custom-modal" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all" onclick="event.stopPropagation();">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h3 id="custom-modal-title" class="text-xl font-bold text-gray-800"></h3>
                <button id="custom-modal-close" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="py-4">
                <p id="custom-modal-message" class="text-gray-600"></p>
                <div id="custom-modal-prompt-container" class="hidden mt-4">
                    <label id="custom-modal-prompt-label" for="custom-modal-input" class="block text-sm font-medium text-gray-700 mb-1"></label>
                    <input type="text" id="custom-modal-input" class="input-style w-full">
                    <p id="custom-modal-error" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>
            </div>
            <div id="custom-modal-footer" class="flex justify-end pt-3 border-t border-gray-200 space-x-3">
                <button id="custom-modal-cancel-btn" class="hidden bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-full btn-hover" data-i18n="btn_cancel">Annuler</button>
                <button id="custom-modal-confirm-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">OK</button>
            </div>
        </div>
    </div>

    <div class="container mx-auto max-w-5xl my-12 px-4">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('form-consigne') }}" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover inline-flex items-center" data-i18n="payment_back_form">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour au formulaire
            </a>
            <button id="payment-reset-btn" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center space-x-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span data-i18n="payment_reset">Réinitialiser et recommencer</span>
            </button>
        </div>

        <!-- Afficheur de message d'erreur -->
        @if(!$isProfileComplete)
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="status">
                <p class="font-bold" data-i18n="payment_security_title">Votre sécurité est notre priorité</p>
                <p data-i18n="payment_security_text">Afin de garantir la protection de vos effets personnels et de respecter les normes de sécurité par rayons X, merci de compléter les informations manquantes.</p>
            </div>
        @endif

        @if ($commandeData)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Colonne de gauche : Récapitulatif de la commande -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6" data-i18n="payment_order_summary">Récapitulatif de votre commande</h1>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        @php
                            // --- Duration Calculation ---
                            $duration_in_minutes = $commandeData['duration_in_minutes'] ?? 0;
                            $duration_display = '';
                            if ($duration_in_minutes > 0) {
                                if ($duration_in_minutes < 1440) {
                                    $hours = floor($duration_in_minutes / 60);
                                    $minutes = $duration_in_minutes % 60;
                                    $duration_display = $hours . ' heure(s)';
                                    if ($minutes > 0) {
                                        $duration_display .= ' et ' . $minutes . ' minute(s)';
                                    }
                                } else {
                                    $days = floor($duration_in_minutes / 1440);
                                    $remaining_hours = floor(($duration_in_minutes % 1440) / 60);
                                    $duration_display = $days . ' jour(s)';
                                    if ($remaining_hours > 0) {
                                        $duration_display .= ' et ' . $remaining_hours . ' heure(s)';
                                    }
                                }
                            }

                            // --- Date Extraction ---
                            $firstLigne = $commandeData['commandeLignes'][0] ?? null;
                            $dateDebut = null;
                            $dateFin = null;
                            if ($firstLigne) {
                                try {
                                    $dateDebut = \Carbon\Carbon::parse($firstLigne['dateDebut']);
                                    $dateFin = \Carbon\Carbon::parse($firstLigne['dateFin']);
                                } catch (\Exception $e) {
                                    // In case of parsing error
                                }
                            }
                        @endphp

                        @if($duration_display || ($dateDebut && $dateFin))
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <div class="space-y-2">
                                <div class="flex items-center text-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 mr-2 text-yellow-custom">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    <p class="text-base"><strong data-i18n="payment_service">Service :</strong> <span class="font-bold text-gray-900" data-i18n="payment_luggage_storage">Consigne de bagage</span></p>
                                </div>

                                @if(isset($commandeData['airportName']))
                                <div class="flex items-center text-gray-800 mt-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 mr-2 text-yellow-custom">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    <p class="text-base"><strong data-i18n="payment_airport">Aéroport :</strong> <span class="font-bold text-gray-900">{{ $commandeData['airportName'] }}</span></p>
                                </div>
                                @endif
                            </div>
                            
                            @if($duration_display)
                            <p class="font-semibold text-gray-700 mt-3" data-i18n="payment_duration">Durée totale</p>
                            <p class="text-lg font-bold text-gray-900">{{ $duration_display }}</p>
                            @endif
                            
                            @if($dateDebut && $dateFin)
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center text-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-base"><strong data-i18n="payment_from">Du :</strong> <span class="font-bold text-gray-900">{{ $dateDebut->format('d/m/Y à H:i') }}</span></p>
                                </div>
                                <div class="flex items-center text-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-base"><strong data-i18n="payment_to">Au :</strong> <span class="font-bold text-gray-900">{{ $dateFin->format('d/m/Y à H:i') }}</span></p>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <ul class="divide-y divide-gray-200">
                            @foreach($commandeData['commandeLignes'] as $ligne)
                                <li class="py-4 flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $ligne['libelleProduit'] }}</p>
                                        <p class="text-sm text-gray-500"><span data-i18n="payment_quantity">Quantité :</span> {{ $ligne['quantite'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        @if(isset($commandeData['discount_amount']) && $commandeData['discount_amount'] > 0 && isset($ligne['prixTTCAvantRemise']) && (float)($ligne['prixTTCAvantRemise']) > (float)($ligne['prixTTC']))
                                            <p class="text-sm text-gray-400 line-through">{{ number_format($ligne['prixTTCAvantRemise'], 2, ',', ' ') }} €</p>
                                            <p class="font-semibold text-gray-800">{{ number_format($ligne['prixTTC'], 2, ',', ' ') }} €</p>
                                        @elseif(isset($commandeData['total_normal_price']) && $commandeData['discount_amount'] > 0)
                                            @php
                                                $itemNormalPrice = isset($ligne['prixTTCAvantRemise']) ? (float)$ligne['prixTTCAvantRemise'] : ($ligne['prixTTC'] / (1 - ($commandeData['discount_percent'] ?? 10) / 100));
                                            @endphp
                                            <p class="text-sm text-gray-400 line-through">{{ number_format($itemNormalPrice, 2, ',', ' ') }} €</p>
                                            <p class="font-semibold text-gray-800">{{ number_format($ligne['prixTTC'], 2, ',', ' ') }} €</p>
                                        @else
                                            <p class="font-semibold text-gray-800">{{ number_format($ligne['prixTTC'], 2, ',', ' ') }} €</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if(isset($commandeData['total_normal_price']) && isset($commandeData['discount_amount']) && $commandeData['discount_amount'] > 0)
                        <div class="py-2 flex justify-between items-center border-t border-gray-200 mt-2">
                            <p class="text-sm text-gray-600" data-i18n="payment_total_normal">Total normal</p>
                            <p class="text-sm text-gray-400 line-through">{{ number_format($commandeData['total_normal_price'], 2, ',', ' ') }} €</p>
                        </div>
                        <div class="py-2 flex justify-between items-center">
                            <p class="text-sm text-green-600 font-semibold">
                                <span data-i18n="payment_discount_online">Promotion réservation en ligne</span> 
                                <span class="text-xs">(-{{ $commandeData['discount_percent'] ?? 10 }}%)</span>
                            </p>
                            <p class="text-sm text-green-600 font-semibold">-{{ number_format($commandeData['discount_amount'], 2, ',', ' ') }} €</p>
                        </div>
                        @endif
                        <div class="py-4 flex justify-between items-center border-t-2 border-gray-200 mt-4">
                            <p class="text-lg font-bold text-gray-900" data-i18n="payment_total_to_pay">Total à payer</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($commandeData['total_prix_ttc'], 2, ',', ' ') }} €</p>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite : Informations et Paiement -->
                <div class="space-y-8">
                    <!-- Bloc d'informations client -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 text-center" data-i18n="your_info">Vos informations</h2>
                        <div class="text-sm text-gray-600 space-y-2 text-left mx-auto max-w-sm">
                            <p id="display-user-name"><strong data-i18n="name">Nom:</strong> {{ $user->prenom ?? 'Non renseigné' }} {{ $user->nom ?? 'Non renseigné' }}</p>
                            <p id="display-user-email"><strong data-i18n="email">Email:</strong> {{ $user->email }}</p>
                            <p id="display-user-phone"><strong data-i18n="phone">Téléphone:</strong>
                                @if($user->telephone)
                                    {{ $user->telephone }}
                                @else
                                    <span data-i18n="not_provided">Non renseigné</span>
                                @endif
                            </p>
                            <p id="display-user-address"><strong data-i18n="address">Adresse:</strong>
                                @if(isset($user->adresse) && $user->adresse)
                                    {{ $user->adresse }}
                                @else
                                    <span data-i18n="not_provided">Non renseignée</span>
                                @endif
                            </p>
                            <p id="display-user-company"><strong data-i18n="company">Société:</strong>
                                @if(isset($user->nomSociete) && $user->nomSociete)
                                    {{ $user->nomSociete }}
                                @else
                                    <span data-i18n="not_provided">Non renseignée</span>
                                @endif
                            </p>
                        </div>
                        <button id="openClientProfileModalBtn" class="mt-4 bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover mx-auto" data-i18n="edit">Modifier</button>
                    </div>

                    <!-- Bloc de paiement -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 text-center" data-i18n="secure_payment">Paiement sécurisé</h2>
                        @if($isProfileComplete)
                            <!-- Formulaire Monetico -->
                            <div id="monetico-form-wrapper" class="mx-auto" style="min-height: 300px;">
                                @if($formToken)
                                    <div class="kr-smart-form" kr-form-token="{{ $formToken }}"></div>
                                    
                                    <!-- Debug Logs -->
                                    <script>
                                        console.log('[Monetico Debug] Form Token:', '{{ $formToken }}');
                                        console.log('[Monetico Debug] isProfileComplete:', '{{ $isProfileComplete ?? false }}');
                                        console.log('[Monetico Debug] hasSavedCard:', '{{ $hasSavedCard ?? false }}');
                                        
                                        window.addEventListener('load', function() {
                                            console.log('[Monetico Debug] Window loaded');
                                            console.log('[Monetico Debug] KR object exists:', typeof window.KR !== 'undefined');
                                            console.log('[Monetico Debug] KRPaymentForm exists:', typeof window.KRPaymentForm !== 'undefined');
                                            
                                            // Check if form is rendered
                                            const formElement = document.querySelector('.kr-smart-form');
                                            console.log('[Monetico Debug] Form element found:', formElement !== null);
                                            if (formElement) {
                                                console.log('[Monetico Debug] Form element attributes:', formElement.attributes);
                                            }
                                        });
                                    </script>
                                @else
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                                        <p class="font-semibold">Erreur d'initialisation du paiement</p>
                                        <p class="text-sm">Le formulaire de paiement n'a pas pu être initialisé. Veuillez recharger la page.</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-gray-100 rounded-md text-center text-gray-600" data-i18n="payment_complete_info">
                                Veuillez compléter vos informations pour activer le paiement.
                            </div>
                        @endif
                    </div>

                    <!-- Bloc de débogage -->
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                                 <details>
                                     <summary class="text-sm text-gray-600 cursor-pointer" data-i18n="payment_debug_title">Aperçu des données de commande (JSON)</summary>
                            <pre class="bg-gray-800 text-white p-4 rounded-md text-xs overflow-x-auto mt-2">{{ json_encode($commandeData, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative max-w-lg mx-auto" role="alert">
                <strong class="font-bold" data-i18n="payment_error">Erreur!</strong>
                <span class="block sm:inline" data-i18n="payment_no_data">Aucune donnée de commande trouvée. Votre session a peut-être expiré.</span>
                <a href="{{ route('form-consigne') }}" class="mt-4 inline-block bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded" data-i18n="payment_back_form">
                    Retour au formulaire
                </a>
            </div>
        @endif
    </div>

    @include('components.client-profile-modal')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/google-libphonenumber@3.2.34/dist/libphonenumber.min.js"></script>
    
    <!-- Google Places API - Script optimisé -->
    @if($googlePlacesApiKey)
    <script>
        // Version avec cache-busting
        const googleApiVersion = '{{ config('app.version', '1.0.0') }}';
        const googlePlacesApiKey = '{{ $googlePlacesApiKey }}';
        
        // Fonction pour charger Google Maps API avec rappel
        function loadGoogleMapsAPI(callback) {
            // Vérifier si l'API est déjà chargée
            if (window.google && window.google.maps && window.google.maps.places) {
                if (callback) callback();
                return;
            }
            
            // Créer l'élément script
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${googlePlacesApiKey}&libraries=places&language=fr&callback=initGooglePlaces&v=3.52&_=${googleApiVersion}`;
            script.async = true;
            script.defer = true;
            
            // Définir la fonction de rappel globale
            window.initGooglePlaces = function() {
                console.log('Google Places API loaded successfully');
                if (callback) callback();
            };
            
            // Gérer les erreurs de chargement
            script.onerror = function() {
                console.error('Failed to load Google Places API');
                const fallbackScript = document.createElement('script');
                fallbackScript.src = `https://maps.googleapis.com/maps/api/js?key=${googlePlacesApiKey}&libraries=places&language=fr&v=3.52`;
                fallbackScript.async = true;
                fallbackScript.defer = true;
                document.head.appendChild(fallbackScript);
                
                const checkGoogleAPI = setInterval(function() {
                    if (window.google && window.google.maps && window.google.maps.places) {
                        clearInterval(checkGoogleAPI);
                        if (callback) callback();
                    }
                }, 500);
            };
            
            document.head.appendChild(script);
        }
        
        // Initialiser l'autocomplétion une fois l'API chargée
        function initAutocomplete() {
            const addressInput = document.getElementById('modal-adresse');
            
            if (!addressInput) {
                console.error('Address input not found');
                return;
            }
            
            if (!window.google || !window.google.maps || !window.google.maps.places) {
                console.error('Google Maps API not available');
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
                    
                    document.getElementById('modal-adresse').value = fullAddress.trim();
                    
                    console.log('Complete address filled:', fullAddress);
                    console.log('Address components:', {
                        street_number,
                        route,
                        postal_code,
                        city,
                        administrative_area,
                        country
                    });
                });
                
                console.log('Google Places Autocomplete initialized');
            } catch (error) {
                console.error('Error initializing Google Places Autocomplete:', error);
        
                    // Character counter for address field (50 char limit for BDM API)
                    document.addEventListener('DOMContentLoaded', function() {
                        const addressInput = document.getElementById('modal-adresse');
                        const counter = document.getElementById('adresse-counter');
            
                        if (addressInput && counter) {
                            function updateCounter() {
                                const length = addressInput.value.length;
                                counter.textContent = length;
                    
                                // Change color when approaching limit
                                if (length >= 45) {
                                    counter.classList.add('text-red-500', 'font-bold');
                                    counter.classList.remove('text-gray-500');
                                } else {
                                    counter.classList.remove('text-red-500', 'font-bold');
                                    counter.classList.add('text-gray-500');
                                }
                            }
                
                            addressInput.addEventListener('input', updateCounter);
                            addressInput.addEventListener('change', updateCounter);
                
                            // Initialize counter on page load
                            updateCounter();
                        }
                    });
            }
        }
    </script>
    @else
    <script>
        console.warn('Google Places API key not configured');
    </script>
    @endif
    
    <script>
        // ========================================================================
        // SIMPLIFIED PHONE NUMBER NORMALIZATION - FOCUS ON RELIABILITY
        // ========================================================================
        
        function normalizePhoneNumber(value, countryData) {
            if (!value) return '';

            const raw = value.trim();
            const dialCode = String(countryData.dialCode || '');
            const countryCode = String(countryData.iso2 || '').toLowerCase();
            const countryCodeUpper = countryCode.toUpperCase();

            console.log('🔧 Input:', value, '| Country:', countryCode, '| DialCode:', dialCode);

            // Normaliser l'entrée (garder + et chiffres)
            let normalizedInput = raw;
            if (normalizedInput.startsWith('00')) {
                normalizedInput = '+' + normalizedInput.substring(2);
            }
            normalizedInput = normalizedInput.replace(/[\s\-\(\)\.]/g, '');
            if (normalizedInput.startsWith('+')) {
                normalizedInput = '+' + normalizedInput.substring(1).replace(/\D/g, '');
            } else {
                normalizedInput = normalizedInput.replace(/\D/g, '');
            }

            // Utiliser libphonenumber via intl-tel-input utils pour TOUS les pays
            if (window.intlTelInputUtils) {
                const candidates = [normalizedInput];
                if (!normalizedInput.startsWith('0') && !normalizedInput.startsWith('+')) {
                    candidates.push('0' + normalizedInput);
                }

                for (const candidate of candidates) {
                    const e164 = window.intlTelInputUtils.formatNumber(
                        candidate,
                        countryCodeUpper,
                        window.intlTelInputUtils.numberFormat.E164
                    );

                    if (e164) {
                        const isValid = window.intlTelInputUtils.isValidNumber(
                            e164,
                            countryCodeUpper
                        );

                        if (isValid) {
                            console.log('→ E164 via utils:', e164);
                            return e164;
                        }
                    }
                }

                // Si utils renvoie un format sans +, on tente de le corriger
                const e164Loose = window.intlTelInputUtils.formatNumber(
                    normalizedInput,
                    countryCodeUpper,
                    window.intlTelInputUtils.numberFormat.E164
                );

                if (e164Loose) {
                    let fixed = e164Loose;
                    if (!fixed.startsWith('+')) {
                        if (dialCode && fixed.startsWith(dialCode)) {
                            fixed = '+' + fixed;
                        } else {
                            fixed = '+' + dialCode + fixed;
                        }
                    }
                    console.log('→ E164 corrigé:', fixed);
                    return fixed;
                }
            }

            // Fallback générique si utils non disponible
            if (!normalizedInput) return '';

            if (normalizedInput.startsWith('+')) {
                console.log('→ Fallback +:', normalizedInput);
                return normalizedInput;
            }

            // Si ça commence déjà par le dialCode, on ajoute juste le +
            if (dialCode && normalizedInput.startsWith(dialCode) && normalizedInput.length > dialCode.length) {
                const withPlus = '+' + normalizedInput;
                console.log('→ Fallback dial code:', withPlus);
                return withPlus;
            }

            const fallback = '+' + dialCode + normalizedInput;
            console.log('→ Fallback concat:', fallback);
            return fallback;
        }

        function isLenientlyValidNumber(value, countryCodeUpper) {
            if (!value) return false;

            if (window.intlTelInputUtils) {
                if (window.intlTelInputUtils.isValidNumber(value, countryCodeUpper)) {
                    return true;
                }
            }

            const digits = value.replace(/\D/g, '');
            return digits.length >= 6 && digits.length <= 15;
        }

        function detectCountryFromNumber(phoneNumber, itiInstance) {
            if (!window.intlTelInputUtils || !itiInstance) return null;

            console.log('🔎 Attempting auto-detection for:', phoneNumber);

            // Obtenir le pays actuellement sélectionné comme fallback
            const currentCountry = itiInstance.getSelectedCountryData();
            const currentCountryCode = currentCountry.iso2.toUpperCase();
            const cleanNumber = phoneNumber.replace(/\D/g, '');

            // Si le numéro est trop court, garder le pays actuel
            if (cleanNumber.length < 6) {
                console.log('  → Number too short, keeping current:', currentCountryCode);
                return currentCountryCode;
            }

            // Essayer d'abord avec région vide (pour numéros avec code pays explicite)
            if (phoneNumber.trim().startsWith('+') || phoneNumber.trim().startsWith('00')) {
                try {
                    let testNumber = phoneNumber.trim();
                    if (testNumber.startsWith('00')) {
                        testNumber = '+' + testNumber.substring(2);
                    }
                    
                    const parsed = window.intlTelInputUtils.parseNumber(testNumber, '');
                    if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                        const cc = parsed.getCountryCode();
                        const nn = parsed.getNationalNumber();
                        
                        if (cc && nn && String(nn).length >= 6) {
                            const isValid = window.intlTelInputUtils.isValidNumber(testNumber, '');
                            if (isValid) {
                                const regionCode = window.intlTelInputUtils.getRegionCodeForCountryCode(cc);
                                if (regionCode) {
                                    console.log('🌐 Auto-detected (with intl code):', regionCode, '(country code:', cc, ')');
                                    return regionCode;
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.log('  → Parse attempt 1 (empty region) failed:', e.message);
                }
            }

            // Si le numéro ne commence pas par +, essayer d'abord avec le pays actuel
            if (!phoneNumber.trim().startsWith('+') && !phoneNumber.trim().startsWith('00')) {
                // Essayer avec le pays actuellement sélectionné (priorité maximale)
                try {
                    const testNumber = '+' + currentCountry.dialCode + cleanNumber;
                    const parsed = window.intlTelInputUtils.parseNumber(testNumber, currentCountryCode);
                    
                    if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                        const nn = parsed.getNationalNumber();
                        if (nn && String(nn).length >= 6) {
                            const isValid = window.intlTelInputUtils.isValidNumber(testNumber, currentCountryCode);
                            if (isValid) {
                                console.log('🌐 Auto-detected (current country):', currentCountryCode);
                                return currentCountryCode;
                            }
                        }
                    }
                } catch (e) {
                    console.log('  → Parse with current country failed:', e.message);
                }
                
                // Essayer avec les pays préférés
                const preferredCountries = ['fr', 'be', 'ch', 'ca', 'mu'];
                for (const prefCountry of preferredCountries) {
                    if (prefCountry === currentCountryCode.toLowerCase()) continue; // Déjà testé
                    
                    try {
                        const countryData = window.intlTelInputGlobals.getCountryData(prefCountry);
                        if (countryData && countryData.dialCode) {
                            const testNumber = '+' + countryData.dialCode + cleanNumber;
                            const parsed = window.intlTelInputUtils.parseNumber(testNumber, prefCountry.toUpperCase());
                            
                            if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                                const nn = parsed.getNationalNumber();
                                if (nn && String(nn).length >= 6) {
                                    const isValid = window.intlTelInputUtils.isValidNumber(testNumber, prefCountry.toUpperCase());
                                    if (isValid) {
                                        console.log('🌐 Auto-detected (preferred country):', prefCountry.toUpperCase());
                                        return prefCountry.toUpperCase();
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        // Continue
                    }
                }
                
                // Essayer les codes pays les plus communs (Europe, Amérique du Nord, etc.)
                const commonCountryCodes = [
                    { code: 33, country: 'FR' }, { code: 32, country: 'BE' }, { code: 1, country: 'US' },
                    { code: 44, country: 'GB' }, { code: 49, country: 'DE' }, { code: 39, country: 'IT' },
                    { code: 34, country: 'ES' }, { code: 31, country: 'NL' }, { code: 41, country: 'CH' },
                    { code: 352, country: 'LU' }, { code: 377, country: 'MC' }, { code: 262, country: 'RE' },
                    { code: 230, country: 'MU' }, { code: 230, country: 'MU' }
                ];
                
                console.log('  → Trying common country codes...');
                for (const { code, country } of commonCountryCodes) {
                    try {
                        const testNumber = '+' + code + cleanNumber;
                        const parsed = window.intlTelInputUtils.parseNumber(testNumber, '');
                        
                        if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                            const parsedCC = parsed.getCountryCode();
                            const nn = parsed.getNationalNumber();
                            
                            if (parsedCC === code && nn && String(nn).length >= 6) {
                                const isValid = window.intlTelInputUtils.isValidNumber(testNumber, country);
                                if (isValid) {
                                    console.log('🌐 Auto-detected (common code', code, '):', country);
                                    return country;
                                }
                            }
                        }
                    } catch (e) {
                        // Continue
                    }
                }
            }

            console.log('  → No country auto-detection successful, keeping current:', currentCountryCode);
            return currentCountryCode;
        }

        // Fonction utilitaire pour afficher des alertes personnalisées
        async function showCustomAlert(title, message) {
            return new Promise(resolve => {
                const modal = document.getElementById('custom-modal-overlay');
                const titleEl = document.getElementById('custom-modal-title');
                const messageEl = document.getElementById('custom-modal-message');
                const confirmBtn = document.getElementById('custom-modal-confirm-btn');
                const closeBtn = document.getElementById('custom-modal-close');
                const cancelBtn = document.getElementById('custom-modal-cancel-btn');
                const promptContainer = document.getElementById('custom-modal-prompt-container');

                titleEl.textContent = title;
                messageEl.textContent = message;
                promptContainer.classList.add('hidden');

                if (title === 'Erreur') {
                    confirmBtn.textContent = 'Retour au formulaire';
                } else {
                    confirmBtn.textContent = 'OK';
                }

                modal.classList.remove('hidden');

                const closeModal = () => {
                    modal.classList.add('hidden');
                    if (title === 'Erreur') {
                        window.location.href = '{{ route("form-consigne") }}';
                    }
                    resolve(true);
                };

                confirmBtn.onclick = closeModal;
                closeBtn.onclick = closeModal;
                cancelBtn.onclick = closeModal;

                modal.onclick = function(e) {
                    if (e.target === modal) closeModal();
                };
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));

            // Phone: no flag, no detection. User must type country code (+..).
            function normalizeE164Phone(raw) {
                if (!raw) return '';
                let v = String(raw).trim();
                if (!v) return '';
                if (v.startsWith('00')) v = '+' + v.slice(2);

                if (!v.startsWith('+')) return ''; // force country code
                const digits = v.replace(/\D/g, '');
                const len = digits.length;
                if (len < 6 || len > 15) return '';
                return '+' + digits;
            }

            function setPhoneError(inputEl, msg) {
                if (!inputEl) return;
                inputEl.classList.add('input-error');
                let errorMsg = inputEl.parentElement.querySelector('.phone-error-msg');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'phone-error-msg text-red-500 text-sm mt-1';
                    inputEl.parentElement.appendChild(errorMsg);
                }
                errorMsg.textContent = msg;
            }

            function clearPhoneError(inputEl) {
                if (!inputEl) return;
                inputEl.classList.remove('input-error');
                const errorMsg = inputEl.parentElement.querySelector('.phone-error-msg');
                if (errorMsg) errorMsg.remove();
            }

            const plainPhoneInput = document.getElementById('modal-telephone');
            if (plainPhoneInput) {
                plainPhoneInput.addEventListener('input', function() {
                    const cleaned = plainPhoneInput.value.replace(/[^\d+\s\-\(\)]/g, '');
                    if (cleaned !== plainPhoneInput.value) plainPhoneInput.value = cleaned;
                });

                plainPhoneInput.addEventListener('blur', function() {
                    const raw = plainPhoneInput.value.trim();
                    if (!raw) {
                        clearPhoneError(plainPhoneInput);
                        return;
                    }

                    const normalized = normalizeE164Phone(raw);
                    if (!normalized) {
                        setPhoneError(
                            plainPhoneInput,
                            t('phone_country_code_hint', 'Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France).')
                        );
                        return;
                    }

                    plainPhoneInput.value = normalized;
                    clearPhoneError(plainPhoneInput);
                });
            }
            
            // Translate luggage labels dynamically
            function translatePaymentPage() {
                // Translate baggage product labels
                const luggageLabelMap = {
                    'Accessoires': 'luggage_accessoires',
                    'Bagage cabine': 'luggage_bagage_cabine',
                    'Bagage soute': 'luggage_bagage_soute',
                    'Bagage spécial': 'luggage_bagage_special',
                    'Vestiaire': 'luggage_vestiaire'
                };
                
                // Find all elements containing luggage labels
                document.querySelectorAll('.font-semibold.text-gray-800').forEach(el => {
                    const text = el.textContent.trim();
                    if (luggageLabelMap[text]) {
                        el.textContent = t(luggageLabelMap[text], text);
                    }
                });
            }
            
            // Call translation after translations-simple.js has loaded
            if (window.translateKey) {
                translatePaymentPage();
            } else {
                setTimeout(translatePaymentPage, 100);
            }
            
            // Initialiser intl-tel-input
            const phoneInput = document.querySelector("#modal-telephone");
            let itiInstance = null;
            
            // Legacy intl-tel-input / country detection code disabled (user must type country code manually)
            if (phoneInput) {
                itiInstance = window.intlTelInput(phoneInput, {
                    initialCountry: "fr",
                    preferredCountries: ["fr", "be", "ch", "ca", "mu", "gb", "us", "de", "es", "it", "nl"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                    formatOnDisplay: false,
                    autoPlaceholder: "off", // Désactivé pour forcer la saisie manuelle avec code pays
                    separateDialCode: false,
                    nationalMode: false
                });
                
                // Désactiver l'autofill APRÈS l'initialisation de intl-tel-input
                // car intl-tel-input peut créer un nouvel input
                setTimeout(() => {
                    const actualInput = phoneInput.querySelector('input[type="tel"]') || phoneInput;
                    actualInput.setAttribute('autocomplete', 'off');
                    actualInput.setAttribute('autocorrect', 'off');
                    actualInput.setAttribute('autocapitalize', 'off');
                    actualInput.setAttribute('spellcheck', 'false');
                }, 100);

                console.log('✅ Phone input initialized');

                // Règles prédéfinies pour TOUS les pays (base de données internationale)
                const countryRules = {
                    'AF': { code: '93', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^93\d{8}$/], mobileStarts: ['07'] },
                    'AL': { code: '355', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^355\d{8}$/], mobileStarts: ['06', '07'] },
                    'DZ': { code: '213', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^213\d{8}$/], mobileStarts: ['05', '06', '07'] },
                    'AD': { code: '376', nationalLength: 6, nationalPrefix: '', patterns: [/^\d{6}$/, /^376\d{6}$/], mobileStarts: ['3'] },
                    'AO': { code: '244', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^244\d{8}$/], mobileStarts: ['9'] },
                    'AR': { code: '54', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^54\d{9}$/], mobileStarts: ['09'] },
                    'AT': { code: '43', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^43\d{9}$/], mobileStarts: ['06', '07'] },
                    'AU': { code: '61', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^61\d{8}$/], mobileStarts: ['04'] },
                    'AZ': { code: '994', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^994\d{8}$/], mobileStarts: ['05', '07'] },
                    'BS': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: ['2', '3'] },
                    'BH': { code: '973', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^973\d{7}$/], mobileStarts: ['3', '6'] },
                    'BD': { code: '880', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^880\d{9}$/], mobileStarts: ['01'] },
                    'BB': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: ['2', '3'] },
                    'BY': { code: '375', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^375\d{8}$/], mobileStarts: ['02', '04'] },
                    'BE': { code: '32', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^32\d{8}$/], mobileStarts: ['04'] },
                    'BZ': { code: '501', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^501\d{6}$/], mobileStarts: ['6', '7'] },
                    'BJ': { code: '229', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^229\d{7}$/], mobileStarts: ['6', '9'] },
                    'BT': { code: '975', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^975\d{7}$/], mobileStarts: ['1', '7'] },
                    'BO': { code: '591', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^591\d{7}$/], mobileStarts: ['6', '7'] },
                    'BA': { code: '387', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^387\d{7}$/], mobileStarts: ['06', '07'] },
                    'BW': { code: '267', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^267\d{7}$/], mobileStarts: ['7'] },
                    'BR': { code: '55', nationalLength: 11, nationalPrefix: '0', patterns: [/^0\d{10}$/, /^55\d{10}$/], mobileStarts: ['09'] },
                    'BN': { code: '673', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^673\d{6}$/], mobileStarts: ['7', '8'] },
                    'BG': { code: '359', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^359\d{8}$/], mobileStarts: ['08', '09'] },
                    'BF': { code: '226', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^226\d{7}$/], mobileStarts: ['6', '7'] },
                    'BI': { code: '257', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^257\d{7}$/], mobileStarts: ['7', '8'] },
                    'KH': { code: '855', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^855\d{8}$/], mobileStarts: ['07', '08', '09'] },
                    'CM': { code: '237', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^237\d{8}$/], mobileStarts: ['6', '7'] },
                    'CA': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^[2-9]\d{9}$/, /^1[2-9]\d{9}$/], mobileStarts: [] },
                    'CV': { code: '238', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^238\d{6}$/], mobileStarts: ['9'] },
                    'CF': { code: '236', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^236\d{7}$/], mobileStarts: ['7', '8'] },
                    'TD': { code: '235', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^235\d{7}$/], mobileStarts: ['6', '7'] },
                    'CL': { code: '56', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^56\d{8}$/], mobileStarts: ['09'] },
                    'CN': { code: '86', nationalLength: 11, nationalPrefix: '0', patterns: [/^0\d{10}$/, /^86\d{10}$/], mobileStarts: ['01', '02'] },
                    'CO': { code: '57', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^57\d{9}$/], mobileStarts: ['03'] },
                    'KM': { code: '269', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^269\d{6}$/], mobileStarts: ['3'] },
                    'CG': { code: '242', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^242\d{8}$/], mobileStarts: ['0', '1', '2'] },
                    'CR': { code: '506', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^506\d{7}$/], mobileStarts: ['8'] },
                    'HR': { code: '385', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^385\d{8}$/], mobileStarts: ['09'] },
                    'CU': { code: '53', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^53\d{7}$/], mobileStarts: ['05'] },
                    'CY': { code: '357', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^357\d{7}$/], mobileStarts: ['9'] },
                    'CZ': { code: '420', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^420\d{8}$/], mobileStarts: ['6', '7'] },
                    'DK': { code: '45', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^45\d{7}$/], mobileStarts: ['2', '4', '5'] },
                    'DJ': { code: '253', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^253\d{7}$/], mobileStarts: ['6', '7'] },
                    'DM': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'DO': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'EC': { code: '593', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^593\d{8}$/], mobileStarts: ['09'] },
                    'EG': { code: '20', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^20\d{9}$/], mobileStarts: ['01'] },
                    'SV': { code: '503', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^503\d{7}$/], mobileStarts: ['7'] },
                    'GQ': { code: '240', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^240\d{8}$/], mobileStarts: [] },
                    'ER': { code: '291', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^291\d{6}$/], mobileStarts: ['7'] },
                    'EE': { code: '372', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^372\d{6}$/], mobileStarts: ['5', '8'] },
                    'ET': { code: '251', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^251\d{8}$/], mobileStarts: ['09'] },
                    'FJ': { code: '679', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^679\d{6}$/], mobileStarts: ['7', '8', '9'] },
                    'FI': { code: '358', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^358\d{8}$/], mobileStarts: ['04'] },
                    'FR': { code: '33', nationalLength: 10, nationalPrefix: '0', patterns: [/^0[1-9]\d{8}$/, /^33[1-9]\d{8}$/], mobileStarts: ['06', '07'] },
                    'GA': { code: '241', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^241\d{7}$/], mobileStarts: ['6', '7'] },
                    'GM': { code: '220', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^220\d{6}$/], mobileStarts: ['2', '3', '7'] },
                    'GE': { code: '995', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^995\d{8}$/], mobileStarts: ['05'] },
                    'DE': { code: '49', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^49\d{9}$/], mobileStarts: ['01'] },
                    'GH': { code: '233', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^233\d{8}$/], mobileStarts: ['02', '05'] },
                    'GI': { code: '350', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^350\d{7}$/], mobileStarts: [] },
                    'GR': { code: '30', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^30\d{9}$/], mobileStarts: ['06', '07'] },
                    'GD': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'GT': { code: '502', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^502\d{7}$/], mobileStarts: ['7'] },
                    'GN': { code: '224', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^224\d{8}$/], mobileStarts: ['6'] },
                    'GW': { code: '245', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^245\d{6}$/], mobileStarts: ['6', '7'] },
                    'GY': { code: '592', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^592\d{6}$/], mobileStarts: ['6'] },
                    'HT': { code: '509', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^509\d{7}$/], mobileStarts: [] },
                    'HN': { code: '504', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^504\d{7}$/], mobileStarts: ['7', '8', '9'] },
                    'HK': { code: '852', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^852\d{7}$/], mobileStarts: ['5', '6', '9'] },
                    'HU': { code: '36', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^36\d{8}$/], mobileStarts: ['01', '06'] },
                    'IS': { code: '354', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^354\d{6}$/], mobileStarts: ['6', '8'] },
                    'IN': { code: '91', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^91\d{9}$/], mobileStarts: ['06', '07', '08', '09'] },
                    'ID': { code: '62', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^62\d{9}$/], mobileStarts: ['08'] },
                    'IR': { code: '98', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^98\d{9}$/], mobileStarts: ['01'] },
                    'IQ': { code: '964', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^964\d{9}$/], mobileStarts: ['07'] },
                    'IE': { code: '353', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^353\d{8}$/], mobileStarts: ['08'] },
                    'IL': { code: '972', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^972\d{8}$/], mobileStarts: ['05'] },
                    'IT': { code: '39', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^39\d{9}$/], mobileStarts: ['03'] },
                    'CI': { code: '225', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^225\d{7}$/], mobileStarts: ['0', '5'] },
                    'JM': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'JP': { code: '81', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^81\d{9}$/], mobileStarts: ['09'] },
                    'JO': { code: '962', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^962\d{8}$/], mobileStarts: ['07'] },
                    'KZ': { code: '7', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^7\d{9}$/], mobileStarts: ['07'] },
                    'KE': { code: '254', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^254\d{8}$/], mobileStarts: ['07'] },
                    'KI': { code: '686', nationalLength: 5, nationalPrefix: '', patterns: [/^\d{5}$/, /^686\d{4}$/], mobileStarts: [] },
                    'KW': { code: '965', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^965\d{7}$/], mobileStarts: ['5', '6'] },
                    'KG': { code: '996', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^996\d{8}$/], mobileStarts: ['05', '07'] },
                    'LA': { code: '856', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^856\d{7}$/], mobileStarts: ['07', '08', '09'] },
                    'LV': { code: '371', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^371\d{7}$/], mobileStarts: ['2', '2'] },
                    'LB': { code: '961', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^961\d{7}$/], mobileStarts: ['07', '08'] },
                    'LS': { code: '266', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^266\d{7}$/], mobileStarts: ['5', '6'] },
                    'LR': { code: '231', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^231\d{7}$/], mobileStarts: ['4', '5', '7', '8', '9'] },
                    'LY': { code: '218', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^218\d{8}$/], mobileStarts: ['09'] },
                    'LI': { code: '423', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^423\d{6}$/], mobileStarts: ['7'] },
                    'LT': { code: '370', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^370\d{7}$/], mobileStarts: ['06', '08'] },
                    'LU': { code: '352', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^352\d{8}$/], mobileStarts: ['6'] },
                    'MO': { code: '853', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^853\d{7}$/], mobileStarts: ['6'] },
                    'MK': { code: '389', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^389\d{7}$/], mobileStarts: ['07'] },
                    'MG': { code: '261', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^261\d{8}$/], mobileStarts: ['03'] },
                    'MW': { code: '265', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^265\d{8}$/], mobileStarts: ['07'] },
                    'MY': { code: '60', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^60\d{9}$/], mobileStarts: ['01'] },
                    'MV': { code: '960', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^960\d{6}$/], mobileStarts: ['7', '9'] },
                    'ML': { code: '223', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^223\d{7}$/], mobileStarts: ['6', '7'] },
                    'MT': { code: '356', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^356\d{7}$/], mobileStarts: ['7', '9'] },
                    'MH': { code: '692', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^692\d{6}$/], mobileStarts: [] },
                    'MR': { code: '222', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^222\d{7}$/], mobileStarts: ['2', '4', '6'] },
                    'MU': { code: '230', nationalLength: 8, nationalPrefix: '', patterns: [/^[2-9]\d{7}$/, /^230[2-9]\d{7}$/], mobileStarts: ['5'] },
                    'MX': { code: '52', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^52\d{9}$/], mobileStarts: ['01'] },
                    'FM': { code: '691', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^691\d{6}$/], mobileStarts: [] },
                    'MD': { code: '373', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^373\d{7}$/], mobileStarts: ['07', '08'] },
                    'MC': { code: '377', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^377\d{7}$/], mobileStarts: ['6'] },
                    'MN': { code: '976', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^976\d{7}$/], mobileStarts: ['08', '09'] },
                    'ME': { code: '382', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^382\d{7}$/], mobileStarts: ['06'] },
                    'MA': { code: '212', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^212\d{8}$/], mobileStarts: ['06', '07'] },
                    'MZ': { code: '258', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^258\d{8}$/], mobileStarts: ['8'] },
                    'MM': { code: '95', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^95\d{7}$/], mobileStarts: ['09'] },
                    'NA': { code: '264', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^264\d{7}$/], mobileStarts: ['08'] },
                    'NR': { code: '674', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^674\d{6}$/], mobileStarts: [] },
                    'NP': { code: '977', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^977\d{9}$/], mobileStarts: ['09'] },
                    'NL': { code: '31', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^31\d{8}$/], mobileStarts: ['06'] },
                    'NZ': { code: '64', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^64\d{8}$/], mobileStarts: ['02'] },
                    'NI': { code: '505', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^505\d{7}$/], mobileStarts: ['8'] },
                    'NE': { code: '227', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^227\d{7}$/], mobileStarts: ['6', '7', '8', '9'] },
                    'NG': { code: '234', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^234\d{9}$/], mobileStarts: ['07', '08', '09'] },
                    'NO': { code: '47', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^47\d{7}$/], mobileStarts: ['4', '5', '9'] },
                    'OM': { code: '968', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^968\d{7}$/], mobileStarts: ['9'] },
                    'PK': { code: '92', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^92\d{9}$/], mobileStarts: ['03'] },
                    'PW': { code: '680', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^680\d{6}$/], mobileStarts: [] },
                    'PA': { code: '507', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^507\d{7}$/], mobileStarts: ['6'] },
                    'PG': { code: '675', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^675\d{7}$/], mobileStarts: [] },
                    'PY': { code: '595', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^595\d{9}$/], mobileStarts: ['08'] },
                    'PE': { code: '51', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^51\d{8}$/], mobileStarts: ['09'] },
                    'PH': { code: '63', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^63\d{9}$/], mobileStarts: ['09'] },
                    'PL': { code: '48', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^48\d{8}$/], mobileStarts: ['05', '06', '07', '08', '09'] },
                    'PT': { code: '351', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^351\d{8}$/], mobileStarts: ['9'] },
                    'QA': { code: '974', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^974\d{7}$/], mobileStarts: ['3', '5', '6', '7'] },
                    'RO': { code: '40', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^40\d{8}$/], mobileStarts: ['07'] },
                    'RU': { code: '7', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^7\d{9}$/], mobileStarts: ['09'] },
                    'RW': { code: '250', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^250\d{8}$/], mobileStarts: ['07', '08'] },
                    'KN': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'LC': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'VC': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'WS': { code: '685', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^685\d{6}$/], mobileStarts: ['6', '7'] },
                    'SM': { code: '378', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^378\d{9}$/], mobileStarts: [] },
                    'ST': { code: '239', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^239\d{6}$/], mobileStarts: ['9'] },
                    'SA': { code: '966', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^966\d{8}$/], mobileStarts: ['05'] },
                    'SN': { code: '221', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^221\d{8}$/], mobileStarts: ['7'] },
                    'RS': { code: '381', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^381\d{8}$/], mobileStarts: ['06'] },
                    'SC': { code: '248', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^248\d{6}$/], mobileStarts: ['2'] },
                    'SL': { code: '232', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^232\d{7}$/], mobileStarts: ['07', '08'] },
                    'SG': { code: '65', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^65\d{7}$/], mobileStarts: ['8', '9'] },
                    'SK': { code: '421', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^421\d{8}$/], mobileStarts: ['09'] },
                    'SI': { code: '386', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^386\d{8}$/], mobileStarts: ['04', '05', '06', '07'] },
                    'SB': { code: '677', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^677\d{6}$/], mobileStarts: ['7'] },
                    'SO': { code: '252', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^252\d{7}$/], mobileStarts: ['6', '9'] },
                    'ZA': { code: '27', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^27\d{8}$/], mobileStarts: ['07'] },
                    'KR': { code: '82', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^82\d{9}$/], mobileStarts: ['01'] },
                    'SS': { code: '211', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^211\d{8}$/], mobileStarts: ['09'] },
                    'ES': { code: '34', nationalLength: 9, nationalPrefix: '', patterns: [/^\d{9}$/, /^34\d{8}$/], mobileStarts: ['6', '7', '9'] },
                    'LK': { code: '94', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^94\d{8}$/], mobileStarts: ['07'] },
                    'SD': { code: '249', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^249\d{8}$/], mobileStarts: ['09'] },
                    'SR': { code: '597', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^597\d{6}$/], mobileStarts: ['6', '7', '8'] },
                    'SZ': { code: '268', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^268\d{7}$/], mobileStarts: ['7', '8'] },
                    'SE': { code: '46', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^46\d{8}$/], mobileStarts: ['07'] },
                    'CH': { code: '41', nationalLength: 9, nationalPrefix: '0', patterns: [/^0[1-9]\d{8}$/, /^41[1-9]\d{8}$/], mobileStarts: ['07'] },
                    'SY': { code: '963', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^963\d{8}$/], mobileStarts: ['09'] },
                    'TW': { code: '886', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^886\d{8}$/], mobileStarts: ['09'] },
                    'TJ': { code: '992', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^992\d{8}$/], mobileStarts: ['05', '09'] },
                    'TZ': { code: '255', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^255\d{8}$/], mobileStarts: ['07'] },
                    'TH': { code: '66', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^66\d{8}$/], mobileStarts: ['08', '09'] },
                    'TL': { code: '670', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^670\d{7}$/], mobileStarts: ['7'] },
                    'TG': { code: '228', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^228\d{7}$/], mobileStarts: ['9'] },
                    'TO': { code: '676', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^676\d{6}$/], mobileStarts: ['8'] },
                    'TT': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^\d{10}$/, /^1\d{10}$/], mobileStarts: [] },
                    'TN': { code: '216', nationalLength: 8, nationalPrefix: '', patterns: [/^\d{8}$/, /^216\d{7}$/], mobileStarts: ['2', '5'] },
                    'TR': { code: '90', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^90\d{9}$/], mobileStarts: ['05'] },
                    'TM': { code: '993', nationalLength: 8, nationalPrefix: '0', patterns: [/^0\d{7}$/, /^993\d{7}$/], mobileStarts: ['06', '08'] },
                    'TV': { code: '688', nationalLength: 5, nationalPrefix: '', patterns: [/^\d{5}$/, /^688\d{4}$/], mobileStarts: [] },
                    'UG': { code: '256', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^256\d{8}$/], mobileStarts: ['07'] },
                    'UA': { code: '380', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^380\d{8}$/], mobileStarts: ['05', '06', '07', '09'] },
                    'AE': { code: '971', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^971\d{8}$/], mobileStarts: ['05'] },
                    'GB': { code: '44', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^44\d{9}$/], mobileStarts: ['07'] },
                    'US': { code: '1', nationalLength: 10, nationalPrefix: '', patterns: [/^[2-9]\d{9}$/, /^1[2-9]\d{9}$/], mobileStarts: ['2', '3', '4', '5', '6', '7', '8', '9'] },
                    'UY': { code: '598', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^598\d{8}$/], mobileStarts: ['09'] },
                    'UZ': { code: '998', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^998\d{8}$/], mobileStarts: ['05', '06', '09'] },
                    'VU': { code: '678', nationalLength: 7, nationalPrefix: '', patterns: [/^\d{7}$/, /^678\d{6}$/], mobileStarts: ['7'] },
                    'VE': { code: '58', nationalLength: 10, nationalPrefix: '0', patterns: [/^0\d{9}$/, /^58\d{9}$/], mobileStarts: ['04'] },
                    'VN': { code: '84', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^84\d{8}$/], mobileStarts: ['09'] },
                    'YE': { code: '967', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^967\d{8}$/], mobileStarts: ['07'] },
                    'ZM': { code: '260', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^260\d{8}$/], mobileStarts: ['09'] },
                    'ZW': { code: '263', nationalLength: 9, nationalPrefix: '0', patterns: [/^0\d{8}$/, /^263\d{8}$/], mobileStarts: ['07'] }
                };

                // Variable pour le debounce
                let detectionTimeout = null;
                
                // Pays prioritaires (bonus de score)
                const preferredCountries = ['FR', 'BE', 'CH', 'CA', 'MU'];

                // Fonction pour détecter le pays avec l'IA 1min.ai
                async function detectCountryWithAI(phoneNumber) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                         document.querySelector('input[name="_token"]')?.value;
                        
                        console.log('🤖 Appel API IA pour:', phoneNumber);
                        
                        const response = await fetch('/api/chatbot/detect-phone-country', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ phone_number: phoneNumber })
                        });
                        
                        if (!response.ok) {
                            console.error('❌ Erreur HTTP:', response.status, response.statusText);
                            return null;
                        }
                        
                        const data = await response.json();
                        console.log('📥 Réponse IA reçue:', data);
                        
                        if (data.success && data.country) {
                            console.log('✅ Pays détecté par IA 1min.ai:', data.country, '(réponse brute:', data.ai_response + ')');
                            return data.country;
                        } else {
                            console.warn('⚠️ IA n\'a pas détecté le pays. Réponse:', data.ai_response, 'Détecté:', data.detected_country);
                            return null;
                        }
                    } catch (error) {
                        console.error('❌ Erreur lors de la détection IA:', error);
                        return null;
                    }
                }

                // Détection automatique avec IA 1min.ai uniquement
                phoneInput.addEventListener('input', function(e) {
                    let value = phoneInput.value.trim();
                    
                    // Nettoyer les caractères non valides
                    value = value.replace(/[^\d+\s\-\(\)]/g, '');
                    if (phoneInput.value !== value) {
                        phoneInput.value = value;
                    }
                    
                    if (detectionTimeout) {
                        clearTimeout(detectionTimeout);
                    }
                    
                    // Détecter seulement si le numéro a une longueur suffisante
                    const cleanDigits = value.replace(/\D/g, '');
                    if (cleanDigits.length >= 6) {
                        detectionTimeout = setTimeout(async function() {
                            console.log('🤖 Détection IA pour:', value);
                            const detectedCountry = await detectCountryWithAI(value);
                            
                            // Appliquer le pays détecté par l'IA
                            if (detectedCountry) {
                                const currentCountry = itiInstance.getSelectedCountryData();
                                const detectedIso2 = String(detectedCountry).toLowerCase();
                                if (currentCountry.iso2 !== detectedIso2) {
                                    itiInstance.setCountry(detectedIso2);
                                    console.log(`🔄 Pays changé par IA: ${currentCountry.iso2} → ${detectedIso2}`);
                                }
                            } else {
                                console.log('⚠️ IA n\'a pas pu détecter le pays pour:', value);
                            }
                        }, 800);
                    }
                });

                phoneInput.addEventListener('blur', function() {
                    if (detectionTimeout) {
                        clearTimeout(detectionTimeout);
                    }
                    
                    const value = phoneInput.value.trim();
                    
                    if (!value) {
                        phoneInput.classList.remove('input-error');
                        const errorMsg = phoneInput.parentElement.querySelector('.phone-error-msg');
                        if (errorMsg) errorMsg.remove();
                        return;
                    }
                    
                    // Détection avec IA uniquement au blur
                    if (value.length >= 6) {
                        console.log('🤖 Détection IA au blur pour:', value);
                        detectCountryWithAI(value).then(country => {
                            if (country) {
                                const currentCountry = itiInstance.getSelectedCountryData();
                                const detectedIso2 = String(country).toLowerCase();
                                if (currentCountry.iso2 !== detectedIso2) {
                                    itiInstance.setCountry(detectedIso2);
                                    console.log(`🔄 Pays changé au blur par IA: ${currentCountry.iso2} → ${detectedIso2}`);
                                }
                            } else {
                                console.log('⚠️ IA n\'a pas pu détecter le pays au blur pour:', value);
                            }
                        });
                    }
                    
                    // Validation du numéro
                    console.log('🔍 Validation du numéro...');
                    
                    let countryData = itiInstance.getSelectedCountryData();
                    let countryCodeUpper = String(countryData.iso2 || '').toUpperCase();
                    const normalized = normalizePhoneNumber(value, countryData);
                    
                    // Utiliser la détection native pour les formats internationaux
                    if (normalized.startsWith('+')) {
                        try {
                            itiInstance.setNumber(normalized);
                            countryData = itiInstance.getSelectedCountryData();
                            countryCodeUpper = String(countryData.iso2 || '').toUpperCase();
                        } catch (e) {
                            // Continue
                        }
                    }
                    
                    phoneInput.value = normalized;
                    itiInstance.setNumber(normalized);
                    
                    // Attendre que ITI traite le numéro
                    setTimeout(() => {
                        const formatted = itiInstance.getNumber() || normalized;
                        const isValid = isLenientlyValidNumber(formatted, countryCodeUpper);
                        
                        if (isValid) {
                            phoneInput.classList.remove('input-error');
                            const errorMsg = phoneInput.parentElement.querySelector('.phone-error-msg');
                            if (errorMsg) errorMsg.remove();
                            phoneInput.value = formatted;
                        } else {
                            phoneInput.classList.add('input-error');
                            let errorMsg = phoneInput.parentElement.querySelector('.phone-error-msg');
                            if (!errorMsg) {
                                errorMsg = document.createElement('p');
                                errorMsg.className = 'phone-error-msg text-red-500 text-sm mt-1';
                                phoneInput.parentElement.appendChild(errorMsg);
                            }
                            const errorCode = (typeof itiInstance.getValidationError === 'function') ? itiInstance.getValidationError() : 0;
                            const errors = {
                                0: t('phone_invalid'),
                                1: t('phone_invalid_country'),
                                2: t('phone_too_short'),
                                3: t('phone_too_long'),
                                4: t('phone_invalid_format')
                            };
                            errorMsg.textContent = errors[errorCode] || t('phone_invalid');
                        }
                    }, 100);
                });

                // NOTE: La détection native d'intl-tel-input via setNumber() est utilisée ci-dessus
                // Cette fonction complexe n'est plus nécessaire car le plugin gère tout nativement
                /*
                function detectCountryFromNumberLocal(value) {
                    if (!value || !value.trim()) {
                        return;
                    }
                    
                    const cleanValue = value.replace(/\D/g, '');
                    const originalValue = value.trim();
                    
                    if (cleanValue.length < 6) {
                        return;
                    }
                    
                    console.log('🔍 Détection avancée pour:', originalValue, '(nettoyé:', cleanValue + ')');
                    
                    let detectedCountry = null;
                    let highestScore = 0;
                    const topMatches = [];
                    const currentCountry = itiInstance.getSelectedCountryData();
                    const currentCountryCode = currentCountry.iso2.toUpperCase();
                    
                    // ÉTAPE 1: Si commence par + ou 00, extraire le code pays (priorité absolue)
                    if (originalValue.startsWith('+') || originalValue.startsWith('00')) {
                        let testValue = originalValue;
                        if (testValue.startsWith('00')) {
                            testValue = '+' + testValue.substring(2);
                        }
                        
                        // Utiliser intlTelInputUtils d'abord (plus fiable)
                        if (window.intlTelInputUtils) {
                            try {
                                const parsed = window.intlTelInputUtils.parseNumber(testValue, '');
                                if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                                    const cc = parsed.getCountryCode();
                                    const nn = parsed.getNationalNumber();
                                    if (cc && nn && String(nn).length >= 6) {
                                        const isValid = window.intlTelInputUtils.isValidNumber(testValue, '');
                                        if (isValid) {
                                            const regionCode = window.intlTelInputUtils.getRegionCodeForCountryCode(cc);
                                            if (regionCode) {
                                                detectedCountry = regionCode;
                                                highestScore = 100;
                                                console.log(`✅ Format international (intlTelInputUtils): +${cc} → ${regionCode}`);
                                            }
                                        }
                                    }
                                }
                            } catch (e) {
                                console.log('  → intlTelInputUtils parse failed, trying rules...');
                            }
                        }
                        
                        // Fallback sur les règles prédéfinies
                        if (!detectedCountry) {
                            for (const [country, rules] of Object.entries(countryRules)) {
                                if (cleanValue.startsWith(rules.code)) {
                                    const remainingDigits = cleanValue.substring(rules.code.length);
                                    // Tolérance ±2 chiffres pour les formats internationaux
                                    if (Math.abs(remainingDigits.length - rules.nationalLength) <= 2) {
                                        detectedCountry = country;
                                        highestScore = 95;
                                        console.log(`✅ Format international (rules): +${rules.code} → ${country}`);
                                        break;
                                    }
                                }
                            }
                        }
                    } 
                    // ÉTAPE 2: Format local - combiner règles + intlTelInputUtils
                    else {
                        // Priorité 1: Tester le pays actuel avec intlTelInputUtils
                        if (window.intlTelInputUtils) {
                            try {
                                const testNumber = '+' + currentCountry.dialCode + cleanValue;
                                const parsed = window.intlTelInputUtils.parseNumber(testNumber, currentCountryCode);
                                if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                                    const nn = parsed.getNationalNumber();
                                    if (nn && String(nn).length >= 6) {
                                        const isValid = window.intlTelInputUtils.isValidNumber(testNumber, currentCountryCode);
                                        if (isValid) {
                                            detectedCountry = currentCountryCode;
                                            highestScore = 90;
                                            console.log(`✅ Pays actuel validé (intlTelInputUtils): ${currentCountryCode}`);
                                        }
                                    }
                                }
                            } catch (e) {
                                // Continue
                            }
                        }
                        
                        // Priorité 2: Tester tous les pays avec scoring intelligent
                        if (!detectedCountry || highestScore < 85) {
                            for (const [country, rules] of Object.entries(countryRules)) {
                                let score = 0;
                                let reason = '';
                                
                                // Test 1: Format international sans + (ex: 33612345678)
                                if (cleanValue.startsWith(rules.code)) {
                                    const remaining = cleanValue.substring(rules.code.length);
                                    if (Math.abs(remaining.length - rules.nationalLength) <= 2) {
                                        score = 100;
                                        reason = 'Intl';
                                    }
                                }
                                // Test 2: Format national avec préfixe (ex: 0612345678 pour FR)
                                else if (rules.nationalPrefix && cleanValue.startsWith(rules.nationalPrefix)) {
                                    const withoutPrefix = cleanValue.substring(rules.nationalPrefix.length);
                                    // Longueur exacte
                                    if (cleanValue.length === rules.nationalLength) {
                                        score = 90;
                                        reason = 'Nat+prefix-exact';
                                        // Bonus mobile
                                        for (const mobileStart of rules.mobileStarts) {
                                            if (cleanValue.startsWith(mobileStart)) {
                                                score = 95;
                                                reason = 'Mobile+prefix-exact';
                                                break;
                                            }
                                        }
                                    }
                                    // Longueur proche (±2)
                                    else if (cleanValue.length >= rules.nationalLength - 2 && cleanValue.length <= rules.nationalLength + 2) {
                                        score = 75;
                                        reason = 'Nat+prefix-approx';
                                        for (const mobileStart of rules.mobileStarts) {
                                            if (cleanValue.startsWith(mobileStart)) {
                                                score = 85;
                                                reason = 'Mobile+prefix-approx';
                                                break;
                                            }
                                        }
                                    }
                                    // Même avec préfixe mais longueur différente, c'est probablement ce pays
                                    else if (cleanValue.length >= 6) {
                                        score = 60;
                                        reason = 'Nat+prefix-loose';
                                        for (const mobileStart of rules.mobileStarts) {
                                            if (cleanValue.startsWith(mobileStart)) {
                                                score = 70;
                                                reason = 'Mobile+prefix-loose';
                                                break;
                                            }
                                        }
                                    }
                                }
                                // Test 3: Format national sans préfixe - longueur exacte
                                else if (!rules.nationalPrefix && cleanValue.length === rules.nationalLength) {
                                    score = 80;
                                    reason = 'Nat-exact';
                                    // Bonus mobile
                                    for (const mobileStart of rules.mobileStarts) {
                                        if (cleanValue.startsWith(mobileStart)) {
                                            score = 90;
                                            reason = 'Mobile-exact';
                                            break;
                                        }
                                    }
                                }
                                // Test 4: Format national sans préfixe - longueur proche
                                else if (!rules.nationalPrefix && Math.abs(cleanValue.length - rules.nationalLength) <= 2) {
                                    score = 65;
                                    reason = 'Nat-approx';
                                    for (const mobileStart of rules.mobileStarts) {
                                        if (cleanValue.startsWith(mobileStart)) {
                                            score = 75;
                                            reason = 'Mobile-approx';
                                            break;
                                        }
                                    }
                                }
                                
                                // Test 5: Patterns regex (très fiable)
                                if (rules.patterns && rules.patterns.length > 0) {
                                    for (const pattern of rules.patterns) {
                                        if (pattern.test(cleanValue)) {
                                            score = Math.max(score, 85);
                                            reason = reason || 'Pattern-match';
                                            break;
                                        }
                                    }
                                }
                                
                                // Bonus CRITIQUE: Pays actuellement sélectionné (priorité maximale)
                                if (score > 0 && country === currentCountryCode) {
                                    score += 25;
                                    reason += '+current';
                                }
                                
                                // Bonus: Pays préférés
                                if (score > 0 && preferredCountries.includes(country)) {
                                    score += 12;
                                }
                                
                                // Validation croisée avec intlTelInputUtils si disponible
                                if (score > 0 && window.intlTelInputUtils) {
                                    try {
                                        // Obtenir le dial code du pays depuis les règles ou intlTelInputGlobals
                                        let dialCode = rules.code;
                                        if (window.intlTelInputGlobals) {
                                            try {
                                                const countryData = window.intlTelInputGlobals.getCountryData(country.toLowerCase());
                                                if (countryData && countryData.dialCode) {
                                                    dialCode = countryData.dialCode;
                                                }
                                            } catch (e) {
                                                // Utiliser rules.code
                                            }
                                        }
                                        
                                        const testNumber = '+' + dialCode + cleanValue;
                                        const parsed = window.intlTelInputUtils.parseNumber(testNumber, country);
                                        if (parsed && parsed.getNationalNumber) {
                                            const nn = parsed.getNationalNumber();
                                            if (nn && String(nn).length >= 6) {
                                                const isValid = window.intlTelInputUtils.isValidNumber(testNumber, country);
                                                if (isValid) {
                                                    score += 15;
                                                    reason += '+validated';
                                                }
                                            }
                                        }
                                    } catch (e) {
                                        // Continue sans validation
                                    }
                                }
                                
                                if (score > 0) {
                                    topMatches.push({ country, score, reason });
                                    if (score > highestScore) {
                                        highestScore = score;
                                        detectedCountry = country;
                                    }
                                }
                            }
                            
                            // Si aucun match fort, utiliser le pays actuel comme fallback intelligent
                            if (!detectedCountry || highestScore < 55) {
                                const currentRules = countryRules[currentCountryCode];
                                if (currentRules) {
                                    let currentScore = 0;
                                    
                                    // Format avec préfixe national
                                    if (currentRules.nationalPrefix && cleanValue.startsWith(currentRules.nationalPrefix)) {
                                        currentScore = 55;
                                    }
                                    // Format sans préfixe mais longueur proche
                                    else if (Math.abs(cleanValue.length - currentRules.nationalLength) <= 3) {
                                        currentScore = 45;
                                    }
                                    
                                    // Bonus mobile
                                    if (currentScore > 0) {
                                        for (const mobileStart of currentRules.mobileStarts) {
                                            if (cleanValue.startsWith(mobileStart)) {
                                                currentScore += 25;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Validation avec intlTelInputUtils
                                    if (currentScore > 0 && window.intlTelInputUtils) {
                                        try {
                                            const testNumber = '+' + currentCountry.dialCode + cleanValue;
                                            const isValid = window.intlTelInputUtils.isValidNumber(testNumber, currentCountryCode);
                                            if (isValid) {
                                                currentScore += 20;
                                            }
                                        } catch (e) {
                                            // Continue
                                        }
                                    }
                                    
                                    if (currentScore > 0 && currentScore >= highestScore) {
                                        detectedCountry = currentCountryCode;
                                        highestScore = currentScore;
                                        console.log(`📍 Fallback intelligent vers pays actuel (${currentCountryCode}, score: ${currentScore})`);
                                    }
                                }
                            }
                            
                            if (topMatches.length > 0) {
                                console.log('📊 Top matches:');
                                topMatches.sort((a,b) => b.score - a.score).slice(0,5).forEach(m => {
                                    console.log(`   ${m.country}: ${m.score} (${m.reason})`);
                                });
                            }
                        }
                        
                        if (detectedCountry) {
                            console.log(`✅ Choix final: ${detectedCountry} (score: ${highestScore})`);
                        } else {
                            console.log(`⚠️ Aucun pays détecté, garde: ${currentCountryCode}`);
                        }
                    }
                    
                    // Appliquer le pays détecté seulement si le score est suffisant (seuil abaissé à 55)
                    if (detectedCountry && highestScore >= 55) {
                        const detectedLower = detectedCountry.toLowerCase();
                        
                        if (currentCountry.iso2 !== detectedLower) {
                            itiInstance.setCountry(detectedLower);
                            console.log(`🔄 Pays changé: ${currentCountry.iso2} → ${detectedLower} (score: ${highestScore})`);
                        }
                    } else if (detectedCountry && highestScore < 55) {
                        console.log(`⚠️ Score trop faible (${highestScore}), garde: ${currentCountryCode}`);
                    }
                }
                */
                // IMPORTANT: removed duplicate blur handler + countrychange->blur loop (was freezing the browser)
            }
            
            // Charger Google Places API si nécessaire
            @if($googlePlacesApiKey)
            const openClientProfileModalBtn = document.getElementById('openClientProfileModalBtn');
            if (openClientProfileModalBtn) {
                openClientProfileModalBtn.addEventListener('click', function() {
                    if (!window.google || !window.google.maps || !window.google.maps.places) {
                        loadGoogleMapsAPI(initAutocomplete);
                    } else {
                        setTimeout(initAutocomplete, 100);
                    }
                });
            }
            @endif
            
            const isProfileComplete = @json($isProfileComplete);
            const isGuest = @json($isGuest);
            console.log('Script loaded. isGuest:', isGuest, 'isProfileComplete:', isProfileComplete);
            
            const clientProfileModal = document.getElementById('clientProfileModal');
            const closeClientProfileModalBtn = document.getElementById('closeClientProfileModalBtn');
            const clientProfileForm = document.getElementById('clientProfileForm');
            const userData = @json($user);
            let areAdditionalFieldsVisible = false;

            const additionalFieldsContainer = document.getElementById('additional-fields-container');
            const toggleAdditionalFieldsBtn = document.getElementById('toggleAdditionalFieldsBtn');
            const toggleText = document.getElementById('toggleText');

            function toggleAdditionalFields(show) {
                if (!additionalFieldsContainer) return;
                if (show) {
                    additionalFieldsContainer.classList.remove('hidden');
                } else {
                    additionalFieldsContainer.classList.add('hidden');
                }
                areAdditionalFieldsVisible = !additionalFieldsContainer.classList.contains('hidden');
            }

            // Hide toggle button by default; optional fields will only show for company
            if (toggleAdditionalFieldsBtn) {
                toggleAdditionalFieldsBtn.classList.add('hidden');
            }

            // Handle Particulier vs Société toggle
            const clientParticulierRadio = document.getElementById('client-particulier');
            const clientSocieteRadio = document.getElementById('client-societe');
            const societeFieldContainer = document.getElementById('societe-field-container');
            const societeInput = document.getElementById('modal-nomSociete');

            function toggleSocieteField() {
                if (clientSocieteRadio && clientSocieteRadio.checked) {
                    // Show Société field
                    if (societeFieldContainer) {
                        societeFieldContainer.classList.remove('hidden');
                    }
                    if (societeInput) {
                        societeInput.setAttribute('required', 'required');
                    }
                    // Show additional fields for Société only
                    toggleAdditionalFields(true);
                } else {
                    // Hide Société field
                    if (societeFieldContainer) {
                        societeFieldContainer.classList.add('hidden');
                    }
                    if (societeInput) {
                        societeInput.removeAttribute('required');
                        societeInput.value = '';
                    }
                    // Hide additional fields for Particulier
                    toggleAdditionalFields(false);
                }
            }

            if (clientParticulierRadio) {
                clientParticulierRadio.addEventListener('change', toggleSocieteField);
            }
            if (clientSocieteRadio) {
                clientSocieteRadio.addEventListener('change', toggleSocieteField);
            }

            // === GESTION DES CHAMPS PREMIUM ===
            const premiumFieldsContainer = document.getElementById('premium-fields-modal-container');
            const premiumModalNotice = document.getElementById('premium-modal-notice');

            // Charger globalLieuxData depuis sessionStorage ou faire un appel API
            let globalLieuxData = [];
            let airportIdForLieux = null;

            try {
                const state = JSON.parse(sessionStorage.getItem('formState'));
                if (state && Array.isArray(state.globalLieuxData)) {
                    globalLieuxData = state.globalLieuxData;
                    airportIdForLieux = state.airportId;
                }
            } catch (e) {
                console.log('[Premium] Could not load globalLieuxData from sessionStorage');
            }

            // Fonction pour charger les lieux depuis l'API BDM
            async function loadLieuxFromApi(airportId) {
                if (!airportId) {
                    console.warn('[Premium] No airport ID available to load lieux');
                    return false;
                }

                try {
                    console.log('[Premium] Fetching lieux from API for airport:', airportId);
                    const response = await fetch('/api/plateforme/' + airportId + '/lieux', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.statut === 1 && Array.isArray(result.content)) {
                            globalLieuxData = result.content;
                            console.log('[Premium] Lieux loaded from API:', globalLieuxData.length, 'lieux');
                            return true;
                        }
                    }
                    console.warn('[Premium] API response not successful:', response.status);
                    return false;
                } catch (error) {
                    console.error('[Premium] Error fetching lieux from API:', error);
                    return false;
                }
            }

            // Initialisation asynchrone des lieux
            (async function initLieux() {
                // Si globalLieuxData est vide, essayer de charger depuis l'API
                if (!globalLieuxData || globalLieuxData.length === 0) {
                    // Try to get airportId from sessionStorage
                    try {
                        const state = JSON.parse(sessionStorage.getItem('formState'));
                        airportIdForLieux = state ? state.airportId : null;
                    } catch (e) {
                        console.log('[Premium] Could not get airportId from sessionStorage');
                    }

                    if (airportIdForLieux) {
                        await loadLieuxFromApi(airportIdForLieux);
                    }

                    // Fallback to static lieux only if API call failed
                    if (!globalLieuxData || globalLieuxData.length === 0) {
                        globalLieuxData = [
                            { id: 1, libelle: 'Lieu 1' },
                            { id: 2, libelle: 'Lieu 2' },
                            { id: 3, libelle: 'Lieu 3' },
                            { id: 4, libelle: 'Lieu 4' }
                        ];
                        console.log('[Premium] Using static lieux (1,2,3,4) as fallback');
                    }
                } else {
                    console.log('[Premium] Loaded globalLieuxData from sessionStorage:', globalLieuxData.length, 'lieux');
                }
            })();

            // Vérifier si premium est dans le panier
            function hasPremiumInCart() {
                // cartItems est stocké dans formState, pas dans cartItems directement
                const state = JSON.parse(sessionStorage.getItem('formState'));
                if (!state || !state.cartItems) return false;
                try {
                    const cart = state.cartItems;
                    return Array.isArray(cart) && cart.some(item => item.key === 'premium');
                } catch (e) {
                    console.error('[hasPremiumInCart] Error:', e);
                    return false;
                }
            }
            
            // Afficher les champs premium si nécessaire
            function updatePremiumFieldsVisibility() {
                const hasPremium = hasPremiumInCart();
                console.log('[updatePremiumFieldsVisibility] hasPremium:', hasPremium);
                console.log('[updatePremiumFieldsVisibility] premiumFieldsContainer:', premiumFieldsContainer);
                console.log('[updatePremiumFieldsVisibility] premiumModalNotice:', premiumModalNotice);
                
                if (premiumFieldsContainer && premiumModalNotice) {
                    if (hasPremium) {
                        premiumFieldsContainer.classList.remove('hidden');
                        premiumModalNotice.classList.remove('hidden');
                        console.log('[updatePremiumFieldsVisibility] Premium fields shown');
                    } else {
                        premiumFieldsContainer.classList.add('hidden');
                        premiumModalNotice.classList.add('hidden');
                        console.log('[updatePremiumFieldsVisibility] Premium fields hidden (no premium in cart)');
                    }
                }
            }
            
            // Appeler au chargement et à l'ouverture du modal
            updatePremiumFieldsVisibility();
            
            // Handler for modal open button - with async lieux loading
            if (openClientProfileModalBtn) {
                openClientProfileModalBtn.addEventListener('click', async () => {
                    // Wait for lieux to be loaded if not already done
                    let waitForLieux = setInterval(() => {
                        if (globalLieuxData && globalLieuxData.length > 0) {
                            clearInterval(waitForLieux);
                            updatePremiumFieldsVisibility();
                            fillPremiumLocations();
                        }
                    }, 100);
                    
                    // Timeout safety: force display after 2 seconds even if lieux not loaded
                    setTimeout(() => {
                        clearInterval(waitForLieux);
                        updatePremiumFieldsVisibility();
                        fillPremiumLocations();
                    }, 2000);
                });
            }
            
            // Gestion des champs dynamiques transport (avion/train)
            const setupTransportFieldHandler = (direction) => {
                const transportSelect = document.querySelector(`select[name="transport_type_${direction}"]`);
                const flightContainer = document.getElementById(`flight_number_${direction}_container`);
                const trainContainer = document.getElementById(`train_number_${direction}_container`);
                
                if (transportSelect) {
                    transportSelect.addEventListener('change', (e) => {
                        const value = e.target.value;
                        if (flightContainer) flightContainer.classList.toggle('hidden', value !== 'airport');
                        if (trainContainer) trainContainer.classList.toggle('hidden', value !== 'train');
                    });
                    // Trigger initial state
                    transportSelect.dispatchEvent(new Event('change'));
                }
            };
            
            setupTransportFieldHandler('arrival');
            setupTransportFieldHandler('departure');
            
            // Remplir les lieux depuis globalLieuxData
            function fillPremiumLocations() {
                const arrivalSelect = document.getElementById('modal-pickup-location-arrival');
                const departureSelect = document.getElementById('modal-restitution-location-departure');
                
                if (typeof globalLieuxData !== 'undefined' && Array.isArray(globalLieuxData) && globalLieuxData.length > 0) {
                    const optionsHTML = globalLieuxData.map(lieu => 
                        `<option value="${lieu.id}">${lieu.libelle || 'Lieu ' + lieu.id}</option>`
                    ).join('');
                    
                    if (arrivalSelect) {
                        arrivalSelect.innerHTML = '<option value="">Sélectionner</option>' + optionsHTML;
                    }
                    if (departureSelect) {
                        departureSelect.innerHTML = '<option value="">Sélectionner</option>' + optionsHTML;
                    }
                } else {
                    // Lieux statiques par défaut (1,2,3,4)
                    const staticOptionsHTML = [
                        { id: 1, libelle: 'Lieu 1' },
                        { id: 2, libelle: 'Lieu 2' },
                        { id: 3, libelle: 'Lieu 3' },
                        { id: 4, libelle: 'Lieu 4' }
                    ].map(lieu => `<option value="${lieu.id}">${lieu.libelle}</option>`).join('');
                    
                    if (arrivalSelect) {
                        arrivalSelect.innerHTML = '<option value="">Sélectionner</option>' + staticOptionsHTML;
                    }
                    if (departureSelect) {
                        departureSelect.innerHTML = '<option value="">Sélectionner</option>' + staticOptionsHTML;
                    }
                }
            }
            
            // Remplir les lieux quand le modal s'ouvre
            if (openClientProfileModalBtn) {
                openClientProfileModalBtn.addEventListener('click', () => {
                    setTimeout(() => {
                        fillPremiumLocations();
                        fillPremiumDatetimeFields();
                    }, 500);
                });
            }
            
            // Remplir les champs datetime avec les valeurs du formulaire
            function fillPremiumDatetimeFields() {
                // Essayer de récupérer depuis sessionStorage (state.js)
                let dateDepot = null, heureDepot = null, dateRecuperation = null, heureRecuperation = null;
                
                try {
                    const state = JSON.parse(sessionStorage.getItem('formState'));
                    if (state) {
                        dateDepot = state.dateDepot;
                        heureDepot = state.heureDepot;
                        dateRecuperation = state.dateRecuperation;
                        heureRecuperation = state.heureRecuperation;
                        console.log('[fillPremiumDatetimeFields] Loaded from sessionStorage:', { dateDepot, heureDepot, dateRecuperation, heureRecuperation });
                    }
                } catch (e) {
                    console.log('[fillPremiumDatetimeFields] Could not load from sessionStorage');
                }
                
                // Fallback: essayer de récupérer depuis les éléments du DOM (si présents)
                if (!dateDepot) {
                    const dateDepotInput = document.getElementById('date-depot');
                    if (dateDepotInput) dateDepot = dateDepotInput.value;
                }
                if (!heureDepot) {
                    const heureDepotInput = document.getElementById('heure-depot');
                    if (heureDepotInput) heureDepot = heureDepotInput.value;
                }
                if (!dateRecuperation) {
                    const dateRecupInput = document.getElementById('date-recuperation');
                    if (dateRecupInput) dateRecuperation = dateRecupInput.value;
                }
                if (!heureRecuperation) {
                    const heureRecupInput = document.getElementById('heure-recuperation');
                    if (heureRecupInput) heureRecuperation = heureRecupInput.value;
                }
                
                const pickupDatetimeInput = document.getElementById('pickup-datetime-arrival');
                const restitutionDatetimeInput = document.getElementById('restitution-datetime-departure');
                
                console.log('[fillPremiumDatetimeFields] Final values:', { dateDepot, heureDepot, dateRecuperation, heureRecuperation });
                
                // Format datetime-local: YYYY-MM-DDTHH:MM
                if (dateDepot && heureDepot && pickupDatetimeInput) {
                    // Pour les champs text (heure-depot), extraire juste l'heure HH:MM
                    let heureValue = heureDepot.trim();
                    // Si le format est "HH:MM:00" ou similaire, prendre juste HH:MM
                    if (heureValue.includes(':')) {
                        const timeParts = heureValue.split(':');
                        heureValue = timeParts[0] + ':' + (timeParts[1] || '00');
                    }
                    pickupDatetimeInput.value = `${dateDepot}T${heureValue}`;
                    console.log('[fillPremiumDatetimeFields] Pickup datetime set to:', pickupDatetimeInput.value);
                }
                
                if (dateRecuperation && heureRecuperation && restitutionDatetimeInput) {
                    let heureValue = heureRecuperation.trim();
                    if (heureValue.includes(':')) {
                        const timeParts = heureValue.split(':');
                        heureValue = timeParts[0] + ':' + (timeParts[1] || '00');
                    }
                    restitutionDatetimeInput.value = `${dateRecuperation}T${heureValue}`;
                    console.log('[fillPremiumDatetimeFields] Restitution datetime set to:', restitutionDatetimeInput.value);
                }
            }

            // Initialize Société field visibility on page load
            toggleSocieteField();

            function validateGuestForm() {
                console.log('validateGuestForm called');

                const requiredFields = [
                    'modal-prenom',
                    'modal-nom',
                    'modal-telephone',
                    'modal-adresse'
                ];

                let isValid = true;

                requiredFields.forEach(fieldId => {
                    const input = document.getElementById(fieldId);
                    if (!input || input.value.trim() === '') {
                        isValid = false;
                        if (input) input.classList.add('input-error');
                    } else {
                        input.classList.remove('input-error');
                    }
                });

                // Validation téléphone via ITI
                const phoneEl = document.getElementById('modal-telephone');
                if (phoneEl && itiInstance) {
                    const raw = phoneEl.value.trim();
                    if (raw) {
                        const countryData = itiInstance.getSelectedCountryData();
                        const normalized = normalizePhoneNumber(raw, countryData);
                        if (!normalized) {
                            isValid = false;
                            setPhoneError(phoneEl, t('phone_country_code_hint', 'Numéro de téléphone invalide.'));
                        } else {
                            phoneEl.value = normalized;
                            clearPhoneError(phoneEl);
                        }
                    }
                }

                const optionalValidators = [];

                // Add Société validation if Société option is selected
                if (clientSocieteRadio && clientSocieteRadio.checked) {
                    const societeField = document.getElementById('modal-nomSociete');
                    if (!societeField || societeField.value.trim() === '') {
                        isValid = false;
                        if (societeField) societeField.classList.add('input-error');
                    } else {
                        if (societeField) societeField.classList.remove('input-error');
                    }
                }

                optionalValidators.forEach(({ id, validate }) => {
                    const input = document.getElementById(id);
                    if (input && input.value.trim() !== '') {
                        if (!validate(input.value.trim())) {
                            isValid = false;
                            input.classList.add('input-error');
                        } else {
                            input.classList.remove('input-error');
                        }
                    } else if (input) {
                        input.classList.remove('input-error');
                    }
                });

                console.log('Validation result:', isValid);
                return isValid;
            }

            if (openClientProfileModalBtn) {
                openClientProfileModalBtn.addEventListener('click', () => {
                    if (additionalFieldsContainer) {
                        additionalFieldsContainer.classList.add('hidden');
                        areAdditionalFieldsVisible = false;
                        toggleText.textContent = "Compléter mon profil (facultatif)";
                    }

                    document.getElementById('modal-email').value = userData.email || '';
                    document.getElementById('modal-nom').value = (isGuest && (userData.nom === 'Invité' || userData.nom === null)) ? '' : (userData.nom || '');
                    document.getElementById('modal-prenom').value = (isGuest && (userData.prenom === 'Client' || userData.prenom === null)) ? '' : (userData.prenom || '');
                    document.getElementById('modal-telephone').value = userData.telephone || '';
                    document.getElementById('modal-adresse').value = userData.adresse || '';
                    document.getElementById('modal-nomSociete').value = userData.nomSociete || '';

                    clientProfileModal.classList.remove('hidden');
                });
            }

            if (closeClientProfileModalBtn) {
                closeClientProfileModalBtn.addEventListener('click', () => {
                    clientProfileModal.classList.add('hidden');
                });
            }

            if (clientProfileForm) {
                let isSubmitting = false; // Prevent double submission
                
                clientProfileForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    // Prevent double submission
                    if (isSubmitting) {
                        console.log('[MODAL SUBMIT] Already submitting, ignoring...');
                        return;
                    }
                    
                    console.log('=== [MODAL SUBMIT] Form submission intercepted ===');
                    console.log('[MODAL SUBMIT] isGuest:', isGuest);
                    console.log('[MODAL SUBMIT] Form data:', Object.fromEntries(new FormData(clientProfileForm).entries()));
                    
                    isSubmitting = true;
                    const submitBtn = document.getElementById('saveClientProfileBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg> Enregistrement...';
                    }

                    if (isGuest) {
                        console.log('[MODAL SUBMIT] Running validation for guest...');
                        if (!validateGuestForm()) {
                            console.log('[MODAL SUBMIT] Validation failed. Submission stopped.');
                            isSubmitting = false;
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Confirmer et payer <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>';
                            }
                            await showCustomAlert(t('error'), t('alert_fill_required'));
                            return;
                        }
                        console.log('[MODAL SUBMIT] Validation passed.');
                    }
                    
                    // === VALIDATION DES CHAMPS PREMIUM ===
                    if (hasPremiumInCart()) {
                        const premiumFieldsContainer = document.getElementById('premium-fields-modal-container');
                        if (premiumFieldsContainer && !premiumFieldsContainer.classList.contains('hidden')) {
                            let premiumValid = true;
                            let missingFields = [];
                            
                            // Validate arrival fields
                            const transportArrival = document.querySelector('select[name="transport_type_arrival"]');
                            const pickupLocation = document.querySelector('select[name="pickup_location_arrival"]');
                            const pickupDatetime = document.querySelector('input[name="pickup_datetime_arrival"]');
                            
                            if (!transportArrival || !transportArrival.value) {
                                premiumValid = false;
                                missingFields.push('Type de transport (arrivée)');
                            }
                            if (!pickupLocation || !pickupLocation.value) {
                                premiumValid = false;
                                missingFields.push('Lieu de prise en charge');
                            }
                            if (!pickupDatetime || !pickupDatetime.value) {
                                premiumValid = false;
                                missingFields.push('Date et heure de prise en charge');
                            }
                            
                            // Validate departure fields
                            const transportDeparture = document.querySelector('select[name="transport_type_departure"]');
                            const restitutionLocation = document.querySelector('select[name="restitution_location_departure"]');
                            const restitutionDatetime = document.querySelector('input[name="restitution_datetime_departure"]');
                            
                            if (!transportDeparture || !transportDeparture.value) {
                                premiumValid = false;
                                missingFields.push('Type de transport (départ)');
                            }
                            if (!restitutionLocation || !restitutionLocation.value) {
                                premiumValid = false;
                                missingFields.push('Lieu de restitution');
                            }
                            if (!restitutionDatetime || !restitutionDatetime.value) {
                                premiumValid = false;
                                missingFields.push('Date et heure de restitution');
                            }
                            
                            if (!premiumValid) {
                                isSubmitting = false;
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Confirmer et payer <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>';
                                }
                                await showCustomAlert('Informations PREMIUM incomplètes', 
                                    'Veuillez remplir tous les champs obligatoires pour le service PREMIUM :<br>' + 
                                    missingFields.join(', '));
                                return;
                            }
                            console.log('[MODAL SUBMIT] Premium validation passed.');
                        }
                    }

                    // Auto-split long address to fit BDM 50-char limit
                    const addressInput = document.getElementById('modal-adresse');
                    if (addressInput && addressInput.value && addressInput.value.length > 50) {
                        const fullAddress = addressInput.value.trim();
                        const mainAddress = fullAddress.slice(0, 50).trim();
                        addressInput.value = mainAddress;
                        console.log('[MODAL SUBMIT] Address truncated to 50 chars:', mainAddress);
                    }

                    const formData = new FormData(clientProfileForm);
                    const data = Object.fromEntries(formData.entries());

                    // === AJOUT DES INFOS PREMIUM DANS LE PAYLOAD ===
                    if (hasPremiumInCart()) {
                        // Récupérer les libellés des lieux sélectionnés
                        const pickupLocationSelect = document.getElementById('modal-pickup-location-arrival');
                        const restitutionLocationSelect = document.getElementById('modal-restitution-location-departure');
                        const pickupLocationLibelle = pickupLocationSelect && pickupLocationSelect.options[pickupLocationSelect.selectedIndex]
                            ? pickupLocationSelect.options[pickupLocationSelect.selectedIndex].text
                            : '';
                        const restitutionLocationLibelle = restitutionLocationSelect && restitutionLocationSelect.options[restitutionLocationSelect.selectedIndex]
                            ? restitutionLocationSelect.options[restitutionLocationSelect.selectedIndex].text
                            : '';
                        
                        // Séparer datetime en date et heure
                        const pickupDatetime = data.pickup_datetime_arrival || '';
                        const restitutionDatetime = data.restitution_datetime_departure || '';
                        
                        let dateArrival = '', timeArrival = '';
                        let dateDeparture = '', timeDeparture = '';
                        
                        if (pickupDatetime) {
                            const [arrivalDate, arrivalTime] = pickupDatetime.split('T');
                            dateArrival = arrivalDate || '';
                            timeArrival = arrivalTime || '';
                        }
                        
                        if (restitutionDatetime) {
                            const [departureDate, departureTime] = restitutionDatetime.split('T');
                            dateDeparture = departureDate || '';
                            timeDeparture = departureTime || '';
                        }
                        
                        // Collecter les infos premium
                        const premiumDetails = {
                            direction: 'both',
                            transport_type_arrival: data.transport_type_arrival || '',
                            transport_type_departure: data.transport_type_departure || '',
                            flight_number_arrival: data.flight_number_arrival || '',
                            flight_number_departure: data.flight_number_departure || '',
                            train_number_arrival: data.train_number_arrival || '',
                            train_number_departure: data.train_number_departure || '',
                            date_arrival: dateArrival,
                            pickup_time_arrival: timeArrival,
                            date_departure: dateDeparture,
                            restitution_time_departure: timeDeparture,
                            pickup_location_arrival: data.pickup_location_arrival || '',
                            pickup_location_arrival_libelle: pickupLocationLibelle,
                            restitution_location_departure: data.restitution_location_departure || '',
                            restitution_location_departure_libelle: restitutionLocationLibelle,
                            instructions_arrival: data.instructions_arrival || ''
                        };
                        
                        // Ajouter au payload pour l'envoi
                        data.premiumDetails = premiumDetails;
                        
                        console.log('[MODAL SUBMIT] Premium details collected:', premiumDetails);
                    }

                    for (const key in data) {
                        if (data[key] === '') {
                            data[key] = null;
                        }
                    }

                    const url = isGuest ? '{{ route("session.updateGuestInfo") }}' : '{{ route("client.update-profile") }}';
                    console.log('[MODAL SUBMIT] Sending request to:', url);
                    console.log('[MODAL SUBMIT] Payload:', data);

                    try {
                        clientProfileModal.classList.add('hidden');
                        console.log('[MODAL SUBMIT] Modal hidden, sending request...');

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(data)
                        });

                        console.log('[MODAL SUBMIT] Response status:', response.status);
                        const result = await response.json();
                        console.log('[MODAL SUBMIT] Response data:', result);

                        if (response.ok && result.success) {
                            console.log('[MODAL SUBMIT] SUCCESS - Redirecting to payment page...');
                            
                            // Update userData locally to reflect the changes
                            userData.prenom = data.prenom || userData.prenom;
                            userData.nom = data.nom || userData.nom;
                            userData.telephone = data.telephone || userData.telephone;
                            userData.adresse = data.adresse || userData.adresse;
                            
                            // Hide modal and show loader
                            clientProfileModal.classList.add('hidden');
                            const loader = document.getElementById('loader');
                            if (loader) {
                                loader.classList.remove('hidden');
                            }
                            
                            console.log('[MODAL SUBMIT] Profile saved, redirecting to payment page...');
                            // Redirect to payment page to refresh server session
                            window.location.href = '{{ route("payment") }}';
                            // Note: No need to reset isSubmitting here because page will reload
                        } else {
                            console.error('[MODAL SUBMIT] Server returned error:', result);
                            isSubmitting = false;
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Confirmer et payer <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>';
                            }
                            clientProfileModal.classList.remove('hidden');
                            let errorMessage = result.message || t('alert_unknown_error');
                            if (result.errors) {
                                errorMessage = t('alert_fix_errors') + '\n';
                                Object.values(result.errors).forEach(errorArray => {
                                    errorMessage += `\n- ${errorArray[0]}`;
                                });
                            }
                            await showCustomAlert(t('alert_update_error_title'), errorMessage);
                            console.error('[MODAL SUBMIT] Update error:', result);
                        }
                    } catch (error) {
                        console.error('[MODAL SUBMIT] Network error:', error);
                        isSubmitting = false;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Confirmer et payer <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>';
                        }
                        clientProfileModal.classList.remove('hidden');
                        await showCustomAlert(t('error'), t('alert_network_error'));
                    }
                });
            }

            // Auto-open modal if profile is not complete
            // The server-side isProfileComplete will be updated after redirect
            if (!isProfileComplete && openClientProfileModalBtn) {
                console.log('[AUTO-OPEN] Profile incomplete, opening modal automatically...');
                setTimeout(() => {
                    openClientProfileModalBtn.click();
                }, 500);
            }

            const paymentResetBtn = document.getElementById('payment-reset-btn');
            if (paymentResetBtn) {
                const modalHTML = `
                    <div id="payment-reset-confirm-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center px-4">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <h3 class="text-xl font-bold text-gray-800">${t('payment_reset_title')}</h3>
                            <p class="mt-4 text-gray-600">${t('payment_reset_text')}</p>
                            <div class="mt-6 flex justify-end space-x-4">
                                <button id="payment-reset-cancel-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300">${t('payment_reset_cancel')}</button>
                                <button id="payment-reset-confirm-btn" class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700">${t('payment_reset_confirm')}</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                const resetModal = document.getElementById('payment-reset-confirm-modal');
                const cancelBtn = document.getElementById('payment-reset-cancel-btn');
                const confirmBtn = document.getElementById('payment-reset-confirm-btn');

                const showResetConfirm = () => {
                    return new Promise(resolve => {
                        resetModal.classList.remove('hidden');
                        cancelBtn.onclick = () => {
                            resetModal.classList.add('hidden');
                            resolve(false);
                        };
                        confirmBtn.onclick = () => {
                            resetModal.classList.add('hidden');
                            resolve(true);
                        };
                    });
                };

                paymentResetBtn.addEventListener('click', async function() {
                    const confirmed = await showResetConfirm();

                    if (confirmed) {
                        const loader = document.getElementById('loader');
                        if (loader) loader.classList.remove('hidden');

                        sessionStorage.removeItem('formState');
                        
                        try {
                            await fetch('{{ route("session.reset") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });
                        } catch (error) {
                            console.error('Failed to reset server session:', error);
                        }

                        setTimeout(() => {
                            window.location.href = '{{ route("form-consigne") }}';
                        }, 500);
                    }
                });
            }
            
            if (window.location.search.includes('nocache') || !window.google) {
                const links = document.querySelectorAll('link[rel="stylesheet"]');
                links.forEach(link => {
                    if (link.href) {
                        link.href = link.href.split('?')[0] + '?v=' + googleApiVersion;
                    }
                });
            }

            @if(session('error'))
                showCustomAlert(t('error'), '{{ session('error') }}');
            @endif

            // Vérifier que le formulaire Monetico est bien chargé (pour info seulement)
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const moneticoForm = document.querySelector('.kr-smart-form');
                    if (moneticoForm) {
                        console.log('✅ Formulaire Monetico détecté et prêt');
                    } else {
                        console.warn('⚠️ Formulaire Monetico non détecté. Vérifiez que le formToken est valide.');
                    }
                }, 2000);
            });
        });
    </script>
</div> {{-- End hp-payment-root --}}
@endsection




