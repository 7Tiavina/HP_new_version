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
    if (items.length === 0) {
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

    var total = 0;
    var subtotalNormal = 0;  /* total avant remise (pour affichage "Total normal") */
    var fragments = [];

    items.forEach(function (item, index) {
        var libelle = item.libelle || '';
        var linePrice = 0;
        var lineNormal = 0;
        var unitPriceValue = 0;
        var unitPriceBeforeDiscountValue = null;

        if (item.itemCategory === 'baggage') {
            var product = productById(item.productId) || (item.libelle ? productByLibelle(item.libelle) : null);
            unitPriceValue = unitPrice(product);
            unitPriceBeforeDiscountValue = product ? unitPriceBeforeDiscount(product) : null;
            var qty = item.quantity || 0;
            linePrice = qty * unitPriceValue;
            if (unitPriceBeforeDiscountValue != null && unitPriceBeforeDiscountValue > unitPriceValue) {
                lineNormal += qty * unitPriceBeforeDiscountValue;
            } else {
                lineNormal += linePrice;
            }
            libelle = product ? productLibelle(product) : (libelle || ('Produit #' + (item.productId || index)));
            libelle = (qty > 1 ? qty + ' × ' : '') + libelle;
        } else if (item.itemCategory === 'option') {
            unitPriceValue = (typeof item.prix === 'number' && !isNaN(item.prix)) ? item.prix : 0;
            unitPriceBeforeDiscountValue = (typeof item.prixUnitaireAvantRemise === 'number' && !isNaN(item.prixUnitaireAvantRemise)) ? item.prixUnitaireAvantRemise : 
                                           (typeof item.prix_ttc_avant_remise === 'number' && !isNaN(item.prix_ttc_avant_remise)) ? item.prix_ttc_avant_remise : unitPriceValue;
            linePrice = unitPriceValue;
            lineNormal = unitPriceBeforeDiscountValue;
        }

        total += linePrice;
        subtotalNormal += lineNormal;

        var hasDiscount = unitPriceBeforeDiscountValue != null && unitPriceBeforeDiscountValue > unitPriceValue;

        var row = document.createElement('div');
        row.className = 'flex justify-between items-center py-2';
        
        var priceHtml = '';
        if (hasDiscount) {
            priceHtml = '<div class="flex items-center gap-2">' +
                        '<span class="text-xs text-gray-400 line-through">' + formatPrice(lineNormal) + '</span>' +
                        '<span class="text-sm font-semibold text-gray-900">' + formatPrice(linePrice) + '</span>' +
                        '</div>';
        } else {
            priceHtml = '<span class="text-sm font-semibold text-gray-900">' + formatPrice(linePrice) + '</span>';
        }
        
        row.innerHTML =
            '<div class="flex-1 text-sm text-gray-800">' + escapeHtml(libelle) + '</div>' +
            '<div class="flex items-center space-x-2">' +
            priceHtml +
            '<button type="button" class="delete-item-btn text-red-500 hover:text-red-700 p-1" data-index="' + index + '" aria-label="Supprimer">' +
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
            '</button>' +
            '</div>';
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
        var firstWithRemise = products.find(function (p) {
            var b = p.prixUnitaireAvantRemise ?? p.prix_unitaire_avant_remise;
            var t = p.tauxRemise ?? p.taux_remise;
            return (b != null && b !== '') || (t != null && t !== '' && Number(t) > 0);
        });
        console.info('[Remises panier] sous-total normal=', subtotalNormal, 'total=', total, 'remise=', discountAmount, 'hasRemise=', hasRemise, 'discountRate=', discountRate, 'exemple produit avec remise=', firstWithRemise ? { libelle: firstWithRemise.libelle, prixUnitaire: firstWithRemise.prixUnitaire ?? firstWithRemise.prix_unitaire, prixUnitaireAvantRemise: firstWithRemise.prixUnitaireAvantRemise ?? firstWithRemise.prix_unitaire_avant_remise, tauxRemise: firstWithRemise.tauxRemise ?? firstWithRemise.taux_remise } : 'aucun');
    }

    if (cartSubtotal) {
        var subtotalEl = cartSubtotal.querySelector('.subtotal-amount');
        if (subtotalEl) subtotalEl.textContent = formatPrice(subtotalNormal);
        cartSubtotal.style.display = hasRemise ? 'flex' : 'none';
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
