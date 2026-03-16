# 📋 Recommandations pour résoudre les problèmes serveur

## 1. ✅ Erreur Monetico INT_901 - CORRIGÉE

Le code a été mis à jour dans `PaymentController.php` pour gérer automatiquement cette erreur.

**Action requise** : Aucune, le code gère maintenant les deux endpoints Monetico.

---

## 2. ⚠️ Erreurs DNS - À CORRIGER SUR LE SERVEUR

### Erreurs constatées :
- `recette-erp.bagagesdumonde.com` - DNS non résolu
- `api.1min.ai` - DNS non résolu

### Solutions :

### Option 1 : Vérifier la configuration DNS du serveur

Connectez-vous en SSH à votre serveur Hostinger et exécutez :

```bash
# Tester la résolution DNS
nslookup recette-erp.bagagesdumonde.com
nslookup api.1min.ai

# Ou avec dig
dig recette-erp.bagagesdumonde.com
dig api.1min.ai
```

### Option 2 : Modifier le fichier /etc/hosts (solution temporaire)

Si les DNS externes ne résolvent pas, ajoutez les IP en dur :

```bash
sudo nano /etc/hosts
```

Ajoutez :
```
<IP_BDM> recette-erp.bagagesdumonde.com
<IP_1MIN> api.1min.ai
```

### Option 3 : Changer les serveurs DNS

Sur Hostinger, configurez des DNS publics fiables :
- Google DNS : `8.8.8.8` et `8.8.4.4`
- Cloudflare DNS : `1.1.1.1` et `1.0.0.1`

### Option 4 : Vérifier le firewall

```bash
# Vérifier les règles firewall
sudo iptables -L -n

# Vérifier que les ports 80 et 443 sont ouverts en sortant
sudo ufw status
```

---

## 3. 🔒 Sécuriser les tentatives de login échouées

5 tentatives de login échouées ont été détectées.

### Solution : Ajouter un rate limiter

Dans `app/Http/Kernel.php`, assurez-vous que le middleware `throttle` est actif :

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:60,1', // 60 requêtes par minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### Ou ajouter un rate limiter spécifique pour le login :

Dans `App\Providers\RouteServiceProvider` :

```php
RateLimiter::for('login', function (Request $request) {
    return auth()->check() ? null : Limit::perMinute(5)->by($request->ip());
});
```

---

## 4. 📊 Monitoring recommandé

### Ajouter un logging amélioré

Créez un middleware pour logger les problèmes réseau :

```bash
php artisan make:middleware LogNetworkErrors
```

### Script de vérification périodique

Créez une commande artisan pour tester les connexions :

```bash
php artisan make:command CheckApiConnections
```

```php
protected $signature = 'api:check';
protected $description = 'Vérifie la connectivité des APIs externes';

public function handle()
{
    $apis = [
        'BDM' => config('services.bdm.base_url'),
        '1min.ai' => config('services.onemin.base_url'),
        'Monetico' => config('monetico.base_url'),
    ];

    foreach ($apis as $name => $url) {
        try {
            $response = Http::timeout(5)->get($url);
            $this->info("✓ {$name}: OK ({$response->status()})");
        } catch (\Exception $e) {
            $this->error("✗ {$name}: {$e->getMessage()}");
        }
    }

    return 0;
}
```

Puis ajoutez une tâche cron pour l'exécuter toutes les heures :

```bash
0 * * * * cd /path/to/project && php artisan api:check >> storage/logs/api-check.log 2>&1
```

---

## 5. 🔄 Déploiement de la correction Monetico

Après cette modification, déployez sur le serveur :

```bash
# Sur le serveur
cd /path/to/laravel/project
git pull
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 📞 Contact support Hostinger

Si les problèmes DNS persistent, contactez le support Hostinger :

- Ticket support : https://hpanel.hostinger.com/support/tickets
- Live chat : disponible 24/7

**Message type** :
> Bonjour, je rencontre des problèmes de résolution DNS sur mon serveur. Les domaines suivants ne peuvent pas être résolus :
> - recette-erp.bagagesdumonde.com
> - api.1min.ai
> 
> Pouvez-vous vérifier la configuration DNS du serveur ?
> Merci.
