<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'email',
        'password_hash',
        'nom',
        'prenom',
        'telephone',
        'civilite',
        'nomSociete',
        'adresse',
        'complementAdresse',
        'ville',
        'codePostal',
        'pays',
        'carte_paiement_last4',
        'carte_paiement_type',
        'carte_paiement_nom',
        'carte_paiement_expiry',
        'monetico_card_token',
    ];

    /**
     * Boot the model - set password_hash to null for guests if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Allow guest clients without password_hash
            if (empty($client->password_hash)) {
                $client->password_hash = null;
            }
        });
    }

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'client_id');
    }

    public function paymentClients()
    {
        return $this->hasMany(PaymentClient::class);
    }
}
