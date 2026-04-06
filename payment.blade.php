@php
    $commandeData = Session::get('commande_en_cours');
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon-hellopassenger.png') }}">
    <title>Finaliser le Paiement - HelloPassenger</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phone input: user must type country code (+..) -->
    
    <!-- Scripts Monetico -->
    <script
        src="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
        kr-public-key="43559169:testpublickey_TpUnzWl3wta3iKfuUeeYylRCWZ99SwdFKQktpbbxaOdxz"
        kr-post-url-success="{{ route('payment.success') }}"
        kr-post-url-refused="{{ route('payment.error') }}"
        kr-post-url-canceled="{{ route('payment.cancel') }}">
    </script>
    <link rel="stylesheet" href="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/ext/neon-reset.min.css">
    <script src="https://api.gateway.monetico-retail.com/static/js/krypton-client/V4.0/ext/neon.js"></script>
    
    <!-- Translation System - MUST BE EARLY -->
    <script src="{{ asset('js/translations-simple.js') }}"></script>
    
    <!-- Google Places API - Chargement conditionnel -->
    @php
        $googlePlacesApiKey = config('services.google.places_api_key');
        $isProduction = app()->environment('production');
    @endphp
    
    <style>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'yellow-custom': '#FFC107',
                        'yellow-hover': '#FFB300',
                        'gray-dark': '#1f2937'
                    }
                }
            }
        }
        
        .input-style {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .input-style:focus {
            border-color: #FFC107;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        }
        
        .input-error {
            border: 2px solid #ef4444 !important;
        }
        
        .custom-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #FFC107;
            border-radius: 50%;
            width: 1.5em;
            height: 1.5em;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Cache busting pour les fichiers statiques */
        .cache-bust {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">
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

    @include('Front.header-front')

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
                                    // IMPORTANT: Forcer le fuseau horaire Europe/Paris pour éviter le décalage UTC
                                    $dateDebut = \Carbon\Carbon::parse($firstLigne['dateDebut'], 'Europe/Paris');
                                    $dateFin = \Carbon\Carbon::parse($firstLigne['dateFin'], 'Europe/Paris');
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
                                        @if(isset($commandeData['total_normal_price']) && $commandeData['discount_amount'] > 0)
                                            @php
                                                // Calculate normal price for this item (with 10% added back)
                                                $itemNormalPrice = $ligne['prixTTC'] / 0.9;
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
                            @if($hasSavedCard && $savedCardInfo)
                                <!-- Option pour utiliser la carte sauvegardée -->
                                <div id="saved-card-option" class="mb-4 p-4 bg-gray-50 rounded-lg border-2 border-yellow-custom">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <input type="radio" id="use-saved-card" name="payment-method" value="saved" checked class="w-5 h-5 text-yellow-custom cursor-pointer">
                                            <label for="use-saved-card" class="cursor-pointer">
                                                <span class="font-semibold text-gray-800" data-i18n="use_saved_card">Utiliser ma carte sauvegardée</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-left pl-8">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="font-bold text-gray-700">{{ $savedCardInfo['type'] }}</span>
                                            <span class="text-gray-600">•••• •••• •••• {{ $savedCardInfo['last4'] }}</span>
                                        </div>
                                        @if($savedCardInfo['nom'])
                                            <p class="text-sm text-gray-600">{{ $savedCardInfo['nom'] }}</p>
                                        @endif
                                        @if($savedCardInfo['expiry'])
                                            <p class="text-sm text-gray-600" data-i18n="expires">Expire le {{ $savedCardInfo['expiry'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Option pour utiliser une nouvelle carte -->
                                <div id="new-card-option" class="mb-4 p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <input type="radio" id="use-new-card" name="payment-method" value="new" class="w-5 h-5 text-yellow-custom cursor-pointer">
                                            <label for="use-new-card" class="cursor-pointer">
                                                <span class="font-semibold text-gray-800" data-i18n="use_new_card">Utiliser une nouvelle carte</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulaire Monetico (caché par défaut si carte sauvegardée disponible) -->
                                <div id="monetico-form-container" class="hidden">
                                    <div class="kr-smart-form mx-auto" kr-form-token="{{ $formToken }}"></div>
                                </div>
                                
                                <!-- Bouton pour payer avec la carte sauvegardée -->
                                <div id="saved-card-payment-btn" class="mt-4">
                                    <button id="pay-with-saved-card-btn" class="w-full bg-yellow-custom text-gray-dark py-4 rounded-full font-bold hover:bg-yellow-hover transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3 text-lg" data-i18n="pay_with_saved_card">
                                        Payer avec ma carte sauvegardée
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <!-- Pas de carte sauvegardée, afficher le formulaire normal -->
                                <div class="kr-smart-form mx-auto" kr-form-token="{{ $formToken }}"></div>
                                
                                <!-- Debug: Log form token -->
                                <script>
                                    console.log('[Monetico Debug] Form Token:', '{{ $formToken }}');
                                    console.log('[Monetico Debug] hasSavedCard:', '{{ $hasSavedCard ?? false }}');
                                    console.log('[Monetico Debug] isProfileComplete:', '{{ $isProfileComplete ?? false }}');
                                    
                                    // Wait for Monetico script to load
                                    window.addEventListener('load', function() {
                                        console.log('[Monetico Debug] Window loaded');
                                        console.log('[Monetico Debug] KR object:', typeof window.KR);
                                        console.log('[Monetico Debug] KRPaymentForm:', typeof window.KRPaymentForm);
                                    });
                                </script>
                            @endif
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

    @include('Front.footer-front')
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

            // Essayer d'abord avec région vide (pour numéros avec code pays)
            try {
                const parsed = window.intlTelInputUtils.parseNumber(phoneNumber, '');
                if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                    const cc = parsed.getCountryCode();
                    const nn = parsed.getNationalNumber();
                    
                    if (cc && nn) {
                        const regionCode = window.intlTelInputUtils.getRegionCodeForCountryCode(cc);
                        if (regionCode) {
                            console.log('🌐 Auto-detected (with intl code):', regionCode, '(country code:', cc, ')');
                            return regionCode;
                        }
                    }
                }
            } catch (e) {
                console.log('  → Parse attempt 1 (empty region) failed:', e.message);
            }

            // Si le numéro ne commence pas par +, essayer en ajoutant TOUS les codes pays (1-999)
            if (!phoneNumber.startsWith('+')) {
                console.log('  → Trying all country codes 1-999 for bare number...');
                
                for (let cc = 1; cc <= 999; cc++) {
                    try {
                        // Essayer en préfixant avec le code pays
                        const testNumber = '+' + cc + phoneNumber;
                        const parsed = window.intlTelInputUtils.parseNumber(testNumber, '');
                        
                        if (parsed && parsed.getCountryCode && parsed.getNationalNumber) {
                            const parsedCC = parsed.getCountryCode();
                            const nn = parsed.getNationalNumber();
                            
                            // Vérifier que le code pays correspond et que le numéro national est valide
                            if (parsedCC === cc && nn && String(nn).length >= 6) {
                                const regionCode = window.intlTelInputUtils.getRegionCodeForCountryCode(cc);
                                if (regionCode) {
                                    console.log('🌐 Auto-detected (country code', cc, '):', regionCode);
                                    return regionCode;
                                }
                            }
                        }
                    } catch (e) {
                        // Continue to next code - fail silently for speed
                    }
                }
            }

            console.log('  → No country auto-detection successful');
            return null;
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
                    confirmBtn.textContent = 'OK';
                } else {
                    confirmBtn.textContent = 'OK';
                }

                modal.classList.remove('hidden');

                const closeModal = () => {
                    modal.classList.add('hidden');
                    // No redirect on error - stay on payment page
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
            if (phoneInput && false) {
                itiInstance = window.intlTelInput(phoneInput, {
                    initialCountry: "fr",
                    preferredCountries: ["fr", "be", "ch", "ca", "mu"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                    formatOnDisplay: false,
                    autoPlaceholder: "aggressive",
                    separateDialCode: false,
                    nationalMode: false
                });

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

                // Permettre la saisie libre
                phoneInput.addEventListener('input', function(e) {
                    let value = phoneInput.value;
                    value = value.replace(/[^\d+\s\-\(\)]/g, '');
                    phoneInput.value = value;
                    
                    if (detectionTimeout) {
                        clearTimeout(detectionTimeout);
                    }
                    
                    detectionTimeout = setTimeout(function() {
                        detectCountryFromNumber(value);
                    }, 800);
                });

                phoneInput.addEventListener('blur', function() {
                    if (detectionTimeout) {
                        clearTimeout(detectionTimeout);
                    }
                    detectCountryFromNumber(phoneInput.value);
                });

                // Fonction de détection basée sur les règles prédéfinies
                function detectCountryFromNumber(value) {
                    const cleanValue = value.replace(/\D/g, '');
                    
                    if (cleanValue.length < 8) {
                        return;
                    }
                    
                    console.log('🔍 Détection pour:', cleanValue);
                    
                    let detectedCountry = null;
                    let highestScore = 0;
                    const topMatches = [];
                    
                    // Si commence par +, extraire le code pays
                    if (value.trim().startsWith('+')) {
                        for (const [country, rules] of Object.entries(countryRules)) {
                            if (cleanValue.startsWith(rules.code)) {
                                const remainingDigits = cleanValue.substring(rules.code.length);
                                if (remainingDigits.length === rules.nationalLength) {
                                    detectedCountry = country;
                                    console.log(`✅ Format international: +${rules.code} → ${country}`);
                                    break;
                                }
                            }
                        }
                    } else {
                        // Tester chaque pays avec scoring intelligent
                        for (const [country, rules] of Object.entries(countryRules)) {
                            let score = 0;
                            let reason = '';
                            
                            // Test 1: Format international sans + (ex: 33612345678)
                            if (cleanValue.startsWith(rules.code)) {
                                const remaining = cleanValue.substring(rules.code.length);
                                if (remaining.length === rules.nationalLength) {
                                    score = 100;
                                    reason = 'Intl';
                                }
                            }
                            // Test 2: Format national avec préfixe (ex: 0612345678)
                            else if (rules.nationalPrefix && cleanValue.startsWith(rules.nationalPrefix)) {
                                if (cleanValue.length === rules.nationalLength) {
                                    score = 90;
                                    reason = 'Nat+prefix';
                                    // Bonus si mobile
                                    for (const mobileStart of rules.mobileStarts) {
                                        if (cleanValue.startsWith(mobileStart)) {
                                            score = 95;
                                            reason = 'Mobile';
                                            break;
                                        }
                                    }
                                }
                            }
                            // Test 3: Format national sans préfixe
                            else if (!rules.nationalPrefix && cleanValue.length === rules.nationalLength) {
                                score = 70;
                                reason = 'Nat-prefix';
                                // Bonus IMPORTANT si commence par identifiant mobile
                                for (const mobileStart of rules.mobileStarts) {
                                    if (cleanValue.startsWith(mobileStart)) {
                                        score = 90;
                                        reason = 'Mobile!';
                                        break;
                                    }
                                }
                            }
                            
                            // Bonus supplémentaire si pays préféré
                            if (score > 0 && preferredCountries.includes(country)) {
                                score += 15;
                            }
                            
                            if (score > 0) {
                                topMatches.push({ country, score, reason });
                                if (score > highestScore) {
                                    highestScore = score;
                                    detectedCountry = country;
                                }
                            }
                        }
                        
                        if (topMatches.length > 0) {
                            console.log('📊 Top matches:');
                            topMatches.sort((a,b) => b.score - a.score).slice(0,5).forEach(m => {
                                console.log(`   ${m.country}: ${m.score} (${m.reason})`);
                            });
                        }
                        
                        if (detectedCountry) {
                            console.log(`✅ Choix: ${detectedCountry}`);
                        }
                    }
                    
                    // Appliquer le pays détecté
                    if (detectedCountry) {
                        const currentCountry = itiInstance.getSelectedCountryData();
                        const detectedLower = detectedCountry.toLowerCase();
                        
                        if (currentCountry.iso2 !== detectedLower) {
                            itiInstance.setCountry(detectedLower);
                            console.log('🌍 Changé vers:', detectedCountry);
                        }
                    }
                }

                // Validation finale au blur
                phoneInput.addEventListener('blur', function() {
                    const value = phoneInput.value.trim();
                    
                    if (!value) {
                        phoneInput.classList.remove('input-error');
                        const errorMsg = phoneInput.parentElement.querySelector('.phone-error-msg');
                        if (errorMsg) errorMsg.remove();
                        return;
                    }
                    
                    console.log('🔍 Validation du numéro...');
                    
                    let countryData = itiInstance.getSelectedCountryData();
                    let countryCodeUpper = String(countryData.iso2 || '').toUpperCase();
                    const normalized = normalizePhoneNumber(value, countryData);
                    
                    // Tenter de détecter le pays depuis le numéro
                    const detectedRegion = detectCountryFromNumber(normalized, itiInstance);
                    if (detectedRegion && detectedRegion !== countryCodeUpper) {
                        console.log('🔄 Switching country to detected:', detectedRegion);
                        itiInstance.setCountry(detectedRegion.toLowerCase());
                        countryData = itiInstance.getSelectedCountryData();
                        countryCodeUpper = detectedRegion;
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
                            console.log('✅ Numéro valide:', formatted);
                        } else {
                            phoneInput.classList.add('input-error');
                            
                            let errorMsg = phoneInput.parentElement.querySelector('.phone-error-msg');
                            if (!errorMsg) {
                                errorMsg = document.createElement('p');
                                errorMsg.className = 'phone-error-msg text-red-500 text-sm mt-1';
                                phoneInput.parentElement.appendChild(errorMsg);
                            }
                            
                            const errorCode = itiInstance.getValidationError();
                            const errors = {
                                0: t('phone_invalid'),
                                1: t('phone_invalid_country'),
                                2: t('phone_too_short'),
                                3: t('phone_too_long'),
                                4: t('phone_invalid_format')
                            };
                            errorMsg.textContent = errors[errorCode] || t('phone_invalid');
                            console.log('❌ Numéro invalide:', errors[errorCode]);
                        }
                    }, 100);
                });

                // Re-normaliser au changement de pays
                phoneInput.addEventListener('countrychange', function() {
                    const value = phoneInput.value.trim();
                    if (value) {
                        console.log('🌍 Changement de pays');
                        phoneInput.dispatchEvent(new Event('blur'));
                    }
                });
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

                // Validation téléphone (E.164 + code pays obligatoire)
                const phoneInput = document.getElementById('modal-telephone');
                if (phoneInput && phoneInput.value.trim()) {
                    const normalized = normalizeE164Phone(phoneInput.value);
                    if (!normalized) {
                        isValid = false;
                        setPhoneError(
                            phoneInput,
                            t('phone_country_code_hint', 'Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France).')
                        );
                    } else {
                        phoneInput.value = normalized;
                        clearPhoneError(phoneInput);
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

            // Gestion de la carte sauvegardée
            @if($hasSavedCard && $savedCardInfo)
            const useSavedCardRadio = document.getElementById('use-saved-card');
            const useNewCardRadio = document.getElementById('use-new-card');
            const moneticoFormContainer = document.getElementById('monetico-form-container');
            const savedCardPaymentBtn = document.getElementById('saved-card-payment-btn');
            const payWithSavedCardBtn = document.getElementById('pay-with-saved-card-btn');

            function togglePaymentMethod() {
                if (useSavedCardRadio && useSavedCardRadio.checked) {
                    // Afficher le bouton de paiement avec carte sauvegardée
                    if (savedCardPaymentBtn) savedCardPaymentBtn.classList.remove('hidden');
                    if (moneticoFormContainer) moneticoFormContainer.classList.add('hidden');
                } else if (useNewCardRadio && useNewCardRadio.checked) {
                    // Afficher le formulaire Monetico
                    if (savedCardPaymentBtn) savedCardPaymentBtn.classList.add('hidden');
                    if (moneticoFormContainer) moneticoFormContainer.classList.remove('hidden');
                }
            }

            if (useSavedCardRadio) {
                useSavedCardRadio.addEventListener('change', togglePaymentMethod);
            }
            if (useNewCardRadio) {
                useNewCardRadio.addEventListener('change', togglePaymentMethod);
            }

            // Initialiser l'état par défaut (carte sauvegardée sélectionnée)
            togglePaymentMethod();

            // Gérer le paiement avec la carte sauvegardée
            if (payWithSavedCardBtn) {
                payWithSavedCardBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Traitement...</span>';
                    
                    try {
                        // Pour l'instant, on utilise le formulaire Monetico normal
                        // Monetico nécessiterait un token de carte pour les paiements récurrents
                        // On va donc afficher le formulaire Monetico mais avec un message indiquant que la carte sera utilisée
                        await showCustomAlert(
                            t('info') || 'Information',
                            '{{ trans("payment.saved_card_note", [], "fr") ?? "Pour des raisons de sécurité, veuillez confirmer votre carte. Les informations de votre carte sauvegardée seront pré-remplies." }}'
                        );
                        
                        // Basculer vers le formulaire Monetico
                        if (useNewCardRadio) {
                            useNewCardRadio.checked = true;
                            togglePaymentMethod();
                        }
                        
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } catch (error) {
                        console.error('Error:', error);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }
            @endif
        });
    </script>
</body>
</html>




