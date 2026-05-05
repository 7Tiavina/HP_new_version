# GEMINI.md – Contexte du projet Laravel "Agent.hellopassenger"

## Informations générales
- Tu me répond toujour en **Francais**
- **Framework :** Laravel 10.x  
- **Langage principal :** PHP 8.2  
- **Base de données :** MySQL  
- **Frontend :** TailwindCSS + Vite  
- **Authentification :** Laravel Breeze / Sanctum  
- **Serveur web :** Apache  

---

## Objectif principal
Le projet **Agent.hellopassenger** est une application Laravel destinée à la **gestion des passagers et des agents**.  
Le cœur du système repose sur le **service consigne**, accessible via le formulaire `/link-form`.  

Ce formulaire JavaScript communique directement avec :
- le backend Laravel,  
- la base de données MySQL,  
- et une **API externe (BDM)** en temps réel.  

L’API BDM est un ERP utilisé par l’administrateur pour **gérer et synchroniser l’ensemble des données opérationnelles** du service (consignes, réservations, paiements, etc.).

---

## Structure du projet
## Documentation Architecturale Technique

## 1. Vue d'ensemble
L'application **Consigne.hellopassenger** est une plateforme Laravel (10.x/PHP 8.2) gérant la consigne de bagages. Elle orchestre les interactions entre le **Client**, les **Agents** et l'**API externe BDM**.

---

## 2. Cartographie des Contrôleurs (Logique Métier)

| Fichier | Rôle | Dépendances / Liens |
| :--- | :--- | :--- |
| `FrontController.php` | Orchestrateur du formulaire, devis, login. | `BdmApiService`, `Client`, `Commande` |
| `PaymentController.php` | Paiement Monetico (Success, Error, IPN). | `Commande`, `Client` |
| `AgentController.php` | Dashboard agent, upload photos bagages. | `Commande`, `BagagePhoto` |
| `CommandeController.php` | Historique client, factures PDF. | `Commande`, `BagageHistory` |
| `ClientController.php` | Profil client, migration paniers invités. | `Client`, `Commande` |
| `AdminAccountController.php`| Gestion des comptes admins (auth). | `User` |
| `UserController.php` | Dashboard admin, stats, analytics. | `Commande`, `User` |
| `BagageConsigneController.php`| Recherche par QR (ref). | `Commande` |

---

## 3. Services (Communication API)

*   **`app/Services/BdmApiService.php`** : Couche unique d'abstraction vers l'ERP BDM.
    *   `getAuthToken()` : Authentification avec cache (3300s).
    *   `getPlateformes()` : Aéroports.
    *   `getQuote()` : Prix, produits, lieux (via `Http::pool`).
    *   `getCommandeOptionsQuote()` : Devis options (Premium/Priority).
    *   `getCommandeContraintes()` : Règles métier API BDM.

---

## 4. Frontend & JavaScript (public/js/)

*   **`booking.js`** : Moteur du formulaire. Pilote `FrontController`.
*   **`cart.js`** : Logique panier (items, quantités, stockage session).
*   **`contraintes.js`** : Appels API pour options (Priority/Premium).
*   **`options-drawer.js`** : Interface de sélection des options.
*   **`modal.js`** : Gestion des modales (Authentification).
*   **`state.js`** : Garde-fou de l'état global JS.

---

## 5. Modèles Eloquent (Structure Données)

*   **`Client.php`** : Profil utilisateur (email, nom, téléphone, hash password).
*   **`Commande.php`** : Pivot central. Lié aux `BagageHistory` et `Client`.
*   **`BagageHistory.php`** : Lignes de prestation (bagages, dates, prix).
*   **`BagagePhoto.php`** : Stockage du chemin des photos uploadées par les agents.

---

## 6. Cartographie des Routes (`routes/web.php`)

| Route | Contrôleur / Méthode | Description |
| :--- | :--- | :--- |
| `/link-form` | `FrontController@redirectForm` | Page principale du formulaire. |
| `/api/get-quote` | `FrontController@getQuote` | Devis API BDM (tarifs/lieux). |
| `/payment` | `PaymentController@showPaymentPage` | Page de paiement Monetico. |
| `/payment/ipn` | `PaymentController@handleIpn` | Notification de paiement serveur (IPN). |
| `/client/dashboard` | `FrontController@clientDashboard` | Espace client protégé. |
| `/agent/dashboard` | `AgentController@dashboard` | Espace agent protégé. |
| `/reservations/ref/{ref}` | `BagageConsigneController@showByRef`| Affichage fiche via QR Code. |
| `/admin/*` | Divers (AdminAccount/User) | Gestion administrative & analytics. |

---

## 7. Flux Critiques

### Flux de Commande (Client -> BDM)
1. **Front (`booking.js`)** -> `FrontController::getQuote` (devis BDM).
2. **Front** -> `FrontController::saveCommandState` (session).
3. **Paiement (`PaymentController`)** -> Validation Monetico -> `handleIpn` (confirmation).

### Flux de Gestion (Agent -> Bagage)
1. **Agent** (`AgentController::showCommande`) -> Récupère `Commande` + `photos`.
2. **Agent** (`uploadPhoto`) -> Enregistre image dans `BagagePhoto` liée à `BagageHistory` (id).


---

## Conventions de code
- Tous les contrôleurs héritent de `BaseController`.  
- Ajouter des **commentaires clairs** pour chaque bloc de logique métier.  
- Centraliser la logique complexe dans des **services dédiés**.  
- Respecter les standards PSR-12 pour la lisibilité et la cohérence du code.  
- Utiliser les **relations Eloquent** (`hasOne`, `hasMany`, `belongsTo`) de manière explicite dans chaque modèle.  

---

## Sécurité & Logs
- **Zéro donnée sensible :** Ne jamais logger de mots de passe (même hashés), tokens API, JWT, cookies ou informations bancaires.
- **Protection des données (RGPD) :** Masquer ou anonymiser les données personnelles (emails, téléphones, adresses) dans les logs de debug.
- **Utilité & Performance :** Ne logger que le contexte nécessaire au debug. Éviter la verbosité inutile pour préserver les performances et l'espace disque.
- **Rotation :** S'assurer que la rotation des logs est configurée (gestion par Laravel par défaut).

---

## Directives Gemini
- Générer exclusivement du code compatible avec **Laravel 10.x**.  
- Toujours respecter la structure du projet (`app/`, `routes/`, `resources/views/`, etc.).  
- Fournir systématiquement les fichiers complémentaires nécessaires :  
  - **migrations**, **seeders**, **modèles**, **contrôleurs**, **vues**, et **tests**.  
- Documenter chaque fonction publique via **PHPDoc**.  
- Lors de la création de nouveaux modules, proposer la logique de validation et les middlewares appropriés.  

---

## Tâches en cours
Le développement se concentre actuellement sur le **module consigne de bagages**, composé de deux sous-parties :  
1. **Formulaire de commande et panier** – gestion de la saisie et des interactions avec la base et l’API.  
2. **Confirmation et paiement** – intégration du système de paiement **Monetico**.

Les tâches et objectifs spécifiques seront définis au fur et à mesure via le **CLI Gemini**.  
