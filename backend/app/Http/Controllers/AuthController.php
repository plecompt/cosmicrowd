<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RecoveryToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_pseudo' => 'required|string|max:50|unique:user,user_pseudo',
            'user_email' => 'required|string|email|max:100|unique:user,user_email',
            'user_password' => 'required|string|min:6',
            'user_avatar' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'user_pseudo' => $request->user_pseudo,
            'user_email' => $request->user_email,
            'user_password' => Hash::make($request->user_password),
            'user_avatar' => $request->user_avatar,
            'user_registration_date' => now(),
            'user_is_admin' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email',
            'user_password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('user_email', $request->user_email)->first();

        if (!$user || !Hash::check($request->user_password, $user->user_password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        // Ici vous pourrez ajouter la génération de token JWT plus tard
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email|exists:user,user_email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('user_email', $request->user_email)->first();
        $recoveryToken = RecoveryToken::createForUser($user->user_id);

        // Ici vous pourrez ajouter l'envoi d'email plus tard
        // Mail::send('emails.recovery', ['token' => $recoveryToken->recovery_token_value], ...);

        return response()->json([
            'success' => true,
            'message' => 'Token de récupération envoyé',
            'token' => $recoveryToken->recovery_token_value // À retirer en production
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $recoveryToken = RecoveryToken::findValidToken($request->token);

        if (!$recoveryToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 400);
        }

        $user = $recoveryToken->user;
        $user->update([
            'user_password' => Hash::make($request->new_password)
        ]);

        $recoveryToken->markAsUsed();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }
}
