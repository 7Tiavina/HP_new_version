/**
 * Chatbot Widget for HelloPassenger
 * Integrates with 1min.ai API
 */

class ChatbotWidget {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        this.whatsappNumber = window.chatbotConfig?.whatsappNumber || '+33612345678';
        this.whatsappMessage = window.chatbotConfig?.whatsappMessage || 'Bonjour, j\'ai besoin d\'aide';
        this.init();
    }

    init() {
        this.createWidget();
        this.attachEvents();
        this.loadHistory();
    }

    createWidget() {
        // Vérifier si le widget existe déjà
        if (document.getElementById('chatbot-widget')) {
            console.log('[Chatbot] Widget already exists, skipping creation');
            return;
        }
        
        // Créer le conteneur principal
        const widget = document.createElement('div');
        widget.id = 'chatbot-widget';
        widget.innerHTML = `
            <!-- Bouton flottant -->
            <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Ouvrir le chat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="currentColor"/>
                </svg>
                <span class="chatbot-badge" id="chatbot-badge" style="display: none;">1</span>
            </button>

            <!-- Fenêtre de chat -->
            <div id="chatbot-window" class="chatbot-window">
                <div class="chatbot-header">
                    <div class="chatbot-header-content">
                        <div class="chatbot-avatar">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="chatbot-header-text">
                            <h3>Assistant HelloPassenger</h3>
                            <p class="chatbot-status">En ligne</p>
                        </div>
                    </div>
                    <button id="chatbot-close" class="chatbot-close" aria-label="Fermer le chat">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="chatbot-messages" id="chatbot-messages">
                    <div class="chatbot-welcome">
                        <p>👋 Bonjour ! Je suis l'assistant HelloPassenger. Comment puis-je vous aider aujourd'hui ?</p>
                    </div>
                </div>

                <!-- WhatsApp Banner (affiché si l'AI ne peut pas résoudre) -->
                <div id="chatbot-whatsapp-banner" class="chatbot-whatsapp-banner" style="display: none;">
                    <div class="chatbot-whatsapp-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <div>
                            <p><strong>Besoin d'aide supplémentaire ?</strong></p>
                            <p>Contactez-nous sur WhatsApp</p>
                        </div>
                        <a href="#" id="chatbot-whatsapp-link" class="chatbot-whatsapp-button" target="_blank" rel="noopener">
                            Ouvrir WhatsApp
                        </a>
                    </div>
                </div>

                <div class="chatbot-input-container">
                    <input 
                        type="text" 
                        id="chatbot-input" 
                        class="chatbot-input" 
                        placeholder="Tapez votre message..."
                        maxlength="1000"
                    />
                    <button id="chatbot-send" class="chatbot-send" aria-label="Envoyer">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M18 2L9 11M18 2L12 18L9 11M18 2L2 8L9 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(widget);
        console.log('[Chatbot] Widget created and added to DOM');
    }

    attachEvents() {
        const toggle = document.getElementById('chatbot-toggle');
        const close = document.getElementById('chatbot-close');
        const send = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');
        const whatsappLink = document.getElementById('chatbot-whatsapp-link');

        toggle.addEventListener('click', () => this.toggle());
        close.addEventListener('click', () => this.toggle());
        send.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Lien WhatsApp
        if (whatsappLink) {
            whatsappLink.href = `https://wa.me/${this.whatsappNumber.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(this.whatsappMessage)}`;
        }
    }

    toggle() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        
        if (this.isOpen) {
            window.classList.add('chatbot-window-open');
            toggle.classList.add('chatbot-toggle-hidden');
            document.getElementById('chatbot-input').focus();
        } else {
            window.classList.remove('chatbot-window-open');
            toggle.classList.remove('chatbot-toggle-hidden');
        }
    }

    async sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();

        if (!message || this.isTyping) return;

        // Afficher le message de l'utilisateur
        this.addMessage(message, 'user');
        input.value = '';
        this.setTyping(true);

        try {
            // Récupérer le token CSRF de différentes sources possibles
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                csrfToken = document.querySelector('input[name="_token"]')?.value;
            }
            if (!csrfToken) {
                csrfToken = document.querySelector('[name="csrf-token"]')?.content;
            }
            
            const response = await fetch('/api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message })
            });

            // Vérifier si la réponse est OK avant de parser le JSON
            if (!response.ok) {
                let errorText = '';
                try {
                    errorText = await response.text();
                    // Essayer de parser comme JSON si possible
                    try {
                        const errorJson = JSON.parse(errorText);
                        console.error('[Chatbot] HTTP Error (JSON):', response.status, errorJson);
                        throw new Error(errorJson.message || `HTTP ${response.status}`);
                    } catch (e) {
                        // Ce n'est pas du JSON, c'est probablement du HTML
                        console.error('[Chatbot] HTTP Error (HTML):', response.status);
                        throw new Error(`Erreur serveur (${response.status}). Veuillez réessayer.`);
                    }
                } catch (e) {
                    console.error('[Chatbot] Error reading response:', e);
                    throw new Error(`Erreur serveur (${response.status}). Veuillez réessayer.`);
                }
            }

            // Vérifier le Content-Type avant de parser
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('[Chatbot] Non-JSON response:', contentType, text.substring(0, 200));
                throw new Error('Réponse invalide du serveur');
            }

            const data = await response.json();
            
            console.log('[Chatbot] Response received:', data);

            // Toujours afficher le message reçu (même si success: false)
            if (data && data.message) {
                console.log('[Chatbot] Displaying message from server:', data.message);
                this.addMessage(data.message, 'assistant');
            } else {
                console.warn('[Chatbot] No message in response, using fallback');
                this.addMessage('Désolé, une erreur est survenue. Veuillez réessayer.', 'assistant');
            }
            
            // Afficher le banner WhatsApp si nécessaire (même en cas d'erreur)
            if (data && (data.show_whatsapp || !data.success)) {
                console.log('[Chatbot] Showing WhatsApp banner');
                this.showWhatsAppBanner();
            } else {
                this.hideWhatsAppBanner();
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            this.addMessage('Désolé, le service est temporairement indisponible. Veuillez nous contacter directement.', 'assistant');
            this.showWhatsAppBanner();
        } finally {
            this.setTyping(false);
        }
    }

    addMessage(text, role) {
        const messages = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message chatbot-message-${role}`;
        
        messageDiv.innerHTML = `
            <div class="chatbot-message-content">
                ${this.escapeHtml(text)}
            </div>
            <div class="chatbot-message-time">${this.getCurrentTime()}</div>
        `;
        
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    setTyping(typing) {
        this.isTyping = typing;
        const messages = document.getElementById('chatbot-messages');
        const typingIndicator = document.getElementById('chatbot-typing');

        if (typing) {
            if (!typingIndicator) {
                const indicator = document.createElement('div');
                indicator.id = 'chatbot-typing';
                indicator.className = 'chatbot-message chatbot-message-assistant';
                indicator.innerHTML = `
                    <div class="chatbot-message-content chatbot-typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                `;
                messages.appendChild(indicator);
            }
            messages.scrollTop = messages.scrollHeight;
        } else {
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
    }

    showWhatsAppBanner() {
        const banner = document.getElementById('chatbot-whatsapp-banner');
        if (banner) {
            banner.style.display = 'block';
        }
    }

    hideWhatsAppBanner() {
        const banner = document.getElementById('chatbot-whatsapp-banner');
        if (banner) {
            banner.style.display = 'none';
        }
    }

    loadHistory() {
        // L'historique est géré côté serveur via la session
        // On peut ajouter ici une logique pour charger les messages précédents si nécessaire
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }
}

// Initialiser le widget quand le DOM est prêt
function initChatbot() {
    try {
        if (document.getElementById('chatbot-widget')) {
            console.log('[Chatbot] Widget already exists');
            return;
        }
        window.chatbot = new ChatbotWidget();
        console.log('[Chatbot] Widget initialized successfully');
    } catch (error) {
        console.error('[Chatbot] Initialization error:', error);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatbot);
} else {
    // Si le DOM est déjà chargé, initialiser immédiatement
    initChatbot();
}

// Fallback: initialiser après un court délai si nécessaire
setTimeout(() => {
    if (!window.chatbot && !document.getElementById('chatbot-widget')) {
        console.log('[Chatbot] Fallback initialization');
        initChatbot();
    }
}, 1000);