<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Client;

class AuthController extends Controller
{
    /**
     * Treat legacy "users" (previous system) as clients when role is not admin/agent.
     */
    private function isLegacyClientUser(?User $user): bool
    {
        if (!$user) return false;
        $role = strtolower((string)($user->role ?? ''));
        return !in_array($role, ['admin', 'agent'], true);
    }

    private function ensureClientFromLegacyUser(User $user): Client
    {
        $client = Client::firstOrNew(['email' => $user->email]);

        // Minimal defaults if record did not exist
        if (!$client->exists) {
            $local = explode('@', (string)$user->email)[0] ?? 'Client';
            $client->prenom = $client->prenom ?: ucfirst(substr($local, 0, 20));
            $client->nom = $client->nom ?: 'Client';
            $client->telephone = $client->telephone ?: null;
        }

        // Reuse the same bcrypt hash from legacy user
        if (!empty($user->password_hash)) {
            $client->password_hash = $user->password_hash;
        }

        $client->save();
        return $client;
    }

    /**
     * Traite la connexion (admin ou client) - détection automatique
     */
    public function login(Request $request)
    {
        // Validate email format first
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $email = $request->email;
        $password = $request->password;
        $lang = session('app_language', 'fr');

        // Check if email exists in system
        $client = Client::where('email', $email)->first();
        if (!$client) {
            // Check legacy users table
            $user = User::where('email', $email)->first();
            if (!$user) {
                // Email not found at all
                $msg = $lang === 'en' ? 'Email address not found in our system.' : 'Adresse email introuvable dans notre système.';
                return redirect()->route('account')
                    ->withErrors(['email' => $msg])
                    ->with('login_error', true)
                    ->withInput($request->only('email'));
            }

            // Legacy user found - check if it's a client user
            if ($this->isLegacyClientUser($user)) {
                $client = $this->ensureClientFromLegacyUser($user);
            } else {
                $msg = $lang === 'en' ? 'Email address not found in our system.' : 'Adresse email introuvable dans notre système.';
                return redirect()->route('account')
                    ->withErrors(['email' => $msg])
                    ->with('login_error', true)
                    ->withInput($request->only('email'));
            }
        }

        if ($client) {
            \Log::info('Client login attempt', [
                'client_id' => $client->id,
                'email' => substr((string)$email, 0, 2) . '***@' . explode('@', (string)$email)[1] ?? '',
            ]);

            // Vérifier si c'est un client invité (pas de mot de passe valide)
            $isGuest = empty($client->password_hash) ||
                      (strpos($client->password_hash, '$2y$') !== 0 && strpos($client->password_hash, '$2a$') !== 0);

            if ($isGuest) {
                \Log::info('Guest client trying to login', ['client_id' => $client->id]);
                return redirect()->route('account')
                    ->with('guest_login_attempt', true)
                    ->with('guest_email', $email)
                    ->withInput($request->only('email'));
            }

            // Nettoyer le mot de passe (supprimer les espaces en début/fin)
            $cleanPassword = trim($password);

            if (Hash::check($cleanPassword, $client->password_hash)) {
                Auth::guard('client')->login($client);
                $request->session()->regenerate();

                \Log::info('Client logged in successfully', ['client_id' => $client->id]);

                if ($request->input('redirect_link_form')) {
                    return redirect(route('payment'))->with('success', 'Connexion réussie !');
                }
                return redirect()->route('client.dashboard')->with('success', 'Connexion réussie !');
            } else {
                // Password is wrong
                $msg = $lang === 'en' ? 'Incorrect password.' : 'Mot de passe incorrect.';
                \Log::warning('Client password mismatch', [
                    'client_id' => $client->id,
                ]);
                return redirect()->route('account')
                    ->withErrors(['email' => $msg])
                    ->with('login_error', true)
                    ->withInput($request->only('email'));
            }
        }

        // Si pas client, essayer comme admin
        $user = User::where('email', $email)->first();
        if ($user) {
            \Log::info('Admin/User found', [
                'user_id' => $user->id,
                'email' => substr((string)$email, 0, 2) . '***@' . explode('@', (string)$email)[1] ?? '',
            ]);
            
            // Nettoyer le mot de passe (supprimer les espaces en début/fin)
            $cleanPassword = trim($password);
            
            if (Hash::check($cleanPassword, $user->password_hash)) {
                // Previous system: some "users" are actually clients.
                if ($this->isLegacyClientUser($user)) {
                    $client = $this->ensureClientFromLegacyUser($user);
                    session()->forget(['user_id', 'user_role', 'agent_id', 'agent_email', 'agent_role']);
                    Auth::guard('client')->login($client);
                    $request->session()->regenerate();

                    \Log::info('Legacy user logged in as client', ['user_id' => $user->id, 'client_id' => $client->id]);
                    return redirect()->route('client.dashboard')->with('success', 'Connexion réussie !');
                }

                // Si c'est un agent, créer la session agent et rediriger vers l'espace agent
                if ($user->role === 'agent') {
                    session()->forget(['user_id', 'user_role']);
                    session([
                        'agent_id' => $user->id,
                        'agent_email' => $user->email,
                        'agent_role' => 'agent',
                    ]);
                    $request->session()->regenerate();

                    \Log::info('Agent logged in successfully (AuthController)', ['agent_id' => $user->id]);
                    return redirect()->route('agent.dashboard')->with('success', 'Connexion réussie !');
                }

                // Sinon admin/user normal
                session()->forget(['agent_id', 'agent_email', 'agent_role']);
                session(['user_id' => $user->id, 'user_role' => $user->role]);
                $request->session()->regenerate();

                \Log::info('Admin logged in successfully', ['user_id' => $user->id, 'role' => $user->role]);
                return redirect()->route('dashboard')->with('success', 'Connexion réussie !');
            } else {
                \Log::warning('User password mismatch', [
                    'user_id' => $user->id,
                ]);
            }
        }

        // Aucune correspondance trouvée
        \Log::warning('Login failed - no matching user or password incorrect');
        return redirect()->route('account')
            ->withErrors(['email' => 'Email ou mot de passe incorrect'])
            ->with('login_error', true)
            ->withInput($request->only('email'));
    }

    /**
     * Déconnexion unifiée
     */
    public function logout(Request $request)
    {
        // Déconnexion admin
        if (session()->has('user_id')) {
            session()->flush();
        }
        
        // Déconnexion client
        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Always send user back to public home after logout.
        return redirect()->route('form-consigne');
    }

    /**
     * Inscription client
     */
    public function register(Request $request)
    {
        $lang = session('app_language', 'fr');

        // Check if client already exists with this email
        $existingClient = Client::where('email', $request->email)->first();

        // If client exists with valid password, block registration
        if ($existingClient && !empty($existingClient->password_hash) &&
            (strpos($existingClient->password_hash, '$2y$') === 0 || strpos($existingClient->password_hash, '$2a$') === 0)) {
            $emailMsg = $lang === 'en'
                ? 'This email is already in use. Please log in or use "Forgot password".'
                : 'Cet email est déjà utilisé. Veuillez vous connecter ou utiliser "Mot de passe oublié".';

            return redirect()->route('account')
                ->withInput()
                ->withErrors(['email' => $emailMsg])
                ->with('from_register', true);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'privacy' => 'required|accepted',
        ], [
            'password.min' => $lang === 'en' ? 'The password must be at least 6 characters.' : 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => $lang === 'en' ? 'The password confirmation does not match.' : 'La confirmation du mot de passe ne correspond pas.',
            'password.required' => $lang === 'en' ? 'The password is required.' : 'Le mot de passe est obligatoire.',
            'email.required' => $lang === 'en' ? 'The email address is required.' : 'L\'adresse email est obligatoire.',
            'email.email' => $lang === 'en' ? 'The email address is not valid.' : 'L\'adresse email n\'est pas valide.',
            'nom.required' => $lang === 'en' ? 'The last name is required.' : 'Le nom est obligatoire.',
            'prenom.required' => $lang === 'en' ? 'The first name is required.' : 'Le prénom est obligatoire.',
            'privacy.required' => $lang === 'en' ? 'You must accept the privacy policy.' : 'Vous devez accepter la politique de confidentialité.',
            'privacy.accepted' => $lang === 'en' ? 'You must accept the privacy policy.' : 'Vous devez accepter la politique de confidentialité.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account')
                ->withInput()
                ->withErrors($validator)
                ->with('from_register', true);
        }

        // Si le client existe mais sans mot de passe (compte invité), mettre à jour
        if ($existingClient) {
            $existingClient->password_hash = Hash::make($request->password);
            $existingClient->nom = $request->nom;
            $existingClient->prenom = $request->prenom;
            if ($request->telephone) {
                $existingClient->telephone = $request->telephone;
            }
            $existingClient->save();
            $client = $existingClient;

            // Transférer les commandes invitées vers ce client
            $this->transferGuestOrdersToClient($client);
        } else {
            // Créer un nouveau compte
            $client = Client::create([
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
            ]);
        }

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        if ($request->input('redirect_link_form')) {
            return redirect(route('payment'))->with('success', 'Compte créé avec succès !');
        }

        // Redirect to profile so user can fill in their address
        return redirect()->route('client.profile')->with('success', 'Compte créé avec succès !');
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
}
