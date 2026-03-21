/**
 * Gestion des contraintes (prestations complémentaires obligatoires)
 * pour les réservations en dehors des heures d'ouverture normales.
 */

// Variable globale pour stocker les contraintes actuelles
window.bookingContraintesItems = [];

/**
 * Récupère les contraintes depuis les résultats de disponibilité déjà stockés
 * @param {string} airportId - ID de la plateforme
 * @param {Array} baggagesForOptionsQuote - Liste des bagages pour la commande
 * @returns {Promise<Array>} - Liste des contraintes
 */
async function fetchContraintes(airportId, baggagesForOptionsQuote) {
    if (!airportId || !baggagesForOptionsQuote || baggagesForOptionsQuote.length === 0) {
        console.log('Pas de contraintes à récupérer (données insuffisantes)');
        return [];
    }

    // Utiliser les contraintes déjà récupérées depuis l'API /date
    const depotContrainte = window.bookingConstraints?.depot;
    const retraitContrainte = window.bookingConstraints?.retrait;
    
    console.log('Contraintes depuis bookingConstraints:', { depotContrainte, retraitContrainte });
    
    if (!depotContrainte && !retraitContrainte) {
        console.log('Aucune contrainte détectée');
        return [];
    }

    const contraintes = [];
    
    // Ajouter la contrainte de dépôt si elle existe
    if (depotContrainte) {
        const prixUnitaire = parseFloat(depotContrainte.prixUnitaire ?? depotContrainte.prix_unitaire ?? depotContrainte.prix ?? 0) || 0;
        
        contraintes.push({
            itemCategory: 'contrainte',
            id: depotContrainte.id ?? '',
            libelle: depotContrainte.libelle ?? depotContrainte.Libelle ?? 'Prestation obligatoire (Dépôt)',
            prix: prixUnitaire,
            prixUnitaire: prixUnitaire,
            prixUnitaireAvantRemise: depotContrainte.prixUnitaireAvantRemise ?? null,
            tauxRemise: depotContrainte.tauxRemise ?? null,
            referenceInterne: depotContrainte.referenceInterne ?? null,
            isMandatory: true,
            type: 'depot'
        });
    }
    
    // Ajouter la contrainte de retrait si elle existe et différente de celle de dépôt
    if (retraitContrainte && retraitContrainte.id !== depotContrainte?.id) {
        const prixUnitaire = parseFloat(retraitContrainte.prixUnitaire ?? retraitContrainte.prix_unitaire ?? retraitContrainte.prix ?? 0) || 0;
        
        contraintes.push({
            itemCategory: 'contrainte',
            id: retraitContrainte.id ?? '',
            libelle: retraitContrainte.libelle ?? retraitContrainte.Libelle ?? 'Prestation obligatoire (Retrait)',
            prix: prixUnitaire,
            prixUnitaire: prixUnitaire,
            prixUnitaireAvantRemise: retraitContrainte.prixUnitaireAvantRemise ?? null,
            tauxRemise: retraitContrainte.tauxRemise ?? null,
            referenceInterne: retraitContrainte.referenceInterne ?? null,
            isMandatory: true,
            type: 'retrait'
        });
    }
    
    console.log('Contraintes à ajouter:', contraintes);
    return contraintes;
}

/**
 * Met à jour les contraintes dans le panier
 * @param {string} airportId - ID de la plateforme
 * @param {Array} baggagesForOptionsQuote - Liste des bagages
 */
async function updateContraintesInCart(airportId, baggagesForOptionsQuote) {
    const contraintes = await fetchContraintes(airportId, baggagesForOptionsQuote);
    
    // NE PAS ajouter aux cartItems - on les stocke juste dans une variable globale
    // Elles seront envoyées séparément au backend
    window.bookingContraintesItems = contraintes;
    console.log('Contraintes stockées (pas dans cartItems):', contraintes);
    
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
    if (typeof updateCartDisplay === 'function') {
        updateCartDisplay();
    }
}
