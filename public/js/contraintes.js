/**
 * Gestion des contraintes (prestations complémentaires obligatoires)
 * pour les réservations en dehors des heures d'ouverture normales.
 */

// Variable globale pour stocker les contraintes actuelles
window.bookingContraintesItems = [];

/**
 * Récupère les contraintes pour une commande donnée depuis l'API
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
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfVal = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        const commandeLignes = baggagesForOptionsQuote.map(bag => ({
            idProduit: bag.productId,
            idService: bag.serviceId,
            dateDebut: bag.dateDebut,
            dateFin: bag.dateFin,
            quantite: bag.quantity
        }));
        
        const response = await fetch(`/api/plateforme/${airportId}/commande/contraintes`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfVal 
            },
            body: JSON.stringify({
                commandeLignes: commandeLignes,
                commandeOptions: []
            })
        });

        if (!response.ok) {
            console.warn('Erreur HTTP lors de la récupération des contraintes:', response.status);
            return [];
        }

        const result = await response.json();
        console.log('Contraintes from API:', result);
        
        if (result.statut === 1 && Array.isArray(result.content)) {
            return result.content.map(contrainte => {
                const prixUnitaire = parseFloat(contrainte.prixUnitaire ?? contrainte.prix_unitaire ?? contrainte.prix ?? 0) || 0;
                
                return {
                    itemCategory: 'contrainte',
                    id: contrainte.id ?? contrainte.Id ?? '',
                    libelle: contrainte.libelle ?? contrainte.Libelle ?? 'Prestation obligatoire',
                    prix: prixUnitaire,
                    prixUnitaire: prixUnitaire,
                    prixUnitaireAvantRemise: contrainte.prixUnitaireAvantRemise ?? null,
                    tauxRemise: contrainte.tauxRemise ?? null,
                    referenceInterne: contrainte.referenceInterne ?? null,
                    isMandatory: true
                };
            });
        }
        
        return [];
    } catch (error) {
        console.warn('Erreur lors de la récupération des contraintes (non-bloquant):', error);
        return [];
    }
}

/**
 * Met à jour les contraintes dans le panier
 * @param {string} airportId - ID de la plateforme
 * @param {Array} baggagesForOptionsQuote - Liste des bagages
 */
async function updateContraintesInCart(airportId, baggagesForOptionsQuote) {
    const contraintes = await fetchContraintes(airportId, baggagesForOptionsQuote);
    window.bookingContraintesItems = contraintes;
    console.log('Contraintes mises à jour dans le panier:', contraintes);
    
    // Mettre à jour l'affichage du panier si la fonction existe
    if (typeof updateCartDisplay === 'function') {
        updateCartDisplay();
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
