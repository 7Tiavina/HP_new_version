<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // Add this import

class BdmApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.bdm.base_url');
    }

    /**
     * Get the base URL for the BDM API
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Récupère un token d'authentification pour l'API BDM, en le mettant en cache.
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAuthToken(): string
    {
        // Tente de récupérer le token depuis le cache
        return Cache::remember('bdm_api_token', 3300, function () {
            Log::info('Cache BDM token expiré. Demande d\'un nouveau token.');

            $response = Http::post($this->baseUrl . '/User/Login', [
                'username' => config('services.bdm.username'),
                'password' => config('services.bdm.password'),
            ]);

            // Lance une exception si la requête HTTP elle-même échoue
            $response->throw();

            // Vérifie si le login a réussi selon la réponse de l'API
            if (!$response->json('isSucceed')) {
                Log::error('L\'API BDM a refusé la connexion.', ['response' => $response->json()]);
                throw new \Exception('Authentification API BDM échouée: L\'API a refusé la connexion.');
            }

            $token = $response->json('data.accessToken');

            if (!$token) {
                Log::error('Impossible de récupérer l\'accessToken depuis la réponse de l\'API BDM.', ['response' => $response->json()]);
                throw new \Exception('Authentification API BDM échouée: token manquant dans la réponse.');
            }

            Log::info('✅ AUTHENTIFICATION API BDM RÉUSSIE. Token obtenu.');
            Log::info('Nouveau token BDM obtenu et mis en cache.');
            return $token;
        });
    }

    /**
     * Récupère toutes les plateformes (aéroports) depuis l'API BDM pour le service de consigne.
     * @return array
     * @throws \Exception
     */
    public function getPlateformes(): ?array
    {
        $serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7'; // ID du service de consigne
        $url = "{$this->baseUrl}/api/service/{$serviceId}/plateformes";
        Log::info("BDM plateformes : GET " . $url, ['base_url_set' => !empty($this->baseUrl)]);

        try {
            $token = $this->getAuthToken();
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(15)
                ->get($url);

            $status = $response->status();
            $json = $response->json();

            // Diagnostic : toujours logger la structure reçue (pas de fallback)
            if ($json === null) {
                Log::error("BDM plateformes : JSON invalide ou vide.", [
                    'http_status' => $status,
                    'body_preview' => strlen($response->body()) > 0 ? substr($response->body(), 0, 500) : '(vide)'
                ]);
                return ['statut' => 0, 'message' => 'Réponse API invalide', 'content' => []];
            }

            $isRootArray = is_array($json) && isset($json[0]) && !isset($json['statut']) && !isset($json['content']);
            $keys = is_array($json) && !$isRootArray ? array_keys($json) : (is_array($json) ? ['root_array_count' => count($json)] : gettype($json));
            Log::info("BDM plateformes : réponse reçue.", [
                'http_status' => $status,
                'response_type' => $isRootArray ? 'root_array' : 'object',
                'keys_or_count' => $keys,
                'content_count' => isset($json['content']) && is_array($json['content']) ? count($json['content']) : null
            ]);

            if (!$response->successful()) {
                Log::error("BDM plateformes : HTTP non 2xx.", ['status' => $status, 'body' => substr($response->body(), 0, 300)]);
                $response->throw();
            }
            return $json;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("BDM plateformes : connexion impossible.", ['url' => $url, 'error' => $e->getMessage()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("BDM plateformes : erreur.", ['url' => $url, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Récupère les produits (types de bagages) pour une plateforme donnée avec une durée par défaut.
     * @param string $idPlateforme
     * @return array
     * @throws \Exception
     */
    public function getProducts(string $idPlateforme): ?array
    {
        $serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7'; // ID du service de consigne
        $defaultDuration = 1; // Durée par défaut en minutes pour obtenir la liste
        Log::info("Récupération des produits pour la plateforme {$idPlateforme} et le service {$serviceId}.");
        try {
            $token = $this->getAuthToken();
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/api/plateforme/{$idPlateforme}/service/{$serviceId}/{$defaultDuration}/produits");

            $response->throw();

            return $response->json(); // Return full JSON response

        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des produits BDM.", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Vérifie la disponibilité d'une plateforme à une date donnée.
     * @param string $idPlateforme
     * @param string $dateToCheck
     * @return array|null
     * @throws \Exception
     */
    public function checkAvailability(string $idPlateforme, string $dateToCheck): ?array
    {
        Log::info('Appel à l\'API BDM pour la disponibilité', ['idPlateforme' => $idPlateforme, 'dateToCheck' => $dateToCheck]);

        try {
            Log::info('Tentative de récupération du token d\'authentification');
            $token = $this->getAuthToken();
            Log::info('Token obtenu avec succès', ['token_length' => strlen($token)]);

            // Le format yyyyMMddTHHmm n'a pas de caractères spéciaux, pas besoin d'encoder
            Log::info('Appel à l\'API BDM avec le token', [
                'url' => "{$this->baseUrl}/api/plateforme/{$idPlateforme}/date/{$dateToCheck}",
                'dateToCheck' => $dateToCheck
            ]);
            
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$this->baseUrl}/api/plateforme/{$idPlateforme}/date/{$dateToCheck}");

            Log::info('Réponse reçue de l\'API BDM', [
                'status' => $response->status(), 
                'body' => $response->json(),
                'url_called' => "{$this->baseUrl}/api/plateforme/{$idPlateforme}/date/{$dateToCheck}"
            ]);

            $response->throw(); // Lance une exception pour les erreurs HTTP

            return $response->json();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('RequestException lors de la vérification de la disponibilité', [
                'error' => $e->getMessage(),
                'status' => $e->response->status() ?? null,
                'body' => $e->response->body() ?? null
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Exception générale lors de la vérification de la disponibilité', [
                'error' => $e->getMessage(),
                'class' => get_class($e)
            ]);
            throw $e;
        }
    }

    /**
     * Récupère les tarifs (produits) et les lieux pour une plateforme, un service et une durée donnés.
     * @param string $idPlateforme
     * @param string $idService
     * @param int $duree
     * @return array|null
     * @throws \Exception
     */
    public function getQuote(string $idPlateforme, string $idService, int $duree): ?array
    {
        Log::info('Appel à l\'API BDM pour les tarifs et lieux', ['idPlateforme' => $idPlateforme, 'idService' => $idService, 'duree' => $duree]);

        try {
            $token = $this->getAuthToken();
            $baseUrl = $this->baseUrl;

            $responses = Http::pool(fn ($pool) => [
                $pool->withToken($token)->withHeaders(['Accept' => 'application/json'])->get("{$baseUrl}/api/plateforme/{$idPlateforme}/service/{$idService}/{$duree}/produits"),
                $pool->withToken($token)->withHeaders(['Accept' => 'application/json'])->get("{$baseUrl}/api/plateforme/{$idPlateforme}/lieux"),
            ]);

            $productsResponse = $responses[0];
            $lieuxResponse = $responses[1];

            // Vérifier si l'un ou l'autre des appels a échoué au niveau HTTP
            if ($productsResponse->failed() || $lieuxResponse->failed()) {
                Log::error("Échec d'au moins un appel API BDM dans le pool getQuote.", [
                    'products_status' => $productsResponse->status(),
                    'products_body' => $productsResponse->body(),
                    'lieux_status' => $lieuxResponse->status(),
                    'lieux_body' => $lieuxResponse->body(),
                ]);
                throw new \Exception('Erreur lors de la communication avec le service de réservation.');
            }

            $productsResult = $productsResponse->json();
            $lieuxResult = $lieuxResponse->json();
            
            // Log pour déboguer les lieux
            Log::info('getQuote - Lieux reçus de BDM:', [
                'statut' => $lieuxResult['statut'] ?? 'unknown',
                'count' => count($lieuxResult['content'] ?? []),
                'lieux' => $lieuxResult['content'] ?? []
            ]);

            // Vérifier si le statut interne de l'API BDM indique un échec
            if (($productsResult['statut'] ?? 0) !== 1 || ($lieuxResult['statut'] ?? 0) !== 1) {
                Log::error("Réponse API BDM avec un statut d'échec dans le pool getQuote.", [
                    'products_response' => $productsResult,
                    'lieux_response' => $lieuxResult,
                ]);
                throw new \Exception("Les données de réservation n'ont pas pu être chargées entièrement.");
            }

            // Si tout réussit, on construit la réponse
            return [
                'statut' => 1,
                'message' => 'Données récupérées',
                'content' => [
                    'products' => $productsResult['content'] ?? [],
                    'lieux' => $lieuxResult['content'] ?? [],
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tarifs/lieux via BDM API', ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Récupère la liste des produits de contraintes de prestations complémentaires liées à la commande.
     *
     * @param string $idPlateforme L'ID de la plateforme (aéroport).
     * @param array $commandeLignes Les lignes de commande pour les bagages.
     * @param array $commandeOptions Les options sélectionnées (ex: Priority, Premium).
     * @param array $commandeInfos Informations sur la commande (modeTransport, lieu, commentaires).
     * @param array $client Données du client.
     * @return array|null La liste des contraintes ou null en cas d'erreur.
     */
    public function getCommandeContraintes(
        string $idPlateforme,
        array $commandeLignes,
        array $commandeOptions = [],
        array $commandeInfos = [],
        array $client = []
    ): ?array
    {
        $url = "{$this->baseUrl}/api/plateforme/{$idPlateforme}/commande/contraintes";

        // Valeurs par défaut si non fournies
        if (empty($client)) {
            $client = [
                "email" => "temp@hellopassenger.com",
                "telephone" => "0000000000",
                "nom" => "Passager",
                "prenom" => "Temp",
                "civilite" => "M.",
                "nomSociete" => "",
                "adresse" => "Adresse inconnue",
                "complementAdresse" => "",
                "ville" => "Ville inconnue",
                "codePostal" => "00000",
                "pays" => "FRA"
            ];
        }

        if (empty($commandeInfos)) {
            $commandeInfos = [
                "modeTransport" => "Inconnu",
                "lieu" => "Inconnu",
                "commentaires" => "Demande de contraintes"
            ];
        }

        $payload = [
            "commandeLignes" => $commandeLignes,
            "commandeOptions" => $commandeOptions,
            "commandeInfos" => $commandeInfos,
            "client" => $client
        ];

        Log::info('BdmApiService::getCommandeContraintes - Payload envoyé', ['payload' => $payload]);

        try {
            $token = $this->getAuthToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('BdmApiService::getCommandeContraintes - Réponse de l\'API BDM', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Erreur API BDM lors de la récupération des contraintes', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'appel API BDM pour les contraintes', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Effectue une requête POST à l'API BDM pour obtenir les prix des options.
     *
     * @param string $idPlateforme L'ID de la plateforme (aéroport).
     * @param array $baggages Les lignes de commande pour les bagages.
     * @param array $options Les options à évaluer (ex: Priority, Premium).
     * @param string $guestEmail L'email de l'invité, si disponible.
     * @return array|null Les prix des options ou null en cas d'erreur.
     */
    public function getCommandeOptionsQuote(string $idPlateforme, array $baggages, ?string $guestEmail = null, ?array $premiumDetails = null): ?array
    {
        $url = "{$this->baseUrl}/api/plateforme/{$idPlateforme}/commande/options?lg=fr";

        // Construire commandeLignes à partir des baggages
        $commandeLignes = array_map(function($baggage) {
            return [
                "idProduit" => $baggage['idProduit'] ?? $baggage['productId'] ?? null,
                "idService" => $baggage['idService'] ?? $baggage['serviceId'] ?? "dfb8ac1b-8bb1-4957-afb4-1faedaf641b7",
                "dateDebut" => $baggage['dateDebut'] ?? $baggage['dateDebut'],
                "dateFin" => $baggage['dateFin'] ?? $baggage['dateFin'],
                "prixTTC" => 0,
                "prixTTCAvantRemise" => 0,
                "tauxRemise" => 0,
                "quantite" => $baggage['quantite'] ?? $baggage['quantity'] ?? 1
            ];
        }, $baggages);

        // Envoyer un tableau vide pour `commandeOptions` pour découvrir les options disponibles
        $commandeOptions = [];

        // Construction du champ commentaires pour l'API BDM
        $commentairesForBdm = "Devis options"; // Valeur par défaut

        if ($premiumDetails) {
            $commentParts = [];

            // Gérer le cas où direction === 'both' (nouveau format)
            if (isset($premiumDetails['direction']) && $premiumDetails['direction'] === 'both') {
                $commentParts[] = "Service Premium complet (Arrivée + Départ)";
                
                // Informations d'ARRIVÉE
                $commentParts[] = "\n--- ARRIVÉE ---";
                if (isset($premiumDetails['transport_type_arrival'])) {
                    $transport = [
                        'airport' => 'Aéroport',
                        'public_transport' => 'Transport en commun',
                        'train' => 'Train',
                        'other' => 'Autre',
                        'flight' => 'Avion'
                    ][$premiumDetails['transport_type_arrival']] ?? $premiumDetails['transport_type_arrival'];
                    $commentParts[] = "Moyen de transport: {$transport}";
                }
                // Add flight number only if airport is selected
                if (isset($premiumDetails['transport_type_arrival']) && $premiumDetails['transport_type_arrival'] === 'airport' && isset($premiumDetails['flight_number_arrival'])) {
                    $commentParts[] = "Numéro de vol d'arrivée: {$premiumDetails['flight_number_arrival']}";
                }
                // Add train number only if train is selected
                if (isset($premiumDetails['transport_type_arrival']) && $premiumDetails['transport_type_arrival'] === 'train' && isset($premiumDetails['train_number_arrival'])) {
                    $commentParts[] = "Indicatif de ligne d'arrivée: {$premiumDetails['train_number_arrival']}";
                }
                if (isset($premiumDetails['date_arrival'])) $commentParts[] = "Date d'arrivée: {$premiumDetails['date_arrival']}";
                // Send both ID and libelle - ID for API, libelle for display
                if (isset($premiumDetails['pickup_location_arrival'])) {
                    $commentParts[] = "Lieu de prise en charge (ID): {$premiumDetails['pickup_location_arrival']}";
                }
                if (isset($premiumDetails['pickup_location_arrival_libelle'])) {
                    $commentParts[] = "Lieu de prise en charge: {$premiumDetails['pickup_location_arrival_libelle']}";
                }
                if (isset($premiumDetails['pickup_time_arrival'])) $commentParts[] = "Heure de prise en charge: {$premiumDetails['pickup_time_arrival']}";
                if (isset($premiumDetails['instructions_arrival'])) $commentParts[] = "Informations complémentaires: {$premiumDetails['instructions_arrival']}";

                // Informations de DÉPART
                $commentParts[] = "\n--- DÉPART ---";
                if (isset($premiumDetails['transport_type_departure'])) {
                    $transport = [
                        'airport' => 'Aéroport',
                        'public_transport' => 'Transport en commun',
                        'train' => 'Train',
                        'other' => 'Autre',
                        'flight' => 'Avion'
                    ][$premiumDetails['transport_type_departure']] ?? $premiumDetails['transport_type_departure'];
                    $commentParts[] = "Moyen de transport: {$transport}";
                }
                // Add flight number only if airport is selected
                if (isset($premiumDetails['transport_type_departure']) && $premiumDetails['transport_type_departure'] === 'airport' && isset($premiumDetails['flight_number_departure'])) {
                    $commentParts[] = "Numéro de vol de départ: {$premiumDetails['flight_number_departure']}";
                }
                // Add train number only if train is selected
                if (isset($premiumDetails['transport_type_departure']) && $premiumDetails['transport_type_departure'] === 'train' && isset($premiumDetails['train_number_departure'])) {
                    $commentParts[] = "Indicatif de ligne de départ: {$premiumDetails['train_number_departure']}";
                }
                if (isset($premiumDetails['date_departure'])) $commentParts[] = "Date de départ: {$premiumDetails['date_departure']}";
                // Send both ID and libelle - ID for API, libelle for display
                if (isset($premiumDetails['restitution_location_departure'])) {
                    $commentParts[] = "Lieu de restitution (ID): {$premiumDetails['restitution_location_departure']}";
                }
                if (isset($premiumDetails['restitution_location_departure_libelle'])) {
                    $commentParts[] = "Lieu de restitution: {$premiumDetails['restitution_location_departure_libelle']}";
                }
                if (isset($premiumDetails['restitution_time_departure'])) $commentParts[] = "Heure de restitution: {$premiumDetails['restitution_time_departure']}";
                if (isset($premiumDetails['instructions_departure'])) $commentParts[] = "Informations complémentaires: {$premiumDetails['instructions_departure']}";
                
            } else if (isset($premiumDetails['direction'])) {
                // Ancien format (compatibilité)
                if ($premiumDetails['direction'] === 'terminal_to_agence') {
                    $commentParts[] = "Type de service: Récupération de vos bagages";
                    if (isset($premiumDetails['flight_number_arrival'])) $commentParts[] = "Numéro de vol d'arrivée: {$premiumDetails['flight_number_arrival']}";
                    if (isset($premiumDetails['date_arrival'])) $commentParts[] = "Date d'arrivée: {$premiumDetails['date_arrival']}";
                    if (isset($premiumDetails['time_arrival'])) $commentParts[] = "Heure d'arrivée: {$premiumDetails['time_arrival']}";
                    if (isset($premiumDetails['pickup_location_arrival_libelle'])) $commentParts[] = "Lieu de prise en charge: {$premiumDetails['pickup_location_arrival_libelle']}";
                    else if (isset($premiumDetails['pickup_location_arrival'])) $commentParts[] = "Lieu de prise en charge (ID): {$premiumDetails['pickup_location_arrival']}";
                    if (isset($premiumDetails['pickup_time_arrival'])) $commentParts[] = "Heure de prise en charge: {$premiumDetails['pickup_time_arrival']}";
                    if (isset($premiumDetails['instructions_arrival'])) $commentParts[] = "Informations complémentaires: {$premiumDetails['instructions_arrival']}";
                } else if ($premiumDetails['direction'] === 'agence_to_terminal') {
                    $commentParts[] = "Type de service: Restitution de vos bagages";
                    if (isset($premiumDetails['flight_number_departure'])) $commentParts[] = "Numéro de vol de départ: {$premiumDetails['flight_number_departure']}";
                    if (isset($premiumDetails['date_departure'])) $commentParts[] = "Date de départ: {$premiumDetails['date_departure']}";
                    if (isset($premiumDetails['time_departure'])) $commentParts[] = "Heure de départ: {$premiumDetails['time_departure']}";
                    if (isset($premiumDetails['restitution_location_departure_libelle'])) $commentParts[] = "Lieu de restitution: {$premiumDetails['restitution_location_departure_libelle']}";
                    else if (isset($premiumDetails['restitution_location_departure'])) $commentParts[] = "Lieu de restitution (ID): {$premiumDetails['restitution_location_departure']}";
                    if (isset($premiumDetails['restitution_time_departure'])) $commentParts[] = "Heure de restitution: {$premiumDetails['restitution_time_departure']}";
                    if (isset($premiumDetails['instructions_departure'])) $commentParts[] = "Informations complémentaires: {$premiumDetails['instructions_departure']}";
                }
            }
            
            if (!empty($commentParts)) {
                $commentairesForBdm = implode('; ', $commentParts);
            }
        }

        // Données client minimales pour la requête de devis d'options
        $clientData = [
            "email" => $guestEmail ?? "temp@hellopassenger.com",
            "telephone" => "0000000000",
            "nom" => "Passager",
            "prenom" => "Temp",
            "civilite" => "M.",
            "nomSociete" => "",
            "adresse" => "Adresse inconnue",
            "complementAdresse" => "",
            "ville" => "Ville inconnue",
            "codePostal" => "00000",
            "pays" => "FRA"
        ];
        
        if (!filter_var($clientData['email'], FILTER_VALIDATE_EMAIL)) {
            $clientData['email'] = "temp@hellopassenger.com";
        }


        $payload = [
            "commandeLignes" => $commandeLignes,
            "commandeOptions" => $commandeOptions, // Envoyer un tableau vide
            "commandeInfos" => [
                "modeTransport" => "Inconnu",
                "lieu" => "Inconnu",
                "commentaires" => $commentairesForBdm // Utilise le commentaire construit
            ],
            "client" => $clientData
        ];

        Log::info('BdmApiService::getCommandeOptionsQuote - Payload envoyé pour découvrir les options', ['payload' => $payload]);

        try {
            $token = $this->getAuthToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('BdmApiService::getCommandeOptionsQuote - Réponse brute de l\'API BDM pour la découverte d\'options', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Erreur API BDM lors de la découverte des prix des options', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'appel API BDM pour la découverte des prix des options', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}