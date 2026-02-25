// =================================================================================
// == Fichier: public/js/modal.js
// == Description: Gère la logique pour toutes les modales du site.
// =================================================================================

let modalResolve;
let wasQuickDateModalOpen = false;
// Use global t function from translations-simple.js (with fallback for safety)
if (typeof t === 'undefined') {
    var t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));
}

// =================================================================================
// == Fonctions génériques pour les modales (Alert, Confirm, Prompt)
// =================================================================================

/**
 * Cache la modale de date rapide si elle est ouverte, pour éviter les superpositions.
 */
function hideQuickDateModalIfOpen() {
    const quickDateModal = document.getElementById('quick-date-modal');
    if (quickDateModal && !quickDateModal.classList.contains('hidden')) {
        wasQuickDateModalOpen = true;
        quickDateModal.classList.add('hidden');
    } else {
        wasQuickDateModalOpen = false;
    }
}

/**
 * Affiche une modale d'alerte simple avec un titre, un message et un bouton "OK".
 * @param {string} title - Le titre de la modale.
 * @param {string} message - Le message (peut contenir du HTML).
 * @returns {Promise<boolean>} - Une promesse qui se résout quand la modale est fermée.
 */
function showCustomAlert(title, message) {
    hideQuickDateModalIfOpen();
    const modalOverlay = document.getElementById('custom-modal-overlay');
    if (!modalOverlay) return Promise.resolve(false);

    document.getElementById('custom-modal-title').textContent = title;
    document.getElementById('custom-modal-message').innerHTML = message;
    document.getElementById('custom-modal-prompt-container').classList.add('hidden');
    
    const footer = document.getElementById('custom-modal-footer');
    footer.innerHTML = `<button id="custom-modal-confirm-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">${t('modal_ok')}</button>`;

    modalOverlay.classList.remove('hidden');

    return new Promise(resolve => {
        modalResolve = resolve;
        document.getElementById('custom-modal-confirm-btn').onclick = () => { 
            closeModal(); 
            modalResolve(true);
        };
    });
}

/**
 * Affiche une modale de confirmation avec un titre, un message et des boutons "Confirmer" et "Annuler".
 * @param {string} title - Le titre de la modale.
 * @param {string} message - Le message de confirmation.
 * @returns {Promise<boolean>} - Une promesse qui se résout à `true` si confirmé, `false` sinon.
 */
function showCustomConfirm(title, message) {
    hideQuickDateModalIfOpen();
    const modalOverlay = document.getElementById('custom-modal-overlay');
    if (!modalOverlay) return Promise.resolve(false);

    document.getElementById('custom-modal-title').textContent = title;
    document.getElementById('custom-modal-message').textContent = message;
    document.getElementById('custom-modal-prompt-container').classList.add('hidden');

    const footer = document.getElementById('custom-modal-footer');
    footer.innerHTML = `
        <button id="modal-btn-cancel-confirm" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-full btn-hover">${t('modal_cancel')}</button>
        <button id="modal-btn-confirm-confirm" class="bg-red-600 text-white font-bold py-2 px-4 rounded-full btn-hover">${t('modal_confirm')}</button>
    `;
    
    modalOverlay.classList.remove('hidden');

    return new Promise(resolve => {
        modalResolve = resolve;
        document.getElementById('modal-btn-confirm-confirm').onclick = () => { closeModal(); modalResolve(true); };
        document.getElementById('modal-btn-cancel-confirm').onclick = () => { closeModal(); modalResolve(false); };
    });
}

/**
 * Affiche une modale avec un champ de saisie.
 * @param {string} title - Le titre de la modale.
 * @param {string} message - Le message d'instruction.
 * @param {string} label - Le label pour le champ de saisie.
 * @returns {Promise<string|null>} - Une promesse qui se résout avec la valeur saisie, ou null si annulé.
 */
function showCustomPrompt(title, message, label) {
    hideQuickDateModalIfOpen();
    const modalOverlay = document.getElementById('custom-modal-overlay');
    if (!modalOverlay) return Promise.resolve(null);
    
    document.getElementById('custom-modal-title').textContent = title;
    document.getElementById('custom-modal-message').textContent = message;
    
    const promptContainer = document.getElementById('custom-modal-prompt-container');
    promptContainer.classList.remove('hidden');
    document.getElementById('custom-modal-prompt-label').textContent = label;
    document.getElementById('custom-modal-input').value = '';
    document.getElementById('custom-modal-error').classList.add('hidden');

    const footer = document.getElementById('custom-modal-footer');
    footer.innerHTML = `
        <button id="custom-modal-cancel-btn" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-full btn-hover">${t('modal_cancel')}</button>
        <button id="custom-modal-confirm-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">${t('modal_confirm')}</button>
    `;

    modalOverlay.classList.remove('hidden');

    return new Promise(resolve => {
        modalResolve = resolve;
        const confirmBtn = document.getElementById('custom-modal-confirm-btn');
        const cancelBtn = document.getElementById('custom-modal-cancel-btn');
        const input = document.getElementById('custom-modal-input');

        confirmBtn.onclick = () => {
            const value = input.value;
            if (label.toLowerCase().includes('email') && (value.trim() === '' || !/^\S+@\S+\.\S+$/.test(value))) {
                document.getElementById('custom-modal-error').textContent = t('prompt_invalid_email');
                document.getElementById('custom-modal-error').classList.remove('hidden');
                return;
            }
            closeModal(); 
            modalResolve(value);
        };
        cancelBtn.onclick = () => { closeModal(); modalResolve(null); };
    });
}


/**
 * Affiche une modale pour choisir entre la connexion et continuer en tant qu'invité.
 * @returns {Promise<'login'|'guest'|null>}
 */
function showLoginOrGuestPrompt() {
    hideQuickDateModalIfOpen();
    const modalOverlay = document.getElementById('custom-modal-overlay');
    if (!modalOverlay) return Promise.resolve(null);

    document.getElementById('custom-modal-title').textContent = t('login_guest_title');
    document.getElementById('custom-modal-message').textContent = t('login_guest_message');
    document.getElementById('custom-modal-prompt-container').classList.add('hidden');
    
    const footer = document.getElementById('custom-modal-footer');
    footer.innerHTML = `
        <button id="btn-continue-guest" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-full btn-hover">${t('login_guest_continue')}</button>
        <button id="btn-login-modal" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">${t('login_guest_login')}</button>
    `;
    modalOverlay.classList.remove('hidden');

    return new Promise(resolve => {
        modalResolve = resolve;
        document.getElementById('btn-login-modal').onclick = () => { closeModal(); resolve('login'); };
        document.getElementById('btn-continue-guest').onclick = () => { closeModal(); resolve('guest'); };
    });
}

/**
 * Ferme la modale principale et nettoie l'état.
 */
function closeModal() {
    const modalOverlay = document.getElementById('custom-modal-overlay');
    if(modalOverlay) {
        modalOverlay.classList.add('hidden');
    }
    
    // Réinitialise le footer à son état par défaut pour les simples alertes
    const footer = document.getElementById('custom-modal-footer');
    if (footer) {
        footer.innerHTML = `<button id="custom-modal-confirm-btn" class="bg-yellow-custom text-gray-dark font-bold py-2 px-4 rounded-full btn-hover">${t('modal_ok')}</button>`;
    }

    if (wasQuickDateModalOpen) {
        const quickDateModal = document.getElementById('quick-date-modal');
        if(quickDateModal) {
            quickDateModal.classList.remove('hidden');
        }
        wasQuickDateModalOpen = false;
    }
}

/**
 * Initialise les écouteurs d'événements globaux pour la modale principale.
 */
function setupGlobalModalListeners() {
    const modalOverlay = document.getElementById('custom-modal-overlay');
    const modalCloseBtn = document.getElementById('custom-modal-close');

    if (modalOverlay) {
        // Clic sur le fond pour fermer
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal();
                if (modalResolve) modalResolve(null); // Annulation
            }
        });
    }

    if (modalCloseBtn) {
        // Clic sur le bouton de fermeture (croix)
        modalCloseBtn.addEventListener('click', () => {
            closeModal();
            if (modalResolve) modalResolve(null); // Annulation
        });
    }

    // Fermer la modale principale avec la touche Escape
    document.addEventListener('keydown', function onGlobalEscape(e) {
        if (e.key !== 'Escape') return;
        const overlay = document.getElementById('custom-modal-overlay');
        if (overlay && !overlay.classList.contains('hidden')) {
            closeModal();
            if (modalResolve) modalResolve(null);
        }
    });
}

// =================================================================================
// == Fonctions spécifiques à la modale de publicité des options (Priority/Premium)
// =================================================================================

function displayOptions(dureeEnMinutes) {
    // Premium : on l'affiche dès que l'API le propose ET qu'il y a des lieux (72 h reste une recommandation, pas un blocage d'affichage)
    const dateDepot = (document.getElementById('date-depot') || {}).value || '';
    const heureDepot = (document.getElementById('heure-depot') || {}).value || '';
    const depotDateTime = new Date(`${dateDepot}T${heureDepot}`);
    const now = new Date();

    const validDate = dateDepot && heureDepot && !isNaN(depotDateTime.getTime());
    const diffInMs = validDate ? depotDateTime - now : 0;
    const diffInHours = diffInMs / (1000 * 60 * 60);
    const isDepotInFuture = diffInHours >= 72;
    const hasLieux = (typeof globalLieuxData !== 'undefined' && globalLieuxData) ? globalLieuxData.length > 0 : false;

    // Afficher l'option Premium si lieux disponibles (sans exiger 72 h pour l'affichage)
    isPremiumAvailable = hasLieux;
}

function updateAdvertModalButtons() {
    ['priority', 'premium'].forEach(optionKey => {
        const addButton = document.getElementById(`add-${optionKey}-from-modal`);
        if (!addButton) return;

        const isInCart = cartItems.some(item => item.key === optionKey);
        addButton.disabled = false; // Always enable the button for toggling

        if (isInCart) {
            addButton.textContent = t('modal_remove_cart', 'Enlever du panier');
            addButton.classList.remove('bg-transparent', 'border', 'border-gray-400', 'text-gray-700', 'hover:bg-gray-100');
            addButton.classList.add('bg-red-600', 'text-white');
        } else {
            addButton.textContent = t('modal_add_cart', 'Ajouter au panier');
            addButton.classList.remove('bg-red-600', 'text-white');
            addButton.classList.add('bg-transparent', 'border', 'border-gray-400', 'text-gray-700', 'hover:bg-gray-100');
        }
    });
    
    // NOUVELLE PARTIE : Mise à jour du bouton "Continuer"
    const continueBtn = document.getElementById('continue-from-options-modal');
    if (continueBtn) {
        const hasOptionsInCart = cartItems.some(item => 
            item.itemCategory === 'option' && (item.key === 'priority' || item.key === 'premium')
        );
        
        if (hasOptionsInCart) {
            continueBtn.textContent = t('modal_validate_continue');
            continueBtn.classList.remove('bg-gray-200', 'text-gray-700');
            continueBtn.classList.add('bg-yellow-custom', 'text-gray-dark');
        } else {
            continueBtn.textContent = t('modal_continue_no_thanks');
            // MODIFICATION ICI : Mettre en jaune au lieu de gris
            continueBtn.classList.remove('bg-gray-200', 'text-gray-700');
            continueBtn.classList.add('bg-yellow-custom', 'text-gray-dark');
        }
    }
}

function toggleOptionFromModal(optionKey) {
    const itemIndex = cartItems.findIndex(item => item.key === optionKey);

    if (itemIndex > -1) {
        // Item exists, so remove it
        cartItems.splice(itemIndex, 1);
    } else {
        // Item does not exist, so add it
        const option = staticOptions[optionKey];
        let premiumDetails = {};

        if (optionKey === 'premium') {
            // Afficher les sections si pas encore visibles
            const premiumMessage = document.getElementById('premium-message-container');
            const premiumInfo = document.getElementById('premium-required-fields-info');
            const arrivalSection = document.getElementById('premium_fields_terminal_to_agence');
            const departureSection = document.getElementById('premium_fields_agence_to_terminal');
            const emptyState = document.getElementById('premium-empty-state');
            
            if (premiumMessage.classList.contains('hidden')) {
                // Sections pas encore visibles, les afficher et faire défiler
                premiumMessage.classList.remove('hidden');
                premiumInfo.classList.remove('hidden');
                arrivalSection.classList.remove('hidden');
                departureSection.classList.remove('hidden');
                emptyState.classList.add('hidden');
                
                document.getElementById('premium-details-modal').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return; // Ne pas ajouter au panier tout de suite
            }
            
            // Validation pour les DEUX sections (arrivée ET départ)
            const arrivalForm = document.getElementById('premium_fields_terminal_to_agence');
            const departureForm = document.getElementById('premium_fields_agence_to_terminal');
            
            let isValid = true;
            const missingFields = [];

            // Fonction pour valider une section
            const validateSection = (formContainer, sectionName, direction) => {
                let sectionValid = true;
                
                // Reset borders first
                formContainer.querySelectorAll('[data-required="true"]').forEach(input => {
                    input.classList.remove('border-red-500');
                });
                
                // Check required fields - only those that are currently visible
                formContainer.querySelectorAll('[data-required="true"]').forEach(input => {
                    // Check if field is visible (not hidden by CSS)
                    const style = window.getComputedStyle(input);
                    const isVisible = style.display !== 'none' && style.visibility !== 'hidden';
                    
                    // Also check parent containers aren't hidden
                    let parent = input.parentElement;
                    let parentVisible = true;
                    while (parent && parent !== formContainer) {
                        const parentStyle = window.getComputedStyle(parent);
                        if (parentStyle.display === 'none' || parentStyle.visibility === 'hidden') {
                            parentVisible = false;
                            break;
                        }
                        parent = parent.parentElement;
                    }
                    
                    if (parentVisible && !input.value.trim()) {
                        sectionValid = false;
                        isValid = false;
                        input.classList.add('border-red-500');
                    }
                });
                
                // Additional validation for transport-specific fields if visible
                const dir = direction.toLowerCase();
                // Validate airport flight number field when airport is selected
                const airportContainerEl = document.getElementById(`transport_details_${dir}_airport`);
                if (airportContainerEl && window.getComputedStyle(airportContainerEl).display !== 'none') {
                    const fieldEl = formContainer.querySelector(`input[name="flight_number_${dir}"]`);
                    if (fieldEl && !fieldEl.value.trim()) {
                        sectionValid = false;
                        isValid = false;
                        fieldEl.classList.add('border-red-500');
                    }
                }
                // Validate train number field when train is selected
                const trainContainerEl = document.getElementById(`transport_details_${dir}_train`);
                if (trainContainerEl && window.getComputedStyle(trainContainerEl).display !== 'none') {
                    const fieldEl = formContainer.querySelector(`input[name="train_number_${dir}"]`);
                    if (fieldEl && !fieldEl.value.trim()) {
                        sectionValid = false;
                        isValid = false;
                        fieldEl.classList.add('border-red-500');
                    }
                }
                
                if (!sectionValid) {
                    missingFields.push(sectionName);
                }
                
                return sectionValid;
            };

            // Valider les DEUX sections
            validateSection(arrivalForm, t('premium_section_arrival'), 'arrival');
            validateSection(departureForm, t('premium_section_departure'), 'departure');

            if (!isValid) {
                const message = missingFields.length === 2 
                    ? t('premium_required_both')
                    : t('premium_required_section').replace('{section}', missingFields.join(', '));
                showCustomAlert(t('premium_form_incomplete_title'), message);
                return;
            }

            // Collecter les données des DEUX sections
            premiumDetails.direction = 'both'; // Indiquer que c'est un service complet
            
            [arrivalForm, departureForm].forEach(formContainer => {
                formContainer.querySelectorAll('input, textarea, select').forEach(input => {
                    if (input.value) {
                        premiumDetails[input.name] = input.value;
                        // Pour les selects, ajouter aussi le libellé
                        if (input.tagName === 'SELECT' && (input.name === 'pickup_location_arrival' || input.name === 'restitution_location_departure')) {
                            const selectedOption = input.options[input.selectedIndex];
                            if (selectedOption) {
                                premiumDetails[input.name + '_libelle'] = selectedOption.text;
                            }
                        }
                    }
                });
            });
        }

        cartItems.push({
            itemCategory: 'option',
            id: option.id,
            key: optionKey,
            libelle: option.libelle,
            prix: option.prixUnitaire,
            details: premiumDetails
        });
    }
    
    updateCartDisplay();
    updateAdvertModalButtons();
}

function showOptionsAdvertisementModal() {
    return new Promise(resolve => {
        const modal = document.getElementById('options-advert-modal');
        const closeBtn = document.getElementById('close-options-advert-modal');
        const addPriorityBtn = document.getElementById('add-priority-from-modal');
        const addPremiumBtn = document.getElementById('add-premium-from-modal');
        const continueBtn = document.getElementById('continue-from-options-modal');

        const prioritySection = document.getElementById('advert-option-priority');
        const premiumSection = document.getElementById('advert-option-premium');
        
        // --- NOUVELLE LOGIQUE D'AFFICHAGE ---
        
        // Masquer les sections par défaut
        prioritySection.classList.add('hidden');
        premiumSection.classList.add('hidden');

        // Gérer l'affichage de l'option Priority
        if (staticOptions.priority && staticOptions.priority.id && staticOptions.priority.prixUnitaire > 0) {
            const priorityPriceEl = document.getElementById('advert-priority-price');
            priorityPriceEl.textContent = `+${staticOptions.priority.prixUnitaire.toFixed(2)} €`;
            prioritySection.classList.remove('hidden');
        }

        // Gérer l'affichage de l'option Premium : afficher la carte dès que l'API renvoie un prix
        const premiumAvailableContent = document.getElementById('premium-available-content');
        const premiumUnavailableMessage = document.getElementById('premium-unavailable-message');
        const hasPremiumFromApi = staticOptions.premium && staticOptions.premium.id && staticOptions.premium.prixUnitaire > 0;

        if (hasPremiumFromApi) {
            if (isPremiumAvailable) {
                // Lieux disponibles : afficher le formulaire et le bouton Ajouter
                const premiumPriceEl = document.getElementById('advert-premium-price');
                premiumPriceEl.textContent = `+${staticOptions.premium.prixUnitaire.toFixed(2)} €`;

                premiumAvailableContent.classList.remove('hidden');
                premiumUnavailableMessage.classList.add('hidden');

                const premiumDetailsContainer = document.getElementById('premium-details-modal');
                const lieux = (typeof globalLieuxData !== 'undefined' && Array.isArray(globalLieuxData)) ? globalLieuxData : [];
                const lieuxOptionsHTML = lieux.map(lieu => {
                    var id = lieu.id ?? lieu.Id ?? lieu.ID;
                    var libelle = lieu.libelle ?? lieu.Libelle ?? lieu.nom ?? '';
                    return `<option value="${id || ''}">${libelle || ''}</option>`;
                }).join('');

                const orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
                const cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
                const isOrly = airportId === orlyAirportId;
                const isCdg = airportId === cdgAirportId;

 
                const transportSpecificFields = (direction) => {
                    const dir = direction.toLowerCase(); // 'arrival' or 'departure'
                    return `
                        <div id="transport_details_${dir}_airport" class="hidden mt-2">
                            <label class="block text-sm font-medium text-gray-700">${t('premium_flight_number')} <span class="text-red-500">*</span></label>
                            <input type="text" name="flight_number_${dir}" class="input-style w-full" placeholder="${t('premium_flight_placeholder')}" data-required="true">
                        </div>
                        <div id="transport_details_${dir}_train" class="hidden mt-2">
                            <label class="block text-sm font-medium text-gray-700">${t('premium_train_number')} <span class="text-red-500">*</span></label>
                            <input type="text" name="train_number_${dir}" class="input-style w-full" placeholder="${t('premium_train_placeholder')}" data-required="true">
                        </div>
                    `;
                };

                premiumDetailsContainer.innerHTML = `
                <div id="premium-message-container" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                    <p class="text-sm text-blue-700"><strong>${t('premium_important')}:</strong> ${t('premium_both_required')}</p>
                </div>
                <p id="premium-required-fields-info" class="hidden font-medium text-gray-700 text-center mb-4">${t('premium_required_fields_info')} <span class="text-red-500">*</span></p>
                <div id="premium_fields_terminal_to_agence" class="hidden mt-4 space-y-3 border-2 border-yellow-200 rounded-lg p-4 bg-yellow-50">
                    <div class="flex items-center mb-3">
                        <span class="text-3xl mr-3">
                            <img src="/plane-arrival.svg" alt="${t('premium_arrival_alt')}" class="h-8 w-8 inline-block" />
                        </span>
                        <div>
                            <h4 class="font-bold text-gray-900 text-lg">${t('premium_arrival_title')} <span class="text-red-500">*</span></h4>
                            <p class="text-sm text-gray-600">${t('premium_arrival_subtitle')}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">${t('premium_transport_label')} <span class="text-red-500">*</span></label>
                        <select name="transport_type_arrival" class="input-style custom-select w-full" data-required="true">
                            <option value="" selected disabled>${t('premium_select_placeholder')}</option>
                            <option value="airport">${t('premium_transport_airport')}</option>
                            <option value="public_transport">${t('premium_transport_public')}</option>
                            <option value="train">${t('premium_transport_train')}</option>
                            <option value="other">${t('premium_transport_other')}</option>
                        </select>
                    </div>
                    ${transportSpecificFields('arrival')}
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-gray-700">${t('premium_arrival_date')}</label><input type="date" id="flight_date_arrival" name="date_arrival" class="input-style w-full"></div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700">${t('premium_pickup_location')} <span class="text-red-500">*</span></label>
                             <select name="pickup_location_arrival" class="input-style custom-select w-full" data-required="true"><option value="" selected disabled>${t('premium_select_placeholder')}</option>${lieuxOptionsHTML}</select>
                         </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">${t('premium_pickup_time')} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="time" id="pickup_time_arrival" name="pickup_time_arrival" class="input-style w-full pr-10 pl-4" data-required="true">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">${t('premium_additional_info')} <span class="text-gray-400 text-xs">(${t('premium_optional')})</span></label><textarea name="instructions_arrival" class="input-style w-full" rows="2" placeholder="${t('premium_arrival_placeholder')}"></textarea></div>
                </div>
                <div id="premium_fields_agence_to_terminal" class="hidden mt-4 space-y-3 border-2 border-blue-200 rounded-lg p-4 bg-blue-50">
                    <div class="flex items-center mb-3">
                        <span class="text-3xl mr-3">
                            <img src="/plane-departure.svg" alt="${t('premium_departure_alt')}" class="h-8 w-8 inline-block" />
                        </span>
                        <div>
                            <h4 class="font-bold text-gray-900 text-lg">${t('premium_departure_title')} <span class="text-red-500">*</span></h4>
                            <p class="text-sm text-gray-600">${t('premium_departure_subtitle')}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">${t('premium_transport_label')} <span class="text-red-500">*</span></label>
                        <select name="transport_type_departure" class="input-style custom-select w-full" data-required="true">
                            <option value="" selected disabled>${t('premium_select_placeholder')}</option>
                            <option value="airport">${t('premium_transport_airport')}</option>
                            <option value="public_transport">${t('premium_transport_public')}</option>
                            <option value="train">${t('premium_transport_train')}</option>
                            <option value="other">${t('premium_transport_other')}</option>
                        </select>
                    </div>
                    ${transportSpecificFields('departure')}
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-gray-700">${t('premium_departure_date')}</label><input type="date" id="flight_date_departure" name="date_departure" class="input-style w-full"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">${t('premium_restitution_location')} <span class="text-red-500">*</span></label>
                            <select name="restitution_location_departure" class="input-style custom-select w-full" data-required="true"><option value="" selected disabled>${t('premium_select_placeholder')}</option>${lieuxOptionsHTML}</select>
                        </div>
                    </div>
                     <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">${t('premium_restitution_time')} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="time" id="restitution_time_departure" name="restitution_time_departure" class="input-style w-full pr-10 pl-4" data-required="true">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">${t('premium_additional_info')} <span class="text-gray-400 text-xs">(${t('premium_optional')})</span></label><textarea name="instructions_departure" class="input-style w-full" rows="2" placeholder="${t('premium_departure_placeholder')}"></textarea></div>
                </div>
                <div id="premium-empty-state" class="text-center py-8 text-gray-500">
                    <p>${t('premium_empty_state')}</p>
                </div>`;
                
                // --- START DYNAMIC PREMIUM LOGIC ---
                
                // Get elements
                const flightDateArrival = document.getElementById('flight_date_arrival');
                const pickupTimeArrival = document.getElementById('pickup_time_arrival');
                const flightDateDeparture = document.getElementById('flight_date_departure');
                const restitutionTimeDeparture = document.getElementById('restitution_time_departure');

                // Pre-fill dates from main form
                flightDateArrival.value = document.getElementById('date-depot').value;
                pickupTimeArrival.value = document.getElementById('heure-depot').value;
                flightDateDeparture.value = document.getElementById('date-recuperation').value;
                restitutionTimeDeparture.value = document.getElementById('heure-recuperation').value;

                // Initialiser Flatpickr sur les champs de temps du modal Premium (allowInput pour saisie clavier)
                if (typeof flatpickr !== 'undefined') {
                    flatpickr("#pickup_time_arrival", {
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

                    flatpickr("#restitution_time_departure", {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        minuteIncrement: 15,
                        locale: "fr",
                        defaultHour: 18,
                        defaultMinute: 0,
                        allowInput: true
                    });
                }

                // Transport type change handler
                const setupTransportTypeHandler = (direction) => {
                    const transportSelect = document.querySelector(`select[name="transport_type_${direction}"]`);
                    const airportDetailsContainer = document.getElementById(`transport_details_${direction}_airport`);
                    const trainDetailsContainer = document.getElementById(`transport_details_${direction}_train`);

                    transportSelect.addEventListener('change', (e) => {
                        // Hide all transport-specific fields by default
                        if (airportDetailsContainer) airportDetailsContainer.classList.add('hidden');
                        if (trainDetailsContainer) trainDetailsContainer.classList.add('hidden');
                        
                        // Show specific field based on selection
                        const selectedType = e.target.value;
                        if (selectedType === 'airport' && airportDetailsContainer) {
                            airportDetailsContainer.classList.remove('hidden');
                        } else if (selectedType === 'train' && trainDetailsContainer) {
                            trainDetailsContainer.classList.remove('hidden');
                        }
                        // public_transport and other don't need number fields
                    });
                };

                setupTransportTypeHandler('arrival');
                setupTransportTypeHandler('departure');

                // --- END DYNAMIC PREMIUM LOGIC ---

                // Plus besoin de basculer les sections - elles sont toujours visibles maintenant
                // L'utilisateur DOIT remplir les deux sections
            } else {
                // API renvoie Premium mais pas de lieux pour cet aéroport : afficher la carte avec message
                premiumAvailableContent.classList.add('hidden');
                premiumUnavailableMessage.classList.remove('hidden');
                premiumUnavailableMessage.innerHTML = `<p class="text-lg font-semibold text-gray-600">${t('modal_premium_unavailable')}</p><p class="text-sm text-gray-500 mt-2">${t('modal_premium_unavailable_reason')}</p>`;
            }
            premiumSection.classList.remove('hidden');
        }
        // --- FIN DE LA NOUVELLE LOGIQUE ---
        
        updateAdvertModalButtons(); // Met à jour l'état des boutons (Ajouter/Enlever)

        let optionsModalEscHandler;
        const closeModalAndResolve = (resolutionValue = 'continued') => {
            modal.classList.add('hidden');
            if (typeof optionsModalEscHandler === 'function') {
                document.removeEventListener('keydown', optionsModalEscHandler);
            }
            continueBtn.onclick = null;
            closeBtn.onclick = null;
            modal.onclick = null;
            addPriorityBtn.onclick = null;
            addPremiumBtn.onclick = null;
            resolve(resolutionValue);
        };

        optionsModalEscHandler = (e) => {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', optionsModalEscHandler);
                closeModalAndResolve('cancelled');
            }
        };
        document.addEventListener('keydown', optionsModalEscHandler);

        addPriorityBtn.onclick = () => toggleOptionFromModal('priority');
        addPremiumBtn.onclick = () => {
            // Appeler toggleOptionFromModal qui gère la validation et l'ajout au panier
            toggleOptionFromModal('premium');
        };
        continueBtn.onclick = () => closeModalAndResolve('continued');
        closeBtn.onclick = () => closeModalAndResolve('cancelled');
        modal.onclick = (e) => {
            if (e.target === modal) {
                closeModalAndResolve('cancelled');
            }
        };
        
        modal.classList.remove('hidden');
    });
}


// Initialise les écouteurs dès que le DOM est prêt.
document.addEventListener('DOMContentLoaded', setupGlobalModalListeners);

// Exposer les fonctions au scope global pour qu'elles soient accessibles
// par les scripts inline dans les fichiers Blade.
window.showCustomAlert = showCustomAlert;
window.showCustomConfirm = showCustomConfirm;
window.showCustomPrompt = showCustomPrompt;
window.showLoginOrGuestPrompt = showLoginOrGuestPrompt;
window.closeModal = closeModal;