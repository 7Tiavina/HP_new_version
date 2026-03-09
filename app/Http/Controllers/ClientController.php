<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\ClientPasswordGeneratedMail;

class ClientController extends Controller
{
    private function isLegacyClientUser(?User $user): bool
    {
        if (!$user) return false;
        $role = strtolower((string)($user->role ?? ''));
        return !in_array($role, ['admin', 'agent'], true);
    }

    private function ensureClientFromLegacyUser(User $user): Client
    {
        $client = Client::firstOrNew(['email' => $user->email]);

        if (!$client->exists) {
            $local = explode('@', (string)$user->email)[0] ?? 'Client';
            $client->prenom = $client->prenom ?: ucfirst(substr($local, 0, 20));
            $client->nom = $client->nom ?: 'Client';
            $client->telephone = $client->telephone ?: null;
        }

        if (!empty($user->password_hash)) {
            $client->password_hash = $user->password_hash;
        }

        $client->save();
        return $client;
    }
    /**
     * Réinitialise le mot de passe d'un client (mot de passe oublié)
     */
    public function forgotPassword(Request $request)
    {
        Log::info('forgotPassword called', [
            'email' => $request->email,
            'all_input' => $request->all(),
        ]);

        try {
            $request->validate([
                // Validate format only; avoid generic "The selected email is invalid."
                // We'll handle "not found" with a clearer message below.
                'email' => 'required|email',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'adresse email est invalide.',
                    'errors' => ['email' => ['L\'adresse email est invalide.']]
                ], 422);
            }
            return back()->withErrors($e->errors());
        }

        Log::info('Validation passed, looking for client', ['email' => $request->email]);

        $client = Client::where('email', $request->email)->first();

        if (!$client) {
            Log::warning('Client not found in forgotPassword', ['email' => $request->email]);

            // Previous system: email might exist in `users` table (role != admin/agent).
            $legacyUser = User::where('email', $request->email)->first();
            if ($this->isLegacyClientUser($legacyUser)) {
                Log::info('Legacy user found for forgotPassword, syncing to client', ['user_id' => $legacyUser->id, 'email' => $legacyUser->email, 'role' => $legacyUser->role]);
                $client = $this->ensureClientFromLegacyUser($legacyUser);
            } else {
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec cet email.',
                    'errors' => ['email' => 'Aucun compte trouvé avec cet email.']
                ], 422);
            }
            
            return back()->withErrors(['email' => 'Aucun compte trouvé avec cet email.']);
            }
        }

        Log::info('Client found for password reset', [
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        // Générer un nouveau mot de passe
        $password = Str::random(12);
        $newHash = Hash::make($password);
        $client->password_hash = $newHash;
        $client->save();

        // Keep legacy `users` (if any) in sync for this email.
        $legacyUserToSync = User::where('email', $client->email)->first();
        if ($this->isLegacyClientUser($legacyUserToSync)) {
            $legacyUserToSync->password_hash = $newHash;
            $legacyUserToSync->save();
        }

        Log::info('New password generated for client', [
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        // Envoyer l'email avec le mot de passe
        try {
            Log::info('Attempting to send password reset email', [
                'client_id' => $client->id,
                'email' => $client->email,
                'password_length' => strlen($password),
                'mail_driver' => config('mail.default'),
            ]);
            
            Mail::to($client->email)->send(new ClientPasswordGeneratedMail($client, $password));
            
            Log::info('Password reset email sent successfully', [
                'client_id' => $client->id,
                'email' => $client->email,
                'to_address' => $client->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id,
                'email' => $client->email,
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage() . '. Veuillez réessayer.',
                    'errors' => ['email' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage() . '. Veuillez réessayer.']
                ], 422);
            }
            
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage() . '. Veuillez réessayer.']);
        }

        $successMessage = 'Un nouveau mot de passe a été généré et envoyé à ' . $client->email . '. Veuillez vérifier votre boîte de réception (et vos spams si nécessaire).';
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Envoie un mot de passe généré à un client invité
     */
    public function sendGeneratedPassword(Request $request)
    {
        Log::info('sendGeneratedPassword called', [
            'email' => $request->email,
            'all_input' => $request->all(),
        ]);

        try {
            $request->validate([
                // Validate format only; handle missing client ourselves.
                'email' => 'required|email',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in sendGeneratedPassword', [
                'errors' => $e->errors(),
                'email' => $request->email,
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        Log::info('Validation passed, looking for client', ['email' => $request->email]);

        $client = Client::where('email', $request->email)->first();

        if (!$client) {
            Log::warning('Client not found in sendGeneratedPassword', ['email' => $request->email]);
            $legacyUser = User::where('email', $request->email)->first();
            if ($this->isLegacyClientUser($legacyUser)) {
                Log::info('Legacy user found for sendGeneratedPassword, syncing to client', ['user_id' => $legacyUser->id, 'email' => $legacyUser->email, 'role' => $legacyUser->role]);
                $client = $this->ensureClientFromLegacyUser($legacyUser);
            } else {
                return back()->withErrors(['email' => 'Aucun compte trouvé avec cet email.']);
            }
        }

        Log::info('Client found', [
            'client_id' => $client->id,
            'email' => $client->email,
            'has_password' => !empty($client->password_hash),
        ]);

        // Permettre la réinitialisation même si le client a déjà un mot de passe
        // (utile si le client a oublié son mot de passe ou s'il y a eu un problème)
        Log::info('Generating new password for client', [
            'client_id' => $client->id,
            'email' => $client->email,
            'had_existing_password' => !empty($client->password_hash),
        ]);

        // Générer un nouveau mot de passe
        $password = Str::random(12);
        $newHash = Hash::make($password);
        $client->password_hash = $newHash;
        $client->save();

        // Sync legacy user password too (if applicable).
        $legacyUserToSync = User::where('email', $client->email)->first();
        if ($this->isLegacyClientUser($legacyUserToSync)) {
            $legacyUserToSync->password_hash = $newHash;
            $legacyUserToSync->save();
        }

        // Envoyer l'email avec le mot de passe
        try {
            Log::info('Attempting to send client password email', [
                'client_id' => $client->id,
                'email' => $client->email,
                'password_length' => strlen($password),
                'mail_driver' => config('mail.default'),
            ]);
            
            // Envoyer l'email de manière synchrone (pas en queue)
            Mail::to($client->email)->send(new ClientPasswordGeneratedMail($client, $password));
            
            Log::info('Client password generated and email sent successfully', [
                'client_id' => $client->id,
                'email' => $client->email,
                'to_address' => $client->email,
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send client password email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id,
                'email' => $client->email,
            ]);
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage() . '. Veuillez réessayer.']);
        }

        // Transférer les commandes invitées vers ce client
        $transferredCount = $this->transferGuestOrdersToClient($client);

        $successMessage = 'Un mot de passe a été généré et envoyé à ' . $client->email . '. ';
        if ($transferredCount > 0) {
            $successMessage .= "Vos {$transferredCount} commande(s) précédente(s) ont été liées à votre compte. ";
        }
        $successMessage .= 'Veuillez vérifier votre boîte de réception (et vos spams si nécessaire).';

        return back()->with('success', $successMessage);
    }

    /**
     * Transfère les commandes invitées vers un client lors de la conversion
     */
    private function transferGuestOrdersToClient(Client $client)
    {
        // Trouver toutes les commandes avec le même email mais sans client_id ou avec un client_id différent
        $guestOrders = Commande::where('client_email', $client->email)
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
            Log::info('Guest orders transferred to client', [
                'client_id' => $client->id,
                'email' => $client->email,
                'orders_count' => $transferredCount,
            ]);
        }

        return $transferredCount;
    }

    /**
     * Affiche la page de profil client
     */
    public function showProfile()
    {
        $client = auth()->guard('client')->user();
        return view('client.profile', compact('client'));
    }

    /**
     * Met à jour le profil client
     */
    public function updateProfile(Request $request)
    {
        Log::info('=== [ClientController@updateProfile] START ===', [
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'method' => $request->method(),
            'all_input' => $request->all(),
            'guard_check' => auth()->guard('client')->check(),
            'guard_id' => auth()->guard('client')->id(),
        ]);

        $client = auth()->guard('client')->user();

        if (!$client) {
            Log::error('[ClientController@updateProfile] No authenticated client found');
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié. Veuillez vous connecter.',
            ], 401);
        }

        Log::info('[ClientController@updateProfile] Authenticated client found', [
            'client_id' => $client->id,
            'client_email' => $client->email,
        ]);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'adresse' => 'nullable|string|max:255',
            'complementAdresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:150',
            'codePostal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:100',
            'premiumDetails' => 'nullable|array',
        ]);

        Log::info('[ClientController@updateProfile] Validation passed', ['validated' => $validated]);

        try {
            $client->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'complementAdresse' => $request->complementAdresse,
                'ville' => $request->ville,
                'codePostal' => $request->codePostal,
                'pays' => $request->pays,
            ]);

            Log::info('[ClientController@updateProfile] Client profile updated successfully', [
                'client_id' => $client->id,
                'new_data' => $validated,
            ]);

            // === HANDLE PREMIUM DETAILS FOR CONNECTED CLIENTS (SAME AS GUEST FLOW) ===
            if ($request->has('premiumDetails') && is_array($request->premiumDetails)) {
                Log::info('[ClientController@updateProfile] Premium details detected for connected client', [
                    'premiumDetails' => $request->premiumDetails,
                ]);

                // Store premium details in session (same as guest flow)
                session(['premiumDetails' => $request->premiumDetails]);

                // Update or create commande_en_cours with premium info
                $commandeEnCours = session('commande_en_cours');
                
                if ($commandeEnCours) {
                    // === GENERER COMMANDEINFOS DEPUIS LES INFOS PREMIUM (MEME LOGIQUE QUE GUEST) ===
                    $premiumDetails = $request->premiumDetails;
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

                    $commandeEnCours['commandeInfos'] = $commandeInfos;
                    session(['commande_en_cours' => $commandeEnCours]);
                    Log::info('[ClientController@updateProfile] commande_en_cours updated with premium info', [
                        'commandeInfos' => $commandeInfos,
                    ]);
                }

                Log::info('[ClientController@updateProfile] Premium details saved to session for connected client');
            }

            if ($request->expectsJson()) {
                Log::info('=== [ClientController@updateProfile] SUCCESS (JSON) ===');
                return response()->json([
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès !'
                ]);
            }

            Log::info('=== [ClientController@updateProfile] SUCCESS (redirect) ===');
            return back()->with('success', 'Profil mis à jour avec succès !');

        } catch (\Exception $e) {
            Log::error('=== [ClientController@updateProfile] EXCEPTION ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }
}
