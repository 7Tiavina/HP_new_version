# PROJECT_MAP.md : Documentation Architecturale Technique

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
