<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Reservation;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

use App\Models\BagageHistory;
use App\Models\Commande;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    private function requireAdmin(): void
    {
        if (!session()->has('user_id')) {
            abort(403, 'Non authentifié.');
        }
        if (session('user_role') !== 'admin') {
            abort(403, 'Accès réservé admin.');
        }
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Récupère par Eloquent
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            // Log pour debug
            \Illuminate\Support\Facades\Log::info('Login attempt', [
                'email' => $request->email,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'role_from_db' => $user->getOriginal('role') ?? $user->role,
            ]);
            
            // Si c'est un agent, créer la session agent et rediriger vers le dashboard agent
            if ($user->role === 'agent') {
                // Nettoyer toute session admin existante
                session()->forget(['user_id', 'user_role']);
                
                // Créer la session agent
                session([
                    'agent_id' => $user->id, 
                    'agent_email' => $user->email, 
                    'agent_role' => 'agent'
                ]);
                
                \Illuminate\Support\Facades\Log::info('Agent logged in', [
                    'agent_id' => $user->id,
                    'session_agent_id' => session('agent_id'),
                ]);
                
                return redirect()->route('agent.dashboard')->with('success', 'Connexion réussie !');
            }
            
            // Sinon, c'est un admin/user normal
            // Nettoyer toute session agent existante
            session()->forget(['agent_id', 'agent_email', 'agent_role']);
            
            // Stocke seulement l'ID et le rôle pour admin
            session(['user_id' => $user->id, 'user_role' => $user->role]);
            
            \Illuminate\Support\Facades\Log::info('Admin/User logged in', [
                'user_id' => $user->id,
                'user_role' => $user->role,
            ]);
            
            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors(['email' => 'Email ou mot de passe incorrect'])
            ->withInput();
    }

    public function dashboard()
    {
        // Si c'est un agent connecté, rediriger vers le dashboard agent
        if (session('agent_id') && session('agent_role') === 'agent') {
            return redirect()->route('agent.dashboard');
        }
        
        if (! session()->has('user_id')) {
            return redirect()->route('login');
        }
        
        // Statistiques détaillées pour le dashboard admin
        $totalCommandes = Commande::count();
        $commandesAujourdhui = Commande::whereDate('created_at', today())->count();
        $commandesSemaine = Commande::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $commandesMois = Commande::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $totalRevenue = Commande::where('statut', 'completed')->sum('total_prix_ttc');
        $revenueAujourdhui = Commande::where('statut', 'completed')->whereDate('created_at', today())->sum('total_prix_ttc');
        $revenueSemaine = Commande::where('statut', 'completed')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_prix_ttc');
        $commandesEnAttente = Commande::where('statut', 'pending')->count();
        $commandesCompleted = Commande::where('statut', 'completed')->count();
        $commandesFailed = Commande::where('statut', 'failed')->count();
        $totalClients = \App\Models\Client::count();
        $totalPhotos = \App\Models\BagagePhoto::count();
        
        $recentCommandes = Commande::with('photos')->latest()
            ->take(10)
            ->get();
        
        return view('dashboard', compact(
            'totalCommandes',
            'commandesAujourdhui',
            'commandesSemaine',
            'commandesMois',
            'totalRevenue',
            'revenueAujourdhui',
            'revenueSemaine',
            'commandesEnAttente',
            'commandesCompleted',
            'commandesFailed',
            'totalClients',
            'totalPhotos',
            'recentCommandes'
        ));
    }

    /**
     * Admin: update statut for a commande (pending/processing/completed/cancelled/failed).
     */
    public function updateCommandeStatus(Request $request, $id)
    {
        $this->requireAdmin();

        $validated = $request->validate([
            'statut' => [
                'required',
                'string',
                Rule::in(['pending', 'processing', 'completed', 'cancelled', 'failed']),
            ],
        ]);

        $commande = Commande::findOrFail($id);
        $commande->statut = $validated['statut'];
        $commande->save();

        return back()->with('success', 'Statut mis à jour.');
    }

    public function logout()
    {
        session()->flush();
        // After admin logout, go back to public home.
        return redirect()->route('front.acceuil');
    }

    // Affiche chaque section
    public function overview()    { return view('components.overview'); }
    
    public function analytics()   { return view('components.analytics'); }
    public function chat()        { return view('components.chat'); }

    public function users() {
        $agents = User::where('role', 'agent')->get();
        $users = User::where('role', 'user')->get();
        return view('components.users', compact('agents', 'users'));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|unique:users',
            'role'                  => 'required|in:user,agent,admin',
            'password'              => 'required|min:6|confirmed',
        ]);

        $hash = Hash::make($request->password);
        $local = explode('@', (string)$request->email)[0] ?? 'User';
        $data = [
            'email'           => $request->email,
            'role'            => $request->role, // S'assurer que le rôle est bien sauvegardé
            'password_hash'   => $hash,
        ];
        if (Schema::hasColumn('users', 'name')) {
            $data['name'] = ucfirst(substr((string)$local, 0, 50));
        }
        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = $hash;
        }

        $user = User::create($data);

        // Log pour debug
        \Illuminate\Support\Facades\Log::info('User created', [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('users')->with('success', 'Utilisateur créé avec le rôle: ' . $user->role);
    }

    public function orders()
    {
        $reservations = \App\Models\Reservation::with('user')
            ->orderByDesc('created_at')
            ->take(25)
            ->get();

        return view('components.orders', compact('reservations'));
    }

   public function myorders()
    {
        $agentId = session('user_id');

        $reservationIds = BagageHistory::where('agent_id', $agentId)
            ->where('status', 'collecté')
            ->pluck('reservation_id');

        $reservations = Reservation::with(['user', 'histories.agent'])
            ->whereIn('id', $reservationIds)
            ->orderByDesc('created_at')
            ->take(25)
            ->get();

        // → On ne passe PAS 'version' du tout
        $options = new QROptions([
            'eccLevel'   => QRCode::ECC_L,
            'outputType' => QROutputInterface::MARKUP_SVG,
            'scale'      => 5,
        ]);
        $qrGenerator = new QRCode($options);

        foreach ($reservations as $res) {
            $res->qr_svg = $qrGenerator->render($res->ref);
        }

        return view('components.myorders', compact('reservations'));
    }
}