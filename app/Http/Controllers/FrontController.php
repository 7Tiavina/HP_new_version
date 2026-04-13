<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str; // Add this import
use Illuminate\Http\Client\Response;
use App\Services\BdmApiService; // Add this import

class FrontController extends Controller
{
    protected $bdmApiService;

    public function __construct(BdmApiService $bdmApiService)
    {
        $this->bdmApiService = $bdmApiService;
    }

    /**
     * Convertit une date et heure du fuseau France vers UTC au format ISO8601.
     *
     * @param string $date Date au format YYYY-MM-DD
     * @param string $heure Heure au format HH:MM
     * @return string Date au format local Europe/Paris (ex: 2026-03-28T12:00:00)
     */
    private function convertFranceDateToUtc(string $date, string $heure): string
    {
        // IMPORTANT: Ne PAS convertir en UTC, garder l'heure locale Europe/Paris
        $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$heure}", 'Europe/Paris');
        return $carbon->format('Y-m-d\TH:i:s');
    }

    public function redirectForm(Request $request)
    {
        // Handle language preference from URL parameter (?lang=en or ?lang=fr)
        $langParam = $request->query('lang');
        if ($langParam) {
            $langParam = strtolower($langParam);
            if (in_array($langParam, ['fr', 'en'])) {
                // Set language in session
                $request->session()->put('app_language', $langParam);
                app()->setLocale($langParam);
                Log::info('Language set from URL parameter', ['lang' => $langParam]);
            }
        }

        // Reset form session for new reservation
        // Clear ALL booking-related data and force return to step 1
        // Preserve authentication if user is logged in
        $authGuard = Auth::guard('client');
        $isAuthenticated = $authGuard->check();
        $authUserId = $isAuthenticated ? $authGuard->id() : null;
        $authUserData = null;
        
        if ($isAuthenticated && $authUserId) {
            $authUser = $authGuard->user();
            $authUserData = [
                'id' => $authUser->id,
                'email' => $authUser->email,
            ];
        }
        
        // Comprehensive list of all booking/session keys to clear
        $keysToClear = [
            // Form state
            'formState',
            'booking_data',
            'guest_session',
            
            // Order data
            'commande_en_cours',
            'order_id',
            'commande_id',
            'last_order',
            'order_completed',
            'payment_completed',
            
            // Client details
            'guest_customer_details',
            'customer_details',
            'client_details',
            
            // Selection data
            'airport_id',
            'service_id',
            'cart_items',
            'global_products_data',
            'global_lieux_data',
            
            // Dates/times
            'date_depot',
            'heure_depot',
            'date_recuperation',
            'heure_recuperation',
            
            // Options/constraints
            'options_data',
            'constraints_data',
            'selected_options',
            
            // Any other booking-related keys
            'step_completed',
            'current_step',
            'payment_intent',
            'session_token',
        ];
        
        $request->session()->forget($keysToClear);
        
        Log::info('Form reset on redirect to /link-form. Auth preserved: ' . ($isAuthenticated ? 'YES (user ID: ' . $authUserId . ')' : 'NO (guest)'));

        try {
            $responsePlateformes = $this->bdmApiService->getPlateformes();

            $plateformes = [];
            // Réponse = tableau à la racine [ {...}, {...} ]
            if (is_array($responsePlateformes) && isset($responsePlateformes[0]) && is_array($responsePlateformes[0])) {
                $plateformes = $responsePlateformes;
            } else {
                $statut = $responsePlateformes['statut'] ?? $responsePlateformes['status'] ?? $responsePlateformes['isSucceed'] ?? null;
                $content = $responsePlateformes['content'] ?? $responsePlateformes['data'] ?? $responsePlateformes['plateformes'] ?? $responsePlateformes['result'] ?? $responsePlateformes['items'] ?? null;
                $contentIsArray = is_array($content);
                if ($contentIsArray && ($statut === 1 || $statut === true || $statut === null)) {
                    $plateformes = $content;
                } else {
                    Log::error("La réponse de l'API BDM pour les plateformes est invalide.", [
                        'response_keys' => is_array($responsePlateformes) ? array_keys($responsePlateformes) : gettype($responsePlateformes),
                        'statut' => $statut,
                        'content_type' => is_array($content) ? 'array(' . count($content) . ')' : gettype($content)
                    ]);
                    throw new \Exception("Réponse invalide de l'API pour les plateformes.");
                }
            }

            // Normaliser id et libelle pour la vue
            foreach ($plateformes as $i => $p) {
                if (!is_array($p)) {
                    unset($plateformes[$i]);
                    continue;
                }
                if (!isset($p['id'])) {
                    $plateformes[$i]['id'] = $p['Id'] ?? $p['ID'] ?? $p['idPlateforme'] ?? $p['plateformeId'] ?? (string)$i;
                }
                if (!isset($p['libelle'])) {
                    $plateformes[$i]['libelle'] = $p['Libelle'] ?? $p['nom'] ?? $p['name'] ?? $p['label'] ?? '';
                }
            }
            $plateformes = array_values($plateformes);
            if (empty($plateformes)) {
                Log::warning("API BDM plateformes : liste vide.", ['keys_received' => is_array($responsePlateformes) && !isset($responsePlateformes[0]) ? array_keys($responsePlateformes) : 'root array']);
            }

            // Prétraitement pour le paramètre d'URL 'airport'
            $selectedAirportId = null;
            $airportIdentifier = $request->query('airport');
            if ($airportIdentifier && !empty($plateformes)) {
                $airportIdentifierLower = strtolower($airportIdentifier);
                $airportMap = ['orly' => 'orly', 'cdg' => 'charles de gaulle'];
                foreach ($plateformes as $plateforme) {
                    $plateformeLibelleLower = strtolower($plateforme['libelle'] ?? '');
                    if ($airportIdentifier === ($plateforme['id'] ?? '')) {
                        $selectedAirportId = $plateforme['id'];
                        break;
                    }
                    if (isset($airportMap[$airportIdentifierLower]) && str_contains($plateformeLibelleLower, $airportMap[$airportIdentifierLower])) {
                        $selectedAirportId = $plateforme['id'];
                        break;
                    }
                }
            }

            // Produits (types de bagages) pour le premier aéroport ; en cas d'échec on garde les plateformes
            $firstPlateformeId = $plateformes[0]['id'] ?? null;
            $products = [];
            if ($firstPlateformeId) {
                try {
                    $responseProducts = $this->bdmApiService->getProducts($firstPlateformeId);
                    $pStatut = $responseProducts['statut'] ?? $responseProducts['status'] ?? $responseProducts['isSucceed'] ?? null;
                    $pContent = $responseProducts['content'] ?? $responseProducts['data'] ?? $responseProducts['products'] ?? null;
                    if (($pStatut === 1 || $pStatut === true) && is_array($pContent)) {
                        $products = $pContent;
                    } else {
                        Log::warning("API BDM produits : format inattendu.", ['keys' => is_array($responseProducts) ? array_keys($responseProducts) : gettype($responseProducts)]);
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur chargement produits BDM (plateformes conservées).", ['error' => $e->getMessage()]);
                    $products = [];
                }
            }

        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des données pour le formulaire : " . $e->getMessage(), ['exception' => $e]);
            $errorMessage = "Impossible de charger les aéroports pour le moment. Vérifiez que l'API BDM est accessible et que les identifiants (config services.bdm) sont corrects.";
            if (config('app.debug')) {
                $errorMessage .= " Détail : " . $e->getMessage();
            }
            return view('Front.formulaire-consigne', [
                'plateformes' => [],
                'products' => [],
                'selectedAirportId' => null,
                'error' => $errorMessage,
                'modal' => $request->query('modal') == '1'
            ]);
        }

        return view('Front.formulaire-consigne', [
            'plateformes' => $plateformes,
            'products' => $products,
            'selectedAirportId' => $selectedAirportId,
            'modal' => $request->query('modal') == '1'
        ]);
    }

    // Note: The getPlateformes and getProducts methods are now handled by BdmApiService
    // and should no longer be present in FrontController directly.
    // The checkAvailability, getQuote, and getOptionsQuote methods below are correct.

    public function showAccountPage()
    {
        return view('auth.account-page');
    }

    public function showClientLogin()
    {
        return view('client.login'); 
    }

    
    public function clientLogin(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $client = Client::where('email', $request->email)->first();

        if ($client) {
            \Log::info('Client found in clientLogin', [
                'client_id' => $client->id,
                'email' => $request->email,
                'has_password_hash' => !empty($client->password_hash),
                'password_hash_start' => substr($client->password_hash ?? '', 0, 10)
            ]);
            
            // Vérifier si c'est un client invité (pas de mot de passe valide)
            $isGuest = empty($client->password_hash) || 
                      (strpos($client->password_hash, '$2y$') !== 0 && strpos($client->password_hash, '$2a$') !== 0);
            
            if ($isGuest) {
                \Log::info('Guest client trying to login via clientLogin', ['client_id' => $client->id, 'email' => $request->email]);
                return back()
                    ->withInput($request->only('email'))
                    ->with('guest_login_attempt', true)
                    ->with('guest_email', $request->email);
            }
            
            if (Hash::check($request->password, $client->password_hash)) {
                Auth::guard('client')->login($client); // login via guard client
                $request->session()->regenerate();

                \Log::info('Client logged in via clientLogin', ['client_id' => $client->id, 'email' => $request->email]);
                // Redirect to client dashboard
                return redirect()->route('client.dashboard')->with('success', 'Connexion réussie !');
            } else {
                \Log::warning('Client password mismatch in clientLogin', ['client_id' => $client->id, 'email' => $request->email]);
            }
        } else {
            \Log::warning('Client not found in clientLogin', ['email' => $request->email]);
        }

        // échec : on renvoie avec un flash pour afficher le modal d'erreur
        return back()->withInput($request->only('email'))->with('login_error', true);
    }

    public function clientLogout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('form-consigne');
    }


    public function clientDashboard()
    {
        $client = Auth::guard('client')->user();
        
        // Commandes avec relations
        $commandes = \App\Models\Commande::where('client_id', $client->id)
            ->with('photos')
            ->latest()
            ->take(5)
            ->get();
        
        // Statistiques détaillées
        $totalCommandes = \App\Models\Commande::where('client_id', $client->id)->count();
        $commandesAujourdhui = \App\Models\Commande::where('client_id', $client->id)
            ->whereDate('created_at', today())
            ->count();
        $commandesSemaine = \App\Models\Commande::where('client_id', $client->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $commandesMois = \App\Models\Commande::where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $commandesCompleted = \App\Models\Commande::where('client_id', $client->id)
            ->where('statut', 'completed')
            ->count();
        $commandesPending = \App\Models\Commande::where('client_id', $client->id)
            ->where('statut', 'pending')
            ->count();
        $totalDepense = \App\Models\Commande::where('client_id', $client->id)
            ->where('statut', 'completed')
            ->sum('total_prix_ttc');
        $depenseAujourdhui = \App\Models\Commande::where('client_id', $client->id)
            ->where('statut', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_prix_ttc');
        $totalPhotos = \App\Models\BagagePhoto::whereHas('commande', function($query) use ($client) {
            $query->where('client_id', $client->id);
        })->count();
        
        return view('client.dashboard', compact(
            'client', 
            'commandes', 
            'totalCommandes',
            'commandesAujourdhui',
            'commandesSemaine',
            'commandesMois',
            'commandesCompleted',
            'commandesPending',
            'totalDepense',
            'depenseAujourdhui',
            'totalPhotos'
        ));
    }

    public function clientRegister(Request $request)
    {
        // Vérifier si un client existe déjà avec cet email
        $existingClient = Client::where('email', $request->email)->first();
        
        // Si le client existe et a déjà un mot de passe valide (hash bcrypt commence par $2y$ ou $2a$)
        if ($existingClient && !empty($existingClient->password_hash) && 
            (strpos($existingClient->password_hash, '$2y$') === 0 || strpos($existingClient->password_hash, '$2a$') === 0)) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255|unique:clients,email',
            ]);
            return back()
                ->withInput()
                ->withErrors(['email' => 'Cet email est déjà utilisé. Veuillez vous connecter ou utiliser "Mot de passe oublié".'])
                ->with('from_register', true);
        }
        
        // Si le client existe mais sans mot de passe (compte invité), permettre la création de compte
        // On ne bloque pas, on va juste mettre à jour le compte existant

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'telephone_complete' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator)
                ->with('from_register', true);
        }

        // Si le client existe mais sans mot de passe (compte invité), mettre à jour
        if ($existingClient) {
            $existingClient->password_hash = Hash::make($request->password);
            $existingClient->nom = $request->nom;
            $existingClient->prenom = $request->prenom;
            $existingClient->telephone = $request->telephone_complete ?: ($request->telephone ?: $existingClient->telephone);
            $existingClient->save();
            $client = $existingClient;
            
            // Transférer les commandes invitées vers ce client
            $this->transferGuestOrdersToClient($client);
        } else {
            // Créer un nouveau compte
            $client = Client::create([
                'email' => $request->email,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone_complete ?: ($request->telephone ?? null),
                'password_hash' => Hash::make($request->password),
            ]);
        }

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        // Redirect to profile so user can fill in their address
        return redirect()->route('client.profile')->with('success', 'Compte créé avec succès ! Complétez votre profil.');
    }

    /**
     * Transfère les commandes invitées vers un client lors de la conversion
     */
    private function transferGuestOrdersToClient(Client $client)
    {
        // Trouver toutes les commandes avec le même email mais sans client_id ou avec un client_id différent
        $guestOrders = \App\Models\Commande::where('client_email', $client->email)
            ->where(function($query) use ($client) {
                $query->whereNull('client_id')
                      ->orWhere('client_id', '!=', $client->id);
            })
            ->get();

        $transferredCount = 0;
        foreach ($guestOrders as $order) {
            $order->client_id = $client->id;
            $order->save();
            $transferredCount++;
        }

        if ($transferredCount > 0) {
            \Log::info('Guest orders transferred to client', [
                'client_id' => $client->id,
                'email' => $client->email,
                'orders_count' => $transferredCount,
            ]);
        }

        return $transferredCount;
    }

    //Vérifie la disponibilité d'une plateforme à une date donnée.
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'idPlateforme' => 'required|string',
            'dateToCheck' => 'required|string',
        ]);

        Log::info('Appel à l\'API BDM pour la disponibilité via BdmApiService', ['data' => $validated]);

        try {
            Log::info('Avant appel checkAvailability');
            $response = $this->bdmApiService->checkAvailability(
                $validated['idPlateforme'],
                $validated['dateToCheck']
            );
            Log::info('Après appel checkAvailability', ['response' => $response]);

            return response()->json($response, 200);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('RequestException lors de la vérification de la disponibilité', [
                'error' => $e->getMessage(),
                'status' => $e->response->status() ?? null,
                'body' => $e->response->body() ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de la disponibilité: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Exception lors de la vérification de la disponibilité via BdmApiService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de la disponibilité: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les tarifs (produits) pour une plateforme, un service et une durée donnés.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuote(Request $request)
    {
        $validated = $request->validate([
            'idPlateforme' => 'required|string',
            'idService' => 'required|string',
            'duree' => 'required|integer|min:1',
        ]);

        Log::info('Appel à l\'API BDM pour les tarifs et lieux via BdmApiService', ['data' => $validated]);

        try {
            $response = $this->bdmApiService->getQuote(
                $validated['idPlateforme'],
                $validated['idService'],
                $validated['duree']
            );
            
            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tarifs/lieux via BdmApiService', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique lors de la récupération des données : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les prix dynamiques pour les options Priority et Premium depuis l\'API BDM.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOptionsQuote(Request $request)
    {
        $validated = $request->validate([
            'idPlateforme' => 'required|string',
            'cartItems' => 'required|array',
            'guestEmail' => 'nullable|email',
            'dateDepot' => 'required|string',
            'heureDepot' => 'required|string',
            'dateRecuperation' => 'required|string',
            'heureRecuperation' => 'required|string',
            'globalProductsData' => 'required|array',
        ]);

        $idPlateforme = $validated['idPlateforme'];
        $cartItemsFromFrontend = $validated['cartItems'];
        $guestEmail = $validated['guestEmail'] ?? null;
        $dateDepot = $validated['dateDepot'];
        $heureDepot = $validated['heureDepot'];
        $dateRecuperation = $validated['dateRecuperation'];
        $heureRecuperation = $validated['heureRecuperation'];
        $globalProductsData = $validated['globalProductsData'];

        Log::info('FrontController::getOptionsQuote - Données de requête reçues', [
            'idPlateforme' => $idPlateforme,
            'cartItemsFromFrontend' => $cartItemsFromFrontend,
            'guestEmail' => $guestEmail,
            'dates' => "{$dateDepot} {$heureDepot} - {$dateRecuperation} {$heureRecuperation}",
        ]);

        $baggages = [];
        $consigneServiceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
        $premiumDetails = null; // Initialise à null pour contenir l'objet premiumDetails complet

        foreach ($cartItemsFromFrontend as $item) {
            // Si c'est l'option Premium et qu'elle contient des détails, les extraire
            if (isset($item['key']) && $item['key'] === 'premium' && isset($item['details'])) {
                $premiumDetails = $item['details'];
            }

            // ... (reste du code pour les baggages) ...
            $productInGlobal = collect($globalProductsData)->firstWhere('id', $item['productId']);
            if ($productInGlobal) {
                $baggages[] = [
                    'productId' => $productInGlobal['id'],
                    'serviceId' => $consigneServiceId,
                    'dateDebut' => $this->convertFranceDateToUtc($dateDepot, $heureDepot),
                    'dateFin' => $this->convertFranceDateToUtc($dateRecuperation, $heureRecuperation),
                    'quantity' => $item['quantity'],
                ];
            }
        }
        
        try {
            // Call the service to discover available options and their prices
            $response = $this->bdmApiService->getCommandeOptionsQuote(
                $idPlateforme,
                $baggages,
                $guestEmail, // Conserver guestEmail séparément
                $premiumDetails // Passer l'objet premiumDetails complet
            );
            Log::info('FrontController::getOptionsQuote - Réponse de BdmApiService::getCommandeOptionsQuote', ['response' => $response]);
    
            if ($response && ($response['statut'] ?? 0) === 1 && isset($response['content'])) {
                $priorityOption = null;
                $premiumOption = null;

                // IDs fixes pour Priority et Premium
                $priorityId = 'fbb2d232-27c8-43eb-a6a9-200b6225871a';
                $premiumId = 'eb2bc6f4-b3f5-4911-90ce-9724250e21a3';

                // Process the API response to find our specific options by ID
                foreach ($response['content'] ?? [] as $optionItem) {
                    $optionId = $optionItem['id'] ?? '';
                    $referenceInterne = $optionItem['referenceInterne'] ?? '';

                    // Detect Priority by ID or reference
                    if ($optionId === $priorityId || strtoupper($referenceInterne) === 'PRIO') {
                        $priorityOption = $optionItem;
                    }
                    // Detect Premium by ID or reference
                    elseif ($optionId === $premiumId || strtoupper($referenceInterne) === 'PREM') {
                        $premiumOption = $optionItem;
                    }
                }

                Log::info('FrontController::getOptionsQuote - Options extraites', [
                    'priority' => $priorityOption,
                    'premium' => $premiumOption
                ]);

                return response()->json([
                    'statut' => 1,
                    'message' => 'Prix des options récupérés avec succès',
                    'content' => [
                        'priority' => $priorityOption,
                        'premium' => $premiumOption,
                    ]
                ]);
            } else {
                Log::error('FrontController::getOptionsQuote - Échec de la récupération des prix des options via BDM API', ['response' => $response]);
                return response()->json([
                    'statut' => 0,
                    'message' => $response['message'] ?? 'Impossible de récupérer les prix des options pour le moment.'
                ], 500);
            }
    
        } catch (\Exception $e) {
            Log::error('FrontController::getOptionsQuote - Erreur lors de la récupération des prix des options : ' . $e->getMessage());
            return response()->json([
                'statut' => 0,
                'message' => 'Erreur technique lors de la récupération des prix des options.'
            ], 500);
        }
    }

    /**
     * Test si l'API BDM renvoie des champs de remise sur les produits (getQuote).
     * GET /api/test-remises pour vérifier la structure des produits.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testApiRemises()
    {
        $serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
        $duree = 60;

        try {
            $responsePlateformes = $this->bdmApiService->getPlateformes();
            $content = $responsePlateformes['content'] ?? $responsePlateformes['data'] ?? $responsePlateformes['plateformes'] ?? null;
            $plateformes = is_array($content) ? $content : (is_array($responsePlateformes) && isset($responsePlateformes[0]) ? $responsePlateformes : []);
            $firstId = null;
            if (!empty($plateformes) && is_array($plateformes[0] ?? null)) {
                $firstId = $plateformes[0]['id'] ?? $plateformes[0]['Id'] ?? $plateformes[0]['ID'] ?? null;
            }
            if (!$firstId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Aucune plateforme disponible pour tester.',
                    'products' => [],
                    'remise_summary' => [],
                ], 200);
            }

            $response = $this->bdmApiService->getQuote((string) $firstId, $serviceId, $duree);
            $products = $response['content']['products'] ?? $response['content'] ?? [];
            if (!is_array($products)) {
                $products = [];
            }

            $remiseSummary = [];
            foreach ($products as $i => $p) {
                $remiseSummary[] = [
                    'index' => $i,
                    'libelle' => $p['libelle'] ?? $p['Libelle'] ?? $p['nom'] ?? null,
                    'id' => $p['id'] ?? $p['Id'] ?? null,
                    'prixUnitaire' => $p['prixUnitaire'] ?? $p['prix_unitaire'] ?? null,
                    'prixUnitaireAvantRemise' => $p['prixUnitaireAvantRemise'] ?? $p['prix_unitaire_avant_remise'] ?? null,
                    'tauxRemise' => $p['tauxRemise'] ?? $p['taux_remise'] ?? null,
                    'has_remise' => isset($p['prixUnitaireAvantRemise']) || isset($p['prix_unitaire_avant_remise'])
                        || (isset($p['tauxRemise']) && (float) ($p['tauxRemise'] ?? 0) > 0)
                        || (isset($p['taux_remise']) && (float) ($p['taux_remise'] ?? 0) > 0),
                    'all_keys' => array_keys($p),
                ];
            }

            return response()->json([
                'ok' => true,
                'message' => 'Réponse getQuote (première plateforme, duree=' . $duree . ' min).',
                'idPlateforme' => $firstId,
                'products_count' => count($products),
                'products_raw' => $products,
                'remise_summary' => $remiseSummary,
                'any_remise' => collect($remiseSummary)->contains('has_remise', true),
            ], 200);
        } catch (\Exception $e) {
            Log::error('testApiRemises: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'products' => [],
                'remise_summary' => [],
            ], 200);
        }
    }

    /**
     * Récupère les lieux pour une plateforme donnée depuis l'API BDM.
     *
     * @param string $idPlateforme
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLieux(string $idPlateforme)
    {
        Log::info('Appel à l\'API BDM pour les lieux', ['idPlateforme' => $idPlateforme]);

        try {
            $token = $this->bdmApiService->getAuthToken();
            $baseUrl = $this->bdmApiService->getBaseUrl();

            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$baseUrl}/api/plateforme/{$idPlateforme}/lieux");

            if ($response->failed()) {
                Log::error("Échec de l'appel API BDM pour les lieux.", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Erreur lors de la communication avec le service de réservation.');
            }

            $result = $response->json();

            // Vérifier si le statut interne de l'API BDM indique un échec
            if (($result['statut'] ?? 0) !== 1) {
                Log::error("Réponse API BDM avec un statut d'échec pour les lieux.", [
                    'response' => $result,
                ]);
                throw new \Exception("Les données de lieux n'ont pas pu être chargées.");
            }

            // Retourner les lieux
            return response()->json([
                'statut' => 1,
                'message' => 'Lieux récupérés avec succès',
                'content' => $result['content'] ?? [],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des lieux via BDM API', ['error' => $e->getMessage()]);
            return response()->json([
                'statut' => 0,
                'message' => 'Erreur technique lors de la récupération des lieux : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des produits de contraintes de prestations complémentaires liées à la commande.
     *
     * @param Request $request
     * @param string $idPlateforme
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContraintes(Request $request, string $idPlateforme)
    {
        $validated = $request->validate([
            'commandeLignes' => 'required|array',
            'commandeOptions' => 'nullable|array',
            'commandeInfos' => 'nullable|array',
            'client' => 'nullable|array',
        ]);

        $commandeLignes = $validated['commandeLignes'];
        $commandeOptions = $validated['commandeOptions'] ?? [];
        $commandeInfos = $validated['commandeInfos'] ?? [];
        $client = $validated['client'] ?? [];

        Log::info('FrontController::getContraintes - Données reçues', [
            'idPlateforme' => $idPlateforme,
            'commandeLignes' => $commandeLignes,
            'commandeOptions' => $commandeOptions,
        ]);

        try {
            $response = $this->bdmApiService->getCommandeContraintes(
                $idPlateforme,
                $commandeLignes,
                $commandeOptions,
                $commandeInfos,
                $client
            );

            if ($response && ($response['statut'] ?? 0) === 1 && isset($response['content'])) {
                return response()->json([
                    'statut' => 1,
                    'message' => 'Contraintes récupérées avec succès',
                    'content' => $response['content'] ?? []
                ]);
            } else {
                Log::error('FrontController::getContraintes - Échec de la récupération des contraintes', [
                    'response' => $response
                ]);
                return response()->json([
                    'statut' => 0,
                    'message' => $response['message'] ?? 'Impossible de récupérer les contraintes.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('FrontController::getContraintes - Erreur : ' . $e->getMessage());
            return response()->json([
                'statut' => 0,
                'message' => 'Erreur technique lors de la récupération des contraintes.'
            ], 500);
        }
    }
}
