<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'john.doe',
            'email' => 'john.doe@gmail.com',
            'password' => 'john.doe123',
            'confirm_password' => 'john.doe123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_register_validation_error()
    {
        $response = $this->withHeaders(
            ['Accept' => 'application/json']
        )->postJson('/api/register', [
            'username' => 'john.doe',
            'password' => 'john.doe123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_login_successfully()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'john.doe',
            'email' => 'john.doe@gmail.com',
            'password' => Hash::make('john.doe123')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'john.doe',
            'password' => 'john.doe123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user', 'expired_at']
            ]);
    }

    public function test_login_wrong_password()
    {
        $user = User::factory()->create([
            'username' => 'john.doe@gmail.com',
            'password' => Hash::make('john.doe123')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'john.doe@gmail.com',
            'password' => 'john.doe123456'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'The username or password is incorrect.']);
    }

    public function test_login_user_not_found()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'john.doe',
            'password' => 'john.doe123'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'The username or password is incorrect.']);
    }

    public function test_logout_successfully()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully.']);
    }
}
