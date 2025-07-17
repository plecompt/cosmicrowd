<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Rules\StrongPassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController
{
    use ApiResponse;

    // Add User, only for admin
    public function add(Request $request): JsonResponse
    {
        try {
            // Validate input
            $request->validate([
                'user_login' => 'required|string|max:50|unique:user,user_login',
                'user_email' => 'required|email|max:100|unique:user,user_email',
                'user_password' => ['required', new StrongPassword],
                'user_role' => 'required|string|in:member,admin',
                'user_active' => 'boolean'
            ]);

            // Creating user in DB
            $user = User::create([
                'user_login' => $request->user_login,
                'user_email' => $request->user_email,
                'user_password' => Hash::make($request->user_password),
                'user_role' => $request->user_role,
                'user_active' => $request->user_active ?? true,
                'user_date_inscription' => now()
            ]);

            return $this->success(null, 'User created successfully', 201);

        } catch (\Exception $e) {
            return $this->error('Error while creating user', 500);
        }
    }

    // Delete a user, only for admin
    public function delete(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('userId');

            if (!$userId) {
                return $this->error('userId is required', 400);
            }

            $user = User::findOrFail($userId);

            // Deleting likes of the user
            $user->likes()->delete();

            // Make user solar systems claimable
            $user->solarSystems()->update([
                'user_id' => null,
                'is_claimable' => true,
                'claimed_at' => null
            ]);

            $user->delete();

            return $this->success(null, 'User deleted', 200);
        } catch (\Exception $e) {
            return $this->error('Error while deleting user', 500);
        }
    }
}
