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
        const chatbotWidget = document.getElementById('chatbot-widget');
        if (chatbotWidget) {
            chatbotWidget.style.display = 'none';
            console.log('[openOptionsDrawer] Chatbot hidden');
        }

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
        const chatbotWidget = document.getElementById('chatbot-widget');
        if (chatbotWidget) {
            chatbotWidget.style.display = 'block';
            console.log('[closeOptionsDrawer] Chatbot shown');
        }
    }, 300); // Match transition duration
}

/**
 * Populate drawer with available options
 */
function populateDrawerOptions() {
    const hasPremiumFromApi = staticOptions.premium && staticOptions.premium.id && staticOptions.premium.prixUnitaire > 0;
    const hasPriorityFromApi = staticOptions.priority && staticOptions.priority.id && staticOptions.priority.prixUnitaire > 0;

    isPremiumAvailable = hasPremiumFromApi;

    console.log('[populateDrawerOptions] staticOptions:', staticOptions);
    console.log('[populateDrawerOptions] Priority:', staticOptions.priority);
    console.log('[populateDrawerOptions] Premium:', staticOptions.premium);

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
        if (hasPremiumFromApi) {
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
            if (premiumUnavailableMsg) {
                premiumUnavailableMsg.classList.remove('hidden');
            }
        }
    }

    // Update buttons state
    updateButtonsState();
    
    // Apply translations to drawer content
    if (typeof applyLanguage === 'function') {
        setTimeout(() => applyLanguage(), 100);
    }
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
    
    // Get all items in cart (baggage + options)
    const allItems = cartItems || [];
    
    console.log('[updateDrawerCart] Cart items:', allItems);
    
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
    
    // Build cart items HTML with correct pricing from API
    let html = '';
    let total = 0;
    
    allItems.forEach((item, index) => {
        console.log('[updateDrawerCart] Item:', item);

        let itemTotal = 0;
        let unitPriceValue = 0;
        let unitPriceBeforeDiscount = 0;
        let libelle = item.libelle || '';

        if (item.itemCategory === 'baggage') {
            // Baggage: get price from globalProductsData like cart.js does
            const product = productById(item.productId);
            unitPriceValue = unitPrice(product);
            // Get price before discount
            unitPriceBeforeDiscount = product?.prixUnitaireAvantRemise ?? product?.prix_unitaire_avant_remise ?? unitPriceValue;
            itemTotal = unitPriceValue * (item.quantity || 1);
            libelle = product ? (product.libelle || product.nom || libelle) : libelle;
            libelle = (item.quantity || 1) + ' × ' + libelle;
        } else if (item.itemCategory === 'option') {
            // Options: use prixUnitaire from API
            unitPriceValue = parseFloat(item.prixUnitaire) || parseFloat(item.prix) || 0;
            unitPriceBeforeDiscount = parseFloat(item.prixUnitaireAvantRemise) || parseFloat(item.prix_ttc_avant_remise) || unitPriceValue;
            itemTotal = unitPriceValue * (item.quantity || 1);
        }

        console.log('[updateDrawerCart] unitPrice:', unitPriceValue, 'unitPriceBeforeDiscount:', unitPriceBeforeDiscount, 'itemTotal:', itemTotal);

        total += itemTotal;

        const isOption = item.itemCategory === 'option';
        const icon = item.key === 'priority' ? '⚡' : (item.key === 'premium' ? '💎' : '🧳');
        const gradientClass = item.key === 'priority' ? 'from-yellow-50 to-amber-50 border-yellow-200' :
                             (item.key === 'premium' ? 'from-purple-50 to-indigo-50 border-purple-200' : 'from-gray-50 to-white border-gray-200');

        const hasDiscount = unitPriceBeforeDiscount > unitPriceValue;

        html += `
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r ${gradientClass} rounded-xl border transition-all hover:shadow-md">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-xl shadow-sm flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">${libelle}</p>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500">
                            ${formatPrice(unitPriceValue)} € x ${item.quantity || 1}
                        </p>
                        ${hasDiscount ? 
                            `<span class="text-xs text-gray-400 line-through">${formatPrice(unitPriceBeforeDiscount)} €</span>` : 
                            ''}
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="flex items-center gap-2 justify-end">
                        ${hasDiscount ? 
                            `<span class="text-xs text-gray-400 line-through">${formatPrice(unitPriceBeforeDiscount * (item.quantity || 1))} €</span>` : 
                            ''}
                        <p class="text-sm font-bold text-gray-900">${formatPrice(itemTotal)} €</p>
                    </div>
                    ${isOption ?
                        `<button onclick="removeOptionFromDrawer('${item.key}')" class="text-xs text-red-500 hover:text-red-700 font-medium mt-0.5 transition-colors">Retirer</button>` :
                        ''}
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
