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
    
    <!-- Import Poppins font to match Hero section -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Apply Poppins to entire form and enlarge text */
        #hp-booking-root,
        #hp-booking-root * {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Enlarge title */
        #hp-booking-root h1[data-i18n="form_title"] {
            font-weight: 700 !important;
            font-size: 42px !important;
            line-height: 1.2 !important;
            color: #1a1a1a !important;
        }

        /* Enlarge description */
        #hp-booking-root p[data-i18n="form_description"] {
            font-weight: 400 !important;
            font-size: 18px !important;
            line-height: 1.6 !important;
            color: #1a1a1a !important;
        }

        /* Enlarge labels */
        #hp-booking-root label {
            font-weight: 500 !important;
            font-size: 16px !important;
            color: #1a1a1a !important;
        }

        /* Enlarge date/time labels (h3) */
        #hp-booking-root h3 {
            font-weight: 600 !important;
            font-size: 17px !important;
            color: #1a1a1a !important;
            line-height: 1.4 !important;
            text-align: center !important;
            word-break: break-word !important;
            hyphens: auto !important;
        }

        /* Specific styles for date labels (deposit and pickup) */
        #hp-booking-root .date-label {
            display: block !important;
            text-align: center !important;
            min-height: 50px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Enlarge inputs and selects */
        #hp-booking-root input,
        #hp-booking-root select {
            font-size: 16px !important;
        }

        /* Enlarge breadcrumb */
        #hp-booking-root .breadcrumb,
        #hp-booking-root [data-i18n="breadcrumb_home"],
        #hp-booking-root [data-i18n="breadcrumb_booking"] {
            font-size: 16px !important;
        }

        /* Enlarge required text */
        #hp-booking-root [data-i18n="form_required_fields"] {
            font-size: 15px !important;
        }

        /* Add spacing between Hero and header/nav */
        .hp-hero-section {
            margin-top: 20px !important;
        }

        /* Add spacing between breadcrumb and form */
        #hp-booking-root .hp-breadcrumb-wrapper {
            margin-bottom: 40px !important;
        }

        /* Add spacing around form container */
        .hp-form-wrapper {
            padding-top: 40px !important;
            padding-bottom: 60px !important;
        }
    </style>
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
    <style>
        /* Timepicker Discret 24h - Jaune */
        :root {
            --primary: #f9c52d;
            --primary-dark: #e5b324;
            --text: #333;
            --shadow: 0 8px 20px rgba(0,0,0,0.12);
            --border: #e0e0e0;
        }

        .time-field-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .time-field {
            padding: 10px 36px 10px 14px;
            font-size: 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            background: white;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
            color: var(--text);
        }
        .time-field:hover { 
            border-color: var(--primary); 
            box-shadow: 0 2px 8px rgba(249, 197, 45, 0.15);
        }
        .time-field:focus { 
            border-color: var(--primary); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(249, 197, 45, 0.2); 
        }

        /* Clock Icon */
        .time-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #999;
            pointer-events: none;
            transition: color 0.3s ease;
        }
        .time-field-wrapper:hover .time-icon {
            color: var(--primary);
        }

        /* Le Popover - Compact */
        .picker-popover {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.08);
            padding: 10px;
            z-index: 1000;
            display: none;
            width: 130px;
            animation: fadeInScale 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeInScale {
            from { 
                opacity: 0; 
                transform: translateY(-4px) scale(0.97); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .picker-popover.active { display: block; }
        .picker-popover.active::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid white;
        }

        .selectors { 
            display: flex; 
            align-items: center; 
            justify-content: center;
            gap: 6px;
        }
        
        .column { 
            display: flex; 
            flex-direction: column; 
            align-items: center;
            gap: 2px;
        }
        
        .val-display { 
            font-size: 20px; 
            font-weight: 700; 
            color: var(--text); 
            padding: 2px 4px;
            min-width: 40px;
            text-align: center;
            background: #fafafa;
            border-radius: 6px;
        }

        .arrow {
            background: white; 
            border: 1px solid var(--border); 
            border-radius: 6px;
            width: 28px; 
            height: 28px; 
            cursor: pointer;
            display: flex; 
            align-items: center; 
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 10px; 
            color: #666;
            font-weight: 600;
        }
        .arrow:hover { 
            background: var(--primary); 
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(249, 197, 45, 0.3);
        }
        .arrow:active {
            transform: translateY(0);
        }

        .separator { 
            font-size: 18px; 
            font-weight: bold; 
            color: #ccc;
            padding-bottom: 2px;
        }

        /* Uniformisation des largeurs pour les champs date/heure */
        .datetime-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .datetime-field {
            width: 100%;
        }
        
        .datetime-field label {
            display: block;
            margin-bottom: 6px;
        }
        
        .datetime-field input {
            width: 100%;
        }
    </style>
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

<!-- Login/Register Modals -->
@include('Front.auth-modals')

<!-- Options Side Drawer (New UX) -->
<div id="options-drawer-overlay" class="hidden fixed inset-0 bg-white bg-opacity-90 z-[10003] transition-opacity opacity-0" style="backdrop-filter: blur(4px);"></div>
<div id="options-drawer" class="hidden fixed top-0 right-0 h-full w-full max-w-2xl bg-white shadow-2xl z-[10004] transform translate-x-full transition-transform duration-400 ease-out flex flex-col">
    <!-- Drawer Header - Gold accent header with close button -->
    <div class="hp-drawer-header-accent bg-gradient-to-r from-yellow-50 to-amber-50 border-b-2 border-yellow-400 p-4 relative flex-shrink-0">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2" data-i18n="drawer_options_title">
                <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                </svg>
                <span>Options & Services</span>
            </h2>
            <!-- Close button with gold hover -->
            <button id="close-options-drawer" class="text-gray-400 hover:text-yellow-600 hover:bg-yellow-100 rounded-lg p-1.5 transition-all duration-200 z-10">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Drawer Body - Scrollable -->
    <div class="flex-1 overflow-y-auto p-6">

        <!-- Premium Option Card - First Priority -->
        <div class="mb-6">
            <div id="drawer-option-premium" class="hidden group relative bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-200 border-2 border-yellow-200 hover:border-yellow-400">
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <div class="w-28 h-28 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                            <img src="/icon_premium.png" alt="Premium" class="w-24 h-24 object-contain p-2" onerror="this.style.display='none'; this.parentElement.innerHTML='💎'; this.parentElement.classList.add('text-6xl');">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-block bg-gradient-to-r from-yellow-500 to-yellow-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm" data-i18n="drawer_premium_label">PREMIUM</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900" data-i18n="drawer_premium_title">Service Premium</h3>
                                <p class="mt-1 text-xs text-gray-600 leading-relaxed" data-i18n="drawer_premium_desc_simple">Remise ou récupération de vos bagages à l'endroit exact choisi dans l'aéroport avec porteur dédié. Service VIP complet.</p>
                            </div>
                            <!-- Add Button - Integrated -->
                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button id="add-premium-btn" onclick="addOptionToCart('premium')" class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white rounded-lg shadow hover:shadow-lg transition-all flex items-center justify-center transform hover:scale-105" title="Ajouter au panier">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p id="drawer-premium-price" class="mt-2 text-xl font-bold text-yellow-600"></p>
                    </div>
                </div>

                <!-- Premium Unavailable Overlay -->
                <div id="premium-drawer-unavailable-message" class="absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-95 rounded-2xl hidden backdrop-blur-sm">
                    <div class="text-center p-4">
                        <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-600" data-i18n="drawer_premium_unavailable">Service Premium indisponible</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Option Card -->
        <div class="mb-6">
            <div id="drawer-option-priority" class="hidden group relative bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-gray-200 border-2 border-gray-200 hover:border-gray-400">
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <div class="w-28 h-28 bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                            <img src="/icon_priority.png" alt="Priority" class="w-24 h-24 object-contain p-2" onerror="this.style.display='none'; this.parentElement.innerHTML='⚡'; this.parentElement.classList.add('text-6xl');">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm" data-i18n="drawer_priority_label">PRIORITAIRE</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900" data-i18n="drawer_priority_title">Service Priority</h3>
                                <p class="mt-1 text-xs text-gray-600 leading-relaxed" data-i18n="drawer_priority_desc">Traitement prioritaire de vos bagages à la dépose et à la récupération. Gagnez du temps et évitez les files d'attente.</p>
                            </div>
                            <!-- Add Button - Integrated -->
                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button id="add-priority-btn" onclick="addOptionToCart('priority')" class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white rounded-lg shadow hover:shadow-lg transition-all flex items-center justify-center transform hover:scale-105" title="Ajouter au panier">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p id="drawer-priority-price" class="mt-2 text-xl font-bold text-gray-600"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Summary Section -->
        <div class="border-t-2 border-gray-300 pt-5 mb-4">
            <!-- Access Options Section (contraintes horaires) -->
            <div id="drawer-access-options" class="mb-4"></div>
            
            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2 section-title" data-i18n="drawer_cart_title">
                <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Votre panier</span>
            </h3>
            <div id="drawer-cart-items" class="space-y-2 mb-3">
                <!-- Cart items will be injected here -->
            </div>
            <div class="mt-4 pt-4 border-t-2 border-yellow-300 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl p-4 cart-total-section">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-700 flex items-center gap-2" data-i18n="drawer_total">
                        <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                        Total à payer
                    </span>
                    <span id="drawer-cart-total" class="text-2xl font-bold text-gray-900 bg-gradient-to-r from-yellow-400 to-yellow-600 bg-clip-text text-transparent">0,00 €</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Drawer Footer - Fixed at bottom with gold gradient -->
    <div class="border-t-2 border-yellow-400 p-4 bg-gradient-to-r from-yellow-50 to-amber-50 flex-shrink-0 flex justify-end">
        <button id="confirm-options-drawer" class="bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold py-3.5 px-10 rounded-full transition-all shadow-lg hover:shadow-xl hover:shadow-yellow-300 transform hover:-translate-y-1 hover:scale-105 duration-300 text-base flex items-center gap-2.5 group" data-i18n="drawer_confirm">
            <span>Continuer</span>
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </div>
</div>

<!-- Quick Date Edit Modal -->
<div id="quick-date-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-[10001] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800" data-i18n="modal_edit_dates">Modifier les dates</h3>
            <button id="close-quick-date-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Date de Dépôt -->
                <div>
                    <h3 class="date-label text-sm font-medium text-gray-700 mb-4" data-i18n="form_deposit_date">DATE DE DÉPÔT DES BAGAGES *</h3>
                    <div class="datetime-container">
                        <div class="datetime-field">
                            <input type="date" id="qdm-date-depot" class="input-style w-full">
                        </div>
                        <div class="datetime-field">
                            <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_deposit_time">HEURE DE DÉPÔT *</label>
                            <div class="time-field-wrapper">
                                <input type="text" id="heure-qdm-depot" class="time-field" readonly>
                                <svg class="time-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>

                                <!-- Popover Timepicker -->
                                <div class="picker-popover" id="qdm-popover-depot">
                                    <div class="selectors">
                                        <div class="column">
                                            <button class="arrow" onclick="changeVal('H', 1, 'qdm-depot')">▲</button>
                                            <div id="h-val-qdm-depot" class="val-display">09</div>
                                            <button class="arrow" onclick="changeVal('H', -1, 'qdm-depot')">▼</button>
                                        </div>
                                        <div class="separator">:</div>
                                        <div class="column">
                                            <button class="arrow" onclick="changeVal('M', 5, 'qdm-depot')">▲</button>
                                            <div id="m-val-qdm-depot" class="val-display">00</div>
                                            <button class="arrow" onclick="changeVal('M', -5, 'qdm-depot')">▼</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date de Retrait -->
                <div>
                    <h3 class="date-label text-sm font-medium text-gray-700 mb-4" data-i18n="form_pickup_date">DATE DE RÉCUPÉRATION DES BAGAGES *</h3>
                    <div class="datetime-container">
                        <div class="datetime-field">
                            <input type="date" id="qdm-date-recuperation" class="input-style w-full">
                        </div>
                        <div class="datetime-field">
                            <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_pickup_time">HEURE DE RÉCUPÉRATION *</label>
                            <div class="time-field-wrapper">
                                <input type="text" id="heure-qdm-recuperation" class="time-field" readonly>
                                <svg class="time-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>

                                <!-- Popover Timepicker -->
                                <div class="picker-popover" id="qdm-popover-recuperation">
                                    <div class="selectors">
                                        <div class="column">
                                            <button class="arrow" onclick="changeVal('H', 1, 'qdm-recuperation')">▲</button>
                                            <div id="h-val-qdm-recuperation" class="val-display">18</div>
                                            <button class="arrow" onclick="changeVal('H', -1, 'qdm-recuperation')">▼</button>
                                        </div>
                                        <div class="separator">:</div>
                                        <div class="column">
                                            <button class="arrow" onclick="changeVal('M', 5, 'qdm-recuperation')">▲</button>
                                            <div id="m-val-qdm-recuperation" class="val-display">00</div>
                                            <button class="arrow" onclick="changeVal('M', -5, 'qdm-recuperation')">▼</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info message -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
                <p class="text-sm text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Les mêmes contraintes s'appliquent : minimum 3h entre dépôt et retrait.</span>
                </p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-center p-6 border-t border-gray-200">
            <button id="qdm-validate-btn" class="bg-yellow-custom text-gray-dark font-bold py-3 px-8 rounded-full btn-hover w-full md:w-auto" data-i18n="modal_validate_dates">
                Valider les dates
            </button>
        </div>
    </div>
</div>




<div id="baggage-tooltip" class="hidden absolute z-10 p-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-sm" role="tooltip">
    <!-- Tooltip content will be injected here -->
</div>

<!-- Main form container with increased top spacing -->
<div class="max-w-6xl mx-auto px-6 py-8" style="margin-top: 60px; padding-top: 50px;">
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

    <!-- Breadcrumb with increased spacing -->
    <div class="hp-breadcrumb-wrapper flex justify-between items-center mb-12" style="margin-top: 30px; align-items: center;">
        <div class="flex items-center space-x-2 text-sm text-gray-500" style="display: flex; align-items: center;">
            <span data-i18n="breadcrumb_home">Accueil</span>
            <span>→</span>
            <span class="text-gray-800 font-medium" data-i18n="breadcrumb_booking">Réserver une consigne</span>
        </div>
        <button id="back-to-step-1-btn" class="hidden bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover flex items-center" style="align-self: center;">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span data-i18n="btn_back">Retour</span>
        </button>
    </div>

    <!-- Form content with increased spacing -->
    <div class="grid lg:grid-cols-3 gap-8" style="margin-top: 40px;">
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
                        <h3 class="date-label text-sm font-medium text-gray-700 mb-4" data-i18n="form_deposit_date">DATE DE DÉPÔT DES BAGAGES *</h3>
                        <div class="datetime-container">
                            <div class="datetime-field">
                                <input type="date" id="date-depot" class="input-style w-full">
                            </div>
                            <div class="datetime-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_deposit_time">HEURE DE DÉPÔT *</label>
                                <div class="time-field-wrapper">
                                    <input type="text" id="heure-depot" class="time-field" readonly>
                                    <svg class="time-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    
                                    <!-- Popover Timepicker -->
                                    <div class="picker-popover" id="popover-depot">
                                        <div class="selectors">
                                            <div class="column">
                                                <button class="arrow" onclick="changeVal('H', 1, 'depot')">▲</button>
                                                <div id="h-val-depot" class="val-display">09</div>
                                                <button class="arrow" onclick="changeVal('H', -1, 'depot')">▼</button>
                                            </div>
                                            <div class="separator">:</div>
                                            <div class="column">
                                                <button class="arrow" onclick="changeVal('M', 5, 'depot')">▲</button>
                                                <div id="m-val-depot" class="val-display">00</div>
                                                <button class="arrow" onclick="changeVal('M', -5, 'depot')">▼</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="date-label text-sm font-medium text-gray-700 mb-4" data-i18n="form_pickup_date">DATE DE RÉCUPÉRATION DES BAGAGES *</h3>
                        <div class="datetime-container">
                            <div class="datetime-field">
                                <input type="date" id="date-recuperation" class="input-style w-full">
                            </div>
                            <div class="datetime-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form_pickup_time">HEURE DE RÉCUPÉRATION *</label>
                                <div class="time-field-wrapper">
                                    <input type="text" id="heure-recuperation" class="time-field" readonly>
                                    <svg class="time-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    
                                    <!-- Popover Timepicker -->
                                    <div class="picker-popover" id="popover-recuperation">
                                        <div class="selectors">
                                            <div class="column">
                                                <button class="arrow" onclick="changeVal('H', 1, 'recuperation')">▲</button>
                                                <div id="h-val-recuperation" class="val-display">18</div>
                                                <button class="arrow" onclick="changeVal('H', -1, 'recuperation')">▼</button>
                                            </div>
                                            <div class="separator">:</div>
                                            <div class="column">
                                                <button class="arrow" onclick="changeVal('M', 5, 'recuperation')">▲</button>
                                                <div id="m-val-recuperation" class="val-display">00</div>
                                                <button class="arrow" onclick="changeVal('M', -5, 'recuperation')">▼</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-2" data-i18n="form_baggage_info_title">
                            Choisissez la bonne catégorie de bagage
                        </h4>
                        <p class="text-sm text-gray-700 mb-2" data-i18n="form_baggage_info_message">
                            Pour préparer votre prise en charge dans les meilleures conditions, veillez à sélectionner le type de bagage adapté à ses dimensions et à son poids.
                        </p>
                        <p class="text-sm text-gray-700 font-medium" data-i18n="form_baggage_info_warning">
                            En cas d'erreur, votre enregistrement devra être ajusté en agence, ce qui peut rallonger le temps de prise en charge.
                        </p>
                    </div>
                </div>
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
                </div>
                <div id="cart-summary" class="bg-white border-2 border-yellow-400 rounded-lg p-6 shadow-sm" style="display: none;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-black text-center w-full" data-i18n="form_your_cart">Votre panier</h3>
                        <div id="cart-duration" class="text-sm text-gray-600 font-medium"></div>
                        <div class="custom-spinner" role="status" aria-hidden="true" id="loading-spinner-cart" style="display: none;"></div>
                    </div>
                    <div id="cart-items-container" class="panier-content">
                        <!-- Cart items will be injected here -->
                    </div>
                    
                    <!-- Ligne de remise (affichée seulement si remise) -->
                    <div id="cart-discount" class="py-3 flex justify-between items-center border-t border-gray-200 mt-3" style="display: none;">
                        <span class="discount-text text-sm text-green-600 font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span data-i18n="cart_discount_online">Offre réservation en ligne</span>
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
<script src="{{ asset('js/options-drawer.js') }}?v={{ $jsVersion('js/options-drawer.js') }}"></script>
<script src="{{ asset('js/contraintes.js') }}?v={{ $jsVersion('js/contraintes.js') }}"></script>
<script src="{{ asset('js/booking.js') }}?v={{ $jsVersion('js/booking.js') }}&t={{ time() }}"></script> 
<!-- Scripts for timepicker discret -->
<script>
    // Fonction pour formater l'heure avec zéro devant
    function formatTime(value) {
        return value.toString().padStart(2, '0');
    }

    // Initialiser les timepickers avec l'heure actuelle
    function initializeTimepickers() {
        // Ne PAS initialiser si des valeurs existent déjà (chargées depuis la session)
        const depotInput = document.getElementById('heure-depot');
        const recupInput = document.getElementById('heure-recuperation');
        
        // Si les inputs ont déjà des valeurs (depuis la session), ne pas les écraser
        if (depotInput && depotInput.value) {
            console.log('[initializeTimepickers] heure-depot déjà définie, skip:', depotInput.value);
            return;
        }
        if (recupInput && recupInput.value) {
            console.log('[initializeTimepickers] heure-recuperation déjà définie, skip:', recupInput.value);
            return;
        }
        
        const now = new Date();
        const currentHour = formatTime(now.getHours());
        const currentMinute = formatTime(Math.floor(now.getMinutes() / 5) * 5); // Arrondi à 5 minutes

        // Initialiser heure-depot
        const depotHDisplay = document.getElementById('h-val-depot');
        const depotMDisplay = document.getElementById('m-val-depot');

        if (depotInput && depotHDisplay && depotMDisplay) {
            depotHDisplay.innerText = currentHour;
            depotMDisplay.innerText = currentMinute;
            depotInput.value = `${currentHour}:${currentMinute}`;
        }

        // Initialiser heure-recuperation (1 heure après l'heure de dépôt par défaut)
        const recupHDisplay = document.getElementById('h-val-recuperation');
        const recupMDisplay = document.getElementById('m-val-recuperation');

        if (recupInput && recupHDisplay && recupMDisplay) {
            let defaultHour = parseInt(currentHour) + 1;
            if (defaultHour > 23) defaultHour = 0;
            defaultHour = formatTime(defaultHour);

            recupHDisplay.innerText = defaultHour;
            recupMDisplay.innerText = currentMinute;
            recupInput.value = `${defaultHour}:${currentMinute}`;
        }
    }

    // Gestion des timepickers discrets
    document.addEventListener('DOMContentLoaded', function () {
        // Initialiser avec l'heure actuelle
        initializeTimepickers();
        
        // Initialisation pour heure-depot
        const depotInput = document.getElementById('heure-depot');
        const depotPopover = document.getElementById('popover-depot');
        
        if (depotInput && depotPopover) {
            depotInput.addEventListener('click', (e) => {
                e.stopPropagation();
                const parent = e.target.parentElement;
                parent.appendChild(depotPopover);
                depotPopover.classList.add('active');
            });
        }

        // Initialisation pour heure-recuperation
        const recupInput = document.getElementById('heure-recuperation');
        const recupPopover = document.getElementById('popover-recuperation');

        if (recupInput && recupPopover) {
            recupInput.addEventListener('click', (e) => {
                e.stopPropagation();
                const parent = e.target.parentElement;
                parent.appendChild(recupPopover);
                recupPopover.classList.add('active');
            });
        }
        
        // Initialisation pour les timepickers de la modale QDM
        const qdmDepotInput = document.getElementById('heure-qdm-depot');
        const qdmDepotPopover = document.getElementById('qdm-popover-depot');
        
        if (qdmDepotInput && qdmDepotPopover) {
            qdmDepotInput.addEventListener('click', (e) => {
                e.stopPropagation();
                const parent = e.target.parentElement;
                parent.appendChild(qdmDepotPopover);
                qdmDepotPopover.classList.add('active');
            });
        }
        
        const qdmRecupInput = document.getElementById('heure-qdm-recuperation');
        const qdmRecupPopover = document.getElementById('qdm-popover-recuperation');
        
        if (qdmRecupInput && qdmRecupPopover) {
            qdmRecupInput.addEventListener('click', (e) => {
                e.stopPropagation();
                const parent = e.target.parentElement;
                parent.appendChild(qdmRecupPopover);
                qdmRecupPopover.classList.add('active');
            });
        }

        // Fermer le popover si on clique en dehors
        document.addEventListener('mousedown', (e) => {
            const popovers = document.querySelectorAll('.picker-popover');
            popovers.forEach(popover => {
                if (!popover.contains(e.target) && !e.target.classList.contains('time-field')) {
                    popover.classList.remove('active');
                }
            });
        });
    });

    // Fonctions globales pour les timepickers
    function changeVal(type, step, suffix) {
        const hDisplay = document.getElementById(`h-val-${suffix}`);
        const mDisplay = document.getElementById(`m-val-${suffix}`);
        
        if (type === 'H') {
            let h = parseInt(hDisplay.innerText) + step;
            if (h > 23) h = 0;
            if (h < 0) h = 23;
            hDisplay.innerText = h.toString().padStart(2, '0');
        } else {
            let m = parseInt(mDisplay.innerText) + step;
            if (m > 59) m = 0;
            if (m < 0) m = 55;
            mDisplay.innerText = m.toString().padStart(2, '0');
        }
        updateInput(suffix);
    }

    function updateInput(suffix) {
        const hDisplay = document.getElementById(`h-val-${suffix}`);
        const mDisplay = document.getElementById(`m-val-${suffix}`);
        const input = document.getElementById(`heure-${suffix}`);
        if (input && hDisplay && mDisplay) {
            input.value = `${hDisplay.innerText}:${mDisplay.innerText}`;
        }
    }

    function closePicker(suffix) {
        const popover = document.getElementById(`popover-${suffix}`);
        if (popover) {
            popover.classList.remove('active');
        }
    }

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
            t('payment_reset_title', 'Réinitialiser la commande'),
            t('payment_reset_text', 'Voulez-vous vraiment continuer ? Toutes les données saisies pour votre commande actuelle seront définitivement perdues.')
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

    // Réaligner lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (typeof alignStickyWithBaggage === 'function') {
            setTimeout(alignStickyWithBaggage, 50);
        }
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
#hp-booking-root .info-icon .text-gray-400 { color: var(--luxe-cream-muted) !important; }
#hp-booking-root .info-icon:hover .text-gray-400 { color: var(--luxe-gold) !important; }
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
#hp-booking-root #baggage-tooltip { background: #000000 !important; border: 1px solid var(--luxe-border); color: #ffffff !important; }
#hp-booking-root #baggage-tooltip .text-white { color: #ffffff !important; }
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

/* Alignement du panier avec la section aéroport sélectionné */
#hp-booking-root #sticky-wrapper {
  align-self: start !important;
  margin-top: 0 !important;
}
#hp-booking-root #sticky-summary {
  position: sticky;
  top: 0 !important;
  margin-top: 0 !important;
}
#hp-booking-root .grid.lg\:grid-cols-3 {
  align-items: start !important;
}
#hp-booking-root #baggage-selection-step {
  margin-top: 0 !important;
  padding-top: 0 !important;
}
#hp-booking-root #baggage-selection-step > .bg-gray-100:first-child,
#hp-booking-root #sticky-summary > .bg-white:first-child {
  margin-top: 0 !important;
  padding-top: 1.5rem !important;
}

/* Styles pour le panier amélioré */
#hp-booking-root .cart-item {
  border-bottom: 1px solid #f3f4f6 !important;
  align-items: flex-start !important;
}
#hp-booking-root .cart-item:last-child {
  border-bottom: 0 !important;
}
#hp-booking-root .cart-item .text-sm.font-medium {
  font-size: 0.9rem !important;
  line-height: 1.5 !important;
  word-break: break-word !important;
}
#hp-booking-root .badge-promo {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
  color: #166534 !important;
  font-weight: 700 !important;
  box-shadow: 0 1px 2px rgba(22, 101, 52, 0.1) !important;
  flex-shrink: 0 !important;
  white-space: nowrap !important;
}
#hp-booking-root .old-price {
  color: #9ca3af !important;
  text-decoration: line-through !important;
  font-size: 0.75rem !important;
  white-space: nowrap !important;
}
#hp-booking-root .current-price {
  color: #111827 !important;
  font-weight: 700 !important;
  font-size: 0.9rem !important;
  white-space: nowrap !important;
}
#hp-booking-root .price-wrapper {
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-end !important;
  gap: 0.25rem !important;
}
#hp-booking-root .delete-item-btn {
  flex-shrink: 0 !important;
  margin-left: 0.25rem !important;
}
/* Styles pour le panier du drawer options (priority/premium) */
#hp-booking-root #drawer-cart-items .cart-item {
  border-bottom: 1px solid #f3f4f6 !important;
  align-items: flex-start !important;
}
#hp-booking-root #drawer-cart-items .cart-item:last-child {
  border-bottom: 0 !important;
}
#hp-booking-root #drawer-cart-items .text-sm.font-bold {
  font-size: 0.9rem !important;
  line-height: 1.5 !important;
  word-break: break-word !important;
}
#hp-booking-root #drawer-cart-items .badge-promo {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
  color: #166534 !important;
  font-weight: 700 !important;
  box-shadow: 0 1px 2px rgba(22, 101, 52, 0.1) !important;
  flex-shrink: 0 !important;
  white-space: nowrap !important;
}
#hp-booking-root #drawer-cart-items .old-price {
  color: #9ca3af !important;
  text-decoration: line-through !important;
  font-size: 0.75rem !important;
  white-space: nowrap !important;
}
#hp-booking-root #drawer-cart-items .current-price {
  color: #111827 !important;
  font-weight: 700 !important;
  font-size: 0.9rem !important;
  white-space: nowrap !important;
}
#hp-booking-root #drawer-cart-items .price-wrapper {
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-end !important;
  gap: 0.25rem !important;
}
#hp-booking-root #drawer-cart-items .delete-item-btn {
  flex-shrink: 0 !important;
  margin-left: 0.25rem !important;
}
</style>

<!-- Styles pour masquer le chatbot quand le drawer ou modal est ouvert -->
<style>
    body.drawer-chatbot-hidden #chatbot-widget {
        display: none !important;
    }
    body.modal-chatbot-hidden #chatbot-widget {
        display: none !important;
    }
</style>
@endpush