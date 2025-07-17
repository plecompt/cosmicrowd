<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
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
    use ApiResponse;

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

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 'Login successful');
    }

    // Logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Successfully logged out');
    }

    // Me, return current user using token in header
    public function me(Request $request): JsonResponse
    {
        return $this->success([
            'user' => $request->user()
        ], 'Current user retrieved');
    }
}
