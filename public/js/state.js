// Fichier: public/js/state.js

// --- STATE MANAGEMENT ---

// Les données qui sont chargées depuis la session ou initialisées à zéro.
let airportId = null;
const serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
let cartItems = [];
let globalProductsData = [];
let globalLieuxData = [];
let guestEmail = null;

// Données statiques ou initiales, rendues globales
let staticOptions = {
    priority: { id: null, libelle: 'Service Priority', prixUnitaire: 0 }, // Initialisé à 0, sera mis à jour par l'API
    premium: { id: null, libelle: 'Service Premium', prixUnitaire: 0 },    // Initialisé à 0, sera mis à jour par l'API
    // Options Access (contraintes horaires) - seront mises à jour par l'API
    access: []
};


/**
 * Sauvegarde l'état actuel du formulaire dans la session du navigateur.
 */
function saveStateToSession() {
    const state = {
        airportId: document.getElementById('airport-select').value,
        dateDepot: document.getElementById('date-depot').value,
        heureDepot: document.getElementById('heure-depot').value,
        dateRecuperation: document.getElementById('date-recuperation').value,
        heureRecuperation: document.getElementById('heure-recuperation').value,
        isBaggageStepVisible: document.getElementById('baggage-selection-step').style.display === 'block',
        isStickyWrapperVisible: document.getElementById('sticky-wrapper').style.display === 'block',
        cartItems: cartItems,
        globalProductsData: globalProductsData,
        globalLieuxData: globalLieuxData,
        guestEmail: guestEmail
    };
    sessionStorage.setItem('formState', JSON.stringify(state));
}

/**
 * Charge l'état du formulaire depuis la session du navigateur.
 */
function loadStateFromSession() {
    const state = JSON.parse(sessionStorage.getItem('formState'));
    if (!state) return;

    const airportSelect = document.getElementById('airport-select');
    const dateDepotInput = document.getElementById('date-depot');
    const heureDepotInput = document.getElementById('heure-depot');
    const dateRecupInput = document.getElementById('date-recuperation');
    const heureRecupInput = document.getElementById('heure-recuperation');

    airportSelect.value = state.airportId;
    airportId = state.airportId;
    dateDepotInput.value = state.dateDepot;
    heureDepotInput.value = state.heureDepot;
    dateRecupInput.value = state.dateRecuperation;
    heureRecupInput.value = state.heureRecuperation;

    // Apply validation states based on loaded values
    if (typeof validateField !== 'undefined') {
        validateField(airportSelect);
        validateField(dateDepotInput);
        validateField(heureDepotInput);
        validateField(dateRecupInput);
        validateField(heureRecupInput);
    }

    globalProductsData = state.globalProductsData || [];
    globalLieuxData = state.globalLieuxData || [];
    cartItems = state.cartItems || [];
    guestEmail = state.guestEmail || null;

    if (state.isBaggageStepVisible) {
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('baggage-selection-step').style.display = 'block';
        document.getElementById('back-to-step-1-btn').classList.remove('hidden');
        displaySelectedDates(); // Assurez-vous que cette fonction est disponible globalement

        const dateDepot = document.getElementById('date-depot').value;
        const heureDepot = document.getElementById('heure-depot').value;
        const dateRecuperation = document.getElementById('date-recuperation').value;
        const heureRecuperation = document.getElementById('heure-recuperation').value;
        const debut = new Date(`${dateDepot}T${heureDepot}:00`);
        const fin = new Date(`${dateRecuperation}T${heureRecuperation}:00`);
        const dureeEnMinutes = Math.ceil(Math.abs(fin - debut) / (1000 * 60));

        if (dureeEnMinutes > 0) {
            // Assurez-vous que cette fonction est disponible
            if(typeof displayOptions !== 'undefined') {
                displayOptions(dureeEnMinutes);
            }
        }
    }

    // Restaurer la visibilité de sticky-wrapper
    if (state.isStickyWrapperVisible) {
        document.getElementById('sticky-wrapper').style.display = 'block';
    } else {
        document.getElementById('sticky-wrapper').style.display = 'none';
    }

    // Assurez-vous que cette fonction est disponible
    if(typeof updateCartDisplay !== 'undefined') {
        updateCartDisplay();
    }
    
    // Les contraintes seront recalculées lors du checkAvailability
    // Ne pas les restaurer depuis sessionStorage car elles dépendent des dates/heures
}