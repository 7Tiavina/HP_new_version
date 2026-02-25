<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OneMinAiService
{
    private $apiKey;
    private $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.onemin_ai.api_key');
        $this->baseUrl = config('services.onemin_ai.base_url', 'https://api.1min.ai');
    }
    
    /**
     * Envoie un message à l'API Conversation de 1min.ai
     * 
     * @param string $message Le message de l'utilisateur
     * @param array $conversationHistory L'historique de la conversation (optionnel)
     * @return array|null La réponse de l'API ou null en cas d'erreur
     */
    public function sendMessage(string $message, array $conversationHistory = [])
    {
        if (!$this->apiKey) {
            Log::warning('[OneMinAiService] API key not configured', [
                'config_key' => config('services.onemin_ai.api_key'),
                'env_key_set' => !empty(env('ONEMIN_AI_API_KEY'))
            ]);
            return null;
        }
        
        Log::info('[OneMinAiService] API key found, proceeding with request', [
            'api_key_length' => strlen($this->apiKey),
            'base_url' => $this->baseUrl
        ]);
        
        try {
            // Construire le contexte avec les informations du site HelloPassenger
            $siteContext = $this->getSiteContext();
            
            // Construire le prompt avec le contexte
            $fullPrompt = $siteContext . "\n\nQuestion du client : " . $message;
            
            // Construire le promptObject selon la documentation 1min.ai
            $promptObject = [
                'prompt' => $fullPrompt,
                'isMixed' => false,
                'webSearch' => false, // Désactiver la recherche web par défaut pour plus de rapidité
            ];
            
            // Construire le payload selon la documentation
            // Endpoint: POST https://api.1min.ai/api/features
            $payload = [
                'type' => 'CHAT_WITH_AI',
                'model' => 'gpt-4o-mini', // Modèle par défaut, peut être configuré
                'promptObject' => $promptObject,
            ];
            
            // Si on a un historique de conversation, on peut l'utiliser
            // Note: Pour CHAT_WITH_AI, conversationId est optionnel
            // On pourrait créer un conversationId unique par session si nécessaire
            
            Log::info('[OneMinAiService] Sending message to 1min.ai API', [
                'message_length' => strlen($message),
                'has_history' => !empty($conversationHistory),
                'endpoint' => $this->baseUrl . '/api/features'
            ]);
            
            // Appel à l'API 1min.ai (non-streaming pour commencer)
            $response = Http::timeout(60) // Timeout plus long pour les réponses AI
                ->withHeaders([
                    'API-KEY' => $this->apiKey, // Utiliser API-KEY au lieu de Authorization Bearer
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/features', $payload);
            
            if (!$response->successful()) {
                Log::error('[OneMinAiService] API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $this->baseUrl . '/api/features'
                ]);
                return null;
            }
            
            $data = $response->json();
            
            Log::info('[OneMinAiService] Response received from 1min.ai API', [
                'status' => $response->status(),
                'has_aiRecord' => isset($data['aiRecord']),
                'has_resultObject' => isset($data['aiRecord']['aiRecordDetail']['resultObject']),
                'response_keys' => array_keys($data),
            ]);
            
            // Extraire la réponse selon le format de l'API 1min.ai
            // La réponse est dans: aiRecord.aiRecordDetail.resultObject
            $aiResponse = null;
            
            if (isset($data['aiRecord']['aiRecordDetail']['resultObject'])) {
                $resultObject = $data['aiRecord']['aiRecordDetail']['resultObject'];
                
                // resultObject peut être un array ou une string
                if (is_array($resultObject) && count($resultObject) > 0) {
                    // Si c'est un array, prendre le premier élément
                    $aiResponse = $resultObject[0];
                } elseif (is_string($resultObject)) {
                    $aiResponse = $resultObject;
                }
            }
            
            // Fallback: chercher dans d'autres champs possibles
            if (!$aiResponse) {
                // Peut-être que la réponse est directement dans resultObject comme string
                if (isset($data['resultObject'])) {
                    $aiResponse = is_array($data['resultObject']) ? implode(' ', $data['resultObject']) : $data['resultObject'];
                } elseif (isset($data['response'])) {
                    $aiResponse = $data['response'];
                } elseif (isset($data['message'])) {
                    $aiResponse = $data['message'];
                }
            }
            
            if (!$aiResponse) {
                Log::warning('[OneMinAiService] Could not extract response from API', [
                    'data_structure' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)
                ]);
                return null;
            }
            
            // Nettoyer la réponse si nécessaire (enlever les balises HTML, etc.)
            $aiResponse = strip_tags($aiResponse);
            $aiResponse = trim($aiResponse);
            
            Log::info('[OneMinAiService] Successfully extracted AI response', [
                'response_length' => strlen($aiResponse),
                'response_preview' => substr($aiResponse, 0, 100)
            ]);
            
            return [
                'response' => $aiResponse,
                'message' => $aiResponse, // Alias pour compatibilité
            ];
            
        } catch (\Exception $e) {
            Log::error('[OneMinAiService] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Vérifie si la réponse de l'AI indique qu'elle ne peut pas résoudre le problème
     * 
     * @param string $aiResponse La réponse de l'AI
     * @return bool True si l'AI ne peut pas résoudre, false sinon
     */
    public function cannotResolve(string $aiResponse): bool
    {
        $cannotResolveKeywords = [
            "je ne peux pas",
            "je ne sais pas",
            "je ne comprends pas",
            "désolé, je ne peux pas",
            "désolé, je ne sais pas",
            "je ne suis pas sûr",
            "je ne peux pas vous aider",
            "contactez",
            "veuillez contacter",
            "appelez",
            "appelez-nous",
            "contactez-nous",
            "support",
            "assistance",
            "i cannot",
            "i don't know",
            "i'm not sure",
            "please contact",
            "contact us",
            "call us"
        ];
        
        $lowerResponse = strtolower($aiResponse);
        
        foreach ($cannotResolveKeywords as $keyword) {
            if (strpos($lowerResponse, strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Retourne le contexte avec les informations du site HelloPassenger
     * 
     * @return string Le contexte à inclure dans les prompts
     */
    private function getSiteContext(): string
    {
        return <<<'CONTEXT'
Tu es l'assistant virtuel de HelloPassenger, une plateforme de réservation de services dans les aéroports parisiens.

INFORMATIONS SUR HELLOPASSENGER :

À PROPOS :
- HelloPassenger facilite les voyages à Paris en proposant des services pratiques pour les voyageurs
- Plateforme de réservation de services dans les aéroports Parisiens
- Voyagez malin et voyagez bien !
- HelloPassenger vous accompagne et vous livre partout en France
- Pas besoin de porter vos bagages, nous nous occupons de tout

SERVICES PROPOSÉS :
1. TRANSPORT DE BAGAGES
   - Service de transport de bagages vers ou depuis l'aéroport
   - Transport partout en France

2. CONSIGNE À BAGAGES
   - Stockage sécurisé de vos bagages dans nos agences
   - Disponible dans les aéroports CDG et ORLY

3. OBJETS PERDUS
   - Service de récupération d'objets perdus

4. VESTIAIRES
   - Service de vestiaires disponible

LOCALISATIONS :

PARIS CDG AIRPORT (Aéroport Charles de Gaulle) :
- Terminal 2
- TGV Railway station – Level 4
- Opposition Sheraton Hotel, entre Terminal 2C et 2E

PARIS ORLY AIRPORT :
- Terminal 3
- Arrival level (niveau arrivées)

COORDONNÉES :
- Téléphone : +33 (0)1 34 38 58 98
- Email : contact@hellopassenger.com

INSTRUCTIONS IMPORTANTES :
- Réponds toujours en français de manière amicale et professionnelle
- Utilise les informations ci-dessus pour répondre aux questions sur HelloPassenger
- Si tu ne connais pas une information spécifique (comme les tarifs exacts), dirige le client vers le site web ou les coordonnées de contact
- Pour les questions sur les tarifs, indique que les prix varient selon le type de bagage, la durée et les options choisies, et qu'ils peuvent être consultés lors de la réservation en ligne
- Encourage les clients à utiliser la plateforme de réservation en ligne pour obtenir des devis précis
- Si une question nécessite des informations que tu ne possèdes pas, propose de contacter le service client par téléphone ou email

CONTEXT;
    }
}
