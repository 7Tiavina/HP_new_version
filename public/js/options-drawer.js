/**
 * Options Side Drawer Management
 * Handles the slide-in drawer for Priority and Premium options selection
 * SIMPLIFIED VERSION - Just add/remove options, no details collection
 */

let optionsDrawerResolve = null;

/**
 * Open the options drawer
 */
function openOptionsDrawer() {
    return new Promise(resolve => {
        const drawer = document.getElementById('options-drawer');
        const overlay = document.getElementById('options-drawer-overlay');

        if (!drawer || !overlay) {
            console.error('[openOptionsDrawer] Drawer or overlay not found');
            return;
        }

        optionsDrawerResolve = resolve;

        // Hide chatbot when drawer is open
        document.body.classList.add('drawer-chatbot-hidden');
        console.log('[openOptionsDrawer] Chatbot hidden');

        // Show elements
        overlay.classList.remove('hidden');
        drawer.classList.remove('hidden');

        // Trigger animation after a small delay
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            drawer.classList.remove('translate-x-full');

            // Force scroll to top to show options first
            const drawerBody = drawer.querySelector('.flex-1.overflow-y-auto');
            if (drawerBody) {
                drawerBody.scrollTop = 0;
            }
        }, 10);

        // Les contraintes sont déjà dans window.bookingContraintesItems
        // Elles ont été calculées lors du checkAvailability ou updateContraintesInCart
        // On ne recharge PAS depuis sessionStorage pour éviter d'afficher d'anciennes contraintes
        
        console.log('[openOptionsDrawer] window.bookingContraintesItems:', window.bookingContraintesItems);

        // Populate drawer with current options
        populateDrawerOptions();
        updateDrawerCart();

        // Setup event listeners
        setupDrawerEventListeners();
    });
}

/**
 * Close the options drawer
 */
function closeOptionsDrawer() {
    const drawer = document.getElementById('options-drawer');
    const overlay = document.getElementById('options-drawer-overlay');

    if (!drawer || !overlay) return;

    // Trigger close animation
    overlay.classList.add('opacity-0');
    drawer.classList.add('translate-x-full');

    // Hide after animation completes
    setTimeout(() => {
        drawer.classList.add('hidden');
        overlay.classList.add('hidden');
        overlay.classList.remove('opacity-0');

        // Show chatbot again when drawer is closed
        document.body.classList.remove('drawer-chatbot-hidden');
        console.log('[closeOptionsDrawer] Chatbot shown');
    }, 300); // Match transition duration
}

/**
 * Populate drawer with available options
 */
function populateDrawerOptions() {
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
            
            console.log('[populateDrawerOptions] Premium 72h check:', {
                dateDepot: dateDepot.toISOString(),
                nowFrance: nowFrance.toISOString(),
                diffInHours: diffInHours.toFixed(2),
                isPremiumTimeValid: isPremiumTimeValid
            });
        }
    } catch (error) {
        console.error('[populateDrawerOptions] Error checking 72h condition:', error);
        // En cas d'erreur, on considère que c'est valide pour ne pas bloquer
        isPremiumTimeValid = true;
    }
    
    // Premium n'est affiché que si API OK ET condition des 72h remplie
    isPremiumAvailable = hasPremiumFromApi && isPremiumTimeValid;

    console.log('[populateDrawerOptions] staticOptions:', staticOptions);
    console.log('[populateDrawerOptions] Priority:', staticOptions.priority);
    console.log('[populateDrawerOptions] Premium:', staticOptions.premium);
    console.log('[populateDrawerOptions] isPremiumTimeValid (>72h):', isPremiumTimeValid);
    console.log('[populateDrawerOptions] isPremiumAvailable (final):', isPremiumAvailable);

    // Priority Option
    const priorityCard = document.getElementById('drawer-option-priority');
    if (priorityCard) {
        if (hasPriorityFromApi) {
            priorityCard.classList.remove('hidden');
            const priceEl = document.getElementById('drawer-priority-price');
            if (priceEl) {
                const unitPrice = staticOptions.priority.prixUnitaire || 0;
                const unitPriceBeforeDiscount = staticOptions.priority.prixUnitaireAvantRemise || staticOptions.priority.prix_unitaire_avant_remise || null;

                console.log('[populateDrawerOptions] Priority prices:', {
                    unitPrice,
                    unitPriceBeforeDiscount,
                    allKeys: Object.keys(staticOptions.priority)
                });

                if (unitPriceBeforeDiscount != null && unitPriceBeforeDiscount > unitPrice) {
                    // Show discounted price with strikethrough original price
                    priceEl.innerHTML = `
                        <span class="text-lg text-gray-400 line-through font-normal mr-2">${formatPrice(unitPriceBeforeDiscount)} €</span>
                        <span class="text-green-600">+${formatPrice(unitPrice)} €</span>
                    `;
                } else {
                    priceEl.textContent = '+' + formatPrice(unitPrice) + ' €';
                }
            }
        } else {
            priorityCard.classList.add('hidden');
        }
    }

    // Premium Option
    const premiumCard = document.getElementById('drawer-option-premium');
    const premiumUnavailableMsg = document.getElementById('premium-drawer-unavailable-message');

    if (premiumCard) {
        if (isPremiumAvailable) {
            // Premium disponible (API OK + 72h OK)
            premiumCard.classList.remove('hidden');
            if (premiumUnavailableMsg) {
                premiumUnavailableMsg.classList.add('hidden');
            }
            const priceEl = document.getElementById('drawer-premium-price');
            if (priceEl) {
                const unitPrice = staticOptions.premium.prixUnitaire || 0;
                const unitPriceBeforeDiscount = staticOptions.premium.prixUnitaireAvantRemise || staticOptions.premium.prix_unitaire_avant_remise || null;

                console.log('[populateDrawerOptions] Premium prices:', {
                    unitPrice,
                    unitPriceBeforeDiscount,
                    allKeys: Object.keys(staticOptions.premium)
                });

                if (unitPriceBeforeDiscount != null && unitPriceBeforeDiscount > unitPrice) {
                    // Show discounted price with strikethrough original price
                    priceEl.innerHTML = `
                        <span class="text-lg text-gray-400 line-through font-normal mr-2">${formatPrice(unitPriceBeforeDiscount)} €</span>
                        <span class="text-purple-600">+${formatPrice(unitPrice)} €</span>
                    `;
                } else {
                    priceEl.textContent = '+' + formatPrice(unitPrice) + ' €';
                }
            }
        } else {
            // Premium non disponible (soit API, soit 72h, soit les deux)
            if (premiumUnavailableMsg) {
                premiumUnavailableMsg.classList.remove('hidden');
                // Message personnalisé selon la raison
                const msgContent = premiumUnavailableMsg.querySelector('p');
                if (msgContent) {
                    if (!hasPremiumFromApi) {
                        msgContent.textContent = 'Service Premium non disponible pour cet aéroport.';
                    } else if (!isPremiumTimeValid) {
                        msgContent.textContent = 'Service Premium disponible uniquement pour les dépôts à plus de 72h.';
                    }
                }
            }
            premiumCard.classList.add('hidden');
        }
    }

    // Update buttons state
    updateButtonsState();
    
    // Afficher les options Access (contraintes horaires)
    displayAccessOptionsInDrawer();

    // Apply translations to drawer content
    if (typeof applyLanguage === 'function') {
        setTimeout(() => applyLanguage(), 100);
    }
}

/**
 * Afficher les options Access dans le drawer
 */
function displayAccessOptionsInDrawer() {
    const accessContainer = document.getElementById('drawer-access-options');
    if (!accessContainer) return;

    accessContainer.innerHTML = '';

    // Afficher les contraintes depuis window.bookingContraintesItems
    const contraintesItems = window.bookingContraintesItems || [];

    console.log('[displayAccessOptionsInDrawer] window.bookingContraintesItems:', contraintesItems);
    console.log('[displayAccessOptionsInDrawer] sessionStorage:', sessionStorage.getItem('hp_booking_contraintes'));

    if (contraintesItems.length === 0) {
        console.log('[displayAccessOptionsInDrawer] Aucune contrainte à afficher');
        return;
    }

    console.log('[displayAccessOptionsInDrawer] Affichage de', contraintesItems.length, 'contrainte(s)');

    // Titre pour la section Access
    const titleEl = document.createElement('h3');
    titleEl.className = 'text-sm font-bold text-gray-700 mb-3 mt-4';
    titleEl.textContent = 'Options Access (contraintes horaires)';
    accessContainer.appendChild(titleEl);
    
    contraintesItems.forEach(function(accessOption) {
        const card = document.createElement('div');
        card.className = 'bg-orange-50 rounded-lg p-4 mb-3 border-2 border-orange-200';
        
        const unitPrice = accessOption.prix || accessOption.prixUnitaire || 0;
        const unitPriceBeforeDiscount = accessOption.prixUnitaireAvantRemise || null;
        const hasDiscount = unitPriceBeforeDiscount != null && unitPriceBeforeDiscount > unitPrice;
        
        let priceHtml = '';
        if (hasDiscount) {
            priceHtml = `
                <span class="text-sm text-gray-400 line-through mr-2">${formatPrice(unitPriceBeforeDiscount)} €</span>
                <span class="text-lg font-bold text-green-600">${formatPrice(unitPrice)} €</span>
            `;
        } else {
            priceHtml = `<span class="text-lg font-bold text-gray-900">${formatPrice(unitPrice)} €</span>`;
        }
        
        card.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800 text-sm">${escapeHtml(accessOption.libelle)}</h4>
                    <p class="text-xs text-orange-600 font-semibold mt-1">⚠️ Obligatoire pour cet horaire</p>
                </div>
                <div class="text-right">
                    ${priceHtml}
                </div>
            </div>
        `;
        
        accessContainer.appendChild(card);
    });
}

/**
 * Update add/remove buttons state based on cart
 */
function updateButtonsState() {
    ['priority', 'premium'].forEach(optionKey => {
        const isInCart = cartItems.some(item => item.key === optionKey);
        
        const addBtn = document.getElementById(`add-${optionKey}-btn`);
        const removeBtn = document.getElementById(`remove-${optionKey}-btn`);
        
        if (addBtn) {
            addBtn.disabled = isInCart;
            addBtn.classList.toggle('opacity-50', isInCart);
            addBtn.classList.toggle('cursor-not-allowed', isInCart);
        }
        
        if (removeBtn) {
            removeBtn.disabled = !isInCart;
            removeBtn.classList.toggle('opacity-50', !isInCart);
            removeBtn.classList.toggle('cursor-not-allowed', !isInCart);
        }
    });
}

/**
 * Update drawer cart display - mirrors cart.js logic for accurate pricing
 */
function updateDrawerCart() {
    const cartItemsContainer = document.getElementById('drawer-cart-items');
    const cartTotalEl = document.getElementById('drawer-cart-total');

    if (!cartItemsContainer || !cartTotalEl) return;

    // Get all items in cart (baggage + options) + contraintes
    const contraintesItems = window.bookingContraintesItems || [];
    const allItems = [...(cartItems || []), ...contraintesItems];

    console.log('[updateDrawerCart] Cart items:', allItems);
    console.log('[updateDrawerCart] Contraintes items:', contraintesItems);

    if (allItems.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-500 font-medium">Votre panier est vide</p>
                <p class="text-xs text-gray-400 mt-1">Ajoutez des options pour continuer</p>
            </div>
        `;
        cartTotalEl.textContent = '0,00 €';
        return;
    }

    // Get products from API for baggage pricing
    const products = (typeof globalProductsData !== 'undefined' && Array.isArray(globalProductsData)) ? globalProductsData : [];

    // Helper function to find product by ID
    function productById(id) {
        const s = id != null ? String(id) : '';
        return products.find(p => (p.id != null ? String(p.id) : '') === s) || null;
    }

    // Helper function to get unit price from product (same as cart.js)
    function unitPrice(p) {
        if (!p) return 0;
        const n = p.prixUnitaire ?? p.prix_unitaire ?? p.prixTTC ?? p.prix_ttc ?? p.price ?? p.unitPrice;
        if (typeof n === 'number' && !isNaN(n)) return n;
        if (typeof n === 'string') {
            const parsed = parseFloat(n.replace(',', '.'));
            if (!isNaN(parsed)) return parsed;
        }
        return 0;
    }

    // Map product libelle to image path (same as Blade template)
    function getProductImage(libelle) {
        const imageMap = {
            'Accessoires': 'accessoires.png',
            'Bagage cabine': 'bag_cabine.png',
            'Bagage soute': 'bag_soute.png',
            'Bagage spécial': 'bag_special.png',
            'Vestiaire': 'vestiaire.png'
        };
        const imageName = imageMap[libelle] || null;
        if (imageName) {
            return `<img src="/${imageName}" alt="${libelle}" class="w-10 h-10 object-contain p-1" onerror="this.style.display='none'; this.parentElement.innerHTML='🧳';" />`;
        }
        return '🧳';
    }

    // Get icon for item (image for baggage, new images for options)
    function getItemIcon(item, productLibelle) {
        if (item.itemCategory === 'option') {
            if (item.key === 'priority') {
                return `<img src="/icon_priority.png" alt="Priority" class="w-10 h-10 object-contain p-1" onerror="this.style.display='none'; this.parentElement.innerHTML='⚡';" />`;
            } else if (item.key === 'premium') {
                return `<img src="/icon_premium.png" alt="Premium" class="w-10 h-10 object-contain p-1" onerror="this.style.display='none'; this.parentElement.innerHTML='💎';" />`;
            }
        }
        // For baggage, use product images
        return getProductImage(productLibelle || '');
    }

    // Build cart items HTML with correct pricing from API
    let html = '';
    let total = 0;

    allItems.forEach((item, index) => {
        console.log('[updateDrawerCart] Item:', item);

        let itemTotal = 0;
        let unitPriceValue = 0;
        let unitPriceBeforeDiscount = 0;
        let libelle = item.libelle || '';
        let itemIcon = '';

        if (item.itemCategory === 'baggage') {
            // Baggage: get price from globalProductsData like cart.js does
            const product = productById(item.productId);
            unitPriceValue = unitPrice(product);
            // Get price before discount
            const avant = product?.prixUnitaireAvantRemise ?? product?.prix_unitaire_avant_remise;
            const taux = product?.tauxRemise ?? product?.taux_remise;
            if (avant != null && avant !== '') {
                unitPriceBeforeDiscount = typeof avant === 'number' ? avant : parseFloat(String(avant).replace(',', '.'));
            } else if (taux != null && taux !== '' && taux > 0) {
                const t = typeof taux === 'number' ? taux : parseFloat(String(taux).replace(',', '.'));
                if (!isNaN(t) && t > 0 && t < 100 && unitPriceValue > 0) {
                    unitPriceBeforeDiscount = Math.round((unitPriceValue / (1 - t / 100)) * 100) / 100;
                }
            }
            if (unitPriceBeforeDiscount === 0) unitPriceBeforeDiscount = unitPriceValue;
            itemTotal = unitPriceValue * (item.quantity || 1);
            libelle = product ? (product.libelle || product.nom || libelle) : libelle;
            libelle = (item.quantity || 1) + ' × ' + libelle;
            // Use product image for baggage
            itemIcon = getItemIcon(item, product?.libelle || product?.nom || '');
        } else if (item.itemCategory === 'option') {
            // Options: use prixUnitaire from API
            unitPriceValue = parseFloat(item.prixUnitaire) || parseFloat(item.prix) || 0;
            const avant = parseFloat(item.prixUnitaireAvantRemise) || parseFloat(item.prix_ttc_avant_remise) || 0;
            const taux = parseFloat(item.tauxRemise) || parseFloat(item.taux_remise) || 0;
            if (avant > 0) {
                unitPriceBeforeDiscount = avant;
            } else if (taux > 0 && unitPriceValue > 0) {
                unitPriceBeforeDiscount = Math.round((unitPriceValue / (1 - taux / 100)) * 100) / 100;
            } else {
                unitPriceBeforeDiscount = unitPriceValue;
            }
            itemTotal = unitPriceValue * (item.quantity || 1);
            // Use new icon images for options
            itemIcon = getItemIcon(item, '');
        } else if (item.itemCategory === 'contrainte') {
            // Contraintes: use prix from item
            unitPriceValue = parseFloat(item.prix) || parseFloat(item.prixUnitaire) || 0;
            const avant = parseFloat(item.prixUnitaireAvantRemise) || 0;
            const taux = parseFloat(item.tauxRemise) || 0;
            if (avant > 0) {
                unitPriceBeforeDiscount = avant;
            } else if (taux > 0 && unitPriceValue > 0) {
                unitPriceBeforeDiscount = Math.round((unitPriceValue / (1 - taux / 100)) * 100) / 100;
            } else {
                unitPriceBeforeDiscount = unitPriceValue;
            }
            itemTotal = unitPriceValue;
            libelle = libelle + ' (obligatoire)';
            itemIcon = '⚠️';
        }

        const hasDiscount = unitPriceBeforeDiscount > unitPriceValue && unitPriceBeforeDiscount > 0;
        const discountRate = hasDiscount && unitPriceBeforeDiscount > 0 
            ? Math.round(((unitPriceBeforeDiscount - unitPriceValue) / unitPriceBeforeDiscount) * 100) 
            : 0;

        console.log('[updateDrawerCart] unitPrice:', unitPriceValue, 'unitPriceBeforeDiscount:', unitPriceBeforeDiscount, 'itemTotal:', itemTotal);

        total += itemTotal;

        const isOption = item.itemCategory === 'option';
        const gradientClass = item.key === 'priority' ? 'from-yellow-50 to-amber-50 border-yellow-200' :
                             (item.key === 'premium' ? 'from-purple-50 to-indigo-50 border-purple-200' : 'from-gray-50 to-white border-gray-200');

        html += `
            <div class="cart-item flex justify-between items-start p-4 bg-gradient-to-r ${gradientClass} rounded-xl border transition-all hover:shadow-md">
                <div class="flex-1 pr-3 min-w-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-xl shadow-sm flex-shrink-0 overflow-hidden">
                            ${itemIcon}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900 break-words" style="font-size: 0.9rem; line-height: 1.5;">${libelle}</p>
                        </div>
                    </div>
                </div>
                <div class="price-wrapper flex flex-col items-end gap-1 flex-shrink-0">
                    ${hasDiscount ? `
                        <div class="flex items-center gap-2 justify-end">
                            <span class="badge-promo inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 flex-shrink-0" style="min-width: 42px; justify-content: center; font-size: 11px;">-${discountRate}%</span>
                            <span class="old-price text-xs text-gray-400 line-through flex-shrink-0">${formatPrice(unitPriceBeforeDiscount * (item.quantity || 1))} €</span>
                        </div>
                    ` : ''}
                    <div class="flex items-center gap-1 justify-end">
                        <span class="current-price text-sm font-bold text-gray-900 flex-shrink-0" style="min-width: 70px; text-align: right;">${formatPrice(itemTotal)} €</span>
                        ${isOption ? `
                            <button onclick="removeOptionFromDrawer('${item.key}')" class="delete-item-btn text-red-500 hover:text-red-700 p-1 flex-shrink-0" aria-label="Retirer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    console.log('[updateDrawerCart] Total:', total);
    
    cartItemsContainer.innerHTML = html;
    cartTotalEl.textContent = formatPrice(total) + ' €';
    
    // Update buttons state
    updateButtonsState();
    
    // Scroll to cart when options change
    scrollToCart();
}

/**
 * Scroll to cart section smoothly
 */
function scrollToCart() {
    const cartSection = document.getElementById('drawer-cart-items');
    if (cartSection) {
        cartSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

/**
 * Setup drawer event listeners
 */
function setupDrawerEventListeners() {
    const closeBtn = document.getElementById('close-options-drawer');
    const overlay = document.getElementById('options-drawer-overlay');
    const confirmBtn = document.getElementById('confirm-options-drawer');
    
    // Close button
    if (closeBtn) {
        closeBtn.onclick = () => {
            closeOptionsDrawer();
            if (optionsDrawerResolve) optionsDrawerResolve(false);
        };
    }
    
    // Overlay click
    if (overlay) {
        overlay.onclick = () => {
            closeOptionsDrawer();
            if (optionsDrawerResolve) optionsDrawerResolve(false);
        };
    }
    
    // Confirm button
    if (confirmBtn) {
        confirmBtn.onclick = () => {
            closeOptionsDrawer();
            if (optionsDrawerResolve) optionsDrawerResolve(true);
        };
    }
    
    // NO checkbox listeners - using add/remove buttons instead
}

/**
 * Add option to cart (simple, no details)
 */
function addOptionToCart(optionKey) {
    const option = staticOptions[optionKey];
    if (!option) return;

    // Check if already in cart
    if (cartItems.some(item => item.key === optionKey)) return;

    cartItems.push({
        itemCategory: 'option',
        id: option.id,
        key: optionKey,
        libelle: option.libelle,
        prix: option.prixUnitaire,
        prixUnitaire: option.prixUnitaire,
        prixUnitaireAvantRemise: option.prixUnitaireAvantRemise ?? null,
        prix_ttc_avant_remise: option.prix_ttc_avant_remise ?? null,
        tauxRemise: option.tauxRemise ?? null,
        taux_remise: option.taux_remise ?? null,
        quantity: 1,
        details: {} // Empty details, will be filled at payment step
    });

    updateCartDisplay();
    updateDrawerCart();
    updateButtonsState();
}

/**
 * Remove option from cart
 */
function removeOptionFromCart(optionKey) {
    const index = cartItems.findIndex(item => item.key === optionKey);
    if (index > -1) {
        cartItems.splice(index, 1);
        updateCartDisplay();
        updateDrawerCart();
        updateButtonsState();
    }
}

/**
 * Remove option from drawer (called from cart item button)
 */
function removeOptionFromDrawer(optionKey) {
    removeOptionFromCart(optionKey);
}

/**
 * Format price for display
 */
function formatPrice(price) {
    return (parseFloat(price) || 0).toFixed(2).replace('.', ',');
}

// Export for use in other files
window.openOptionsDrawer = openOptionsDrawer;
window.closeOptionsDrawer = closeOptionsDrawer;
window.removeOptionFromDrawer = removeOptionFromDrawer;
