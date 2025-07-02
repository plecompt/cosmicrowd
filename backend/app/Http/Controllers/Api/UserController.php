<?php

namespace App\Http\Controllers\Api;

use App\Rules\StrongPassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Return a user
    public function view(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('userId');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Need a userId'
                ], 400);
            }
            
            $user = User::findOrFail($userId);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'user_id' => $user->user_id,
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'user_active' => $user->user_active,
                    'user_role' => $user->user_role,
                    'user_last_login' => $user->user_last_login,
                    'user_date_inscription' => $user->user_date_inscription
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    // Add User, only for admin
    public function add(Request $request): JsonResponse
    {
        try {
            //Validate input
            $request->validate([
                'user_login' => 'required|string|max:50|unique:user,user_login',
                'user_email' => 'required|email|max:100|unique:user,user_email',
                'user_password' => ['required', new StrongPassword],
                'user_role' => 'required|string|in:member,admin', // 🌟 Seule différence !
                'user_active' => 'boolean'
            ]);

            //Creating user in db
            $user = User::create([
                'user_login' => $request->user_login,
                'user_email' => $request->user_email,
                'user_password' => Hash::make($request->user_password),
                'user_role' => $request->user_role,
                'user_active' => $request->user_active ?? true,
                'user_date_inscription' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while creating user'
            ], 500);
        }
    }


    //Delete an user, only for admin
    public function delete(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('userId');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'userId requis'
                ], 400);
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
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while deleting user'
            ], 500);
        }
    }
}
