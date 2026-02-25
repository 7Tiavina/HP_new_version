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
- `app/Models/` → Modèles Eloquent représentant les principales entités (User, Passenger, Agent, etc.)  
- `app/Http/Controllers/` → Contrôleurs gérant la logique métier et les interactions entre vues et modèles  
- `routes/web.php` → Routes de l’interface utilisateur  
- `routes/api.php` → Endpoints REST utilisés par les intégrations externes  
- `database/migrations/` → Définition du schéma de la base de données  
- `resources/views/` → Vues Blade globales  
- `resources/views/Front/` → Vues Blade du frontend liées au service consigne et aux interfaces clients  
- `public/` → Point d’entrée HTTP et ressources compilées  
- dans `docs\console-front.txt` je met des fois les erreurs que fais le JS dans la console du navigateur
- dans `docs\copypast.txt` je met des fois des infos(texte,infos supplementaire,doccumentation etc) tu dois lire si je te demande dans le chat
- dans `docs\Documentation de l' API v2.pdf` ancienne doccumentation de l' API BDM (l' API est en maintenance constante)

---

## Conventions de code
- Tous les contrôleurs héritent de `BaseController`.  
- Ajouter des **commentaires clairs** pour chaque bloc de logique métier.  
- Centraliser la logique complexe dans des **services dédiés**.  
- Respecter les standards PSR-12 pour la lisibilité et la cohérence du code.  
- Utiliser les **relations Eloquent** (`hasOne`, `hasMany`, `belongsTo`) de manière explicite dans chaque modèle.  

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
