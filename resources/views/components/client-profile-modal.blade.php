<div id="clientProfileModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-75 backdrop-blur-sm">
    <div class="relative mx-auto max-w-4xl w-full shadow-2xl rounded-3xl bg-white overflow-hidden transform transition-all">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-8 text-center">
            <h3 class="text-3xl font-bold text-gray-900" data-i18n="modal_title">Complétez vos informations</h3>
            <p class="text-gray-800 text-base mt-2" data-i18n="modal_subtitle">
                Finalisez votre réservation en remplissant vos coordonnées
            </p>
            <p id="premium-modal-notice" class="text-gray-900 text-sm mt-3 font-semibold hidden bg-white bg-opacity-30 inline-block px-4 py-2 rounded-full" data-i18n="modal_premium_notice">
                ⭐ Option PREMIUM sélectionnée - Informations complémentaires requises
            </p>
        </div>

        <div class="p-8">
            <form id="clientProfileForm" class="space-y-6">
                @csrf

                <input type="hidden" name="email" id="modal-email">

                <!-- Type de Client: Particulier vs Société -->
                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl border border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-4 tracking-wide" data-i18n="client_type_label">Type de client</label>
                    <div class="flex gap-4">
                        <label class="flex-1 flex items-center p-4 bg-white rounded-xl border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                            <input type="radio" id="client-particulier" name="clientType" value="particulier" checked class="w-5 h-5 text-yellow-500 focus:ring-yellow-400">
                            <span class="ml-3 text-base font-medium text-gray-800" data-i18n="client_type_particulier">Particulier</span>
                        </label>
                        <label class="flex-1 flex items-center p-4 bg-white rounded-xl border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                            <input type="radio" id="client-societe" name="clientType" value="societe" class="w-5 h-5 text-yellow-500 focus:ring-yellow-400">
                            <span class="ml-3 text-base font-medium text-gray-800" data-i18n="client_type_societe">Société</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Left Column - Coordonnées -->
                    <div class="space-y-5">
                        <h4 class="font-bold text-gray-900 flex items-center text-lg" data-i18n="modal_section_1">
                            <span class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-full flex items-center justify-center mr-3 text-sm font-bold shadow-md">1</span>
                            Vos coordonnées
                        </h4>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_prenom">Prénom</label>
                                <input type="text" name="prenom" id="modal-prenom" placeholder="Prénom" data-i18n-placeholder="placeholder_prenom" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_nom">Nom</label>
                                <input type="text" name="nom" id="modal-nom" placeholder="Nom" data-i18n-placeholder="placeholder_nom" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                            </div>
                        </div>

                        <div>
                            <label for="modal-telephone" class="block text-xs font-bold text-gray-600 uppercase tracking-wide">
                                Téléphone mobile
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Entrez votre numéro avec le code pays (ex : +33 6 12 34 56 78)
                            </p>
                            <div class="mt-2">
                                <input type="tel"
                                    name="telephone"
                                    id="modal-telephone"
                                    placeholder="+33 6 12 34 56 78"
                                    autocomplete="off"
                                    class="block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_adresse">Adresse</label>
                            <input type="text" name="adresse" id="modal-adresse" maxlength="50" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                            <p class="text-xs text-gray-500 mt-2"><span id="adresse-counter">0</span><span data-i18n="address_counter_suffix">/50 caractères</span></p>
                        </div>
                    </div>

                    <!-- Right Column - Infos complémentaires -->
                    <div class="space-y-5">
                        <div class="flex items-center">
                            <button id="toggleAdditionalFieldsBtn" type="button" class="text-sm font-bold text-gray-700 flex items-center hover:text-yellow-600 transition-colors" data-i18n="btn_complete_profile">
                                <span id="toggleText">Compléter mon profil (facultatif)</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <div id="additional-fields-container" class="hidden mt-4 pt-6 border-t-2 border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
                            <div id="societe-field-container" class="hidden md:col-span-3">
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_societe">Nom de la Société</label>
                                <input type="text" name="nomSociete" id="modal-nomSociete" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Section infos PREMIUM -->
                    <div id="premium-fields-modal-container" class="hidden md:col-span-2 space-y-6">
                        <h4 class="font-bold text-gray-900 flex items-center text-lg">
                            <span class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-full flex items-center justify-center mr-3 text-sm font-bold shadow-md">2</span>
                            Informations PREMIUM
                        </h4>

                        <!-- Premium Fields Grid - Side by Side -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Arrival Section (Left) -->
                            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-500 rounded-xl flex items-center justify-center shadow-md">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </div>
                                    <h5 class="ml-3 font-bold text-gray-900">Arrivée (Terminal → Agence)</h5>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Type de transport <span class="text-red-500">*</span></label>
                                        <select name="transport_type_arrival" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
                                            <option value="">Sélectionner</option>
                                            <option value="airport">Avion</option>
                                            <option value="train">Train</option>
                                            <option value="public_transport">Transports en commun</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>

                                    <div id="flight_number_arrival_container" class="hidden">
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Numéro de vol</label>
                                        <input type="text" name="flight_number_arrival" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium" placeholder="Ex: AF1234">
                                    </div>

                                    <div id="train_number_arrival_container" class="hidden">
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Numéro de train</label>
                                        <input type="text" name="train_number_arrival" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium" placeholder="Ex: TGV8823">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Lieu de prise en charge <span class="text-red-500">*</span></label>
                                        <select name="pickup_location_arrival" id="modal-pickup-location-arrival" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
                                            <option value="">Sélectionner</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Date et heure <span class="text-red-500">*</span></label>
                                        <input type="datetime-local" name="pickup_datetime_arrival" id="pickup-datetime-arrival" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Informations complémentaires</label>
                                        <textarea name="instructions_arrival" rows="2" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium" placeholder="Précisions pour faciliter la prise en charge..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Departure Section (Right) -->
                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center shadow-md">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                    </div>
                                    <h5 class="ml-3 font-bold text-gray-900">Départ (Agence → Terminal)</h5>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Type de transport <span class="text-red-500">*</span></label>
                                        <select name="transport_type_departure" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
                                            <option value="">Sélectionner</option>
                                            <option value="airport">Avion</option>
                                            <option value="train">Train</option>
                                            <option value="public_transport">Transports en commun</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>

                                    <div id="flight_number_departure_container" class="hidden">
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Numéro de vol</label>
                                        <input type="text" name="flight_number_departure" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium" placeholder="Ex: AF456">
                                    </div>

                                    <div id="train_number_departure_container" class="hidden">
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Numéro de train</label>
                                        <input type="text" name="train_number_departure" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium" placeholder="Ex: TGV8824">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Lieu de restitution <span class="text-red-500">*</span></label>
                                        <select name="restitution_location_departure" id="modal-restitution-location-departure" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
                                            <option value="">Sélectionner</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Date et heure <span class="text-red-500">*</span></label>
                                        <input type="datetime-local" name="restitution_datetime_departure" id="restitution-datetime-departure" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4 items-center justify-center pt-6 border-t-2 border-gray-200">
                    <button id="closeClientProfileModalBtn" type="button" class="w-full md:w-auto order-2 md:order-1 px-8 py-3 text-gray-600 font-bold hover:text-gray-800 transition-all border-2 border-gray-300 rounded-xl hover:border-gray-400 hover:bg-gray-50" data-i18n="btn_cancel">
                        Annuler
                    </button>
                    <button id="saveClientProfileBtn" type="submit" class="w-full md:w-auto order-1 md:order-2 px-12 py-4 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center justify-center text-lg" data-i18n="btn_confirm_pay">
                        Confirmer et payer
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>