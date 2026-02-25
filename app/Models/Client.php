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

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
