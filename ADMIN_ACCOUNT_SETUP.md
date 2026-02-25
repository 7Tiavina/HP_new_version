# Système de gestion des comptes administrateurs

Ce document explique comment créer et réinitialiser les comptes administrateurs.

## Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
ADMIN_CREATE_TOKEN=your-secret-token-for-creating-accounts
ADMIN_RESET_TOKEN=your-secret-token-for-resetting-passwords
```

**Important :** Changez ces tokens par des valeurs sécurisées et uniques. Ne les partagez pas publiquement.

## URLs

### Créer un compte administrateur

**URL :** `/admin/create-account?token=VOTRE_TOKEN`

**Exemple :**
```
http://votre-domaine.com/admin/create-account?token=your-secret-token-for-creating-accounts
```

**Fonctionnalités :**
- Crée un nouveau compte administrateur
- Génère automatiquement un mot de passe sécurisé (16 caractères aléatoires)
- Envoie les identifiants par email à l'adresse saisie
- Permet de choisir le rôle (admin, agent, user)

### Réinitialiser le mot de passe administrateur

**URL :** `/admin/reset-password?token=VOTRE_TOKEN`

**Exemple :**
```
http://votre-domaine.com/admin/reset-password?token=your-secret-token-for-resetting-passwords
```

**Fonctionnalités :**
- Réinitialise le mot de passe d'un compte admin existant
- Génère automatiquement un nouveau mot de passe sécurisé (16 caractères aléatoires)
- Envoie le nouveau mot de passe par email à l'adresse du compte

## Sécurité

- Les URLs sont protégées par des tokens secrets
- Les mots de passe sont générés de manière sécurisée (16 caractères aléatoires)
- Les identifiants sont envoyés uniquement à l'adresse email configurée
- Les tokens doivent être changés régulièrement pour plus de sécurité

## Utilisation

1. Configurez les tokens dans le fichier `.env`
2. Accédez à l'URL avec le token approprié
3. Remplissez le formulaire
4. Les identifiants seront envoyés automatiquement par email
