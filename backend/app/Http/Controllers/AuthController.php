<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RecoveryToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'user_login' => 'required|string|unique:user,user_login',
            'user_email' => 'required|email|unique:user,user_email',
            'user_password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'user_login' => $request->user_login,
            'user_email' => $request->user_email,
            'user_password' => Hash::make($request->user_password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'user_login' => 'required|string',
            'user_password' => 'required|string',
        ]);

        $user = User::where('user_login', $request->user_login)->first();

        if (!$user || !Hash::check($request->user_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'user_login' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'user_password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès']);
    }

    public function changeEmail(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_email' => 'required|email|unique:user,user_email',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'user_email' => $request->new_email
        ]);

        return response()->json(['message' => 'Email modifié avec succès']);
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
