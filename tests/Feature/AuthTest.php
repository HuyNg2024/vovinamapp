<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        // 1. Arrange: Create a user
        $user = User::factory()->create([
            'email' => 'test@vovinamapp.com',
            'password' => Hash::make('secret123'),
        ]);

        // 2. Act: Attempt to login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@vovinamapp.com',
            'password' => 'secret123',
        ]);

        // 3. Assert: Check if login is successful and returns token
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user'
        ]);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        // 1. Arrange: Create a user
        User::factory()->create([
            'email' => 'test@vovinamapp.com',
            'password' => Hash::make('secret123'),
        ]);

        // 2. Act: Attempt to login with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@vovinamapp.com',
            'password' => 'wrongpassword',
        ]);

        // 3. Assert: Check if login fails with 401
        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized'
        ]);
    }
}
