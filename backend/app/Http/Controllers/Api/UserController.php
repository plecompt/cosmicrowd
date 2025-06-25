<?php

namespace App\Http\Controllers\Api;

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

    //Delete a user
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
