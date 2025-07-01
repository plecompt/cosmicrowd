<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\RecoveryToken;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Check if login is available
    public function checkLoginAvailability(Request $request) {
        $available = !User::where('login', $request->login)->exists();
        
        return response()->json(['available' => $available]);
    }

    // Check if email is available
    public function checkEmailAvailability(Request $request) {
        $available = !User::where('email', $request->email)->exists();
        
        return response()->json(['available' => $available]);
    }

    // Register
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'user_login' => 'required|string|max:50|unique:user,user_login',
            'user_email' => 'required|email|max:100|unique:user,user_email',
            'user_password' => ['required', new StrongPassword],
        ]);

        $user = User::create([
            'user_login' => $request->user_login,
            'user_email' => $request->user_email,
            'user_password' => Hash::make($request->user_password),
            'user_active' => true,
            'user_role' => 'member',
            'user_date_inscription' => now()
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

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

    // Change password
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'different:current_password', new StrongPassword],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'user_password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password successfully changed.']);
    }

    // Change email
    public function changeEmail(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_email' => 'required|email|max:100|unique:user,user_email',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'user_email' => $request->new_email
        ]);

        return response()->json(['message' => 'Email successfully changed.']);
    }

    // ForgotPassword, send an email with a token to reset password
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email|max:100|exists:user,user_email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('user_email', $request->user_email)->first();

        if (!$user->user_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been disabled.'
            ], 403);
        }

        $recoveryToken = RecoveryToken::createForUser($user->user_id);

        // Using blade view in folder ressources/views/emails => recovery.blade.php
        Mail::send('emails.recovery', ['token' => $recoveryToken->recovery_token_value], function ($message) use ($user) {
            $message->to($user->user_email);
            $message->subject('Password reset request');
        });

        return response()->json([
            'success' => true,
            'message' => 'Recovery email sent.'
        ]);
    }

    // Return if the current token is valid or not (expired, used, invalid)
    public function verifyToken(Request $request): JsonResponse
    {
        $token = RecoveryToken::where('recovery_token_value', $request->token)->first();

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token not found.'], 404);
        }

        if ($token->recovery_token_used) {
            return response()->json(['success' => false, 'message' => 'Token already used.'], 409);
        }

        if ($token->recovery_token_expires_at <= now()) {
            return response()->json(['success' => false, 'message' => 'Token expired.'], 410);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid token.',
            'user_id' => $token->recovery_token_user_id
        ]);
    }

    //change the password after a forgotten password
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'new_password' => ['required', new StrongPassword]
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
                'message' => 'Invalid or expired token.'
            ], 400);
        }

        $user = $recoveryToken->user;
        if (!$user->user_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been disabled.'
            ], 403);
        }

        $user->update([
            'user_password' => Hash::make($request->new_password)
        ]);

        $recoveryToken->markAsUsed();

        return response()->json([
            'success' => true,
            'message' => 'Password successfully reset.'
        ]);
    }

    // Delete account
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->user_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        // Deleting all the likes
        $user->solarSystemLikes()->delete();
        $user->planetLikes()->delete();
        $user->moonLikes()->delete();

        // Deleting tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Deleting user and claimed user_solar_system_ownership with onCascade
        $user->delete();

        return response()->json([
            'message' => 'Account successfully deleted.'
        ]);
    }


}
