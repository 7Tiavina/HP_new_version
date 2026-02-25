<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Str;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;

class BagageConsigneApiController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|integer|exists:users,id',
            'departure'    => 'required|string|max:10',
            'arrival'      => 'required|string|max:10',
            'collect_date' => 'required|date',
            'deliver_date' => 'required|date|after_or_equal:collect_date',
            'status'       => 'required|string|max:50',
        ]);

        $data['ref'] = strtoupper(Str::random(10));

        try {
            $reservation = Reservation::create($data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'reservation' => $reservation
        ], 201);
    }


    public function index()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Reservation::all()
            ], 200);
        } catch (\Throwable $e) {
            // pour debug
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
