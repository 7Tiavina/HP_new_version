<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    // Champs modifiables par assignation de masse
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash',
        'role',
    ];

    // Ne jamais exposer le mot de passe brut
    protected $hidden = [
        'password',
        'password_hash',
    ];
}
