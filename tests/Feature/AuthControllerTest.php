<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register() {
        $response = $this->postJson('/api/register', [
            'name' => 'Rafael Garcia',
            'email' => 'rafael@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'patient',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['user', 'token']);
    }
    
    public function test_user_cannot_register() {
        $user = User::factory()->create([
            'email' => 'rafael@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Rafael Garcia',
            'email' => 'rafael@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'patient',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    public function test_user_can_login() {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }
    
    public function test_user_send_invalid_credentials() {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_can_logout() {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);
    }
}
