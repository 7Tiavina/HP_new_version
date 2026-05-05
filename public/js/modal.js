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
    // Premium : on l'affiche seulement si l'API le retourne avec un id et un prix
    // ET si la date de dépôt est à plus de 72h de la date actuelle (heure France)
    const hasPremiumFromApi = staticOptions.premium && staticOptions.premium.id && staticOptions.premium.prixUnitaire > 0;
    const hasPriorityFromApi = staticOptions.priority && staticOptions.priority.id && staticOptions.priority.prixUnitaire > 0;
    
    // Vérifier la condition des 72h pour Premium
    let isPremiumTimeValid = false;
    try {
        const dateDepotStr = document.getElementById('date-depot')?.value;
        const heureDepotStr = document.getElementById('heure-depot')?.value;
        
        if (dateDepotStr && heureDepotStr) {
            // Créer la date de dépôt
            const dateDepot = new Date(`${dateDepotStr}T${heureDepotStr}`);
            
            // Obtenir la date actuelle en heure France (UTC+1 ou UTC+2 selon DST)
            const nowFrance = new Date(new Date().toLocaleString('en-US', {timeZone: 'Europe/Paris'}));
            
            // Calculer la différence en heures
            const diffInMs = dateDepot.getTime() - nowFrance.getTime();
            const diffInHours = diffInMs / (1000 * 60 * 60);
            
            // Premium disponible seulement si > 72h
            isPremiumTimeValid = diffInHours > 72;
        }
    } catch (error) {
        console.error('[displayOptions] Error checking 72h condition:', error);
        // En cas d'erreur, on considère que c'est valide pour ne pas bloquer
        isPremiumTimeValid = true;
    }
    
    // Premium n'est affiché que si API OK ET condition des 72h remplie
    isPremiumAvailable = hasPremiumFromApi && isPremiumTimeValid;
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
        // Item does not exist, so add it - check if the other option is already in cart
        const otherOptionKey = optionKey === 'premium' ? 'priority' : 'premium';
        if (cartItems.some(item => item.key === otherOptionKey)) {
            return;
        }
        
        const option = staticOptions[optionKey];
        let premiumDetails = {};

        if (optionKey === 'premium') {
            // Pour premium, on ajoute juste l'option sans demander les infos
            // Les infos seront complétées dans la modale de paiement /payment
            premiumDetails.direction = 'both';
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
        
        // Vérifier que les éléments essentiels existent
        if (!modal) {
            resolve('continued'); // Passer directement
            return;
        }

        // --- NOUVELLE LOGIQUE D'AFFICHAGE ---

        // Masquer les sections par défaut (avec vérification)
        if (prioritySection) prioritySection.classList.add('hidden');
        if (premiumSection) premiumSection.classList.add('hidden');

        // Gérer l'affichage de l'option Priority
        if (staticOptions.priority && staticOptions.priority.id && staticOptions.priority.prixUnitaire > 0) {
            const priorityPriceEl = document.getElementById('advert-priority-price');
            if (priorityPriceEl) {
                priorityPriceEl.textContent = `+${staticOptions.priority.prixUnitaire.toFixed(2)} €`;
            }
            if (prioritySection) prioritySection.classList.remove('hidden');
        }

        // Gérer l'affichage de l'option Premium : afficher seulement si l'API renvoie un prix et un id
        const premiumAvailableContent = document.getElementById('premium-available-content');
        const premiumUnavailableMessage = document.getElementById('premium-unavailable-message');
        const hasPremiumFromApi = staticOptions.premium && staticOptions.premium.id && staticOptions.premium.prixUnitaire > 0;

        if (hasPremiumFromApi) {
            // Afficher premium avec le bon prix récupéré de l'API
            const premiumPriceEl = document.getElementById('advert-premium-price');
            if (premiumPriceEl) {
                premiumPriceEl.textContent = `+${staticOptions.premium.prixUnitaire.toFixed(2)} €`;
            }

            if (premiumAvailableContent) premiumAvailableContent.classList.remove('hidden');
            if (premiumUnavailableMessage) premiumUnavailableMessage.classList.add('hidden');

            // === SUPPRESSION DES CHAMPS PREMIUM DANS LA MODALE D'OPTIONS ===
            // Les champs seront affichés uniquement dans la modale de paiement /payment
            const premiumDetailsContainer = document.getElementById('premium-details-modal');
            if (premiumDetailsContainer) {
                premiumDetailsContainer.innerHTML = `
                    <div class="text-center py-4 text-gray-600">
                        <p class="text-sm">${t('premium_info_later')}</p>
                    </div>
                `;
            }
            
            if (premiumSection) premiumSection.classList.remove('hidden');
        }
        // --- FIN DE LA NOUVELLE LOGIQUE ---

        updateAdvertModalButtons(); // Met à jour l'état des boutons (Ajouter/Enlever)

        let optionsModalEscHandler;
        const closeModalAndResolve = (resolutionValue = 'continued') => {
            if (modal) modal.classList.add('hidden');
            if (typeof optionsModalEscHandler === 'function') {
                document.removeEventListener('keydown', optionsModalEscHandler);
            }
            if (continueBtn) continueBtn.onclick = null;
            if (closeBtn) closeBtn.onclick = null;
            if (modal) modal.onclick = null;
            if (addPriorityBtn) addPriorityBtn.onclick = null;
            if (addPremiumBtn) addPremiumBtn.onclick = null;
            resolve(resolutionValue);
        };

        optionsModalEscHandler = (e) => {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', optionsModalEscHandler);
                closeModalAndResolve('cancelled');
            }
        };
        document.addEventListener('keydown', optionsModalEscHandler);

        if (addPriorityBtn) addPriorityBtn.onclick = () => toggleOptionFromModal('priority');
        if (addPremiumBtn) addPremiumBtn.onclick = () => {
            // Appeler toggleOptionFromModal qui gère la validation et l'ajout au panier
            toggleOptionFromModal('premium');
        };
        if (continueBtn) continueBtn.onclick = () => closeModalAndResolve('continued');
        if (closeBtn) closeBtn.onclick = () => closeModalAndResolve('cancelled');
        if (modal) {
            modal.onclick = (e) => {
                if (e.target === modal) {
                    closeModalAndResolve('cancelled');
                }
            };
        }

        if (modal) modal.classList.remove('hidden');
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