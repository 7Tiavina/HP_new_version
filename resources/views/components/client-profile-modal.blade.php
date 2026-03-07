<div id="clientProfileModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm">
    <div class="relative mx-auto w-full max-w-3xl shadow-2xl bg-white overflow-hidden transform transition-all my-8 flex flex-col" style="max-height: 90vh;">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-4 text-center flex-shrink-0">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div id="step-1-indicator" class="w-8 h-8 bg-white text-yellow-600 rounded-full flex items-center justify-center font-bold shadow-lg text-sm">1</div>
                <div class="w-12 h-1 bg-white bg-opacity-50"></div>
                <div id="step-2-indicator" class="w-8 h-8 bg-white bg-opacity-30 text-white rounded-full flex items-center justify-center font-bold text-sm">2</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900" data-i18n="modal_title">Complétez vos informations</h3>
            <p id="step-1-title" class="text-gray-800 text-sm mt-0.5">Informations client</p>
            <p id="step-2-title" class="text-gray-800 text-sm mt-0.5 hidden">Informations PREMIUM</p>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="flex-1 overflow-y-auto p-8">
            <form id="clientProfileForm" class="max-w-4xl mx-auto">
                @csrf

                <input type="hidden" name="email" id="modal-email">

                <!-- STEP 1: Client Information -->
                <div id="step-1-content" class="space-y-5">
                    <!-- Type de Client - Reduced -->
                    <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-3 tracking-wide" data-i18n="client_type_label">Type de client</label>
                        <div class="flex gap-3">
                            <label class="flex-1 flex items-center p-3 bg-white rounded-lg border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                                <input type="radio" id="client-particulier" name="clientType" value="particulier" checked class="w-4 h-4 text-yellow-500 focus:ring-yellow-400">
                                <span class="ml-2 text-sm font-medium text-gray-800" data-i18n="client_type_particulier">Particulier</span>
                            </label>
                            <label class="flex-1 flex items-center p-3 bg-white rounded-lg border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                                <input type="radio" id="client-societe" name="clientType" value="societe" class="w-4 h-4 text-yellow-500 focus:ring-yellow-400">
                                <span class="ml-2 text-sm font-medium text-gray-800" data-i18n="client_type_societe">Société</span>
                            </label>
                        </div>
                    </div>

                    <!-- Coordonnées -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-5">
                            <h4 class="font-bold text-gray-900 flex items-center text-lg">
                                <span class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-full flex items-center justify-center mr-3 text-sm font-bold shadow-md">1</span>
                                Vos coordonnées
                            </h4>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_prenom">Prénom <span class="text-red-500">*</span></label>
                                    <input type="text" name="prenom" id="modal-prenom" required placeholder="Prénom" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_nom">Nom <span class="text-red-500">*</span></label>
                                    <input type="text" name="nom" id="modal-nom" required placeholder="Nom" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                                </div>
                            </div>

                            <div>
                                <label for="modal-telephone" class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Téléphone mobile <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mt-1">Entrez votre numéro avec le code pays (ex : +33 6 12 34 56 78)</p>
                                <div class="mt-2">
                                    <input type="tel" name="telephone" id="modal-telephone" required placeholder="+33 6 12 34 56 78" autocomplete="off" class="block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_adresse">Adresse <span class="text-red-500">*</span></label>
                                <input type="text" name="adresse" id="modal-adresse" required maxlength="50" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                                <p class="text-xs text-gray-500 mt-2"><span id="adresse-counter">0</span><span data-i18n="address_counter_suffix">/50 caractères</span></p>
                            </div>
                        </div>

                        <!-- Right Column - Optional fields -->
                        <div class="space-y-5">
                            <div class="flex items-center">
                                <button id="toggleAdditionalFieldsBtn" type="button" class="text-sm font-bold text-gray-700 flex items-center hover:text-yellow-600 transition-colors" data-i18n="btn_complete_profile">
                                    <span id="toggleText">Compléter mon profil (facultatif)</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div id="additional-fields-container" class="hidden mt-4 pt-6 border-t-2 border-gray-200">
                                <div id="societe-field-container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_societe">Nom de la Société</label>
                                    <input type="text" name="nomSociete" id="modal-nomSociete" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-3 text-gray-800 font-medium">
                                </div>
                            </div>

                            <!-- Info box -->
                            <div class="mt-6 p-6 bg-blue-50 rounded-2xl border border-blue-200">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-blue-900">Pourquoi ces informations ?</h5>
                                        <p class="text-sm text-blue-700 mt-1">Pour finaliser votre réservation et permettre le contrôle de sécurité des bagages par rayons X conformément aux réglementations aéroportuaires.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Premium Information -->
                <div id="step-2-content" class="hidden space-y-6">
                    <div class="mb-6 p-4 bg-purple-50 rounded-xl border border-purple-200">
                        <p class="text-sm text-purple-800">
                            <span class="font-bold">ℹ️ Information :</span> Vous avez sélectionné l'option <span class="font-semibold">Premium</span>. Veuillez remplir les informations de transport ci-dessous pour permettre à notre équipe de préparer votre service VIP.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Arrival Section -->
                        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-500 rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <h5 class="ml-3 font-bold text-gray-900 text-lg">Arrivée (Terminal → Agence)</h5>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Type de transport <span class="text-red-500">*</span></label>
                                    <select name="transport_type_arrival" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
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
                                    <select name="pickup_location_arrival" id="modal-pickup-location-arrival" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Date et heure <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" name="pickup_datetime_arrival" id="pickup-datetime-arrival" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Informations complémentaires</label>
                                    <textarea name="instructions_arrival" rows="2" class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-3 text-gray-800 font-medium" placeholder="commentaires..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Departure Section -->
                        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </div>
                                <h5 class="ml-3 font-bold text-gray-900 text-lg">Départ (Agence → Terminal)</h5>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Type de transport <span class="text-red-500">*</span></label>
                                    <select name="transport_type_departure" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
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
                                    <select name="restitution_location_departure" id="modal-restitution-location-departure" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Date et heure <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" name="restitution_datetime_departure" id="restitution-datetime-departure" required class="mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-3 text-gray-800 font-medium">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer - Fixed -->
        <div class="border-t-2 border-gray-200 p-6 bg-gray-50 flex-shrink-0">
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-4 items-center justify-between">
                <button id="closeClientProfileModalBtn" type="button" class="w-full md:w-auto px-8 py-3 text-gray-600 font-bold hover:text-gray-800 transition-all border-2 border-gray-300 rounded-xl hover:border-gray-400 hover:bg-gray-100" data-i18n="btn_cancel">
                    Annuler
                </button>
                <div class="flex gap-4 w-full md:w-auto">
                    <button id="backToStep1Btn" type="button" class="hidden w-full md:w-auto px-8 py-3 text-gray-600 font-bold hover:text-gray-800 transition-all border-2 border-gray-300 rounded-xl hover:border-gray-400 hover:bg-gray-100">
                        ← Retour
                    </button>
                    <button id="saveClientProfileBtn" type="button" class="w-full md:w-auto px-12 py-4 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center justify-center text-lg">
                        <span id="btn-continue-text">Continuer</span>
                        <span id="btn-confirm-text" class="hidden">Confirmer et payer</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>