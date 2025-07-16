<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\RecoveryToken;
use App\Rules\StrongPassword;
use App\Models\SolarSystem;
use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController
{
    // Login
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'user_email' => 'required|string',
            'user_password' => 'required|string',
        ]);

        $user = User::where('user_email', $request->user_email)->first();

        if (!$user || !Hash::check($request->user_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials.'],
            ]);
        }

        if (!$user->user_active) {
            throw ValidationException::withMessages([
                'error' => ['Account has been disabled.'],
            ]);
        }

        $user->update([
            'user_last_login' => now()
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    // me, return current user using token in header
    public function me(Request $request): JsonResponse
    {
        return response()->json(['succes' => true, 'user' => $request->user()]);
    }


}
