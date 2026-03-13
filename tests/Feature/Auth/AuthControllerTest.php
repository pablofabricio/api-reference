<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'pablo@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);
    }

    public function test_login_returns_unauthorized_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'pablo@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'pablo@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'invalid_credentials',
            ]);
    }

    public function test_me_requires_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_me_returns_authenticated_user_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ])
            ->assertJsonMissing(['password' => $user->password]);
    }

    public function test_logout_returns_success_message_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    public function test_refresh_returns_new_token_payload_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'igreja@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/refresh');

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);
    }
}
