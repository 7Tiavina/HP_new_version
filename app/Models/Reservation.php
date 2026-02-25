<?php
// app/Models/Reservation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id','ref','departure',
        'arrival','collect_date',
        'deliver_date','status',
    ];

    /**
     * Chaque réservation appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function bagageHistories()
    {
        return $this->hasMany(BagageHistory::class);
    }

    public function histories()
    {
        return $this->hasMany(BagageHistory::class);
    }

}
