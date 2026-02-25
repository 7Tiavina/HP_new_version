<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Commande;
use App\Models\PaymentClient; // Ajout du nouveau modèle
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // Added
use Illuminate\Support\Facades\Validator; // Added for guest validation
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\OrderConfirmationMail;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * Prépare les données de la commande et les stocke en session avant la redirection vers la page de paiement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function preparePayment(Request $request)
    {
        try {
            Log::info('Entering preparePayment method.', ['request_data' => $request->all()]);

            $validatedData = $request->validate([
                'lang' => 'nullable|string|in:en,fr',
                'airportId' => 'required|string',
                'airportName' => 'required|string',
                'dateDepot' => 'required|date',
                'heureDepot' => 'required|string',
                'dateRecuperation' => 'required|date',
                'heureRecuperation' => 'required|string',
                'baggages' => 'required|array',
                'products' => 'required|array',
                'options' => 'nullable|array',
                'options.*.id' => 'required|string',
                'options.*.libelle' => 'required|string',
                'options.*.prix' => 'required|numeric',
                'options.*.details' => 'nullable|array',
                // Validation for Premium option details
                'options.*.details.direction' => 'nullable|string|in:terminal_to_agence,agence_to_terminal,both',
                
                // Arrival flow (required for terminal_to_agence or both)
                'options.*.details.transport_type_arrival' => 'required_if:options.*.details.direction,terminal_to_agence|required_if:options.*.details.direction,both|nullable|string',
                'options.*.details.flight_number_arrival' => 'nullable|string',
                'options.*.details.train_number_arrival' => 'nullable|string',
                'options.*.details.tgv_number_arrival' => 'nullable|string',
                'options.*.details.car_plate_arrival' => 'nullable|string',
                'options.*.details.date_arrival' => 'required_if:options.*.details.direction,terminal_to_agence|required_if:options.*.details.direction,both|nullable|date',
                'options.*.details.pickup_location_arrival' => 'required_if:options.*.details.direction,terminal_to_agence|required_if:options.*.details.direction,both|nullable|string',
                'options.*.details.pickup_time_arrival' => 'required_if:options.*.details.direction,terminal_to_agence|required_if:options.*.details.direction,both|nullable|date_format:H:i',
                'options.*.details.instructions_arrival' => 'nullable|string',

                // Departure flow (required for agence_to_terminal or both)
                'options.*.details.transport_type_departure' => 'required_if:options.*.details.direction,agence_to_terminal|required_if:options.*.details.direction,both|nullable|string',
                'options.*.details.flight_number_departure' => 'nullable|string',
                'options.*.details.train_number_departure' => 'nullable|string',
                'options.*.details.tgv_number_departure' => 'nullable|string',
                'options.*.details.car_plate_departure' => 'nullable|string',
                'options.*.details.date_departure' => 'required_if:options.*.details.direction,agence_to_terminal|required_if:options.*.details.direction,both|nullable|date',
                'options.*.details.restitution_location_departure' => 'required_if:options.*.details.direction,agence_to_terminal|required_if:options.*.details.direction,both|nullable|string',
                'options.*.details.restitution_time_departure' => 'required_if:options.*.details.direction,agence_to_terminal|required_if:options.*.details.direction,both|nullable|date_format:H:i',
                'options.*.details.instructions_departure' => 'nullable|string',
            ]);

            // Store language in session
            $lang = $validatedData['lang'] ?? Session::get('app_language', 'fr');
            Session::put('app_language', $lang);

            $serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
            $baggageTypeToLibelleMap = [
                'accessory' => 'Accessoires', 'cabin' => 'Bagage cabine', 'hold' => 'Bagage soute',
                'special' => 'Bagage spécial', 'cloakroom' => 'Vestiaire',
            ];

            $commandeLignes = [];
            $premiumDetails = null; // Will store premium option's details

            // 1. Process Baggages
            foreach ($validatedData['baggages'] as $baggage) {
                $expectedLibelle = $baggageTypeToLibelleMap[$baggage['type']] ?? null;
                if (!$expectedLibelle) throw new \Exception('Unknown baggage type: ' . $baggage['type']);
                $productDetails = collect($validatedData['products'])->firstWhere('libelle', $expectedLibelle);
                if (!$productDetails) throw new \Exception('Product details not found for: ' . $expectedLibelle);

                $commandeLignes[] = [
                    "idProduit" => $productDetails['id'], "idService" => $serviceId,
                    "dateDebut" => $validatedData['dateDepot'] . 'T' . $validatedData['heureDepot'] . ':00.000Z',
                    "dateFin" => $validatedData['dateRecuperation'] . 'T' . $validatedData['heureRecuperation'] . ':00.000Z',
                    "prixTTC" => ($productDetails['prixUnitaire'] * $baggage['quantity']), "quantite" => $baggage['quantity'],
                    "libelleProduit" => $productDetails['libelle']
                ];
            }

            // 2. Process Options
            if (!empty($validatedData['options'])) {
                foreach ($validatedData['options'] as $selectedOption) {
                    // If this is the premium option, store its details separately
                    if (stripos($selectedOption['libelle'], 'Premium') !== false) {
                        $premiumDetails = $selectedOption['details'] ?? null;
                    }

                    // Add the option line WITHOUT the details object to commandeLignes
                    $commandeLignes[] = [
                        "idProduit" => $selectedOption['id'], "idService" => $serviceId,
                        "dateDebut" => $validatedData['dateDepot'] . 'T' . $validatedData['heureDepot'] . ':00.000Z',
                        "dateFin" => $validatedData['dateRecuperation'] . 'T' . $validatedData['heureRecuperation'] . ':00.000Z',
                        "prixTTC" => $selectedOption['prix'], "quantite" => 1,
                        "libelleProduit" => $selectedOption['libelle'],
                        "is_option" => true
                    ];
                }
            }

            // 3. Create commandeInfos from extracted premium details
            $commandeInfos = [
                'modeTransport' => '',
                'lieu' => '',
                'commentaires' => '',
            ];

            if ($premiumDetails && isset($premiumDetails['direction'])) {
                $details = $premiumDetails;
                $commentairesArray = [];
                $directionText = ($details['direction'] ?? '') === 'terminal_to_agence' ? 'Récupération bagages' : 'Restitution bagages';
                $commentairesArray[] = "Type de service: " . $directionText;

                $isArrivalFlow = ($details['direction'] ?? '') === 'terminal_to_agence';
                $transportTypeKey = $isArrivalFlow ? 'transport_type_arrival' : 'transport_type_departure';
                
                $modeTransport = $details[$transportTypeKey] ?? 'Non spécifié';
                // Mapper les valeurs techniques du frontend aux libellés lisibles
                $displayModeTransport = [
                    'airport' => 'Aéroport',
                    'public_transport' => 'Transport en commun',
                    'train' => 'Train',
                    'other' => 'Autre',
                    'car' => 'Voiture',
                    'taxi' => 'Taxi',
                    'vtc' => 'VTC',
                    'bus' => 'Bus',
                    'metro' => 'Métro',
                    'flight' => 'Avion',
                    'tgv' => 'TGV',
                    'rer_metro' => 'RER/Métro',
                ][$modeTransport] ?? ucfirst(str_replace('_', ' ', $modeTransport));
                $commandeInfos['modeTransport'] = $displayModeTransport;

                switch ($modeTransport) {
                    case 'airport':
                        $flightNumberKey = $isArrivalFlow ? 'flight_number_arrival' : 'flight_number_departure';
                        if (!empty($details[$flightNumberKey])) $commentairesArray[] = "Numéro de vol: " . $details[$flightNumberKey];
                        break;
                    case 'train':
                        $trainNumberKey = $isArrivalFlow ? 'train_number_arrival' : 'train_number_departure';
                        if (!empty($details[$trainNumberKey])) $commentairesArray[] = "Indicatif de ligne: " . $details[$trainNumberKey];
                        break;
                    // No details needed for public_transport and other transport types
                }

                $locationKey = $isArrivalFlow ? 'pickup_location_arrival' : 'restitution_location_departure';
                $locationLabelKey = $locationKey . '_libelle';
                $timeKey = $isArrivalFlow ? 'pickup_time_arrival' : 'restitution_time_departure';
                $instructionsKey = $isArrivalFlow ? 'instructions_arrival' : 'instructions_departure';
                $dateKey = $isArrivalFlow ? 'date_arrival' : 'date_departure';

                $commandeInfos['lieu'] = $details[$locationLabelKey] ?? $details[$locationKey] ?? 'Non spécifié';

                if (!empty($details[$dateKey])) {
                    $dateLabel = $isArrivalFlow ? 'Date de prise en charge' : 'Date de restitution';
                    $commentairesArray[] = "$dateLabel: " . $details[$dateKey];
                }
                
                if (!empty($details[$timeKey])) {
                    $timeLabel = $isArrivalFlow ? 'Heure de prise en charge' : 'Heure de restitution';
                    $commentairesArray[] = "$timeLabel: " . $details[$timeKey];
                }

                if (!empty($details[$instructionsKey])) $commentairesArray[] = "Informations complémentaires: " . $details[$instructionsKey];
                
                $commandeInfos['commentaires'] = implode('; ', $commentairesArray);
            }

            // 4. Prepare Client and Final Command Data
            $user = Auth::guard('client')->user();
            $clientData = $this->getClientData($user, $request->input('guest_email'));
            if (!$clientData) return response()->json(['message' => 'Client non identifié.'], 401);

            // Calculate total price with discount (prices already include 10% discount)
            $totalWithDiscount = array_reduce($commandeLignes, fn($sum, $item) => $sum + ($item['prixTTC'] ?? 0), 0);
            
            // Calculate normal price (what it would cost without discount)
            // If prices already have 10% discount: normalPrice = discountedPrice / 0.9
            $discountPercent = 10;
            $totalNormalPrice = $totalWithDiscount / (1 - $discountPercent / 100);
            $discountAmount = $totalNormalPrice * ($discountPercent / 100);
            $totalAfterDiscount = $totalWithDiscount; // Final price is already discounted
            
            $commandeData = [
                'idPlateforme' => $validatedData['airportId'],
                'airportName' => $validatedData['airportName'],
                'commandeLignes' => $commandeLignes,
                'commandeInfos' => $commandeInfos, // Add commandeInfos to the session
                'client' => $clientData,
                'total_normal_price' => $totalNormalPrice, // Price without discount (for display)
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'total_prix_ttc' => $totalAfterDiscount, // Final price with discount
            ];

            Session::put('commande_en_cours', $commandeData);
            Log::info('Commande data stored in session.', ['data' => $commandeData]);

            return response()->json(['message' => 'Commande préparée avec succès.', 'redirect_url' => route('payment')]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in preparePayment', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Les données fournies sont invalides.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in preparePayment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Une erreur interne est survenue lors de la préparation du paiement.'], 500);
        }
    }

    public function updateGuestInfoInSession(Request $request)
{
    $validated = $request->validate([
        // REQUIRED
        'telephone' => 'required|string|max:255',
        'nom'       => 'required|string|max:255',
        'prenom'    => 'required|string|max:255',
        'adresse'   => 'required|string|max:255',

        // OPTIONAL (Swagger-safe)
        'civilite'          => 'sometimes|nullable|string|max:10',
        'nomSociete'        => 'sometimes|nullable|string|max:255',
        'complementAdresse' => 'sometimes|nullable|string|max:255',
        'ville'             => 'sometimes|nullable|string|max:255',
        'codePostal'        => 'sometimes|nullable|string|max:20',
        'pays'              => 'sometimes|nullable|string|max:255',
    ]);

    // SERVER-SIDE DEFAULTS
    $data = array_merge([
        'nomSociete'        => null,
        'complementAdresse' => null,
    ], $validated);

    Session::put('guest_customer_details', $data);

    $commandeData = Session::get('commande_en_cours');
    if ($commandeData && isset($commandeData['client']['is_guest']) && $commandeData['client']['is_guest']) {
        $commandeData['client'] = array_merge($commandeData['client'], $data);
        Session::put('commande_en_cours', $commandeData);
    }

    return response()->json([
        'success' => true,
        'message' => 'Guest information updated in session.',
    ]);
}


    private function getBdmToken(): string
    {
        return Cache::remember('bdm_api_token', 3300, function () {
            Log::info('Cache BDM token expir%c3%a9. Demande d%c3%a0 un nouveau token.');
            $response = Http::post(config('services.bdm.base_url') . '/User/Login', [
                'userName' => config('services.bdm.username'),
                'email' => config('services.bdm.email'),
                'password' => config('services.bdm.password'),
            ]);
            $response->throw();
            if (!$response->json('isSucceed')) {
                Log::error('L%c3%a0API BDM a refus%c3%a9 la connexion.', ['response' => $response->json()]);
                throw new \Exception('Authentification API BDM %c3%a9chou%c3%a9e: L%c3%a0API a refus%c3%a9 la connexion.');
            }
            $token = $response->json('data.accessToken');
            if (!$token) {
                Log::error('Impossible de r%c3%a9cup%c3%a9rer l%c3%a0accessToken depuis la r%c3%a9ponse de l%c3%a0API BDM.', ['response' => $response->json()]);
                throw new \Exception('Authentification API BDM %c3%a9chou%c3%a9e: token manquant dans la r%c3%a9ponse.');
            }
            Log::info('✅ AUTHENTIFICATION API BDM R%c3%a9USSIE. Token obtenu.');
            Log::info('Nouveau token BDM obtenu et mis en cache.');
            return $token;
        });
    }

    public function redirectToMonetico($savedCardInfo = null)
    {
        Log::info('Entering redirectToMonetico method with Basic Auth as per documentation.');
        $commandeData = session('commande_en_cours');
        if (!$commandeData) {
            Log::error('Monetico redirection failed: Commande data not found in session.');
            return null;
        }
        $orderId = 'CMD-' . uniqid();
        Session::put('monetico_order_id', $orderId);

        $customerEmail = $commandeData['client']['email'] ?? null;
        $customerFirstName = $commandeData['client']['prenom'] ?? null;
        $customerLastName = $commandeData['client']['nom'] ?? null;

        if ((!$customerEmail || !$customerFirstName || !$customerLastName) && Auth::guard('client')->check()) {
            $authenticatedUser = Auth::guard('client')->user();
            $customerEmail = $customerEmail ?? $authenticatedUser->email;
            $customerFirstName = $customerFirstName ?? $authenticatedUser->prenom;
            $customerLastName = $customerLastName ?? $authenticatedUser->nom;
        }
        if (!$customerEmail || !$customerFirstName || !$customerLastName) {
            Log::error('Monetico redirection failed: Missing customer email, first name or last name.', ['commandeData' => $commandeData]);
            return null;
        }

        // Préparer les données de paiement
        $paymentMethod = ['type' => 'Card'];
        
        // Vérifier si l'utilisateur a un token Monetico sauvegardé (méthode intelligente)
        $moneticoToken = null;
        if (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
            $moneticoToken = $user->monetico_card_token ?? null;
        }
        
        // Si on a un token Monetico, l'utiliser pour réutiliser la carte (méthode la plus intelligente)
        if ($moneticoToken) {
            $paymentMethod['token'] = $moneticoToken;
            Log::info('Using saved Monetico token for payment', [
                'has_token' => true,
                'token_length' => strlen($moneticoToken)
            ]);
        } elseif ($savedCardInfo && is_array($savedCardInfo)) {
            // Sinon, essayer de pré-remplir avec les informations disponibles
            // Essayer d'ajouter le nom du titulaire si disponible
            if (!empty($savedCardInfo['nom'])) {
                $paymentMethod['holderName'] = $savedCardInfo['nom'];
                Log::info('Attempting to prefill holder name in Monetico payload', ['holderName' => $savedCardInfo['nom']]);
            }
            
            // Essayer d'ajouter la date d'expiration si disponible (format MM/YY)
            if (!empty($savedCardInfo['expiry'])) {
                $expiry = $savedCardInfo['expiry'];
                // Convertir MM/YY en format attendu par Monetico (peut être MMYY ou séparé)
                if (preg_match('/^(\d{2})\/(\d{2})$/', $expiry, $matches)) {
                    $paymentMethod['expiryMonth'] = $matches[1];
                    $paymentMethod['expiryYear'] = '20' . $matches[2];
                    Log::info('Attempting to prefill expiry date in Monetico payload', [
                        'expiryMonth' => $matches[1],
                        'expiryYear' => '20' . $matches[2]
                    ]);
                }
            }
            
            // Ajouter le type de carte si disponible (pour information)
            if (!empty($savedCardInfo['type'])) {
                $paymentMethod['cardBrand'] = $savedCardInfo['type'];
            }
        }

        $payload = [
            'shopId' => config('monetico.login'), 
            'amount' => (int)($commandeData['total_prix_ttc'] * 100), 
            'currency' => 'EUR',
            'orderId' => $orderId,
            'paymentAction' => 'Authorization', // Demander une pré-autorisation au lieu d'un paiement direct
            'customer' => ['email' => $customerEmail, 'firstName' => $customerFirstName, 'lastName' => $customerLastName],
            'paymentMethod' => $paymentMethod,
            'urls' => [
                'success' => route('payment.success'), 
                'error' => route('payment.error'),
                'cancel' => route('payment.cancel'), 
                'return' => route('payment.return'),
            ],
        ];
        
        // Ajouter des métadonnées si on a une carte sauvegardée (pour référence)
        if ($savedCardInfo && !empty($savedCardInfo['last4'])) {
            $payload['metadata'] = [
                'saved_card_last4' => $savedCardInfo['last4'],
                'saved_card_type' => $savedCardInfo['type'] ?? null,
            ];
        }

        Log::info('Calling Monetico CreatePayment API with correct Basic Auth.');
        
        try {
            $response = Http::timeout(30)
                ->retry(2, 1000) // Retry 2 times with 1 second delay
                ->withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')), 
                    'Content-Type' => 'application/json', 
                    'Accept' => 'application/json',
                ])
                ->post(config('monetico.base_url') . '/Charge/CreatePayment', $payload);

            Log::info('Monetico API response (Basic Auth flow): ' . $response->body());

            if ($response->successful()) {
                $paymentData = $response->json();
                if (isset($paymentData['answer']['formToken'])) {
                    return $paymentData['answer']['formToken'];
                }
            } else {
                Log::error('Monetico API error (Basic Auth flow): ' . $response->body(), [
                    'status' => $response->status(),
                    'headers' => $response->headers()
                ]);
                return null;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Monetico API connection error: ' . $e->getMessage(), [
                'url' => config('monetico.base_url') . '/Charge/CreatePayment',
                'exception' => $e
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Monetico API exception: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return null;
        }
    }

    public function showPaymentPage()
    {
        Log::info('----------------------------------------------------');
        Log::info('[showPaymentPage] START - Handling /payment route.');

        // Check if user has already completed a payment recently (to prevent duplicate orders)
        $lastCommandeId = Session::get('last_commande_id');
        $apiPaymentResult = Session::get('api_payment_result');

        // If both are present, the user has already completed a payment
        if ($lastCommandeId && $apiPaymentResult) {
            Log::info('[showPaymentPage] User has already completed a payment. Redirecting to success page.');
            return redirect()->route('payment.success.show')->with('info', 'Votre commande a déjà été traitée avec succès.');
        }

        $commandeData = Session::get('commande_en_cours');
        if (!$commandeData || !isset($commandeData['client'])) {
            Log::error('[showPaymentPage] CRITICAL: Commande data or client info NOT FOUND in session. Aborting.');
            return redirect()->route('payment')->with('error', 'Votre session a expiré ou vos informations sont invalides. Veuillez recommencer votre commande depuis le début.');
        }

        $clientDataFromSession = $commandeData['client'];
        $isGuest = $clientDataFromSession['is_guest'] ?? false;
        $user = null;

        if ($isGuest) {
            $user = (object) $clientDataFromSession;
        } else {
            $user = Auth::guard('client')->user();
            if (!$user) {
                return redirect()->route('payment')->with('error', 'Un problème de connexion est survenu. Veuillez vous reconnecter.');
            }
        }

        $isProfileComplete = !empty($user->telephone);
        $formToken = null;

        if ($isProfileComplete) {
            Log::info('[showPaymentPage] Client profile is complete. Proceeding to get formToken for pre-authorization.');
            $commandeData['client'] = (array) $user;
            Session::put('commande_en_cours', $commandeData);

            try {
                // Passer les informations de carte sauvegardée à redirectToMonetico si disponibles
                $savedCardInfoForApi = null;
                if (!$isGuest && $user && !empty($user->carte_paiement_last4)) {
                    $savedCardInfoForApi = [
                        'type' => $user->carte_paiement_type,
                        'last4' => $user->carte_paiement_last4,
                        'nom' => $user->carte_paiement_nom,
                        'expiry' => $user->carte_paiement_expiry,
                    ];
                }
                
                $formToken = $this->redirectToMonetico($savedCardInfoForApi);
                if (!$formToken) {
                    Log::error('[showPaymentPage] FAILURE: Did not receive formToken from Monetico.');
                    // Store error in session and let the view handle it
                    Session::flash('error', 'Un problème est survenu lors de l\'initialisation du paiement. Veuillez réessayer.');
                } else {
                    Log::info('[showPaymentPage] SUCCESS: formToken for pre-authorization received.');
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('[showPaymentPage] Connection error when calling Monetico API: ' . $e->getMessage());
                Session::flash('error', 'Impossible de se connecter au service de paiement. Veuillez réessayer dans quelques instants.');
                $formToken = null;
            } catch (\Exception $e) {
                Log::error('[showPaymentPage] Unexpected error when calling Monetico API: ' . $e->getMessage());
                Session::flash('error', 'Une erreur inattendue s\'est produite. Veuillez réessayer.');
                $formToken = null;
            }
        } else {
            Log::warning('[showPaymentPage] Profile for client ' . ($user->email ?? '') . ' is incomplete. Displaying form for completion.');
        }

        // Check if user has a saved card
        $hasSavedCard = false;
        $savedCardInfo = null;
        if (!$isGuest && $user) {
            $hasSavedCard = !empty($user->carte_paiement_last4) && !empty($user->carte_paiement_type);
            if ($hasSavedCard) {
                $savedCardInfo = [
                    'type' => $user->carte_paiement_type,
                    'last4' => $user->carte_paiement_last4,
                    'nom' => $user->carte_paiement_nom,
                    'expiry' => $user->carte_paiement_expiry,
                ];
            }
        }

        // Get any error message from the session
        $errorMessage = session('error');
        $hasError = !empty($errorMessage);

        return view('payment', compact('user', 'formToken', 'isProfileComplete', 'isGuest', 'errorMessage', 'hasError', 'hasSavedCard', 'savedCardInfo'));
    }

    public function paymentSuccess(Request $request)
    {
        $commandeData = Session::get('commande_en_cours');
        if (!$commandeData) {
            return redirect()->route('payment')->with('error', 'Votre session a expiré. Veuillez recommencer votre commande.');
        }

        Log::info('[paymentSuccess] Requête reçue de Monetico', $request->all());

        // Extraire le transactionId de la réponse Monetico
        $krAnswer = json_decode($request->get('kr-answer'), true);
        $moneticoTransactionId = null;

        if ($krAnswer && isset($krAnswer['transactions']) && is_array($krAnswer['transactions']) && count($krAnswer['transactions']) > 0) {
            // Prendre le premier UUID de transaction
            $moneticoTransactionId = $krAnswer['transactions'][0]['uuid'] ?? null;
        }

        if (!$moneticoTransactionId) {
            Log::error('[paymentSuccess] Monetico transactionId not found in the request.', [
                'request_all' => $request->all(),
                'query_params' => $request->query(),
                'kr_answer_decoded' => $krAnswer,
                'has_transaction_id_key' => $request->has('transactionId'),
                'available_keys' => array_keys($request->all())
            ]);
            return redirect()->route('payment')->with('error', 'Une erreur est survenue lors du traitement de votre paiement. Aucun débit n\'a été effectué. Veuillez réessayer.');
        }

        Log::info('[paymentSuccess] Pre-authorization successful. Starting BDM validation.', [
            'monetico_transaction_id' => $moneticoTransactionId,
            'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? null
        ]);

        // STEP 1: Call BDM API to create the definitive order
        try {
            $token = $this->getBdmToken();
            $idPlateforme = $commandeData['idPlateforme'];

            $lignesProduits = [];
            $lignesOptions = [];
            foreach ($commandeData['commandeLignes'] as $ligne) {
                if (isset($ligne['is_option']) && $ligne['is_option']) {
                    unset($ligne['is_option']);
                    $lignesOptions[] = $ligne;
                } else {
                    $lignesProduits[] = $ligne;
                }
            }
            
            $payload = [
                'commandeLignes' => $lignesProduits,
                'commandeOptions' => $lignesOptions,
                'client' => $commandeData['client'],
                'commandeInfos' => $commandeData['commandeInfos'] ?? new \stdClass(),
            ];

            Log::info('[paymentSuccess] Sending final creation request to BDM API.', ['payload' => $payload]);

            $bdmResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json',
            ])->post(config('services.bdm.base_url') . "/api/plateforme/{$idPlateforme}/commande", $payload);

            Log::info('[paymentSuccess] BDM API response received.', [
                'status_code' => $bdmResponse->status(),
                'response_body' => $bdmResponse->body(),
                'monetico_transaction_id' => $moneticoTransactionId
            ]);

            $apiResult = $bdmResponse->json();

            // STEP 2: Handle BDM API response
            if ($bdmResponse->successful() && isset($apiResult['statut']) && $apiResult['statut'] === 1) {

                // BDM Success -> Capture Payment
                Log::info('[paymentSuccess] BDM order creation successful. Proceeding to capture payment.', ['bdm_order_id' => $apiResult['message']]);
                
                Log::info('[paymentSuccess] Attempting to capture payment with Monetico.', [
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id')
                ]);

                $captureResponse = $this->_moneticoCapturePayment($moneticoTransactionId);

                Log::info('[paymentSuccess] Monetico capture response received.', [
                    'status_code' => $captureResponse->status(),
                    'response_body' => $captureResponse->body(),
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id')
                ]);

                if (!$captureResponse->successful()) {
                    // CRITICAL: BDM order is created, but payment capture failed. Requires manual intervention.
                    throw new \Exception('CRITICAL: BDM order created but Monetico capture failed. Response: ' . $captureResponse->body());
                }

                Log::info('[paymentSuccess] Monetico payment capture successful.');

                // Proceed to save everything in local DB
                $clientData = $commandeData['client'];
                
                // Extraire les informations de carte depuis la réponse Monetico
                $cardInfo = $this->extractCardInfoFromMoneticoResponse($krAnswer, $request);
                
                // Préparer les données client avec l'adresse complète
                $clientUpdateData = array_merge($clientData, [
                    'adresse' => $clientData['adresse'] ?? null,
                    'complementAdresse' => $clientData['complementAdresse'] ?? null,
                    'ville' => $clientData['ville'] ?? null,
                    'codePostal' => $clientData['codePostal'] ?? null,
                    'pays' => $clientData['pays'] ?? null,
                ]);
                
                // Si l'utilisateur est connecté (pas un invité) et qu'on a des infos de carte, les sauvegarder
                $isGuest = $clientData['is_guest'] ?? true;
                if (!$isGuest && $cardInfo) {
                    $cardUpdateData = [
                        'carte_paiement_type' => $cardInfo['type'] ?? null,
                        'carte_paiement_last4' => $cardInfo['last4'] ?? null,
                        'carte_paiement_nom' => $cardInfo['holderName'] ?? null,
                        'carte_paiement_expiry' => $cardInfo['expiry'] ?? null,
                    ];
                    
                    // Sauvegarder le token Monetico si disponible (pour réutilisation future)
                    if (isset($cardInfo['token']) && !empty($cardInfo['token'])) {
                        $cardUpdateData['monetico_card_token'] = $cardInfo['token'];
                        Log::info('[paymentSuccess] Token Monetico sauvegardé pour réutilisation future', [
                            'client_id' => $authenticatedUser->id,
                            'has_token' => true
                        ]);
                    }
                    
                    $clientUpdateData = array_merge($clientUpdateData, $cardUpdateData);
                }
                
                $client = \App\Models\Client::updateOrCreate(
                    ['email' => $clientData['email']], 
                    $clientUpdateData
                );
                $clientId = $client->id;

                $commande = Commande::create([
                    'client_id' => $clientId, 'client_email' => $clientData['email'], 'client_nom' => $clientData['nom'],
                    'client_prenom' => $clientData['prenom'], 'client_telephone' => $clientData['telephone'],
                    'client_civilite' => $clientData['civilite'] ?? null, 'client_nom_societe' => $clientData['nomSociete'] ?? null,
                    'client_adresse' => $clientData['adresse'] ?? null, 'client_complement_adresse' => $clientData['complementAdresse'] ?? null,
                    'client_ville' => $clientData['ville'] ?? null, 'client_codePostal' => $clientData['codePostal'] ?? null,
                    'client_pays' => $clientData['pays'] ?? null, 'id_api_commande' => $apiResult['message'] ?? null,
                    'id_plateforme' => $idPlateforme, 'total_prix_ttc' => $commandeData['total_prix_ttc'], 'statut' => 'completed',
                    'details_commande_lignes' => json_encode($commandeData['commandeLignes']),
                    'invoice_content' => $apiResult['content'] ?? null,
                ]);

                PaymentClient::create([
                    'client_id' => $clientId, 'commande_id' => $commande->id,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id'),
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'amount' => $commande->total_prix_ttc * 100, 'currency' => 'EUR', 'status' => 'paid',
                    'payment_method' => $request->input('brand'), 'raw_response' => json_encode($request->all()),
                ]);

                // Send email, clear session, and redirect
                Session::forget(['commande_en_cours', 'monetico_order_id', 'guest_customer_details']);
                Session::put('api_payment_result', $apiResult);
                Session::put('last_commande_id', $commande->id);

                try {
                    $language = Session::get('app_language', 'fr');
                    Mail::to($commande->client_email)->send(new OrderConfirmationMail($commande, $commande->invoice_content, $language));
                    Log::info('[paymentSuccess] Confirmation email sent successfully.');
                } catch (\Exception $mailException) {
                    Log::error('[paymentSuccess] Failed to send confirmation email.', ['error' => $mailException->getMessage()]);
                }

                return redirect()->route('payment.success.show');

            } else {
                // BDM Failure -> Void Payment
                $errorMessage = $apiResult['message'] ?? 'Erreur inconnue de l\'API BDM.';
                Log::error('[paymentSuccess] BDM order creation failed. Voiding payment.', [
                    'error' => $errorMessage,
                    'api_result' => $apiResult,
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id'),
                    'response_status' => $bdmResponse->status(),
                    'response_body' => $bdmResponse->body()
                ]);

                $this->_moneticoVoidPayment($moneticoTransactionId);

                // Personnaliser le message d'erreur en fonction du type d'erreur
                $friendlyMessage = $this->getFriendlyErrorMessage($errorMessage, $apiResult);
                return redirect()->route('payment')->with('error', $friendlyMessage);
            }

        } catch (\Exception $e) {
            // General exception handler
            Log::critical('[paymentSuccess] A critical error occurred during the transaction.', [
                'error' => $e->getMessage(),
                'monetico_transaction_id' => $moneticoTransactionId ?? null,
                'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id'),
                'exception' => $e,
                'commande_data_exists' => isset($commandeData),
                'full_request' => $request->all()
            ]);

            // Attempt to void the payment as a safety net, if it hasn't been captured.
            // (If capture fails, the exception is thrown, so we land here)
            if (isset($moneticoTransactionId)) {
                $voidResponse = $this->_moneticoVoidPayment($moneticoTransactionId);
                Log::info('[paymentSuccess] Void response after exception.', [
                    'status_code' => $voidResponse->status(),
                    'response_body' => $voidResponse->body(),
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id')
                ]);
            }

            return redirect()->route('payment')->with('error', 'Un problème technique est survenu. Aucun débit n\'a été effectué. Veuillez réessayer ou contacter le support si le problème persiste.');
        }
    }

    public function handleIpn(Request $request)
    {
        // Handle the IPN from Monetico
        $data = $request->all();
        Log::info('Monetico IPN received: ' . json_encode($data));

        // Add your logic to process the IPN data, e.g., update order status

        return response()->json(['status' => 'success']);
    }

    public function showPaymentSuccess()
    {
        \Illuminate\Support\Facades\Log::info('[showPaymentSuccess] START - Displaying payment success page.');
        $apiResult = Session::get('api_payment_result');
        $lastCommandeId = Session::get('last_commande_id');

        if (!$apiResult || !$lastCommandeId) {
            \Illuminate\Support\Facades\Log::error('[showPaymentSuccess] CRITICAL: api_payment_result or last_commande_id NOT FOUND in session. Redirecting.');
            return redirect()->route('payment')->with('error', 'Votre session de paiement a expiré. Veuillez recommencer votre commande.');
        }

        \Illuminate\Support\Facades\Log::debug('[showPaymentSuccess] last_commande_id from session: ' . $lastCommandeId);

        // Fetch the Commande object with its paymentClient to get the monetico_order_id
        $commande = Commande::with('paymentClient')->find($lastCommandeId);

        if (!$commande) {
            \Illuminate\Support\Facades\Log::error('[showPaymentSuccess] CRITICAL: Commande object NOT FOUND in DB for ID: ' . $lastCommandeId . '. Redirecting.');
            return redirect()->route('payment')->with('error', 'La commande n\'a pas été trouvée. Veuillez recommencer votre commande.');
        }

        // Log the presence and part of invoice_content
        if ($commande->invoice_content) {
            \Illuminate\Support\Facades\Log::debug('[showPaymentSuccess] Commande invoice_content present. Length: ' . strlen($commande->invoice_content) . ', First 100 chars: ' . substr($commande->invoice_content, 0, 100));
        } else {
            \Illuminate\Support\Facades\Log::warning('[showPaymentSuccess] Commande invoice_content is NULL or empty for Commande ID: ' . $commande->id);
        }


        // Forget all session data after displaying the success page to prevent duplicate orders
        Session::forget(['api_payment_result', 'last_commande_id', 'commande_en_cours', 'monetico_order_id', 'guest_customer_details']);

        return view('payment-success', [
            'apiResult' => $apiResult,
            'lastCommandeId' => $lastCommandeId,
            'commande' => $commande, // Pass the full commande object
        ]);
    }

    public function paymentError(Request $request)
    {
        Log::error('Payment failed or was rejected by Monetico.', $request->all());
        return redirect()->route('payment')->with('error', 'Votre paiement a été refusé par votre banque. Veuillez vérifier vos informations de paiement et réessayer.');
    }

    public function paymentCancel(Request $request)
    {
        Log::info('Payment was cancelled by the user.', $request->all());
        return redirect()->route('payment')->with('info', 'Vous avez annulé le processus de paiement. Vous pouvez recommencer votre commande.');
    }

    public function paymentReturn(Request $request)
    {
        // Most of the time, the 'return' URL is called for successful payments.
        // We redirect to the main success handler which contains the full logic to verify and save the command.
        return redirect()->route('payment.success', $request->query());
    }

    public function clearGuestSession(Request $request)
    {
        // Use flush() to completely clear the session. This is a more robust way to "reset"
        // as it removes all data, including 'commande_en_cours', 'guest_customer_details', etc.
        // This will also log out an authenticated user, which is the expected behavior for a full reset.
        $request->session()->flush();
        
        Log::info('Full session flushed for reset.');
        return response()->json(['success' => true, 'message' => 'Session reset successfully.']);
    }

    /**
     * Check if a string is a valid UUID.
     *
     * @param string $uuid
     * @return boolean
     */
    private function isUuid($uuid)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) !== 1)) {
            return false;
        }
        return true;
    }

    // Helper method to consolidate client data retrieval
    private function getClientData($user, $guestEmail) {
        if ($user) {
            return [
                "email" => $user->email, 
                "telephone" => $user->telephone, 
                "nom" => $user->nom,
                "prenom" => $user->prenom, 
                "civilite" => $user->civilite ?? null, 
                "nomSociete" => $user->nomSociete ?? null,
                "adresse" => $user->adresse ?? null, 
                "complementAdresse" => $user->complementAdresse ?? null, 
                "ville" => $user->ville ?? null,
                "codePostal" => $user->codePostal ?? null, 
                "pays" => $user->pays ?? null,
                "is_guest" => false
            ];
        }

        if ($guestEmail) {
            \Illuminate\Support\Facades\Validator::make(['guest_email' => $guestEmail], ['guest_email' => 'required|email|max:255'])->validate();
            $persistentGuestDetails = Session::get('guest_customer_details', []);
            
            // Définir les valeurs de base pour les invités
            $baseGuestValues = [
                "email" => $guestEmail, 
                "telephone" => null, 
                "nom" => '',  // Laisser vide pour que l'utilisateur les remplisse
                "prenom" => '', 
                "adresse" => null,
                "is_guest" => true
            ];
            
            // Appliquer les valeurs par défaut pour les champs supplémentaires uniquement s'ils ne sont pas déjà définis
            $defaultValues = [
                // Ces champs sont maintenant gérés par le frontend ou la validation
            ];
            
            // Fusionner les valeurs persistantes avec les valeurs par défaut, en priorisant les valeurs persistantes
            $mergedValues = array_merge($baseGuestValues, $defaultValues, $persistentGuestDetails);
            
            return $mergedValues;
        }
        
        return null;
    }

    /**
     * Capture a pre-authorized payment via Monetico API.
     * @param string $transactionId The transaction ID from the authorization.
     * @return \Illuminate\Http\Client\Response
     */
    private function _moneticoCapturePayment(string $transactionId)
    {
        $url = config('monetico.base_url') . "/Charge/{$transactionId}/Capture";
        Log::info('Calling Monetico Capture API.', ['url' => $url]);

        return Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url);
    }

    /**
     * Void a pre-authorized payment via Monetico API.
     * @param string $transactionId The transaction ID from the authorization.
     * @return \Illuminate\Http\Client\Response
     */
    private function _moneticoVoidPayment(string $transactionId)
    {
        $url = config('monetico.base_url') . "/Charge/{$transactionId}/Void";
        Log::info('Calling Monetico Void API.', ['url' => $url]);

        return Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url);
    }

    /**
     * Extrait les informations de carte de manière sécurisée depuis la réponse Monetico
     * @param array $krAnswer La réponse décodée de Monetico
     * @param Request $request La requête complète
     * @return array|null Les informations de carte (type, last4, expiry, holderName) ou null
     */
    private function extractCardInfoFromMoneticoResponse($krAnswer, Request $request)
    {
        $cardInfo = [];
        
        // Extraire le type de carte (brand) depuis la requête ou krAnswer
        $brand = $request->input('brand');
        if (!$brand && isset($krAnswer['paymentMethod']['brand'])) {
            $brand = $krAnswer['paymentMethod']['brand'];
        }
        if ($brand) {
            // Normaliser le nom de la carte
            $cardInfo['type'] = $this->normalizeCardType($brand);
        }
        
        // Extraire les 4 derniers chiffres depuis krAnswer
        if (isset($krAnswer['paymentMethod']['pan'])) {
            $pan = $krAnswer['paymentMethod']['pan'];
            // Si c'est un numéro complet, prendre les 4 derniers chiffres
            if (strlen($pan) >= 4) {
                $cardInfo['last4'] = substr($pan, -4);
            } else {
                $cardInfo['last4'] = $pan;
            }
        } elseif (isset($krAnswer['paymentMethod']['last4'])) {
            $cardInfo['last4'] = $krAnswer['paymentMethod']['last4'];
        }
        
        // Extraire la date d'expiration
        if (isset($krAnswer['paymentMethod']['expiryMonth']) && isset($krAnswer['paymentMethod']['expiryYear'])) {
            $month = str_pad($krAnswer['paymentMethod']['expiryMonth'], 2, '0', STR_PAD_LEFT);
            $year = substr($krAnswer['paymentMethod']['expiryYear'], -2); // Prendre les 2 derniers chiffres
            $cardInfo['expiry'] = $month . '/' . $year;
        }
        
        // Extraire le nom sur la carte (si disponible)
        if (isset($krAnswer['paymentMethod']['holderName'])) {
            $cardInfo['holderName'] = $krAnswer['paymentMethod']['holderName'];
        }
        
        // IMPORTANT: Extraire le token/alias Monetico pour réutiliser la carte
        // Monetico peut retourner le token dans plusieurs endroits selon la version de l'API
        if (isset($krAnswer['paymentMethod']['token'])) {
            $cardInfo['token'] = $krAnswer['paymentMethod']['token'];
        } elseif (isset($krAnswer['paymentMethod']['alias'])) {
            $cardInfo['token'] = $krAnswer['paymentMethod']['alias'];
        } elseif (isset($krAnswer['paymentMethod']['paymentMethodToken'])) {
            $cardInfo['token'] = $krAnswer['paymentMethod']['paymentMethodToken'];
        } elseif (isset($krAnswer['token'])) {
            $cardInfo['token'] = $krAnswer['token'];
        } elseif (isset($krAnswer['alias'])) {
            $cardInfo['token'] = $krAnswer['alias'];
        }
        
        // Log pour debug
        if (isset($cardInfo['token'])) {
            Log::info('[extractCardInfoFromMoneticoResponse] Token Monetico trouvé pour réutilisation future', [
                'has_token' => true,
                'token_length' => strlen($cardInfo['token'])
            ]);
        } else {
            Log::info('[extractCardInfoFromMoneticoResponse] Aucun token Monetico trouvé dans la réponse', [
                'krAnswer_keys' => array_keys($krAnswer),
                'paymentMethod_keys' => isset($krAnswer['paymentMethod']) ? array_keys($krAnswer['paymentMethod']) : []
            ]);
        }
        
        // Retourner null si aucune information n'a été trouvée
        return !empty($cardInfo) ? $cardInfo : null;
    }
    
    /**
     * Normalise le type de carte depuis la réponse Monetico
     * @param string $brand Le brand de Monetico
     * @return string Le type normalisé
     */
    private function normalizeCardType($brand)
    {
        $brand = strtoupper(trim($brand));
        
        $typeMap = [
            'VISA' => 'Visa',
            'MASTERCARD' => 'Mastercard',
            'MASTER' => 'Mastercard',
            'AMEX' => 'American Express',
            'AMERICAN EXPRESS' => 'American Express',
            'CB' => 'CB',
            'CARTE BLEUE' => 'CB',
        ];
        
        return $typeMap[$brand] ?? $brand;
    }

    /**
     * Génère un message d'erreur convivial en fonction du type d'erreur
     */
    private function getFriendlyErrorMessage($errorMessage, $apiResult = null)
    {
        // Si c'est une erreur de validation du code postal
        if (strpos(strtolower($errorMessage), 'codepostal') !== false ||
            strpos(strtolower($errorMessage), 'code postal') !== false ||
            (is_array($apiResult) && isset($apiResult['errors']) &&
             strpos(json_encode($apiResult['errors']), 'CodePostal') !== false)) {
            return "Le code postal que vous avez saisi n'est pas valide. Veuillez vérifier votre adresse et réessayer.";
        }

        // Si c'est une erreur de validation d'email
        if (strpos(strtolower($errorMessage), 'email') !== false) {
            return "L'adresse email que vous avez saisie n'est pas valide. Veuillez la corriger et réessayer.";
        }

        // Si c'est une erreur de validation de téléphone
        if (strpos(strtolower($errorMessage), 'telephone') !== false ||
            strpos(strtolower($errorMessage), 'téléphone') !== false) {
            return "Le numéro de téléphone que vous avez saisi n'est pas valide. Veuillez le corriger et réessayer.";
        }

        // Si c'est une erreur de disponibilité
        if (strpos(strtolower($errorMessage), 'disponibilité') !== false ||
            strpos(strtolower($errorMessage), 'disponible') !== false) {
            return "Le créneau horaire sélectionné n'est plus disponible. Veuillez choisir un autre horaire et réessayer.";
        }

        // Si c'est une erreur de produit indisponible
        if (strpos(strtolower($errorMessage), 'produit') !== false &&
            (strpos(strtolower($errorMessage), 'disponible') !== false ||
             strpos(strtolower($errorMessage), 'indisponible') !== false)) {
            return "Le service que vous avez sélectionné n'est plus disponible. Veuillez modifier votre commande et réessayer.";
        }

        // Messages d'erreur génériques plus conviviaux
        if ($errorMessage === 'Erreur inconnue de l\'API BDM.' || empty(trim($errorMessage))) {
            return "Un problème est survenu lors de l'enregistrement de votre commande. Aucun débit n'a été effectué. Veuillez réessayer.";
        }

        // Retourner un message personnalisé basé sur le contenu de l'erreur
        return "Un problème est survenu lors de l'enregistrement de votre commande : {$errorMessage}. Aucun débit n'a été effectué. Veuillez vérifier vos informations et réessayer.";
    }
}