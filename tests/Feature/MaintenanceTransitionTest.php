<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_transition_request_status(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_IT_SUPPORT]);
        $req  = MaintenanceRequest::factory()->create([
            'status' => MaintenanceRequest::STATUS_PENDING,
            'technician_id' => $tech->id, // Assign tech first
        ]);

        Sanctum::actingAs($tech);

        // Accept the job
        $resp1 = $this->postJson("/api/repair-requests/{$req->id}/transition", [
            'status' => MaintenanceRequest::STATUS_ACCEPTED,
        ]);
        $resp1->assertOk();
        $this->assertSame(MaintenanceRequest::STATUS_ACCEPTED, $resp1->json('data.status'));

        // Start work
        $resp2 = $this->postJson("/api/repair-requests/{$req->id}/transition", [
            'status' => MaintenanceRequest::STATUS_IN_PROGRESS,
        ]);
        $resp2->assertOk();
        $this->assertSame(MaintenanceRequest::STATUS_IN_PROGRESS, $resp2->json('data.status'));
    }
}
