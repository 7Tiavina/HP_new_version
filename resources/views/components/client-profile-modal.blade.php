<div id="clientProfileModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-0 sm:p-4 bg-gray-900 bg-opacity-75 backdrop-blur-sm">
    <div class="relative mx-auto w-full sm:max-w-3xl shadow-2xl bg-white overflow-hidden transform transition-all sm:my-8 flex flex-col sm:rounded-2xl sm:h-auto h-[90vh]" style="max-height: 90vh;">

    <!-- Styles pour masquer le chatbot quand le modal est ouvert -->
    <style>
        body.modal-chatbot-hidden #chatbot-widget {
            display: none !important;
        }
    </style>

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-2 sm:p-4 text-center flex-shrink-0">
            <div class="flex items-center justify-center gap-1.5 sm:gap-3 mb-1.5 sm:mb-2">
                <div id="step-1-indicator" class="w-6 h-6 sm:w-8 sm:h-8 bg-white text-yellow-600 rounded-full flex items-center justify-center font-bold shadow-lg text-xs">1</div>
                <div class="w-6 sm:w-12 h-1 bg-white bg-opacity-50"></div>
                <div id="step-2-indicator" class="w-6 h-6 sm:w-8 sm:h-8 bg-white bg-opacity-30 text-white rounded-full flex items-center justify-center font-bold text-xs">2</div>
            </div>
            <h3 class="text-base sm:text-xl font-bold text-gray-900" style="font-family: 'Space Grotesk', sans-serif;" data-i18n="modal_title">Complétez vos informations</h3>
            <p id="step-1-title" class="text-gray-800 text-xs sm:text-sm mt-0.5" style="font-family: 'Space Grotesk', sans-serif;">Informations client</p>
            <p id="step-2-title" class="text-gray-800 text-xs sm:text-sm mt-0.5 hidden" style="font-family: 'Space Grotesk', sans-serif;">Informations PREMIUM</p>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="flex-1 overflow-y-auto p-3 sm:p-5 pb-4 sm:pb-6">
            <form id="clientProfileForm" class="max-w-4xl mx-auto">
                @csrf

                <input type="hidden" name="email" id="modal-email">

                <!-- STEP 1: Client Information -->
                <div id="step-1-content" class="space-y-4 sm:space-y-5">
                    <!-- Type de Client - Reduced -->
                    <div class="p-3 sm:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 sm:mb-3 tracking-wide" data-i18n="client_type_label">Type de client</label>
                        <div class="flex gap-2 sm:gap-3">
                            <label class="flex-1 flex items-center p-2 sm:p-3 bg-white rounded-lg border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                                <input type="radio" id="client-particulier" name="clientType" value="particulier" checked class="w-4 h-4 text-yellow-500 focus:ring-yellow-400">
                                <span class="ml-2 text-xs sm:text-sm font-medium text-gray-800" data-i18n="client_type_particulier">Particulier</span>
                            </label>
                            <label class="flex-1 flex items-center p-2 sm:p-3 bg-white rounded-lg border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition-all">
                                <input type="radio" id="client-societe" name="clientType" value="societe" class="w-4 h-4 text-yellow-500 focus:ring-yellow-400">
                                <span class="ml-2 text-xs sm:text-sm font-medium text-gray-800" data-i18n="client_type_societe">Société</span>
                            </label>
                        </div>
                    </div>

                    <!-- Coordonnées -->
                    <div class="grid grid-cols-1 gap-4 sm:gap-6">
                        <div class="space-y-4 sm:space-y-5">
                            <h4 class="font-bold text-gray-900 flex items-center text-base sm:text-lg">
                                <span class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-full flex items-center justify-center mr-2 sm:mr-3 text-xs sm:text-sm font-bold shadow-md">1</span>
                                <span data-i18n="modal_section_1">Vos coordonnées</span>
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_prenom">Prénom <span class="text-red-500">*</span></label>
                                    <input type="text" name="prenom" id="modal-prenom" required data-i18n-placeholder="placeholder_prenom" placeholder="Prénom" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_nom">Nom <span class="text-red-500">*</span></label>
                                    <input type="text" name="nom" id="modal-nom" required data-i18n-placeholder="placeholder_nom" placeholder="Nom" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>
                            </div>

                            <div>
                                <label for="modal-telephone" class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_telephone">Téléphone mobile <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mt-1" data-i18n="phone_hint">Entrez votre numéro avec le code pays (ex : +33 6 12 34 56 78)</p>
                                <div class="mt-1.5 sm:mt-2">
                                    <input type="tel" name="telephone" id="modal-telephone" required placeholder="+33 6 12 34 56 78" autocomplete="off" class="block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_adresse">Adresse <span class="text-red-500">*</span></label>
                                <input type="text" name="adresse" id="modal-adresse" required maxlength="50" data-i18n-placeholder="placeholder_address_payment" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                <p class="text-xs text-gray-500 mt-1.5 sm:mt-2"><span id="adresse-counter">0</span><span data-i18n="address_counter_suffix">/50 caractères</span></p>
                            </div>
                        </div>

                        <!-- Right Column - Optional fields -->
                        <div class="space-y-4 sm:space-y-5">
                            <div class="flex items-center">
                                <button id="toggleAdditionalFieldsBtn" type="button" class="text-xs sm:text-sm font-bold text-gray-700 flex items-center hover:text-yellow-600 transition-colors" data-i18n="btn_complete_profile">
                                    <span id="toggleText">Compléter mon profil (facultatif)</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div id="additional-fields-container" class="hidden mt-3 sm:mt-4 pt-4 sm:pt-6 border-t-2 border-gray-200">
                                <div id="societe-field-container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="label_societe">Nom de la Société</label>
                                    <input type="text" name="nomSociete" id="modal-nomSociete" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>
                            </div>

                            <!-- Info box -->
                            <div class="mt-4 sm:mt-6 p-4 sm:p-6 bg-green-50 rounded-2xl border border-green-200">
                                <div class="flex items-start gap-2 sm:gap-3">
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-green-900 mb-2 text-sm sm:text-base" data-i18n="why_info_title">Pourquoi ces informations ?</h5>
                                        <p class="text-xs sm:text-sm text-green-800 mb-2" data-i18n="why_info_text_part1">
                                            Vos coordonnées sont nécessaires pour enregistrer votre commande.
                                        </p>
                                        <p class="text-xs sm:text-sm text-green-800" data-i18n="why_info_text_part2">
                                            Elles sont également requises pour des raisons de sécurité, tous les bagages déposés en consigne étant contrôlés par rayon X conformément aux exigences aéroportuaires.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Premium Information -->
                <div id="step-2-content" class="hidden space-y-4 sm:space-y-6">
                    <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-purple-50 rounded-xl border border-purple-200">
                        <p class="text-xs sm:text-sm text-purple-800" data-i18n="premium_info_notice">
                            <span class="font-bold">ℹ️ Information :</span> Vous avez choisi l'option Premium, et nous vous en remercions.
                            <br>
                            Les informations à compléter ci-dessous nous permettent d'organiser votre prestation.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:gap-6">
                        <!-- Arrival Section -->
                        <div class="p-4 sm:p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200">
                            <div class="flex items-center mb-4 sm:mb-6">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-400 to-green-500 rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <h5 class="ml-2 sm:ml-3 font-bold text-gray-900 text-sm sm:text-lg" data-i18n="premium_arrival_title">Arrivée (Terminal → Agence)</h5>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_transport_label">Type de transport <span class="text-red-500">*</span></label>
                                    <select name="transport_type_arrival" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                        <option value="" data-i18n="premium_select">Sélectionner</option>
                                        <option value="airport" data-i18n="premium_airport">Avion</option>
                                        <option value="train" data-i18n="premium_train">TGV</option>
                                        <option value="public_transport" data-i18n="premium_public_transport">Métro / RER</option>
                                        <option value="other" data-i18n="premium_other">Autre</option>
                                    </select>
                                </div>

                                <div id="flight_number_arrival_container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_flight_number">Numéro de vol</label>
                                    <input type="text" name="flight_number_arrival" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base" placeholder="Ex: AF1234">
                                </div>

                                <div id="train_number_arrival_container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_train_number">Numéro de TGV</label>
                                    <input type="text" name="train_number_arrival" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base" placeholder="Ex: TGV8823">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_pickup_location">Lieu de prise en charge <span class="text-red-500">*</span></label>
                                    <select name="pickup_location_arrival" id="modal-pickup-location-arrival" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                        <option value="" data-i18n="premium_select">Sélectionner</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1 group">
                                        <span data-i18n="premium_datetime_label">Date et heure</span>
                                        <span class="text-red-500">*</span>
                                        <div class="relative inline-block">
                                            <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="absolute left-0 top-6 transform ml-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg shadow-lg w-56 sm:w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                                Les dates et heures de prise en charge peuvent être modifiées et peuvent être différentes de celles de la consigne.
                                            </div>
                                        </div>
                                    </label>
                                    <input type="datetime-local" name="pickup_datetime_arrival" id="pickup-datetime-arrival" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_instructions">Informations complémentaires</label>
                                    <textarea name="instructions_arrival" rows="2" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base" data-i18n-placeholder="premium_instructions_placeholder" placeholder="commentaires..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Departure Section -->
                        <div class="p-4 sm:p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200">
                            <div class="flex items-center mb-4 sm:mb-6">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </div>
                                <h5 class="ml-2 sm:ml-3 font-bold text-gray-900 text-sm sm:text-lg" data-i18n="premium_departure_title">Départ (Agence → Terminal)</h5>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_transport_label">Type de transport <span class="text-red-500">*</span></label>
                                    <select name="transport_type_departure" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                        <option value="" data-i18n="premium_select">Sélectionner</option>
                                        <option value="airport" data-i18n="premium_airport">Avion</option>
                                        <option value="train" data-i18n="premium_train">TGV</option>
                                        <option value="public_transport" data-i18n="premium_public_transport">Métro / RER</option>
                                        <option value="other" data-i18n="premium_other">Autre</option>
                                    </select>
                                </div>

                                <div id="flight_number_departure_container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_flight_number">Numéro de vol</label>
                                    <input type="text" name="flight_number_departure" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base" placeholder="Ex: AF456">
                                </div>

                                <div id="train_number_departure_container" class="hidden">
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_train_number">Numéro de TGV</label>
                                    <input type="text" name="train_number_departure" class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base" placeholder="Ex: TGV8824">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide" data-i18n="premium_restitution_location">Lieu de restitution <span class="text-red-500">*</span></label>
                                    <select name="restitution_location_departure" id="modal-restitution-location-departure" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                        <option value="" data-i18n="premium_select">Sélectionner</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1 group">
                                        <span data-i18n="premium_datetime_label">Date et heure</span>
                                        <span class="text-red-500">*</span>
                                        <div class="relative inline-block">
                                            <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="absolute left-0 top-6 transform ml-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg shadow-lg w-56 sm:w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                                Les dates et heures de restitution peuvent être modifiées et peuvent être différentes de celles de la consigne.
                                            </div>
                                        </div>
                                    </label>
                                    <input type="datetime-local" name="restitution_datetime_departure" id="restitution-datetime-departure" required class="mt-1.5 sm:mt-2 block w-full rounded-xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all py-2.5 sm:py-3 text-gray-800 font-medium text-sm sm:text-base">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer - Fixed -->
        <div class="border-t-2 border-gray-200 p-2 sm:p-3 bg-gray-50 flex-shrink-0">
            <div class="max-w-4xl mx-auto flex flex-col gap-1.5 sm:gap-3 items-center justify-between">
                <button id="closeClientProfileModalBtn" type="button" class="w-full px-4 sm:px-8 py-2 sm:py-2.5 text-gray-600 font-bold hover:text-gray-800 transition-all border-2 border-gray-300 rounded-xl hover:border-gray-400 hover:bg-gray-100 text-sm sm:text-base" data-i18n="btn_cancel">
                    Annuler
                </button>
                <div class="flex gap-1.5 sm:gap-3 w-full">
                    <button id="backToStep1Btn" type="button" class="hidden w-full px-3 sm:px-6 py-2 sm:py-2.5 text-gray-600 font-bold hover:text-gray-800 transition-all border-2 border-gray-300 rounded-xl hover:border-gray-400 hover:bg-gray-100 text-xs sm:text-sm sm:text-base" data-i18n="btn_back">
                        ← Retour
                    </button>
                    <button id="saveClientProfileBtn" type="button" class="w-full px-4 sm:px-10 py-2 sm:py-3 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center justify-center text-sm sm:text-lg">
                        <span id="btn-continue-text" data-i18n="btn_continue">Continuer</span>
                        <span id="btn-confirm-text" class="hidden" data-i18n="btn_confirm_pay">Confirmer et payer</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour gérer l'affichage du chatbot -->
<script>
(function() {
    const modal = document.getElementById('clientProfileModal');
    const openBtn = document.getElementById('openClientProfileModalBtn');
    const closeBtn = document.getElementById('closeClientProfileModalBtn');
    
    if (!modal) return;
    
    // Fonction pour masquer le chatbot
    function hideChatbot() {
        document.body.classList.add('modal-chatbot-hidden');
    }
    
    // Fonction pour afficher le chatbot
    function showChatbot() {
        document.body.classList.remove('modal-chatbot-hidden');
    }
    
    // Écouter l'ouverture du modal
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            // Petit délai pour s'assurer que le modal est ouvert
            setTimeout(function() {
                if (!modal.classList.contains('hidden')) {
                    hideChatbot();
                }
            }, 100);
        });
    }
    
    // Écouter la fermeture du modal
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            showChatbot();
        });
    }
    
    // Fermeture via la touche Echap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            showChatbot();
        }
    });
    
    // Fermeture en cliquant en dehors du modal (sur l'overlay)
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            showChatbot();
        }
    });
})();
</script>