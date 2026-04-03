/**
 * Quick Date Modal - Utilise EXACTEMENT les mêmes pickers que le formulaire principal
 */

let qdmEscapeHandler;

// Use global t function
if (typeof t === 'undefined') {
    var t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));
}

function openQuickDateModal() {
    // Copier les valeurs actuelles depuis le formulaire principal
    const depotDate = document.getElementById('date-depot').value;
    const depotHeure = document.getElementById('heure-depot').value;
    const retraitDate = document.getElementById('date-recuperation').value;
    const retraitHeure = document.getElementById('heure-recuperation').value;

    // Définir les valeurs dans les inputs de la modale
    document.getElementById('qdm-date-depot').value = depotDate || '';
    document.getElementById('qdm-date-recuperation').value = retraitDate || '';
    document.getElementById('heure-qdm-depot').value = depotHeure || '';
    document.getElementById('heure-qdm-recuperation').value = retraitHeure || '';

    // Mettre à jour les scroll wheels si elles existent
    if (depotHeure) {
        const [h, m] = depotHeure.split(':');
        setScrollWheelValue('qdm-depot', h, m);
    } else {
        setScrollWheelValue('qdm-depot', '09', '00');
    }

    if (retraitHeure) {
        const [h, m] = retraitHeure.split(':');
        setScrollWheelValue('qdm-recuperation', h, m);
    } else {
        setScrollWheelValue('qdm-recuperation', '18', '00');
    }

    // Appliquer les mêmes contraintes que le formulaire principal
    applyQdmDateConstraints();

    // Afficher la modale
    document.getElementById('quick-date-modal').classList.remove('hidden');

    // Initialiser les scroll wheels après l'affichage
    setTimeout(() => {
        if (typeof initScrollWheelsIfNeeded === 'function') {
            initScrollWheelsIfNeeded();
        }
    }, 100);

    // Fermer avec Escape
    const qdmModal = document.getElementById('quick-date-modal');
    const removeEscape = () => {
        document.removeEventListener('keydown', qdmEscapeHandler);
    };
    qdmEscapeHandler = function(e) {
        if (e.key === 'Escape') {
            qdmModal.classList.add('hidden');
            removeEscape();
        }
    };
    document.addEventListener('keydown', qdmEscapeHandler);
}

function setScrollWheelValue(suffix, hour, minute) {
    // Set value on hours wheel (cycle 1 = middle cycle)
    const hWheel = document.getElementById(`scroll-h-${suffix}`);
    if (hWheel) {
        const hItem = hWheel.querySelector(`[data-value="${hour}"]`);
        if (hItem) {
            // Use the item from cycle 1 (middle)
            const allHItems = hWheel.querySelectorAll(`[data-value="${hour}"]`);
            const targetItem = allHItems[1] || hItem; // cycle 1
            targetItem.parentElement.scrollTop = targetItem.offsetTop - 40;
        }
    }
    
    // Set value on minutes wheel (cycle 1 = middle cycle)
    const mWheel = document.getElementById(`scroll-m-${suffix}`);
    if (mWheel) {
        const mItem = mWheel.querySelector(`[data-value="${minute}"]`);
        if (mItem) {
            const allMItems = mWheel.querySelectorAll(`[data-value="${minute}"]`);
            const targetItem = allMItems[1] || mItem; // cycle 1
            targetItem.parentElement.scrollTop = targetItem.offsetTop - 40;
        }
    }
}

function applyQdmDateConstraints() {
    const today = new Date();
    const pad = (num) => num.toString().padStart(2, '0');
    const todayFormatted = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
    
    const qdmDateDepotInput = document.getElementById('qdm-date-depot');
    const qdmDateRecupInput = document.getElementById('qdm-date-recuperation');
    
    // Contraintes de date
    qdmDateDepotInput.min = todayFormatted;
    
    // Si la date de récupération existe, l'utiliser comme max pour dépôt
    const existingRecupDate = document.getElementById('qdm-date-recuperation').value;
    if (existingRecupDate) {
        qdmDateDepotInput.max = existingRecupDate;
    }
    
    // La date de récupération doit être >= date de dépôt
    const existingDepotDate = document.getElementById('qdm-date-depot').value;
    if (existingDepotDate) {
        qdmDateRecupInput.min = existingDepotDate;
    } else {
        qdmDateRecupInput.min = todayFormatted;
    }
}

// Fonction pour valider et appliquer les nouvelles dates
async function validateQdmDates() {
    // Vérifier que airportId est défini
    if (typeof airportId === 'undefined' || !airportId) {
        await showCustomAlert(t('alert_missing_data_title'), 'Aéroport non sélectionné. Veuillez sélectionner un aéroport d\'abord.');
        return;
    }

    const newDepotDate = document.getElementById('qdm-date-depot').value;
    const newDepotHeure = document.getElementById('heure-qdm-depot').value;
    const newRetraitDate = document.getElementById('qdm-date-recuperation').value;
    const newRetraitHeure = document.getElementById('heure-qdm-recuperation').value;

    // Validation des champs requis
    if (!newDepotDate || !newDepotHeure || !newRetraitDate || !newRetraitHeure) {
        await showCustomAlert(t('alert_missing_data_title'), t('alert_missing_data_message'));
        return;
    }

    // Validation : date de retrait > date de dépôt
    const qdm_temp_depot_date = new Date(`${newDepotDate}T${newDepotHeure}`);
    const qdm_temp_retrait_date = new Date(`${newRetraitDate}T${newRetraitHeure}`);

    if (qdm_temp_retrait_date <= qdm_temp_depot_date) {
        await showCustomAlert(t('date_invalid_title'), t('date_invalid_after_dropoff'));
        return;
    }

    // Validation : minimum 3h entre dépôt et retrait
    if (qdm_temp_depot_date.toDateString() === qdm_temp_retrait_date.toDateString()) {
        const diffInMs = qdm_temp_retrait_date - qdm_temp_depot_date;
        const diffInHours = diffInMs / (1000 * 60 * 60);
        if (diffInHours < 3) {
            await showCustomAlert(t('date_invalid_title'), t('date_invalid_same_day_min'));
            return;
        }
    }

    // Appliquer les nouvelles valeurs au formulaire principal
    const pad = (num) => num.toString().padStart(2, '0');
    document.getElementById('date-depot').value = newDepotDate;
    document.getElementById('heure-depot').value = newDepotHeure;
    document.getElementById('date-recuperation').value = newRetraitDate;
    document.getElementById('heure-recuperation').value = newRetraitHeure;

    // Mettre à jour l'affichage des dates sélectionnées
    if (typeof displaySelectedDates === 'function') {
        displaySelectedDates();
    }

    // Réinitialiser les contraintes avant de recalculer
    if (typeof resetContraintes === 'function') {
        resetContraintes();
    }

    // Fermer la modale d'abord
    document.getElementById('quick-date-modal').classList.add('hidden');
    if (typeof qdmEscapeHandler === 'function') {
        document.removeEventListener('keydown', qdmEscapeHandler);
    }

    // Maintenant vérifier la disponibilité (avec les nouvelles valeurs déjà appliquées)
    const isAvailable = await checkAvailability();

    if (isAvailable) {
        // Recalculer les tarifs
        if (typeof getQuoteAndDisplay === 'function') {
            await getQuoteAndDisplay();
        }

        // Sauvegarder l'état
        if (typeof saveStateToSession === 'function') {
            saveStateToSession();
        }
    } else {
        // Si pas disponible, afficher un message puis rouvrir la modale
        await showCustomAlert(
            t('alert_agency_closed_title', 'Horaires non disponibles'),
            t('alert_hours_not_available', 'Les horaires sélectionnés ne sont pas disponibles. Veuillez choisir d\'autres horaires.')
        );
        // Rouvrir la modale pour permettre de choisir d'autres horaires
        openQuickDateModal();
    }
}

// Initialisation des écouteurs d'événements
document.addEventListener('DOMContentLoaded', function() {
    // Bouton de validation
    const validateBtn = document.getElementById('qdm-validate-btn');
    if (validateBtn) {
        validateBtn.addEventListener('click', validateQdmDates);
    }
    
    // Bouton de fermeture
    const closeBtn = document.getElementById('close-quick-date-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            document.getElementById('quick-date-modal').classList.add('hidden');
        });
    }
    
    // Écouteurs pour les changements de date (mettre à jour les contraintes)
    const qdmDateDepot = document.getElementById('qdm-date-depot');
    const qdmDateRecup = document.getElementById('qdm-date-recuperation');
    
    if (qdmDateDepot) {
        qdmDateDepot.addEventListener('change', () => {
            // Mettre à jour la contrainte max pour dépôt
            const recupValue = document.getElementById('qdm-date-recuperation').value;
            if (recupValue) {
                qdmDateDepot.max = recupValue;
            }
            // Mettre à jour la contrainte min pour récupération
            document.getElementById('qdm-date-recuperation').min = qdmDateDepot.value;
        });
    }
    
    if (qdmDateRecup) {
        qdmDateRecup.addEventListener('change', () => {
            // Mettre à jour la contrainte max pour dépôt
            document.getElementById('qdm-date-depot').max = qdmDateRecup.value;
        });
    }
});
