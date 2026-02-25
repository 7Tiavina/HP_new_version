<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentClient extends Model
{
    use HasFactory;

    protected $table = 'payment_clients';

    protected $fillable = [
        'client_id',
        'commande_id',
        'monetico_order_id',
        'monetico_transaction_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
