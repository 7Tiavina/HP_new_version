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
     * Convertit une date et heure du fuseau France vers UTC au format ISO8601.
     *
     * @param string $date Date au format YYYY-MM-DD
     * @param string $heure Heure au format HH:MM
     * @return string Date au format local Europe/Paris (ex: 2026-03-28T12:00:00)
     */
    private function convertFranceDateToUtc(string $date, string $heure): string
    {
        // IMPORTANT: Ne PAS convertir en UTC, garder l'heure locale Europe/Paris
        // L'API BDM attend les dates en heure locale, pas en UTC
        $carbon = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$heure}", 'Europe/Paris');
        return $carbon->format('Y-m-d\TH:i:s');
    }

    /**
     * Prépare les données de la commande et les stocke en session avant la redirection vers la page de paiement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function preparePayment(Request $request)
    {
        try {
            Log::info('Entering preparePayment method.');

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
                'guest_email' => 'nullable|email|max:255',
                'options' => 'nullable|array',
                'options.*.id' => 'required|string',
                'options.*.libelle' => 'required|string',
                'options.*.description' => 'nullable|string',
                'options.*.prix' => 'required_without:options.*.prixUnitaire|nullable|numeric',
                'options.*.prixUnitaire' => 'required_without:options.*.prix|nullable|numeric',
                'options.*.details' => 'nullable|array',
                // Validation for Premium option details - ALL OPTIONAL (will be filled at payment step)
                'options.*.details.direction' => 'nullable|string|in:terminal_to_agence,agence_to_terminal,both',

                // Arrival flow - ALL OPTIONAL (will be filled at payment step)
                'options.*.details.transport_type_arrival' => 'nullable|string',
                'options.*.details.flight_number_arrival' => 'nullable|string',
                'options.*.details.train_number_arrival' => 'nullable|string',
                'options.*.details.tgv_number_arrival' => 'nullable|string',
                'options.*.details.car_plate_arrival' => 'nullable|string',
                'options.*.details.date_arrival' => 'nullable|date',
                'options.*.details.pickup_location_arrival' => 'nullable|string',
                'options.*.details.pickup_time_arrival' => 'nullable|date_format:H:i',
                'options.*.details.instructions_arrival' => 'nullable|string',

                // Departure flow - ALL OPTIONAL (will be filled at payment step)
                'options.*.details.transport_type_departure' => 'nullable|string',
                'options.*.details.flight_number_departure' => 'nullable|string',
                'options.*.details.train_number_departure' => 'nullable|string',
                'options.*.details.tgv_number_departure' => 'nullable|string',
                'options.*.details.car_plate_departure' => 'nullable|string',
                'options.*.details.date_departure' => 'nullable|date',
                'options.*.details.restitution_location_departure' => 'nullable|string',
                'options.*.details.restitution_time_departure' => 'nullable|date_format:H:i',
                'options.*.details.instructions_departure' => 'nullable|string',

                // Contraintes (prestations complémentaires obligatoires) - pour calcul du total uniquement
                'contraintes' => 'nullable|array',
                'contraintes.*.id' => 'nullable|string',
                'contraintes.*.libelle' => 'nullable|string',
                'contraintes.*.prix' => 'nullable|numeric',
                'contraintes.*.prixUnitaire' => 'nullable|numeric',
                'contraintes.*.isMandatory' => 'nullable|boolean',
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
            $optionsTotal = 0.0;

            // 1. Process Baggages
            foreach ($validatedData['baggages'] as $baggage) {
                $expectedLibelle = $baggageTypeToLibelleMap[$baggage['type']] ?? null;
                if (!$expectedLibelle) {
                    return response()->json(['message' => 'Type de bagage non reconnu. Veuillez rafraîchir la page et réessayer.'], 400);
                }
                $productDetails = collect($validatedData['products'])->firstWhere('libelle', $expectedLibelle);
                if (!$productDetails) {
                    return response()->json([
                        'message' => 'Les tarifs ne sont pas à jour. Veuillez sélectionner un aéroport et des bagages, mettre à jour le panier, puis réessayer le paiement.',
                    ], 400);
                }

                $qty = (int)($baggage['quantity'] ?? 0);
                // Prix et remise pris de l'API BDM uniquement (pas de fallback) : camelCase ou snake_case
                $unitPriceToPay = (float)($productDetails['prixUnitaire'] ?? $productDetails['prix_unitaire'] ?? 0);
                $lineToPay = $unitPriceToPay * $qty;
                $prixTTC = round($lineToPay, 2);
                $tauxRemise = (float)($productDetails['tauxRemise'] ?? $productDetails['taux_remise'] ?? 0);
                $prixUnitaireAvantRemiseRaw = $productDetails['prixUnitaireAvantRemise'] ?? $productDetails['prix_unitaire_avant_remise'] ?? null;
                $prixUnitaireAvantRemise = $prixUnitaireAvantRemiseRaw !== null
                    ? (float)$prixUnitaireAvantRemiseRaw
                    : ($tauxRemise > 0 ? $unitPriceToPay / (1 - $tauxRemise / 100) : $unitPriceToPay);
                $prixTTCAvantRemise = round($prixUnitaireAvantRemise * $qty, 2);

                $commandeLignes[] = [
                    "idProduit" => $productDetails['id'], "idService" => $serviceId,
                    "dateDebut" => $this->convertFranceDateToUtc($validatedData['dateDepot'], $validatedData['heureDepot']),
                    "dateFin" => $this->convertFranceDateToUtc($validatedData['dateRecuperation'], $validatedData['heureRecuperation']),
                    "prixTTC" => $prixTTC,
                    "prixTTCAvantRemise" => $prixTTCAvantRemise,
                    "tauxRemise" => $tauxRemise,
                    "quantite" => $qty,
                    "libelleProduit" => $productDetails['libelle']
                ];
            }

            // 2. Process Options - Fetch fresh prices from BDM API
            if (!empty($validatedData['options'])) {
                Log::info('[preparePayment] Processing options from frontend...', [
                    'options_count' => count($validatedData['options'])
                ]);

                $bdmToken = $this->getBdmToken();
                $idPlateforme = $validatedData['airportId'];

                // Build commandeLignes for API call (with baggages to get correct options prices)
                $apiCommandeLignes = [];
                foreach ($commandeLignes as $ligne) {
                    $apiCommandeLignes[] = [
                        'idProduit' => $ligne['idProduit'],
                        'idService' => $ligne['idService'],
                        'dateDebut' => $ligne['dateDebut'],
                        'dateFin' => $ligne['dateFin'],
                        'quantite' => $ligne['quantite'],
                    ];
                }

                // Call BDM API to get fresh option prices using BdmApiService
                $bdmApiService = new \App\Services\BdmApiService();
                $lang = Session::get('app_language', 'fr');

                Log::info('[preparePayment] Calling BdmApiService::getCommandeOptionsQuote with baggages...', [
                    'idPlateforme' => $idPlateforme,
                    'commandeLignes_count' => count($apiCommandeLignes),
                    'lang' => $lang
                ]);

                // Pass the baggages to the API so it can calculate options prices correctly
                $freshOptionsResult = $bdmApiService->getCommandeOptionsQuote(
                    $idPlateforme,
                    $apiCommandeLignes,
                    $validatedData['guest_email'] ?? ($user->email ?? null),
                    null,
                    $lang
                );

                Log::info('[preparePayment] BdmApiService response', [
                    'result' => $freshOptionsResult,
                ]);

                $freshOptionsData = [];
                if ($freshOptionsResult && isset($freshOptionsResult['content']) && is_array($freshOptionsResult['content'])) {
                    foreach ($freshOptionsResult['content'] as $option) {
                        if (isset($option['id'])) {
                            $freshOptionsData[$option['id']] = $option;
                        }
                    }
                    Log::info('[preparePayment] Fresh options retrieved', ['count' => count($freshOptionsData)]);
                }

                foreach ($validatedData['options'] as $selectedOption) {
                    // If this is the premium option, store its details separately
                    if (stripos($selectedOption['libelle'], 'Premium') !== false) {
                        $premiumDetails = $selectedOption['details'] ?? null;
                        
                        // === FUSIONNER AVEC LES INFOS PREMIUM DE LA SESSION (si disponibles) ===
                        $sessionPremiumDetails = Session::get('premiumDetails');
                        if ($sessionPremiumDetails && is_array($sessionPremiumDetails)) {
                            // Fusionner les données de la session avec celles du frontend
                            $premiumDetails = array_merge($premiumDetails ?? [], $sessionPremiumDetails);
                            Log::info('[preparePayment] Merged premium details from session', [
                                'session_premium_details' => $sessionPremiumDetails,
                                'merged_premium_details' => $premiumDetails,
                            ]);
                        }
                    }

                    // Try to get fresh price from BDM API response
                    $optionId = $selectedOption['id'];
                    $freshOption = $freshOptionsData[$optionId] ?? null;
                    $optDescription = $selectedOption['description'] ?? null;

                    if ($freshOption) {
                        // Use fresh price from BDM API
                        $optPrix = (float)($freshOption['prixUnitaire'] ?? 0);
                        $optTauxRemise = (float)($freshOption['tauxRemise'] ?? 0);
                        $optPrixAvantRemise = (float)($freshOption['prixUnitaireAvantRemise'] ?? $freshOption['prixUnitaire'] ?? 0);
                        $optDescription = $freshOption['description'] ?? $freshOption['Description'] ?? $optDescription;

                        Log::info('[preparePayment] Using FRESH price from BDM', [
                            'option_id' => $optionId,
                            'libelle' => $selectedOption['libelle'],
                            'fresh_price' => $optPrix,
                            'fresh_price_before_discount' => $optPrixAvantRemise,
                            'fresh_discount_rate' => $optTauxRemise,
                            'frontend_price' => $selectedOption['prix'] ?? $selectedOption['prixUnitaire'] ?? 0,
                        ]);
                    } else {
                        // Fallback to frontend price (should not happen)
                        $optPrix = (float)($selectedOption['prix'] ?? $selectedOption['prixUnitaire'] ?? 0);

                        // Use default 10% discount for known options if API didn't return it
                        $optTauxRemise = (float)($selectedOption['tauxRemise'] ?? $selectedOption['taux_remise'] ?? 10.0);
                        $optPrixAvantRemiseRaw = $selectedOption['prixTTCAvantRemise'] ?? $selectedOption['prix_ttc_avant_remise'] ?? null;
                        $optPrixAvantRemise = $optPrixAvantRemiseRaw !== null
                            ? (float)$optPrixAvantRemiseRaw
                            : ($optTauxRemise > 0 ? $optPrix / (1 - $optTauxRemise / 100) : $optPrix);

                        Log::warning('[preparePayment] Using FALLBACK frontend price (BDM API returned empty)', [
                            'option_id' => $optionId,
                            'libelle' => $selectedOption['libelle'],
                            'price' => $optPrix,
                            'price_before_discount' => $optPrixAvantRemise,
                            'discount_rate' => $optTauxRemise,
                        ]);
                    }

                    $optionsTotal += $optPrix;

                    $commandeLignes[] = [
                        "id" => $selectedOption['id'],
                        "idProduit" => $selectedOption['id'],
                        "reference" => $selectedOption['referenceInterne'] ?? null,
                        "referenceInterne" => $selectedOption['referenceInterne'] ?? null,
                        "idService" => $serviceId,
                        "dateDebut" => $this->convertFranceDateToUtc($validatedData['dateDepot'], $validatedData['heureDepot']),
                        "dateFin" => $this->convertFranceDateToUtc($validatedData['dateRecuperation'], $validatedData['heureRecuperation']),
                        "prixTTC" => $optPrix,
                        "prixTTCAvantRemise" => round($optPrixAvantRemise, 2),
                        "tauxRemise" => $optTauxRemise,
                        "quantite" => 1,
                        "libelleProduit" => $selectedOption['libelle'],
                        "description" => $optDescription,
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
            $guestEmail = $validatedData['guest_email'] ?? $request->input('guest_email');
            $clientData = $this->getClientData($user, $guestEmail);
            if (!$clientData) {
                return response()->json([
                    'message' => 'Veuillez vous connecter ou indiquer votre adresse email (paiement invité).',
                ], 401);
            }

            $optionsTotal = round($optionsTotal, 2);

            // Calculer le total de base (baggages + options)
            $totalToPay = round(array_reduce($commandeLignes, fn($sum, $item) => $sum + ((float)($item['prixTTC'] ?? 0)), 0), 2);
            $totalNormalPrice = round(array_reduce($commandeLignes, fn($sum, $item) => $sum + ((float)($item['prixTTCAvantRemise'] ?? $item['prixTTC'] ?? 0)), 0), 2);
            
            // Ajouter les contraintes au total (prestations complémentaires obligatoires)
            // Les contraintes ne seront PAS envoyées à BDM mais le total payé doit les inclure
            $contraintes = $validatedData['contraintes'] ?? [];
            $contraintesTotal = 0;
            if (!empty($contraintes)) {
                foreach ($contraintes as $contrainte) {
                    // Nettoyer les données de contrainte pour éviter les problèmes de sérialisation
                    $cleanContrainte = [
                        'id' => $contrainte['id'] ?? '',
                        'libelle' => $contrainte['libelle'] ?? '',
                        'prix' => (float)($contrainte['prix'] ?? $contrainte['prixUnitaire'] ?? 0),
                        'prixUnitaire' => (float)($contrainte['prix'] ?? $contrainte['prixUnitaire'] ?? 0),
                        'isMandatory' => true
                    ];
                    $contraintesTotal += $cleanContrainte['prix'];
                }
                $contraintesTotal = round($contraintesTotal, 2);
                $totalToPay += $contraintesTotal;
                $totalNormalPrice += $contraintesTotal;
                
                Log::info('[preparePayment] Contraintes ajoutées au total', [
                    'contraintes_count' => count($contraintes),
                    'contraintes_total' => $contraintesTotal,
                ]);
            }
            
            $totalToPay = round($totalToPay, 2);
            $totalNormalPrice = round($totalNormalPrice, 2);
            
            $discountAmount = round(max(0, $totalNormalPrice - $totalToPay), 2);
            $firstTauxRemise = 0;
            foreach ($commandeLignes as $ligne) {
                $t = (float)($ligne['tauxRemise'] ?? 0);
                if ($t > 0) {
                    $firstTauxRemise = $t;
                    break;
                }
            }
            $discountPercent = $discountAmount > 0 ? ($firstTauxRemise > 0 ? $firstTauxRemise : 10) : 0;

            $commandeData = [
                'idPlateforme' => $validatedData['airportId'],
                'airportName' => $validatedData['airportName'],
                'commandeLignes' => $commandeLignes,
                'commandeInfos' => $commandeInfos,
                'client' => $clientData,
                'contraintes' => $contraintes, // Stocker les contraintes pour référence
                'contraintes_total' => $contraintesTotal,
                'total_normal_price' => $totalNormalPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'total_prix_ttc' => $totalToPay,
            ];

            Session::put('commande_en_cours', $commandeData);
            Log::info('Commande data stored in session.');

            return response()->json(['message' => 'Commande préparée avec succès.', 'redirect_url' => route('payment')]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in preparePayment', ['errors' => $e->errors()]);
            $errors = $e->errors();
            $firstMessage = is_array($errors) ? (reset($errors)[0] ?? null) : null;
            $message = $firstMessage ?: 'Les données fournies sont invalides. Vérifiez les champs du formulaire (dates, options Premium, etc.).';
            return response()->json(['message' => $message, 'errors' => $errors], 422);
        } catch (\Exception $e) {
            Log::error('Error in preparePayment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Une erreur interne est survenue lors de la préparation du paiement.'], 500);
        }
    }

    public function updateGuestInfoInSession(Request $request)
    {
        Log::info('=== [updateGuestInfoInSession] START ===', [
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'has_commande_en_cours' => $request->session()->has('commande_en_cours'),
        ]);

        try {
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
                
                // Premium details (optional, only if premium option is selected)
                'premiumDetails' => 'sometimes|nullable|array',
                'premiumDetails.direction' => 'sometimes|nullable|string|in:terminal_to_agence,agence_to_terminal,both',
                'premiumDetails.transport_type_arrival' => 'sometimes|nullable|string',
                'premiumDetails.transport_type_departure' => 'sometimes|nullable|string',
                'premiumDetails.flight_number_arrival' => 'sometimes|nullable|string',
                'premiumDetails.flight_number_departure' => 'sometimes|nullable|string',
                'premiumDetails.train_number_arrival' => 'sometimes|nullable|string',
                'premiumDetails.train_number_departure' => 'sometimes|nullable|string',
                'premiumDetails.date_arrival' => 'sometimes|nullable|string',
                'premiumDetails.date_departure' => 'sometimes|nullable|string',
                'premiumDetails.pickup_location_arrival' => 'sometimes|nullable|string',
                'premiumDetails.restitution_location_departure' => 'sometimes|nullable|string',
                'premiumDetails.pickup_time_arrival' => 'sometimes|nullable|string',
                'premiumDetails.restitution_time_departure' => 'sometimes|nullable|string',
                'premiumDetails.instructions_arrival' => 'sometimes|nullable|string',
                'premiumDetails.instructions_departure' => 'sometimes|nullable|string',
            ]);

            Log::info('[updateGuestInfoInSession] Validation passed');

            // Force phone to E.164 with country code (e.g. +33...), no auto-detection.
            $rawPhone = (string)($validated['telephone'] ?? '');
            Log::info('[updateGuestInfoInSession] Raw phone before normalization');
            
            $rawPhone = trim($rawPhone);
            $rawPhone = preg_replace('/[^\d\+]/', '', $rawPhone); // keep digits and +
            if (str_starts_with($rawPhone, '00')) {
                $rawPhone = '+' . substr($rawPhone, 2);
            }

            $hasPlus = str_starts_with($rawPhone, '+');
            $digits = preg_replace('/\D/', '', $rawPhone);
            $len = strlen($digits);

            Log::info('[updateGuestInfoInSession] Phone normalization result', [
                'hasPlus' => $hasPlus,
                'len' => $len,
            ]);

            if (!$hasPlus || $len < 6 || $len > 15) {
                Log::error('[updateGuestInfoInSession] Phone validation failed', [
                    'len' => $len,
                ]);
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'telephone' => 'Veuillez renseigner votre numéro avec le code pays (ex: +33...).',
                ]);
            }

            $validated['telephone'] = '+' . $digits;
            Log::info('[updateGuestInfoInSession] Phone validated');

            // SERVER-SIDE DEFAULTS
            $data = array_merge([
                'nomSociete'        => null,
                'complementAdresse' => null,
            ], $validated);

            Log::info('[updateGuestInfoInSession] Data to store');

            Session::put('guest_customer_details', $data);
            Log::info('[updateGuestInfoInSession] guest_customer_details stored in session');

            $commandeData = Session::get('commande_en_cours');
            Log::info('[updateGuestInfoInSession] commande_en_cours before update', [
                'exists' => $commandeData !== null,
                'is_guest' => $commandeData['client']['is_guest'] ?? null,
            ]);

            if ($commandeData && isset($commandeData['client']['is_guest'])) {
                // Preserve is_guest flag and other client data fields
                $originalIsGuest = $commandeData['client']['is_guest'];
                $originalEmail = $commandeData['client']['email'] ?? null;

                // Merge new data while preserving critical flags
                $commandeData['client'] = array_merge($commandeData['client'], $data);

                // Ensure is_guest and email are preserved
                $commandeData['client']['is_guest'] = $originalIsGuest;
                $commandeData['client']['email'] = $originalEmail;

                // === STOCKER LES INFOS PREMIUM DANS LA SESSION ET METTRE A JOUR COMMANDEINFOS ===
                if (isset($validated['premiumDetails']) && is_array($validated['premiumDetails'])) {
                    $premiumDetails = $validated['premiumDetails'];
                    $commandeData['premiumDetails'] = $premiumDetails;
                    
                    // === GENERER COMMANDEINFOS DEPUIS LES INFOS PREMIUM ===
                    $commandeInfos = [
                        'modeTransport' => '',
                        'lieu' => '',
                        'commentaires' => '',
                    ];
                    
                    if (isset($premiumDetails['direction'])) {
                        $commentairesArray = [];
                        $commentairesArray[] = "Type de service: Service Premium complet (Arrivée + Départ)";
                        
                        // === ARRIVAL FLOW ===
                        if (!empty($premiumDetails['transport_type_arrival'])) {
                            $modeTransport = $premiumDetails['transport_type_arrival'];
                            $displayModeTransport = [
                                'airport' => 'Aéroport',
                                'public_transport' => 'Transport en commun',
                                'train' => 'Train',
                                'other' => 'Autre',
                            ][$modeTransport] ?? ucfirst(str_replace('_', ' ', $modeTransport));
                            
                            $commandeInfos['modeTransport'] = $displayModeTransport;
                            
                            if ($modeTransport === 'airport' && !empty($premiumDetails['flight_number_arrival'])) {
                                $commentairesArray[] = "Vol arrivée: " . $premiumDetails['flight_number_arrival'];
                            }
                            if ($modeTransport === 'train' && !empty($premiumDetails['train_number_arrival'])) {
                                $commentairesArray[] = "Train arrivée: " . $premiumDetails['train_number_arrival'];
                            }
                        }
                        
                        // === DEPARTURE FLOW ===
                        if (!empty($premiumDetails['transport_type_departure'])) {
                            $modeTransport = $premiumDetails['transport_type_departure'];
                            $displayModeTransport = [
                                'airport' => 'Aéroport',
                                'public_transport' => 'Transport en commun',
                                'train' => 'Train',
                                'other' => 'Autre',
                            ][$modeTransport] ?? ucfirst(str_replace('_', ' ', $modeTransport));
                            
                            // Only set if not already set from arrival
                            if (empty($commandeInfos['modeTransport'])) {
                                $commandeInfos['modeTransport'] = $displayModeTransport;
                            }
                            
                            if ($modeTransport === 'airport' && !empty($premiumDetails['flight_number_departure'])) {
                                $commentairesArray[] = "Vol départ: " . $premiumDetails['flight_number_departure'];
                            }
                            if ($modeTransport === 'train' && !empty($premiumDetails['train_number_departure'])) {
                                $commentairesArray[] = "Train départ: " . $premiumDetails['train_number_departure'];
                            }
                        }
                        
                        // === LIEU (pickup location priority) ===
                        if (!empty($premiumDetails['pickup_location_arrival_libelle'])) {
                            $commandeInfos['lieu'] = $premiumDetails['pickup_location_arrival_libelle'];
                        } else if (!empty($premiumDetails['restitution_location_departure_libelle'])) {
                            $commandeInfos['lieu'] = $premiumDetails['restitution_location_departure_libelle'];
                        } else if (!empty($premiumDetails['pickup_location_arrival'])) {
                            $commandeInfos['lieu'] = "Lieu ID: " . $premiumDetails['pickup_location_arrival'];
                        } else if (!empty($premiumDetails['restitution_location_departure'])) {
                            $commandeInfos['lieu'] = "Lieu ID: " . $premiumDetails['restitution_location_departure'];
                        } else {
                            $commandeInfos['lieu'] = 'Non spécifié';
                        }
                        
                        // === DATES ET HEURES ===
                        if (!empty($premiumDetails['date_arrival'])) {
                            $commentairesArray[] = "Date arrivée: " . $premiumDetails['date_arrival'];
                        }
                        if (!empty($premiumDetails['pickup_time_arrival'])) {
                            $commentairesArray[] = "Heure prise en charge: " . $premiumDetails['pickup_time_arrival'];
                        }
                        if (!empty($premiumDetails['date_departure'])) {
                            $commentairesArray[] = "Date départ: " . $premiumDetails['date_departure'];
                        }
                        if (!empty($premiumDetails['restitution_time_departure'])) {
                            $commentairesArray[] = "Heure restitution: " . $premiumDetails['restitution_time_departure'];
                        }
                        
                        // === INSTRUCTIONS ===
                        if (!empty($premiumDetails['instructions_arrival'])) {
                            $commentairesArray[] = "Infos complémentaires: " . $premiumDetails['instructions_arrival'];
                        }
                        
                        $commandeInfos['commentaires'] = implode('; ', $commentairesArray);
                    }
                    
                    $commandeData['commandeInfos'] = $commandeInfos;
                    
                    Log::info('[updateGuestInfoInSession] commandeInfos generated from premium details', [
                        'commandeInfos' => $commandeInfos,
                    ]);
                }

                Session::put('commande_en_cours', $commandeData);
                
                // === IMPORTANT: Sauvegarder premiumDetails dans la session principale aussi ===
                if (isset($validated['premiumDetails']) && is_array($validated['premiumDetails'])) {
                    Session::put('premiumDetails', $validated['premiumDetails']);
                    Log::info('[updateGuestInfoInSession] premiumDetails saved to session', [
                        'premiumDetails' => $validated['premiumDetails'],
                    ]);
                }
                
                Log::info('[updateGuestInfoInSession] commande_en_cours updated with guest info', [
                    'client_email' => $commandeData['client']['email'] ?? null,
                    'is_guest' => $commandeData['client']['is_guest'] ?? null,
                    'has_premium_details' => isset($commandeData['premiumDetails']),
                ]);
            }

            Log::info('=== [updateGuestInfoInSession] SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Guest information updated in session.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('=== [updateGuestInfoInSession] VALIDATION ERROR ===', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('=== [updateGuestInfoInSession] EXCEPTION ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get BDM API token with automatic refresh on expiration
     * Token cached for only 5 minutes to avoid expiration issues
     */
    private function getBdmToken(bool $forceRefresh = false): string
    {
        $cacheKey = 'bdm_api_token';
        $cacheTtl = 300; // 5 minutes instead of 55 to avoid expiration
        
        // If force refresh, forget the cached token
        if ($forceRefresh) {
            Cache::forget($cacheKey);
            Log::info('Forcing BDM token refresh');
        }
        
        return Cache::remember($cacheKey, $cacheTtl, function () {
            Log::info('Cache BDM token expiré ou forcé. Demande d\'un nouveau token.');
            
            $credentials = [
                'userName' => config('services.bdm.username'),
                'email' => config('services.bdm.email'),
                'password' => config('services.bdm.password'),
            ];
            
            Log::info('Tentative d\'authentification BDM', [
                'url' => config('services.bdm.base_url') . '/User/Login',
                'username' => $credentials['userName'],
            ]);
            
            try {
                $response = Http::timeout(30)->post(
                    config('services.bdm.base_url') . '/User/Login', 
                    $credentials
                );
                
                // Log the raw response for debugging
                Log::info('Réponse brute BDM', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                // Handle 400/401 errors - credentials might be wrong
                if ($response->status() === 400 || $response->status() === 401) {
                    Log::error('BDM API authentication failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Authentification API BDM échouée: Credentials invalides ou API indisponible');
                }
                
                $response->throw();
                
                $responseData = $response->json();
                
                if (!isset($responseData['isSucceed']) || !$responseData['isSucceed']) {
                    Log::error('L\'API BDM a refusé la connexion', ['response' => $responseData]);
                    throw new \Exception('Authentification API BDM échouée: L\'API a refusé la connexion');
                }
                
                $token = $responseData['data']['accessToken'] ?? null;
                
                if (!$token) {
                    Log::error('Impossible de récupérer l\'accessToken depuis la réponse de l\'API BDM', [
                        'response' => $responseData
                    ]);
                    throw new \Exception('Authentification API BDM échouée: token manquant dans la réponse');
                }
                
                Log::info('✅ AUTHENTIFICATION API BDM RÉUSSIE. Token obtenu (valide 5 min).');
                return $token;
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('BDM API connection failed', [
                    'error' => $e->getMessage(),
                    'url' => config('services.bdm.base_url')
                ]);
                throw new \Exception('API BDM inaccessible: ' . $e->getMessage());
            }
        });
    }

    public function redirectToMonetico()
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
            Log::error('Monetico redirection failed: Missing customer email, first name or last name.');
            return null;
        }

        // Préparer les données de paiement
        // IMPORTANT: Monetico ne permet PAS de pré-remplir les champs de carte pour des raisons PCI-DSS
        // Le formulaire doit TOUJOURS être rempli manuellement par l'utilisateur
        // On ne passe PAS de token ni de données de pré-remplissage dans CreatePayment
        // Le token sera utilisé uniquement pour les paiements récurrents via API directe (sans formulaire)
        $paymentMethod = ['type' => 'Card'];
        
        // Note: On ne passe pas le token ici car CreatePayment crée un formulaire qui nécessite
        // une saisie manuelle. Pour utiliser un token, il faudrait faire un paiement direct via API
        // sans formulaire, mais cela nécessiterait le CVV à chaque fois, donc pas vraiment utile.
        
        Log::info('Creating Monetico payment form (manual entry required for PCI-DSS compliance)', [
            'note' => 'Token and card data cannot be pre-filled in the form for security reasons'
        ]);

        $payload = [
            'shopId' => config('monetico.login'),
            'amount' => (int)($commandeData['total_prix_ttc'] * 100),
            'currency' => 'EUR',
            'orderId' => $orderId,
            'paymentAction' => 'Authorization', // Demander une pré-autorisation au lieu d'un paiement direct
            'customer' => ['email' => $customerEmail, 'firstName' => $customerFirstName, 'lastName' => $customerLastName],
            'paymentMethod' => $paymentMethod,
            'paymentFormConfig' => [
                'toolbarVisibility' => false,
            ],
            'urls' => [
                'success' => route('payment.success'),
                'error' => route('payment.error'),
                'cancel' => route('payment.cancel'),
                'return' => route('payment.return'),
            ],
        ];

        Log::info('Calling Monetico CreatePayment API with correct Basic Auth.', [
            'shopId' => config('monetico.login'),
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'orderId' => $payload['orderId'],
        ]);

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
                    Log::info('Monetico CreatePayment SUCCESS - formToken obtained', [
                        'order_id' => $orderId,
                        'formToken_length' => strlen($paymentData['answer']['formToken']),
                    ]);
                    return $paymentData['answer']['formToken'];
                } else {
                    Log::error('Monetico API response missing formToken', [
                        'response' => $paymentData,
                    ]);
                }
            } else {
                Log::error('Monetico API error (Basic Auth flow): ' . $response->body(), [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'request_payload' => $payload,
                ]);
                return null;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Monetico API connection error: ' . $e->getMessage(), [
                'url' => config('monetico.base_url') . '/Charge/CreatePayment',
                'exception' => $e,
                'request_payload' => $payload,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Monetico API exception: ' . $e->getMessage(), [
                'exception' => $e,
                'request_payload' => $payload,
            ]);
            return null;
        }
    }

    public function showPaymentPage(Request $request)
    {
        // Indicate that we are on the payment page for the "Back" button on /account
        session(['from_payment' => true]);
        
        Log::info('----------------------------------------------------');
        Log::info('[showPaymentPage] START - Handling /payment route.');
        Log::info('[showPaymentPage] Session ID: ' . session()->getId());
        Log::info('[showPaymentPage] Session data keys: ' . json_encode(array_keys(session()->all())));

        // Restore booking state from login redirect (from /link-form context)
        $bookingState = Session::get('booking_form_state');
        if ($bookingState && !Session::has('commande_en_cours')) {
            Log::info('[showPaymentPage] Restoring booking state from login redirect');
            try {
                // Build a request and forward to preparePayment
                $restoreRequest = new \Illuminate\Http\Request();
                $restoreRequest->setMethod('POST');
                $restoreRequest->query->set('lang', Session::get('app_language', 'fr'));

                // Map cart data to preparePayment format
                $dateDepot = $bookingState['dateDepot'];
                $heureDepot = $bookingState['heureDepot'];
                $dateRecup = $bookingState['dateRecuperation'];
                $heureRecup = $bookingState['heureRecuperation'];
                $airportName = $bookingState['airportName'] ?? '';

                // Derive airport name from lieux data if not set
                if (empty($airportName) && !empty($bookingState['globalLieuxData'])) {
                    $firstLieu = reset($bookingState['globalLieuxData']);
                    if (is_array($firstLieu)) {
                        $airportName = $firstLieu['plateforme'] ?? '';
                    }
                }

                $restoreRequest->request->set('airportId', $bookingState['airportId']);
                $restoreRequest->request->set('airportName', $airportName);
                $restoreRequest->request->set('dateDepot', $dateDepot);
                $restoreRequest->request->set('heureDepot', $heureDepot);
                $restoreRequest->request->set('dateRecuperation', $dateRecup);
                $restoreRequest->request->set('heureRecuperation', $heureRecup);
                $restoreRequest->request->set('guest_email', $bookingState['guestEmail'] ?? null);
                $restoreRequest->request->set('lang', Session::get('app_language', 'fr'));
                $restoreRequest->request->set('options', []);
                $restoreRequest->request->set('contraintes', []);

                // Convert cartItems to baggages format
                $baggages = [];
                $products = $bookingState['globalProductsData'] ?? [];
                foreach ($bookingState['cartItems'] as $item) {
                    // cartItems structure: {itemCategory, productId, quantity, baggageType, ...}
                    if ($item['itemCategory'] === 'baggage' || $item['type'] === 'baggage') {
                        $baggages[] = [
                            'type' => $item['baggageType'] ?? $item['type'] ?? 'cabin',
                            'quantity' => $item['quantity'] ?? 1,
                        ];
                    }
                }
                $restoreRequest->request->set('baggages', $baggages);
                $restoreRequest->request->set('products', $products);

                Log::info('[showPaymentPage] Calling preparePayment with restored state', [
                    'baggages' => $baggages,
                    'products_count' => count($products),
                ]);

                $prepareResponse = $this->preparePayment($restoreRequest);

                // preparePayment populated commande_en_cours in session
                // Clear booking state and redirect to /payment
                Session::forget('booking_form_state');
                return redirect(route('payment'));
            } catch (\Exception $e) {
                Log::error('[showPaymentPage] Failed to restore booking state: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                Session::forget('booking_form_state');
            }
        }

        // Check if user has already completed a payment recently (to prevent duplicate orders)
        $lastCommandeId = Session::get('last_commande_id');
        $apiPaymentResult = Session::get('api_payment_result');

        // If both are present, the user has already completed a payment
        if ($lastCommandeId && $apiPaymentResult) {
            Log::info('[showPaymentPage] User has already completed a payment. Redirecting to success page.');
            return redirect()->route('payment.success.show')->with('info', 'Votre commande a déjà été traitée avec succès.');
        }

        $commandeData = Session::get('commande_en_cours');
        Log::info('[showPaymentPage] commande_en_cours exists: ' . ($commandeData ? 'YES' : 'NO'));
        if ($commandeData) {
            Log::info('[showPaymentPage] commande_en_cours client data exists');
        }
        
        if (!$commandeData || !isset($commandeData['client'])) {
            Log::error('[showPaymentPage] CRITICAL: Commande data or client info NOT FOUND in session. Aborting.');
            // FIX: Redirect to form-consigne instead of payment to avoid infinite loop
            return redirect()->route('form-consigne')->with('error', 'Votre session a expiré ou vos informations sont invalides. Veuillez recommencer votre commande depuis le début.');
        }

        $clientDataFromSession = $commandeData['client'];
        $isGuest = $clientDataFromSession['is_guest'] ?? false;
        
        // FIX: If is_guest is false but no authenticated user, force is_guest to true
        // This happens when session data is corrupted or incorrectly set
        if (!$isGuest && !Auth::guard('client')->check()) {
            Log::warning('[showPaymentPage] is_guest is false but no authenticated user. Forcing is_guest to true.');
            $isGuest = true;
            // Also fix the session data
            $commandeData['client']['is_guest'] = true;
            Session::put('commande_en_cours', $commandeData);
        }
        
        Log::info('[showPaymentPage] isGuest determined from session: ' . ($isGuest ? 'TRUE' : 'FALSE'));

        $user = null;

        if ($isGuest) {
            $user = (object) $clientDataFromSession;
            Log::info('[showPaymentPage] Using guest user object', [
                'telephone' => $user->telephone ?? 'NULL',
                'email' => $user->email ?? 'NULL',
            ]);
        } else {
            $user = Auth::guard('client')->user();
            Log::info('[showPaymentPage] Auth guard check', [
                'guard_check' => Auth::guard('client')->check(),
                'guard_id' => Auth::guard('client')->id(),
                'user_id' => $user?->id,
            ]);
            if (!$user) {
                // This should not happen anymore due to the fix above
                Log::error('[showPaymentPage] CRITICAL: is_guest is false but no authenticated user. Redirecting to form.');
                return redirect()->route('form-consigne')->with('error', 'Session invalide. Veuillez recommencer votre commande.');
            }
        }

        $isProfileComplete = !empty($user->telephone);
        Log::info('[showPaymentPage] isProfileComplete: ' . ($isProfileComplete ? 'TRUE' : 'FALSE') . ' (telephone: ' . ($user->telephone ?? 'NULL') . ')');

        // === VERIFICATION PREMIUM ===
        // Vérifier si premium est dans le panier
        $hasPremiumInCart = false;
        $commandeLignes = $commandeData['commandeLignes'] ?? [];
        foreach ($commandeLignes as $ligne) {
            if (isset($ligne['libelleProduit']) && stripos($ligne['libelleProduit'], 'Premium') !== false) {
                $hasPremiumInCart = true;
                break;
            }
        }
        
        // Vérifier si les infos premium sont complètes
        $premiumDetailsComplete = false;
        if ($hasPremiumInCart) {
            $premiumDetails = Session::get('premiumDetails');
            $commandeInfos = $commandeData['commandeInfos'] ?? [];
            
            // Vérifier si on a les infos essentielles
            if ($premiumDetails && 
                !empty($premiumDetails['transport_type_arrival']) && 
                !empty($premiumDetails['pickup_location_arrival']) &&
                !empty($premiumDetails['transport_type_departure']) && 
                !empty($premiumDetails['restitution_location_departure'])) {
                $premiumDetailsComplete = true;
            }
        }
        
        // Le profil n'est considéré complet que si :
        // - Sans premium : isProfileComplete = true
        // - Avec premium : isProfileComplete = true ET premiumDetailsComplete = true
        $isProfileAndPremiumComplete = false;
        if ($hasPremiumInCart) {
            $isProfileAndPremiumComplete = $isProfileComplete && $premiumDetailsComplete;
        } else {
            $isProfileAndPremiumComplete = $isProfileComplete;
        }
        
        Log::info('[showPaymentPage] hasPremiumInCart: ' . ($hasPremiumInCart ? 'TRUE' : 'FALSE'));
        Log::info('[showPaymentPage] premiumDetailsComplete: ' . ($premiumDetailsComplete ? 'TRUE' : 'FALSE'));
        Log::info('[showPaymentPage] isProfileAndPremiumComplete: ' . ($isProfileAndPremiumComplete ? 'TRUE' : 'FALSE'));

        $formToken = null;

        if ($isProfileAndPremiumComplete) {
            Log::info('[showPaymentPage] Client profile and premium (if applicable) are complete. Proceeding to get formToken for pre-authorization.');
            // S'assurer que les données client sont correctement formatées (pas un objet Laravel)
            $commandeData['client'] = $this->getClientData($user, null);
            Session::put('commande_en_cours', $commandeData);

            try {
                $formToken = $this->redirectToMonetico();
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
            Log::warning('[showPaymentPage] Profile for client is incomplete. Displaying form for completion.');
        }

        // Get any error message from the session
        $errorMessage = session('error');
        $hasError = !empty($errorMessage);

        Log::info('[showPaymentPage] END - Returning view', [
            'isProfileComplete' => $isProfileComplete,
            'isProfileAndPremiumComplete' => $isProfileAndPremiumComplete,
            'hasPremiumInCart' => $hasPremiumInCart,
            'premiumDetailsComplete' => $premiumDetailsComplete,
            'isGuest' => $isGuest,
            'hasFormToken' => $formToken !== null,
        ]);
        Log::info('----------------------------------------------------');

        return view('payment', compact('user', 'formToken', 'isProfileComplete', 'isProfileAndPremiumComplete', 'hasPremiumInCart', 'premiumDetailsComplete', 'isGuest', 'errorMessage', 'hasError'));
    }

    public function paymentSuccess(Request $request)
    {
        $commandeData = Session::get('commande_en_cours');
        if (!$commandeData) {
            return redirect()->route('payment')->with('error', 'Votre session a expiré. Veuillez recommencer votre commande.');
        }

        // LOG de la requête reçue de Monetico
        Log::info('[paymentSuccess] Requête reçue de Monetico', [
            'has_kr_answer' => $request->has('kr-answer'),
        ]);

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
            // Get BDM token with retry on 400 error
            $token = null;
            $retryCount = 0;
            $maxRetries = 2;
            
            while ($retryCount < $maxRetries && !$token) {
                try {
                    $token = $this->getBdmToken($retryCount > 0); // Force refresh on retry
                } catch (\Exception $e) {
                    $retryCount++;
                    Log::warning('BDM token attempt failed, retrying...', [
                        'attempt' => $retryCount,
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($retryCount >= $maxRetries) {
                        throw $e;
                    }
                    
                    // Clear cache and retry
                    Cache::forget('bdm_api_token');
                }
            }
            
            $idPlateforme = $commandeData['idPlateforme'];

            $lignesProduits = [];
            $lignesOptions = [];
            foreach ($commandeData['commandeLignes'] as $ligne) {
                $isOption = isset($ligne['is_option']) && $ligne['is_option'];
                unset($ligne['is_option']);
                
                // API BDM/ERP exige prixTTCAvantRemise et tauxRemise sur chaque ligne
                $prixTTC = (float)($ligne['prixTTC'] ?? 0);
                $ligne['prixTTCAvantRemise'] = $ligne['prixTTCAvantRemise'] ?? $prixTTC;
                $ligne['tauxRemise'] = $ligne['tauxRemise'] ?? 0;
                
                // IMPORTANT: Retirer les champs inutiles des options pour l'API BDM
                if ($isOption) {
                    // BDM attend seulement les champs essentiels pour les options
                    $lignesOptions[] = [
                        'idProduit' => $ligne['idProduit'],
                        'idService' => $ligne['idService'],
                        'dateDebut' => $ligne['dateDebut'],
                        'dateFin' => $ligne['dateFin'],
                        'prixTTC' => $ligne['prixTTC'],
                        'prixTTCAvantRemise' => $ligne['prixTTCAvantRemise'],
                        'tauxRemise' => $ligne['tauxRemise'],
                        'quantite' => $ligne['quantite']
                        // Pas de libelleProduit, pas de id, pas de reference - juste idProduit et les prix
                    ];
                } else {
                    $lignesProduits[] = $ligne;
                }
            }
            
            // S'assurer que les données client sont correctement formatées pour l'API BDM
            $clientDataRaw = $commandeData['client'];
            
            // Si c'est un objet Client Laravel (converti en array), extraire seulement les champs nécessaires
            if (isset($clientDataRaw['attributes']) || isset($clientDataRaw['\u0000*\u0000connection']) || (is_array($clientDataRaw) && !isset($clientDataRaw['email']) && !isset($clientDataRaw['nom']))) {
                // C'est un objet Client Laravel converti en array, reformater proprement
                $user = Auth::guard('client')->check() ? Auth::guard('client')->user() : null;
                $guestEmail = $clientDataRaw['email'] ?? ($clientDataRaw['attributes']['email'] ?? null);
                $clientDataForBdm = $this->getClientData($user, $guestEmail);
                
                // Si getClientData retourne null, extraire manuellement depuis les attributes
                if (!$clientDataForBdm && isset($clientDataRaw['attributes'])) {
                    $attrs = $clientDataRaw['attributes'];
                    $clientDataForBdm = [
                        'email' => $attrs['email'] ?? null,
                        'nom' => $attrs['nom'] ?? null,
                        'prenom' => $attrs['prenom'] ?? null,
                        'telephone' => $attrs['telephone'] ?? null,
                        'adresse' => $attrs['adresse'] ?? null,
                        'complementAdresse' => $attrs['complementAdresse'] ?? null,
                        'ville' => $attrs['ville'] ?? null,
                        'codePostal' => $attrs['codePostal'] ?? null,
                        'pays' => $attrs['pays'] ?? null,
                        'is_guest' => false,
                    ];
                }
            } else {
                // Les données sont déjà dans le bon format, les utiliser telles quelles
                $clientDataForBdm = $clientDataRaw;
            }
            
            // L'API BDM attend les champs avec majuscules (Nom, Email, Prenom)
            // Créer un nouveau tableau avec les champs formatés correctement et sans accents (compatibilité BDM)
            $clientDataFormatted = [
                'Nom' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['nom'] ?? $clientDataForBdm['Nom'] ?? null),
                'Email' => $clientDataForBdm['email'] ?? $clientDataForBdm['Email'] ?? null,
                'Prenom' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['prenom'] ?? $clientDataForBdm['Prenom'] ?? null),
                'Telephone' => $clientDataForBdm['telephone'] ?? $clientDataForBdm['Telephone'] ?? null,
                'Adresse' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['adresse'] ?? $clientDataForBdm['Adresse'] ?? null),
                'ComplementAdresse' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['complementAdresse'] ?? $clientDataForBdm['ComplementAdresse'] ?? null),
                'Ville' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['ville'] ?? $clientDataForBdm['Ville'] ?? null),
                'CodePostal' => $clientDataForBdm['codePostal'] ?? $clientDataForBdm['CodePostal'] ?? null,
                'Pays' => $clientDataForBdm['pays'] ?? $clientDataForBdm['Pays'] ?? null,
                'Civilite' => $clientDataForBdm['civilite'] ?? $clientDataForBdm['Civilite'] ?? null,
                'NomSociete' => \App\Services\BdmApiService::sanitizeString($clientDataForBdm['nomSociete'] ?? $clientDataForBdm['NomSociete'] ?? null),
            ];
            
            // Retirer les valeurs null pour éviter d'envoyer des champs vides
            $clientDataFormatted = array_filter($clientDataFormatted, function($value) {
                return $value !== null && $value !== '';
            });
            
            // commandeInfos doit toujours contenir modeTransport, lieu, commentaires pour que l'ERP remonte les infos
            $commandeInfos = $commandeData['commandeInfos'] ?? [];
            $commandeInfos = array_merge(
                ['modeTransport' => '', 'lieu' => '', 'commentaires' => ''],
                is_array($commandeInfos) ? $commandeInfos : (array) $commandeInfos
            );

            // Nettoyage des infos de commande pour la compatibilité BDM
            $commandeInfos['modeTransport'] = \App\Services\BdmApiService::sanitizeString($commandeInfos['modeTransport']);
            $commandeInfos['lieu'] = \App\Services\BdmApiService::sanitizeString($commandeInfos['lieu']);
            $commandeInfos['commentaires'] = \App\Services\BdmApiService::sanitizeString($commandeInfos['commentaires']);

            $payload = [
                'commandeLignes' => $lignesProduits,
                'commandeOptions' => $lignesOptions,
                'client' => $clientDataFormatted,
                'commandeInfos' => $commandeInfos,
            ];

            Log::info('[paymentSuccess] Sending final creation request to BDM API.');
            
            // Log détaillé des prix envoyés pour débogage
            Log::info('[paymentSuccess] Prix envoyés à BDM:', [
                'lignes_produits' => array_map(function($ligne) {
                    return [
                        'id' => $ligne['idProduit'],
                        'libelle' => $ligne['libelleProduit'] ?? 'N/A',
                        'prixTTC' => $ligne['prixTTC'],
                        'prixTTCAvantRemise' => $ligne['prixTTCAvantRemise'],
                        'tauxRemise' => $ligne['tauxRemise'],
                        'quantite' => $ligne['quantite'],
                    ];
                }, $lignesProduits),
                'lignes_options' => array_map(function($ligne) {
                    return [
                        'id' => $ligne['idProduit'],
                        'libelle' => $ligne['libelleProduit'] ?? 'N/A',
                        'prixTTC' => $ligne['prixTTC'],
                        'prixTTCAvantRemise' => $ligne['prixTTCAvantRemise'],
                        'tauxRemise' => $ligne['tauxRemise'],
                    ];
                }, $lignesOptions),
                'total_attendu' => $commandeData['total_prix_ttc'],
            ]);

            $lang = Session::get('app_language', 'fr');
            $bdmResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json',
            ])->post(config('services.bdm.base_url') . "/api/plateforme/{$idPlateforme}/commande?lg={$lang}", $payload);

            Log::info('[paymentSuccess] BDM API response received.', [
                'status_code' => $bdmResponse->status(),
                'response_body' => $bdmResponse->body(),
                'monetico_transaction_id' => $moneticoTransactionId
            ]);

            $apiResult = $bdmResponse->json();

            // STEP 2: Handle BDM API response
            if ($bdmResponse->successful() && isset($apiResult['statut']) && $apiResult['statut'] === 1) {

                // BDM Success -> Capture Payment
                Log::info('[paymentSuccess] BDM order creation successful. Proceeding to capture payment.', [
                    'bdm_order_id' => $apiResult['message'],
                    'monetico_transaction_id' => $moneticoTransactionId,
                ]);

                Log::info('[paymentSuccess] Attempting to capture payment with Monetico.', [
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id'),
                    'capture_amount' => $commandeData['total_prix_ttc'] ?? 'N/A',
                ]);

                $captureResponse = $this->_moneticoCapturePayment($moneticoTransactionId);

                Log::info('[paymentSuccess] Monetico capture response received.', [
                    'status_code' => $captureResponse->status(),
                    'response_body' => $captureResponse->body(),
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id')
                ]);

                if (!$captureResponse->successful()) {
                    $captureBody = $captureResponse->body();
                    $captureJson = $captureResponse->json();
                    
                    Log::error('[paymentSuccess] CRITICAL: Monetico capture failed!', [
                        'monetico_transaction_id' => $moneticoTransactionId,
                        'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id'),
                        'status_code' => $captureResponse->status(),
                        'response_body' => $captureBody,
                        'response_json' => $captureJson,
                        'bdm_order_id' => $apiResult['message'],
                    ]);
                    
                    // CRITICAL: BDM order is created, but payment capture failed. Requires manual intervention.
                    throw new \Exception('CRITICAL: BDM order created but Monetico capture failed. Response: ' . $captureBody);
                }

                Log::info('[paymentSuccess] Monetico payment capture successful.', [
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'captured_amount' => $commandeData['total_prix_ttc'] ?? 'N/A',
                ]);

                // Proceed to save everything in local DB
                $clientData = $commandeData['client'];
                
                // Extraire les informations de carte depuis la réponse Monetico
                $cardInfo = $this->extractCardInfoFromMoneticoResponse($krAnswer, $request);
                
                // Si on n'a pas trouvé les données dans krAnswer, essayer de les récupérer via l'API Monetico
                if (!$cardInfo || (empty($cardInfo['last4']) && empty($cardInfo['type']))) {
                    Log::info('[paymentSuccess] Données de carte non trouvées dans krAnswer, tentative de récupération via API Monetico', [
                        'monetico_transaction_id' => $moneticoTransactionId,
                        'request_all' => $request->all()
                    ]);
                    
                    try {
                        $cardInfoFromAPI = $this->getCardInfoFromMoneticoAPI($moneticoTransactionId);
                        if ($cardInfoFromAPI && (!empty($cardInfoFromAPI['last4']) || !empty($cardInfoFromAPI['type']))) {
                            $cardInfo = $cardInfoFromAPI;
                            Log::info('[paymentSuccess] Données de carte récupérées via API Monetico', ['has_data' => true]);
                        }
                    } catch (\Exception $e) {
                        Log::error('[paymentSuccess] Erreur lors de la récupération des données via API Monetico', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Dernière tentative : extraire depuis raw_response (toute la requête)
                if (!$cardInfo || (empty($cardInfo['last4']) && empty($cardInfo['type']))) {
                    Log::info('[paymentSuccess] Tentative d\'extraction depuis raw_response complet', [
                        'request_all_keys' => array_keys($request->all())
                    ]);
                    $cardInfoFromRaw = $this->extractCardInfoFromRawRequest($request->all());
                    if ($cardInfoFromRaw && (!empty($cardInfoFromRaw['last4']) || !empty($cardInfoFromRaw['type']))) {
                        $cardInfo = $cardInfoFromRaw;
                        Log::info('[paymentSuccess] Données de carte récupérées depuis raw_response', ['has_data' => true]);
                    }
                }
                
                // Log final pour voir ce qu'on a réussi à extraire
                Log::info('[paymentSuccess] Résultat final de l\'extraction des données de carte', [
                    'cardInfo' => $cardInfo,
                    'has_cardInfo' => !empty($cardInfo),
                    'has_last4' => !empty($cardInfo['last4'] ?? null),
                    'has_type' => !empty($cardInfo['type'] ?? null),
                    'has_token' => !empty($cardInfo['token'] ?? null),
                ]);
                
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
                $authenticatedUser = null;
                
                if (!$isGuest && Auth::guard('client')->check()) {
                    $authenticatedUser = Auth::guard('client')->user();
                }
                
                // Sauvegarder les informations de carte si disponibles
                if ($cardInfo) {
                    $cardUpdateData = [
                        'carte_paiement_type' => $cardInfo['type'] ?? null,
                        'carte_paiement_last4' => $cardInfo['last4'] ?? null,
                        'carte_paiement_nom' => $cardInfo['holderName'] ?? null,
                        'carte_paiement_expiry' => $cardInfo['expiry'] ?? null,
                    ];
                    
                    // Sauvegarder le token Monetico si disponible (pour réutilisation future)
                    if (isset($cardInfo['token']) && !empty($cardInfo['token'])) {
                        $cardUpdateData['monetico_card_token'] = $cardInfo['token'];
                        Log::info('[paymentSuccess] Token Monetico trouvé et sera sauvegardé', [
                            'has_token' => true,
                            'token_length' => strlen($cardInfo['token']),
                            'is_guest' => $isGuest
                        ]);
                    }
                    
                    // Si l'utilisateur est connecté, mettre à jour directement son compte
                    if ($authenticatedUser) {
                        Log::info('[paymentSuccess] Mise à jour des informations de carte pour l\'utilisateur connecté', [
                            'client_id' => $authenticatedUser->id,
                            'cardUpdateData' => $cardUpdateData
                        ]);
                        $authenticatedUser->update($cardUpdateData);
                        $clientId = $authenticatedUser->id;
                    } else {
                        // Pour les invités, ajouter les données de carte au clientUpdateData
                        $clientUpdateData = array_merge($clientUpdateData, $cardUpdateData);
                    }
                }
                
                // Créer ou mettre à jour le client (pour les invités ou si pas encore créé)
                if (!$authenticatedUser) {
                    // For guest clients, ensure password_hash is null
                    $clientCreateData = $clientUpdateData;
                    if ($isGuest) {
                        $clientCreateData['password_hash'] = null;
                    }
                    
                    Log::info('[paymentSuccess] Creating/updating client', [
                        'is_guest' => $isGuest,
                        'has_password_hash' => isset($clientCreateData['password_hash']),
                    ]);
                    
                    $client = \App\Models\Client::updateOrCreate(
                        ['email' => $clientData['email']],
                        $clientCreateData
                    );
                    $clientId = $client->id;
                    
                    Log::info('[paymentSuccess] Client created/updated successfully', [
                        'client_id' => $clientId,
                    ]);
                }

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

                Log::info('[paymentSuccess] Commande créée avec succès', [
                    'commande_id' => $commande->id,
                    'total_prix_ttc' => $commandeData['total_prix_ttc'],
                    'contraintes_total' => $commandeData['contraintes_total'] ?? 0,
                    'commande_lignes_count' => count($commandeData['commandeLignes']),
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

                Log::info('[paymentSuccess] Calling Monetico Void due to BDM failure.', [
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'reason' => 'BDM order creation failed',
                    'bdm_error' => $errorMessage,
                ]);

                $voidResponse = $this->_moneticoVoidPayment($moneticoTransactionId);
                
                Log::info('[paymentSuccess] Void response after BDM failure.', [
                    'status_code' => $voidResponse->status(),
                    'response_body' => $voidResponse->body(),
                    'monetico_transaction_id' => $moneticoTransactionId,
                ]);

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
                'full_request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Attempt to void the payment as a safety net, if it hasn't been captured.
            // (If capture fails, the exception is thrown, so we land here)
            if (isset($moneticoTransactionId)) {
                Log::info('[paymentSuccess] Attempting to void payment after critical exception.', [
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'exception' => $e->getMessage(),
                ]);
                
                $voidResponse = $this->_moneticoVoidPayment($moneticoTransactionId);
                Log::info('[paymentSuccess] Void response after exception.', [
                    'status_code' => $voidResponse->status(),
                    'response_body' => $voidResponse->body(),
                    'monetico_transaction_id' => $moneticoTransactionId,
                    'monetico_order_id' => $krAnswer['orderDetails']['orderId'] ?? Session::get('monetico_order_id')
                ]);
                
                if (!$voidResponse->successful()) {
                    Log::error('[paymentSuccess] CRITICAL: Void operation failed!', [
                        'monetico_transaction_id' => $moneticoTransactionId,
                        'status_code' => $voidResponse->status(),
                        'response_body' => $voidResponse->body(),
                        'original_exception' => $e->getMessage(),
                    ]);
                }
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
        Log::error('Payment failed or was rejected by Monetico.');
        return redirect()->route('payment')->with('error', 'Votre paiement a été refusé par votre banque. Veuillez vérifier vos informations de paiement et réessayer.');
    }

    public function paymentCancel(Request $request)
    {
        Log::info('Payment was cancelled by the user.');
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
        // Store authenticated user data before resetting
        $authGuard = Auth::guard('client');
        $isAuthenticated = $authGuard->check();
        $authenticatedUserId = $isAuthenticated ? $authGuard->id() : null;
        $authenticatedUserData = null;
        
        if ($isAuthenticated && $authenticatedUserId) {
            $authenticatedUser = $authGuard->user();
            $authenticatedUserData = [
                'id' => $authenticatedUser->id,
                'email' => $authenticatedUser->email,
            ];
        }
        
        // Only clear booking-related session data, NOT authentication
        $keysToClear = [
            'formState',
            'booking_data',
            'guest_session',
            'commande_en_cours',
            'guest_customer_details',
            'airport_id',
            'service_id',
            'cart_items',
            'global_products_data',
            'global_lieux_data',
            'date_depot',
            'heure_depot',
            'date_recuperation',
            'heure_recuperation',
        ];
        
        $request->session()->forget($keysToClear);
        
        Log::info('Booking session data cleared for reset. Auth preserved: ' . ($isAuthenticated ? 'YES (user ID: ' . $authenticatedUserId . ')' : 'NO (guest)'));
        
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
        // Try primary endpoint first: /Charge/{transactionId}/Capture
        $url = config('monetico.base_url') . "/Charge/{$transactionId}/Capture";
        Log::info('Calling Monetico Capture API (primary endpoint).', ['url' => $url]);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url);

        // If the primary endpoint fails with INT_901, try the alternative endpoint
        if (!$response->successful()) {
            $responseData = $response->json();
            if (($responseData['answer']['errorCode'] ?? '') === 'INT_901') {
                Log::info('Primary Capture endpoint failed (INT_901), trying alternative endpoint...', [
                    'transaction_id' => $transactionId,
                ]);

                // Alternative endpoint: /Charge/Capture with transactionId in body
                $alternativeUrl = config('monetico.base_url') . "/Charge/Capture";
                Log::info('Calling Monetico Capture API (alternative endpoint).', ['url' => $alternativeUrl]);

                $alternativeResponse = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($alternativeUrl, [
                    'transactionId' => $transactionId,
                ]);

                return $alternativeResponse;
            }
        }

        return $response;
    }

    /**
     * Void a pre-authorized payment via Monetico API.
     * @param string $transactionId The transaction ID from the authorization.
     * @return \Illuminate\Http\Client\Response
     */
    private function _moneticoVoidPayment(string $transactionId)
    {
        // Standard Monetico/Lyra REST API V4 endpoint for cancellation is /Transaction/CancelOrRefund
        // Note: We use 'uuid' as the parameter name for the transaction ID
        $url = config('monetico.base_url') . "/Transaction/CancelOrRefund";
        Log::info('Calling Monetico Void API (CancelOrRefund).', ['url' => $url, 'transaction_id' => $transactionId]);

        $payload = [
            'uuid' => $transactionId
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url, $payload);

        $status = $response->status();
        $body = $response->json();
        $moneticoStatus = $body['status'] ?? 'UNKNOWN';

        Log::info('Monetico Void response received.', [
            'http_status' => $status,
            'monetico_status' => $moneticoStatus,
            'error_code' => $body['answer']['errorCode'] ?? null,
            'error_message' => $body['answer']['errorMessage'] ?? null
        ]);

        // If standard endpoint fails, try the alternative legacy endpoints
        if ($moneticoStatus !== 'SUCCESS') {
            $errorCode = $body['answer']['errorCode'] ?? '';
            
            // If the standard endpoint is not enabled (PSP_100) or not found (INT_901), try legacy Charge endpoint
            if ($errorCode === 'PSP_100' || $errorCode === 'INT_901' || $errorCode === 'INT_902') {
                Log::info('Standard Void endpoint failed or not enabled, trying legacy Charge endpoint...', [
                    'transaction_id' => $transactionId,
                    'reason' => $errorCode
                ]);
                
                $legacyUrl = config('monetico.base_url') . "/Charge/{$transactionId}/Void";
                $legacyResponse = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($legacyUrl);
                
                $legacyBody = $legacyResponse->json();
                if (($legacyBody['status'] ?? '') === 'SUCCESS') {
                    return $legacyResponse;
                }
                
                // Final attempt: PaymentMethod endpoint
                Log::info('Legacy Charge endpoint also failed, trying PaymentMethod endpoint...', [
                    'transaction_id' => $transactionId
                ]);
                
                $pmUrl = config('monetico.base_url') . "/PaymentMethod/{$transactionId}/Void";
                return Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($pmUrl);
            }
        }

        return $response;
    }

    /**
     * Extrait les informations de carte depuis raw_response (toute la requête)
     * @param array $rawRequest Toute la requête
     * @return array|null Les informations de carte ou null
     */
    private function extractCardInfoFromRawRequest($rawRequest)
    {
        $cardInfo = [];
        
        // Chercher dans tous les endroits possibles de rawRequest
        if (isset($rawRequest['kr-answer'])) {
            $krAnswerRaw = is_string($rawRequest['kr-answer']) ? json_decode($rawRequest['kr-answer'], true) : $rawRequest['kr-answer'];
            if ($krAnswerRaw) {
                return $this->extractCardInfoFromMoneticoResponse($krAnswerRaw, new \Illuminate\Http\Request($rawRequest));
            }
        }
        
        // Chercher directement dans les paramètres
        $brand = $rawRequest['brand'] ?? $rawRequest['cardBrand'] ?? $rawRequest['paymentMethodBrand'] ?? null;
        $last4 = $rawRequest['last4'] ?? $rawRequest['pan'] ?? $rawRequest['cardLast4'] ?? null;
        $holderName = $rawRequest['holderName'] ?? $rawRequest['cardHolderName'] ?? $rawRequest['cardholderName'] ?? null;
        $expiryMonth = $rawRequest['expiryMonth'] ?? $rawRequest['expMonth'] ?? null;
        $expiryYear = $rawRequest['expiryYear'] ?? $rawRequest['expYear'] ?? null;
        
        if ($brand) {
            $cardInfo['type'] = $this->normalizeCardType($brand);
        }
        if ($last4) {
            $cardInfo['last4'] = $last4;
        }
        if ($holderName) {
            $cardInfo['holderName'] = $holderName;
        }
        if ($expiryMonth && $expiryYear) {
            $month = str_pad((string)$expiryMonth, 2, '0', STR_PAD_LEFT);
            $year = substr((string)$expiryYear, -2);
            $cardInfo['expiry'] = $month . '/' . $year;
        }
        
        return !empty($cardInfo) ? $cardInfo : null;
    }
    
    /**
     * Récupère les informations de carte depuis l'API Monetico en utilisant le transactionId
     * @param string $transactionId L'ID de transaction Monetico
     * @return array|null Les informations de carte ou null
     */
    private function getCardInfoFromMoneticoAPI($transactionId)
    {
        try {
            $url = config('monetico.base_url') . "/Charge/{$transactionId}";
            Log::info('[getCardInfoFromMoneticoAPI] Appel API Monetico pour récupérer les données de carte', ['url' => $url]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode(config('monetico.login') . ':' . config('monetico.secret_key')),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info('[getCardInfoFromMoneticoAPI] Réponse API Monetico reçue', ['data' => $data]);
                
                // Extraire les données depuis la réponse API
                // Les données sont dans transactions[0].transactionDetails.cardDetails
                $cardInfo = [];
                
                if (isset($data['transactions']) && is_array($data['transactions']) && count($data['transactions']) > 0) {
                    $transaction = $data['transactions'][0];
                    
                    if (isset($transaction['transactionDetails']['cardDetails'])) {
                        $cardDetails = $transaction['transactionDetails']['cardDetails'];
                        
                        if (isset($cardDetails['effectiveBrand'])) {
                            $cardInfo['type'] = $this->normalizeCardType($cardDetails['effectiveBrand']);
                        }
                        if (isset($cardDetails['pan'])) {
                            $pan = $cardDetails['pan'];
                            if (preg_match('/(\d{4})$/', $pan, $matches)) {
                                $cardInfo['last4'] = $matches[1];
                            } else {
                                $cardInfo['last4'] = strlen($pan) >= 4 ? substr($pan, -4) : $pan;
                            }
                        } elseif (isset($cardDetails['cardHolderPan'])) {
                            $pan = $cardDetails['cardHolderPan'];
                            if (preg_match('/(\d{4})$/', $pan, $matches)) {
                                $cardInfo['last4'] = $matches[1];
                            }
                        }
                        if (isset($cardDetails['cardHolderName'])) {
                            $cardInfo['holderName'] = $cardDetails['cardHolderName'];
                        }
                        if (isset($cardDetails['expiryMonth']) && isset($cardDetails['expiryYear'])) {
                            $month = str_pad((string)$cardDetails['expiryMonth'], 2, '0', STR_PAD_LEFT);
                            $year = substr((string)$cardDetails['expiryYear'], -2);
                            $cardInfo['expiry'] = $month . '/' . $year;
                        } elseif (isset($cardDetails['cardHolderExpiryMonth']) && isset($cardDetails['cardHolderExpiryYear'])) {
                            $month = str_pad((string)$cardDetails['cardHolderExpiryMonth'], 2, '0', STR_PAD_LEFT);
                            $year = substr((string)$cardDetails['cardHolderExpiryYear'], -2);
                            $cardInfo['expiry'] = $month . '/' . $year;
                        }
                    }
                    
                    // Chercher le token
                    if (isset($transaction['paymentMethodToken']) && !empty($transaction['paymentMethodToken'])) {
                        $cardInfo['token'] = $transaction['paymentMethodToken'];
                    }
                }
                
                // Fallback: chercher dans paymentMethod si disponible
                if (empty($cardInfo) && isset($data['paymentMethod'])) {
                    $pm = $data['paymentMethod'];
                    if (isset($pm['brand'])) {
                        $cardInfo['type'] = $this->normalizeCardType($pm['brand']);
                    }
                    if (isset($pm['pan'])) {
                        $pan = $pm['pan'];
                        $cardInfo['last4'] = strlen($pan) >= 4 ? substr($pan, -4) : $pan;
                    } elseif (isset($pm['last4'])) {
                        $cardInfo['last4'] = $pm['last4'];
                    }
                    if (isset($pm['holderName'])) {
                        $cardInfo['holderName'] = $pm['holderName'];
                    }
                    if (isset($pm['expiryMonth']) && isset($pm['expiryYear'])) {
                        $month = str_pad((string)$pm['expiryMonth'], 2, '0', STR_PAD_LEFT);
                        $year = substr((string)$pm['expiryYear'], -2);
                        $cardInfo['expiry'] = $month . '/' . $year;
                    }
                    if (isset($pm['token'])) {
                        $cardInfo['token'] = $pm['token'];
                    } elseif (isset($pm['alias'])) {
                        $cardInfo['token'] = $pm['alias'];
                    }
                }
                
                return !empty($cardInfo) ? $cardInfo : null;
            } else {
                Log::error('[getCardInfoFromMoneticoAPI] Erreur API Monetico', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[getCardInfoFromMoneticoAPI] Exception', ['error' => $e->getMessage()]);
        }
        
        return null;
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
        
        // LOG COMPLET pour debug - voir toute la structure
        Log::info('[extractCardInfoFromMoneticoResponse] DEBUG - Structure complète de la réponse', [
            'request_all' => $request->all(),
            'krAnswer_structure' => $krAnswer,
            'krAnswer_keys' => $krAnswer ? array_keys($krAnswer) : [],
            'has_paymentMethod' => isset($krAnswer['paymentMethod']),
            'paymentMethod_structure' => $krAnswer['paymentMethod'] ?? null,
            'has_transactions' => isset($krAnswer['transactions']),
            'transactions_count' => isset($krAnswer['transactions']) ? count($krAnswer['transactions']) : 0,
        ]);
        
        // Méthode 1: Chercher dans la requête directement (Monetico peut passer les données en paramètres GET/POST)
        $brand = $request->input('brand') ?? $request->input('cardBrand') ?? $request->input('paymentMethodBrand');
        $last4 = $request->input('last4') ?? $request->input('pan') ?? $request->input('cardLast4');
        $holderName = $request->input('holderName') ?? $request->input('cardHolderName') ?? $request->input('cardholderName');
        $expiryMonth = $request->input('expiryMonth') ?? $request->input('expMonth');
        $expiryYear = $request->input('expiryYear') ?? $request->input('expYear');
        
        // Méthode 2: Chercher dans krAnswer['paymentMethod']
        if (isset($krAnswer['paymentMethod'])) {
            $pm = $krAnswer['paymentMethod'];
            
            if (!$brand && isset($pm['brand'])) {
                $brand = $pm['brand'];
            }
            if (!$last4) {
                if (isset($pm['pan'])) {
                    $pan = $pm['pan'];
                    $last4 = strlen($pan) >= 4 ? substr($pan, -4) : $pan;
                } elseif (isset($pm['last4'])) {
                    $last4 = $pm['last4'];
                } elseif (isset($pm['cardNumber'])) {
                    $cardNum = $pm['cardNumber'];
                    $last4 = strlen($cardNum) >= 4 ? substr($cardNum, -4) : $cardNum;
                }
            }
            if (!$holderName && isset($pm['holderName'])) {
                $holderName = $pm['holderName'];
            }
            if (!$expiryMonth && isset($pm['expiryMonth'])) {
                $expiryMonth = $pm['expiryMonth'];
            }
            if (!$expiryYear && isset($pm['expiryYear'])) {
                $expiryYear = $pm['expiryYear'];
            }
            
            // Chercher le token dans paymentMethod
            if (isset($pm['token'])) {
                $cardInfo['token'] = $pm['token'];
            } elseif (isset($pm['alias'])) {
                $cardInfo['token'] = $pm['alias'];
            } elseif (isset($pm['paymentMethodToken'])) {
                $cardInfo['token'] = $pm['paymentMethodToken'];
            } elseif (isset($pm['cardToken'])) {
                $cardInfo['token'] = $pm['cardToken'];
            }
        }
        
        // Méthode 3: Chercher dans les transactions (première transaction) - C'EST ICI QUE SONT LES DONNÉES !
        if (isset($krAnswer['transactions']) && is_array($krAnswer['transactions']) && count($krAnswer['transactions']) > 0) {
            $transaction = $krAnswer['transactions'][0];
            
            // Les données sont dans transactionDetails.cardDetails (pas dans paymentMethod)
            if (isset($transaction['transactionDetails']['cardDetails'])) {
                $cardDetails = $transaction['transactionDetails']['cardDetails'];
                
                if (!$brand && isset($cardDetails['effectiveBrand'])) {
                    $brand = $cardDetails['effectiveBrand'];
                }
                if (!$last4) {
                    if (isset($cardDetails['pan'])) {
                        $pan = $cardDetails['pan'];
                        // Extraire les 4 derniers chiffres depuis "497011XXXXXX1003"
                        if (preg_match('/(\d{4})$/', $pan, $matches)) {
                            $last4 = $matches[1];
                        } else {
                            $last4 = strlen($pan) >= 4 ? substr($pan, -4) : $pan;
                        }
                    } elseif (isset($cardDetails['cardHolderPan'])) {
                        $pan = $cardDetails['cardHolderPan'];
                        if (preg_match('/(\d{4})$/', $pan, $matches)) {
                            $last4 = $matches[1];
                        } else {
                            $last4 = strlen($pan) >= 4 ? substr($pan, -4) : $pan;
                        }
                    }
                }
                if (!$holderName && isset($cardDetails['cardHolderName'])) {
                    $holderName = $cardDetails['cardHolderName'];
                }
                if (!$expiryMonth) {
                    if (isset($cardDetails['expiryMonth'])) {
                        $expiryMonth = $cardDetails['expiryMonth'];
                    } elseif (isset($cardDetails['cardHolderExpiryMonth'])) {
                        $expiryMonth = $cardDetails['cardHolderExpiryMonth'];
                    }
                }
                if (!$expiryYear) {
                    if (isset($cardDetails['expiryYear'])) {
                        $expiryYear = $cardDetails['expiryYear'];
                    } elseif (isset($cardDetails['cardHolderExpiryYear'])) {
                        $expiryYear = $cardDetails['cardHolderExpiryYear'];
                    }
                }
            }
            
            // Chercher aussi dans paymentMethodDetails si disponible
            if (isset($transaction['transactionDetails']['paymentMethodDetails'])) {
                $pmd = $transaction['transactionDetails']['paymentMethodDetails'];
                
                if (!$brand && isset($pmd['effectiveBrand'])) {
                    $brand = $pmd['effectiveBrand'];
                }
                if (!$last4 && isset($pmd['id'])) {
                    $pan = $pmd['id'];
                    if (preg_match('/(\d{4})$/', $pan, $matches)) {
                        $last4 = $matches[1];
                    }
                }
                if (!$expiryMonth && isset($pmd['expiryMonth'])) {
                    $expiryMonth = $pmd['expiryMonth'];
                }
                if (!$expiryYear && isset($pmd['expiryYear'])) {
                    $expiryYear = $pmd['expiryYear'];
                }
            }
            
            // Chercher le token dans la transaction
            if (!isset($cardInfo['token'])) {
                if (isset($transaction['paymentMethodToken']) && !empty($transaction['paymentMethodToken'])) {
                    $cardInfo['token'] = $transaction['paymentMethodToken'];
                } elseif (isset($transaction['token'])) {
                    $cardInfo['token'] = $transaction['token'];
                }
            }
        }
        
        // Méthode 4: Chercher à la racine de krAnswer
        if (!$brand && isset($krAnswer['brand'])) {
            $brand = $krAnswer['brand'];
        }
        if (!isset($cardInfo['token'])) {
            if (isset($krAnswer['token'])) {
                $cardInfo['token'] = $krAnswer['token'];
            } elseif (isset($krAnswer['alias'])) {
                $cardInfo['token'] = $krAnswer['alias'];
            } elseif (isset($krAnswer['paymentMethodToken'])) {
                $cardInfo['token'] = $krAnswer['paymentMethodToken'];
            }
        }
        
        // Construire le tableau cardInfo avec les valeurs trouvées
        if ($brand) {
            $cardInfo['type'] = $this->normalizeCardType($brand);
        }
        
        if ($last4) {
            $cardInfo['last4'] = $last4;
        }
        
        if ($holderName) {
            $cardInfo['holderName'] = $holderName;
        }
        
        if ($expiryMonth && $expiryYear) {
            $month = str_pad((string)$expiryMonth, 2, '0', STR_PAD_LEFT);
            $year = substr((string)$expiryYear, -2); // Prendre les 2 derniers chiffres
            $cardInfo['expiry'] = $month . '/' . $year;
        }
        
        // Log final pour debug
        Log::info('[extractCardInfoFromMoneticoResponse] Résultat de l\'extraction', [
            'cardInfo' => $cardInfo,
            'found_brand' => !empty($brand),
            'found_last4' => !empty($last4),
            'found_holderName' => !empty($holderName),
            'found_expiry' => !empty($expiryMonth) && !empty($expiryYear),
            'found_token' => isset($cardInfo['token']),
        ]);
        
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

        // Si c'est une erreur de validation du Nom ou Prénom
        if (strpos(strtolower($errorMessage), 'nom') !== false || 
            strpos(strtolower($errorMessage), 'prenom') !== false ||
            (is_array($apiResult) && isset($apiResult['errors']) && 
             (isset($apiResult['errors']['Client.Nom']) || isset($apiResult['errors']['Client.Prenom'])))) {
            return "Le nom ou le prénom saisi contient des caractères non autorisés. Veuillez utiliser uniquement des lettres et réessayer.";
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