<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Asset;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_summary_structure(): void
    {
        $user = User::factory()->create();
        Asset::factory()->count(5)->create();
        MaintenanceRequest::factory()->count(12)->create();

        Sanctum::actingAs($user);
        $resp = $this->getJson('/api/stats/summary');
        $resp->assertOk()->assertJsonStructure([
            'assets_total',
            'requests_open',
            'requests_closed',
            'priority_counts' => ['low','medium','high','urgent'],
            'recent_daily' => [
                '*' => ['date','count']
            ],
        ]);
    }
}
