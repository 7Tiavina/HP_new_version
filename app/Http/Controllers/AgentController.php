<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Commande;
use App\Models\BagagePhoto;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Show agent login form
     */
    public function showLogin(Request $request)
    {
        // Avoid stale CSRF token issues ("Page expired") after server restarts/back navigation.
        $request->session()->regenerateToken();
        return view('agent.login');
    }

    /**
     * Handle agent login (agent-only)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || $user->role !== 'agent') {
            return back()
                ->with('error', 'Aucun compte agent trouvé avec cet email.')
                ->withInput($request->only('email'));
        }

        if (!Hash::check(trim($request->password), $user->password_hash)) {
            return back()
                ->with('error', 'Email ou mot de passe incorrect.')
                ->withInput($request->only('email'));
        }

        // Clean any admin session and set agent session
        session()->forget(['user_id', 'user_role']);
        session([
            'agent_id' => $user->id,
            'agent_email' => $user->email,
            'agent_role' => 'agent',
        ]);
        $request->session()->regenerate();

        Log::info('Agent logged in successfully (AgentController)', [
            'agent_id' => $user->id,
        ]);

        return redirect()->route('agent.dashboard')->with('success', 'Connexion réussie !');
    }

    /**
     * Show agent dashboard
     */
    public function dashboard()
    {
        // Vérifier que l'utilisateur est bien un agent connecté
        if (!session('agent_id') || session('agent_role') !== 'agent') {
            // Nettoyer la session si elle est invalide
            session()->forget(['agent_id', 'agent_email', 'agent_role']);
            return redirect()->route('agent.login')->with('error', 'Veuillez vous connecter en tant qu\'agent.');
        }

        $agentId = session('agent_id');
        
        // Commandes récentes (toutes les commandes pour que l'agent puisse voir toutes les commandes)
        $commandes = Commande::with(['client', 'photos.agent'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Statistiques détaillées
        $stats = [
            'total_commandes' => Commande::count(),
            'commandes_aujourdhui' => Commande::whereDate('created_at', today())->count(),
            'commandes_semaine' => Commande::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'commandes_mois' => Commande::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'commandes_completed' => Commande::where('statut', 'completed')->count(),
            'commandes_pending' => Commande::where('statut', 'pending')->count(),
            'revenue_total' => Commande::where('statut', 'completed')->sum('total_prix_ttc'),
            'revenue_aujourdhui' => Commande::where('statut', 'completed')->whereDate('created_at', today())->sum('total_prix_ttc'),
            'photos_depot' => BagagePhoto::where('type', 'depot')->where('agent_id', $agentId)->count(),
            'photos_restitution' => BagagePhoto::where('type', 'restitution')->where('agent_id', $agentId)->count(),
            'total_photos' => BagagePhoto::where('agent_id', $agentId)->count(),
        ];

        return view('agent.dashboard', compact('commandes', 'stats'));
    }

    /**
     * Show commande details for photo upload
     */
    public function showCommande($id)
    {
        if (!session('agent_id')) {
            return redirect()->route('agent.login')->with('error', 'Veuillez vous connecter.');
        }

        $commande = Commande::with(['client', 'photos.agent'])->findOrFail($id);
        
        return view('agent.commande-details', compact('commande'));
    }

    /**
     * Upload photo for a commande
     */
    public function uploadPhoto(Request $request, $commandeId)
    {
        if (!session('agent_id')) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $request->validate([
            'type' => 'required|in:depot,restitution',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $commande = Commande::findOrFail($commandeId);
            $agentId = session('agent_id');

            // Upload photo - Use 'public' disk explicitly
            $photo = $request->file('photo');
            $filename = 'bagages/' . $commandeId . '/' . $request->type . '_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            
            // Store using 'public' disk explicitly
            $photoPath = $photo->storeAs('bagages/' . $commandeId, $request->type . '_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension(), 'public');
            
            // Verify file was saved
            $fullPath = Storage::disk('public')->path($photoPath);
            if (!file_exists($fullPath)) {
                Log::error('Photo file not saved', [
                    'expected_path' => $fullPath,
                    'photo_path' => $photoPath,
                    'filename' => $filename
                ]);
                return response()->json(['error' => 'Erreur lors de l\'enregistrement du fichier'], 500);
            }
            
            Log::info('Photo file saved successfully', [
                'photo_path' => $photoPath,
                'file_size' => filesize($fullPath),
            ]);

            // Save photo record
            $bagagePhoto = BagagePhoto::create([
                'commande_id' => $commandeId,
                'type' => $request->type,
                'photo_path' => $photoPath,
                'agent_id' => $agentId,
                'notes' => $request->notes,
            ]);

            Log::info('Photo uploaded by agent', [
                'agent_id' => $agentId,
                'commande_id' => $commandeId,
                'type' => $request->type,
                'photo_id' => $bagagePhoto->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploadée avec succès !',
                'photo' => [
                    'id' => $bagagePhoto->id,
                    'url' => $bagagePhoto->photo_url,
                    'type' => $bagagePhoto->type,
                    'created_at' => $bagagePhoto->created_at->format('d/m/Y H:i'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading photo', [
                'error' => $e->getMessage(),
                'commande_id' => $commandeId,
                'agent_id' => session('agent_id'),
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'upload de la photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a photo
     */
    public function deletePhoto($photoId)
    {
        if (!session('agent_id')) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            $photo = BagagePhoto::findOrFail($photoId);
            
            // Delete file from storage
            if (Storage::exists('public/' . $photo->photo_path)) {
                Storage::delete('public/' . $photo->photo_path);
            }

            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Photo supprimée avec succès !',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout agent
     */
    public function logout()
    {
        session()->forget(['agent_id', 'agent_email', 'agent_role']);
        return redirect()->route('agent.login')->with('success', 'Déconnexion réussie !');
    }
}
