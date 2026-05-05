<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\AdminAccountCreatedMail;
use App\Mail\AdminPasswordResetMail;

class AdminAccountController extends Controller
{
    /**
     * Affiche le formulaire de création de compte admin (protégé par token)
     */
    public function showCreateForm(Request $request)
    {
        $token = $request->query('token');
        $validToken = env('ADMIN_CREATE_TOKEN', 'default-secret-token-change-in-env');
        
        if ($token !== $validToken) {
            abort(403, 'Token invalide');
        }
        
        return view('admin.create-account');
    }

    /**
     * Crée un compte admin et envoie les identifiants par email
     */
    public function createAccount(Request $request)
    {
        $token = $request->input('token');
        $validToken = env('ADMIN_CREATE_TOKEN', 'default-secret-token-change-in-env');
        
        if ($token !== $validToken) {
            abort(403, 'Token invalide');
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|string|in:admin,agent,user',
        ]);

        // Générer un mot de passe sécurisé
        $password = Str::random(16);
        $hashedPassword = Hash::make($password);
        $local = explode('@', (string)$request->email)[0] ?? 'User';

        // Créer le compte admin
        $data = [
            'email' => $request->email,
            'password_hash' => $hashedPassword,
            'role' => $request->role ?? 'admin',
        ];
        if (Schema::hasColumn('users', 'name')) {
            $data['name'] = ucfirst(substr((string)$local, 0, 50));
        }
        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = $hashedPassword;
        }
        $user = User::create($data);

        // Envoyer l'email avec les identifiants
        try {
            // Send credentials to the newly created user's email.
            Mail::to($user->email)
                ->send(new AdminAccountCreatedMail($user, $password));
            
            Log::info('Admin account created and email sent', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin account creation email', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        return redirect()->back()->with('success', 'Compte créé avec succès. Les identifiants ont été envoyés par email.');
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe (protégé par token)
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $validToken = env('ADMIN_RESET_TOKEN', 'default-reset-token-change-in-env');
        
        if ($token !== $validToken) {
            abort(403, 'Token invalide');
        }
        
        return view('admin.reset-password');
    }

    /**
     * Réinitialise le mot de passe admin et envoie le nouveau mot de passe par email
     */
    public function resetPassword(Request $request)
    {
        $token = $request->input('token');
        $validToken = env('ADMIN_RESET_TOKEN', 'default-reset-token-change-in-env');
        
        if ($token !== $validToken) {
            abort(403, 'Token invalide');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'Aucun compte admin trouvé avec cet email.']);
        }

        // Générer un nouveau mot de passe sécurisé
        $newPassword = Str::random(16);
        $newHash = Hash::make($newPassword);
        $user->password_hash = $newHash;
        if (Schema::hasColumn('users', 'password')) {
            $user->password = $newHash;
        }
        $user->save();

        // Envoyer l'email avec le nouveau mot de passe
        try {
            // Send the new password to the user's email.
            Mail::to($user->email)
                ->send(new AdminPasswordResetMail($user, $newPassword));
            
            Log::info('Admin password reset and email sent', [
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin password reset email', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        return redirect()->back()->with('success', 'Mot de passe réinitialisé avec succès. Le nouveau mot de passe a été envoyé par email.');
    }
}
