<div id="clientProfileModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4" style="background-image: linear-gradient(rgba(33, 33, 33, 0.8), rgba(33, 33, 33, 0.8)), url('{{ asset('rayonx.png') }}'); background-size: cover; background-position: center;">
    <div class="relative mx-auto border-none max-w-5xl w-full shadow-2xl rounded-3xl bg-white overflow-hidden transform transition-all">
        
        <div class="bg-[#ffc107] p-6 text-[#212121] text-center rounded-t-3xl">
            <h3 class="text-2xl font-bold" data-i18n="modal_title">Votre sécurité, notre priorité !</h3>
            <p class="text-[#212121] text-opacity-90 text-sm mt-1" data-i18n="modal_subtitle">
                Pour la protection de vos biens et le respect des normes aéroportuaires (contrôle par rayons X), veuillez compléter vos informations de contact.
            </p>        </div>

        <div class="p-8 relative">
            <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: url('{{ asset('rayonx.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            <form id="clientProfileForm" class="relative z-10">
                @csrf
                
                <input type="hidden" name="email" id="modal-email">

                <!-- Type de Client: Particulier vs Société -->
                <div class="mb-8 p-6 bg-gray-100 rounded-2xl border-2 border-gray-300">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-4" data-i18n="client_type_label">Type de client</label>
                    <div class="flex gap-6">
                        <div class="flex items-center">
                            <input type="radio" id="client-particulier" name="clientType" value="particulier" checked class="w-5 h-5 text-[#ffc107] cursor-pointer">
                            <label for="client-particulier" class="ml-3 text-lg font-medium text-gray-800 cursor-pointer" data-i18n="client_type_particulier">Particulier</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="client-societe" name="clientType" value="societe" class="w-5 h-5 text-[#ffc107] cursor-pointer">
                            <label for="client-societe" class="ml-3 text-lg font-medium text-gray-800 cursor-pointer" data-i18n="client_type_societe">Société</label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-4">
                        <h4 class="font-bold text-[#212121] flex items-center" data-i18n="modal_section_1">
                            <span class="w-8 h-8 bg-[#ffc107] bg-opacity-20 text-[#212121] rounded-full flex items-center justify-center mr-2 text-sm font-bold">1</span>
                            Vos coordonnées
                        </h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase" data-i18n="label_prenom">Prénom</label>
                                <input type="text" name="prenom" id="modal-prenom" placeholder="Prénom" data-i18n-placeholder="placeholder_prenom" class="mt-1 block w-full rounded-2xl border-2 border-gray-400 bg-gray-200 focus:bg-white focus:border-[#ffc107] focus:ring-[#ffc107] transition-all py-3 text-gray-800 font-medium text-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase" data-i18n="label_nom">Nom</label>
                                <input type="text" name="nom" id="modal-nom" placeholder="Nom" data-i18n-placeholder="placeholder_nom" class="mt-1 block w-full rounded-2xl border-2 border-gray-400 bg-gray-200 focus:bg-white focus:border-[#ffc107] focus:ring-[#ffc107] transition-all py-3 text-gray-800 font-medium text-lg">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase" data-i18n="label_telephone">Téléphone mobile</label>
                                <input type="tel" name="telephone" id="modal-telephone" placeholder="+33 6 12 34 56 78 (avec code pays)" data-i18n-placeholder="placeholder_telephone" autocomplete="off" class="mt-1 block w-full rounded-2xl border-2 border-gray-400 bg-gray-200 focus:bg-white focus:border-[#ffc107] focus:ring-[#ffc107] transition-all py-3 text-gray-800 font-medium text-lg">
                                <p class="text-xs text-gray-500 mt-1" data-i18n="phone_country_code_hint">⚠️ Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France, +230 pour Maurice)</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase" data-i18n="label_adresse">Adresse</label>
                            <input type="text" name="adresse" id="modal-adresse" maxlength="50" class="mt-1 block w-full rounded-2xl border-2 border-gray-400 bg-gray-200 focus:bg-white focus:border-[#ffc107] focus:ring-[#ffc107] transition-all py-3 text-gray-800 font-medium text-lg">
                            <p class="text-xs text-gray-500 mt-1"><span id="adresse-counter">0</span><span data-i18n="address_counter_suffix">/50 caractères</span></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <button id="toggleAdditionalFieldsBtn" type="button" class="text-sm font-medium text-[#212121] flex items-center" data-i18n="btn_complete_profile">
                                <span id="toggleText">Compléter mon profil (facultatif)</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                        
                        <div id="additional-fields-container" class="hidden mt-6 pt-6 border-t border-gray-300 grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
                            <!-- Nom Société - CONDITIONAL -->
                            <div id="societe-field-container" class="hidden">
                                <label class="block text-xs font-bold text-gray-700 uppercase" data-i18n="label_societe">Nom de la Société</label>
                                <input type="text" name="nomSociete" id="modal-nomSociete" class="mt-1 block w-full rounded-2xl border-2 border-gray-400 bg-gray-200 focus:ring-[#ffc107] text-gray-800 font-medium text-lg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex flex-col md:flex-row gap-4 items-center justify-center">
                    <button id="closeClientProfileModalBtn" type="button" class="order-2 md:order-1 px-8 py-3 text-gray-400 font-bold hover:text-gray-600 transition-all border-2 border-gray-300 rounded-2xl hover:border-gray-400" data-i18n="btn_cancel">
                        Annuler
                    </button>
                    <button id="saveClientProfileBtn" type="submit" class="order-1 md:order-2 px-12 py-4 bg-[#ffc107] text-[#212121] font-bold rounded-2xl shadow-lg hover:bg-[#e6ae02] transform transition-all flex items-center text-lg" data-i18n="btn_confirm_pay">
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