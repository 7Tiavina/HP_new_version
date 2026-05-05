// Global Translation System for HelloPassenger
const translations = {
    fr: {
        // Modal
        modal_title: "Votre sécurité, notre priorité !",
        modal_subtitle: "Pour la protection de vos biens et le respect des normes aéroportuaires (contrôle par rayons X), veuillez compléter vos informations de contact.",
        
        // Client Type
        client_type_label: "Type de client",
        client_type_particulier: "Particulier",
        client_type_societe: "Société",
        
        // Form Labels
        label_prenom: "Prénom",
        label_nom: "Nom",
        label_telephone: "Téléphone mobile",
        label_adresse: "Adresse",
        label_civilite: "Civilité",
        label_societe: "Nom de la Société",
        label_complement: "Complément",
        label_ville: "Ville",
        label_code_postal: "Code Postal",
        label_pays: "Pays",
        
        // Options
        option_mr: "Monsieur",
        option_mrs: "Madame",
        
        // Buttons
        btn_complete_profile: "Compléter mon profil (facultatif)",
        btn_hide_fields: "Masquer les champs optionnels",
        btn_cancel: "Annuler",
        btn_confirm_pay: "Confirmer et payer",
        
        // Page sections
        modal_section_1: "Vos coordonnées",
        
        // Payment page
        your_info: "Vos informations",
        secure_payment: "Paiement sécurisé",
        name: "Nom",
        email: "Email",
        phone: "Téléphone",
        address: "Adresse",
        company: "Société",
        not_provided: "Non renseigné(e)",
        edit: "Modifier",
        
        // Errors
        error_required_field: "Ce champ est requis",
        error_invalid_email: "Email invalide",
        error_invalid_phone: "Numéro de téléphone invalide",
        error_invalid_postal_code: "Code postal invalide",
        
        // Header/Navigation
        home: "Accueil",
        services: "Services",
        about: "À propos",
        contact: "Contact",
        login: "Connexion",
        register: "Inscription",
        logout: "Déconnexion",
        profile: "Profil",
        
        // Footer
        footer_about: "À propos",
        footer_terms: "Conditions d'utilisation",
        footer_privacy: "Politique de confidentialité",
        footer_contact: "Nous contacter",
        footer_rights: "Tous droits réservés",
    },
    en: {
        // Modal
        modal_title: "Your security, our priority!",
        modal_subtitle: "To protect your belongings and comply with airport standards (X-ray screening), please complete your contact information.",
        
        // Client Type
        client_type_label: "Client type",
        client_type_particulier: "Individual",
        client_type_societe: "Company",
        
        // Form Labels
        label_prenom: "First Name",
        label_nom: "Last Name",
        label_telephone: "Mobile Phone",
        label_adresse: "Address",
        label_civilite: "Civility",
        label_societe: "Company Name",
        label_complement: "Complement",
        label_ville: "City",
        label_code_postal: "Postal Code",
        label_pays: "Country",
        
        // Options
        option_mr: "Mr.",
        option_mrs: "Mrs.",
        
        // Buttons
        btn_complete_profile: "Complete my profile (optional)",
        btn_hide_fields: "Hide optional fields",
        btn_cancel: "Cancel",
        btn_confirm_pay: "Confirm and pay",
        
        // Page sections
        modal_section_1: "Your contact details",
        
        // Payment page
        your_info: "Your information",
        secure_payment: "Secure payment",
        name: "Name",
        email: "Email",
        phone: "Phone",
        address: "Address",
        company: "Company",
        not_provided: "Not provided",
        edit: "Edit",
        
        // Errors
        error_required_field: "This field is required",
        error_invalid_email: "Invalid email",
        error_invalid_phone: "Invalid phone number",
        error_invalid_postal_code: "Invalid postal code",
        
        // Header/Navigation
        home: "Home",
        services: "Services",
        about: "About",
        contact: "Contact",
        login: "Login",
        register: "Sign up",
        logout: "Logout",
        profile: "Profile",
        
        // Footer
        footer_about: "About",
        footer_terms: "Terms of use",
        footer_privacy: "Privacy policy",
        footer_contact: "Contact us",
        footer_rights: "All rights reserved",
    }
};

// Language Manager
class LanguageManager {
    constructor() {
        this.currentLanguage = localStorage.getItem('app_language') || 'fr';
        console.log('LanguageManager initialized with language:', this.currentLanguage);
        this.init();
    }
    
    init() {
        // Délay to ensure DOM is fully loaded
        setTimeout(() => {
            this.applyLanguage(this.currentLanguage);
            this.setupLanguageButtons();
            console.log('LanguageManager setup complete');
        }, 100);
    }
    
    setLanguage(lang) {
        if (translations[lang]) {
            this.currentLanguage = lang;
            localStorage.setItem('app_language', lang);
            console.log('Language set to:', lang);
            this.applyLanguage(lang);
        } else {
            console.error('Language not found:', lang);
        }
    }
    
    getTranslation(key) {
        return translations[this.currentLanguage][key] || key;
    }
    
    applyLanguage(lang) {
        console.log('Applying language:', lang);
        
        // Update all elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const translation = translations[lang][key];
            
            if (translation) {
                // For select options, update the visible option
                if (element.tagName === 'SELECT') {
                    element.querySelectorAll('option').forEach(option => {
                        const optionKey = option.getAttribute('data-i18n');
                        if (optionKey && translations[lang][optionKey]) {
                            option.textContent = translations[lang][optionKey];
                        }
                    });
                } else {
                    element.textContent = translation;
                }
            }
        });
        
        // Update modal language button states
        setTimeout(() => {
            const frBtn = document.getElementById('lang-fr-btn');
            const enBtn = document.getElementById('lang-en-btn');
            if (frBtn && enBtn) {
                if (lang === 'fr') {
                    frBtn.classList.remove('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
                    frBtn.classList.add('bg-[#ffc107]', 'text-[#212121]');
                    enBtn.classList.remove('bg-[#ffc107]', 'text-[#212121]');
                    enBtn.classList.add('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
                } else {
                    enBtn.classList.remove('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
                    enBtn.classList.add('bg-[#ffc107]', 'text-[#212121]');
                    frBtn.classList.remove('bg-[#ffc107]', 'text-[#212121]');
                    frBtn.classList.add('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
                }
            }
            
            // Update header language button states
            const headerFrBtn = document.getElementById('header-lang-fr-btn');
            const headerEnBtn = document.getElementById('header-lang-en-btn');
            if (headerFrBtn && headerEnBtn) {
                if (lang === 'fr') {
                    headerFrBtn.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                    headerFrBtn.classList.add('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    headerEnBtn.classList.remove('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    headerEnBtn.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                } else {
                    headerEnBtn.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                    headerEnBtn.classList.add('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    headerFrBtn.classList.remove('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    headerFrBtn.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                }
            }
            
            // Update mobile language button states
            const mobileFrBtn = document.getElementById('mobile-lang-fr-btn');
            const mobileEnBtn = document.getElementById('mobile-lang-en-btn');
            if (mobileFrBtn && mobileEnBtn) {
                if (lang === 'fr') {
                    mobileFrBtn.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                    mobileFrBtn.classList.add('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    mobileEnBtn.classList.remove('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    mobileEnBtn.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                } else {
                    mobileEnBtn.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                    mobileEnBtn.classList.add('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    mobileFrBtn.classList.remove('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
                    mobileFrBtn.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
                }
            }
            
            // Update toggle text for profile fields
            this.updateToggleText();
        }, 50);
    }
    
    setupLanguageButtons() {
        // Use event delegation on document for all language buttons
        document.addEventListener('click', (e) => {
            const langBtn = e.target.closest('[data-lang]');
            if (langBtn) {
                const lang = langBtn.getAttribute('data-lang');
                this.setLanguage(lang);
            }
        });
    }
    
    updateToggleText() {
        const toggleText = document.getElementById('toggleText');
        const additionalFieldsContainer = document.getElementById('additional-fields-container');
        
        if (toggleText && additionalFieldsContainer) {
            const isVisible = !additionalFieldsContainer.classList.contains('hidden');
            toggleText.textContent = isVisible 
                ? this.getTranslation('btn_hide_fields')
                : this.getTranslation('btn_complete_profile');
        }
    }
}

// Initialize language manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (!window.languageManager) {
        window.languageManager = new LanguageManager();
    }
});

// Also run on interactive state
if (document.readyState !== 'loading') {
    if (!window.languageManager) {
        window.languageManager = new LanguageManager();
    }
}
