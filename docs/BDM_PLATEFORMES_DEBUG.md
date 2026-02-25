# Diagnostic : aucun aéroport (plateformes BDM)

Aucun fallback : les aéroports viennent uniquement de l’API BDM.

## Comment savoir si l’API est fermée / injoignable

1. **Route de vérification**  
   - **`GET /bdm-status`** (route web, sans préfixe)  
   - ou **`GET /api/bdm-status`** (route API)

   En local (127.0.0.1) tester dans cet ordre :
   - `http://127.0.0.1/bdm-status`
   - `http://127.0.0.1/public/bdm-status` (si la racine du serveur est le dossier du projet)
   - `http://127.0.0.1:8000/bdm-status` (si vous lancez `php artisan serve`)
   - `http://127.0.0.1/api/bdm-status` ou `http://127.0.0.1:8000/api/bdm-status`

   Si vous avez **404 partout** : le serveur doit utiliser le dossier **`public`** comme racine (ou lancer `php artisan serve` depuis la racine du projet).

   - Réponse **200** + `"open": true` → API joignable, nombre de plateformes dans `plateformes_count`.
   - Réponse **503** + `"open": false` → API fermée, non configurée ou erreur (connexion, auth, etc.) ; en mode debug, `detail` contient le message d’erreur.

2. **Logs** (`storage/logs/laravel.log`)  
   Après un chargement de `/link-form` ou un appel à `/api/bdm-status` :
   - `BDM plateformes : connexion impossible` → API injoignable (fermée, timeout, mauvaise URL).
   - `BDM plateformes : HTTP non 2xx` → API a répondu mais erreur (ex. 503 maintenance, 401 non autorisé).
   - `Authentification API BDM échouée` → login refusé (identifiants ou compte désactivé).

3. **Doc API**  
   Dans le projet : `docs/copypast.txt` (extrait Swagger Commande). La doc complète (ex. PDF “Documentation de l’API v2”) est en maintenance côté BDM ; les endpoints utilisés ici sont :
   - **Auth** : `POST {base_url}/User/Login`
   - **Plateformes** : `GET {base_url}/api/service/{idService}/plateformes`

---

## 1. Vérifier la config `.env`

```env
BDM_API_BASE_URL=https://votre-api-bdm.com
BDM_API_USERNAME=...
BDM_API_EMAIL=...
BDM_API_PASSWORD=...
```

- `BDM_API_BASE_URL` : URL de base **sans** slash final (ex. `https://api.example.com`).
- Endpoint appelé : `{BDM_API_BASE_URL}/api/service/dfb8ac1b-8bb1-4957-afb4-1faedaf641b7/plateformes`.

## 2. Consulter les logs après un chargement du formulaire

Après avoir ouvert `/link-form`, regarder **`storage/logs/laravel.log`** :

- **`BDM plateformes : GET ...`** → URL appelée et si `base_url` est set.
- **`BDM plateformes : réponse reçue`** → `http_status`, `response_type` (object ou root_array), `keys_or_count`, `content_count`.
- **`BDM plateformes : JSON invalide`** ou **`connexion impossible`** ou **`HTTP non 2xx`** → erreur détaillée.

À partir de ces lignes vous voyez exactement ce que l’API renvoie et pourquoi les aéroports sont vides ou absents.

## 3. Formats de réponse acceptés par le front

Le contrôleur accepte notamment :

- Tableau à la racine : `[ { "id": "...", "libelle": "..." }, ... ]`
- Objet avec liste : `{ "statut": 1, "content": [ ... ] }` ou `"data"` / `"plateformes"` / `"result"` / `"items"`.

Chaque élément doit avoir un identifiant (clé `id`, `Id`, `idPlateforme`, etc.) et un libellé (`libelle`, `Libelle`, `nom`, etc.).
