<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'user_login' => 'testUser',
            'user_email' => 'testUser@cosmicrowd.com',
            'user_password' => '@RandomPassword1234',
        ];

        $response = $this->postJson('/api/v1/users/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'user' => [
                            'user_id',
                            'user_login',
                            'user_email',
                        ],
                    ],
                 ])
                 ->assertJsonMissing(['user_password']);

        // Check user is created in database
        $this->assertDatabaseHas('user', [
            'user_login' => 'testUser',
            'user_email' => 'testUser@cosmicrowd.com',
        ]);

        // Check password is hashed and matches
        $user = User::where('user_email', 'testUser@cosmicrowd.com')->first();
        $this->assertTrue(Hash::check('@RandomPassword1234', $user->user_password));
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'user_login' => 'testUser',
            'user_email' => 'testUser@cosmicrowd.com',
            'user_password' => Hash::make('password123'),
            'user_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'user_email' => 'testUser@cosmicrowd.com',
            'user_password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                         'user' => [
                             'user_id',
                             'user_login',
                             'user_email',
                         ]
                     ]
                 ])
                 ->assertJsonMissing(['user_password']);
    }

    public function test_user_cannot_register_with_invalid_data()
    {
        $userData = [
            'user_login' => '',
            'user_email' => 'invalid-email',
            'user_password' => '123',
        ];

        $response = $this->postJson('/api/v1/users/register', $userData);

        $response->assertStatus(500)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors'
                 ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
       $user = User::factory()->create([
           'user_login' => 'testUser',
           'user_email' => 'testUser@cosmicrowd.com',
           'user_password' => Hash::make('password123'),
           'user_active' => true,
           'user_role' => 'member',
       ]);
    
       $response = $this->postJson('/api/v1/auth/login', [
           'user_login' => 'testUser',
           'user_password' => 'wrongpassword',
       ]);
    
       $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors'
                ]);
    }

}
