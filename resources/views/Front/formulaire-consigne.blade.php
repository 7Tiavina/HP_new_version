@php
    $isModal = isset($modal) && $modal;
    $layout = $isModal ? 'layouts.formulaire-embed' : 'layouts.front';
@endphp
@extends($layout)

@section('title', 'Réserver une consigne — Hello Passenger')
@section('meta_description', 'Book luggage storage or transport at Paris CDG and Orly. Hello Passenger — secure, simple booking.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/flatpickr/material_blue.css') }}">
    <link rel="stylesheet" href="{{ asset('css/booking-form.css') }}?v={{ file_exists(public_path('css/booking-form.css')) ? filemtime(public_path('css/booking-form.css')) : '1' }}">
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            corePlugins: { preflight: false },
            important: '#hp-booking-root',
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
@endpush

@section('content')
<div id="hp-booking-root" class="hp-booking-page {{ $isModal ? 'hp-modal-mode' : '' }}" data-selected-airport-id="{{ $selectedAirportId ?? '' }}" data-modal="{{ $isModal ? '1' : '0' }}">

<!-- Loader Overlay -->
<div id="loader" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[10003] flex items-center justify-center">
    <div class="custom-spinner !w-12 !h-12 !border-4"></div>
</div>

<!-- Custom Modal -->
<div id="custom-modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[10005] flex items-center justify-center px-4">
    <div id="custom-modal" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 id="custom-modal-title" class="text-xl font-bold text-gray-800"></h3>
            <button id="custom-modal-close" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <div class="py-4">
            <p id="custom-modal-message" class="text-gray-600"></p>
            <div id="custom-modal-prompt-container" class="hidden mt-4">
                <label id="custom-modal-prompt-label" for="custom-modal-input" class="block text-sm font-medium text-gray-700 mb-1"></label>
                <input type="text" id="custom-modal-input" class="input-style w-full">
                <p id="custom-modal-error" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
        </div>
        <!-- Modal Footer -->
        <div id="custom-modal-footer" class="flex justify-end pt-3 border-t border-gray-200 space-x-3">
            <button id="custom-modal-cancel-btn" class="hidden bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-full btn-hover" data-i18n="btn_cancel">Annuler</button>
            <button id="custom-modal-confirm-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">OK</button>
        </div>
    </div>
</div>

<!-- Options Advertisement Modal -->
<div id="options-advert-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-[10004] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl transform transition-all max-h-[90vh] overflow-y-auto relative">
        <!-- Close Button -->
        <button id="close-options-advert-modal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-10">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-8 text-center">
            <h2 class="text-3xl font-bold text-gray-800" data-i18n="modal_optimize_title">Optimisez votre expérience !</h2>
            <p class="mt-2 text-gray-600" data-i18n="modal_optimize_subtitle">Ajoutez nos services exclusifs pour un voyage sans tracas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-200">
            <!-- Priority Option -->
            <div id="advert-option-priority" class="hidden bg-white p-8">
                <div class="text-center">
                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full" data-i18n="modal_priority_label">PRIORITAIRE</span>
                    <h3 class="mt-4 text-2xl font-bold text-gray-900" data-i18n="modal_priority_title">Service Priority</h3>
                    <p class="mt-2 text-gray-500" data-i18n="modal_priority_desc">Bénéficiez d'un traitement prioritaire pour vos bagages à la dépose et à la récupération.</p>
                    <p id="advert-priority-price" class="mt-4 text-3xl font-extrabold text-gray-900">+15 €</p>
                    <button id="add-priority-from-modal" data-option-key="priority" class="mt-6 w-full bg-transparent border border-gray-400 text-gray-700 font-bold py-3 px-4 rounded-lg btn-hover hover:bg-gray-100" data-i18n="modal_add_cart">Ajouter au panier</button>
                </div>
            </div>

            <!-- Premium Option -->
            <div id="advert-option-premium" class="hidden bg-white p-8 relative">
                <div id="premium-available-content">
                    <div class="text-center">
                        <span class="inline-block bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded-full" data-i18n="modal_premium_label">PREMIUM</span>
                        <h3 class="mt-4 text-2xl font-bold text-gray-900" data-i18n="modal_premium_title">Service Premium</h3>
                        <p class="mt-2 text-gray-500" data-i18n="modal_premium_desc">Permet de remettre ou récupérer ses bagages directement à l'endroit exact choisi à l'aéroport, avec l'aide d'un porteur dédié. Le client indique le lieu, son mode de transport et un commentaire, et l'équipe s'occupe de tout.</p>
                        <p id="advert-premium-price" class="mt-4 text-3xl font-extrabold text-gray-900">+25 €</p>
                        <div id="premium-details-modal" class="mt-4 text-left space-y-3">
                            <!-- Premium specific fields will be injected here -->
                        </div>
                        <button id="add-premium-from-modal" data-option-key="premium" class="mt-6 w-full bg-transparent border border-gray-400 text-gray-700 font-bold py-3 px-4 rounded-lg btn-hover hover:bg-gray-100" data-i18n="modal_add_cart">Ajouter au panier</button>
                    </div>
                </div>
                <div id="premium-unavailable-message" class="absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-90 rounded-lg hidden">
                    <p class="text-lg font-semibold text-gray-600" data-i18n="modal_premium_unavailable">Service Premium indisponible</p>
                </div>
            </div>
        </div>

        <div class="p-6 text-center bg-gray-50">
            <button id="continue-from-options-modal" class="bg-yellow-custom text-gray-dark font-bold py-3 px-8 rounded-full btn-hover" data-i18n="modal_validate_continue">Valider et continuer →</button>
        </div>
    </div>
</div>

<!-- Quick Date Edit Modal -->
<div id="quick-date-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-[10001] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800" data-i18n="modal_edit_dates">Modifier les dates</h3>
            <button id="close-quick-date-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Date Blocks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Depot Block -->
                <div id="quick-depot-block" class="border border-gray-300 rounded-lg p-4 text-center cursor-pointer">
                    <p class="font-semibold text-gray-700">DÉPÔT</p>
                    <p id="quick-depot-date-display" class="text-2xl font-bold text-gray-900 mt-2">--</p>
                    <p id="quick-depot-time-display" class="text-lg text-gray-600">--:--</p>
                </div>
                <!-- Retrait Block -->
                <div id="quick-retrait-block" class="border border-gray-200 rounded-lg p-4 text-center cursor-pointer">
                    <p class="font-semibold text-gray-700">RETRAIT</p>
                    <p id="quick-retrait-date-display" class="text-2xl font-bold text-gray-900 mt-2">--</p>
                    <p id="quick-retrait-time-display" class="text-lg text-gray-600">--:--</p>
                </div>
            </div>

            <!-- Date Selection Mode -->
            <div class="text-center hidden">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button type="button" id="qdm-btn-depot" class="py-2 px-4 text-sm font-medium text-gray-900 bg-white rounded-l-lg border border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-2 focus:ring-yellow-custom">
                        Modifier Dépôt
                    </button>
                    <button type="button" id="qdm-btn-retrait" class="py-2 px-4 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-2 focus:ring-yellow-custom">
                        Modifier Retrait
                    </button>
                </div>
            </div>

            <!-- Quick Select Buttons -->
            <div id="qdm-quick-select-container" class="p-4 bg-gray-50 rounded-lg">
                <p id="qdm-editing-label" class="text-center font-semibold mb-4">Modification de la date de Dépôt</p>
                <div class="flex justify-center space-x-4">
                    <button data-day="today" class="qdm-day-btn py-2 px-6 bg-gray-200 rounded-full">Auj.</button>
                    <button data-day="tomorrow" class="qdm-day-btn py-2 px-6 bg-gray-200 rounded-full">Demain</button>
                    <button data-day="custom" class="qdm-day-btn py-2 px-6 bg-gray-200 rounded-full">Personnalisé</button>
                </div>
                <!-- Custom Date Input -->
                <div id="qdm-custom-date-container" class="hidden mt-4 text-center">
                    <input type="date" id="qdm-custom-date-input" class="input-style mx-auto">
                </div>
            </div>

            <!-- Hour Selection -->
            <div id="qdm-hour-container" class="p-4 bg-gray-50 rounded-lg">
                 <p class="text-center font-semibold mb-4">Heure</p>
                 <div id="qdm-hour-grid" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    <!-- Hour buttons will be injected here -->
                 </div>
                 <div id="qdm-custom-hour-container" class="hidden mt-4 text-center">
                    <div class="relative inline-block">
                        <input type="time" id="qdm-custom-time-input" class="input-style mx-auto pr-10 pl-4">
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-center p-6 border-t border-gray-200">
            <button id="qdm-validate-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-6 rounded-full btn-hover w-full">
                Valider
            </button>
        </div>
    </div>
</div>




<div id="baggage-tooltip" class="hidden absolute z-10 p-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-sm" role="tooltip">
    <!-- Tooltip content will be injected here -->
</div>

<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-2">
        <h1 class="text-3xl font-bold text-gray-800" data-i18n="form_title">Réserver une consigne</h1>
        <button id="reset-form-btn" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center space-x-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span data-i18n="form_reset">Réinitialiser</span>
        </button>
    </div>
    <p class="text-gray-600 mb-8" data-i18n="form_description">
        Sélectionnez le type de consigne et suivez les étapes du formulaire. Nous vous indiquerons les informations à fournir.
    </p>

    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <span data-i18n="breadcrumb_home">Accueil</span>
            <span>→</span>
            <span class="text-gray-800 font-medium" data-i18n="breadcrumb_booking">Réserver une consigne</span>
        </div>
        <button id="back-to-step-1-btn" class="hidden bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span data-i18n="btn_back">Retour</span>
        </button>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <!-- Étape 1: Aéroport et Dates -->
            <div id="step-1" class="{{ $isModal ? 'hp-step-active' : '' }}" style="display: block;">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-red-500 mb-4" data-i18n="form_required_fields">* Tous les champs sont obligatoires</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_airport_label">
                                DANS QUEL AÉROPORT SOUHAITEZ-VOUS LAISSER VOS BAGAGES ? *
                            </label>
                            @if(isset($error) && $error)
                                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3 mb-3">{{ $error }}</p>
                            @endif
                            <select id="airport-select" class="input-style custom-select w-full">
                                <option value="" selected disabled data-i18n="form_select_airport">Sélectionner un aéroport</option>
                                @if(isset($plateformes) && count($plateformes) > 0)
                                    @foreach($plateformes as $plateforme)
                                        <option value="{{ $plateforme['id'] }}">{{ $plateforme['libelle'] }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled data-i18n="form_no_airport">Aucun aéroport disponible pour le moment</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mt-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4" data-i18n="form_deposit_date">DATE DE DÉPÔT DES BAGAGES *</h3>
                        <input type="date" id="date-depot" class="input-style w-full mb-4">
                        <p class="text-xs text-gray-500 mb-2" data-i18n="form_premium_hint_72h">Pour afficher l'option Service Premium, choisissez une date de dépôt au moins 3 jours à l'avance.</p>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_deposit_time">HEURE DE DÉPÔT *</label>
                        <div class="relative">
                            <input type="time" id="heure-depot" class="input-style w-full pr-10 pl-4">
                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4" data-i18n="form_pickup_date">DATE DE RÉCUPÉRATION DES BAGAGES *</h3>
                        <input type="date" id="date-recuperation" class="input-style w-full mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_pickup_time">HEURE DE RÉCUPÉRATION *</label>
                        <div class="relative">
                            <input type="time" id="heure-recuperation" class="input-style w-full pr-10 pl-4">
                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <button id="check-availability-btn" class="bg-yellow-custom text-gray-dark font-bold py-3 px-8 rounded-full btn-hover" data-i18n="form_check_availability">
                        VOIR LA DISPONIBILITÉ
                        <span class="custom-spinner" role="status" aria-hidden="true" id="loading-spinner-availability" style="display: none;"></span>
                    </button>
                </div>
            </div>

            <div id="baggage-selection-step" class="{{ $isModal ? '' : '' }}" style="display: none;">
                <!-- Display Airport Name -->
                <div class="bg-gray-100 p-4 rounded-lg mb-6 text-center">
                    <p class="text-sm font-medium text-gray-600" data-i18n="form_selected_airport">AÉROPORT SÉLECTIONNÉ</p>
                    <p id="display-airport-name" class="text-lg font-bold text-gray-900"></p>
                </div>

                <!-- Display Dates -->
                <div id="dates-display" class="flex justify-around bg-gray-100 p-4 rounded-lg mb-6 text-center cursor-pointer hover:bg-gray-200 hover:shadow-md transition-all duration-200">
                    <div>
                        <p class="text-sm font-medium text-gray-600" data-i18n="form_deposit_short">DÉPÔT</p>
                        <p id="display-date-depot" class="text-lg font-bold text-gray-900"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600" data-i18n="form_pickup_short">RETRAIT</p>
                        <p id="display-date-recuperation" class="text-lg font-bold text-gray-900"></p>
                    </div>
                </div>

                <!-- New Baggage Selection -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800" data-i18n="form_choose_luggage">1. Choisissez vos bagages</h3>
                    </div>
                    <div id="baggage-grid-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-3">
                        @if(isset($products) && count($products) > 0)
                            {{-- Grille bagages rendue côté serveur --}}
                            @php
                                $product_map_icons = [
                                    'Accessoires' => '<img src="' . asset('accessoires.png') . '" alt="Accessoires" class="h-full w-full object-contain p-1" />',
                                    'Bagage cabine' => '<img src="' . asset('bag_cabine.png') . '" alt="Bagage cabine" class="h-full w-full object-contain p-1" />',
                                    'Bagage soute' => '<img src="' . asset('bag_soute.png') . '" alt="Bagage soute" class="h-full w-full object-contain p-1" />',
                                    'Bagage spécial' => '<img src="' . asset('bag_special.png') . '" alt="Bagage spécial" class="h-full w-full object-contain p-1" />',
                                    'Vestiaire' => '<img src="' . asset('vestiaire.png') . '" alt="Vestiaire" class="h-full w-full object-contain p-1" />',
                                ];
                                $default_icon = '<svg width="24" height="24" fill="none" viewBox="0 0 24 24" class="text-gray-600"><path stroke="currentColor" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke="currentColor" stroke-width="2" d="M9.5 9.5h.01v.01h-.01V9.5zm5 0h.01v.01h-.01V9.5zm-2.5 5a2.5 2.5 0 00-5 0h5z" /></svg>';
                            @endphp
                            @foreach($products as $product)
                                @php
                                    $libelle = $product['libelle'];
                                    $icon = $product_map_icons[$libelle] ?? $default_icon;
                                    // Map libelle to translation key - remove accents
                                    $libelleKey = strtolower(str_replace(' ', '_', $libelle));
                                    // Remove accents and special characters
                                    $libelleKey = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $libelleKey);
                                    $i18nKey = 'luggage_' . $libelleKey;
                                @endphp
                                <div class="baggage-option p-4 rounded-lg flex flex-col items-center justify-between space-y-2" data-product-id="{{ $product['id'] }}" data-libelle="{{ $libelle }}">
                                    <div class="w-20 h-20 bg-white rounded flex items-center justify-center">
                                        {!! $icon !!}
                                    </div>
                                    <div class="flex items-center justify-center space-x-1">
                                        <span class="text-sm font-medium text-center" data-i18n="{{ $i18nKey }}">{{ $libelle }}</span>
                                        <span class="info-icon cursor-pointer" data-libelle="{{ $libelle }}" data-i18n-key="{{ $i18nKey }}_desc">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">                                        <button type="button" class="quantity-change-btn w-8 h-8 border border-gray-300 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100" data-action="minus" data-product-id="{{ $product['id'] }}">−</button>
                                        <span class="font-bold text-lg w-5 text-center" data-quantity-display="{{ $product['id'] }}">0</span>
                                        <button type="button" class="quantity-change-btn w-8 h-8 border border-gray-300 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100" data-action="plus" data-product-id="{{ $product['id'] }}">+</button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-span-full py-8 px-4 text-center bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-gray-600 font-medium" data-i18n="form_no_products">Aucun type de bagage disponible pour le moment.</p>
                                <p class="text-sm text-gray-500 mt-2" data-i18n="form_no_products_retry">Veuillez réessayer plus tard ou nous contacter.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="bg-yellow-custom rounded-lg p-6">
                <h3 class="font-bold text-black mb-2" data-i18n="form_attention">ATTENTION !</h3>
                <p class="text-sm text-black leading-relaxed" data-i18n="form_attention_text">
                    Les trajets pour la livraison ou la récupération des bagages peuvent inclure les gares : Gare du Nord, Châtelet Les Halles, Gare de Lyon, ou Saint-Michel Notre-Dame.
                </p>
            </div>

            <div class="bg-gray-800 rounded-lg p-4 flex items-center justify-between">
                <p class="text-white text-sm" data-i18n="form_partner_text">
                    Vous êtes un professionnel du tourisme ? Facilitez le voyage de vos clients !
                </p>
                <button class="bg-transparent border border-white text-white px-4 py-2 rounded-full text-sm hover:bg-white hover:text-gray-800 transition-colors" data-i18n="form_become_partner">
                    DEVENIR PARTENAIRE →
                </button>
            </div>
        </div>

        <div class="w-full lg:w-full relative" id="sticky-wrapper" style="display: none;">
            <div id="sticky-summary" class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm text-center">
                    <p class="text-lg font-bold text-gray-800 mb-2" data-i18n="form_total_price">Tarif TOTAL</p>
                    <div id="summary-price" class="text-4xl font-bold text-gray-800">0 €</div>
                </div>
                <div id="empty-cart" class="bg-white border-2 border-yellow-400 rounded-lg p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg mx-auto mb-4 flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" class="text-gray-400">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L9 11m-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-6" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-black mb-2" data-i18n="form_empty_cart">Votre panier est vide :(</h3>
                    <div class="bg-gray-100 rounded p-3 mt-4">
                        <p class="text-sm text-gray-600 mb-2" data-i18n="form_total">Total:</p>
                        <p class="text-2xl font-bold text-black total-panier">0€</p>
                    </div>
                </div>
                <div id="cart-summary" class="bg-white border-2 border-yellow-400 rounded-lg p-6 shadow-sm" style="display: none;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-black" data-i18n="form_your_cart">Votre panier</h3>
                        <div id="cart-duration" class="text-sm text-gray-600 font-medium"></div>
                        <div class="custom-spinner" role="status" aria-hidden="true" id="loading-spinner-cart" style="display: none;"></div>
                    </div>
                    <div id="cart-items-container" class="panier-content divide-y divide-gray-200">
                        <!-- Cart items will be injected here -->
                    </div>
                    <div id="cart-subtotal" class="py-2 flex justify-between items-center border-t border-gray-200 mt-2" style="display: none;">
                        <span class="subtotal-text text-sm text-gray-600" data-i18n="payment_total_normal">Total normal</span>
                        <span class="subtotal-amount text-sm text-gray-600"></span>
                    </div>
                    <div id="cart-discount" class="py-2 flex justify-between items-center border-t border-gray-200 mt-2" style="display: none;">
                        <span class="discount-text text-sm text-green-600 font-semibold" data-i18n="cart_discount_online">
                            Offre réservation en ligne (consigne bagages uniquement)
                        </span>
                        <span class="discount-amount text-sm text-green-600 font-semibold"></span>
                    </div>
                    <div class="bg-yellow-custom rounded p-3 mt-4 flex justify-center items-center summary-total-container cursor-pointer hover:opacity-90 transition-opacity" role="button" tabindex="0" id="btn-proceed-payment" aria-label="Procéder au paiement">
                        <span class="text-lg font-bold text-gray-dark" data-i18n="form_proceed_payment">Procéder au paiement</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
    var initialProducts = @json($products);
    // Routes injected for JS (works even if app is hosted in a subfolder like /public)
    window.APP_ROUTES = {
        checkAvailability: @json(route('api.check-availability')),
        getQuote: @json(route('api.get-quote')),
        optionsQuote: @json(url('api/commande/options-quote')),
        checkAuthStatus: @json(url('check-auth-status')),
        preparePayment: @json(route('prepare.payment')),
    };
</script>

@php
    $jsVersion = function ($path) {
        $full = public_path($path);
        return file_exists($full) ? filemtime($full) : '1';
    };
@endphp
<!-- Scripts JS externalisés (versionnés pour éviter cache navigateur) -->
<script src="{{ asset('js/translations-simple.js') }}?v={{ $jsVersion('js/translations-simple.js') }}"></script>
<script src="{{ asset('js/state.js') }}?v={{ $jsVersion('js/state.js') }}"></script>
<script src="{{ asset('js/utils.js') }}?v={{ $jsVersion('js/utils.js') }}"></script>
<script src="{{ asset('js/modal.js') }}?v={{ $jsVersion('js/modal.js') }}"></script>
<script src="{{ asset('js/quick-date-modal.js') }}?v={{ $jsVersion('js/quick-date-modal.js') }}"></script>
<script src="{{ asset('js/cart.js') }}?v={{ $jsVersion('js/cart.js') }}"></script>
<script src="{{ asset('js/booking.js') }}?v={{ $jsVersion('js/booking.js') }}"></script>

<!-- Scripts for flatpickr timepicker -->
<script src="{{ asset('js/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('js/flatpickr/fr.js') }}"></script>

<script>
    // Ce script reste en ligne car il contient une route Blade resolue par PHP
    document.addEventListener('DOMContentLoaded', function () {
        // Initialiser les timepickers avec flatpickr (plus moderne et responsive)
        const heureDepotFP = flatpickr("#heure-depot", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr",
            defaultHour: 9,
            defaultMinute: 0,
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
                // Add clock icon to Flatpickr input (right side)
                const input = instance.input;
                const parent = input.parentElement;
                if (!parent.querySelector('.clock-icon')) {
                    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    icon.setAttribute('class', 'absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none z-10 clock-icon');
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', 'currentColor');
                    icon.setAttribute('viewBox', '0 0 24 24');
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    parent.appendChild(icon);
                    // Add padding-right to input
                    input.style.paddingRight = '2.5rem';
                }
            }
        });

        const heureRecupFP = flatpickr("#heure-recuperation", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr",
            defaultHour: 18,
            defaultMinute: 0,
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
                // Add clock icon to Flatpickr input (right side)
                const input = instance.input;
                const parent = input.parentElement;
                if (!parent.querySelector('.clock-icon')) {
                    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    icon.setAttribute('class', 'absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none z-10 clock-icon');
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', 'currentColor');
                    icon.setAttribute('viewBox', '0 0 24 24');
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    parent.appendChild(icon);
                    // Add padding-right to input
                    input.style.paddingRight = '2.5rem';
                }
            }
        });

        flatpickr("#qdm-custom-time-input", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr",
            defaultHour: 9,
            defaultMinute: 0,
            allowInput: true
        });

        // Initialisation des listeners qui dépendent d'éléments du DOM chargés
        if(typeof setupQdmListeners !== 'undefined') setupQdmListeners();

        // Appeler loadStateFromSession au chargement de la page pour restaurer l'état
        if(typeof loadStateFromSession !== 'undefined') {
            loadStateFromSession();
        }

        // Le setup des listeners de la modale custom est déjà dans modal.js
        // Le setup des listeners du booking est dans booking.js
        
        // Listener pour le bouton de réinitialisation
        document.getElementById('reset-form-btn').addEventListener('click', async function () {
            const confirmed = await showCustomConfirm(
                'Réinitialiser la commande',
                'Voulez-vous vraiment continuer ? Toutes les données saisies pour votre commande actuelle seront définitivement perdues.'
            );
            if (confirmed) {
                const loader = document.getElementById('loader');
                if (loader) {
                    loader.classList.remove('hidden');
                }

                sessionStorage.removeItem('formState');

                try {
                    // La route 'session.reset' est necessaire ici.
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
                    location.reload();
                }, 500);
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
/* Luxe theme overrides — loaded last so they win over Tailwind */
#hp-booking-root.hp-booking-page { background: var(--luxe-bg) !important; }
#hp-booking-root .bg-white { background: var(--luxe-card) !important; }
#hp-booking-root .bg-gray-50,
#hp-booking-root .bg-gray-100 { background: var(--luxe-surface) !important; }
#hp-booking-root .baggage-option.selected { background: rgba(201, 169, 98, 0.15) !important; border-color: var(--luxe-gold) !important; }
#hp-booking-root .baggage-option.selected .w-20.h-20 { background: rgba(201, 169, 98, 0.2) !important; }
#hp-booking-root .input-style,
#hp-booking-root input { background: var(--luxe-surface) !important; border-color: var(--luxe-border) !important; color: var(--luxe-cream) !important; }
#hp-booking-root select:not(#airport-select) { background: var(--luxe-surface) !important; border-color: var(--luxe-border) !important; color: var(--luxe-cream) !important; }
#hp-booking-root .input-style:focus,
#hp-booking-root input:focus { border-color: var(--luxe-gold) !important; box-shadow: 0 0 0 2px rgba(201, 169, 98, 0.25) !important; }
#hp-booking-root .input-completed { background: rgba(201, 169, 98, 0.1) !important; }
/* Texte visible sur fond sombre — tout le formulaire */
#hp-booking-root .text-gray-800,
#hp-booking-root .text-gray-900,
#hp-booking-root h1, #hp-booking-root h2, #hp-booking-root h3 { color: var(--luxe-cream) !important; }
#hp-booking-root .text-gray-600,
#hp-booking-root .text-gray-700,
#hp-booking-root .text-gray-500 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root .border-gray-200,
#hp-booking-root .border-gray-300 { border-color: var(--luxe-border) !important; }
#hp-booking-root .text-red-500,
#hp-booking-root .text-red-600 { color: #e5a0a0 !important; }
#hp-booking-root .text-amber-700,
#hp-booking-root .bg-amber-50 { background: rgba(201, 169, 98, 0.15) !important; border-color: var(--luxe-border) !important; color: var(--luxe-cream) !important; }
#hp-booking-root .quantity-change-btn,
#hp-booking-root .baggage-option .text-gray-600 { color: var(--luxe-cream-muted) !important; border-color: var(--luxe-border) !important; }
#hp-booking-root .quantity-change-btn:hover,
#hp-booking-root .baggage-option:hover .text-gray-600 { color: var(--luxe-cream) !important; background: rgba(201, 169, 98, 0.1) !important; }
/* Custom modal + options modal + quick-date modal — texte visible */
#hp-booking-root #custom-modal,
#hp-booking-root #options-advert-modal .bg-white,
#hp-booking-root #quick-date-modal .bg-white { background: var(--luxe-card) !important; border-color: var(--luxe-border) !important; }
#hp-booking-root #custom-modal .text-gray-800,
#hp-booking-root #custom-modal .text-gray-700,
#hp-booking-root #custom-modal .text-gray-600,
#hp-booking-root #options-advert-modal .text-gray-800,
#hp-booking-root #options-advert-modal .text-gray-900,
#hp-booking-root #options-advert-modal .text-gray-600,
#hp-booking-root #options-advert-modal .text-gray-500,
#hp-booking-root #options-advert-modal .text-gray-700,
#hp-booking-root #quick-date-modal .text-gray-800,
#hp-booking-root #quick-date-modal .text-gray-900,
#hp-booking-root #quick-date-modal .text-gray-600,
#hp-booking-root #quick-date-modal .text-gray-700 { color: var(--luxe-cream) !important; }
#hp-booking-root #custom-modal .text-gray-400,
#hp-booking-root #options-advert-modal .text-gray-400,
#hp-booking-root #quick-date-modal .text-gray-400 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root #custom-modal .border-gray-200,
#hp-booking-root #options-advert-modal .border-gray-200,
#hp-booking-root #quick-date-modal .border-gray-200 { border-color: var(--luxe-border) !important; }
#hp-booking-root #custom-modal .bg-gray-200,
#hp-booking-root #options-advert-modal .bg-gray-50,
#hp-booking-root #options-advert-modal .bg-gray-100,
#hp-booking-root #options-advert-modal .bg-gray-200,
#hp-booking-root #quick-date-modal .bg-gray-50,
#hp-booking-root #quick-date-modal .bg-gray-200 { background: var(--luxe-surface) !important; }
#hp-booking-root #options-advert-modal .border-gray-400,
#hp-booking-root #options-advert-modal .hover\:bg-gray-100:hover,
#hp-booking-root #quick-date-modal button.bg-white { background: var(--luxe-surface) !important; border-color: var(--luxe-border) !important; color: var(--luxe-cream) !important; }
#hp-booking-root #baggage-tooltip { background: var(--luxe-surface) !important; border: 1px solid var(--luxe-border); }
#hp-booking-root .bg-yellow-100.text-yellow-800,
#hp-booking-root .bg-purple-100.text-purple-800 { background: rgba(201, 169, 98, 0.2) !important; color: var(--luxe-gold) !important; }
#hp-booking-root select option { background: var(--luxe-surface); color: var(--luxe-cream); }
/* Service Premium — badge, formulaires et textes conformes luxe */
#hp-booking-root #advert-option-premium .bg-purple-100,
#hp-booking-root #advert-option-premium .bg-purple-100.text-purple-800 { background: rgba(201, 169, 98, 0.25) !important; color: var(--luxe-gold) !important; border: 1px solid var(--luxe-gold); }
#hp-booking-root #advert-option-premium .text-purple-800 { color: var(--luxe-gold) !important; }
#hp-booking-root #advert-option-premium .text-gray-900,
#hp-booking-root #advert-option-premium .text-gray-700,
#hp-booking-root #advert-option-premium .text-gray-600,
#hp-booking-root #advert-option-premium .text-gray-500 { color: var(--luxe-cream) !important; }
#hp-booking-root #advert-option-premium .text-gray-400 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root #premium-message-container,
#hp-booking-root #premium_fields_terminal_to_agence,
#hp-booking-root #premium_fields_agence_to_terminal { background: rgba(201, 169, 98, 0.12) !important; border-color: var(--luxe-gold) !important; }
#hp-booking-root #premium-message-container .text-blue-700,
#hp-booking-root #premium_fields_terminal_to_agence h4,
#hp-booking-root #premium_fields_agence_to_terminal h4 { color: var(--luxe-cream) !important; }
#hp-booking-root #premium_fields_terminal_to_agence .text-gray-700,
#hp-booking-root #premium_fields_terminal_to_agence .text-gray-600,
#hp-booking-root #premium_fields_agence_to_terminal .text-gray-700,
#hp-booking-root #premium_fields_agence_to_terminal .text-gray-600 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root #premium-empty-state,
#hp-booking-root #premium-unavailable-message { background: var(--luxe-surface) !important; }
#hp-booking-root #premium-empty-state .text-gray-500,
#hp-booking-root #premium-unavailable-message .text-gray-600,
#hp-booking-root #premium-unavailable-message .text-gray-500 { color: var(--luxe-cream-muted) !important; }
/* Aéroport + dates display — visible + luxe */
#hp-booking-root #baggage-selection-step .bg-gray-100 { background: var(--luxe-card) !important; border: 1px solid var(--luxe-border); }
#hp-booking-root #baggage-selection-step .text-gray-600,
#hp-booking-root #baggage-selection-step .text-gray-700 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root #display-airport-name,
#hp-booking-root #display-date-depot,
#hp-booking-root #display-date-recuperation { color: var(--luxe-cream) !important; }
#hp-booking-root #dates-display:hover { background: rgba(201, 169, 98, 0.12) !important; border-color: var(--luxe-gold); }

/* Modal mode — step by step, no scroll */
#hp-booking-root.hp-modal-mode .max-w-6xl { max-width: 100%; padding: 1rem; }
#hp-booking-root.hp-modal-mode .flex.justify-between.items-center.mb-2,
#hp-booking-root.hp-modal-mode .text-gray-600.mb-8,
#hp-booking-root.hp-modal-mode .flex.justify-between.items-center.mb-8 > div:first-child,
#hp-booking-root.hp-modal-mode .bg-yellow-custom.rounded-lg,
#hp-booking-root.hp-modal-mode .bg-gray-800.rounded-lg { display: none !important; }
#hp-booking-root.hp-modal-mode .grid.lg\:grid-cols-3 { display: block; }
#hp-booking-root.hp-modal-mode #step-1,
#hp-booking-root.hp-modal-mode #baggage-selection-step {
  display: none !important;
  min-height: 0;
}
#hp-booking-root.hp-modal-mode #step-1.hp-step-active,
#hp-booking-root.hp-modal-mode #baggage-selection-step.hp-step-active {
  display: block !important;
  animation: hp-fadeIn 0.3s ease;
}
@keyframes hp-fadeIn { from { opacity: 0; } to { opacity: 1; } }
#hp-booking-root.hp-modal-mode .lg\:col-span-2 { max-width: 100%; }
#hp-booking-root.hp-modal-mode #sticky-wrapper {
  display: block !important;
  position: relative;
}
#hp-booking-root.hp-modal-mode #baggage-selection-step .space-y-6 > * + * { margin-top: 1rem; }
#hp-booking-root.hp-modal-mode #baggage-grid-container { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.75rem; }
#hp-booking-root.hp-modal-mode .baggage-option { padding: 0.75rem; }
#hp-booking-root.hp-modal-mode .baggage-option .w-20 { width: 48px; height: 48px; }
#hp-booking-root.hp-modal-mode .baggage-option .h-20 { height: 48px; }

/* Override luxe theme for validation states - MUST come after luxe styles */
#hp-booking-root .input-default { border-color: #d1d5dc !important; background: #f9fafb !important; }
#hp-booking-root .input-filled { border-color: #FFC107 !important; background: #fef9e7 !important; }
#hp-booking-root .input-error { border-color: #dc2626 !important; background: #fef2f2 !important; }

/* Fix airport select - remove any luxe background that might cause dropdown duplication */
#hp-booking-root #airport-select {
  background-color: #f9fafb !important;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
  background-position: right 0.75rem center !important;
  background-repeat: no-repeat !important;
  background-size: 1.5em 1.5em !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
}
#hp-booking-root #airport-select.input-default {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
}
#hp-booking-root #airport-select.input-filled {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2392400e' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
}
#hp-booking-root #airport-select.input-error {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23b91c1c' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
}
</style>
@endpush