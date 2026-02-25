<?php

namespace App\Http\Controllers;

use App\Services\OneMinAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected $oneMinAiService;
    
    public function __construct(OneMinAiService $oneMinAiService)
    {
        $this->oneMinAiService = $oneMinAiService;
    }
    
    /**
     * Traite un message du chatbot
     */
    public function sendMessage(Request $request)
    {
        try {
            // Valider les données
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);
            
            $message = $validated['message'];
            $conversationHistory = Session::get('chatbot_history', []);
            
            // Envoyer le message à l'API 1min.ai
            $response = $this->oneMinAiService->sendMessage($message, $conversationHistory);
            
            if (!$response) {
                // Si l'API ne répond pas, afficher un message clair avec WhatsApp
                Log::warning('[ChatbotController] OneMinAiService returned null - API unavailable or misconfigured');
                return response()->json([
                    'success' => false,
                    'message' => 'Désolé, le service de chat n\'est pas disponible pour le moment. Notre équipe est là pour vous aider via WhatsApp.',
                    'show_whatsapp' => true,
                ], 200);
            }
            
            // Extraire la réponse de l'IA
            $aiResponse = $response['response'] ?? $response['message'] ?? null;
            
            if (!$aiResponse) {
                Log::warning('[ChatbotController] Could not extract AI response', ['response_structure' => $response]);
                return response()->json([
                    'success' => false,
                    'message' => 'Désolé, je n\'ai pas pu traiter votre demande. Veuillez nous contacter via WhatsApp pour plus d\'aide.',
                    'show_whatsapp' => true,
                ], 200);
            }
            
            // Vérifier si l'AI ne peut pas résoudre le problème
            $cannotResolve = $this->oneMinAiService->cannotResolve($aiResponse);
            
            // Mettre à jour l'historique de conversation
            $conversationHistory[] = [
                'role' => 'user',
                'content' => $message,
            ];
            $conversationHistory[] = [
                'role' => 'assistant',
                'content' => $aiResponse,
            ];
            
            // Limiter l'historique à 10 derniers messages (5 paires)
            if (count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
            }
            
            Session::put('chatbot_history', $conversationHistory);
            
            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'show_whatsapp' => $cannotResolve,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message invalide. Veuillez réessayer.',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('[ChatbotController] Exception in sendMessage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
                'show_whatsapp' => true,
            ], 500);
        }
    }
    
    /**
     * Réinitialise l'historique de conversation
     */
    public function resetHistory()
    {
        Session::forget('chatbot_history');
        
        return response()->json([
            'success' => true,
            'message' => 'Historique réinitialisé',
        ]);
    }
    
    /**
     * Détecte le pays d'un numéro de téléphone en utilisant l'IA 1min.ai
     */
    public function detectPhoneCountry(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|max:50',
            ]);
            
            $phoneNumber = $validated['phone_number'];
            
            // Détection basée sur des règles d'abord (plus rapide et fiable)
            $detectedByRules = $this->detectCountryByRules($phoneNumber);
            
            if ($detectedByRules) {
                Log::info('[ChatbotController] Pays détecté par règles', [
                    'phone' => $phoneNumber,
                    'country' => $detectedByRules
                ]);
                
                return response()->json([
                    'success' => true,
                    'country' => strtolower($detectedByRules),
                    'method' => 'rules',
                ]);
            }
            
            // Si les règles ne trouvent pas, utiliser l'IA avec un prompt expert mondial
            $cleanDigits = preg_replace('/\D/', '', $phoneNumber);
            $length = strlen($cleanDigits);
            $startsWith = substr($cleanDigits, 0, min(3, $length));
            
            $prompt = "Tu es un EXPERT MONDIAL en numéros de téléphone. Tu connais TOUS les formats de TOUS les pays du monde (195+ pays).\n\n";
            $prompt .= "Numéro à analyser: {$phoneNumber}\n";
            $prompt .= "Longueur: {$length} chiffres\n";
            $prompt .= "Commence par: {$startsWith}\n\n";
            
            $prompt .= "ANALYSE DÉTAILLÉE REQUISE:\n\n";
            
            $prompt .= "1. FORMATS INTERNATIONAUX (commencent par + ou 00):\n";
            $prompt .= "   - Extraire le code pays (1-3 chiffres après + ou 00)\n";
            $prompt .= "   - +33/0033=FR, +44/0044=GB, +32/0032=BE, +41/0041=CH\n";
            $prompt .= "   - +1/001=US/CA, +230/00230=MU (Maurice), +49/0049=DE\n";
            $prompt .= "   - +34/0034=ES, +39/0039=IT, +31/0031=NL\n";
            $prompt .= "   - Connais TOUS les codes pays du monde (ex: +230=MU, +230=MU)\n\n";
            
            $prompt .= "2. FORMATS LOCAUX (sans + ni 00) - ANALYSE PAR LONGUEUR ET PRÉFIXE:\n\n";
            $prompt .= "   MAURICE (MU) - PRIORITÉ HAUTE:\n";
            $prompt .= "   - 7-8 chiffres commençant par 5, 6 ou 7 = MU\n";
            $prompt .= "   - Exemples: 57177950 (8 chiffres, commence par 5) = MU\n";
            $prompt .= "   - Exemples: 6123456 (7 chiffres, commence par 6) = MU\n";
            $prompt .= "   - Exemples: 71234567 (8 chiffres, commence par 7) = MU\n";
            $prompt .= "   - Format international: +230 suivi de 7-8 chiffres = MU\n\n";
            
            $prompt .= "   AUTRES PAYS:\n";
            $prompt .= "   - UK: 07XXXXXXXX (11 chiffres) ou +44\n";
            $prompt .= "   - FR: 06XXXXXXXX ou 07XXXXXXXX (10 chiffres) ou +33\n";
            $prompt .= "   - BE: 04XXXXXXXX (10 chiffres) ou +32\n";
            $prompt .= "   - CH: 07XXXXXXXX (10 chiffres) ou +41\n";
            $prompt .= "   - ES: 6XXXXXXXX ou 7XXXXXXXX (9 chiffres) ou +34\n";
            $prompt .= "   - IT: 3XXXXXXXX (10 chiffres) ou +39\n";
            $prompt .= "   - NL: 06XXXXXXXX (10 chiffres) ou +31\n";
            $prompt .= "   - Et TOUS les autres pays du monde avec leurs formats spécifiques\n\n";
            
            $prompt .= "3. MÉTHODE D'ANALYSE:\n";
            $prompt .= "   a) Vérifier d'abord si format international (+ ou 00)\n";
            $prompt .= "   b) Si format local, analyser longueur ET chiffres de début\n";
            $prompt .= "   c) MAURICE: 7-8 chiffres commençant par 5, 6 ou 7 = MU (PRIORITÉ)\n";
            $prompt .= "   d) Comparer avec formats connus de TOUS les pays du monde\n";
            $prompt .= "   e) Utiliser ta connaissance complète des formats téléphoniques mondiaux\n\n";
            
            $prompt .= "RÉPONSE REQUISE:\n";
            $prompt .= "Réponds UNIQUEMENT le code ISO2 du pays en MAJUSCULES (ex: MU, FR, GB, US, BE, CH, DE, ES, IT, NL, etc.).\n";
            $prompt .= "Ne réponds QUE le code pays, rien d'autre. Pas d'explication, pas de texte supplémentaire.\n\n";
            $prompt .= "Code pays:";
            
            Log::info('[ChatbotController] Envoi demande détection pays à l\'IA', [
                'phone' => $phoneNumber,
                'prompt_length' => strlen($prompt)
            ]);
            
            // Envoyer à l'IA
            $response = $this->oneMinAiService->sendMessage($prompt, []);
            
            if (!$response) {
                Log::warning('[ChatbotController] OneMinAiService returned null');
                return response()->json([
                    'success' => false,
                    'country' => null,
                    'error' => 'IA service unavailable'
                ], 200);
            }
            
            // Extraire la réponse de l'IA
            $aiResponse = $response['response'] ?? $response['message'] ?? '';
            
            Log::info('[ChatbotController] Réponse IA reçue', [
                'phone' => $phoneNumber,
                'ai_response_raw' => $aiResponse,
                'response_structure' => $response
            ]);
            
            if (empty($aiResponse)) {
                Log::warning('[ChatbotController] Réponse IA vide');
                return response()->json([
                    'success' => false,
                    'country' => null,
                    'ai_response' => $aiResponse,
                ], 200);
            }
            
            // Nettoyer la réponse pour extraire le code pays
            $aiResponse = trim($aiResponse);
            $aiResponse = strtoupper($aiResponse);
            
            // Extraire le code pays (2 lettres majuscules) - plusieurs patterns
            $detectedCountry = null;
            
            // Pattern 1: Code pays isolé (ex: "GB" ou "GB." ou "GB,")
            if (preg_match('/\b([A-Z]{2})\b/', $aiResponse, $matches)) {
                $detectedCountry = $matches[1];
            }
            // Pattern 2: Code pays au début (ex: "GB: United Kingdom")
            elseif (preg_match('/^([A-Z]{2})[:\s]/', $aiResponse, $matches)) {
                $detectedCountry = $matches[1];
            }
            // Pattern 3: Code pays à la fin (ex: "United Kingdom (GB)")
            elseif (preg_match('/\(([A-Z]{2})\)/', $aiResponse, $matches)) {
                $detectedCountry = $matches[1];
            }
            // Pattern 4: Prendre les 2 premiers caractères si ce sont des lettres majuscules
            elseif (preg_match('/^([A-Z]{2})/', $aiResponse, $matches)) {
                $detectedCountry = $matches[1];
            }
            
            // Liste des codes pays valides (tous les pays du monde - ISO 3166-1 alpha-2)
            // Cette liste permet à l'IA de détecter n'importe quel pays du monde
            $validCountries = [
                // Europe
                'FR', 'GB', 'BE', 'CH', 'DE', 'ES', 'IT', 'NL', 'AT', 'DK', 'SE', 'NO', 'FI', 'PL', 'CZ', 'GR', 'HU', 'RO', 'BG', 'HR', 'SI', 'SK', 'LT', 'LV', 'EE', 'LU', 'MT', 'CY', 'IE', 'PT', 'IS', 'FO', 'GL', 'AD', 'MC', 'SM', 'VA', 'LI', 'GI', 'IM', 'JE', 'GG', 'AX', 'SJ',
                // Amérique
                'US', 'CA', 'MX', 'BR', 'AR', 'CL', 'CO', 'PE', 'VE', 'EC', 'GT', 'CU', 'BO', 'DO', 'HN', 'PY', 'SV', 'NI', 'CR', 'PA', 'UY', 'JM', 'TT', 'BZ', 'BS', 'BB', 'GD', 'LC', 'VC', 'AG', 'SR', 'GY', 'FK',
                // Asie
                'CN', 'JP', 'IN', 'KR', 'ID', 'TH', 'VN', 'PH', 'MY', 'SG', 'BD', 'PK', 'LK', 'MM', 'KH', 'LA', 'NP', 'AF', 'IQ', 'IR', 'SA', 'AE', 'IL', 'JO', 'LB', 'KW', 'OM', 'QA', 'BH', 'YE', 'SY', 'AM', 'AZ', 'GE', 'KZ', 'UZ', 'TM', 'TJ', 'KG', 'MN', 'TW', 'HK', 'MO', 'BN', 'MV', 'BT', 'TL', 'FM', 'MH', 'PW', 'NR', 'TV', 'KI', 'SB', 'VU', 'NC', 'PF', 'WS', 'TO', 'FJ', 'PG', 'CK', 'NU', 'TK',
                // Afrique
                'MU', 'ZA', 'EG', 'NG', 'KE', 'GH', 'TZ', 'ET', 'UG', 'DZ', 'SD', 'MA', 'AO', 'MZ', 'MG', 'CM', 'CI', 'NE', 'BF', 'ML', 'MW', 'ZM', 'ZW', 'SN', 'TD', 'SO', 'GN', 'RW', 'BJ', 'BI', 'TN', 'ER', 'TG', 'SL', 'LY', 'LR', 'MR', 'GM', 'GW', 'GQ', 'GA', 'CG', 'CD', 'CF', 'SS', 'ST', 'SC', 'CV', 'KM', 'DJ', 'EH', 'LS', 'BW', 'NA', 'SZ',
                // Océanie
                'AU', 'NZ', 'PG', 'FJ', 'SB', 'VU', 'NC', 'PF', 'WS', 'TO', 'FM', 'MH', 'KI', 'PW', 'TV', 'NR', 'CK', 'NU', 'TK',
                // Autres
                'RU', 'TR', 'UA', 'BY', 'MD', 'AL', 'MK', 'RS', 'ME', 'BA', 'XK', 'IS', 'FO', 'GL', 'AD', 'MC', 'SM', 'VA', 'LI', 'GI', 'IM', 'JE', 'GG', 'AX', 'SJ'
            ];
            
            if ($detectedCountry && in_array($detectedCountry, $validCountries)) {
                Log::info('[ChatbotController] ✅ Pays détecté par IA', [
                    'phone' => $phoneNumber,
                    'country' => $detectedCountry,
                    'ai_response' => $aiResponse
                ]);
                
                return response()->json([
                    'success' => true,
                    'country' => strtolower($detectedCountry),
                    'ai_response' => $aiResponse,
                ]);
            }
            
            Log::warning('[ChatbotController] ⚠️ Pays non détecté par IA', [
                'phone' => $phoneNumber,
                'ai_response' => $aiResponse,
                'detected_country' => $detectedCountry,
                'is_valid' => $detectedCountry ? in_array($detectedCountry, $validCountries) : false
            ]);
            
            return response()->json([
                'success' => false,
                'country' => null,
                'ai_response' => $aiResponse,
                'detected_country' => $detectedCountry,
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('[ChatbotController] Exception in detectPhoneCountry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'country' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Détecte le pays d'un numéro de téléphone en utilisant des règles prédéfinies
     * Plus rapide et fiable que l'IA pour les cas courants
     */
    private function detectCountryByRules(string $phoneNumber): ?string
    {
        $cleanNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        $digitsOnly = preg_replace('/\D/', '', $phoneNumber);
        
        // Format international avec +
        if (strpos($cleanNumber, '+') === 0) {
            $code = substr($cleanNumber, 1);
            
            // Codes pays à 1 chiffre
            if (strlen($code) >= 10 && substr($code, 0, 1) === '1') {
                // US ou CA - difficile à distinguer sans plus d'info, on retourne null pour laisser l'IA décider
                return null;
            }
            
            // Codes pays à 2 chiffres
            $twoDigit = substr($code, 0, 2);
            $countryMap = [
                '33' => 'FR', // France
                '44' => 'GB', // Royaume-Uni
                '32' => 'BE', // Belgique
                '41' => 'CH', // Suisse
                '49' => 'DE', // Allemagne
                '34' => 'ES', // Espagne
                '39' => 'IT', // Italie
                '31' => 'NL', // Pays-Bas
                '61' => 'AU', // Australie
                '64' => 'NZ', // Nouvelle-Zélande
                '33' => 'FR', // France (doublon pour clarté)
            ];
            
            if (isset($countryMap[$twoDigit])) {
                return $countryMap[$twoDigit];
            }
            
            // Codes pays à 3 chiffres
            $threeDigit = substr($code, 0, 3);
            $countryMap3 = [
                '230' => 'MU', // Maurice
                '230' => 'MU', // Maurice (doublon)
            ];
            
            if (isset($countryMap3[$threeDigit])) {
                return $countryMap3[$threeDigit];
            }
        }
        
        // Format avec 00
        if (strpos($cleanNumber, '00') === 0) {
            $code = substr($cleanNumber, 2);
            
            $twoDigit = substr($code, 0, 2);
            $countryMap = [
                '33' => 'FR',
                '44' => 'GB',
                '32' => 'BE',
                '41' => 'CH',
                '49' => 'DE',
                '34' => 'ES',
                '39' => 'IT',
                '31' => 'NL',
            ];
            
            if (isset($countryMap[$twoDigit])) {
                return $countryMap[$twoDigit];
            }
        }
        
        // Format local - règles spécifiques
        $length = strlen($digitsOnly);
        
        // MAURICE: 7-8 chiffres commençant par 5, 6 ou 7 (PRIORITÉ - format unique)
        if (($length === 7 || $length === 8) && in_array(substr($digitsOnly, 0, 1), ['5', '6', '7'])) {
            // Format typique mauricien: 5XXXXXX, 6XXXXXX, ou 7XXXXXX
            // Exemple: 57177950 = 8 chiffres, commence par 5 = MU
            return 'MU';
        }
        
        // UK: 11 chiffres commençant par 07
        if ($length === 11 && substr($digitsOnly, 0, 2) === '07') {
            return 'GB';
        }
        
        // France: 10 chiffres commençant par 06 ou 07
        if ($length === 10 && (substr($digitsOnly, 0, 2) === '06' || substr($digitsOnly, 0, 2) === '07')) {
            return 'FR';
        }
        
        // Belgique: 10 chiffres commençant par 04
        if ($length === 10 && substr($digitsOnly, 0, 2) === '04') {
            return 'BE';
        }
        
        // Suisse: 10 chiffres commençant par 07
        if ($length === 10 && substr($digitsOnly, 0, 2) === '07') {
            // Ambigu avec FR, on retourne null pour laisser l'IA décider
            return null;
        }
        
        // Espagne: 9 chiffres commençant par 6 ou 7
        if ($length === 9 && (substr($digitsOnly, 0, 1) === '6' || substr($digitsOnly, 0, 1) === '7')) {
            return 'ES';
        }
        
        // Italie: 10 chiffres commençant par 3
        if ($length === 10 && substr($digitsOnly, 0, 1) === '3') {
            return 'IT';
        }
        
        // Pays-Bas: 10 chiffres commençant par 06
        if ($length === 10 && substr($digitsOnly, 0, 2) === '06') {
            // Ambigu avec FR, on retourne null pour laisser l'IA décider
            return null;
        }
        
        // Pour tous les autres cas, retourner null pour laisser l'IA décider
        // L'IA a une connaissance mondiale des formats téléphoniques
        return null;
    }
}
