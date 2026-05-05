/**
 * Cart display and session sync.
 * updateCartDisplay is called from booking.js, modal.js and state.js.
 * API doc: products have id, libelle, prixUnitaire (or prix_unitaire); options have libelle, prix.
 */
function updateCartDisplay() {
    const container = document.getElementById('cart-items-container');
    const emptyCart = document.getElementById('empty-cart');
    const cartSummary = document.getElementById('cart-summary');
    const summaryPrice = document.getElementById('summary-price');
    const totalPanier = document.querySelector('.total-panier');
    const cartSubtotal = document.getElementById('cart-subtotal');
    const cartDiscount = document.getElementById('cart-discount');

    if (!container) return;

    var items = (typeof cartItems !== 'undefined' && Array.isArray(cartItems)) ? cartItems : [];
    
    // Ajouter les contraintes obligatoires si elles existent
    const contraintesItems = (typeof window.bookingContraintesItems !== 'undefined' && Array.isArray(window.bookingContraintesItems)) ? window.bookingContraintesItems : [];
    
    // Fusionner les items du panier avec les contraintes
    var allItems = [...items, ...contraintesItems];
    
    if (allItems.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (cartSummary) cartSummary.style.display = 'none';
        if (summaryPrice) summaryPrice.textContent = '0 €';
        if (totalPanier) totalPanier.textContent = '0€';
        container.innerHTML = '';
        if (typeof saveStateToSession === 'function') saveStateToSession();
        return;
    }

    if (emptyCart) emptyCart.style.display = 'none';
    if (cartSummary) cartSummary.style.display = 'block';

    var products = (typeof globalProductsData !== 'undefined' && Array.isArray(globalProductsData)) ? globalProductsData : [];
    function productById(id) {
        var s = id != null ? String(id) : '';
        return products.find(function (p) { return (p.id != null ? String(p.id) : '') === s; }) || null;
    }
    function productByLibelle(libelle) {
        if (!libelle || !products.length) return null;
        var norm = String(libelle).trim().toLowerCase();
        return products.find(function (p) {
            var pl = (p.libelle || p.nom || '').trim().toLowerCase();
            return pl && pl === norm;
        }) || null;
    }
    function unitPrice(p) {
        if (!p) return 0;
        var n = p.prixUnitaire ?? p.prix_unitaire ?? p.prixTTC ?? p.prix_ttc ?? p.price ?? p.unitPrice;
        if (typeof n === 'number' && !isNaN(n)) return n;
        if (typeof n === 'string') { var parsed = parseFloat(n.replace(',', '.')); if (!isNaN(parsed)) return parsed; }
        return 0;
    }
    /** Prix unitaire avant remise. Utilise prixUnitaireAvantRemise si présent, sinon calcule à partir de tauxRemise. */
    function unitPriceBeforeDiscount(p) {
        if (!p) return null;
        var avant = p.prixUnitaireAvantRemise ?? p.prix_unitaire_avant_remise;
        if (avant != null && avant !== '') {
            var n = typeof avant === 'number' ? avant : parseFloat(String(avant).replace(',', '.'));
            if (!isNaN(n)) return n;
        }
        var taux = p.tauxRemise ?? p.taux_remise;
        if (taux != null && taux !== '') {
            var t = typeof taux === 'number' ? taux : parseFloat(String(taux).replace(',', '.'));
            if (!isNaN(t) && t > 0 && t < 100) {
                var u = unitPrice(p);
                if (u > 0) return Math.round((u / (1 - t / 100)) * 100) / 100;
            }
        }
        return null;
    }
    function productLibelle(p) {
        return (p && (p.libelle || p.nom)) || '';
    }
    /** Taux de remise en pourcentage */
    function discountRate(p) {
        if (!p) return 0;
        var t = p.tauxRemise ?? p.taux_remise;
        if (t != null && t !== '') {
            var taux = typeof t === 'number' ? t : parseFloat(String(t).replace(',', '.'));
            if (!isNaN(taux) && taux > 0 && taux < 100) return taux;
        }
        return 0;
    }

    var total = 0;
    var subtotalNormal = 0;  /* total avant remise (pour affichage "Total normal") */
    var totalSavings = 0;    /* économies totales */
    var fragments = [];

    allItems.forEach(function (item, index) {
        var libelle = item.libelle || '';
        var linePrice = 0;
        var lineNormal = 0;
        var unitPriceValue = 0;
        var unitPriceBeforeDiscountValue = null;
        var lineDiscountRate = 0;
        var isMandatory = item.isMandatory || item.itemCategory === 'contrainte';

        if (item.itemCategory === 'baggage') {
            var product = productById(item.productId) || (item.libelle ? productByLibelle(item.libelle) : null);
            unitPriceValue = unitPrice(product);
            unitPriceBeforeDiscountValue = product ? unitPriceBeforeDiscount(product) : null;
            lineDiscountRate = product ? discountRate(product) : 0;
            var qty = item.quantity || 0;
            linePrice = qty * unitPriceValue;
            if (unitPriceBeforeDiscountValue != null && unitPriceBeforeDiscountValue > unitPriceValue) {
                lineNormal += qty * unitPriceBeforeDiscountValue;
            } else {
                lineNormal += linePrice;
            }
            libelle = product ? productLibelle(product) : (libelle || ('Produit #' + (item.productId || index)));
            
            // Translate the libelle if possible
            var translationMap = {
                'Accessoires': 'luggage_accessoires',
                'Bagage cabine': 'luggage_bagage_cabine',
                'Bagage soute': 'luggage_bagage_soute',
                'Bagage spécial': 'luggage_bagage_special',
                'Vestiaire': 'luggage_vestiaire'
            };
            var originalLibelle = libelle;
            // Remove quantity prefix for translation lookup
            var libelleForTranslation = libelle.replace(/^\d+\s*×\s*/, '');
            if (translationMap[libelleForTranslation]) {
                libelle = typeof window.t === 'function' ? window.t(translationMap[libelleForTranslation], libelleForTranslation) : libelleForTranslation;
            } else {
                libelle = libelleForTranslation;
            }
            // Re-add quantity prefix if needed
            if (qty > 1) {
                libelle = qty + ' × ' + libelle;
            }
        } else if (item.itemCategory === 'option') {
            unitPriceValue = (typeof item.prix === 'number' && !isNaN(item.prix)) ? item.prix : 0;
            unitPriceBeforeDiscountValue = (typeof item.prixUnitaireAvantRemise === 'number' && !isNaN(item.prixUnitaireAvantRemise)) ? item.prixUnitaireAvantRemise :
                                           (typeof item.prix_ttc_avant_remise === 'number' && !isNaN(item.prix_ttc_avant_remise)) ? item.prix_ttc_avant_remise : unitPriceValue;
            linePrice = unitPriceValue;
            lineNormal = unitPriceBeforeDiscountValue;
            // Pour les options, calculer le taux de remise
            if (unitPriceBeforeDiscountValue > unitPriceValue && unitPriceBeforeDiscountValue > 0) {
                lineDiscountRate = Math.round(((unitPriceBeforeDiscountValue - unitPriceValue) / unitPriceBeforeDiscountValue) * 100);
            }
            
            // Use API labels for options (priority/premium)
            // Removed translations that were overriding API labels
        } else if (item.itemCategory === 'contrainte') {
            // Contrainte obligatoire - prix déjà défini dans l'item
            unitPriceValue = (typeof item.prix === 'number' && !isNaN(item.prix)) ? item.prix : 0;
            
            // Calculer le prix avant remise
            var avantRemise = item.prixUnitaireAvantRemise ?? item.prix_ttc_avant_remise;
            if (avantRemise != null && avantRemise !== '') {
                unitPriceBeforeDiscountValue = typeof avantRemise === 'number' ? avantRemise : parseFloat(String(avantRemise).replace(',', '.'));
            } else {
                // Calculer à partir du taux de remise si disponible
                var taux = item.tauxRemise ?? item.taux_remise;
                if (taux != null && taux !== '') {
                    var t = typeof taux === 'number' ? taux : parseFloat(String(taux).replace(',', '.'));
                    if (!isNaN(t) && t > 0 && t < 100 && unitPriceValue > 0) {
                        unitPriceBeforeDiscountValue = Math.round((unitPriceValue / (1 - t / 100)) * 100) / 100;
                    } else {
                        unitPriceBeforeDiscountValue = unitPriceValue;
                    }
                } else {
                    unitPriceBeforeDiscountValue = unitPriceValue;
                }
            }
            
            linePrice = unitPriceValue;
            lineNormal = unitPriceBeforeDiscountValue;
            
            // Calculer le taux de remise pour les contraintes
            if (unitPriceBeforeDiscountValue > unitPriceValue && unitPriceBeforeDiscountValue > 0) {
                lineDiscountRate = Math.round(((unitPriceBeforeDiscountValue - unitPriceValue) / unitPriceBeforeDiscountValue) * 100);
            }
            
            // Ajouter un indicateur visuel pour les contraintes obligatoires
            var mandatoryText = typeof window.t === 'function' ? window.t('cart_mandatory_badge', '(obligatoire)') : '(obligatoire)';
            libelle = libelle + ' <span class="text-xs text-orange-600 font-semibold">' + mandatoryText + '</span>';
        }

        total += linePrice;
        subtotalNormal += lineNormal;
        totalSavings += (lineNormal - linePrice);

        var hasDiscount = unitPriceBeforeDiscountValue != null && unitPriceBeforeDiscountValue > unitPriceValue && lineDiscountRate > 0;

        var row = document.createElement('div');
        row.className = 'cart-item flex justify-between items-start py-3 border-b border-gray-150 last:border-0';

        // Partie gauche : nom du produit uniquement (plus lisible)
        var leftHtml = '<div class="flex-1 pr-3 min-w-0">';
        leftHtml += '<div class="flex items-center gap-2">';
        // Pour les contraintes, ne pas échapper le HTML (contient déjà des balises <span>)
        if (item.itemCategory === 'contrainte') {
            leftHtml += '<span class="text-sm font-medium text-gray-800 break-words">' + libelle + '</span>';
        } else {
            leftHtml += '<span class="text-sm font-medium text-gray-800 break-words">' + escapeHtml(libelle) + '</span>';
        }
        leftHtml += '</div>';

        // Afficher la description pour Priority et Premium seulement
        if ((item.key === 'priority' || item.key === 'premium') && item.description) {
            leftHtml += '<div class="text-xs text-gray-500 mt-1 leading-tight">' + escapeHtml(item.description) + '</div>';
        }

        leftHtml += '</div>';

        // Partie droite : badge remise + prix + bouton supprimer (tous alignés à droite)
        var rightHtml = '<div class="price-wrapper flex flex-col items-end gap-1 flex-shrink-0" style="min-width: 120px;">';
        // Première ligne : badge remise (si applicable)
        if (hasDiscount) {
            rightHtml += '<div class="flex items-center gap-2 justify-end">';
            rightHtml += '<span class="badge-promo inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 flex-shrink-0" style="min-width: 42px; justify-content: center; font-size: 11px;">-' + lineDiscountRate.toFixed(0) + '%</span>';
            rightHtml += '<span class="old-price text-xs text-gray-400 line-through flex-shrink-0 text-right" style="min-width: 65px;">' + formatPrice(lineNormal) + '</span>';
            rightHtml += '</div>';
        }
        // Deuxième ligne : prix actuel + bouton supprimer
        rightHtml += '<div class="flex items-center gap-1 justify-end">';
        rightHtml += '<span class="current-price text-sm font-bold text-gray-900 flex-shrink-0 text-right" style="min-width: 65px;">' + formatPrice(linePrice) + '</span>';
        // Bouton de suppression - pas pour les contraintes obligatoires
        if (!isMandatory) {
            rightHtml += '<button type="button" class="delete-item-btn text-red-500 hover:text-red-700 p-1 flex-shrink-0" data-index="' + index + '" aria-label="Supprimer">' +
                '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                '</button>';
        }
        rightHtml += '</div>';
        rightHtml += '</div>';

        row.innerHTML = leftHtml + rightHtml;
        fragments.push(row);
    });

    container.innerHTML = '';
    fragments.forEach(function (el) { container.appendChild(el); });

    var totalStr = formatPrice(total);
    if (summaryPrice) summaryPrice.textContent = totalStr;
    if (totalPanier) totalPanier.textContent = totalStr;

    var discountAmount = subtotalNormal > total ? Math.round((subtotalNormal - total) * 100) / 100 : 0;
    var hasRemise = discountAmount >= 0.01;

    // Get discount rate from first product with remise
    var discountRate = 0;
    if (products.length > 0) {
        var firstWithRemise = products.find(function (p) {
            var t = p.tauxRemise ?? p.taux_remise;
            return t != null && t !== '' && Number(t) > 0;
        });
        if (firstWithRemise) {
            discountRate = firstWithRemise.tauxRemise ?? firstWithRemise.taux_remise ?? 0;
        }
    }

    if (typeof console !== 'undefined' && console.info && products.length > 0) {
        // [Remises panier] info removed
    }

    // Affichage du sous-total et des économies
    if (cartSubtotal) {
        cartSubtotal.style.display = 'none';
    }
    if (cartDiscount) {
        var discountTextEl = cartDiscount.querySelector('.discount-text');
        var discountAmountEl = cartDiscount.querySelector('.discount-amount');
        if (discountTextEl && discountRate > 0) {
            // Use translation if available
            var discountText = 'Offre réservation en ligne';
            if (typeof window.translateKey === 'function') {
                discountText = window.translateKey('cart_discount_online', 'Offre réservation en ligne');
            }
            discountTextEl.textContent = discountText + ' (-' + Number(discountRate).toFixed(0) + '%)';
        }
        if (discountAmountEl) discountAmountEl.textContent = (hasRemise ? '-' : '') + formatPrice(discountAmount);
        cartDiscount.style.display = hasRemise ? 'flex' : 'none';
    }
    
    // Affichage de la ligne "Vous économisez" - supprimé
    var savingsRow = document.getElementById('cart-savings');
    var savingsAmount = document.getElementById('cart-savings-amount');
    if (savingsRow) savingsRow.style.display = 'none';

    if (typeof saveStateToSession === 'function') saveStateToSession();
}

function formatPrice(amount) {
    if (typeof amount !== 'number' || isNaN(amount)) return '0 €';
    return amount.toFixed(2).replace('.', ',') + ' €';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
