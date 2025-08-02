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

    /**
     * Add a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'user_login' => 'required|string|max:50|unique:user,user_login',
            'user_email' => 'required|email|max:100|unique:user,user_email',
            'user_password' => ['required', new StrongPassword],
            'user_role' => 'required|string|in:member,admin',
            'user_active' => 'boolean'
        ]);

        User::create([
            'user_login' => $request->user_login,
            'user_email' => $request->user_email,
            'user_password' => Hash::make($request->user_password),
            'user_role' => $request->user_role,
            'user_active' => $request->user_active ?? true
        ]);

        return $this->success(null, 'User created successfully', 201);
    }

    /**
     * Delete an user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $userId = $request->input('userId');

        if (!$userId) {
            return $this->error('userId is required', 400);
        }

        $user = User::findOrFail($userId);

        // Delete tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $user->delete();

        return $this->success(null, 'User deleted', 200);
    }
}