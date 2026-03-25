// Fichier: public/js/booking.js

// Définitions spécifiques au module de booking
// Use global t function from translations-simple.js (with fallback for safety)
if (typeof t === 'undefined') {
    var t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));
}
const getLang = () => (window.getCurrentLanguage ? window.getCurrentLanguage() : (localStorage.getItem('app_language') || 'fr'));

// Routes (injected by Blade when available). Fallbacks keep local dev working.
const ROUTES = (window.APP_ROUTES || {});
const routeUrl = (key, fallback) => ROUTES[key] || fallback;

/**
 * Alignement dynamique du panier avec la section Aéroport sélectionné
 */
function alignStickyWithBaggage() {
    const baggageStep = document.getElementById('baggage-selection-step');
    const stickyWrapper = document.getElementById('sticky-wrapper');
    
    if (!baggageStep || !stickyWrapper || stickyWrapper.style.display === 'none') return;
    
    const baggageTop = baggageStep.getBoundingClientRect().top;
    const gridTop = stickyWrapper.parentElement.getBoundingClientRect().top;
    const offset = baggageTop - gridTop;
    
    stickyWrapper.style.paddingTop = offset + 'px';
    console.log('Panier aligné avec offset:', offset + 'px');
}

const productMapJs = {
    'Accessoires': { 
        type: 'accessory', 
        description: () => t('luggage_accessoires_desc', 'Moins de 3 kg. Petits objets : sac à main, sac d’ordinateur, caméra.') 
    },
    'Bagage cabine': { 
        type: 'cabin', 
        description: () => t('luggage_bagage_cabine_desc', 'Moins de 9 kg. Dimensions max : 55 x 35 x 25 cm.') 
    },
    'Bagage soute': { 
        type: 'hold', 
        description: () => t('luggage_bagage_soute_desc', 'Moins de 30 kg. Somme des dimensions (L+l+h) inférieure à 158 cm.') 
    },
    'Bagage spécial': {
        type: 'special',
        description: () => t('luggage_bagage_special_desc', 'Poids max autorisé pour un bagage spécial = 32 kg unitaire (loi).')
    },
    'Vestiaire': { 
        type: 'cloakroom', 
        description: () => t('luggage_vestiaire_desc', 'Pour les manteaux, vestes ou autres vêtements sur cintre.') 
    }
};

let isPriorityAvailable = false;
let isPremiumAvailable = false;

/**
 * ============================================
 * FORM VALIDATION STATE MANAGEMENT
 * ============================================
 */

/**
 * Set input field to default state (gray)
 * @param {HTMLElement} input - The input element
 */
function setInputDefault(input) {
    if (!input) return;
    input.classList.remove('input-filled', 'input-error');
    input.classList.add('input-default');
    clearFieldError(input);
}

/**
 * Set input field to filled state (yellow)
 * @param {HTMLElement} input - The input element
 */
function setInputFilled(input) {
    if (!input) return;
    input.classList.remove('input-default', 'input-error');
    input.classList.add('input-filled');
    clearFieldError(input);
}

/**
 * Set input field to error state (red)
 * @param {HTMLElement} input - The input element
 * @param {string} errorMessage - Optional error message to display
 */
function setInputError(input, errorMessage = '') {
    if (!input) return;
    input.classList.remove('input-default', 'input-filled');
    input.classList.add('input-error');
    if (errorMessage) {
        showFieldError(input, errorMessage);
    }
}

/**
 * Show error message for a field
 * @param {HTMLElement} input - The input element
 * @param {string} message - Error message to display
 */
function showFieldError(input, message) {
    if (!input) return;
    
    // Remove existing error message if present
    clearFieldError(input);
    
    // Create error message element
    const errorEl = document.createElement('p');
    errorEl.className = 'error-message';
    errorEl.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        ${message}
    `;
    
    // Insert error after the input - handle different structures
    const parent = input.parentElement;
    if (parent) {
        // For inputs inside .relative div (time/date inputs)
        if (parent.classList.contains('relative')) {
            const grandParent = parent.parentElement;
            if (grandParent) {
                grandParent.appendChild(errorEl);
            }
        } else {
            // For select (airport) - insert directly after
            parent.appendChild(errorEl);
        }
    }
    
    // Mark label as error if exists
    const label = document.querySelector(`label[for="${input.id}"]`);
    if (label) {
        label.classList.add('label-error');
    }
}

/**
 * Clear error message for a field
 * @param {HTMLElement} input - The input element
 */
function clearFieldError(input) {
    if (!input) return;
    
    // Remove error message - handle different structures
    const parent = input.parentElement;
    if (parent) {
        // For inputs inside .relative div
        if (parent.classList.contains('relative')) {
            const grandParent = parent.parentElement;
            if (grandParent) {
                const errorEl = grandParent.querySelector('.error-message');
                if (errorEl) errorEl.remove();
            }
        } else {
            const errorEl = parent.querySelector('.error-message');
            if (errorEl) errorEl.remove();
        }
    }
    
    // Remove label error state
    const label = document.querySelector(`label[for="${input.id}"]`);
    if (label) {
        label.classList.remove('label-error', 'label-filled');
    }
}

/**
 * Validate a single field and update its state
 * @param {HTMLElement} input - The input element
 * @returns {boolean} - True if valid, false otherwise
 */
function validateField(input) {
    if (!input) return false;
    
    const value = input.value.trim();
    const id = input.id;
    
    // Check if field is required
    const isRequired = input.hasAttribute('required') || 
                       input.closest('.form-required') !== null ||
                       id.includes('airport') || 
                       id.includes('date') || 
                       id.includes('heure');
    
    if (isRequired && !value) {
        const fieldName = getFieldName(input);
        setInputError(input, t('field_required_error', `${fieldName} est requis`));
        return false;
    }
    
    // Field-specific validation
    if (value) {
        switch(id) {
            case 'airport-select':
                if (value === '' || value === '0') {
                    setInputError(input, t('alert_select_airport', 'Veuillez sélectionner un aéroport'));
                    return false;
                }
                break;
                
            case 'date-depot':
            case 'date-recuperation':
                if (!isValidDate(value)) {
                    setInputError(input, t('invalid_date', 'Date invalide'));
                    return false;
                }
                break;
                
            case 'heure-depot':
            case 'heure-recuperation':
                if (!isValidTime(value)) {
                    setInputError(input, t('invalid_time', 'Heure invalide'));
                    return false;
                }
                break;
        }
        
        // Field is valid and has value - set to FILLED (yellow)
        setInputFilled(input);
        return true;
    }
    
    // Empty but not required - set to DEFAULT (gray)
    setInputDefault(input);
    return true;
}

/**
 * Get human-readable field name for error messages
 * @param {HTMLElement} input - The input element
 * @returns {string} - Field name
 */
function getFieldName(input) {
    const label = document.querySelector(`label[for="${input.id}"]`);
    if (label) {
        return label.textContent.replace('*', '').trim();
    }
    
    const nameMap = {
        'airport-select': t('form_airport_label', 'Aéroport'),
        'date-depot': t('form_deposit_date', 'Date de dépôt'),
        'date-recuperation': t('form_pickup_date', 'Date de récupération'),
        'heure-depot': t('form_deposit_time', 'Heure de dépôt'),
        'heure-recuperation': t('form_pickup_time', 'Heure de récupération')
    };
    
    return nameMap[input.id] || t('field', 'Champ');
}

/**
 * Validate date format (YYYY-MM-DD)
 * @param {string} dateStr - Date string to validate
 * @returns {boolean} - True if valid
 */
function isValidDate(dateStr) {
    if (!dateStr) return false;
    const date = new Date(dateStr);
    return date instanceof Date && !isNaN(date);
}

/**
 * Validate time format (HH:mm)
 * @param {string} timeStr - Time string to validate
 * @returns {boolean} - True if valid
 */
function isValidTime(timeStr) {
    if (!timeStr) return false;
    const timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;
    return timeRegex.test(timeStr);
}

/**
 * Validate all Step 1 fields (airport + dates)
 * @returns {boolean} - True if all fields are valid
 */
function validateStep1() {
    const fields = [
        document.getElementById('airport-select'),
        document.getElementById('date-depot'),
        document.getElementById('date-recuperation'),
        document.getElementById('heure-depot'),
        document.getElementById('heure-recuperation')
    ];
    
    let isValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Reset all validation states to default
 */
function resetValidationStates() {
    const fields = document.querySelectorAll('#step-1 input, #step-1 select');
    fields.forEach(field => {
        setInputDefault(field);
    });
}


/**
 * Affiche les dates sélectionnées dans la section de résumé.
 */
function displaySelectedDates() {
    const options = { month: 'short', day: 'numeric' };
    const locale = getLang() === 'en' ? 'en-US' : 'fr-FR';
    const depotDate = new Date(document.getElementById('date-depot').value).toLocaleDateString(locale, options);
    const recupDate = new Date(document.getElementById('date-recuperation').value).toLocaleDateString(locale, options);
    const depotHeure = document.getElementById('heure-depot').value;
    const recupHeure = document.getElementById('heure-recuperation').value;

    document.getElementById('display-date-depot').textContent = `${depotDate}, ${depotHeure}`;
    document.getElementById('display-date-recuperation').textContent = `${recupDate}, ${recupHeure}`;

    const airportSelect = document.getElementById('airport-select');
    const selectedAirportName = airportSelect.options[airportSelect.selectedIndex].text;
    document.getElementById('display-airport-name').textContent = selectedAirportName;
}


/**
 * Helper function to check availability for a single date and time.
 * @param {string} date - The date in YYYY-MM-DD format.
 * @param {string} time - The time in HH:mm format.
 * @returns {Promise<object>} - Object with available (boolean), estContrainte (boolean), contrainte (object)
 */
async function checkSingleDateAvailability(date, time) {
    if (!airportId || !date || !time) {
        // This case should be handled by the calling function, but as a safeguard:
        await showCustomAlert(t('alert_missing_data_title'), t('alert_missing_data_message'));
        return { available: false, estContrainte: false, contrainte: null };
    }

    try {
        const dateTime = new Date(`${date}T${time}`);
        
        // Vérifier si la date est antérieure à la date actuelle (en tenant compte de l'heure)
        const now = new Date();
        // Arrondir l'heure actuelle à la minute pour éviter les problèmes de secondes
        now.setSeconds(0, 0);
        
        if (dateTime < now) {
            console.log('Date antérieure à maintenant:', { dateTime, now });
            return { 
                available: false, 
                estContrainte: false, 
                contrainte: null,
                message: 'Date antérieure'
            };
        }
        
        // Formater la date pour l'API BDM : yyyyMMddTHHmm (ex: 20260322T0500)
        const pad = (num) => num.toString().padStart(2, '0');
        const year = dateTime.getFullYear();
        const month = pad(dateTime.getMonth() + 1);
        const day = pad(dateTime.getDate());
        const hours = pad(dateTime.getHours());
        const minutes = pad(dateTime.getMinutes());
        
        const dateToVerify = `${year}${month}${day}T${hours}${minutes}`;
        
        console.log('Date envoyée à l\'API BDM:', dateToVerify);

        const response = await fetch(routeUrl('checkAvailability', '/api/check-availability'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ idPlateforme: airportId, dateToCheck: dateToVerify })
        });

        const result = await response.json();

        // Nouvelle logique avec contraintes
        const content = result.content || {};
        
        // L'API retourne: estOuvert, avecContrainte, contrainte
        const estOuvert = content.estOuvert ?? (result.statut === 1);
        const estContrainte = content.avecContrainte ?? content.estContrainte ?? false;
        const contrainte = content.contrainte ?? content.Contrainte ?? null;
        
        // La plateforme est "disponible" si elle est ouverte OU si elle est contrainte (on peut débloquer)
        const available = estOuvert || estContrainte;
        
        console.log('Check availability result:', { estOuvert, estContrainte, contrainte, available, dateToVerify });
        
        return { 
            available, 
            estContrainte, 
            contrainte,
            statut: result.statut,
            message: result.message
        };
    } catch (error) {
        console.error(`Erreur lors de la vérification de disponibilité pour ${date} ${time}:`, error);
        await showCustomAlert(t('error'), t('alert_availability_error'));
        return { available: false, estContrainte: false, contrainte: null };
    }
}


/**
 * Vérifie la disponibilité de l'agence pour les dates de dépôt ET de retrait.
 * @returns {Promise<boolean>}
 */
async function checkAvailability() {
    console.log('checkAvailability() called');
    const spinner = document.getElementById('loading-spinner-availability');
    const btn = document.getElementById('check-availability-btn');

    console.log('Spinner element:', spinner);
    console.log('Button element:', btn);

    // Exit early if button doesn't exist (not on booking page)
    if (!btn) {
        console.log('Button not found, exiting');
        return false;
    }

    // First, validate all fields with visual feedback
    const isStep1Valid = validateStep1();
    
    if (!isStep1Valid) {
        // Scroll to first error
        const firstError = document.querySelector('.input-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }

    // Show spinner if it exists
    if (spinner) {
        spinner.style.display = 'inline-block';
    }
    btn.disabled = true;

    const dateDepot = document.getElementById('date-depot').value;
    const heureDepot = document.getElementById('heure-depot').value;
    const dateRetrait = document.getElementById('date-recuperation').value;
    const heureRetrait = document.getElementById('heure-recuperation').value;

    console.log('Airport ID:', airportId);
    console.log('Dates/Times:', { dateDepot, heureDepot, dateRetrait, heureRetrait });

    try {
        // Check both dates in parallel
        const [depotResult, retraitResult] = await Promise.all([
            checkSingleDateAvailability(dateDepot, heureDepot),
            checkSingleDateAvailability(dateRetrait, heureRetrait)
        ]);

        const depotAvailable = depotResult.available;
        const retraitAvailable = retraitResult.available;
        
        // Stocker les contraintes pour les utiliser plus tard
        window.bookingConstraints = {
            depot: depotResult.estContrainte ? depotResult.contrainte : null,
            retrait: retraitResult.estContrainte ? retraitResult.contrainte : null
        };
        
        console.log('Booking constraints:', window.bookingConstraints);

        if (depotAvailable && retraitAvailable) {
            // Vérifier s'il y a des contraintes
            const hasConstraints = window.bookingConstraints.depot || window.bookingConstraints.retrait;

            if (hasConstraints) {
                console.log('Plateforme avec contraintes - récupération des détails...');
                // Récupérer les contraintes depuis l'API avec forceRefresh
                const baggagesForOptionsQuote = cartItems.filter(i => i.itemCategory === 'baggage').map(item => {
                    const pid = item.productId != null ? String(item.productId) : '';
                    const product = (globalProductsData || []).find(p => (p.id != null ? String(p.id) : '') === pid);
                    const sid = product && (product.idService ?? product.id_service);
                    return {
                        productId: item.productId,
                        serviceId: sid || serviceId,
                        dateDebut: `${dateDepot}T${heureDepot}:00`,
                        dateFin: `${dateRetrait}T${heureRetrait}:00`,
                        quantity: item.quantity
                    };
                });
                
                if (typeof updateContraintesInCart === 'function') {
                    await updateContraintesInCart(airportId, baggagesForOptionsQuote, true);
                }
            }

            return true;
        } else {
            // Provide a more specific error message
            let errorMessage = t('agency_hours_message');
            if (!depotAvailable && !retraitAvailable) {
                errorMessage += t('agency_hours_both_out');
            } else if (!depotAvailable) {
                errorMessage += t('agency_hours_dropoff_out');
            } else {
                errorMessage += t('agency_hours_pickup_out');
            }
            errorMessage += `<br><br>${t('agency_hours_contact')}`;

            await showCustomAlert(t('alert_agency_closed_title'), errorMessage);
            return false;
        }
    } catch (error) {
        // This catch is for errors in Promise.all or the main logic, not the individual fetches
        console.error('Erreur lors de la vérification de disponibilité (Promise.all):', error);
        await showCustomAlert(t('error'), t('alert_availability_error'));
        return false;
    } finally {
        if (spinner) {
            spinner.style.display = 'none';
        }
        btn.disabled = false;
    }
}


/**
 * Récupère le devis depuis l'API et met à jour l'affichage.
 */
async function getQuoteAndDisplay() {
    const cartSpinner = document.getElementById('loading-spinner-cart');
    if (cartSpinner) cartSpinner.style.display = 'inline-block';

    const dateDepot = document.getElementById('date-depot').value;
    const heureDepot = document.getElementById('heure-depot').value;
    const dateRecuperation = document.getElementById('date-recuperation').value;
    const heureRecuperation = document.getElementById('heure-recuperation').value;

    if (!dateDepot || !heureDepot || !dateRecuperation || !heureRecuperation) {
        await showCustomAlert(t('warning'), t('alert_check_dates_message'));
        if (cartSpinner) cartSpinner.style.display = 'none';
        return;
    }

    const debut = new Date(`${dateDepot}T${heureDepot}:00`);
    const fin = new Date(`${dateRecuperation}T${heureRecuperation}:00`);
    const dureeEnMinutes = Math.ceil(Math.abs(fin - debut) / (1000 * 60));

    if (dureeEnMinutes <= 0) {
        await showCustomAlert(t('warning'), t('alert_return_after_dropoff'));
        if (cartSpinner) cartSpinner.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(routeUrl('getQuote', '/api/get-quote'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ idPlateforme: airportId, idService: serviceId, duree: dureeEnMinutes })
        });

        if (!response.ok) {
            const text = await response.text();
            let msg = 'HTTP ' + response.status;
            try {
                var errJson = JSON.parse(text);
                msg = errJson.message || errJson.error || msg;
            } catch (err) {
                if (text && text.length < 200) msg = text;
            }
            await showCustomAlert(t('alert_pricing_error_title'), msg || t('alert_pricing_fetch_error'));
            if (cartSpinner) cartSpinner.style.display = 'none';
            return;
        }
        const result = await response.json().catch(function () { return {}; });
        if (result.statut === 1 && result.content) {
            var c = result.content;
            globalProductsData = Array.isArray(c.products) ? c.products : (Array.isArray(c) ? c : []);
            // Pour premium : lieux statiques (1,2,3,4) si l'endpoint ne retourne rien
            globalLieuxData = Array.isArray(c.lieux) && c.lieux.length > 0 ? c.lieux : [
                { id: 1, libelle: 'Lieu 1' },
                { id: 2, libelle: 'Lieu 2' },
                { id: 3, libelle: 'Lieu 3' },
                { id: 4, libelle: 'Lieu 4' }
            ];
            // Pour tester les remises : ouvrir la console (F12) et vérifier que les produits ont tauxRemise ou prixUnitaireAvantRemise
            if (globalProductsData.length > 0 && typeof console !== 'undefined' && console.info) {
                console.info('[Remises] Produits reçus de l\'API getQuote:', globalProductsData.map(p => ({
                    libelle: p.libelle,
                    prixUnitaire: p.prixUnitaire ?? p.prix_unitaire,
                    tauxRemise: p.tauxRemise ?? p.taux_remise,
                    prixUnitaireAvantRemise: p.prixUnitaireAvantRemise ?? p.prix_unitaire_avant_remise
                })));
            }
            displayOptions(dureeEnMinutes);
            
            // Charger les contraintes obligatoires (prestations complémentaires)
            const dateDepot = document.getElementById('date-depot').value;
            const heureDepot = document.getElementById('heure-depot').value;
            const dateRecuperation = document.getElementById('date-recuperation').value;
            const heureRecuperation = document.getElementById('heure-recuperation').value;
            
            const baggagesForConstraints = cartItems.filter(i => i.itemCategory === 'baggage').map(item => {
                const pid = item.productId != null ? String(item.productId) : '';
                const product = (globalProductsData || []).find(p => (p.id != null ? String(p.id) : '') === pid);
                const sid = product && (product.idService ?? product.id_service);
                return {
                    productId: item.productId,
                    serviceId: sid || serviceId,
                    dateDebut: `${dateDepot}T${heureDepot}:00`,
                    dateFin: `${dateRecuperation}T${heureRecuperation}:00`,
                    quantity: item.quantity
                };
            });

            if (typeof updateContraintesInCart === 'function' && baggagesForConstraints.length > 0) {
                await updateContraintesInCart(airportId, baggagesForConstraints);
            }
            
            if (typeof updateCartDisplay === 'function') updateCartDisplay();
            if (typeof syncQuantityDisplays === 'function') syncQuantityDisplays();
        } else {
            await showCustomAlert(t('alert_pricing_error_title'), t('alert_pricing_error_message') + ' ' + (result.message || t('alert_invalid_response')));
        }
    } catch (error) {
        console.error('Erreur lors de la récupération des tarifs et lieux:', error);
        await showCustomAlert(t('error'), t('alert_pricing_fetch_error'));
    } finally {
        if (cartSpinner) cartSpinner.style.display = 'none';
    }
}

/**
 * Gère le clic sur le bouton de paiement.
 */
async function handleTotalClick() {
    const loader = document.getElementById('loader');
    const t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));
    if (loader) loader.classList.remove('hidden');

    var loaderSafetyId = setTimeout(function () {
        if (loader && !loader.classList.contains('hidden')) {
            loader.classList.add('hidden');
            loader.style.display = 'none';
            console.warn('Loader safety timeout: forced hide after 45s');
        }
    }, 45000);

    var didRedirect = false;
    try {
        if (cartItems.length === 0) {
            await showCustomAlert(t('alert_empty_cart_title'), t('alert_empty_cart_message'));
            if (loader) loader.classList.add('hidden');
            return;
        }
        
        // --- NOUVELLE LOGIQUE D'OPTIONS ---
        const dateDepot = document.getElementById('date-depot').value;
        const heureDepot = document.getElementById('heure-depot').value;
        const dateRecuperation = document.getElementById('date-recuperation').value;
        const heureRecuperation = document.getElementById('heure-recuperation').value;

        const baggagesForOptionsQuote = cartItems.filter(i => i.itemCategory === 'baggage').map(item => {
            const pid = item.productId != null ? String(item.productId) : '';
            const product = (globalProductsData || []).find(p => (p.id != null ? String(p.id) : '') === pid);
            const sid = product && (product.idService ?? product.id_service);
            return {
                productId: item.productId,
                serviceId: sid || serviceId,
                dateDebut: `${dateDepot}T${heureDepot}:00`,
                dateFin: `${dateRecuperation}T${heureRecuperation}:00`,
                quantity: item.quantity
            };
        });

        let shouldShowOptionsModal = false;

        // S'assurer que tarifs et lieux sont chargés (requis pour les options et la modale)
        if (!globalProductsData || globalProductsData.length === 0 || !globalLieuxData || globalLieuxData.length === 0) {
            if (typeof getQuoteAndDisplay === 'function') {
                await getQuoteAndDisplay();
            }
        }

        // --- NOUVELLE LOGIQUE : Récupérer les contraintes obligatoires ---
        // Utilise la fonction du fichier contraintes.js
        if (typeof updateContraintesInCart === 'function') {
            await updateContraintesInCart(airportId, baggagesForOptionsQuote);
        }
        // --- FIN NOUVELLE LOGIQUE CONTRAINTES ---

        try {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfVal = csrfMeta ? csrfMeta.getAttribute('content') : '';
            const optCtrl = new AbortController();
            const optTimeout = setTimeout(function () { optCtrl.abort(); }, 15000);
            const optionsQuoteResponse = await fetch(routeUrl('optionsQuote', '/api/commande/options-quote'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfVal },
                body: JSON.stringify({
                    idPlateforme: airportId,
                    cartItems: baggagesForOptionsQuote,
                    guestEmail: guestEmail,
                    dateDepot: dateDepot,
                    heureDepot: heureDepot,
                    dateRecuperation: dateRecuperation,
                    heureRecuperation: heureRecuperation,
                    globalProductsData: globalProductsData
                }),
                signal: optCtrl.signal
            });
            clearTimeout(optTimeout);

            const optionsQuoteResult = await optionsQuoteResponse.json();

            if (optionsQuoteResult.statut === 1 && optionsQuoteResult.content) {
                console.log('Options from API:', optionsQuoteResult.content);

                // Normaliser les options (API BDM peut renvoyer prix_unitaire, Id, Libelle)
                var norm = function (o) {
                    if (!o) return null;
                    return {
                        id: o.id ?? o.Id ?? o.ID,
                        libelle: o.libelle ?? o.Libelle ?? o.nom ?? '',
                        prixUnitaire: parseFloat(o.prixUnitaire ?? o.prix_unitaire ?? o.prix ?? 0) || 0,
                        prixUnitaireAvantRemise: o.prixUnitaireAvantRemise ?? o.prix_unitaire_avant_remise ?? null,
                        tauxRemise: o.tauxRemise ?? o.taux_remise ?? null,
                        referenceInterne: o.referenceInterne ?? o.ReferenceInterne ?? null
                    };
                };

                // Le backend retourne déjà un objet avec priority et premium
                var apiPriority = optionsQuoteResult.content.priority;
                var apiPremium = optionsQuoteResult.content.premium;

                var foundPriority = norm(apiPriority);
                var foundPremium = norm(apiPremium);

                // Mettre à jour staticOptions seulement si on a trouvé les options
                if (foundPriority && foundPriority.id) {
                    staticOptions.priority = foundPriority;
                }
                if (foundPremium && foundPremium.id) {
                    staticOptions.premium = foundPremium;
                }
                
                // Récupérer les options Access (contraintes horaires) depuis le contenu
                // L'API peut retourner d'autres options dans content qui ne sont pas priority/premium
                var allOptions = optionsQuoteResult.content;
                if (Array.isArray(allOptions)) {
                    // Filtrer les options qui ne sont pas Priority ou Premium
                    staticOptions.access = allOptions
                        .filter(function(opt) {
                            var ref = (opt.referenceInterne ?? opt.ReferenceInterne ?? '').toUpperCase();
                            var lib = (opt.libelle ?? '').toLowerCase();
                            // Exclure Priority et Premium, garder les options Access
                            return ref !== 'PRIO' && ref !== 'PREM' && 
                                   !lib.includes('priority') && 
                                   !lib.includes('premium') &&
                                   (lib.includes('access') || lib.includes('night') || lib.includes('evening') || lib.includes('morning'));
                        })
                        .map(norm);
                }

                console.log('Updated staticOptions:', staticOptions);
                console.log('Priority found:', !!foundPriority?.id, 'Premium found:', !!foundPremium?.id);
                console.log('Access options found:', staticOptions.access);

                // Déterminer si le drawer doit être affiché
                if (staticOptions.priority?.id || staticOptions.premium?.id || staticOptions.access.length > 0) {
                    shouldShowOptionsModal = true;
                }

            } else {
                console.warn('Options API:', optionsQuoteResult.message || 'Pas d\'options');
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des prix des options:', error);
            // On continue sans les options, pas de blocage
        }

        if (shouldShowOptionsModal) {
            // Calculer la disponibilité de premium
            displayOptions(0); // L'argument n'est pas utilisé mais la fonction est appelée pour isPremiumAvailable
            // Utiliser le nouveau drawer au lieu de l'ancienne modale
            const result = await window.openOptionsDrawer();
            if (result === false) {
                if (loader) loader.classList.add('hidden');
                return; // L'utilisateur a fermé le drawer
            }
        }

        // --- FIN NOUVELLE LOGIQUE ---

        // Le reste du processus de paiement continue ici
        var authData = { authenticated: false };
        try {
            var authResp = await fetch(routeUrl('checkAuthStatus', '/check-auth-status'));
            authData = await authResp.json().catch(function () { return authData; });
        } catch (authErr) {
            console.warn('Auth check failed:', authErr);
        }

        const currentLang = window.getCurrentLanguage ? window.getCurrentLanguage() : (localStorage.getItem('app_language') || 'fr');

        if (!authData.authenticated) {
            if (!guestEmail) {
                if (loader) loader.classList.add('hidden');
                await sleep(300);

                const choice = await showLoginOrGuestPrompt();

                if (choice === 'login') {
                    if (window.openLoginModal) {
                        window.openLoginModal();
                    } else {
                        await showCustomAlert(t('error'), t('alert_open_login_error'));
                    }
                    if (loader) loader.classList.add('hidden');
                    return;
                } else if (choice === 'guest') {
                    const email = await showCustomPrompt(
                        t('prompt_contact_title'),
                        t('prompt_contact_subtitle'),
                        t('prompt_contact_placeholder')
                    );
                    if (email) {
                        guestEmail = email;
                        saveStateToSession();
                        if (loader) loader.classList.remove('hidden');
                    } else {
                        if (loader) loader.classList.add('hidden');
                        return;
                    }
                } else {
                    if (loader) loader.classList.add('hidden');
                    return;
                }
            }
        } else {
            guestEmail = null;
            saveStateToSession();
        }

        const baggages = cartItems.filter(i => i.itemCategory === 'baggage').map(item => ({ type: item.type, quantity: item.quantity }));
        var rawOptions = cartItems.filter(i => i.itemCategory === 'option');
        var options = rawOptions.map(function (o) {
            var p = o.prix ?? o.prixUnitaire ?? 0;
            var refInterne = '';
            if (o.key === 'priority' && staticOptions.priority && staticOptions.priority.referenceInterne) {
                refInterne = staticOptions.priority.referenceInterne;
            } else if (o.key === 'premium' && staticOptions.premium && staticOptions.premium.referenceInterne) {
                refInterne = staticOptions.premium.referenceInterne;
            }
            return {
                id: o.id || '',
                libelle: o.libelle || '',
                prix: p,
                prixUnitaire: p,
                details: o.details || null,
                referenceInterne: refInterne
            };
        });

        // Récupérer les contraintes (pour calcul du total uniquement)
        // Les contraintes ne sont PAS envoyées à BDM, elles seront ajoutées automatiquement par l'ERP
        var contraintes = [];
        if (typeof window.bookingContraintesItems !== 'undefined' && Array.isArray(window.bookingContraintesItems)) {
            contraintes = window.bookingContraintesItems.map(function (c) {
                return {
                    id: c.id || '',
                    libelle: c.libelle || '',
                    prix: c.prix || 0,
                    prixUnitaire: c.prixUnitaire || c.prix || 0,
                    isMandatory: true
                };
            });
        }

        console.log('Options envoyées (hors contraintes - ajoutées auto par ERP):', options);
        console.log('Contraintes (pour calcul du total uniquement):', contraintes);

        var airportSelect = document.getElementById('airport-select');
        var airportName = (airportSelect && airportSelect.options && airportSelect.options[airportSelect.selectedIndex]) ? airportSelect.options[airportSelect.selectedIndex].text : '';

        const formData = {
            airportId: airportId,
            airportName: airportName,
            dateDepot: document.getElementById('date-depot').value,
            heureDepot: document.getElementById('heure-depot').value,
            dateRecuperation: document.getElementById('date-recuperation').value,
            heureRecuperation: document.getElementById('heure-recuperation').value,
            baggages: baggages,
            products: globalProductsData,
            options: options,
            contraintes: contraintes, // Pour calcul du total uniquement (ne sera pas envoyé à BDM)
            guest_email: guestEmail,
            lang: currentLang
        };

        const prepareUrl = routeUrl('preparePayment', '/prepare-payment');
        const controller = new AbortController();
        const timeoutId = setTimeout(function () { controller.abort(); }, 30000);

        let prepareResponse;
        try {
            var csrfEl = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfEl ? csrfEl.getAttribute('content') : '';
            prepareResponse = await fetch(prepareUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(formData),
                signal: controller.signal
            });
        } catch (fetchErr) {
            clearTimeout(timeoutId);
            if (fetchErr.name === 'AbortError') {
                await showCustomAlert(t('error'), t('alert_tech_error') || 'La requête a expiré. Vérifiez votre connexion et réessayez.');
            } else {
                await showCustomAlert(t('error'), t('alert_tech_error') || 'Erreur de connexion. Vérifiez votre connexion et réessayez.');
            }
            if (loader) loader.classList.add('hidden');
            return;
        }
        clearTimeout(timeoutId);

        let resultData;
        try {
            resultData = await prepareResponse.json();
        } catch (jsonErr) {
            console.error('Réponse non-JSON du serveur:', jsonErr);
            await showCustomAlert(t('error'), t('alert_tech_error') || 'Le serveur a renvoyé une réponse invalide. Réessayez.');
            if (loader) loader.classList.add('hidden');
            return;
        }

        if (prepareResponse.ok) {
            var url = resultData && resultData.redirect_url;
            if (!url || typeof url !== 'string') {
                await showCustomAlert(t('error'), t('alert_tech_error') || 'Redirection impossible. Réessayez.');
                if (loader) loader.classList.add('hidden');
                return;
            }
            didRedirect = true;
            await sleep(300);
            if (window.self !== window.top) {
                try { window.parent.postMessage({ type: 'hp-booking-redirect', url: url }, '*'); } catch (e) {}
                window.top.location.href = url;
            } else {
                window.location.href = url;
            }
        } else {
            let errorMessage = (resultData && resultData.message) || t('alert_unknown_error');
            if (resultData && resultData.errors) {
                var flatErrors = Object.values(resultData.errors).flat();
                if (flatErrors && flatErrors.length) errorMessage += '<br><br>' + flatErrors.join('<br>');
            }
            await showCustomAlert(t('alert_validation_error_title'), errorMessage);
            if (loader) loader.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erreur critique dans handleTotalClick:', error);
        await showCustomAlert(t('error'), t('alert_tech_error') || 'Une erreur est survenue. Réessayez.');
    } finally {
        clearTimeout(loaderSafetyId);
        if (!didRedirect && loader) {
            loader.classList.add('hidden');
            loader.style.display = 'none';
        }
    }
}

/**
 * Applique les contraintes sur les champs de date et heure.
 */
function applyDateInputConstraints() {
    const dateDepotInput = document.getElementById('date-depot');
    const dateRecuperationInput = document.getElementById('date-recuperation');
    const heureDepotInput = document.getElementById('heure-depot');
    const heureRecuperationInput = document.getElementById('heure-recuperation');

    const today = new Date();
    const pad = (num) => num.toString().padStart(2, '0');
    const todayFormatted = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;

    dateDepotInput.min = todayFormatted;
    dateDepotInput.max = dateRecuperationInput.value || '';

    // Heures d'ouverture de base
    heureDepotInput.min = '07:01';
    heureDepotInput.max = '21:00';

    // Si la date de dépôt est aujourd'hui, ajuster l'heure minimale à l'heure actuelle + 1 heure
    if (dateDepotInput.value === todayFormatted) {
        const currentHour = today.getHours();
        const currentMinute = today.getMinutes();
        // Arrondir à l'heure suivante
        const nextAvailableHour = currentHour + 1;
        const nextAvailableMinute = currentMinute; // Garder les minutes actuelles
        
        if (nextAvailableHour < 21) {
            // Formater l'heure minimale avec les minutes actuelles
            heureDepotInput.min = `${pad(nextAvailableHour)}:${pad(nextAvailableMinute)}`;
        } else {
            // Si on est après 20h, on ne peut plus réserver pour aujourd'hui
            heureDepotInput.min = '23:59'; // Bloquer la sélection
        }
        
        // Si l'heure actuelle de dépôt est dans le passé, la réinitialiser
        if (heureDepotInput.value) {
            const [currentHeureDepotHour, currentHeureDepotMinute] = heureDepotInput.value.split(':').map(Number);
            const selectedDateTime = new Date(today.getFullYear(), today.getMonth(), today.getDate(), currentHeureDepotHour, currentHeureDepotMinute);
            if (selectedDateTime < today) {
                // Réinitialiser à l'heure suivante disponible
                heureDepotInput.value = heureDepotInput.min;
            }
        }
    }

    if (dateDepotInput.value) {
        dateRecuperationInput.min = dateDepotInput.value;
    } else {
        dateRecuperationInput.min = todayFormatted;
    }

    heureRecuperationInput.min = '07:01';
    heureRecuperationInput.max = '21:00';

    if (dateDepotInput.value === dateRecuperationInput.value && heureDepotInput.value) {
        const [depotHour, depotMinute] = heureDepotInput.value.split(':').map(Number);
        let minRecuperationHour = depotHour + 3;
        if(minRecuperationHour < 21) {
             heureRecuperationInput.min = `${pad(minRecuperationHour)}:${pad(depotMinute)}`;
        } else {
             // Si le dépôt + 3h dépasse 21h, on ne peut pas récupérer le même jour
             // Il faudrait une logique plus complexe ici, pour l'instant on se contente de ça
        }
    }
    saveStateToSession();
}

/**
 * Sync quantity display spans (data-quantity-display) from cartItems.
 */
function syncQuantityDisplays() {
    document.querySelectorAll('[data-quantity-display]').forEach(function (el) {
        const productId = el.getAttribute('data-quantity-display');
        const qty = (typeof cartItems !== 'undefined' ? cartItems : [])
            .filter(function (i) { return i.itemCategory === 'baggage' && (String(i.productId) === String(productId)); })
            .reduce(function (sum, i) { return sum + (i.quantity || 0); }, 0);
        el.textContent = qty;
    });
}

/**
 * Gère le clic sur + / - des quantités bagages. Ajoute ou retire des cartItems et met à jour l'affichage.
 */
function handleQuantityChange(e) {
    const btn = e.target.closest('.quantity-change-btn');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const productId = btn.getAttribute('data-product-id');
    const option = btn.closest('.baggage-option');
    const libelle = option ? (option.getAttribute('data-libelle') || '') : '';
    const type = (productMapJs[libelle] && productMapJs[libelle].type) ? productMapJs[libelle].type : 'baggage';

    if (!productId) return;

    if (action === 'plus') {
        const existing = cartItems.find(function (i) {
            return i.itemCategory === 'baggage' && String(i.productId) === String(productId);
        });
        if (existing) {
            existing.quantity = (existing.quantity || 0) + 1;
        } else {
            cartItems.push({
                itemCategory: 'baggage',
                productId: productId,
                type: type,
                quantity: 1,
                libelle: libelle || null
            });
        }
    } else if (action === 'minus') {
        const existing = cartItems.find(function (i) {
            return i.itemCategory === 'baggage' && String(i.productId) === String(productId);
        });
        if (existing) {
            existing.quantity = (existing.quantity || 1) - 1;
            if (existing.quantity <= 0) {
                const idx = cartItems.indexOf(existing);
                if (idx !== -1) cartItems.splice(idx, 1);
            }
        }
    }

    syncQuantityDisplays();
    if (typeof updateCartDisplay === 'function') updateCartDisplay();
    if (typeof saveStateToSession === 'function') saveStateToSession();
    
    // Mettre à jour les contraintes quand on change la quantité de bagages
    if (typeof updateContraintesInCart === 'function' && airportId) {
        const dateDepot = document.getElementById('date-depot')?.value;
        const heureDepot = document.getElementById('heure-depot')?.value;
        const dateRecuperation = document.getElementById('date-recuperation')?.value;
        const heureRecuperation = document.getElementById('heure-recuperation')?.value;
        
        if (dateDepot && heureDepot && dateRecuperation && heureRecuperation) {
            const baggagesForConstraints = cartItems.filter(i => i.itemCategory === 'baggage').map(item => {
                const pid = item.productId != null ? String(item.productId) : '';
                const product = (globalProductsData || []).find(p => (p.id != null ? String(p.id) : '') === pid);
                const sid = product && (product.idService ?? product.id_service);
                return {
                    productId: item.productId,
                    serviceId: sid || serviceId,
                    dateDebut: `${dateDepot}T${heureDepot}:00`,
                    dateFin: `${dateRecuperation}T${heureRecuperation}:00`,
                    quantity: item.quantity
                };
            });
            if (baggagesForConstraints.length > 0) {
                updateContraintesInCart(airportId, baggagesForConstraints);
            }
        }
    }
}

/**
 * Point d'entrée principal, initialise tous les écouteurs d'événements.
 */
document.addEventListener('DOMContentLoaded', function () {
    // ---- DEBUT BLOC DE PRE-SELECTION ----
    const selectedAirportIdFromUrl = document.body.dataset.selectedAirportId;
    if (selectedAirportIdFromUrl) {
        const airportSelect = document.getElementById('airport-select');
        if (airportSelect) {
            airportSelect.value = selectedAirportIdFromUrl;
            // Mettre à jour la variable globale utilisée par le script
            airportId = selectedAirportIdFromUrl;
            // Simuler un événement de changement pour déclencher toute logique dépendante
            airportSelect.dispatchEvent(new Event('change'));
        }
    }
    // ---- FIN BLOC DE PRE-SELECTION ----

    // Chargement de l'état initial
    loadStateFromSession();
    if (typeof syncQuantityDisplays === 'function') syncQuantityDisplays();

    // Initialisation des listeners pour les modales
    if(typeof setupQdmListeners !== 'undefined') setupQdmListeners();
    // Le setup des listeners de la modale custom est déjà dans modal.js

    // Initialisation des contraintes de date
    applyDateInputConstraints();
    
    // InitContraintes sera appelé par loadStateFromSession si des contraintes sont en cache
    // ou lors du checkAvailability si nécessaire

    // --- ÉCOUTEURS D'ÉVÉNEMENTS ---

    document.getElementById('back-to-step-1-btn').addEventListener('click', function () {
        var step1 = document.getElementById('step-1');
        var step2 = document.getElementById('baggage-selection-step');
        step2.style.display = 'none';
        step1.style.display = 'block';
        if (document.getElementById('hp-booking-root') && document.getElementById('hp-booking-root').classList.contains('hp-modal-mode')) {
            step2.classList.remove('hp-step-active');
            step1.classList.add('hp-step-active');
        }
        this.classList.add('hidden');
        document.getElementById('sticky-wrapper').style.display = 'none';
        saveStateToSession();
    });

    document.getElementById('airport-select').addEventListener('change', function () {
        airportId = this.value;
        saveStateToSession();
    });

    document.getElementById('check-availability-btn').addEventListener('click', async () => {
        console.log('CHECK AVAILABILITY BUTTON CLICKED!');
        saveStateToSession();
        const isAvailable = await checkAvailability();
        console.log('checkAvailability returned:', isAvailable);
        if (isAvailable) {
            var step1 = document.getElementById('step-1');
            var step2 = document.getElementById('baggage-selection-step');
            step1.style.display = 'none';
            step2.style.display = 'block';
            if (document.getElementById('hp-booking-root') && document.getElementById('hp-booking-root').classList.contains('hp-modal-mode')) {
                step1.classList.remove('hp-step-active');
                step2.classList.add('hp-step-active');
            }
            document.getElementById('back-to-step-1-btn').classList.remove('hidden');
            document.getElementById('sticky-wrapper').style.display = 'block';
            
            // Aligner le panier avec la section Aéroport sélectionné
            if (typeof alignStickyWithBaggage === 'function') {
                setTimeout(alignStickyWithBaggage, 50);
            }
            
            displaySelectedDates();
            getQuoteAndDisplay();
            saveStateToSession();
        }
    });

    // Listeners pour le panier (ajout/suppression d'articles)
    document.getElementById('baggage-grid-container').addEventListener('click', handleQuantityChange);
    document.getElementById('cart-items-container').addEventListener('click', (e) => {
        const target = e.target.closest('.delete-item-btn');
        if (target) {
            const index = parseInt(target.dataset.index, 10);
            cartItems.splice(index, 1);
            updateCartDisplay(); // Met à jour l'affichage et sauvegarde la session
        }
    });

    // Bouton « Procéder au paiement » : clic sur la zone total
    const paymentBtn = document.querySelector('.summary-total-container');
    if (paymentBtn && typeof handleTotalClick === 'function') {
        paymentBtn.addEventListener('click', handleTotalClick);
        paymentBtn.setAttribute('role', 'button');
        paymentBtn.setAttribute('tabindex', '0');
        paymentBtn.style.cursor = 'pointer';
        paymentBtn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleTotalClick();
            }
        });
    }

    // Listeners pour les inputs de date/heure
    const dateInputs = ['date-depot', 'date-recuperation', 'heure-depot', 'heure-recuperation'];
    dateInputs.forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener('change', function() {
            applyDateInputConstraints();
            validateField(input); // Real-time validation on change
        });
        input.addEventListener('input', function() {
            saveStateToSession();
            // Validate on input for immediate feedback (with debounce could be added)
            if (input.value.trim()) {
                validateField(input);
            } else {
                setInputDefault(input);
            }
        });
        // Also validate on blur
        input.addEventListener('blur', function() {
            validateField(input);
        });
    });

    // Airport select validation
    const airportSelect = document.getElementById('airport-select');
    airportSelect.addEventListener('change', function() {
        airportId = this.value;
        saveStateToSession();
        validateField(this);
    });

    // Initialize validation states on page load for pre-filled fields
    const allFormFields = ['airport-select', ...dateInputs];
    allFormFields.forEach(id => {
        const field = document.getElementById(id);
        if (field && field.value.trim()) {
            validateField(field);
        } else if (field) {
            setInputDefault(field);
        }
    });

    // Listener pour le tooltip
    const tooltip = document.getElementById('baggage-tooltip');
    const baggageSelectionStep = document.getElementById('baggage-selection-step');
    baggageSelectionStep.addEventListener('mouseover', (e) => {
        const target = e.target.closest('.info-icon');
        if (!target) return;
        const libelle = target.dataset.libelle;
        const descKey = target.dataset.i18nKey;
        const productData = productMapJs[libelle];
        if (productData && productData.description) {
            // Call description as function to get translated text
            tooltip.textContent = typeof productData.description === 'function' ? productData.description() : productData.description;
            tooltip.classList.remove('hidden');
            const rect = target.getBoundingClientRect();
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            tooltip.style.top = `${rect.top + window.scrollY - tooltip.offsetHeight - 5}px`;
        }
    });
    baggageSelectionStep.addEventListener('mouseout', (e) => {
        if (e.target.closest('.info-icon')) {
            tooltip.classList.add('hidden');
        }
    });

    // Initialiser l'alignement du panier après le chargement
    setTimeout(alignStickyWithBaggage, 100);
    setTimeout(alignStickyWithBaggage, 500);
});

window.addEventListener('pageshow', function(event) {
    // Vérifie si la page est restaurée depuis le cache (bfcache)
    if (event.persisted) {
        // Masque le loader si la page est restaurée depuis le cache du navigateur
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hidden');
        }
        console.log('Page restaurée depuis le cache. Loader masqué.');
    }
});

// ============================================
// DEBUG / TEST FUNCTIONS - Remove in production
// ============================================
window.testValidation = function() {
    console.log('Testing validation functions...');
    console.log('setInputDefault:', typeof setInputDefault);
    console.log('setInputFilled:', typeof setInputFilled);
    console.log('setInputError:', typeof setInputError);
    console.log('validateField:', typeof validateField);
    console.log('validateStep1:', typeof validateStep1);
    
    // Test on first field
    const airport = document.getElementById('airport-select');
    if (airport) {
        console.log('Airport select found:', airport);
        console.log('Current classes:', airport.classList);
        setInputFilled(airport);
        console.log('After setInputFilled - classes:', airport.classList);
    }
};
console.log('Validation functions loaded. Run testValidation() to test.');
