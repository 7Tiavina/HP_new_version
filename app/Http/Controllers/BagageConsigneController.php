<?php
// app/Http/Controllers/BagageConsigneController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\BagageHistory;

class BagageConsigneController extends Controller
{
    // Liste toutes les réservations
    public function reservList()
    {
        $reservations = Reservation::with('user')->get();
        return view('components.reservations', compact('reservations'));
    }

    // Affiche le formulaire de création
    public function create()
    {
        return view('components.reservation_create');
    }
    

    public function showByRef($ref)
        {
            $reservation = Reservation::with('user')
                                      ->where('ref', $ref)
                                      ->firstOrFail();

            return view('components.reservation_show', compact('reservation'));
        }
    
    public function collecterBagage(Request $request, $id)
    {
        $request->validate([
            'image_data' => 'required|string',
        ]);

        $reservation = Reservation::findOrFail($id);

        $image = $request->image_data;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'bagage_' . time() . '.png';

        Storage::disk('public')->put("collected_photos/{$imageName}", base64_decode($image));
        $photoUrl = "/storage/collected_photos/{$imageName}";

        $reservation->update(['status' => 'collecté']);

        BagageHistory::create([
            'reservation_id' => $reservation->id,
            'status' => 'collecté',
            'agent_id' => session('user_id'),
            'timestamp' => now(),
            'photo_url' => $photoUrl,
        ]);

        return redirect()->route('orders')->with('success', 'Bagage collecté avec succès.');
    }
}
