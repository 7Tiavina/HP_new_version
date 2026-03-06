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
        
        // Show elements
        overlay.classList.remove('hidden');
        drawer.classList.remove('hidden');
        
        // Trigger animation after a small delay
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            drawer.classList.remove('translate-x-full');
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
    }, 300); // Match transition duration
}

/**
 * Populate drawer with available options
 */
function populateDrawerOptions() {
    const hasPremiumFromApi = staticOptions.premium && staticOptions.premium.id && staticOptions.premium.prixUnitaire > 0;
    const hasPriorityFromApi = staticOptions.priority && staticOptions.priority.id && staticOptions.priority.prixUnitaire > 0;
    
    isPremiumAvailable = hasPremiumFromApi;
    
    // Priority Option
    const priorityCard = document.getElementById('drawer-option-priority');
    if (priorityCard) {
        if (hasPriorityFromApi) {
            priorityCard.classList.remove('hidden');
            const priceEl = document.getElementById('drawer-priority-price');
            if (priceEl) {
                priceEl.textContent = '+' + formatPrice(staticOptions.priority.prixUnitaire) + ' €';
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
                priceEl.textContent = '+' + formatPrice(staticOptions.premium.prixUnitaire) + ' €';
            }
        } else {
            if (premiumUnavailableMsg) {
                premiumUnavailableMsg.classList.remove('hidden');
            }
        }
    }
}

/**
 * Update drawer cart display
 */
function updateDrawerCart() {
    const cartItemsContainer = document.getElementById('drawer-cart-items');
    const cartTotalEl = document.getElementById('drawer-cart-total');
    
    if (!cartItemsContainer || !cartTotalEl) return;
    
    // Get all items in cart (baggage + options)
    const allItems = cartItems || [];
    
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
    
    // Build cart items HTML with correct pricing from API
    let html = '';
    let total = 0;
    
    allItems.forEach((item, index) => {
        // Use prixUnitaire from API for options, or calculated price for baggage
        let itemTotal = 0;
        let unitPrice = 0;
        
        if (item.itemCategory === 'option') {
            // Options: use prixUnitaire directly from API
            unitPrice = item.prixUnitaire || item.prix || 0;
            itemTotal = unitPrice * (item.quantity || 1);
        } else {
            // Baggage: use prix from cart (already calculated from API)
            unitPrice = item.prix || 0;
            itemTotal = unitPrice * (item.quantity || 1);
        }
        
        total += itemTotal;
        
        const isOption = item.itemCategory === 'option';
        const icon = item.key === 'priority' ? '⚡' : (item.key === 'premium' ? '💎' : '🧳');
        const gradientClass = item.key === 'priority' ? 'from-yellow-50 to-amber-50 border-yellow-200' : 
                             (item.key === 'premium' ? 'from-purple-50 to-indigo-50 border-purple-200' : 'from-gray-50 to-white border-gray-200');
        
        html += `
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r ${gradientClass} rounded-xl border transition-all hover:shadow-md">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-xl shadow-sm flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">${item.libelle || 'Article'}</p>
                    <p class="text-xs text-gray-500">
                        ${formatPrice(unitPrice)} € x ${item.quantity || 1}
                        ${item.itemCategory !== 'option' && item.discountApplied ? '<span class="text-green-600 ml-1">(promo)</span>' : ''}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900">${formatPrice(itemTotal)} €</p>
                    ${isOption ? 
                        `<button onclick="removeOptionFromDrawer('${item.key}')" class="text-xs text-red-500 hover:text-red-700 font-medium mt-0.5 transition-colors">Retirer</button>` : 
                        ''}
                </div>
            </div>
        `;
    });
    
    cartItemsContainer.innerHTML = html;
    cartTotalEl.textContent = formatPrice(total) + ' €';
    
    // Update checkboxes state
    updateDrawerCheckboxes();
    
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
 * Update drawer checkboxes based on cart
 */
function updateDrawerCheckboxes() {
    ['priority', 'premium'].forEach(optionKey => {
        const toggle = document.getElementById(`drawer-${optionKey}-toggle`);
        if (!toggle) return;
        
        const isInCart = cartItems.some(item => item.key === optionKey);
        toggle.checked = isInCart;
    });
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
    
    // Priority toggle
    const priorityToggle = document.getElementById('drawer-priority-toggle');
    if (priorityToggle) {
        priorityToggle.onchange = (e) => {
            if (e.target.checked) {
                addOptionToCart('priority');
            } else {
                removeOptionFromCart('priority');
            }
        };
    }
    
    // Premium toggle
    const premiumToggle = document.getElementById('drawer-premium-toggle');
    if (premiumToggle) {
        premiumToggle.onchange = (e) => {
            if (e.target.checked) {
                addOptionToCart('premium');
            } else {
                removeOptionFromCart('premium');
            }
        };
    }
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
        quantity: 1,
        details: {} // Empty details, will be filled at payment step
    });
    
    updateCartDisplay();
    updateDrawerCart();
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
