// Simple Translation System
console.log('Translation script loading...');

// Prevent redeclaration if file is loaded multiple times
if (typeof window.translations === 'undefined') {
    window.translations = {
    fr: {
        // Header & Navigation
        menu: "MENU",
        nav_about: "À propos",
        nav_faq: "FAQ",
        nav_book: "Réserver",
        nav_home: "Accueil",
        login_btn: "Se connecter",
        create_account_short: "S'inscrire",
        logout_btn: "Déconnexion",
        admin_access: "Accès Admin",
        book: "RÉSERVER",
        disconnect: "DÉCONNECTER",
        
        // Modal
        modal_title: "Votre sécurité, notre priorité !",
        modal_subtitle: "Pour la protection de vos biens et le respect des normes aéroportuaires (contrôle par rayons X), veuillez compléter vos informations de contact.",
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
        label_email: "Email",
        label_password: "Mot de passe",
        
        // Placeholders
        placeholder_prenom: "Prénom",
        placeholder_nom: "Nom",
        placeholder_telephone: "+33 6 12 34 56 78 (avec code pays)",
        placeholder_pays: "Ex: France",
        
        // Options
        option_mr: "Monsieur",
        option_mrs: "Madame",
        
        // Buttons
        btn_complete_profile: "Compléter mon profil (facultatif)",
        btn_hide_fields: "Masquer les champs optionnels",
        btn_cancel: "Annuler",
        btn_confirm_pay: "Confirmer et payer",
        btn_submit: "Valider",
        btn_back: "Retour",
        btn_continue: "Continuer",
        btn_modify: "Modifier",
        btn_delete: "Supprimer",
        btn_save: "Enregistrer",
        btn_close: "Fermer",
        
        // Page sections
        modal_section_1: "Vos coordonnées",
        your_info: "Vos informations",
        secure_payment: "Paiement sécurisé",
        order_summary: "Récapitulatif de commande",
        
        // Payment page
        name: "Nom",
        email: "Email",
        phone: "Téléphone",
        address: "Adresse",
        company: "Société",
        city: "Ville",
        postal_code: "Code postal",
        country: "Pays",
        edit: "Modifier",
        not_provided: "Non renseigné",
        
        // Login/Register
        login_title: "Se connecter",
        login_subtitle: "Accédez à votre compte",
        register_title: "Créer un compte",
        register_subtitle: "Rejoignez HelloPassenger",
        your_email: "VOTRE ADRESSE EMAIL :",
        your_password: "VOTRE MOT DE PASSE :",
        forgot_password: "Mot de passe oublié ?",
        remember_me: "Rester connecté(e)",
        no_account: "Pas encore de compte ?",
        already_account: "Déjà un compte ?",
        create_account: "CRÉER UN COMPTE →",
        create_my_account: "CRÉER MON COMPTE",
        nom: "NOM :",
        prenom: "PRÉNOM :",
        email: "ADRESSE EMAIL :",
        telephone: "TÉLÉPHONE :",
        password: "MOT DE PASSE :",
        confirm_password: "CONFIRMER MOT DE PASSE :",
        login_error_title: "Erreur de connexion",
        login_error_message: "Identifiants invalides — veuillez réessayer.",
        close_btn: "Fermer",
        my_account: "Mon compte",
        logout_btn: "Déconnexion",
        
        // Common
        required: "Obligatoire",
        optional: "Facultatif",
        loading: "CHARGEMENT",
        error: "Erreur",
        success: "Succès",
        warning: "Attention",
        
        // Footer
        footer_about: "À propos",
        footer_terms: "Conditions d'utilisation",
        footer_privacy: "Politique de confidentialité",
        footer_contact: "Nous contacter",
        footer_rights: "Tous droits réservés",
        home: "Accueil Hello Passenger",
        footer_locate: "Nous localiser",
        footer_quick_links: "Liens rapides",
        footer_transport: "Transport de bagages",
        footer_left_luggage: "Consigne de bagages",
        footer_lost_object: "Récupérer mon objet perdu",
        footer_description: "Hello Passenger est une plateforme qui vous permet de réserver un transport de bagages vers ou depuis l'aéroport ainsi que de stocker vos bagages en consigne dans notre agence située à Paris CDG et Paris ORLY.",
        footer_cdg: "Terminal 2, gare TGV – Niveau 4, face à l'hôtel Sheraton, entre 2C et 2E.",
        footer_orly: "Terminal 3, niveau arrivées.",
        footer_since: "depuis 2015",
        footer_credits: "© <span>Hello Passenger</span> 2026. Tous droits réservés.",
        footer_access: "Plan d'accès",
        footer_cdg_address: "Terminal 2 - Gare TGV - Niveau 4<br>Opposition Hôtel Sheraton",
        footer_orly_address: "Terminal 3 - Niveau d'arrivée",
        footer_email: "Email",
        footer_follow: "Suivez-nous",
        footer_links: "Liens Rapides",
        footer_services: "Services",
        footer_faq: "FAQ",
        footer_contact_link: "Contact",
        footer_book: "Réservez maintenant ↗",
        promo_intro: "Profitez de 10€ de réduction avec le code",
        footer_copyright: "© 2026 par Hello Passenger. Tous droits réservés.",
        footer_created: "Créé par <span class=\"text-yellow-500 font-bold\">Blablabla Agency</span>.",
        
        // Errors
        error_required_field: "Ce champ est requis",
        error_invalid_email: "Email invalide",
        error_invalid_phone: "Numéro de téléphone invalide",
        error_invalid_postal_code: "Code postal invalide",
        
        // Home Page
        hero_discover: "Découvrir",
        hero_title: "des solutions de voyage<br>simples à l'aéroport",
        hero_desc: "Voyagez léger, sans bagages avec notre plateforme.",
        intro_title: "Voyager n'a jamais été aussi simple !",
        intro_text: "Hello Passenger est spécialiste des services voyageurs aéroportuaires. Que vous voyagiez vers ou depuis Paris, en visite ou en départ, seul, en couple ou en famille, nous rendons votre trajet facile et sans stress.",
        stat_experience: "Expérience",
        stat_success: "Taux de succès",
        stat_since: "À votre service depuis",
        services_primary: "Nos services principaux",
        services_sub: "Transport et stockage à Paris CDG & Orly",
        service_transport: "Transport de bagages",
        service_transport_desc: "Porte-à-porte ou transfert aéroport pour vos bagages. Fiable et tracé.",
        service_storage: "Consigne de bagages",
        service_storage_desc: "Déposez vos bagages à nos comptoirs à CDG Terminal 2 et Orly Terminal 3. Sécurisé et flexible.",
        read_more: "En savoir plus →",
        svc_left: "Consigne bagages",
        svc_left_desc: "Stockage sécurisé à l'aéroport pour explorer Paris les mains libres.",
        svc_lost: "Objets perdus",
        svc_lost_desc: "Nous aidons à récupérer les bagages et objets perdus à l'aéroport.",
        svc_children: "Équipement enfants",
        svc_children_desc: "Poussettes et services famille pour un voyage fluide.",
        svc_tech: "Équipement high-tech",
        svc_tech_desc: "Cartes SIM, Wi-Fi pocket et location tech à l'aéroport.",
        process_title: "Notre processus",
        process_sub: "Votre expérience bagages, notre priorité",
        process_1: "Réservez",
        process_1_desc: "Finalisez votre réservation en quelques étapes sur notre plateforme sécurisée. Votre bon numérique est émis instantanément et accessible par email et compte personnel.",
        process_2: "Choisissez votre service",
        process_2_desc: "Décidez de la gestion de vos bagages. Dépôt à notre équipement aéroport, bénéficiez d'un service meet & collect personnalisé, ou organisez un transport coordonné vers/depuis l'aéroport.",
        process_3: "Voyagez sereinement",
        process_3_desc: "Déplacez-vous librement pendant que nous prenons soin de vos bagages. Gérés avec professionnalisme, discrétion et les plus hauts standards de sécurité.",
        testimonial_title: "Approuvé par les voyageurs du monde entier",
        testimonial_sub: "Des nouveaux clients à nos plus fidèles, découvrez pourquoi les gens adorent le service Hello Passenger.",
        testimonial_1: "Très bonne expérience. Nous avons laissé 6/7 bagages dont un ordinateur et d'autres affaires pour environ 60 € pendant 4/5 heures — le temps de prendre le train en ville pour une promenade. Service sympathique et efficace.",
        testimonial_2: "Professionnels, dignes de confiance et tarifs raisonnables. Ils nous ont rendu nos bagages perdus rapidement et facilement. Nous sommes reconnaissants envers cette organisation et les personnes qui y travaillent.",
        testimonial_3: "Cet endroit est idéal pour stocker vos bagages pendant une sortie à Paris. Les bagages sont pesés et facturés selon le poids et la durée. Les locaux sont bien organisés, le personnel très efficace. Nous avons récupéré nos bagages plus tôt que prévu et avons eu un remboursement.",
        testimonial_4: "Consigne et récupération simples pour profiter d'une journée à Paris sans vous soucier de vos bagages.",
        trust_reviews: "<strong>Note 4,3/5</strong> — Avis Google",
        trust_benefits: "<strong>Avantages exclusifs en ligne</strong>",
        trust_secure: "<strong>Paiement 100% sécurisé</strong>",
        trust_clickcollect: "<strong>Click & Collect GRATUIT</strong>",
        about_title: "Enraciné dans les aéroports parisiens. Piloté par les gens.",
        about_intro: "Hello Passenger est exploité par <strong class=\"text-white\">Bagages du Monde</strong>, partenaire officiel de <strong class=\"text-white\">Aéroports de Paris (Groupe ADP)</strong> depuis 2003. Plus de vingt ans à <strong class=\"text-white\">Paris-Charles de Gaulle (CDG)</strong> et <strong class=\"text-white\">Paris-Orly (ORY)</strong>.",
        about_security: "Sécurité visible. Confiance assurée.",
        about_security_text: "Chaque article : <strong class=\"text-white\">100% contrôle X-ray</strong> ; stockage <strong class=\"text-white\">vidéosurveillé, protégé par alarme</strong> ; <strong class=\"text-white\">accès contrôlé</strong> ; <strong class=\"text-white\">entièrement traçable</strong>. Procédures conformes au <strong class=\"text-white\">CSI (Code de sécurité intérieure)</strong>.",
        about_xray_caption: "100% contrôle X-ray — Chaque article contrôlé",
        about_culture: "Une culture de responsabilité",
        about_culture_text: "Tenues, carte d'identité, habilitation sécurité, formation continue, attention. Nos équipes à CDG et ORY sont formées, certifiées et habilitées. La confiance se construit en face à face.",
        about_professionals: "Les professionnels qui rendent tout possible",
        about_professionals_text: "Rencontrez les personnes derrière le service — nos équipes à CDG et ORY rendent la gestion de vos bagages fluide et sécurisée.",
        about_team: "Équipe",
        about_team_desc: "Sécurité et plus de 20 ans d'expérience",
        about_client_exp: "Expérience client",
        about_support: "Support dédié",
        about_monitored: "Stockage surveillé",
        about_cctv: "Vidéosurveillance et alarme",
        about_why: "Pourquoi nous choisir",
        about_why_1: "100% contrôle X-ray pour chaque article",
        about_why_2: "Stockage vidéosurveillé et protégé par alarme",
        about_why_3: "Plus de 20 ans à Paris CDG et Orly",
        about_why_4: "Équipes formées, certifiées, habilitées sécurité",
        about_why_5: "Approuvé par les voyageurs du monde entier",
        back_home: "← Retour à l'accueil",
        faq_title: "Centre d'aide",
        faq_subtitle: "Réponses à vos questions",
        faq_info_title: "Informations et horaires",
        faq_info_text: "Lors d'une escale à <strong class=\"text-white\">Paris Charles de Gaulle (CDG)</strong> ou <strong class=\"text-white\">Paris Orly (ORY)</strong>, voyager léger fait toute la différence. <strong class=\"text-white\">Hello Passenger</strong> propose gestion sécurisée des bagages, transport et support dédié aux aéroports parisiens.",
        faq_info_sub: "Vous trouverez ci-dessous les réponses aux questions les plus fréquentes sur la consigne à Paris, le transit aéroport, les bagages perdus et les services voyageurs.",
        faq_what_title: "Que faire",
        faq_what_text: "Planifiez vos bagages avec Hello Passenger : <strong class=\"text-white\">réservez</strong> sur notre plateforme sécurisée, <strong class=\"text-white\">choisissez votre service</strong> (dépôt aéroport, meet & collect ou transport), puis <strong class=\"text-white\">voyagez sereinement</strong> pendant que nous gérons vos bagages. Votre bon numérique est émis instantanément et disponible par email et compte personnel.",
        faq_what_1: "Finalisez votre réservation en quelques étapes.",
        faq_what_2: "Sélectionnez la gestion de vos bagages (dépôt à l'agence, meet & collect ou transport coordonné).",
        faq_what_3: "Déposez ou remettez vos bagages à l'heure et au lieu convenus.",
        faq_what_4: "Récupérez vos bagages au retour ou faites-les livrer comme prévu.",
        faq_faq_title: "Questions fréquentes",
        faq_q1: "Que faire si mes bagages sont perdus à l'aéroport ?",
        home_hero_title: "HelloPassenger facilite<br />votre voyage à Paris !",
        home_transport_label: "TRANSPORT DE BAGAGES",
        home_transport_title: "Voyagez léger :<br />nous acheminons<br />vos bagages !",
        home_storage_label: "CONSIGNE À BAGAGES",
        home_storage_title: "Une escale à Paris ?<br />Nous gardons<br />vos bagages !",
        home_about_title: "Avec HelloPassenger...",
        home_about_subtitle: "Voyagez malin et voyagez bien !",
        home_about_text1: "Nous vous proposons une solution innovante pour simplifier vos déplacements. Que vous soyez en voyage d'affaires ou en vacances, HelloPassenger vous accompagne pour un transport plus simple et plus pratique.",
        home_about_text2: "HelloPassenger vous accompagne et vous livre partout en France ! Pas de souci plus besoin de porter vos bagages ! Nous nous occupons de tout.",
        home_see_offers: "Voir toutes nos offres",
        home_stroller_rental: "Location<br />de poussettes",
        home_lost_objects: "Objets perdus",
        home_lockers: "Vestiaires",
        home_discover_services: "Découvrez<br />tous nos services",
        home_platform_title: "HelloPassenger :",
        home_platform_subtitle1: "Votre plateforme de réservation",
        home_platform_subtitle2: "de services dans les aéroports Parisiens",
        home_feature1_title: "TROUVEZ ET RÉSERVEZ<br />VOS SERVICES",
        home_feature1_text: "Réservez en quelques clics tous les services dont vous avez besoin pour votre voyage.",
        home_feature2_title: "PRÉPAREZ<br />VOS VACANCES",
        home_feature2_text: "Anticipez et organisez votre voyage pour partir serein et détendu.",
        home_feature3_title: "PROFITEZ<br />DE VOTRE SÉJOUR",
        home_feature3_text: "Voyagez l'esprit tranquille en ayant tout organisé à l'avance.",
        
        // Booking Form
        form_title: "Réserver une consigne",
        form_reset: "Réinitialiser",
        form_description: "Sélectionnez le type de consigne et suivez les étapes du formulaire. Nous vous indiquerons les informations à fournir.",
        breadcrumb_home: "Accueil",
        breadcrumb_booking: "Réserver une consigne",
        form_required_fields: "* Tous les champs sont obligatoires",
        form_airport_label: "DANS QUEL AÉROPORT SOUHAITEZ-VOUS LAISSER VOS BAGAGES ? *",
        form_select_airport: "Sélectionner un aéroport",
        form_no_products: "Aucun type de bagage disponible pour le moment.",
        form_no_products_retry: "Veuillez réessayer plus tard ou nous contacter.",
        form_deposit_date: "DATE DE DÉPÔT DES BAGAGES *",
        form_deposit_time: "HEURE DE DÉPÔT *",
        form_pickup_date: "DATE DE RÉCUPÉRATION DES BAGAGES *",
        form_pickup_time: "HEURE DE RÉCUPÉRATION *",
        form_check_availability: "VOIR LA DISPONIBILITÉ",
        form_selected_airport: "AÉROPORT SÉLECTIONNÉ",
        form_deposit_short: "DÉPÔT",
        form_pickup_short: "RETRAIT",
        form_choose_luggage: "1. Choisissez vos bagages",
        form_attention: "ATTENTION !",
        form_attention_text: "Les trajets pour la livraison ou la récupération des bagages peuvent inclure les gares : Gare du Nord, Châtelet Les Halles, Gare de Lyon, ou Saint-Michel Notre-Dame.",
        form_partner_text: "Vous êtes un professionnel du tourisme ? Facilitez le voyage de vos clients !",
        form_become_partner: "DEVENIR PARTENAIRE →",
        form_total_price: "Tarif TOTAL",
        form_empty_cart: "Votre panier est vide :(",
        form_total: "Total:",
        form_your_cart: "Votre panier",
        form_proceed_payment: "Procéder au paiement",
        
        // Payment Success
        success_title: "Paiement réussi !",
        success_subtitle: "Votre commande a été confirmée et votre facture a été générée.",
        success_download: "Télécharger ma facture",
        success_preview: "Aperçu de la facture",
        
        // Reservations
        reservations_title: "Mes Réservations",
        reservations_success: "Succès!",
        reservations_empty: "Vous n'avez pas encore de commandes.",
        reservations_order_id: "ID Commande",
        reservations_platform: "Plateforme",
        reservations_total: "Total TTC",
        reservations_status: "Statut",
        reservations_date: "Date",
        reservations_details: "Détails",
        
        // Modals
        modal_optimize_title: "Optimisez votre expérience !",
        modal_optimize_subtitle: "Ajoutez nos services exclusifs pour un voyage sans tracas.",
        modal_priority_label: "PRIORITAIRE",
        modal_priority_title: "Service Priority",
        modal_priority_desc: "Bénéficiez d'un traitement prioritaire pour vos bagages à la dépose et à la récupération.",
        modal_premium_label: "PREMIUM",
        modal_premium_title: "Service Premium",
        modal_premium_desc: "Permet de remettre ou récupérer ses bagages directement à l'endroit exact choisi à l'aéroport, avec l'aide d'un porteur dédié. Le client indique le lieu, son mode de transport et un commentaire, et l'équipe s'occupe de tout.",
        modal_premium_unavailable: "Service Premium indisponible",
        modal_premium_unavailable_reason: "Pour afficher l'option Premium : choisissez une date de dépôt au moins 72 h (3 jours) dans le futur. Des lieux de prise en charge doivent être disponibles pour votre aéroport.",
        form_premium_hint_72h: "Pour afficher l'option Service Premium, choisissez une date de dépôt au moins 3 jours à l'avance.",
        modal_add_cart: "Ajouter au panier",
        modal_remove_cart: "Enlever du panier",
        modal_validate_continue: "Valider et continuer →",
        modal_edit_dates: "Modifier les dates",
        
        // Payment Page Details
        payment_security_title: "Votre sécurité est notre priorité",
        payment_security_text: "Afin de garantir la protection de vos effets personnels et de respecter les normes de sécurité par rayons X, merci de compléter les informations manquantes.",
        payment_order_summary: "Récapitulatif de votre commande",
        payment_service: "Service :",
        payment_luggage_storage: "Consigne de bagage",
        
        // Baggage types
        luggage_accessoires: "Accessoires",
        luggage_bagage_cabine: "Bagage cabine",
        luggage_bagage_soute: "Bagage soute",
        luggage_bagage_special: "Bagage spécial",
        luggage_vestiaire: "Vestiaire",
        luggage_accessoires_desc: "Petits objets comme un sac à main, un ordinateur portable ou un casque.",
        luggage_bagage_cabine_desc: "Valise de taille cabine, généralement jusqu'à 55x35x25 cm.",
        luggage_bagage_soute_desc: "Grande valise enregistrée en soute.",
        luggage_bagage_special_desc: "Objets volumineux ou hors format comme un équipement de sport ou un instrument de musique.",
        luggage_vestiaire_desc: "Pour les manteaux, vestes ou autres vêtements sur cintre.",
        
        // Time units
        time_days: "jour(s)",
        time_day: "jour",
        time_hours: "heure(s)",
        time_hour: "heure",
        time_minutes: "minute(s)",
        time_and: "et",
        time_per_hour: "/ heure",
        time_per_day: "/ jour",
        payment_airport: "Aéroport :",
        payment_duration: "Durée totale",
        payment_from: "Du :",
        payment_to: "Au :",
        payment_quantity: "Quantité :",
        payment_total_normal: "Total normal",
        payment_discount_online: "Promotion réservation en ligne",
        payment_total_to_pay: "Total à payer",
        cart_subtotal: "Sous-total",
        cart_discount_online: "Offre réservation en ligne : -10% (consigne bagages uniquement)",
        
        // Dashboard Client
        dashboard_welcome: "Bienvenue",
        dashboard_subtitle: "Gérez vos réservations et votre compte",
        dashboard_total_reservations: "Total réservations",
        dashboard_total_spent: "Total dépensé",
        dashboard_account_status: "Statut compte",
        dashboard_active: "Actif",
        dashboard_new_reservation: "Nouvelle réservation",
        dashboard_book_now: "Réserver maintenant",
        dashboard_my_reservations: "Mes réservations",
        dashboard_view_all: "Voir toutes mes réservations",
        dashboard_edit_profile: "Modifier profil",
        dashboard_update_info: "Mettre à jour mes informations",
        dashboard_help: "Aide & Support",
        dashboard_get_help: "Obtenir de l'aide",
        dashboard_recent_reservations: "Réservations récentes",
        dashboard_no_reservations: "Vous n'avez pas encore de réservations",
        dashboard_create_first: "Créer ma première réservation",
        dashboard_order_ref: "Référence",
        dashboard_date: "Date",
        dashboard_amount: "Montant",
        dashboard_status: "Statut",
        dashboard_actions: "Actions",
        dashboard_view_invoice: "Voir facture",
        dashboard_view_all_reservations: "Voir toutes mes réservations",
        
        // Profile Page
        profile_title: "Mon Profil",
        profile_subtitle: "Gérez vos informations personnelles",
        profile_email_note: "L'email ne peut pas être modifié",
        
        // Header Menu
        header_admin: "Administrateur",
        header_my_dashboard: "Mon tableau de bord",
        header_my_reservations: "Mes réservations",
        header_new_reservation: "Nouvelle réservation",
        header_admin_dashboard: "Dashboard",
        header_orders: "Commandes",
        header_users: "Utilisateurs",
        header_analytics: "Analytiques",
        
        // Status
        status_completed: "Terminé",
        status_pending: "En attente",
        status_cancelled: "Annulé",
        status_processing: "En cours",
        
        payment_error: "Erreur!",
        payment_no_data: "Aucune donnée de commande trouvée. Votre session a peut-être expiré.",
        payment_back_form: "Retour au formulaire",
        payment_complete_info: "Veuillez compléter vos informations pour activer le paiement.",
        payment_reset: "Réinitialiser et recommencer",
        payment_reset_title: "Réinitialiser la commande",
        payment_reset_text: "Voulez-vous vraiment continuer ? Toutes les données saisies pour votre commande actuelle seront définitivement perdues.",
        payment_reset_cancel: "Annuler",
        payment_reset_confirm: "Confirmer",
        payment_debug_title: "Aperçu des données de commande (JSON)",
        address_counter_suffix: "/50 caractères",

        // Alerts & Prompts
        prompt_contact_title: "Comment pouvons-nous vous joindre ?",
        prompt_contact_subtitle: "C’est sur ce mail que vous recevrez la confirmation de réservation.",
        prompt_contact_placeholder: "Adresse e-mail",
        alert_open_login_error: "Impossible d'ouvrir la fenêtre de connexion.",
        alert_validation_error_title: "Erreur de validation",
        alert_unknown_error: "Une erreur inconnue est survenue.",
        alert_tech_error: "Une erreur technique est survenue.",
        alert_update_error_title: "Erreur de mise à jour",
        alert_network_error: "Une erreur réseau est survenue.",
        alert_fix_errors: "Veuillez corriger les erreurs suivantes :",
        alert_fill_required: "Veuillez remplir tous les champs obligatoires correctement.",
        alert_missing_data_title: "Données manquantes",
        alert_missing_data_message: "Impossible de vérifier la disponibilité sans aéroport, date et heure.",
        alert_availability_error: "Une erreur technique est survenue lors de la vérification de la disponibilité.",
        alert_incomplete_fields_title: "Champs incomplets",
        alert_incomplete_fields_message: "Veuillez remplir tous les champs : aéroport, dates et heures de dépôt et de retrait.",
        alert_agency_closed_title: "Agence fermée",
        alert_check_dates_message: "Veuillez vérifier les dates et heures de dépôt et de récupération.",
        alert_return_after_dropoff: "La date de récupération doit être postérieure à la date de dépôt.",
        alert_pricing_error_title: "Erreur de tarification",
        alert_pricing_error_message: "Erreur lors de la récupération des tarifs :",
        alert_invalid_response: "Réponse invalide",
        agency_hours_message: "Notre agence est ouverte de 07h00 à 21h00 7/7. ",
        agency_hours_both_out: "Les horaires de dépôt et de retrait sont en dehors des heures d'ouverture.",
        agency_hours_dropoff_out: "L'horaire de dépôt est en dehors des heures d'ouverture.",
        agency_hours_pickup_out: "L'horaire de retrait est en dehors des heures d'ouverture.",
        agency_hours_contact: "Pour toutes demandes hors horaire merci de nous contacter au +33 <strong>1 34 38 58 98</strong>.",
        alert_pricing_fetch_error: "Une erreur technique est survenue lors de la récupération des tarifs.",
        alert_empty_cart_title: "Panier vide",
        alert_empty_cart_message: "Votre panier est vide.",
        alert_options_pricing_title: "Info Tarification Options",
        alert_options_pricing_fallback: "Impossible de récupérer les prix des options pour le moment. Vous pouvez continuer sans.",
        alert_options_pricing_error_title: "Erreur Technique",
        alert_options_pricing_error_message: "Une erreur technique est survenue lors de la récupération des prix des options. Vous pouvez continuer sans.",
        date_invalid_title: "Date invalide",
        date_invalid_after_dropoff: "La date de retrait doit être postérieure à la date de dépôt.",
        date_invalid_same_day_min: "Pour une réservation le même jour, un délai minimum de 3 heures est requis entre le dépôt et le retrait.",
        date_update_error: "Une erreur est survenue lors de la mise à jour des dates.",
        modal_ok: "OK",
        modal_cancel: "Annuler",
        modal_confirm: "Confirmer",
        prompt_invalid_email: "Veuillez entrer une adresse e-mail valide.",
        login_guest_title: "Comment souhaitez-vous procéder ?",
        login_guest_message: "Connectez-vous pour utiliser vos informations enregistrées ou continuez en tant qu'invité.",
        login_guest_continue: "Continuer en invité",
        login_guest_login: "Se connecter",
        modal_continue_no_thanks: "Je ne suis pas intéressé - Continuer →",
        premium_section_arrival: "J'arrive à l'aéroport",
        premium_section_departure: "Je pars de l'aéroport",
        premium_required_both: "Vous devez remplir tous les champs obligatoires pour les DEUX sections (arrivée ET départ).",
        premium_required_section: "Veuillez remplir tous les champs obligatoires dans la section : {section}",
        premium_form_incomplete_title: "Formulaire incomplet",
        premium_important: "Important",
        premium_both_required: "Vous devez remplir les informations pour les DEUX sens (arrivée ET départ) pour bénéficier du service Premium complet.",
        premium_required_fields_info: "Les champs marqués d'un",
        premium_arrival_alt: "Arrivée",
        premium_arrival_title: "J'arrive à l'aéroport",
        premium_arrival_subtitle: "Récupération de vos bagages - Tous les champs requis",
        premium_transport_label: "Moyen de transport",
        premium_select_placeholder: "Sélectionner...",
        premium_transport_airport: "Aéroport",
        premium_transport_public: "Transport en commun",
        premium_transport_train: "Train",
        premium_transport_other: "Autre",
        premium_transport_flight: "Avion",
        premium_arrival_date: "Date d'arrivée",
        premium_pickup_location: "Lieu de prise en charge",
        premium_pickup_time: "Heure de prise en charge",
        premium_additional_info: "Informations complémentaires",
        premium_optional: "optionnel",
        premium_arrival_placeholder: "Précisions supplémentaires pour faciliter la prise en charge...",
        premium_departure_alt: "Départ",
        premium_departure_title: "Je pars de l'aéroport",
        premium_departure_subtitle: "Dépôt de vos bagages - Tous les champs requis",
        premium_departure_date: "Date de départ",
        premium_restitution_location: "Lieu de restitution",
        premium_restitution_time: "Heure de restitution",
        premium_departure_placeholder: "Précisions supplémentaires pour faciliter la restitution...",
        premium_empty_state: "Cliquez sur \"Ajouter au panier\" pour remplir les détails du service Premium",
        premium_flight_number: "Numéro de vol",
        premium_train_number: "Indicatif de ligne",
        premium_flight_placeholder: "Ex: AF1234",
        premium_train_placeholder: "Ex: TGV 8523",
        phone_invalid: "Numéro invalide",
        phone_invalid_country: "Code pays invalide",
        phone_too_short: "Numéro trop court",
        phone_too_long: "Numéro trop long",
        phone_country_code_hint: "⚠️ Veuillez renseigner votre numéro avec le code pays (ex: +33 pour la France, +230 pour Maurice)",
        phone_invalid_format: "Format invalide",
    },
    en: {
        // Header & Navigation
        menu: "MENU",
        nav_about: "About Us",
        nav_faq: "FAQ",
        nav_book: "Book now",
        nav_home: "Home",
        login_btn: "Login",
        create_account_short: "Register",
        logout_btn: "Logout",
        admin_access: "Admin Access",
        book: "BOOK",
        disconnect: "DISCONNECT",
        
        // Modal
        modal_title: "Your security, our priority!",
        modal_subtitle: "To protect your belongings and comply with airport standards (X-ray screening), please complete your contact information.",
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
        label_email: "Email",
        label_password: "Password",
        
        // Placeholders
        placeholder_prenom: "First name",
        placeholder_nom: "Last name",
        placeholder_telephone: "+33 6 12 34 56 78 (with country code)",
        placeholder_pays: "e.g., France",
        
        // Options
        option_mr: "Mr.",
        option_mrs: "Mrs.",
        
        // Buttons
        btn_complete_profile: "Complete my profile (optional)",
        btn_hide_fields: "Hide optional fields",
        btn_cancel: "Cancel",
        btn_confirm_pay: "Confirm and pay",
        btn_submit: "Submit",
        btn_back: "Back",
        btn_continue: "Continue",
        btn_modify: "Edit",
        btn_delete: "Delete",
        btn_save: "Save",
        btn_close: "Close",
        
        // Page sections
        modal_section_1: "Your contact details",
        your_info: "Your information",
        secure_payment: "Secure payment",
        order_summary: "Order summary",
        
        // Payment page
        name: "Name",
        email: "Email",
        phone: "Phone",
        address: "Address",
        company: "Company",
        city: "City",
        postal_code: "Postal Code",
        country: "Country",
        edit: "Edit",
        not_provided: "Not provided",
        
        // Login/Register
        login_title: "Login",
        login_subtitle: "Access your account",
        register_title: "Create an account",
        register_subtitle: "Join HelloPassenger",
        your_email: "YOUR EMAIL ADDRESS:",
        your_password: "YOUR PASSWORD:",
        forgot_password: "Forgot password?",
        remember_me: "Remember me",
        no_account: "No account yet?",
        already_account: "Already have an account?",
        create_account: "CREATE AN ACCOUNT →",
        create_my_account: "CREATE MY ACCOUNT",
        nom: "LAST NAME:",
        prenom: "FIRST NAME:",
        email: "EMAIL ADDRESS:",
        telephone: "PHONE:",
        password: "PASSWORD:",
        confirm_password: "CONFIRM PASSWORD:",
        login_error_title: "Login Error",
        login_error_message: "Invalid credentials — please try again.",
        close_btn: "Close",
        my_account: "My account",
        logout_btn: "Logout",
        
        // Common
        required: "Required",
        optional: "Optional",
        loading: "LOADING",
        error: "Error",
        success: "Success",
        warning: "Warning",
        
        // Footer
        footer_about: "About",
        footer_terms: "Terms of use",
        footer_privacy: "Privacy policy",
        footer_contact: "Contact us",
        footer_rights: "All rights reserved",
        home: "Hello Passenger Home",
        footer_locate: "Locate Us",
        footer_quick_links: "Quick Links",
        footer_transport: "Transport of luggage",
        footer_left_luggage: "Left luggage",
        footer_lost_object: "Recover my lost object",
        footer_description: "Hello Passenger is a platform that allows you to book a transport of luggage to or from the airport as well as to store your luggage in consignment in our agency located in Paris CDG and Paris ORLY.",
        footer_cdg: "Terminal 2, TGV station – Level 4, opposite Sheraton Hotel, between 2C and 2E.",
        footer_orly: "Terminal 3, arrival level.",
        footer_since: "since 2015",
        footer_credits: "© <span>Hello Passenger</span> 2026. All rights reserved.",
        footer_access: "Access Plan",
        footer_cdg_address: "Terminal 2 - TGV Station - Level 4<br>Opposite Sheraton Hotel",
        footer_orly_address: "Terminal 3 - Arrival Level",
        footer_email: "Email",
        footer_follow: "Follow Us",
        footer_links: "Quick Links",
        footer_services: "Services",
        footer_faq: "FAQ",
        footer_contact_link: "Contact",
        footer_book: "Book Now ↗",
        promo_intro: "Enjoy €10 off your booking with the code",
        footer_copyright: "© 2026 by Hello Passenger. All Rights Reserved.",
        footer_created: "Created by <span class=\"text-yellow-500 font-bold\">Blablabla Agency</span>.",
        
        // Errors
        error_required_field: "This field is required",
        error_invalid_email: "Invalid email",
        error_invalid_phone: "Invalid phone number",
        error_invalid_postal_code: "Invalid postal code",
        
        // Home Page
        hero_discover: "Discover",
        hero_title: "easy airport<br>travel solutions",
        hero_desc: "Travel Light, Luggage Free with our platform.",
        intro_title: "Traveling has never been easier!",
        intro_text: "Hello Passenger is a specialist in airport passenger services. Whether you are traveling to or from Paris, visiting or leaving, alone, as a couple, or with children, we make your journey easy and stress-free.",
        stat_experience: "Experience",
        stat_success: "Success Rate",
        stat_since: "At your service since",
        services_primary: "Our Primary Services",
        services_sub: "Transport and storage at Paris CDG & Orly",
        service_transport: "Transport of Luggage",
        service_transport_desc: "Door-to-door or airport transfer for your bags. Reliable and tracked.",
        service_storage: "Luggage Storage",
        service_storage_desc: "Store your bags at our counters at CDG Terminal 2 and Orly Terminal 3. Secure and flexible.",
        read_more: "Read more →",
        svc_left: "Left Luggage Facilities",
        svc_left_desc: "Secure storage at the airport so you can explore Paris hands-free.",
        svc_lost: "Lost Items",
        svc_lost_desc: "We help recover lost luggage and items at the airport.",
        svc_children: "Children's Equipment",
        svc_children_desc: "Strollers and family-friendly services for a smooth journey.",
        svc_tech: "High Tech Equipment",
        svc_tech_desc: "SIM cards, pocket Wi‑Fi and tech rentals at the airport.",
        process_title: "Our Process",
        process_sub: "Your luggage experience, our priority",
        process_1: "Reserve",
        process_1_desc: "Complete your reservation in just a few steps through our secure platform. Your digital voucher is instantly issued and accessible from your email and personal account.",
        process_2: "Choose your service",
        process_2_desc: "Decide how your luggage is handled. Drop off at our dedicated airport facility, benefit from a personalised meet & collect service, or arrange coordinated transport to or from the airport.",
        process_3: "Travel with confidence",
        process_3_desc: "Move freely while we take care of your luggage. Handled with professionalism, discretion and the highest standards of security, from start to finish.",
        testimonial_title: "Trusted by Worldwide Travelers",
        testimonial_sub: "From first-time customers to our most loyal commuters, see why people love the service of Hello Passenger.",
        testimonial_1: "Had a very positive experience. Left 6/7 items of luggage including a computer and other stuff and it cost around €60 for 4/5 hours — long enough to take the train into town for a walk. Friendly and efficient service.",
        testimonial_2: "Professional, trustworthy and reasonably priced. They returned our lost luggage to us quickly and easily. We are grateful for this organization and the people who work here.",
        testimonial_3: "This place is great for storing your luggage while you take a trip into Paris Central. Bags are weighed and charged by weight and time. The premises are well organised, the staff are very well organised. We collected our bags earlier than planned and got a refund.",
        testimonial_4: "Straightforward bag storage and pickup to free you for a day out in Paris without worrying about luggage.",
        trust_reviews: "<strong>Rating of 4.3/5</strong> — Google Reviews",
        trust_benefits: "<strong>Exclusive online benefits</strong>",
        trust_secure: "<strong>100% secure payment</strong>",
        trust_clickcollect: "<strong>FREE Click & Collect</strong>",
        about_title: "Rooted in Paris Airports. Driven by People.",
        about_intro: "Hello Passenger is operated by <strong class=\"text-white\">Bagages du Monde</strong>, official partner of <strong class=\"text-white\">Aéroports de Paris (Groupe ADP)</strong> since 2003. Over twenty years at <strong class=\"text-white\">Paris-Charles de Gaulle (CDG)</strong> and <strong class=\"text-white\">Paris-Orly (ORY)</strong>.",
        about_security: "Security You Can See. People You Can Trust.",
        about_security_text: "Every item: <strong class=\"text-white\">100% X-ray control</strong>; <strong class=\"text-white\">CCTV-monitored, alarm-protected</strong> storage; <strong class=\"text-white\">controlled access</strong>; <strong class=\"text-white\">fully traceable</strong>. Procedures in line with <strong class=\"text-white\">CSI (Code de sécurité intérieure)</strong>.",
        about_xray_caption: "100% X-ray control — Every item screened",
        about_culture: "A Culture of Responsibility",
        about_culture_text: "Uniforms, ID, security clearance, continuous training, attentiveness. Our teams at CDG and ORY are trained, certified, and security-cleared. Trust is built face to face.",
        about_professionals: "The Professionals Who Make It Possible",
        about_professionals_text: "Meet the people behind the service — our teams at CDG and ORY make your luggage handling smooth and secure.",
        about_team: "Team",
        about_team_desc: "Security and 20+ years of experience",
        about_client_exp: "Client experience",
        about_support: "Dedicated support",
        about_monitored: "Monitored storage",
        about_cctv: "CCTV & alarm-protected",
        about_why: "Why Choose Us",
        about_why_1: "100% X-ray control for every item",
        about_why_2: "CCTV-monitored, alarm-protected storage",
        about_why_3: "20+ years at Paris CDG and Orly",
        about_why_4: "Trained, certified, security-cleared teams",
        about_why_5: "Trusted by travelers worldwide",
        back_home: "← Back to home",
        faq_title: "Help Center",
        faq_subtitle: "Answers to your questions",
        faq_info_title: "Information and timetables",
        faq_info_text: "During a layover at <strong class=\"text-white\">Paris Charles de Gaulle (CDG)</strong> or <strong class=\"text-white\">Paris Orly (ORY) airport</strong>, traveling light makes all the difference. <strong class=\"text-white\">Hello Passenger</strong> provides secure luggage handling, transport services, and dedicated support at Paris airports.",
        faq_info_sub: "Below you'll find answers to the most frequently asked questions about luggage storage at Paris airports, airport transit, lost baggage, and passenger services.",
        faq_what_title: "What to do",
        faq_what_text: "Plan your luggage with Hello Passenger: <strong class=\"text-white\">reserve</strong> on our secure platform, <strong class=\"text-white\">choose your service</strong> (airport drop-off, meet &amp; collect, or transport), then <strong class=\"text-white\">travel with confidence</strong> while we handle your bags. Your digital voucher is issued instantly and is available from your email and personal account.",
        faq_what_1: "Complete your reservation in a few steps.",
        faq_what_2: "Select how your luggage is handled (drop-off at our facility, meet &amp; collect, or coordinated transport).",
        faq_what_3: "Drop off or hand over your luggage at the agreed time and place.",
        faq_what_4: "Collect your luggage when you return or have it delivered as arranged.",
        faq_faq_title: "Frequently asked questions",
        faq_q1: "What should I do if my baggage is lost at the airport?",
        home_hero_title: "HelloPassenger makes<br />your trip to Paris easier!",
        home_transport_label: "LUGGAGE TRANSPORT",
        home_transport_title: "Travel light:<br />we ship<br />your luggage!",
        home_storage_label: "LUGGAGE STORAGE",
        home_storage_title: "Stopover in Paris?<br />We keep<br />your luggage!",
        home_about_title: "With HelloPassenger...",
        home_about_subtitle: "Travel smart and travel well!",
        home_about_text1: "We offer you an innovative solution to simplify your travels. Whether you are on a business trip or on vacation, HelloPassenger accompanies you for simpler and more practical transport.",
        home_about_text2: "HelloPassenger accompanies you and delivers anywhere in France! No worries, no need to carry your luggage! We take care of everything.",
        home_see_offers: "See all our offers",
        home_stroller_rental: "Stroller<br />rental",
        home_lost_objects: "Lost objects",
        home_lockers: "Lockers",
        home_discover_services: "Discover<br />all our services",
        home_platform_title: "HelloPassenger:",
        home_platform_subtitle1: "Your booking platform",
        home_platform_subtitle2: "for services at Paris airports",
        home_feature1_title: "FIND AND BOOK<br />YOUR SERVICES",
        home_feature1_text: "Book in a few clicks all the services you need for your trip.",
        home_feature2_title: "PREPARE<br />YOUR VACATION",
        home_feature2_text: "Anticipate and organize your trip to leave serene and relaxed.",
        home_feature3_title: "ENJOY<br />YOUR STAY",
        home_feature3_text: "Travel with peace of mind having everything organized in advance.",
        
        // Booking Form
        form_title: "Book a storage",
        form_reset: "Reset",
        form_description: "Select the type of storage and follow the form steps. We will indicate the information to provide.",
        breadcrumb_home: "Home",
        breadcrumb_booking: "Book a storage",
        form_required_fields: "* All fields are required",
        form_airport_label: "WHICH AIRPORT DO YOU WISH TO LEAVE YOUR LUGGAGE AT? *",
        form_select_airport: "Select an airport",
        form_no_products: "No luggage type available at the moment.",
        form_no_products_retry: "Please try again later or contact us.",
        form_deposit_date: "LUGGAGE DROP-OFF DATE *",
        form_deposit_time: "DROP-OFF TIME *",
        form_pickup_date: "LUGGAGE PICKUP DATE *",
        form_pickup_time: "PICKUP TIME *",
        form_check_availability: "CHECK AVAILABILITY",
        form_selected_airport: "SELECTED AIRPORT",
        form_deposit_short: "DROP-OFF",
        form_pickup_short: "PICKUP",
        form_choose_luggage: "1. Choose your luggage",
        form_attention: "ATTENTION!",
        form_attention_text: "Journeys for delivery or collection of luggage may include the stations: Gare du Nord, Châtelet Les Halles, Gare de Lyon, or Saint-Michel Notre-Dame.",
        form_partner_text: "Are you a tourism professional? Make your customers' trip easier!",
        form_become_partner: "BECOME A PARTNER →",
        form_total_price: "TOTAL Price",
        form_empty_cart: "Your cart is empty :(",
        form_total: "Total:",
        form_your_cart: "Your cart",
        form_proceed_payment: "Proceed to payment",
        
        // Payment Success
        success_title: "Payment successful!",
        success_subtitle: "Your order has been confirmed and your invoice has been generated.",
        success_download: "Download my invoice",
        success_preview: "Invoice preview",
        
        // Reservations
        reservations_title: "My Reservations",
        reservations_success: "Success!",
        reservations_empty: "You don't have any orders yet.",
        reservations_order_id: "Order ID",
        reservations_platform: "Platform",
        reservations_total: "Total incl. VAT",
        reservations_status: "Status",
        reservations_date: "Date",
        reservations_details: "Details",
        
        // Modals
        modal_optimize_title: "Optimize your experience!",
        modal_optimize_subtitle: "Add our exclusive services for a hassle-free trip.",
        modal_priority_label: "PRIORITY",
        modal_priority_title: "Priority Service",
        modal_priority_desc: "Benefit from priority treatment for your luggage at drop-off and pick-up.",
        modal_premium_label: "PREMIUM",
        modal_premium_title: "Premium Service",
        modal_premium_desc: "Allows you to drop off or pick up your luggage directly at the exact location chosen at the airport, with the help of a dedicated porter. The customer indicates the location, their mode of transport and a comment, and the team takes care of everything.",
        modal_premium_unavailable: "Premium Service unavailable",
        modal_premium_unavailable_reason: "To show the Premium option: choose a drop-off date at least 72 hours (3 days) in the future. Pick-up locations must be available for your airport.",
        form_premium_hint_72h: "To see the Premium service option, choose a drop-off date at least 3 days in advance.",
        modal_add_cart: "Add to cart",
        modal_remove_cart: "Remove from cart",
        modal_validate_continue: "Validate and continue →",
        modal_edit_dates: "Edit dates",
        
        // Payment Page Details
        payment_security_title: "Your security is our priority",
        payment_security_text: "To ensure the protection of your personal belongings and comply with X-ray security standards, please complete the missing information.",
        payment_order_summary: "Your order summary",
        payment_service: "Service:",
        payment_luggage_storage: "Luggage storage",
        
        // Baggage types
        luggage_accessoires: "Accessories",
        luggage_bagage_cabine: "Cabin baggage",
        luggage_bagage_soute: "Checked baggage",
        luggage_bagage_special: "Special baggage",
        luggage_vestiaire: "Cloakroom",
        luggage_accessoires_desc: "Small items like a handbag, laptop, or headphones.",
        luggage_bagage_cabine_desc: "Cabin-size suitcase, typically up to 55x35x25 cm.",
        luggage_bagage_soute_desc: "Large suitcase checked in hold.",
        luggage_bagage_special_desc: "Bulky or oversized items like sports equipment or a musical instrument.",
        luggage_vestiaire_desc: "For coats, jackets, or other hanging garments.",
        
        // Time units
        time_days: "day(s)",
        time_day: "day",
        time_hours: "hour(s)",
        time_hour: "hour",
        time_minutes: "minute(s)",
        time_and: "and",
        time_per_hour: "/ hour",
        time_per_day: "/ day",
        payment_airport: "Airport:",
        payment_duration: "Total duration",
        payment_from: "From:",
        payment_to: "To:",
        payment_quantity: "Quantity:",
        payment_total_normal: "Normal total",
        payment_discount_online: "Online booking promotion",
        payment_total_to_pay: "Total to pay",
        cart_subtotal: "Subtotal",
        cart_discount_online: "Online booking offer: -10% (luggage storage only)",
        
        // Dashboard Client
        dashboard_welcome: "Welcome",
        dashboard_subtitle: "Manage your reservations and account",
        dashboard_total_reservations: "Total reservations",
        dashboard_total_spent: "Total spent",
        dashboard_account_status: "Account status",
        dashboard_active: "Active",
        dashboard_new_reservation: "New reservation",
        dashboard_book_now: "Book now",
        dashboard_my_reservations: "My reservations",
        dashboard_view_all: "View all my reservations",
        dashboard_edit_profile: "Edit profile",
        dashboard_update_info: "Update my information",
        dashboard_help: "Help & Support",
        dashboard_get_help: "Get help",
        dashboard_recent_reservations: "Recent reservations",
        dashboard_no_reservations: "You don't have any reservations yet",
        dashboard_create_first: "Create my first reservation",
        dashboard_order_ref: "Reference",
        dashboard_date: "Date",
        dashboard_amount: "Amount",
        dashboard_status: "Status",
        dashboard_actions: "Actions",
        dashboard_view_invoice: "View invoice",
        dashboard_view_all_reservations: "View all my reservations",
        
        // Profile Page
        profile_title: "My Profile",
        profile_subtitle: "Manage your personal information",
        profile_email_note: "Email cannot be changed",
        
        // Header Menu
        header_admin: "Administrator",
        header_my_dashboard: "My dashboard",
        header_my_reservations: "My reservations",
        header_new_reservation: "New reservation",
        header_admin_dashboard: "Dashboard",
        header_orders: "Orders",
        header_users: "Users",
        header_analytics: "Analytics",
        
        // Status
        status_completed: "Completed",
        status_pending: "Pending",
        status_cancelled: "Cancelled",
        status_processing: "Processing",
        
        payment_error: "Error!",
        payment_no_data: "No order data found. Your session may have expired.",
        payment_back_form: "Back to form",
        payment_complete_info: "Please complete your information to enable payment.",
        payment_reset: "Reset and start over",
        payment_reset_title: "Reset order",
        payment_reset_text: "Do you really want to continue? All data entered for your current order will be permanently lost.",
        payment_reset_cancel: "Cancel",
        payment_reset_confirm: "Confirm",
        payment_debug_title: "Order data preview (JSON)",
        address_counter_suffix: "/50 characters",

        // Alerts & Prompts
        prompt_contact_title: "How can we reach you?",
        prompt_contact_subtitle: "This email will receive your booking confirmation.",
        prompt_contact_placeholder: "Email address",
        alert_open_login_error: "Unable to open the login window.",
        alert_validation_error_title: "Validation error",
        alert_unknown_error: "An unknown error occurred.",
        alert_tech_error: "A technical error occurred.",
        alert_update_error_title: "Update error",
        alert_network_error: "A network error occurred.",
        alert_fix_errors: "Please fix the following errors:",
        alert_fill_required: "Please complete all required fields correctly.",
        alert_missing_data_title: "Missing data",
        alert_missing_data_message: "Unable to check availability without airport, date, and time.",
        alert_availability_error: "A technical error occurred while checking availability.",
        alert_incomplete_fields_title: "Incomplete fields",
        alert_incomplete_fields_message: "Please fill in all fields: airport, drop-off and pick-up dates and times.",
        alert_agency_closed_title: "Agency closed",
        alert_check_dates_message: "Please verify the drop-off and pick-up dates and times.",
        alert_return_after_dropoff: "The pick-up date must be after the drop-off date.",
        alert_pricing_error_title: "Pricing error",
        alert_pricing_error_message: "Error while retrieving rates:",
        alert_invalid_response: "Invalid response",
        agency_hours_message: "Our agency is open from 07:00 to 21:00, 7 days a week. ",
        agency_hours_both_out: "Drop-off and pick-up times are outside opening hours.",
        agency_hours_dropoff_out: "Drop-off time is outside opening hours.",
        agency_hours_pickup_out: "Pick-up time is outside opening hours.",
        agency_hours_contact: "For out-of-hours requests, please contact us at +33 <strong>1 34 38 58 98</strong>.",
        alert_pricing_fetch_error: "A technical error occurred while retrieving rates.",
        alert_empty_cart_title: "Empty cart",
        alert_empty_cart_message: "Your cart is empty.",
        alert_options_pricing_title: "Options Pricing Info",
        alert_options_pricing_fallback: "Unable to retrieve option prices at the moment. You can continue without them.",
        alert_options_pricing_error_title: "Technical error",
        alert_options_pricing_error_message: "A technical error occurred while retrieving option prices. You can continue without them.",
        date_invalid_title: "Invalid date",
        date_invalid_after_dropoff: "The pick-up date must be after the drop-off date.",
        date_invalid_same_day_min: "For same-day bookings, a minimum 3-hour gap is required between drop-off and pick-up.",
        date_update_error: "An error occurred while updating the dates.",
        modal_ok: "OK",
        modal_cancel: "Cancel",
        modal_confirm: "Confirm",
        prompt_invalid_email: "Please enter a valid email address.",
        login_guest_title: "How would you like to proceed?",
        login_guest_message: "Log in to use your saved information or continue as a guest.",
        login_guest_continue: "Continue as guest",
        login_guest_login: "Log in",
        modal_continue_no_thanks: "No thanks - Continue →",
        premium_section_arrival: "I am arriving at the airport",
        premium_section_departure: "I am leaving the airport",
        premium_required_both: "You must complete all required fields for BOTH sections (arrival AND departure).",
        premium_required_section: "Please complete all required fields in the section: {section}",
        premium_form_incomplete_title: "Incomplete form",
        premium_important: "Important",
        premium_both_required: "You must fill in information for BOTH directions (arrival AND departure) to benefit from the full Premium service.",
        premium_required_fields_info: "Fields marked with",
        premium_arrival_alt: "Arrival",
        premium_arrival_title: "I am arriving at the airport",
        premium_arrival_subtitle: "Baggage pickup - All fields required",
        premium_transport_label: "Mode of transport",
        premium_select_placeholder: "Select...",
        premium_transport_airport: "Airport",
        premium_transport_public: "Public transport",
        premium_transport_train: "Train",
        premium_transport_other: "Other",
        premium_transport_flight: "Plane",
        premium_arrival_date: "Arrival date",
        premium_pickup_location: "Pickup location",
        premium_pickup_time: "Pickup time",
        premium_additional_info: "Additional information",
        premium_optional: "optional",
        premium_arrival_placeholder: "Extra details to facilitate pickup...",
        premium_departure_alt: "Departure",
        premium_departure_title: "I am leaving the airport",
        premium_departure_subtitle: "Baggage drop-off - All fields required",
        premium_departure_date: "Departure date",
        premium_restitution_location: "Drop-off location",
        premium_restitution_time: "Drop-off time",
        premium_departure_placeholder: "Extra details to facilitate drop-off...",
        premium_empty_state: "Click \"Add to cart\" to fill in Premium service details",
        premium_flight_number: "Flight number",
        premium_train_number: "Train line code",
        premium_flight_placeholder: "e.g., AF1234",
        premium_train_placeholder: "e.g., TGV 8523",
        phone_invalid: "Invalid number",
        phone_invalid_country: "Invalid country code",
        phone_too_short: "Number too short",
        phone_too_long: "Number too long",
        phone_invalid_format: "Invalid format",
        phone_country_code_hint: "⚠️ Please enter your number with the country code (e.g., +33 for France, +230 for Mauritius)",
    }
};
}  // Close the if (typeof window.translations === 'undefined')

function getCurrentLanguage() {
    return localStorage.getItem('app_language') || 'fr';
}

function translateKey(key, fallback) {
    const lang = getCurrentLanguage();
    return (window.translations[lang] && window.translations[lang][key]) || fallback || (window.translations.fr && window.translations.fr[key]) || key;
}

window.getCurrentLanguage = getCurrentLanguage;
window.translateKey = translateKey;
window.t = (key, fallback) => translateKey(key, fallback);

window.currentLang = window.currentLang || (localStorage.getItem('app_language') || 'fr');

function applyLanguage(lang) {
    console.warn('=== APPLYING LANGUAGE:', lang, '===');
    window.currentLang = lang;
    localStorage.setItem('app_language', lang);
    
    // Update all text with data-i18n
    const elements = document.querySelectorAll('[data-i18n]');
    console.log('Found', elements.length, 'elements to translate');
    
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (window.translations[lang][key]) {
            const oldText = el.textContent;
            el.innerHTML = window.translations[lang][key];
            console.log('✓ Translated:', key, 'from', oldText, 'to', window.translations[lang][key]);
        }
    });

    // Update all aria-labels with data-i18n-label
    const labelElements = document.querySelectorAll('[data-i18n-label]');
    labelElements.forEach(el => {
        const key = el.getAttribute('data-i18n-label');
        const translated = translateKey(key, el.getAttribute('aria-label') || '');
        el.setAttribute('aria-label', translated);
    });

    // Update all placeholders with data-i18n-placeholder
    const placeholderElements = document.querySelectorAll('[data-i18n-placeholder]');
    placeholderElements.forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        const translated = translateKey(key, el.getAttribute('placeholder') || '');
        el.setAttribute('placeholder', translated);
    });
    
    // Update all language buttons (excluding translation widget – it manages its own state)
    const buttons = document.querySelectorAll('[data-lang]:not(#hp-lang-widget [data-lang])');
    
    buttons.forEach((btn) => {
        const btnLang = btn.getAttribute('data-lang');
        if (btn.closest('#hp-lang-widget')) return;
        
        if (btnLang === lang) {
            btn.style.backgroundColor = '#ffc107';
            btn.style.color = '#212121';
            btn.classList.remove('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
            btn.classList.add('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
            console.log('✓ Activated button:', btnLang);
        } else {
            btn.style.backgroundColor = '#374151';
            btn.style.color = '#d1d5db';
            btn.classList.remove('bg-yellow-custom', 'text-gray-dark', 'hover:bg-yellow-hover');
            btn.classList.add('bg-gray-700', 'text-gray-300', 'hover:bg-gray-600');
        }
    });
    
    console.warn('=== LANGUAGE APPLIED SUCCESSFULLY ===\n');
}

// Set up event listeners
function setupLanguageButtons() {
    console.log('Setting up language buttons...');
    
    // Method 1: Direct click on document
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-lang]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const lang = btn.getAttribute('data-lang');
            console.warn('!!! LANGUAGE BUTTON CLICKED:', lang, '!!!');
            applyLanguage(lang);
        }
    }, true); // Use capture phase
    
    // Method 2: Direct button listeners
    const allLangButtons = document.querySelectorAll('[data-lang]');
    console.log('Attaching direct listeners to', allLangButtons.length, 'buttons');
    
    allLangButtons.forEach((btn, idx) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const lang = btn.getAttribute('data-lang');
            console.warn('!!! DIRECT LISTENER - LANGUAGE CLICKED:', lang, '!!!');
            applyLanguage(lang);
        }, true);
        
        btn.addEventListener('touchend', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const lang = btn.getAttribute('data-lang');
            console.warn('!!! TOUCH LISTENER - LANGUAGE CLICKED:', lang, '!!!');
            applyLanguage(lang);
        }, true);
    });
    
    console.log('Language buttons setup complete!');
}

// Initialize
console.log('Document ready state:', document.readyState);

if (document.readyState === 'loading') {
    console.log('Document still loading, waiting...');
    document.addEventListener('DOMContentLoaded', () => {
        console.warn('*** DOM LOADED - INITIALIZING ***');
        setTimeout(() => {
            applyLanguage(getCurrentLanguage());
            setupLanguageButtons();
        }, 100);
    });
} else {
    console.warn('*** DOM ALREADY LOADED - INITIALIZING NOW ***');
    setTimeout(() => {
        applyLanguage(getCurrentLanguage());
        setupLanguageButtons();
    }, 100);
}

console.log('Translation script loaded!');
