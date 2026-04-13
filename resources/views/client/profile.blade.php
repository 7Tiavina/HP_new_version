@extends('layouts.front')

@section('title', 'Mon Profil — Hello Passenger')

@push('styles')
    <script>window.tailwind=window.tailwind||{};window.tailwind.config={corePlugins:{preflight:false},important:'#client-page-root'};</script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css"/>
    
    <!-- intl-tel-input dark theme -->
    <style>
        .iti__country-list {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #ffffff !important;
        }
        .iti__country {
            color: #ffffff !important;
        }
        .iti__country:hover {
            background-color: #374151 !important;
        }
        .iti__highlight {
            background-color: #f9c52d !important;
        }
    </style>
@endpush

@section('content')
<div id="client-page-root" class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900" data-i18n="profile_title">Mon Profil</h1>
                <p class="text-gray-600 mt-2" data-i18n="profile_subtitle">Gérez vos informations personnelles</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors" data-i18n="btn_back">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold" data-i18n="success">Succès!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold" data-i18n="error">Erreur!</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('client.update-profile') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $client->prenom) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                    </div>
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom', $client->nom) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_email">Email</label>
                        <input type="email" id="email" value="{{ $client->email }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed" disabled>
                        <p class="text-xs text-gray-500 mt-1" data-i18n="profile_email_note">L'email ne peut pas être modifié</p>
                    </div>
                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_telephone">Téléphone mobile</label>
                        <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}" placeholder="" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4" data-i18n="address_section">Adresse</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-2" data-i18n="label_adresse">Adresse</label>
                            <div class="relative">
                                <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $client->adresse) }}" placeholder="Commencez à taper votre adresse..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition-colors" data-i18n="btn_save">Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- DELETE ACCOUNT SECTION -->
        <div class="mt-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-900" data-i18n="delete_account_title">Supprimer mon compte</h3>
                        <p class="text-sm text-red-700 mt-1" data-i18n="delete_account_desc">Cette action est irréversible. Toutes vos données personnelles seront définitivement supprimées conformément au RGPD.</p>
                        <button type="button" id="open-delete-modal" class="mt-3 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors" data-i18n="delete_account_btn">Supprimer mon compte</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE ACCOUNT MODAL -->
<div id="delete-account-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900" data-i18n="delete_modal_title">Confirmer la suppression</h2>
                <button type="button" id="close-delete-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="delete-account-form" method="POST" action="{{ route('client.delete-account') }}">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-800" data-i18n="delete_modal_warning">En supprimant votre compte :</p>
                    <ul class="text-sm text-red-700 mt-2 space-y-1 list-disc list-inside">
                        <li data-i18n="delete_modal_item1">Vos données personnelles seront définitivement effacées</li>
                        <li data-i18n="delete_modal_item3">Vous ne pourrez plus accéder à votre historique</li>
                        <li data-i18n="delete_modal_item4">Cette action est <strong>irréversible</strong></li>
                    </ul>
                </div>
                <label class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg cursor-pointer">
                    <input type="checkbox" id="delete-confirm-checkbox" name="confirm_delete" value="1" required
                        class="mt-0.5 w-4 h-4 text-red-600 border-red-300 rounded focus:ring-red-500">
                    <span class="text-sm text-red-800" data-i18n="delete_modal_confirm_checkbox">Je comprends que la suppression de mon compte est <strong>définitive et irréversible</strong> et que toutes mes données personnelles seront effacées.</span>
                </label>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <button type="button" id="cancel-delete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" data-i18n="delete_modal_cancel">Annuler</button>
                <button type="submit" id="confirm-delete-btn" disabled class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" data-i18n="delete_modal_confirm">Supprimer définitivement</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete account modal
    const openDeleteBtn = document.getElementById('open-delete-modal');
    const deleteModal = document.getElementById('delete-account-modal');
    const closeDeleteBtn = document.getElementById('close-delete-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const deleteCheckbox = document.getElementById('delete-confirm-checkbox');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

    function openDeleteModal() {
        deleteModal.classList.remove('hidden');
        if (deleteCheckbox) deleteCheckbox.checked = false;
        confirmDeleteBtn.disabled = true;
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        if (deleteCheckbox) deleteCheckbox.checked = false;
        confirmDeleteBtn.disabled = true;
    }

    if (openDeleteBtn) openDeleteBtn.addEventListener('click', openDeleteModal);
    if (closeDeleteBtn) closeDeleteBtn.addEventListener('click', closeDeleteModal);
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);

    // Close on backdrop click
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) closeDeleteModal();
        });
    }

    // Enable/disable confirm button based on checkbox
    if (deleteCheckbox) {
        deleteCheckbox.addEventListener('change', function() {
            confirmDeleteBtn.disabled = !this.checked;
        });
    }
});
</script>
@endsection

<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('telephone');
        if (phoneInput && window.intlTelInput) {
            const iti = window.intlTelInput(phoneInput, {
                initialCountry: 'auto',
                geoIpLookup: function(callback) {
                    fetch('https://ipapi.co/json')
                        .then(function(res) { return res.json(); })
                        .then(function(data) { callback(data.country_code); })
                        .catch(function() { callback('fr'); });
                },
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
                preferredCountries: ['fr', 'mu', 'be', 'ch', 'ca'],
                autoPlaceholder: 'aggressive',
                separateDialCode: false
            });
        }
    });
</script>

@php
    $googlePlacesApiKey = config('services.google.places_api_key');
@endphp

@if($googlePlacesApiKey)
@push('scripts')
<script>
    // ========================================================================
    // GOOGLE PLACES API - ADDRESS AUTOCOMPLETE (Same as /payment modal)
    // ========================================================================
    
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
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googlePlacesApiKey}&libraries=places&language=fr&callback=initGooglePlacesProfile&v=3.52&_=${googleApiVersion}`;
        script.async = true;
        script.defer = true;

        // Définir la fonction de rappel globale
        window.initGooglePlacesProfile = function() {
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
    function initProfileAutocomplete() {
        const addressInput = document.getElementById('adresse');

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

                addressInput.value = fullAddress.trim();
                
                // Auto-fill city and country fields
                const villeInput = document.getElementById('ville');
                const paysInput = document.getElementById('pays');
                
                if (city && villeInput) {
                    villeInput.value = city;
                }
                if (country && paysInput) {
                    paysInput.value = country;
                }

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

            console.log('Google Places Autocomplete initialized for profile');
        } catch (error) {
            console.error('Error initializing Google Places Autocomplete:', error);
        }
    }

    // Charger l'API et initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.google || !window.google.maps || !window.google.maps.places) {
            loadGoogleMapsAPI(initProfileAutocomplete);
        } else {
            setTimeout(initProfileAutocomplete, 100);
        }
    });
</script>
@endpush
@endif
