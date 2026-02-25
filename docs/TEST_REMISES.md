# Tester l’affichage des remises (sans fallback)

Les remises s’affichent **uniquement** quand l’API BDM les renvoie. Il n’y a pas de remise fictive côté front ni backend.

## Ce que l’API doit renvoyer

### 1. Endpoint produits (getQuote)

L’appel utilisé est le même que pour les tarifs (ex. `GET /api/plateforme/{id}/service/{idService}/{duree}/produits` côté BDM). La réponse doit contenir pour **chaque produit** au moins une des informations de remise suivantes :

| Champ (camelCase)       | Champ (snake_case)           | Description |
|-------------------------|------------------------------|-------------|
| `tauxRemise`            | `taux_remise`                | Taux de remise en % (ex. 10 pour -10 %) |
| `prixUnitaireAvantRemise` | `prix_unitaire_avant_remise` | Prix unitaire TTC avant remise |

- Si l’API envoie **`tauxRemise`** (ou `taux_remise`) > 0, le prix après remise peut être déduit avec `prixUnitaire` (ou `prix_unitaire`).
- Si l’API envoie **`prixUnitaireAvantRemise`** (ou `prix_unitaire_avant_remise`), il est utilisé tel quel pour le “total normal”.
- Les deux formats (camelCase et snake_case) sont gérés côté Laravel et JS.

Exemple de produit **avec** remise (ce que l’API peut renvoyer) :

```json
{
  "id": "...",
  "libelle": "Bagage soute",
  "prixUnitaire": 18.00,
  "tauxRemise": 10,
  "prixUnitaireAvantRemise": 20.00
}
```

Sans remise, l’API peut ne pas envoyer `tauxRemise` / `prixUnitaireAvantRemise` (ou les mettre à 0 / null). Dans ce cas, aucune remise n’est affichée.

### 2. Endpoint options (options-quote)

Pour les options (Priority, Premium), les champs optionnels sont :

- `tauxRemise` / `taux_remise`
- `prixTTCAvantRemise` / `prix_ttc_avant_remise`

Même logique : remise affichée seulement si l’API envoie ces données.

## Comment vérifier que l’API renvoie bien les remises

### 1. Console navigateur (recommandé)

1. Aller sur le formulaire de réservation (`/link-form`).
2. Choisir un aéroport, des dates, ajouter au moins un bagage pour déclencher l’appel au tarif (getQuote).
3. Ouvrir les outils développeur (F12) → onglet **Console**.
4. Chercher le log : **`[Remises] Produits reçus de l'API getQuote:`**

Vous verrez pour chaque produit les champs utilisés pour les remises :

- `prixUnitaire`
- `tauxRemise`
- `prixUnitaireAvantRemise`

Si `tauxRemise` ou `prixUnitaireAvantRemise` sont présents et cohérents, les remises doivent s’afficher dans le panier et sur la page paiement. Sinon, l’API ne renvoie pas encore ces champs (à adapter côté BDM).

### 2. Onglet Network (réseau)

1. F12 → onglet **Réseau (Network)**.
2. Déclencher le chargement des tarifs (étape 2 avec bagages).
3. Repérer la requête vers **`/api/get-quote`** (ou l’URL de votre API).
4. Ouvrir la réponse (Response) et vérifier `content.products[]` : chaque objet doit contenir au moins `tauxRemise` ou `prixUnitaireAvantRemise` pour que les remises s’affichent.

### 3. Côté backend (log Laravel)

Si vous loguez la réponse BDM dans `BdmApiService::getQuote()` (ou l’endpoint qui appelle BDM), vérifier que la réponse JSON des produits contient bien les champs de remise. Tant que l’API BDM ne les renvoie pas, il n’y aura pas d’affichage de remise (volontairement, pas de fallback).

## Où les remises s’affichent

- **Formulaire (panier)** : bloc “Total normal” + ligne “Offre réservation en ligne” avec le montant en vert (ex. `-2,50 €`), uniquement si l’API a renvoyé des remises sur les produits.
- **Page paiement** : idem, “Total normal”, “Promotion réservation en ligne (-X %)” et “-X €”, uniquement si `discount_amount` > 0 calculé à partir des données reçues (produits/options avec remise).

## Résumé

- **Pas de fallback** : pas de -10 % ou autre remise inventée si l’API ne renvoie rien.
- **Tester** : utiliser le log console `[Remises] Produits reçus de l'API getQuote:` et l’onglet Network pour s’assurer que l’API envoie `tauxRemise` et/ou `prixUnitaireAvantRemise` (ou équivalents snake_case) sur les produits.
