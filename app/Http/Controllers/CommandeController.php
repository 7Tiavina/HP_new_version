<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Commande;
use Illuminate\Support\Facades\Response;

class CommandeController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        $commandes = Commande::with('photos.agent')
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        return view('mes-reservations', compact('commandes'));
    }

    /**
     * Get photos for a commande (API endpoint for client)
     */
    public function getPhotos($id)
    {
        try {
            $client = Auth::guard('client')->user();
            
            if (!$client) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $commande = Commande::with('photos.agent')->findOrFail($id);

            // Verify the commande belongs to the client (check by client_id or email)
            if ($commande->client_id && $commande->client_id !== $client->id) {
                \Illuminate\Support\Facades\Log::warning('Unauthorized photo access attempt', [
                    'client_id' => $client->id,
                    'commande_client_id' => $commande->client_id,
                    'commande_id' => $id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Also check by email if client_id is null
            if (!$commande->client_id && $commande->client_email !== $client->email) {
                \Illuminate\Support\Facades\Log::warning('Unauthorized photo access attempt by email', [
                    'client_email' => $client->email,
                    'commande_email' => $commande->client_email,
                    'commande_id' => $id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $photos = $commande->photos->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'url' => $photo->photo_url,
                    'type' => $photo->type,
                    'created_at' => $photo->created_at->format('d/m/Y H:i'),
                    'notes' => $photo->notes,
                    'agent' => $photo->agent ? $photo->agent->email : null,
                ];
            });

            \Illuminate\Support\Facades\Log::info('Photos retrieved for client', [
                'commande_id' => $id,
                'client_id' => $client->id,
                'photos_count' => $photos->count()
            ]);

            return response()->json(['photos' => $photos]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting photos', [
                'commande_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }

    public function showInvoice($id)
    {
        try {
            $commande = Commande::with('paymentClient')->findOrFail($id); // Charger paymentClient pour la référence

            if (!$commande->invoice_content) {
                \Illuminate\Support\Facades\Log::warning("Facture Base64 non trouvée pour la commande ID: {$id}.");
                abort(404, 'Contenu de la facture introuvable.');
            }

            $pdfContent = base64_decode($commande->invoice_content);

            if ($pdfContent === false) {
                \Illuminate\Support\Facades\Log::error("Erreur lors du décodage Base64 pour la facture de la commande ID: {$id}.");
                abort(500, 'Erreur lors de la lecture de la facture.');
            }
            
            // Format invoice/commande number with airport prefix
            $baseRef = $commande->id_api_commande ?? $commande->paymentClient->monetico_order_id ?? $commande->id;
            $orlyAirportId = '64f00ace-31b6-45b0-bcb2-b562b1ac08d9';
            $cdgAirportId = '88bb89e0-b966-4420-9ed3-7a6745e4d947';
            $airportId = $commande->id_plateforme ?? null;
            
            if ($airportId === $orlyAirportId) {
                $reference = 'F-ORY-' . $baseRef;
            } elseif ($airportId === $cdgAirportId) {
                $reference = 'F-CDG-' . $baseRef;
            } else {
                $reference = $baseRef;
            }
            
            $fileName = "facture-HelloPassenger-{$reference}.pdf";

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"'); // inline pour l'aperçu

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Illuminate\Support\Facades\Log::warning("Commande ID: {$id} non trouvée lors de la tentative d'affichage de la facture.");
            abort(404, 'Commande introuvable.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur lors du service de la facture pour la commande ID: {$id}: " . $e->getMessage(), ['exception' => $e]);
            abort(500, 'Une erreur est survenue lors de la récupération de la facture.');
        }
    }


}