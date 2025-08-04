<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\RecoveryToken;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;

class UserController
{
    use ApiResponse;

    /**
     * Check if login is available
     *
     * @param Request $request
     * @return JsonResponse Availabality of login
     */
    public function checkLoginAvailability(Request $request) {
        $available = !User::where('user_login', $request->login)->exists();
        
        return $this->success(['available' => $available]);
    }

    /**
     * Check if email is available
     *
     * @param Request $request
     * @return JsonResponse Availabality of email
     */
    public function checkEmailAvailability(Request $request) {
        $available = !User::where('user_email', $request->email)->exists();
        
        return $this->success(['available' => $available]);
    }

    /**
     * Return the given user
     *
     * @param Request $request
     * @param string $userId
     * @return JsonResponse Return the user with given userId
     */
    public function view(Request $request, string $userId): JsonResponse
    {
        try {
            if (!$userId) {
                return $this->error('Need a userId', 400);
            }
            
            $user = User::findOrFail($userId);
            
            return $this->success([
                'user' => [
                    'user_id' => $user->user_id,
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'user_active' => $user->user_active,
                    'user_role' => $user->user_role,
                    'user_last_login' => $user->user_last_login,
                    'user_date_inscription' => $user->user_date_inscription
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->error('User not found', 404);
        }
    }

    /**
     * Contact, send Cosmicrowd an email from user_email and a confirmation to user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function contact(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email|max:100',
                'user_message' => 'required|string|min:10|max:1000',
                'user_name' => 'nullable|string|max:100',
                'subject' => 'nullable|string|max:200'
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 400, $validator->errors());
            }

            $contactData = [
                'user_email' => $request->user_email,
                'user_name' => $request->user_name ?? 'Anonymous user',
                'user_message' => $request->user_message,
                'subject' => $request->subject ?? 'Contact from Cosmicrowd',
                'sent_at' => now(),
                'user_ip' => $request->ip(),
            ];

            // Sending mail to contact@cosmicrowd.com
            Mail::send('emails.contact', $contactData, function ($message) use ($contactData): void {
                $message->to('contact@cosmicrowd.com')
                    ->replyTo($contactData['user_email'], $contactData['user_name'])
                    ->subject('[CosmiCrowd Contact] ' . $contactData['subject']);
            });

            // Sending mail confirmation to user
            Mail::send('emails.contact-confirmation', $contactData, function ($message) use ($contactData): void {
                $message->to($contactData['user_email'], $contactData['user_name'])
                    ->subject('Message Received - CosmiCrowd');
            });

            return $this->success(null, 'Mail successfully send');

        } catch (\Exception $e) {
            return $this->error('Error while sending your email, please try again.', 500);
        }
    }

    /**
     * Register an user in database
     *
     * @param Request $request
     * @return JsonResponse Return the created user, and a token that front will inject in every request
     */
    public function register(Request $request): JsonResponse
    {
        try {
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

            return $this->success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Error while regestering, please try again.', 500);
        }
    }

    /**
     * User asked to change password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => ['required', 'different:current_password', new StrongPassword],
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->user_password)) {
                return $this->error('Current password is incorrect', 401,['current_password' => ['Current password is incorrect.']]);
            }

            $user->update([
                'user_password' => Hash::make($request->new_password)
            ]);

            return $this->success(null, 'Password successfully changed.');
        } catch (\Exception $e) {
            return $this->error('Password not changed, please try again.', 500);
        }
    }

    /**
     * User asked to change email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_email' => 'required|email|max:100|unique:user,user_email',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->user_password)) {
                return $this->error(
                    'Current password is incorrect',
                    401,
                    ['current_password' => ['Current password is incorrect.']]
                );
            }

            $user->update([
                'user_email' => $request->new_email
            ]);

            return $this->success(null, 'Email successfully changed.');
        } catch (\Exception $e) {
            return $this->error('Email not changed, please try again.', 500);
        }
    }

    /**
     * Send an email to $user_email with a link containing a token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email|max:100|exists:user,user_email'
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 400, $validator->errors());
            }

            $user = User::where('user_email', $request->user_email)->first();

            if (!$user->user_active) {
                return $this->error('This account has been disabled.', 403);
            }

            $recoveryToken = RecoveryToken::createForUser($user->user_id);

            // Using blade view in folder ressources/views/emails => recovery.blade.php
            Mail::send('emails.recovery', ['token' => $recoveryToken->recovery_token_value], function ($message) use ($user): void {
                $message->to($user->user_email);
                $message->subject('Password reset request');
            });

            return $this->success(null, 'Recovery email sent.');
        } catch (\Exception $e) {
            return $this->error('Recovery email not send, please try again', 500);
        }
    }

    /**
     * Return if the current token is valid or not (expired, used, invalid)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyToken(Request $request): JsonResponse
    {
        try {
            $token = RecoveryToken::where('recovery_token_value', $request->token)->first();

            if (!$token) {
                return $this->error('Token not found.', 404);
            }

            if ($token->recovery_token_used) {
                return $this->error('Token already used.', 409);
            }

            if ($token->recovery_token_expires_at <= now()) {
                return $this->error('Token expired.', 410);
            }

            return $this->success(['user_id' => $token->recovery_token_user_id], 'Valid token.');
        } catch (\Exception $e) {
            return $this->error('Something went wrong while checking token validity, please try again.', 500);
        }
    }

    /**
     * Change the password after a forgotten password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'new_password' => ['required', new StrongPassword]
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 400, $validator->errors());
            }

            $recoveryToken = RecoveryToken::findValidToken($request->token);

            if (!$recoveryToken) {
                return $this->error('Invalid or expired token.', 400);
            }

            $user = $recoveryToken->user;
            if (!$user->user_active) {
                return $this->error('This account has been disabled.', 403);
            }

            $user->update([
                'user_password' => Hash::make($request->new_password)
            ]);

            $recoveryToken->markAsUsed();

            return $this->success(null, 'Password successfully reset.');
        } catch (\Exception $e) {
            return $this->error('Something went wrong while changing password, please try again.', 500);
        }
    }

    /**
     * Deleting the account after user requested it
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
            ]);
        
            $user = $request->user();
            if (!Hash::check($request->current_password, $user->user_password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect.'],
                ]);
            }
        
            // Release claimed resources by setting user_id to null
            SolarSystem::where('user_id', $user->user_id)->update(['user_id' => null]);
            Planet::where('user_id', $user->user_id)->update(['user_id' => null]);
            Moon::where('user_id', $user->user_id)->update(['user_id' => null]);
        
            // Delete tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete();
        
            return $this->success(null, 'Account successfully deleted.');
        } catch (\Exception $e) {
            return $this->error('Something went wrong while deleting account, please try again.', 500);
        }
    }
}
