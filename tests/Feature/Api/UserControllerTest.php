<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_listed_with_valid_token()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => "john.doe@gmail.com",
            'username' => 'john.doe',
            'password' => 'john.doe123',
        ]);
        $this->actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email']]
            ]);
    }

    public function test_user_can_be_created()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson('/api/users', [
            "name" => "Jane Doe",
            "username" => "jane.doe",
            "email" => "jane.doe@gmail.com",
            "password" => "jane.doe123",
            "confirm_password" => "jane.doe123"
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'User are successfully saved.']);
    }

    public function test_user_can_be_updated()
    {
        $admin = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => "jane.doe@gmail.com",
            'username' => 'jane.doe',
            'password' => 'jane.doe123',
        ]);
        $this->actingAs($admin);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/users/{$target->id}", [
            "name" => "Jane Doe - Edited",
            "username" => "jane.doe.edited",
            "email" => "jane.doe@gmail.com"
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User are successfully updated.']);
    }

    public function test_user_can_be_deleted()
    {
        $admin = User::factory()->create([
            'name' => 'John Doe',
            'email' => "john.doe@gmail.com",
            'username' => 'john.doe',
            'password' => 'john.doe123',
        ]);
        $target = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => "jane.doe@gmail.com",
            'username' => 'jane.doe',
            'password' => 'jane.doe123',
        ]);
        $this->actingAs($admin);

        $response = $this->withHeaders(
            ['Accept' => 'application/json']
        )->deleteJson("/api/users/{$target->id}", [
            "confirm_password" => "john.doe123"
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User are successfully deleted.']);
    }

    public function test_unauthorized_user_gets_401()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }
}
