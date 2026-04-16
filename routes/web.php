<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BagageConsigneController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AgentController;

// Servir les assets Hostinger (JS, CSS, images) avec le bon MIME type (évite les erreurs "invalid JavaScript MIME type")
Route::get('hostinger/{path}', function (string $path) {
    $path = str_replace(['../', '..\\'], '', $path);
    $path = preg_replace('/%3[fF].*$/', '', $path); // ?ver=... in path
    $fullPath = public_path('hostinger/' . $path);
    if (!File::exists($fullPath) || !File::isFile($fullPath)) {
        abort(404);
    }
    $realPath = realpath($fullPath);
    $hostingerRoot = realpath(public_path('hostinger'));
    if ($realPath === false || $hostingerRoot === false || !str_starts_with($realPath, $hostingerRoot)) {
        abort(404);
    }
    $ext = strtolower(File::extension($fullPath));
    $mimes = [
        'js' => 'application/javascript',
        'mjs' => 'application/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'ico' => 'image/x-icon',
    ];
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    return Response::file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*')->name('hostinger.asset');

Route::get('/', fn() => redirect()->route('form-consigne'));

// Routes d'authentification unifiées
use App\Http\Controllers\AuthController;
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login.submit');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');

// Routes admin (legacy - pour compatibilité)
Route::get('/login', [UserController::class, 'showLogin'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.submit');
Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
Route::post('/admin/commandes/{id}/status', [UserController::class, 'updateCommandeStatus'])->name('admin.commandes.status');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');


Route::get('/overview',     [UserController::class, 'overview'])    ->name('overview');
    Route::get('/analytics',    [UserController::class, 'analytics'])   ->name('analytics');
    Route::get('/chat', [UserController::class, 'chat'])->name('chat');

Route::post('/users', [UserController::class, 'createUser'])->name('users.create');
Route::get('/users', [UserController::class, 'users'])->name('users');    


Route::get('/orders',       [UserController::class, 'orders'])      ->name('orders');
Route::get('/myorders',       [UserController::class, 'myorders'])      ->name('myorders');

    
Route::get('/reservations', [UserController::class, 'reservations'])->name('reservations');
// Affiche la fiche d’une réservation via son ref (QR code)

Route::get('/reservations/ref/{ref}', [BagageConsigneController::class, 'showByRef'])
     ->name('reservations.showByRef');

Route::post('/reservations/{id}/collecter', [BagageConsigneController::class, 'collecterBagage'])->name('collecter.bagage');







// Dashboard client (protégé)
Route::middleware('auth:client')->group(function () {
    Route::get('/client/dashboard', [FrontController::class, 'clientDashboard'])->name('client.dashboard');
    Route::post('/client/logout', [FrontController::class, 'clientLogout'])->name('client.logout');
});

// Affiche modal/login
Route::get('/client/login', [FrontController::class, 'showClientLogin'])->name('client.login');

// Traitement login client
Route::post('/client/login', [FrontController::class, 'clientLogin'])->name('client.login.submit');
Route::post('/client/register', [FrontController::class, 'clientRegister'])->name('client.register');







// Vérifier si l'API BDM est joignable (accessible aussi sans préfixe /api en local)
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
        $bdm = app(\App\Services\BdmApiService::class);
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

// Nouvelles routes pour les appels de l'API BDM via le FrontController
Route::post('/api/check-availability', [FrontController::class, 'checkAvailability'])->name('api.check-availability');
Route::post('/api/get-quote', [FrontController::class, 'getQuote'])->name('api.get-quote');
Route::post('/api/save-command-state', [FrontController::class, 'saveCommandState'])->name('api.save-command-state');
Route::get('/api/plateforme/{idPlateforme}/lieux', [FrontController::class, 'getLieux'])->name('api.lieux');
// Test : vérifier si l'API BDM renvoie des remises sur les produits
Route::get('/api/test-remises', [FrontController::class, 'testApiRemises'])->name('api.test-remises');

// Routes pour le paiement
Route::get('/link-form', [FrontController::class, 'redirectForm'])->name('form-consigne');

// Account page route
Route::get('/account', [FrontController::class, 'showAccountPage'])->name('account');
Route::get('/en/account', function() {
    session(['app_language' => 'en']);
    return app(App\Http\Controllers\FrontController::class)->showAccountPage();
})->name('account.en');

// Route pour changer la langue
Route::get('/set-language', function () {
    $lang = request('lang');
    if (in_array($lang, ['fr', 'en'])) {
        session(['app_language' => $lang]);
    }
    return redirect(url()->previous() ?: route('form-consigne'));
})->name('set-language');

Route::get('/check-auth-status', function () {
    return response()->json(['authenticated' => Auth::guard('client')->check()]);
});

Route::middleware('auth:client')->group(function () { // Spécifier la garde 'client'
    Route::get('/mes-reservations', [CommandeController::class, 'index'])->name('mes.reservations');
    Route::get('/mes-reservations/photos/{id}', [CommandeController::class, 'getPhotos'])->name('mes.reservations.photos');
    Route::get('/client/profile', [ClientController::class, 'showProfile'])->name('client.profile');
    Route::post('/client/update-profile', [ClientController::class, 'updateProfile'])->name('client.update-profile'); // Point to ClientController
    Route::post('/client/update-password', [ClientController::class, 'updatePassword'])->name('client.update-password');
    Route::delete('/client/delete-account', [ClientController::class, 'deleteAccount'])->name('client.delete-account');
});

// Route pour envoyer un mot de passe généré aux clients invités
Route::post('/client/send-generated-password', [ClientController::class, 'sendGeneratedPassword'])->name('client.send-generated-password');
// Route pour mot de passe oublié
Route::post('/client/forgot-password', [ClientController::class, 'forgotPassword'])->name('client.forgot-password');

// Routes de paiement publiques
Route::post('/session/update-guest-info', [PaymentController::class, 'updateGuestInfoInSession'])->name('session.updateGuestInfo');
Route::get('/payment', [PaymentController::class, 'showPaymentPage'])->name('payment');
Route::post('/prepare-payment', [PaymentController::class, 'preparePayment'])->name('prepare.payment');

// New routes for Monetico payment
Route::match(['get', 'post'], '/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/error', [PaymentController::class, 'paymentError'])->name('payment.error');
Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
Route::get('/payment/return', [PaymentController::class, 'paymentReturn'])->name('payment.return');
Route::post('/payment/ipn', [PaymentController::class, 'handleIpn'])->name('payment.ipn');
Route::get('/payment/success/show', [PaymentController::class, 'showPaymentSuccess'])->name('payment.success.show');

Route::post('/reset-session', [PaymentController::class, 'clearGuestSession'])->name('session.reset');
Route::get('/commandes/{id}/download-invoice', [CommandeController::class, 'downloadInvoice'])->name('commandes.download-invoice');
Route::get('/invoice/{id}', [CommandeController::class, 'showInvoice'])->name('invoices.show');
Route::get('//commandes/{id}', [CommandeController::class, 'show'])->name('commandes.show');

// Routes pour la gestion des comptes administrateurs (protégées par token)
Route::get('/admin/create-account', [AdminAccountController::class, 'showCreateForm'])->name('admin.create-account.show');
Route::post('/admin/create-account', [AdminAccountController::class, 'createAccount'])->name('admin.create-account');
Route::get('/admin/reset-password', [AdminAccountController::class, 'showResetForm'])->name('admin.reset-password.show');
Route::post('/admin/reset-password', [AdminAccountController::class, 'resetPassword'])->name('admin.reset-password');

// Routes Agent (authentification et gestion des photos)
Route::get('/agent/login', [AgentController::class, 'showLogin'])->name('agent.login');
Route::post('/agent/login', [AgentController::class, 'login'])->name('agent.login.submit');
Route::post('/agent/logout', [AgentController::class, 'logout'])->name('agent.logout');
Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
Route::get('/agent/commande/{id}', [AgentController::class, 'showCommande'])->name('agent.commande.show');
Route::post('/agent/commande/{id}/upload-photo', [AgentController::class, 'uploadPhoto'])->name('agent.upload.photo');
Route::delete('/agent/photo/{id}', [AgentController::class, 'deletePhoto'])->name('agent.delete.photo');
