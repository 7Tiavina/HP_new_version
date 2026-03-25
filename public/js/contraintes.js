/**
 * Gestion des contraintes (prestations complémentaires obligatoires)
 * pour les réservations en dehors des heures d'ouverture normales.
 */

// Variable globale pour stocker les contraintes actuelles
window.bookingContraintesItems = [];

// Clés pour le sessionStorage
const CONTRAINTES_STORAGE_KEY = 'hp_booking_contraintes';
const CONTRAINTES_DATES_KEY = 'hp_booking_contraintes_dates';

/**
 * Sauvegarde les contraintes dans le sessionStorage
 */
function saveContraintesToSession() {
    try {
        const data = {
            contraintes: window.bookingContraintesItems,
            timestamp: Date.now()
        };
        sessionStorage.setItem(CONTRAINTES_STORAGE_KEY, JSON.stringify(data));
        console.log('Contraintes sauvegardées dans sessionStorage:', data);
    } catch (e) {
        console.error('Erreur sauvegarde contraintes dans sessionStorage:', e);
    }
}

/**
 * Récupère les contraintes depuis le sessionStorage
 * @returns {Array|null}
 */
function loadContraintesFromSession() {
    try {
        const dataStr = sessionStorage.getItem(CONTRAINTES_STORAGE_KEY);
        if (!dataStr) return null;
        
        const data = JSON.parse(dataStr);
        // Expirer après 30 minutes
        const maxAge = 30 * 60 * 1000;
        if (Date.now() - data.timestamp > maxAge) {
            console.log('Contraintes expirées dans sessionStorage');
            sessionStorage.removeItem(CONTRAINTES_STORAGE_KEY);
            return null;
        }
        
        console.log('Contraintes chargées depuis sessionStorage:', data.contraintes);
        return data.contraintes || [];
    } catch (e) {
        console.error('Erreur chargement contraintes depuis sessionStorage:', e);
        return null;
    }
}

/**
 * Sauvegarde les dates utilisées pour le calcul des contraintes
 */
function saveContraintesDatesToSession(dateDepot, heureDepot, dateRetrait, heureRetrait) {
    try {
        const dates = { dateDepot, heureDepot, dateRetrait, heureRetrait };
        sessionStorage.setItem(CONTRAINTES_DATES_KEY, JSON.stringify(dates));
    } catch (e) {
        console.error('Erreur sauvegarde dates contraintes:', e);
    }
}

/**
 * Vérifie si les dates ont changé depuis le dernier calcul des contraintes
 */
function haveContraintesDatesChanged(dateDepot, heureDepot, dateRetrait, heureRetrait) {
    try {
        const datesStr = sessionStorage.getItem(CONTRAINTES_DATES_KEY);
        if (!datesStr) return true;
        
        const dates = JSON.parse(datesStr);
        return dates.dateDepot !== dateDepot || 
               dates.heureDepot !== heureDepot || 
               dates.dateRetrait !== dateRetrait || 
               dates.heureRetrait !== heureRetrait;
    } catch (e) {
        return true;
    }
}

/**
 * Récupère les contraintes depuis l'API BDM /commande/contraintes
 * @param {string} airportId - ID de la plateforme
 * @param {Array} baggagesForOptionsQuote - Liste des bagages pour la commande
 * @returns {Promise<Array>} - Liste des contraintes
 */
async function fetchContraintes(airportId, baggagesForOptionsQuote) {
    if (!airportId || !baggagesForOptionsQuote || baggagesForOptionsQuote.length === 0) {
        console.log('Pas de contraintes à récupérer (données insuffisantes)');
        return [];
    }

    try {
        // Construire les commandeLignes pour l'API
        const commandeLignes = baggagesForOptionsQuote.map(item => {
            return {
                idProduit: item.productId,
                idService: item.serviceId || 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7',
                dateDebut: item.dateDebut,
                dateFin: item.dateFin,
                quantite: item.quantity || 1,
                prixTTC: 0,
                prixTTCAvantRemise: 0,
                tauxRemise: 0
            };
        });

        // Données client minimales (temporaires pour le calcul des contraintes)
        const client = {
            email: "temp@hellopassenger.com",
            telephone: "0000000000",
            nom: "Passager",
            prenom: "Temp",
            civilite: "M.",
            nomSociete: "",
            adresse: "Adresse inconnue",
            complementAdresse: "",
            ville: "Ville inconnue",
            codePostal: "00000",
            pays: "FRA"
        };

        // Informations commande minimales
        const commandeInfos = {
            modeTransport: "Inconnu",
            lieu: "Inconnu",
            commentaires: "Demande de contraintes"
        };

        console.log('Appel API /commande/contraintes avec:', {
            idPlateforme: airportId,
            commandeLignes: commandeLignes
        });

        // Appel à l'API /plateforme/{id}/commande/contraintes
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const response = await fetch(`/api/plateforme/${airportId}/commande/contraintes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                commandeLignes: commandeLignes,
                commandeOptions: [],
                commandeInfos: commandeInfos,
                client: client
            })
        });

        if (!response.ok) {
            console.error('Erreur HTTP lors de la récupération des contraintes:', response.status);
            return [];
        }

        const result = await response.json();
        console.log('Réponse API contraintes:', result);

        // Vérifier le statut de la réponse
        if (result.statut !== 1 || !result.content || !Array.isArray(result.content)) {
            console.log('Aucune contrainte retournée par l\'API ou statut échec');
            return [];
        }

        // Transformer les contraintes de l'API en items utilisables
        const contraintes = result.content.map(contrainte => {
            const prixUnitaire = parseFloat(contrainte.prixUnitaire ?? contrainte.prix_unitaire ?? contrainte.prix ?? 0) || 0;
            
            return {
                itemCategory: 'contrainte',
                id: contrainte.id ?? '',
                libelle: contrainte.libelle ?? contrainte.Libelle ?? 'Prestation obligatoire',
                prix: prixUnitaire,
                prixUnitaire: prixUnitaire,
                prixUnitaireAvantRemise: contrainte.prixUnitaireAvantRemise ?? null,
                tauxRemise: contrainte.tauxRemise ?? null,
                referenceInterne: contrainte.referenceInterne ?? null,
                isMandatory: true,
                // Déterminer le type (depot/retrait) selon le libellé ou autre critère
                type: (contrainte.libelle || '').toLowerCase().includes('dépôt') || 
                      (contrainte.libelle || '').toLowerCase().includes('depot') ? 'depot' : 
                      (contrainte.libelle || '').toLowerCase().includes('retrait') ? 'retrait' : 'autre'
            };
        });

        console.log('Contraintes récupérées depuis l\'API:', contraintes);
        return contraintes;

    } catch (error) {
        console.error('Erreur lors de la récupération des contraintes:', error);
        return [];
    }
}

/**
 * Met à jour les contraintes dans le panier
 * @param {string} airportId - ID de la plateforme
 * @param {Array} baggagesForOptionsQuote - Liste des bagages
 * @param {boolean} forceRefresh - Forcer le recalcul même si les dates n'ont pas changé
 */
async function updateContraintesInCart(airportId, baggagesForOptionsQuote, forceRefresh = false) {
    console.log('[updateContraintesInCart] Called with forceRefresh:', forceRefresh);
    console.log('[updateContraintesInCart] Current window.bookingContraintesItems:', window.bookingContraintesItems);
    
    // Vérifier si on peut utiliser le sessionStorage
    const dateDepot = document.getElementById('date-depot')?.value;
    const heureDepot = document.getElementById('heure-depot')?.value;
    const dateRetrait = document.getElementById('date-recuperation')?.value;
    const heureRetrait = document.getElementById('heure-recuperation')?.value;
    
    // Si forceRefresh ou si les dates ont changé, on doit appeler l'API
    const datesChanged = haveContraintesDatesChanged(dateDepot, heureDepot, dateRetrait, heureRetrait);
    
    console.log('[updateContraintesInCart] Dates changed:', datesChanged);
    
    // Seulement utiliser le cache si pas de forceRefresh et dates identiques
    if (!forceRefresh && !datesChanged) {
        const cachedContraintes = loadContraintesFromSession();
        if (cachedContraintes && cachedContraintes.length > 0) {
            window.bookingContraintesItems = cachedContraintes;
            console.log('[updateContraintesInCart] Contraintes utilisées depuis sessionStorage:', cachedContraintes);
            
            // Mettre à jour l'affichage du panier
            if (typeof updateCartDisplay === 'function') {
                updateCartDisplay();
            }
            if (typeof updateDrawerCart === 'function') {
                updateDrawerCart();
            }
            return cachedContraintes;
        }
    }
    
    // Si on arrive ici, c'est qu'on doit appeler l'API
    console.log('[updateContraintesInCart] Appel API nécessaire (forceRefresh ou dates changées)');
    
    // Appeler l'API pour obtenir les contraintes à jour
    const contraintes = await fetchContraintes(airportId, baggagesForOptionsQuote);
    
    // Stocker dans la variable globale
    window.bookingContraintesItems = contraintes;
    
    // Sauvegarder dans le sessionStorage SEULEMENT s'il y a des contraintes
    if (contraintes.length > 0) {
        saveContraintesToSession();
        if (dateDepot && heureDepot && dateRetrait && heureRetrait) {
            saveContraintesDatesToSession(dateDepot, heureDepot, dateRetrait, heureRetrait);
        }
    } else {
        // Si pas de contraintes, on nettoie le sessionStorage
        console.log('[updateContraintesInCart] Pas de contraintes, nettoyage sessionStorage');
        sessionStorage.removeItem(CONTRAINTES_STORAGE_KEY);
        sessionStorage.removeItem(CONTRAINTES_DATES_KEY);
    }
    
    console.log('[updateContraintesInCart] Contraintes stockées:', contraintes);

    // Mettre à jour l'affichage du panier si la fonction existe
    if (typeof updateCartDisplay === 'function') {
        updateCartDisplay();
    }

    // Mettre à jour le drawer si la fonction existe
    if (typeof updateDrawerCart === 'function') {
        updateDrawerCart();
    }

    return contraintes;
}

/**
 * Vérifie s'il y a des contraintes actives
 * @returns {boolean}
 */
function hasContraintes() {
    return window.bookingContraintesItems && window.bookingContraintesItems.length > 0;
}

/**
 * Calcule le total des contraintes
 * @returns {number}
 */
function getContraintesTotal() {
    if (!window.bookingContraintesItems || !Array.isArray(window.bookingContraintesItems)) {
        return 0;
    }
    return window.bookingContraintesItems.reduce((sum, item) => sum + (item.prix || 0), 0);
}

/**
 * Réinitialise les contraintes (à appeler quand on change les dates)
 */
function resetContraintes() {
    window.bookingContraintesItems = [];
    sessionStorage.removeItem(CONTRAINTES_STORAGE_KEY);
    sessionStorage.removeItem(CONTRAINTES_DATES_KEY);
    if (typeof updateCartDisplay === 'function') {
        updateCartDisplay();
    }
}

/**
 * Initialise les contraintes au chargement de la page
 * @param {string} airportId - ID de la plateforme
 */
async function initContraintes(airportId) {
    console.log('[initContraintes] Called with airportId:', airportId);
    
    if (!airportId) {
        console.log('[initContraintes] airportId is null or undefined, skipping initialization');
        return false;
    }
    
    // NE PAS restaurer les contraintes depuis sessionStorage au chargement
    // Les contraintes doivent être recalculées en fonction des dates/heures actuelles
    // Elles seront calculées lors du checkAvailability ou updateContraintesInCart
    
    console.log('[initContraintes] Skip restoration, will be calculated on checkAvailability');
    return false;
}
