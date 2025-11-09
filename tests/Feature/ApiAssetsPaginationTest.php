<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAssetsPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assets_index_returns_data_and_meta(): void
    {
        $user = User::factory()->create();
    Asset::factory()->count(30)->create();
    $this->assertSame(30, Asset::count());

        Sanctum::actingAs($user);
    $directPaginateTotal = Asset::query()->paginate(10)->total();
    $this->assertSame(30, $directPaginateTotal, 'Direct paginate total mismatch');
        $resp = $this->getJson('/api/assets?per_page=10');
        $resp->assertOk();
        $resp->assertJsonStructure([
            'data',
            'meta' => ['current_page','per_page','total','last_page'],
        ]);
        // meta.total should match total assets count
        $this->assertSame(Asset::count(), $resp->json('meta.total'));
        $this->assertGreaterThanOrEqual(0, $resp->json('meta.total'));
    }
}
