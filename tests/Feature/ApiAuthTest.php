<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_fails_with_invalid_credentials(): void
    {
        $resp = $this->postJson('/api/auth/login', [
            'email' => 'no@example.com',
            'password' => 'wrongpass',
        ]);
        $resp->assertStatus(401);
    }

    public function test_login_success_and_me_returns_abilities(): void
    {
        $user = User::factory()->create([
            'password' => 'secret1234',
            'role' => User::ROLE_ADMIN,
        ]);

        $resp = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret1234',
            'device_name' => 'test',
        ]);
        $resp->assertCreated();
        $token = $resp->json('token');
        $this->assertIsString($token);

        // Use Sanctum actingAs for /auth/me
        Sanctum::actingAs($user);
        $me = $this->getJson('/api/auth/me');
        $me->assertOk()->assertJsonStructure([
            'id','name','email','role','abilities'
        ]);
    }
}
