<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BagageConsigneApiController;
use App\Http\Controllers\FrontController;
use App\Services\BdmApiService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK ✅'
    ]);
});

Route::get('/bdm-status', function () {
    $baseUrl = config('services.bdm.base_url');
    if (empty($baseUrl)) {
        return response()->json([
            'open' => false,
            'message' => 'API BDM fermée ou non configurée',
            'detail' => 'BDM_API_BASE_URL manquant dans .env',
        ], 503);
    }
    try {
        $bdm = app(BdmApiService::class);
        $response = $bdm->getPlateformes();
        $content = $response['content'] ?? $response['data'] ?? $response['plateformes'] ?? (is_array($response) && isset($response[0]) ? $response : null);
        $count = is_array($content) ? count($content) : 0;
        return response()->json([
            'open' => true,
            'message' => 'API BDM joignable',
            'plateformes_count' => $count,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'open' => false,
            'message' => 'API BDM fermée ou injoignable',
            'detail' => config('app.debug') ? $e->getMessage() : null,
        ], 503);
    }
});

Route::post('/reservations', [BagageConsigneApiController::class, 'store']);
Route::get('/reservations', [BagageConsigneApiController::class, 'index']);

// New route for getting options quote
Route::post('/commande/options-quote', [FrontController::class, 'getOptionsQuote']);

// New route for getting contraintes (prestations complémentaires obligatoires)
Route::post('/plateforme/{idPlateforme}/commande/contraintes', [FrontController::class, 'getContraintes'])->name('api.commande.contraintes');