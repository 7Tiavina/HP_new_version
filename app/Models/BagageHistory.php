<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BagageHistory extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'status', 'agent_id', 'timestamp', 'photo_url'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
