<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_request_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $resp = $this->postJson('/api/auth/password/email', [
            'email' => $user->email,
        ]);

        $resp->assertOk();
    }
}
